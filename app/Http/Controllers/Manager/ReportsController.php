<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Carbon;

class ReportsController extends Controller
{
    public function index()
    {
        $userId = session('auth_user_id');

        // Shipments per month (last 6 months)
        $monthlyShipments = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthlyShipments[] = [
                'month'     => $month->format('M Y'),
                'total'     => Shipment::where('user_id', $userId)
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'completed' => Shipment::where('user_id', $userId)
                    ->where('tracking_status', 'Completed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
                'delayed' => Shipment::where('user_id', $userId)
                    ->where('tracking_status', 'Delayed')
                    ->whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->count(),
            ];
        }

        // Risk distribution
        $riskDist = [
            'High'   => Shipment::where('user_id', $userId)->where('risk_level', 'High')->count(),
            'Medium' => Shipment::where('user_id', $userId)->where('risk_level', 'Medium')->count(),
            'Low'    => Shipment::where('user_id', $userId)->where('risk_level', 'Low')->count(),
        ];

        // Top suppliers by shipment count
        $topSuppliers = Supplier::withCount(['shipments' => fn($q) => $q->where('user_id', $userId)])
            ->having('shipments_count', '>', 0)
            ->orderByDesc('shipments_count')
            ->limit(5)
            ->get();

        // PO summary
        $poStats = [
            'total'     => PurchaseOrder::where('user_id', $userId)->count(),
            'draft'     => PurchaseOrder::where('user_id', $userId)->where('status', 'Draft')->count(),
            'approved'  => PurchaseOrder::where('user_id', $userId)->where('status', 'Approved')->count(),
            'completed' => PurchaseOrder::where('user_id', $userId)->where('status', 'Completed')->count(),
            'total_value' => PurchaseOrder::where('user_id', $userId)->sum('total_amount'),
        ];

        // Total shipping cost
        $totalCost = Shipment::where('user_id', $userId)->sum('shipping_cost');

        // Status breakdown
        $statusBreakdown = Shipment::where('user_id', $userId)
            ->selectRaw('tracking_status, count(*) as count')
            ->groupBy('tracking_status')
            ->get()
            ->pluck('count', 'tracking_status');

        return view('manager.reports.index', compact(
            'monthlyShipments', 'riskDist', 'topSuppliers',
            'poStats', 'totalCost', 'statusBreakdown'
        ));
    }
}
