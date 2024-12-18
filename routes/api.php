<?php

use App\Http\Controllers\AbonnementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\CaisseController;
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
use App\Http\Controllers\TypeDepenseController;
use App\Http\Controllers\TypeRecetteController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VersementBanqueController;
use App\Http\Controllers\VersementController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Caisse;
use App\Models\Chariot;
use App\Models\Company;
use App\Models\TypeDepense;
use App\Models\TypeRecette;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// login sections

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::post('/login', [UserController::class, 'handleLoginAttempt']);





// Protect routes with sanctum middleware

Route::middleware('auth:sanctum')->group(function () {
   Route::prefix('')->group(function (){
       Route::get('production_petrisseur/{date}', [PetrisseurController::class, 'productionDuJour']);
       Route::resource('petrisseurs', PetrisseurController::class);
       Route::get('panetiers/date/{date}', [PanetierController::class, 'productionDuJour']);
       Route::resource('panetiers', PanetierController::class)
           ->parameters(['panetiers' => 'productionPanetier']);
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

       //================= VERSEMENTS BANQUES
       Route::resource('versements_banques', VersementBanqueController::class)
           ->only(['index', 'store', 'destroy']);
// ============= SECTION LIVREURS ====================
       Route::get('/livreurs/{livreur}/historique', [LivreurController::class, 'historique']);
       Route::get('/livreurs/{livreur}/distribution_panetiers', [LivreurController::class, 'getDistribPanetiersOfLivreurs']);
       Route::get('/distribution_panetiers/get_list_for_versements/{entity_type}/{entity_id}', [DistribPanetierController::class,
           'getDistribPanetiersOfVersement']);

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
           return response()->json(Chariot::ofCurrentBoulangerie()->get()->map(function (Chariot $chariot){
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

// ================== SECTION PRODUCTION PATISSERIE ====================
       Route::post('production_patisseries/{prodPatisserie}/encaisser', [ProdPatisserieController::class, 'encaisserProdPatisserie']);
       Route::post('production_patisseries/{prod_patisserie}/articles', [ProdPatisserieController::class, 'storeArticles']);
       Route::get('production_patisseries/{prod_patisserie}/articles', [ProdPatisserieController::class, 'getArticles']);
       Route::get('production_patisseries/date/{date}', [ProdPatisserieController::class, 'getProdPatisserieByDate']);
       Route::put('production_patisseries/{prodPatisserie}/articles', [ProdPatisserieController::class, 'updateArticles']);

       Route::resource('production_patisseries', ProdPatisserieController::class)->parameters([
           'production_patisseries' => 'prodPatisserie',
       ]);
// transfert route should accept both get and post requests
       Route::match(['get', 'post'], 'production_patisseries/{prodPatisserie}/transfer', [ProdPatisserieController::class, 'transfer']);

// ================== END SECTION PRODUCTION PATISSERIE ====================

       Route::get('caisses/{dateStart}/{dateEnd?}', [CaisseController::class, 'caisseDate']);
       Route::get('caisse',function (){
           $caisse = Caisse::requireCaisseOfLoggedInUser();
           return response()->json(["solde" => $caisse->solde]);
       });
//    ============= ABONNEMENT ===============
       Route::post('abonnements', [AbonnementController::class, 'store']);

       // Resources of type depenses and type recettes
         Route::resource('type_depenses', TypeDepenseController::class);
         Route::resource('type_recettes', TypeRecetteController::class);

   })
   ->withoutMiddleware(AdminMiddleware::class);
//==========  ADMIN SECTION =============
// admin section routes with 'admin' prefix and protected by admin middleware
    Route::prefix('admin')->group(function () {
        Route::get('boulangeries', [AdminController::class, 'boulangeries']);
        Route::post('boulangeries/change_active', [AdminController::class, 'changeActiveBoulangerie']);
        Route::get('boulangeries/{boulangerie}/dashboard/{date}', [AdminController::class, 'dashboard']);
        Route::get('users_and_boulangeries', [AdminController::class, 'getUsersAndBoulangeries']);
        Route::get('boulangeries/{boulangerie}',[AdminController::class,'getBoulangerieData']);
        Route::put('boulangeries/{boulangerie}/update',[AdminController::class,'updateBoulangerieData']);
        Route::post('users/create', [AdminController::class, 'createUser']);
        Route::post('users/{user}/update', [AdminController::class, 'updateUser']);
        Route::put('users/{user}/toggle', [AdminController::class, 'toggleUserBanState']);
        Route::delete('chariots/{chariot}/delete', [AdminController::class, 'deleteChariot']);
    })->middleware(AdminMiddleware::class);



});