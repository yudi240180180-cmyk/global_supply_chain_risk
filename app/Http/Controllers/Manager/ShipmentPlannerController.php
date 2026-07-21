<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\Port;
use App\Models\ShipmentStatusLog;
use App\Services\ShipmentRiskService;
use App\Services\ShipmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShipmentPlannerController extends Controller
{
    public function __construct(
        protected ShipmentRiskService $riskService,
        protected ShipmentService     $shipmentService
    ) {}

    public function index()
    {
        $userId    = session('auth_user_id');
        $shipments = Shipment::with(['supplier', 'originPort.country', 'destinationPort.country'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);

        return view('manager.shipments.index', compact('shipments'));
    }

    public function create()
    {
        $userId    = session('auth_user_id');
        $suppliers = Supplier::orderBy('company_name')->get();
        $ports     = Port::with('country')->orderBy('name')->get();

        return view('manager.shipments.create', compact('suppliers', 'ports'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,id',
            'origin_port_id'       => 'required|exists:ports,id',
            'destination_port_id'  => 'required|exists:ports,id',
            'cargo_name'           => 'required|string|max:255',
            'cargo_weight'         => 'required|numeric|min:0.1',
            'container_count'      => 'required|integer|min:1',
            'container_type'       => 'required|in:20FT,40FT,40HC',
            'quantity'             => 'required|integer|min:1',
            'estimated_departure'  => 'nullable|date',
            'estimated_arrival'    => 'nullable|date|after_or_equal:estimated_departure',
        ]);

        $origin      = Port::findOrFail($data['origin_port_id']);
        $destination = Port::findOrFail($data['destination_port_id']);

        // Hitung jarak & biaya
        $calc = $this->shipmentService->calculate($origin, $destination, $data['container_count']);

        // Generate shipment code
        $code = 'SC-' . now()->format('Ymd') . '-' . str_pad(
            Shipment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
        );

        $userId = session('auth_user_id');

        $shipment = Shipment::create([
            'shipment_code'       => $code,
            'user_id'             => $userId,
            'supplier_id'         => $data['supplier_id'],
            'origin_port_id'      => $data['origin_port_id'],
            'destination_port_id' => $data['destination_port_id'],
            'cargo_name'          => $data['cargo_name'],
            'cargo_weight'        => $data['cargo_weight'],
            'container_count'     => $data['container_count'],
            'container_type'      => $data['container_type'],
            'quantity'            => $data['quantity'],
            'estimated_departure' => $data['estimated_departure'] ?? null,
            'estimated_arrival'   => $data['estimated_arrival'] ?? null,
            'distance_km'         => $calc['distance_km'],
            'estimated_days'      => $calc['estimated_days'],
            'shipping_cost'       => $calc['shipping_cost'],
            'status'              => 'Planning',
            'tracking_status'     => 'Planning',
        ]);

        // Hitung risk score otomatis
        $riskResult = $this->riskService->calculate($shipment);
        $this->riskService->saveToShipment($shipment, $riskResult);

        // Log status awal
        ShipmentStatusLog::create([
            'shipment_id' => $shipment->id,
            'status'      => 'Planning',
            'notes'       => 'Shipment created and risk assessment completed.',
            'risk_at_log' => $riskResult['overall_risk_score'],
            'logged_by'   => $userId,
            'logged_at'   => now(),
        ]);

        return redirect()
            ->route('manager.shipments.show', $shipment)
            ->with('success', "Shipment {$code} created. Risk assessed: {$riskResult['risk_level']}.");
    }

    public function show(Shipment $shipment)
    {
        // Pastikan ini milik user yang login
        if ($shipment->user_id !== session('auth_user_id')) {
            abort(403);
        }

        $shipment->load([
            'supplier.country',
            'originPort.country',
            'destinationPort.country',
            'recommendations',
            'statusLogs.loggedBy',
            'shippingCost',
            'items',
        ]);

        // Risk timeline untuk chart (ambil dari status logs)
        $riskTimeline = $shipment->statusLogs()
            ->orderBy('logged_at')
            ->get()
            ->map(fn($log) => [
                'date'  => $log->logged_at->format('d M H:i'),
                'risk'  => $log->risk_at_log ?? 0,
                'label' => $log->status,
            ]);

        return view('manager.shipments.show', compact('shipment', 'riskTimeline'));
    }
}
