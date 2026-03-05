<?php

namespace Modules\Inventory\Livewire\Reports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Modules\Inventory\Entities\InventoryMovement;

#[Layout('layouts.app')]
class UsageReport extends Component
{
    use WithPagination;

    public $period = 'weekly';
    public $startDate;
    public $endDate;
    public $searchTerm = '';
    public $chartOptions = [];

    public function mount()
    {
        // Get values from query parameters
        $this->period = request('period', 'weekly');
        $this->startDate = request('startDate', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $this->endDate = request('endDate', Carbon::now()->format('Y-m-d'));
        $this->searchTerm = request('search', '');
        
        $this->loadReportData();
    }

    public function updatedPeriod()
    {
        // Update date range based on selected period
        $this->startDate = match($this->period) {
            'daily' => Carbon::now()->format('Y-m-d'),
            'monthly' => Carbon::now()->startOfMonth()->format('Y-m-d'),
            default => Carbon::now()->startOfWeek()->format('Y-m-d') // weekly is default
        };
        
        $this->endDate = Carbon::now()->format('Y-m-d');
        
        return $this->redirect(route('inventory.reports.usage', [
            'period' => $this->period,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->searchTerm
        ]));
    }

    public function updatedStartDate()
    {
        return $this->redirect(route('inventory.reports.usage', [
            'period' => $this->period,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->searchTerm
        ]));
    }

    public function updatedEndDate()
    {
        return $this->redirect(route('inventory.reports.usage', [
            'period' => $this->period,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->searchTerm
        ]));
    }

    public function updatedSearchTerm()
    {
        return $this->redirect(route('inventory.reports.usage', [
            'period' => $this->period,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'search' => $this->searchTerm
        ]));
    }

    public function loadReportData()
    {
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);
        
        // Get current period data
        $currentData = $this->getMovementData($startDate, $endDate);
        
        // Get previous period data for comparison
        $daysDiff = $startDate->diffInDays($endDate);
        $previousStart = $startDate->copy()->subDays($daysDiff);
        $previousEnd = $startDate->copy()->subDay();
        $previousData = $this->getMovementData($previousStart, $previousEnd);

        $chartOptions = [
            'chart' => [
                'height' => 420,
                'type' => 'area',
                'fontFamily' => 'Inter, sans-serif',
                'toolbar' => [
                    'show' => false
                ]
            ],
            'series' => [
                [
                    'name' => __('inventory::modules.reports.usage.current_period'),
                    'data' => $currentData['values'],
                    'color' => '#1A56DB'
                ],
                [
                    'name' => __('inventory::modules.reports.usage.previous_period'),
                    'data' => $previousData['values'],
                    'color' => '#FDBA8C'
                ]
            ],
            'xaxis' => [
                'categories' => $currentData['labels'],
                'labels' => [
                    'style' => [
                        'colors' => '#6B7280',
                        'fontSize' => '12px',
                        'fontWeight' => 500
                    ]
                ]
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'colors' => '#6B7280',
                        'fontSize' => '12px',
                        'fontWeight' => 500
                    ]
                ]
            ],
            'colors' => ['#1A56DB', '#FDBA8C'],
            'dataLabels' => [
                'enabled' => false
            ],
            'stroke' => [
                'curve' => 'smooth',
                'width' => 2
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'enabled' => true,
                    'opacityFrom' => 0.45,
                    'opacityTo' => 0.05
                ]
            ],
            'tooltip' => [
                'theme' => 'dark'
            ]
        ];

        $this->dispatch('updateChart', options: $chartOptions);
    }

    private function getMovementData($startDate, $endDate)
    {
        $query = InventoryMovement::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($this->searchTerm) {
            $query->whereHas('item', function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%');
            });
        }

        $periodFormat = match($this->period) {
            'weekly' => 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))',
            'monthly' => 'DATE_FORMAT(created_at, "%Y-%m-01")',
            default => 'DATE(created_at)'
        };

        $movements = $query->selectRaw($periodFormat . ' as date, SUM(CASE 
                WHEN transaction_type = ? THEN quantity 
                WHEN transaction_type IN (?, ?, ?) THEN -quantity 
                ELSE 0 END) as total', [
                InventoryMovement::TRANSACTION_TYPE_STOCK_ADDED,
                InventoryMovement::TRANSACTION_TYPE_ORDER_USED,
                InventoryMovement::TRANSACTION_TYPE_WASTE,
                InventoryMovement::TRANSACTION_TYPE_TRANSFER
            ])
            ->groupByRaw($periodFormat)
            ->orderByRaw('date')
            ->get();

        $labels = [];
        $values = [];

        foreach ($movements as $movement) {
            $date = Carbon::parse($movement->date);
            $labels[] = match($this->period) {
                'weekly' => 'Week ' . $date->week . ' (' . $date->format('M d') . ')',
                'monthly' => $date->format('M Y'),
                default => $date->format('M d')
            };
            $values[] = (float) $movement->total;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }

    public function render()
    {
        $query = InventoryMovement::query()
            ->when($this->searchTerm, function ($query) {
                $query->whereHas('item', function ($q) {
                    $q->where('name', 'like', '%' . $this->searchTerm . '%');
                });
            })
            ->whereBetween('created_at', [$this->startDate, $this->endDate]);

        $periodFormat = match($this->period) {
            'weekly' => 'DATE(DATE_SUB(created_at, INTERVAL WEEKDAY(created_at) DAY))',
            'monthly' => 'DATE_FORMAT(created_at, "%Y-%m-01")',
            default => 'DATE(created_at)'
        };

        $movements = $query->select('inventory_movements.*')
            ->selectRaw($periodFormat . ' as period_date')
            ->with(['item.unit'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('inventory::livewire.reports.usage-report', [
            'movements' => $movements,
            'totalUsage' => $query->sum('quantity'),
            'previousPeriodUsage' => $this->calculatePreviousPeriodUsage(),
            'transactionTypes' => [
                'STOCK_ADDED' => InventoryMovement::TRANSACTION_TYPE_STOCK_ADDED,
                'ORDER_USED' => InventoryMovement::TRANSACTION_TYPE_ORDER_USED,
                'WASTE' => InventoryMovement::TRANSACTION_TYPE_WASTE,
                'TRANSFER' => InventoryMovement::TRANSACTION_TYPE_TRANSFER,
            ],
        ]);
    }

    private function calculatePreviousPeriodUsage()
    {
        $currentStart = Carbon::parse($this->startDate);
        $currentEnd = Carbon::parse($this->endDate);
        $daysDiff = $currentStart->diffInDays($currentEnd);

        $previousStart = $currentStart->copy()->subDays($daysDiff);
        $previousEnd = $currentStart->copy()->subDay();

        $query = InventoryMovement::whereBetween('created_at', [$previousStart, $previousEnd]);

        if ($this->searchTerm) {
            $query->whereHas('item', function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%');
            });
        }

        return $query->sum('quantity');
    }
} 