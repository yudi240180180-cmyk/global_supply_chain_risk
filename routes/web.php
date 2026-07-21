<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Health check endpoint for Railway
Route::get('/health', fn() => response('OK', 200));
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
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Manager\ShipmentPlannerController;
use App\Http\Controllers\Manager\ShipmentTrackingController;
use App\Http\Controllers\Manager\RouteRecommendationController;
use App\Http\Controllers\Manager\PurchaseOrderController;
use App\Http\Controllers\Manager\SupplierManagerController;
use App\Http\Controllers\Manager\CostEstimatorController;
use App\Http\Controllers\Manager\ReportsController;

// ─── Authentication Switcher ───────────────────────────────────────────────
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
Route::get('/logout', [AuthController::class, 'logout']); // fallback helper

// ─── Admin Portal (Protected) ──────────────────────────────────────────────
Route::middleware('role:admin')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('shipments', ShipmentController::class);

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

    // ─── Intelligence ──────────────────────────────────────────────────────────
    Route::get('/countries', [CountryPageController::class, 'index'])->name('countries.index');
    Route::get('/countries/{id}', [CountryPageController::class, 'show'])->name('countries.show');
    Route::get('/risk', [RiskPageController::class, 'index'])->name('risk.index');
    Route::get('/news', [NewsPageController::class, 'index'])->name('news.index');
    Route::get('/weather', [WeatherPageController::class, 'index'])->name('weather.index');
    Route::get('/currency', [CurrencyPageController::class, 'index'])->name('currency.index');
    Route::get('/ports', [PortsPageController::class, 'index'])->name('ports.index');
    Route::get('/ports/{id}', [PortsPageController::class, 'show'])->name('ports.show');

    // ─── Tools ─────────────────────────────────────────────────────────────────
    Route::get('/compare', [ComparePageController::class, 'index'])->name('compare.index');

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
});

// ─── Public Routes (No Auth Required) ─────────────────────────────────────────
Route::get('/watchlist', [WatchlistPageController::class, 'index'])->name('watchlist.index');

// ─── Import Manager Portal (Protected) ──────────────────────────────────────
Route::middleware('role:import_manager')->prefix('manager')->name('manager.')->group(function () {
    Route::get('/', [ManagerDashboardController::class, 'index'])->name('dashboard');
    
    // Shipment Planner
    Route::resource('shipments', ShipmentPlannerController::class);
    Route::get('shipments/{shipment}/track', [ShipmentTrackingController::class, 'show'])->name('shipments.track');
    Route::post('shipments/{shipment}/status', [ShipmentTrackingController::class, 'updateStatus'])->name('shipments.status');

    // Route Recommendation
    Route::get('routes', [RouteRecommendationController::class, 'index'])->name('routes.index');
    Route::post('routes/recommend', [RouteRecommendationController::class, 'recommend'])->name('routes.recommend');

    // Purchase Orders
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.status');

    // Suppliers
    Route::resource('suppliers', SupplierManagerController::class)->only(['index', 'show']);

    // Cost Estimator
    Route::get('cost-estimator', [CostEstimatorController::class, 'index'])->name('cost-estimator.index');
    Route::post('cost-estimator/calculate', [CostEstimatorController::class, 'calculate'])->name('cost-estimator.calculate');

    // Reports
    Route::get('reports', [ReportsController::class, 'index'])->name('reports.index');

    // Watchlist (Reuses existing page logic)
    Route::get('watchlist', [WatchlistPageController::class, 'index'])->name('watchlist.index');
});
