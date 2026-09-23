<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Removes distrib_panetiers rows duplicated by concurrent submissions of the distribution form
 * (see the lock added in DistribPanetierController::store()).
 *
 * A livreur can legitimately have several rows in one production (supplements during the shift), so a row
 * is only considered a duplicate when it was created within a few seconds of another row of the same
 * production and the same entity. Rows linked to a versement are never deleted, and balances are not
 * modified: livreur balances are computed from the rows, and stored client balances were only counted once.
 */
class RemoveDuplicateDistribPanetiers extends Command
{
    protected $signature = 'distrib-panetiers:remove-duplicates
        {--production=* : Only check these production_panetier ids}
        {--window=5 : Max seconds between two rows of the same entity to be considered duplicates}
        {--execute : Really delete the rows (dry-run by default)}';

    protected $description = 'Detect (dry-run) or delete distrib_panetiers rows duplicated by double submissions';

    private const ENTITY_COLUMNS = ['livreur_id', 'client_id', 'boutique_id', 'abonnement_id'];

    public function handle(): int
    {
        $isExecuteMode = (bool) $this->option('execute');
        $duplicateWindowInSeconds = (int) $this->option('window');
        $productionIdentifierFilter = array_map('intval', $this->option('production'));

        $this->info($isExecuteMode ? 'EXECUTE MODE: rows will be deleted' : 'DRY-RUN: nothing will be modified');

        $rowsSharingProductionAndEntity = $this->fetchRowsSharingProductionAndEntity($productionIdentifierFilter);
        $reportLines = [];
        $rowsToDelete = collect();
        $clustersToReview = 0;
        $rowsLeftAsPossibleSupplements = 0;

        foreach ($rowsSharingProductionAndEntity as $rowsOfOneEntity) {
            foreach ($this->splitIntoDuplicateClusters($rowsOfOneEntity, $duplicateWindowInSeconds) as $cluster) {
                if ($cluster->count() === 1) {
                    $rowsLeftAsPossibleSupplements++;
                    continue;
                }
                $decisions = $this->decideActionsForCluster($cluster);
                if ($decisions->contains(fn ($decision) => $decision['action'] === 'REVIEW')) {
                    $clustersToReview++;
                }
                foreach ($decisions as $decision) {
                    $row = $decision['row'];
                    $reportLines[] = [
                        $row->production_panetier_id,
                        $this->describeEntity($row),
                        $row->id,
                        $row->nombre_pain,
                        $row->bonus ?? 'null',
                        $row->versement_id ?? '-',
                        $row->created_at,
                        $decision['action'],
                        $decision['reason'],
                    ];
                    if ($decision['action'] === 'DELETE') {
                        $rowsToDelete->push($row);
                    }
                }
            }
        }

        if (empty($reportLines)) {
            $this->info('No duplicate found.');
            return self::SUCCESS;
        }

        $this->table(
            ['production', 'entity', 'row id', 'nombre_pain', 'bonus', 'versement', 'created_at', 'action', 'reason'],
            $reportLines
        );
        $this->printSummary($rowsToDelete, $clustersToReview, $rowsLeftAsPossibleSupplements);

        if (!$isExecuteMode) {
            $this->warn('Dry-run only. Re-run with --execute to delete the rows marked DELETE.');
            return self::SUCCESS;
        }
        if ($rowsToDelete->isEmpty()) {
            return self::SUCCESS;
        }
        if (!$this->confirm("Delete {$rowsToDelete->count()} rows?")) {
            return self::FAILURE;
        }

        return $this->deleteRows($rowsToDelete);
    }

    /**
     * @return Collection<string, Collection> rows grouped by "production|entity", only groups with more than one row
     */
    private function fetchRowsSharingProductionAndEntity(array $productionIdentifierFilter): Collection
    {
        $groupsWithSeveralRows = DB::table('distrib_panetiers')
            ->select(array_merge(['production_panetier_id'], self::ENTITY_COLUMNS))
            ->when($productionIdentifierFilter, fn ($query) => $query->whereIn('production_panetier_id', $productionIdentifierFilter))
            ->groupBy(array_merge(['production_panetier_id'], self::ENTITY_COLUMNS))
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $candidateRows = collect();
        foreach ($groupsWithSeveralRows->pluck('production_panetier_id')->unique() as $productionIdentifier) {
            $candidateRows = $candidateRows->merge(
                DB::table('distrib_panetiers')->where('production_panetier_id', $productionIdentifier)->orderBy('id')->get()
            );
        }

        return $candidateRows
            ->groupBy(fn ($row) => $row->production_panetier_id . '|' . $this->describeEntity($row))
            ->filter(fn (Collection $rowsOfOneEntity) => $rowsOfOneEntity->count() > 1);
    }

    /**
     * Rows created within the window of the previous row belong to the same double submission.
     */
    private function splitIntoDuplicateClusters(Collection $rowsOfOneEntity, int $duplicateWindowInSeconds): Collection
    {
        $clusters = collect();
        $currentCluster = collect();
        $previousCreatedAt = null;
        foreach ($rowsOfOneEntity->sortBy('id') as $row) {
            $createdAt = Carbon::parse($row->created_at);
            if ($previousCreatedAt !== null && abs($createdAt->diffInSeconds($previousCreatedAt)) > $duplicateWindowInSeconds) {
                $clusters->push($currentCluster);
                $currentCluster = collect();
            }
            $currentCluster->push($row);
            $previousCreatedAt = $createdAt;
        }
        $clusters->push($currentCluster);

        return $clusters;
    }

    private function decideActionsForCluster(Collection $cluster): Collection
    {
        $paidRows = $cluster->filter(fn ($row) => $row->versement_id !== null);
        $keptRows = $paidRows->isNotEmpty() ? $paidRows : collect([$cluster->sortBy('id')->first()]);

        return $cluster->map(function ($row) use ($keptRows) {
            if ($keptRows->contains('id', $row->id)) {
                $reason = $row->versement_id !== null ? 'has versement' : 'oldest copy';
                return ['row' => $row, 'action' => 'KEEP', 'reason' => $reason];
            }
            if ((int) $row->nombre_pain === 0) {
                return ['row' => $row, 'action' => 'DELETE', 'reason' => 'unpaid copy with 0 pain'];
            }
            $identicalKeptRow = $keptRows->first(fn ($keptRow) => $this->haveSameQuantities($keptRow, $row));
            if ($identicalKeptRow !== null) {
                return ['row' => $row, 'action' => 'DELETE', 'reason' => "unpaid copy of #{$identicalKeptRow->id}"];
            }
            return ['row' => $row, 'action' => 'REVIEW', 'reason' => 'unpaid, values differ from kept row'];
        })->values();
    }

    private function haveSameQuantities(object $firstRow, object $secondRow): bool
    {
        return (int) $firstRow->nombre_pain === (int) $secondRow->nombre_pain
            && (int) ($firstRow->bonus ?? 0) === (int) ($secondRow->bonus ?? 0);
    }

    private function describeEntity(object $row): string
    {
        foreach (self::ENTITY_COLUMNS as $entityColumn) {
            if ($row->{$entityColumn} !== null) {
                return str_replace('_id', '', $entityColumn) . ' ' . $row->{$entityColumn};
            }
        }
        return 'none';
    }

    private function printSummary(Collection $rowsToDelete, int $clustersToReview, int $rowsLeftAsPossibleSupplements): void
    {
        $this->newLine();
        $this->info("Rows to delete: {$rowsToDelete->count()} ({$rowsToDelete->sum('nombre_pain')} pains)");
        foreach ($rowsToDelete->groupBy('production_panetier_id') as $productionIdentifier => $rowsOfProduction) {
            $this->line("  production {$productionIdentifier}: {$rowsOfProduction->count()} rows, "
                . "{$rowsOfProduction->sum('nombre_pain')} pains, ids " . $rowsOfProduction->pluck('id')->implode(','));
        }
        $this->info("Duplicate groups needing manual review: {$clustersToReview}");
        $this->info("Rows of the same entity/production created apart (supplements, untouched): {$rowsLeftAsPossibleSupplements}");
    }

    private function deleteRows(Collection $rowsToDelete): int
    {
        $backupFilePath = 'distrib_panetiers_duplicates_backup_' . now()->format('Ymd_His') . '.json';
        Storage::disk('local')->put($backupFilePath, json_encode($rowsToDelete->values(), JSON_PRETTY_PRINT));
        $this->info('Backup written to ' . Storage::disk('local')->path($backupFilePath));

        $deletedRowCount = 0;
        DB::transaction(function () use ($rowsToDelete, &$deletedRowCount) {
            foreach ($rowsToDelete as $plannedRow) {
                // re-check under lock: the row may have been paid or edited since the report was computed
                $currentRow = DB::table('distrib_panetiers')->where('id', $plannedRow->id)->lockForUpdate()->first();
                if ($currentRow === null) {
                    continue;
                }
                if ($currentRow->versement_id !== null || (int) $currentRow->nombre_pain !== (int) $plannedRow->nombre_pain) {
                    $this->warn("Skipped #{$plannedRow->id}: changed since the report (paid or edited)");
                    continue;
                }
                $deletedRowCount += DB::table('distrib_panetiers')->where('id', $plannedRow->id)->delete();
            }
        });
        $this->info("Deleted {$deletedRowCount} rows.");

        return self::SUCCESS;
    }
}
