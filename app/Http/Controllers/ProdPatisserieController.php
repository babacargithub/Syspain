<?php

namespace App\Http\Controllers;

use App\Models\ProdPatisserie;
use App\Models\ArticleProdPatisserie;
use App\Models\Boulangerie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProdPatisserieController extends Controller
{
    /**
     *
     * @return JsonResponse
     */
    public function index()
    {
        $prodPatisseries = ProdPatisserie::where('boulangerie_id', Boulangerie::requireBoulangerieOfLoggedInUser()->id)->get();
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
            'date_production' => 'required|date',
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
            'date_production' => 'required|date',
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
        $prodPatisserie->delete();
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
                'article_nom' => $articleProdPatisserie->article->nom,
            ];
        });

        return response()->json($articles);
    }
}
