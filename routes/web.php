<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\RiskController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\WeatherController;
use App\Http\Controllers\Api\EconomicsController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Web\CountryPageController;
use App\Http\Controllers\Web\RiskPageController;
use App\Http\Controllers\Web\NewsPageController;
use App\Http\Controllers\Web\WeatherPageController;
use App\Http\Controllers\Web\CurrencyPageController;
use App\Http\Controllers\Web\PortsPageController;
use App\Http\Controllers\Web\ComparePageController;
use App\Http\Controllers\Web\WatchlistPageController;
use App\Http\Controllers\Web\AdminController;

// ─── Main Dashboard ─────────────────────────────────────────────────────────
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// ─── Sync ───────────────────────────────────────────────────────────────────
Route::post('/sync-data', function () {
    try {
        Artisan::call('migrate', ['--force' => true, '--no-interaction' => true]);
        Artisan::call('sync:all', ['--skip-external' => true, '--no-interaction' => true]);
        return redirect('/')->with('sync_status', 'Sync complete. Check dashboard for latest metrics.');
    } catch (\Throwable $e) {
        return redirect('/')->with('sync_status', 'Sync failed: ' . $e->getMessage());
    }
})->name('sync.data');

// ─── Countries ──────────────────────────────────────────────────────────────
Route::get('/countries', [CountryPageController::class, 'index'])->name('countries.index');
Route::get('/countries/{id}', [CountryPageController::class, 'show'])->name('countries.show');

// ─── Risk Scoring ───────────────────────────────────────────────────────────
Route::get('/risk', [RiskPageController::class, 'index'])->name('risk.index');

// ─── News Intelligence ──────────────────────────────────────────────────────
Route::get('/news', [NewsPageController::class, 'index'])->name('news.index');

// ─── Weather Monitoring ─────────────────────────────────────────────────────
Route::get('/weather', [WeatherPageController::class, 'index'])->name('weather.index');

// ─── Currency / Exchange Rates ──────────────────────────────────────────────
Route::get('/currency', [CurrencyPageController::class, 'index'])->name('currency.index');

// ─── Port Location Dashboard ────────────────────────────────────────────────
Route::get('/ports', [PortsPageController::class, 'index'])->name('ports.index');
Route::get('/ports/{id}', [PortsPageController::class, 'show'])->name('ports.show');

// ─── Country Comparison ─────────────────────────────────────────────────────
Route::get('/compare', [ComparePageController::class, 'index'])->name('compare.index');

// ─── Watchlist ──────────────────────────────────────────────────────────────
Route::get('/watchlist', [WatchlistPageController::class, 'index'])->name('watchlist.index');

// ─── Admin Dashboard ────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{id}/role', [AdminController::class, 'updateRole'])->name('users.role');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::get('/ports', [AdminController::class, 'ports'])->name('ports');
    Route::delete('/ports/{id}', [AdminController::class, 'destroyPort'])->name('ports.destroy');
    Route::get('/articles', [AdminController::class, 'articles'])->name('articles');
    Route::post('/articles', [AdminController::class, 'storeArticle'])->name('articles.store');
    Route::delete('/articles/{id}', [AdminController::class, 'destroyArticle'])->name('articles.destroy');
    Route::get('/risk-weights', [AdminController::class, 'riskWeights'])->name('risk-weights');
    Route::post('/risk-weights', [AdminController::class, 'updateRiskWeights'])->name('risk-weights.update');
});
