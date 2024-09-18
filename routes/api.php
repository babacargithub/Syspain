<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DistribPanetierController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\PanetierController;
use App\Http\Controllers\PetrisseurController;
use App\Http\Controllers\IntrantController;
use App\Http\Controllers\ProdPatisserieController;
use App\Http\Controllers\RecetteController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VersementController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Caisse;
use App\Models\Chariot;
use App\Models\Depense;
use App\Models\TypeDepense;
use App\Models\TypeRecette;
use Illuminate\Support\Facades\Route;
Route::get('production_petrisseur/{date}', [PetrisseurController::class, 'productionDuJour']);
Route::resource('petrisseurs', PetrisseurController::class);
Route::get('panetiers/date/{date}', [PanetierController::class, 'productionDuJour']);
Route::resource('panetiers', PanetierController::class)

    ->parameters([
        'panetiers' => 'productionPanetier',
        // customise the store route
    ]);
Route::get('distribution_panetiers/destinations', [DistribPanetierController::class, 'getEntitiesForDistrib']);
Route::get('distribution_panetiers/{productionPanetier}/destinations', [DistribPanetierController::class, 'getEntitiesForDistrib']);
Route::post('distribution_panetiers/{productionPanetier}', [DistribPanetierController::class, 'store'])->name('distrib-panetier');
Route::resource('distribution_panetiers', DistribPanetierController::class,[
    'only' => ['index','update','destroy','show']
])->parameters([
    'distribution_panetiers' => 'distribPanetier',
    // customise the store route
]);
Route::get('versements/livreurs', [VersementController::class, 'versementsLivreurs'])->name('versements.livreurs');
Route::get('versements/destinations', [VersementController::class, 'destinations'])->name('versements.destinations');
// versements d'une date
Route::get('versements/date/{date}', [VersementController::class, 'versementsDate'])->name('versements.date');

// ============= SECTION LIVREURS ====================
Route::get('/livreurs/{livreur}/historique', [LivreurController::class, 'historique']);
Route::get('/livreurs/{livreur}/distribution_panetiers', [LivreurController::class, 'getDistribPanetiersOfLivreurs']);

Route::put('livreurs/{livreur}/activate/{is_active}', [LivreurController::class, 'disable'])->name('livreurs.activate');
Route::resource('livreurs', LivreurController::class);
Route::resource('versements', VersementController::class);
Route::get('depenses/date/{date}', [DepenseController::class, 'depensesDate'])->name('depenses.date');
Route::resource('depenses', DepenseController::class);
Route::resource('intrants', IntrantController::class);
Route::post('stocks/entree',[StockController::class,'entreeStock']);
Route::post('stocks/sortie/{intrant}',[StockController::class,'sortieStock']);
Route::get('stocks/movements/{intrant}', [StockController::class, 'getMovements']);
Route::put('clients/{client}/toggle', [ClientController::class, 'toggle']);

Route::resource('clients', ClientController::class);

Route::get('recettes/date/{date}', [RecetteController::class, 'recettesJour']);
Route::resource('recettes', RecetteController::class);
Route::get('chariots',function (){
    return response()->json(Chariot::all()->map(function (Chariot $chariot){
        return [
            'id' => $chariot->id,
            'nom' => $chariot->nom,
            "nombre_pain" => $chariot->nombre_pain,
        ];
    }));

});
Route::get('types_depenses_recettes',function (){
    return response()->json([
        'type_depenses' => TypeDepense::all()->map(function (TypeDepense $depense){
            return [
                'id' => $depense->id,
                'nom' => $depense->nom,
            ];
        }),
        'type_recettes' => TypeRecette::all()->map(function (TypeRecette $recette){
            return [
                'id' => $recette->id,
                'nom' => $recette->nom,
            ];
        }),
    ]);
});
Route::resource('articles', ArticleController::class);
Route::delete('production_patisseries/delete_article/{articleProdPatisserie}', [ProdPatisserieController::class,
    'deleteArticle']);
Route::post('production_patisseries/{prodPatisserie}/encaisser', [ProdPatisserieController::class, 'encaisserProdPatisserie']);
Route::post('production_patisseries/{prod_patisserie}/articles', [ProdPatisserieController::class, 'storeArticles']);
Route::get('production_patisseries/{prod_patisserie}/articles', [ProdPatisserieController::class, 'getArticles']);
Route::get('production_patisseries/date/{date}', [ProdPatisserieController::class, 'getProdPatisserieByDate']);
Route::put('production_patisseries/{prodPatisserie}/articles', [ProdPatisserieController::class, 'updateArticles']);

Route::resource('production_patisseries', ProdPatisserieController::class)->parameters([
    'production_patisseries' => 'prodPatisserie',
]);

Route::get('caisse',function (){

    $caisse = Caisse::requireCaisseOfLoggedInUser();
    return response()->json(["solde" => $caisse->solde]);

});

// admin section routes with 'admin' prefix and protected by admin middleware
//TODO implement the admin middleware later
Route::prefix('admin')->group(function () {
    Route::get('boulangeries', [AdminController::class, 'boulangeries']);
    Route::get('boulangeries/{boulangerie}/dashboard', [AdminController::class, 'dashboard']);
})->middleware(AdminMiddleware::class);



