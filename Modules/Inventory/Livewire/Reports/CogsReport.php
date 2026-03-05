<?php

namespace Modules\Inventory\Livewire\Reports;

use Livewire\Component;
use Modules\Inventory\Entities\InventoryItem;
use Modules\Inventory\Entities\InventoryMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Entities\InventoryItemCategory;


class CogsReport extends Component
{
    public $startDate;
    public $endDate;
    public $selectedCategory = 'all';
    public $reportData = [];
    public $totalCogs = 0;
    public $categories = [];

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
        $this->categories = InventoryItemCategory::all();
        $this->generateReport();
    }

    public function generateReport()
    {
        // Set MySQL to non-strict mode for this query
        DB::statement("SET SESSION sql_mode=''");
        $query = InventoryMovement::query()
            ->join('inventory_items', 'inventory_movements.inventory_item_id', '=', 'inventory_items.id')
            ->whereBetween('inventory_movements.created_at', [$this->startDate, $this->endDate])
            ->where('inventory_movements.transaction_type', InventoryMovement::TRANSACTION_TYPE_ORDER_USED);

        if ($this->selectedCategory !== 'all') {
            $query->where('inventory_items.inventory_item_category_id', $this->selectedCategory);
        }

        $this->reportData = $query->select(
            'inventory_items.name as product_name',
            'inventory_items.inventory_item_category_id',
            'inventory_items.unit_purchase_price',
            'inventory_movements.inventory_item_id',
            DB::raw('SUM(inventory_movements.quantity) as total_quantity'),
            DB::raw('SUM(inventory_movements.quantity * inventory_items.unit_purchase_price) as total_cost')
        )
            ->groupBy('inventory_items.id', 'inventory_items.name', 'inventory_items.inventory_item_category_id')
            ->with('item', 'item.unit')
            ->get();

        $this->calculateTotals();
        // Reset SQL mode back to default after query execution
        DB::statement("SET SESSION sql_mode=(SELECT @@global.sql_mode)");
    }

    private function calculateTotals()
    {
        $this->totalCogs = $this->reportData->sum('total_cost');
    }

    public function render()
    {
        return view('inventory::livewire.reports.cogs-report');
    }
}
