<?php

use App\Http\Controllers\CreditApplicationController;
use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Credit Application Routes
Route::post('/credits', [CreditApplicationController::class, 'store']);
Route::get('/credits/{id}', [CreditApplicationController::class, 'getCreditStatus']);
Route::get('/credits/{id}/installments', [CreditApplicationController::class, 'indexInstallments']);

// Client Routes
Route::get('/clients', [ClientController::class, 'index']);
