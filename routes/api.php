<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\RiskController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\EconomicsController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CompareController;
use App\Http\Controllers\Api\AdminApiController;

// ─── Auth ────────────────────────────────────────────────────────────────────
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ─── Dashboard ───────────────────────────────────────────────────────────────
Route::get('/dashboard', [DashboardController::class, 'apiSummary']);

// ─── Countries ───────────────────────────────────────────────────────────────
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/countries/{id}', [CountryController::class, 'show']);

// ─── Risk ────────────────────────────────────────────────────────────────────
Route::get('/risk', [RiskController::class, 'index']);
Route::get('/risk/summary', [RiskController::class, 'summary']);
Route::get('/risk/{countryId}', [RiskController::class, 'show']);

// ─── News ────────────────────────────────────────────────────────────────────
Route::get('/news', [NewsController::class, 'index']);
Route::get('/news/summary', [NewsController::class, 'summary']);
Route::get('/news/{id}', [NewsController::class, 'show']);

// ─── Weather ─────────────────────────────────────────────────────────────────
Route::get('/weather', [WeatherController::class, 'index']);
Route::get('/weather/{countryCode}', [WeatherController::class, 'showCountryWeather']);

// ─── Economics ───────────────────────────────────────────────────────────────
Route::get('/economics', [EconomicsController::class, 'index']);
Route::get('/economics/{countryCode}', [EconomicsController::class, 'showCountryEconomics']);

// ─── Currency / Exchange Rates ───────────────────────────────────────────────
Route::get('/currency', [CurrencyController::class, 'index']);
Route::get('/currency/{currencyCode}', [CurrencyController::class, 'show']);
Route::get('/currency/{currencyCode}/history', [CurrencyController::class, 'history']);

// ─── Ports ───────────────────────────────────────────────────────────────────
Route::get('/ports', [PortController::class, 'index']);
Route::get('/ports/nearest', [PortController::class, 'nearest']);
Route::get('/ports/{id}', [PortController::class, 'show']);

// ─── Country Comparison ──────────────────────────────────────────────────────
Route::get('/compare', [CompareController::class, 'compare']);

// ─── Protected Routes ────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    // Watchlist
    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{id}', [WatchlistController::class, 'destroy']);

    // Admin API
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/users', [AdminApiController::class, 'users']);
        Route::patch('/users/{id}/role', [AdminApiController::class, 'updateRole']);
        Route::delete('/users/{id}', [AdminApiController::class, 'destroyUser']);
        Route::get('/stats', [AdminApiController::class, 'stats']);
    });
});
