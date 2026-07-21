<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $managers = User::where('role', 'import_manager')->get();
        $suppliers = Supplier::all();
        $shipments = Shipment::all();

        if ($managers->isEmpty() || $suppliers->isEmpty()) {
            return;
        }

        $poItems = [
            ['name' => 'OLED Screen Sub-Assembly A', 'price' => 45.50, 'unit' => 'pcs'],
            ['name' => 'Snapdragon Processor Chips', 'price' => 88.00, 'unit' => 'pcs'],
            ['name' => 'Lithium Battery Cells (3000mAh)', 'price' => 3.20, 'unit' => 'pcs'],
            ['name' => 'Aluminium Frame Cases', 'price' => 12.00, 'unit' => 'pcs'],
            ['name' => 'Optical Camera Sensor Module', 'price' => 24.50, 'unit' => 'pcs'],
        ];

        for ($i = 1; $i <= 10; $i++) {
            $manager = $managers->random();
            $supplier = $suppliers->first(fn($s) => $s->company_name === $manager->company_name) ?? $suppliers->random();

            // Link to a shipment of the same manager
            $shipment = $shipments->where('user_id', $manager->id)->random() ?? null;

            $date = Carbon::now()->addDays(rand(-25, 5));
            $expected = (clone $date)->addDays(rand(10, 20));

            $poNumber = 'PO-' . $date->format('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_number'   => $poNumber,
                'user_id'     => $manager->id,
                'supplier_id' => $supplier->id,
                'shipment_id' => $shipment?->id,
                'status'      => ['Draft', 'Approved', 'Shipped', 'Completed', 'Cancelled'][rand(0, 4)],
                'order_date'  => $date,
                'expected_date' => $expected,
                'total_amount' => 0, // will sum up
                'notes'        => 'Standard import batch purchase request. Checked for port risk factors and currency adjustments.',
            ]);

            $totalAmount = 0;
            // Add 1 to 3 items
            $itemCount = rand(1, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $itemTemplate = $poItems[array_rand($poItems)];
                $qty = rand(100, 2000);
                $totalPrice = $qty * $itemTemplate['price'];

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_name'         => $itemTemplate['name'],
                    'quantity'          => $qty,
                    'unit'              => $itemTemplate['unit'],
                    'unit_price'        => $itemTemplate['price'],
                    'total_price'       => $totalPrice,
                ]);

                $totalAmount += $totalPrice;
            }

            $po->update(['total_amount' => $totalAmount]);
        }
    }
}
