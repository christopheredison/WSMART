<?php

use App\Http\Controllers\RiskOfficer\RiskRegisterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LossEventProjectImportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/check-deskripsi-peristiwa', [RiskRegisterController::class, 'checkDeskripsiPeristiwa'])->name('check.deskripsi.peristiwa');

Route::post('loss-event-projects/import', LossEventProjectImportController::class);
