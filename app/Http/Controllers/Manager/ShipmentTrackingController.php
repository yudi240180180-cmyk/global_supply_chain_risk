<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentStatusLog;
use App\Services\ShipmentRiskService;
use Illuminate\Http\Request;

class ShipmentTrackingController extends Controller
{
    protected array $statusFlow = [
        'Planning'  => ['Ready'],
        'Ready'     => ['Loading'],
        'Loading'   => ['Departed'],
        'Departed'  => ['At Sea'],
        'At Sea'    => ['Arrived', 'Delayed'],
        'Delayed'   => ['At Sea', 'Arrived', 'Cancelled'],
        'Arrived'   => ['Completed'],
        'Completed' => [],
        'Cancelled' => [],
    ];

    public function __construct(protected ShipmentRiskService $riskService) {}

    public function show(Shipment $shipment)
    {
        if ($shipment->user_id !== session('auth_user_id')) {
            abort(403);
        }

        $shipment->load([
            'supplier', 'originPort.country', 'destinationPort.country',
            'statusLogs.loggedBy', 'recommendations',
        ]);

        $nextStatuses = $this->statusFlow[$shipment->tracking_status] ?? [];

        // Risk timeline
        $riskTimeline = $shipment->statusLogs()
            ->orderBy('logged_at')
            ->get()
            ->map(fn($log) => [
                'label' => $log->logged_at->format('d M'),
                'risk'  => $log->risk_at_log ?? 0,
                'status'=> $log->status,
            ]);

        return view('manager.shipments.track', compact('shipment', 'nextStatuses', 'riskTimeline'));
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        if ($shipment->user_id !== session('auth_user_id')) {
            abort(403);
        }

        $data = $request->validate([
            'tracking_status' => 'required|string',
            'notes'           => 'nullable|string|max:500',
        ]);

        $allowed = $this->statusFlow[$shipment->tracking_status] ?? [];
        if (! in_array($data['tracking_status'], $allowed)) {
            return back()->with('error', "Cannot transition from '{$shipment->tracking_status}' to '{$data['tracking_status']}'.");
        }

        // Recalculate risk
        $riskResult = $this->riskService->calculate($shipment);

        // Update timestamps based on status
        $updates = ['tracking_status' => $data['tracking_status']];
        if ($data['tracking_status'] === 'Departed')  $updates['actual_departure'] = now();
        if ($data['tracking_status'] === 'Arrived')   $updates['actual_arrival']   = now();

        $shipment->update(array_merge($updates, [
            'overall_risk_score'   => $riskResult['overall_risk_score'],
            'risk_level'           => $riskResult['risk_level'],
        ]));

        // Log the status change
        ShipmentStatusLog::create([
            'shipment_id' => $shipment->id,
            'status'      => $data['tracking_status'],
            'notes'       => $data['notes'] ?? 'Status updated.',
            'risk_at_log' => $riskResult['overall_risk_score'],
            'logged_by'   => session('auth_user_id'),
            'logged_at'   => now(),
        ]);

        return back()->with('success', "Status updated to {$data['tracking_status']}.");
    }
}
