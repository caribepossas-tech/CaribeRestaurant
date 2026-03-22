<?php

namespace App\Livewire\Kot;

use App\Models\Kot;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class Kots extends Component
{

    protected $listeners = ['refreshKots' => '$refresh'];
    public $filterOrders;
    public $dateRangeType;
    public $startDate;
    public $endDate;
    public $confirmDeleteKotModal = false;
    public $kotIdToDelete;

    public function mount()
    {
        $this->dateRangeType = 'today';
        $this->filterOrders = 'in_kitchen';
        $this->startDate = now()->startOfWeek()->format('m/d/Y');
        $this->endDate = now()->endOfWeek()->format('m/d/Y');

        $this->setDateRange();
    }

    public function setDateRange()
    {
        switch ($this->dateRangeType) {
        case 'today':
            $this->startDate = now()->startOfDay()->format('m/d/Y');
            $this->endDate = now()->startOfDay()->format('m/d/Y');
            break;

        case 'lastWeek':
            $this->startDate = now()->subWeek()->startOfWeek()->format('m/d/Y');
            $this->endDate = now()->subWeek()->endOfWeek()->format('m/d/Y');
            break;

        case 'last7Days':
            $this->startDate = now()->subDays(7)->format('m/d/Y');
            $this->endDate = now()->startOfDay()->format('m/d/Y');
            break;

        case 'currentMonth':
            $this->startDate = now()->startOfMonth()->format('m/d/Y');
            $this->endDate = now()->startOfDay()->format('m/d/Y');
            break;

        case 'lastMonth':
            $this->startDate = now()->subMonth()->startOfMonth()->format('m/d/Y');
            $this->endDate = now()->subMonth()->endOfMonth()->format('m/d/Y');
            break;

        case 'currentYear':
            $this->startDate = now()->startOfYear()->format('m/d/Y');
            $this->endDate = now()->startOfDay()->format('m/d/Y');
            break;

        case 'lastYear':
            $this->startDate = now()->subYear()->startOfYear()->format('m/d/Y');
            $this->endDate = now()->subYear()->endOfYear()->format('m/d/Y');
            break;
        
        default:
            $this->startDate = now()->startOfWeek()->format('m/d/Y');
            $this->endDate = now()->endOfWeek()->format('m/d/Y');
            break;
        }

    }

    #[On('setStartDate')]
    public function setStartDate($start)
    {
        $this->startDate = $start;
    }

    #[On('setEndDate')]
    public function setEndDate($end)
    {
        $this->endDate = $end;
    }

    public function render()
    {
        $start = Carbon::createFromFormat('m/d/Y', $this->startDate)->startOfDay()->toDateTimeString();
        $end = Carbon::createFromFormat('m/d/Y', $this->endDate)->endOfDay()->toDateTimeString();

        // Base query with eager loading for all required data
        $query = Kot::with([
            'order.table',
            'order.waiter',
            'items.menuItem',
            'items.menuItemVariation',
            'items.modifierOptions'
        ])
        ->withCount('items')
        ->join('orders', 'kots.order_id', '=', 'orders.id')
        ->select('kots.*') // Ensure we get Kot model attributes
        ->whereBetween('kots.created_at', [$start, $end])
        ->whereNotIn('orders.status', ['canceled', 'draft'])
        ->orderBy('kots.id', 'desc');

        // Fetch counts for tabs using separate counts or a single fetch
        // For efficiency, we can fetch all filtered by date once and then filter in memory for counts
        // BUT for the main list, we apply the specific status filter in SQL.
        
        $allKotsForPeriod = (clone $query)->get();

        $inKitchen = $allKotsForPeriod->filter(fn($kot) => $kot->status == 'in_kitchen');
        $served = $allKotsForPeriod->filter(fn($kot) => $kot->status == 'served');
        $foodReady = $allKotsForPeriod->filter(fn($kot) => $kot->status == 'food_ready');

        $kotList = match ($this->filterOrders) {
            'in_kitchen' => $inKitchen,
            'served' => $served,
            'food_ready' => $foodReady,
            default => $allKotsForPeriod,
        };

        return view('livewire.kot.kots', [
            'kots' => $kotList,
            'inKitchenCount' => $inKitchen->count(),
            'servedCount' => $served->count(),
            'foodReadyCount' => $foodReady->count(),
        ]);
    }

    public function changeKotStatus($id, $status)
    {
        Kot::where('id', $id)->update([
            'status' => $status
        ]);

        $this->dispatch('refreshKots');
    }

    public function confirmDeleteKot($id)
    {
        $this->kotIdToDelete = $id;
        $this->confirmDeleteKotModal = true;
    }

    public function deleteKot()
    {
        $id = $this->kotIdToDelete;
        $kot = Kot::find($id);
        if (!$kot) return;

        $order = $kot->order;
        $kotCounts = $order->kot->count();
        
        if ($kotCounts == 1) {
            $order->status = 'canceled';
            $order->save();

            if ($order->table) {
                $order->table->update(['available_status' => 'available']);
            }
        }

        Kot::destroy($id);
        $this->confirmDeleteKotModal = false;
        
        $this->dispatch('refreshKots');

        if ($kotCounts == 1) {
            $order->delete();
        }
    }

}
