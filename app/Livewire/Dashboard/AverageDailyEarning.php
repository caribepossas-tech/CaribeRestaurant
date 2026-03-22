<?php

namespace App\Livewire\Dashboard;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AverageDailyEarning extends Component
{

    public $orderCount;
    public $percentChange;
    
    public function mount()
    {
        $daysInMonth = now()->format('d');
        $daysInPreviousMonth = now()->subMonth()->daysInMonth;

        $totalEarnings = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $totalPreviousEarnings = Order::where('status', 'paid')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('total');
    
        $this->orderCount = ($totalEarnings / $daysInMonth);

        $averageDailyPreviousEarnings = $totalPreviousEarnings / $daysInPreviousMonth;

        $orderDifference = ($this->orderCount - $averageDailyPreviousEarnings);

        $this->percentChange  = (($orderDifference / ($averageDailyPreviousEarnings == 0 ? 1 : $averageDailyPreviousEarnings)) * 100);
    }

    public function render()
    {
        return view('livewire.dashboard.average-daily-earning');
    }

}
