<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixOrderNumbers extends Command
{
    protected $signature = 'orders:fix-numbers {--dry-run : Show what would be changed without actually changing it}';

    protected $description = 'Re-number all orders sequentially per branch based on creation order (fixes duplicate order_number=11 bug)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made to the database.');
        }

        // Get all branch IDs that have duplicate order numbers
        $branchIds = Order::select('branch_id')
            ->groupBy('branch_id', 'order_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('branch_id')
            ->unique();

        if ($branchIds->isEmpty()) {
            $this->info('No duplicate order numbers found. Nothing to fix.');
            return 0;
        }

        $this->info("Found duplicate order numbers in " . $branchIds->count() . " branch(es).");

        foreach ($branchIds as $branchId) {
            $this->info("\nProcessing branch_id: {$branchId}");

            // Get all orders for this branch ordered by creation time (or ID as fallback)
            $orders = Order::where('branch_id', $branchId)
                ->orderBy('id')
                ->get();

            $this->table(
                ['ID', 'Current #', 'New #', 'Created At'],
                $orders->map(fn($o, $i) => [
                    $o->id,
                    $o->order_number,
                    $i + 1,
                    $o->created_at,
                ])->toArray()
            );

            if (!$dryRun) {
                if (!$this->confirm("Apply renumbering for branch {$branchId}?", true)) {
                    $this->line("Skipped branch {$branchId}.");
                    continue;
                }

                DB::transaction(function () use ($orders) {
                    foreach ($orders as $index => $order) {
                        $newNumber = $index + 1;
                        if ($order->order_number !== $newNumber) {
                            $order->update(['order_number' => $newNumber]);
                        }
                    }
                });

                $this->info("Branch {$branchId} renumbered successfully.");
            }
        }

        if ($dryRun) {
            $this->warn("\nThis was a dry run. Run without --dry-run to apply changes.");
        } else {
            $this->info("\nAll branches fixed.");
        }

        return 0;
    }
}
