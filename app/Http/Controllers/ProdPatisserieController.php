<?php

namespace App\Http\Controllers;

use App\Models\ProdPatisserie;
use App\Models\ArticleProdPatisserie;
use App\Models\Boulangerie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    /**
     *
     * @param Request $request
     * @return JsonResponse
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
                            ->where('periode', request('periode'))
                            ->where('date_production', request('date_production'));
                    }) ],

            'periode' => 'in:matin,soir'
        ], [
            'date_production.unique' => 'On a déjà une production pour cette date et cette période'
        ]);

        $prodPatisserie = new ProdPatisserie($validatedData);
        $prodPatisserie->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        $prodPatisserie->save();

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

}
