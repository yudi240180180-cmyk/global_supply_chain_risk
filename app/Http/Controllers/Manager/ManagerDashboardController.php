<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\ExchangeRateHistory;
use Illuminate\Support\Carbon;

class ManagerDashboardController extends Controller
{
    public function index()
    {
        $userId = session('auth_user_id');

        // Shipment stats (user's own shipments)
        $shipments = Shipment::where('user_id', $userId);

        $stats = [
            'total'     => (clone $shipments)->count(),
            'ongoing'   => (clone $shipments)->whereIn('tracking_status', ['Ready','Loading','Departed','At Sea'])->count(),
            'completed' => (clone $shipments)->where('tracking_status', 'Completed')->count(),
            'delayed'   => (clone $shipments)->where('tracking_status', 'Delayed')->count(),
            'high_risk' => (clone $shipments)->where('risk_level', 'High')->count(),
            'planning'  => (clone $shipments)->where('tracking_status', 'Planning')->count(),
        ];

        // Upcoming ETA (next 30 days)
        $upcomingShipments = Shipment::with(['originPort.country', 'destinationPort.country', 'supplier'])
            ->where('user_id', $userId)
            ->whereIn('tracking_status', ['At Sea', 'Departed', 'Loading'])
            ->whereNotNull('estimated_arrival')
            ->where('estimated_arrival', '>=', now())
            ->orderBy('estimated_arrival')
            ->limit(5)
            ->get();

        // High risk shipments
        $highRiskShipments = Shipment::with(['originPort.country', 'destinationPort.country', 'supplier'])
            ->where('user_id', $userId)
            ->where('risk_level', 'High')
            ->whereNotIn('tracking_status', ['Completed', 'Cancelled'])
            ->latest()
            ->limit(5)
            ->get();

        // Recent shipments
        $recentShipments = Shipment::with(['originPort.country', 'destinationPort.country', 'supplier'])
            ->where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        // Recent POs
        $recentPOs = PurchaseOrder::with('supplier')
            ->where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        // Currency alerts (top 5 currencies with rates)
        $currencyRates = ExchangeRateHistory::select('currency_code')
            ->selectRaw('MAX(rate_to_usd) as rate')
            ->groupBy('currency_code')
            ->limit(6)
            ->get();

        // Shipment chart data (last 6 months)
        $chartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $chartData[] = [
                'month' => $month->format('M'),
                'count' => Shipment::where('user_id', $userId)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        return view('manager.dashboard', compact(
            'stats',
            'upcomingShipments',
            'highRiskShipments',
            'recentShipments',
            'recentPOs',
            'currencyRates',
            'chartData'
        ));
    }
}
