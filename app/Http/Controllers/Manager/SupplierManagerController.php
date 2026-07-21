<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Supplier;

class SupplierManagerController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::with(['country', 'shipments'])
            ->withCount('shipments')
            ->orderBy('company_name')
            ->paginate(15);

        return view('manager.suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->load(['country', 'port']);

        $shipments = $supplier->shipments()
            ->with(['originPort.country', 'destinationPort.country'])
            ->latest()
            ->limit(10)
            ->get();

        $stats = [
            'total'     => $supplier->shipments()->count(),
            'completed' => $supplier->shipments()->where('tracking_status', 'Completed')->count(),
            'ongoing'   => $supplier->shipments()->whereIn('tracking_status', ['Ready','Loading','Departed','At Sea'])->count(),
            'delayed'   => $supplier->shipments()->where('tracking_status', 'Delayed')->count(),
            'avg_risk'  => round($supplier->shipments()->avg('overall_risk_score') ?? 0, 1),
        ];

        return view('manager.suppliers.show', compact('supplier', 'shipments', 'stats'));
    }
}
