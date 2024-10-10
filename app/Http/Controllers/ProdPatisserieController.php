<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Caisse;
use App\Models\ProdPatisserie;
use App\Models\ArticleProdPatisserie;
use App\Models\Boulangerie;
use App\Models\Recette;
use App\Models\TypeRecette;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ProdPatisserieController extends Controller
{
    /**
     *
     * @return JsonResponse
     */
    public function index()
    {
        $prodPatisseries = ProdPatisserie::orderByDesc('date_production')
            ->get();
        return response()->json($prodPatisseries);
    }
    // get prod patisserie of a specific date
    public function getProdPatisserieByDate(Request $request, $date)
    {
        $prodPatisseries = ProdPatisserie::whereDate('date_production', '<=',$date)
            ->orderByDesc('date_production')
            ->limit(31)
            ->get()->map(function ($prodPatisserie) {
                return [
                    'id' => $prodPatisserie->id,
                    'periode' => $prodPatisserie->periode,
                    'date_production' => $prodPatisserie->date_production,
                    'verse' => (bool)$prodPatisserie->verse,
                    'montant_a_verser' => $prodPatisserie->montant_a_verser,
                    'restant_transfere' => (bool)$prodPatisserie->restant_transfere,
                    'nombre_a_verser' => $prodPatisserie->nombre_a_verser,
                    'articles' => [],
                ];
            });
        return response()->json($prodPatisseries);
    }

    /**
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(Request $request)
    {

        $validatedData = $request->validate([
            'date_production' => [
                'required',
                'date',
                Rule::unique('prod_patisseries')
                    ->where(function ($query) {
                        return $query
//                            ->where('periode', request('periode'))
                            ->where('date_production', request('date_production'));
                    }) ],
        ], [
            'date_production.unique' => 'Cette date est déjà enregistrée',
        ]);

        $prodPatisserie = new ProdPatisserie($validatedData);
        $prodPatisserie->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        $prodPatisserie->periode ='matin';

        $prodPatisserieSoir = clone $prodPatisserie;
        $prodPatisserieSoir->periode = 'soir';
        $prodPatisserie->save();
        $prodPatisserieSoir->save();



        return response()->json($prodPatisserie, 201);
    }

    /**
     *
     * @param ProdPatisserie $prodPatisserie
     * @return JsonResponse
     */
    public function show(ProdPatisserie $prodPatisserie)
    {
        $prodPatisserie->load('articleProdPatisseries.article');
        return response()->json($prodPatisserie);
    }

    /**
     * Update the specified prod patisserie in storage.
     *
     * @param Request $request
     * @param ProdPatisserie $prodPatisserie
     * @return JsonResponse
     */
    public function update(Request $request, ProdPatisserie $prodPatisserie)
    {
        $validatedData = $request->validate([
            'periode' => 'in:matin,soir',
            'date_production' => [
                'date',
                Rule::unique('prod_patisseries')
                    ->where(function ($query) {
                        return $query
                            ->where('periode', request('periode'))
                            ->where('date_production', request('date_production'));
                    })->ignore($prodPatisserie->id)],
        ]);

        $prodPatisserie->update($validatedData);

        return response()->json($prodPatisserie);
    }

    /**
     *
     * @param ProdPatisserie $prodPatisserie
     * @return JsonResponse
     */
    public function destroy(ProdPatisserie $prodPatisserie)
    {
        $prodPatisserie->forceDelete();
        return response()->json(null, 204);
    }

    /**
     *
     * @param Request $request
     * @param ProdPatisserie $prodPatisserie
     * @return JsonResponse
     */
    public function storeArticles(Request $request, ProdPatisserie $prodPatisserie)
    {
        $validatedData = $request->validate([
            'articles' => 'required|array',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.restant' => 'integer',
            'articles.*.retour' => 'integer',
            'articles.*.quantite' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validatedData, $prodPatisserie) {
            foreach ($validatedData['articles'] as $articleData) {
                $articleProdPatisserie = new ArticleProdPatisserie($articleData);
                $articleProdPatisserie->prod_patisserie_id = $prodPatisserie->id;
                $articleProdPatisserie->save();
            }
        });

        return response()->json(['message' => 'Articles added successfully'], 201);
    }

    /**
     *
     * @param ProdPatisserie $prodPatisserie
     * @return JsonResponse
     */
    public function getArticles(ProdPatisserie $prodPatisserie)
    {
        $articles = $prodPatisserie->articles->map(function ($articleProdPatisserie) {
            return [
                'id' => $articleProdPatisserie->id,
                'article_id' => $articleProdPatisserie->article_id,
                'quantite' => $articleProdPatisserie->quantite,
                'restant' => $articleProdPatisserie->restant,
                'retour' => $articleProdPatisserie->retour,
                'article_prix' => $articleProdPatisserie->article->prix,
                'article_nom' => $articleProdPatisserie->article->nom,
            ];
        });

        return response()->json($articles);
    }
    //// ======////-======

    /**
     * Update articles for the specified ProdPatisserie.
     *
     * @param Request $request
     * @param ProdPatisserie $prodPatisserie
     * @return JsonResponse
     */
    public function updateArticles(Request $request, ProdPatisserie $prodPatisserie)
    {
        $validatedData = $request->validate([
            'articles' => 'required|array',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite' => 'required|integer|min:1',
            'articles.*.retour' => 'nullable|integer|min:0',
            'articles.*.restant' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validatedData, $prodPatisserie) {
            foreach ($validatedData['articles'] as $articleData) {
                $articleProdPatisserie = ArticleProdPatisserie::updateOrCreate(
                    [
                        'prod_patisserie_id' => $prodPatisserie->id,
                        'article_id' => $articleData['article_id']
                    ],
                    [
                        'quantite' => $articleData['quantite'],
                        'retour' => $articleData['retour'] ?? 0,
                        'restant' => $articleData['restant'] ?? 0,
                    ]
                );
            }
        });

        return response()->json(['message' => 'Articles updated successfully'], 200);
    }


////-======

    public function deleteArticle(ArticleProdPatisserie $articleProdPatisserie){
        $articleProdPatisserie->delete();
        return response()->json(null, 204);
    }

    public function encaisserProdPatisserie(Request $request, ProdPatisserie $prodPatisserie)
    {
        $data = $request->validate([
            'montant' => 'required|numeric',
            'articles'=>'required|array',
            // articles should have id, retour and restant attributes required
            'articles.*.id' => 'required|exists:article_prod_patisseries,id',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.retour' => 'required|integer|min:0',
            'articles.*.restant' => 'required|integer|min:0',
        ]);

        // create recettes
        DB::transaction(function () use ($data, $prodPatisserie) {
            // update articles first
            foreach ($data['articles'] as $articleData) {
                $articleProdPatisserie = ArticleProdPatisserie::findOrFail($articleData['id']);
                $articleProdPatisserie->update([
                    'retour' => $articleData['retour'],
                    'restant' => $articleData['restant'],
                ]);
            }

            $recette = new Recette();
            $recette->montant = $data['montant'];
            $recette->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;

            $typeRecette = TypeRecette::ofCurrentBoulangerie()
                ->where('constant_name',TypeRecette::VENTE_PATISSERIE)
               ->firstOrFail();
            $recette->typeRecette()->associate($typeRecette);
            Carbon::setLocale('fr');

// Create a Carbon instance for the desired date
            $date_formatted = Carbon::parse($prodPatisserie->date_production);

// Format the date to the desired format
            $formattedDate = $date_formatted->translatedFormat('l j F Y');
            $recette->commentaire = "Encaissement de la production patisserie du " . $formattedDate." du " .
                $prodPatisserie->periode;
            $caisse = Caisse::requireCaisseOfLoggedInUser();
            $recette->caisse()->associate($caisse);
            $recette->save();
            $caisse->augmenterSolde($data['montant'],$prodPatisserie->toArray());
            $prodPatisserie->verse = true;
            $prodPatisserie->save();
        });
        return response()->json(['message' => 'Encaissement effectué avec succès'], 200);


    }

    public function transfer(ProdPatisserie $prodPatisserie, Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->getTransferData($prodPatisserie);
        }

        if ($request->isMethod('post')) {
            return $this->postTransferData($prodPatisserie,$request);
        }

        return response()->json(['message' => 'Method not allowed'], 405);
    }

    /**
     * Get the nearest ProdPatisserie and articles with retour > 0.
     *
     * @param int $id
     * @return JsonResponse
     */
    protected function getTransferData(ProdPatisserie $currentProdPatisserie)
    {
        // Find the current ProdPatisserie

        // Fetch the nearest ProdPatisserie (add logic to determine the nearest one)
        $nearestProdPatisserie = ProdPatisserie::where('date_production', '>=', $currentProdPatisserie->date_production)
            ->where('id', '>', $currentProdPatisserie->id)
            ->orderBy('date_production')
            ->first();
        if ($nearestProdPatisserie != null) {
            // we check if the periods are different and if the nearest prod patisserie is in the soir period
            if ($nearestProdPatisserie->date_production == $currentProdPatisserie->date_production) {
                if ($nearestProdPatisserie->periode == 'matin' && $currentProdPatisserie->periode == 'soir') {
                    $nearestProdPatisserie = null;
                }
            }
        }

        // Fetch articles with retour > 0
        $articles = $currentProdPatisserie->articles()
            ->where('restant', '>', 0)
            ->get()->map(function ($articleProdPatisserie) {
                return [
                    'id' => $articleProdPatisserie->id,
                    'article_id' => $articleProdPatisserie->article_id,
                    'quantite' => $articleProdPatisserie->quantite,
                    'retour' => $articleProdPatisserie->retour,
                    'restant' => $articleProdPatisserie->restant,
                    'article_prix' => $articleProdPatisserie->article->prix,
                    'article_nom' => $articleProdPatisserie->article->nom,
                ];
            });

        return response()->json([
            'nearestProdPatisserie' => $nearestProdPatisserie,
            'articles' => $articles,
        ]);
    }

    /**
     * Handle the transfer of articles to the nearest ProdPatisserie.
     *
     * @param Request $request
     * @return JsonResponse
     */
    protected function postTransferData(ProdPatisserie $source, Request
$request)
    {
        $validated = $request->validate([
            'articles' => 'required|array',
            'articles.*.article_id' => 'required|exists:article_prod_patisseries,id',
            'destination_prod_patisserie_id' => 'required|exists:prod_patisseries,id',
        ]);
        // if already transferred it should fail validation
        if ($source->restant_transfere) {
            return response()->json(['message' => 'Articles déjà transféré'], 422);
        }

            $articlesToTransfer = $source->articles()
                ->whereIn('id', array_map(function ($article) {
                    return $article['article_id'];
                }, $validated['articles']))
                ->get();

            $destination = ProdPatisserie::findOrFail($validated['destination_prod_patisserie_id']);

            DB::transaction(function () use ($articlesToTransfer, $destination, $source) {
                foreach ($articlesToTransfer as $article) {
                    $destination->articles()->create(
                        [   'article_id' => $article->article_id,
                            'quantite' => $article->restant,
                            'retour' => 0,
                            'restant' => 0,
                        ]
                    );
                }
                $source->restant_transfere = true;
                $source->save();
            });



            // Here you could create a record that logs the transfer or perform any additional logic


        return response()->json(['message' => 'Articles transferred successfully']);
    }



}
