<?php

use App\Http\Controllers\DistribPanetierController;
use App\Http\Controllers\PanetierController;
use App\Http\Controllers\PetrisseurController;
use Illuminate\Support\Facades\Route;
Route::get('production_petrisseur/{date}', [PetrisseurController::class, 'productionDuJour']);
Route::resource('petrisseurs', PetrisseurController::class);
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

