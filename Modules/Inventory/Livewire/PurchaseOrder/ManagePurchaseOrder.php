<?php

namespace Modules\Inventory\Livewire\PurchaseOrder;

use Livewire\Component;
use Modules\Inventory\Entities\PurchaseOrder;
use Modules\Inventory\Entities\Supplier;
use Modules\Inventory\Entities\InventoryItem;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;

class ManagePurchaseOrder extends Component
{
    use LivewireAlert;

    public $showModal = false;
    public $isEditing = false;
    public $purchaseOrder;

    // Form fields
    public $supplierId;
    public $orderDate;
    public $expectedDeliveryDate;
    public $notes;
    public $items = [];

    protected $listeners = [
        'showPurchaseOrderModal' => 'showModal',
        'editPurchaseOrder' => 'edit',
    ];

    protected function rules()
    {
        return [
            'supplierId' => 'required|exists:suppliers,id',
            'orderDate' => 'required|date',
            'expectedDeliveryDate' => 'nullable|date|after_or_equal:orderDate',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventoryItemId' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unitPrice' => 'required|numeric|min:0.01',
        ];
    }

    public function mount($supplierId = null)
    {
        $this->orderDate = now()->format('Y-m-d');
        $this->supplierId = $supplierId;
        $this->resetItems();
    }

    public function resetItems()
    {
        $this->items = [    
            [
                'inventoryItemId' => '',
                'quantity' => 1,
                'unitPrice' => 0,
                'subtotal' => 0,
            ],
            [
                'inventoryItemId' => '',
                'quantity' => 1,
                'unitPrice' => 0,
                'subtotal' => 0,
            ]
        ];
    }

    public function showModal()
    {
        $currentSupplierId = $this->supplierId;
        // $this->resetExcept('showModal');
        $this->supplierId = $currentSupplierId;
        $this->orderDate = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        $this->isEditing = true;
        $this->purchaseOrder = $purchaseOrder;
        $this->supplierId = $purchaseOrder->supplier_id;
        $this->orderDate = $purchaseOrder->order_date->format('Y-m-d');
        $this->expectedDeliveryDate = $purchaseOrder->expected_delivery_date?->format('Y-m-d');
        $this->notes = $purchaseOrder->notes;

        $this->items = $purchaseOrder->items->map(function ($item) {
            return [
                'inventoryItemId' => $item->inventory_item_id,
                'quantity' => $item->quantity,
                'unitPrice' => $item->unit_price,
            ];
        })->toArray();

        $this->showModal = true;
    }

    public function addItem()
    {
        $this->items[] = [
            'inventoryItemId' => '',
            'quantity' => 1,
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function calculateSubtotal($index) {
        $item = $this->items[$index];
        $this->items[$index]['subtotal'] = $item['unitPrice'] * $item['quantity'];
    }

    public function save()
{
    $this->validate();

    DB::transaction(function () {
        if ($this->isEditing) {
            $po = $this->purchaseOrder;
            $po->update([
                'supplier_id' => $this->supplierId,
                'order_date' => $this->orderDate,
                'expected_delivery_date' => $this->expectedDeliveryDate,
                'notes' => $this->notes,
            ]);

            $po->items()->delete();
        } else {
            // Generar el PO number de forma segura dentro de la transacción
            $poNumber = $this->generateUniquePONumber();
            
            $po = PurchaseOrder::create([
                'branch_id' => branch()->id,
                'supplier_id' => $this->supplierId,
                'order_date' => $this->orderDate,
                'expected_delivery_date' => $this->expectedDeliveryDate,
                'notes' => $this->notes,
                'created_by' => user()->id,
                'status' => 'draft',
                'po_number' => $poNumber, // Asignar el número generado
            ]);
        }

        foreach ($this->items as $item) {
            InventoryItem::find($item['inventoryItemId'])->update([
                'unit_purchase_price' => $item['unitPrice'],
            ]);

            $po->items()->create([
                'inventory_item_id' => $item['inventoryItemId'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unitPrice'],
                'subtotal' => $item['quantity'] * $item['unitPrice'], // Calcular automáticamente
            ]);
        }

        $po->update(['total_amount' => $po->items->sum('subtotal')]);
    });

    $this->showModal = false;
    $this->isEditing = false;
    $this->dispatch('purchaseOrderSaved');
    $this->alert('success', trans('inventory::modules.purchaseOrder.saved_successfully'));
}

// Nuevo método para generar PO numbers únicos
private function generateUniquePONumber()
{
    return DB::transaction(function () {
        // Bloquear la tabla para evitar condiciones de carrera
        $lastPO = PurchaseOrder::lockForUpdate()->orderBy('id', 'desc')->first();
        
        if ($lastPO && $lastPO->po_number) {
            // Extraer el número y incrementar
            $lastNumber = (int) preg_replace('/[^0-9]/', '', $lastPO->po_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $poNumber = 'PO-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        
        // Verificar que no exista (doble seguridad)
        $exists = PurchaseOrder::where('po_number', $poNumber)->exists();
        if ($exists) {
            // Si por alguna razón existe, intentar con el siguiente número
            $newNumber++;
            $poNumber = 'PO-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
        }
        
        return $poNumber;
    });
}

    public function fetchUnitPrice($index)
    {
        $item = $this->items[$index];
        $inventoryItem = InventoryItem::find($item['inventoryItemId']);
        $this->items[$index]['unitPrice'] = $inventoryItem->unit_purchase_price;
        $this->calculateSubtotal($index);
    }

    public function render()
    {
        $inventoryItems = InventoryItem::query();
        
        if ($this->supplierId) {
           $inventoryItems = $inventoryItems->where('preferred_supplier_id', $this->supplierId);
        }

        $inventoryItems = $inventoryItems->where('branch_id', branch()->id)
            ->with(['unit', 'category'])
            ->orderBy('name')
            ->get()
            ->map(function ($item) {
                $categoryName = $item->category ? $item->category->name : 'No Category';
                $unitSymbol = $item->unit ? $item->unit->symbol : '';
                $item->display_name = "{$item->name} ({$categoryName} - {$unitSymbol})";
                return $item;
            });

        return view('inventory::livewire.purchase-order.manage-purchase-order', [
            'suppliers' => Supplier::where('restaurant_id', restaurant()->id)
                ->orderBy('name')
                ->get(),
            'inventoryItems' => $inventoryItems,
        ]);
    }
}
