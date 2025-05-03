<?php

namespace App\Service;

use App\Models\Abonnement;
use App\Models\Boulangerie;
use App\Models\Boutique;
use App\Models\Caisse;
use App\Models\Client;
use App\Models\DistribPanetier;
use App\Models\Livreur;
use App\Models\TypeRecette;
use App\Models\Versement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VersementService
{


    public function store(Request $request): JsonResponse
    {
        $data = $this->validateRequest($request);
        $caisse = $this->getCaisse($data);
        $versement = new Versement($data);
        $montant_verse = $data['montant'];

        DB::transaction(function () use ($data, $versement, $montant_verse, $caisse) {
            $distrib_panetier = $this->getDistribPanetier($data);
            $this->setupVersementEntity($versement, $distrib_panetier, $data);
            $this->processVersement($versement, $distrib_panetier, $data, $montant_verse, $caisse);
            $this->associateVersementWithCaisse($versement, $caisse);
            $this->updateCaisseSolde($caisse, $montant_verse);
            $this->associateDistribPanetierWithVersement($distrib_panetier, $versement);
        });

        return response()->json($versement, 201);
    }

    /**
     * Validate the incoming request
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'montant' => 'required|numeric',
            'nombre_retour' => 'required|integer',
            'nombre_pain_matin' => 'integer',
            'distrib_panetier_id' => 'integer|exists:distrib_panetiers,id',
        ]);
    }

    /**
     * Get the caisse for the transaction
     */
    protected function getCaisse($data)
    {
        if (!isset($data['caisse_id'])) {
            $data['caisse_id'] = Caisse::requireCaisseOfLoggedInUser()->id;
        }

        return Caisse::findOrFail($data['caisse_id']);
    }

    /**
     * Get the distribution panetier
     */
    protected function getDistribPanetier($data): DistribPanetier
    {
        $distrib_panetier = DistribPanetier::findOrFail($data['distrib_panetier_id']);
        $distrib_panetier->nombre_retour = $data['nombre_retour'];

        return $distrib_panetier;
    }

    /**
     * Setup versement entity based on distrib_panetier entity type
     */
    protected function setupVersementEntity(Versement $versement, DistribPanetier $distrib_panetier, array $data): Versement
    {
        if ($distrib_panetier->isForLivreur()) {
            $versement->livreur_id = $distrib_panetier->livreur_id;
        } elseif ($distrib_panetier->isForClient()) {
            $versement->client_id = $distrib_panetier->client_id;
        } elseif ($distrib_panetier->isForBoutique()) {
            $versement->boutique_id = $distrib_panetier->boutique_id;
        } elseif ($distrib_panetier->isForAbonnement()) {
            $versement->abonnement_id = $distrib_panetier->abonnement_id;
        }
        $versement->montant_verse = $data['montant'];
        $versement->nombre_retour = $data['nombre_retour'];
        $versement->date_versement = today()->toDateString();
        $versement->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;

        return $versement;
    }

    /**
     * Process the versement based on its type
     */
    protected function processVersement(Versement $versement, DistribPanetier $distrib_panetier, array $data, float $montant_verse, Caisse $caisse): void
    {
        if ($versement->isForLivreur()) {
            $this->processLivreurVersement($versement, $distrib_panetier, $data, $montant_verse, $caisse);
        } elseif ($versement->isForClient()) {
            $this->processClientVersement($versement, $distrib_panetier, $data, $montant_verse, $caisse);
        } elseif ($versement->isForBoutique()) {
            $this->processBoutiqueVersement($versement, $data, $montant_verse, $caisse);
        } elseif ($versement->isForAbonnement()) {
            $this->processAbonnementVersement($versement, $data, $montant_verse, $caisse);
        }
    }

    /**
     * Process livreur versement
     */
    protected function processLivreurVersement(Versement $versement, DistribPanetier $distrib_panetier, array $data, float $montant_verse, Caisse $caisse): void
    {
        $livreur = Livreur::findOrFail($data['livreur_id']);
        $versement->livreur()->associate($livreur);

        // Check the amount paid by the delivery person
        $compte_livreur = $livreur->compteLivreur;
        $compte_data = $compte_livreur->toArray();
        $montant_a_verser = $distrib_panetier->valeurPain();

        // Update the delivery person's reliquat balance
        if ($montant_verse > $montant_a_verser) {
            $compte_livreur->solde_reliquat -= ($montant_verse - $montant_a_verser);
        } elseif ($montant_verse < $montant_a_verser) {
            $compte_livreur->solde_reliquat += ($montant_a_verser - $montant_verse);
        } else if ($montant_verse == $montant_a_verser) {
            $compte_livreur->solde_reliquat = 0;
        }

        $versement->compte_data = $compte_data;
        $versement->save();

        // Update the delivery person's account
        $compte_livreur->dette = $compte_livreur->dette - ($distrib_panetier->nombre_pain * $livreur->prix_pain);
        $compte_livreur->solde_pain = $compte_livreur->solde_pain - $distrib_panetier->nombre_pain;
        $compte_livreur->save();

        // Create the receipt
        $identifier = $versement->identifier();
        $this->createRecette(
            $caisse,
            $montant_verse,
            TypeRecette::VERSEMENT_LIVREUR,
            'Versement de ' . $identifier
        );
    }

    /**
     * Process client versement
     * @throw ModelNotFoundException
     */
    protected function processClientVersement(Versement $versement, DistribPanetier $distrib_panetier, array $data, float $montant_verse, Caisse $caisse): void
    {
        $client = Client::find($data['client_id']);
        $versement->client()->associate($client);

        $compte_client = $versement->client->compteClient;
        $compte_client->dette = $compte_client->dette - (
                $distrib_panetier->nombre_pain * Boulangerie::requireBoulangerieOfLoggedInUser()->prix_pain_client
            );
        $compte_client->solde_pain = $compte_client->solde_pain - $distrib_panetier->nombre_pain;
        $compte_client->save();

        $this->createRecette(
            $caisse,
            $montant_verse,
            TypeRecette::VERSEMENT_CLIENT,
            'Versement de ' . $client->identifier()
        );
    }

    /**
     * Process boutique versement
     */
    protected function processBoutiqueVersement(Versement $versement, array $data, float $montant_verse, Caisse $caisse): void
    {
        $boutique = Boutique::findOrFail($data['boutique_id']);
        $versement->boutique()->associate($boutique);

        $this->createRecette(
            $caisse,
            $montant_verse,
            TypeRecette::VENTE_BOUTIQUE,
            'Vente Boutique  ' . $boutique->identifier()
        );
    }

    /**
     * Process abonnement versement
     */
    protected function processAbonnementVersement(Versement $versement, array $data, float $montant_verse, Caisse $caisse): void
    {
        $abonnement = Abonnement::findOrFail($data['abonnement_id']);
        $versement->abonnement()->associate($abonnement);

        $this->createRecette(
            $caisse,
            $montant_verse,
            TypeRecette::VERSEMENT_ABONNEMENT,
            'Paiement Abonnement :  ' . $abonnement->identifier()
        );
    }

    /**
     * Create a recette record
     */
    protected function createRecette(Caisse $caisse, float $montant, string $type_constant_name, string $commentaire)
    {
        $caisse->recettes()->create([
            'montant' => $montant,
            'type_recette_id' => TypeRecette::ofCurrentBoulangerie()
                ->where("constant_name", $type_constant_name)
                ->firstOrFail()->id,
            'commentaire' => $commentaire,
            'boulangerie_id' => Boulangerie::requireBoulangerieOfLoggedInUser()->id,
        ]);
    }

    /**
     * Associate versement with caisse
     */
    protected function associateVersementWithCaisse(Versement $versement, Caisse $caisse)
    {
        $versement->caisse()->associate($caisse);
        $versement->save();
    }

    /**
     * Update caisse solde
     */
    protected function updateCaisseSolde(Caisse $caisse, float $montant_verse)
    {
        $caisse->augmenterSolde($montant_verse);
        $caisse->save();
    }

    /**
     * Associate distrib_panetier with versement
     */
    protected function associateDistribPanetierWithVersement(DistribPanetier $distrib_panetier, Versement $versement): void
    {
        $distrib_panetier->versement()->associate($versement);
        $distrib_panetier->save();
    }
}