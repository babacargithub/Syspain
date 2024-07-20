<?php

use App\Http\Controllers\DepenseController;
use App\Http\Controllers\DistribPanetierController;
use App\Http\Controllers\LivreurController;
use App\Http\Controllers\PanetierController;
use App\Http\Controllers\PetrisseurController;
use App\Http\Controllers\IntrantController;
use App\Http\Controllers\RecetteController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\VersementController;
use Illuminate\Support\Facades\Route;
Route::get('production_petrisseur/{date}', [PetrisseurController::class, 'productionDuJour']);
Route::resource('petrisseurs', PetrisseurController::class);
Route::get('panetiers/date/{date}', [PanetierController::class, 'productionDuJour']);
Route::resource('panetiers', PanetierController::class)

    ->parameters([
        'panetiers' => 'productionPanetier',
        // customise the store route
    ]);;
Route::post('distribution_panetiers/{productionPanetier}', [DistribPanetierController::class, 'store'])->name('distrib-panetier');
Route::resource('distribution_panetiers', DistribPanetierController::class,[
    'only' => ['index','update','destroy','show']
])->parameters([
    'distribution_panetiers' => 'distribPanetier',
    // customise the store route
]);
Route::get('versements/livreurs', [VersementController::class, 'versementsLivreurs'])->name('versements.livreurs');
// versements d'une date
Route::get('versements/date/{date}', [VersementController::class, 'versementsDate'])->name('versements.date');
Route::put('livreurs/{livreur}/activate/{is_active}', [LivreurController::class, 'disable'])->name('livreurs.activate');
Route::resource('livreurs', LivreurController::class);
Route::resource('versements', VersementController::class);
Route::get('depenses/date/{date}', [DepenseController::class, 'depensesDate'])->name('depenses.date');
Route::resource('depenses', DepenseController::class);
Route::resource('intrants', IntrantController::class);
Route::post('stocks/entree',[StockController::class,'entreeStock']);
Route::post('stocks/sortie/{intrant}',[StockController::class,'sortieStock']);
Route::get('recettes/date/{date}', [RecetteController::class, 'recettesJour']);
Route::resource('recettes', RecetteController::class);


