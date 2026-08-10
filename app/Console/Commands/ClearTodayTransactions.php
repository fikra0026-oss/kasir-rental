<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\Product;
use App\Models\Member;
use App\Models\PlaystationUnit;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClearTodayTransactions extends Command
{
    protected $signature = 'transactions:clear-today {--yes : Skip confirmation}';
    protected $description = "Remove all transactions created today (restores stocks, updates units and members)";

    public function handle()
    {
        if (! $this->option('yes')) {
            if (! $this->confirm('This will permanently delete all transactions created today. Continue?', false)) {
                $this->info('Aborted.');
                return 1;
            }
        }

        $today = Carbon::today();

        $transactions = Transaction::whereDate('created_at', $today)->with('items')->get();

        if ($transactions->isEmpty()) {
            $this->info('No transactions found for today.');
            return 0;
        }

        DB::transaction(function () use ($transactions) {
            foreach ($transactions as $transaction) {
                // restore product stock
                foreach ($transaction->items as $item) {
                    if ($item->product_id) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('stock', $item->quantity ?? 0);
                        }
                    }
                }

                // update unit status to kosong
                if ($transaction->playstation_unit_id) {
                    $unit = PlaystationUnit::find($transaction->playstation_unit_id);
                    if ($unit) {
                        $unit->update(['status' => 'kosong']);
                    }
                }

                // adjust member counters
                if ($transaction->member_id) {
                    $member = Member::find($transaction->member_id);
                    if ($member) {
                        // decrement total_transactions but not below zero
                        if ($member->total_transactions > 0) {
                            $member->decrement('total_transactions');
                        }
                        // adjust points (approximate reversal)
                        $pointsToRemove = (int) floor(($transaction->total ?? 0) / 10000);
                        if ($pointsToRemove > 0) {
                            $member->decrement('points', min($member->points ?? 0, $pointsToRemove));
                        }
                    }
                }

                // delete items then transaction
                $transaction->items()->delete();
                $transaction->delete();
            }
        });

        $this->info('Deleted '.$transactions->count().' transaction(s) from today and restored related data.');

        return 0;
    }
}
