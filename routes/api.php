<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelController;
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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['jwt'])->group(function () {
    Route::get('user-loggued', [AuthController::class, 'userLoggued']);
    Route::get('/countries', [TravelController::class, 'getCountries']);
    Route::get('/cities/{countryId}', [TravelController::class, 'getCitiesByCountry']);
    Route::post('/convert-currency', [TravelController::class, 'convertCurrency']);
    Route::get('/weather/{city}', [TravelController::class, 'getWeather']);
    Route::get('/history', [TravelController::class, 'getHistory']);
    Route::post('/history', [TravelController::class, 'storeHistory']);
});


