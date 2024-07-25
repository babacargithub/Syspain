<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Boulangerie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ArticleController extends Controller
{
    /**
     * Display a listing of the articles.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $articles = Article::where('boulangerie_id', Boulangerie::requireBoulangerieOfLoggedInUser()->id)->get();
        return response()->json($articles);
    }

    /**
     * Store a newly created article in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nom' => 'required|string|max:255',
            'prix' => 'required|integer',
        ]);

        $article = new Article($validatedData);
        $article->boulangerie_id = Boulangerie::requireBoulangerieOfLoggedInUser()->id;
        $article->save();

        return response()->json($article, 201);
    }

    /**
     * Display the specified article.
     *
     * @param Article $article
     * @return JsonResponse
     */
    public function show(Article $article)
    {
        return response()->json($article);
    }

    /**
     * Update the specified article in storage.
     *
     * @param Request $request
     * @param Article $article
     * @return JsonResponse
     */
    public function update(Request $request, Article $article)
    {
        $validatedData = $request->validate([
            'nom' => 'string|max:255',
            'prix' => 'integer',
        ]);

        $article->update($validatedData);

        return response()->json($article);
    }

    /**
     * Remove the specified article from storage.
     *
     * @param Article $article
     * @return JsonResponse
     */
    public function destroy(Article $article)
    {
        $article->delete();

        return response()->json(null, 204);
    }
}
