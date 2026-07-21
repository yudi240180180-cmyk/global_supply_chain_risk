<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Shipment;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $userId = session('auth_user_id');
        $orders = PurchaseOrder::with(['supplier', 'shipment'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);

        return view('manager.purchase-orders.index', compact('orders'));
    }

    public function create()
    {
        $userId    = session('auth_user_id');
        $suppliers = Supplier::where('status', 'Active')->orderBy('company_name')->get();
        $shipments = Shipment::where('user_id', $userId)
            ->whereIn('tracking_status', ['Planning', 'Ready', 'Loading'])
            ->orderBy('shipment_code')
            ->get();

        return view('manager.purchase-orders.create', compact('suppliers', 'shipments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'shipment_id'    => 'nullable|exists:shipments,id',
            'order_date'     => 'required|date',
            'expected_date'  => 'nullable|date|after_or_equal:order_date',
            'notes'          => 'nullable|string|max:1000',
            'items'          => 'required|array|min:1',
            'items.*.name'   => 'required|string|max:255',
            'items.*.qty'    => 'required|integer|min:1',
            'items.*.unit'   => 'required|string',
            'items.*.price'  => 'required|numeric|min:0',
        ]);

        $userId = session('auth_user_id');

        // Generate PO number
        $poNumber = 'PO-' . now()->format('Ymd') . '-' . str_pad(
            PurchaseOrder::whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        // Calculate total
        $total = collect($data['items'])->sum(fn($item) => $item['qty'] * $item['price']);

        $po = PurchaseOrder::create([
            'po_number'   => $poNumber,
            'user_id'     => $userId,
            'supplier_id' => $data['supplier_id'],
            'shipment_id' => $data['shipment_id'] ?? null,
            'status'      => 'Draft',
            'order_date'  => $data['order_date'],
            'expected_date' => $data['expected_date'] ?? null,
            'total_amount' => $total,
            'notes'        => $data['notes'] ?? null,
        ]);

        foreach ($data['items'] as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'item_name'  => $item['name'],
                'quantity'   => $item['qty'],
                'unit'       => $item['unit'],
                'unit_price' => $item['price'],
                'total_price'=> $item['qty'] * $item['price'],
            ]);
        }

        return redirect()
            ->route('manager.purchase-orders.index')
            ->with('success', "Purchase Order {$poNumber} created successfully.");
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->user_id !== session('auth_user_id')) {
            abort(403);
        }

        $purchaseOrder->load(['supplier.country', 'shipment', 'items', 'user']);

        return view('manager.purchase-orders.show', compact('purchaseOrder'));
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->user_id !== session('auth_user_id')) {
            abort(403);
        }

        $data = $request->validate([
            'status' => 'required|in:Draft,Approved,Shipped,Completed,Cancelled',
        ]);

        $purchaseOrder->update($data);

        return back()->with('success', "PO status updated to {$data['status']}.");
    }
}
