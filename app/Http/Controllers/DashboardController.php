<?php

namespace App\Http\Controllers;

use App\Models\AdditionalFund;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\PlaystationUnit;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();
        $week = Carbon::today()->subDays(7);
        $month = Carbon::today()->startOfMonth();

        $transactionsToday = Transaction::whereDate('created_at', $today)->count();
        $incomeToday = Transaction::whereDate('created_at', $today)->sum('total');
        $incomeWeek = Transaction::whereDate('created_at', '>=', $week)->sum('total');
        $incomeMonth = Transaction::whereDate('created_at', '>=', $month)->sum('total');
        $totalRevenue = Transaction::sum('total');
        $psRevenue = Transaction::whereHas('items', function ($query) {
            $query->where('type', 'rental');
        })->sum('total');
        $foodDrinkRevenue = Transaction::whereHas('items', function ($query) {
            $query->whereIn('type', ['food', 'drink']);
        })->sum('total');
        $psRevenueToday = Transaction::whereDate('created_at', $today)
            ->whereHas('items', function ($query) {
                $query->where('type', 'rental');
            })->sum('total');
        $foodDrinkRevenueToday = Transaction::whereDate('created_at', $today)
            ->whereHas('items', function ($query) {
                $query->whereIn('type', ['food', 'drink']);
            })->sum('total');
        $totalExpenses = Expense::sum('amount');
        $totalAdditionalFunds = AdditionalFund::sum('amount');
        $netIncome = $totalRevenue - $totalExpenses;
        $totalKeuangan = $totalRevenue + $totalAdditionalFunds - $totalExpenses;

        $statusCounts = PlaystationUnit::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $upcomingBookings = Booking::where('date', '>=', $today)
            ->whereIn('status', ['pending', 'aktif'])
            ->orderBy('date')
            ->orderBy('start_time')
            ->take(5)
            ->get();

        $units = PlaystationUnit::with('transactions')->orderBy('name')->get();

        $expiredTransactions = Transaction::where('status', 'berjalan')
            ->whereNotNull('started_at')
            ->where('duration_minutes', '>', 0)
            ->get()
            ->filter(function ($transaction) {
                $endTime = $transaction->started_at->copy()->addMinutes($transaction->duration_minutes);

                return $endTime->isPast();
            });

        foreach ($expiredTransactions as $transaction) {
            $transaction->update([
                'status' => 'selesai',
                'ended_at' => now(),
            ]);

            if ($transaction->playstation_unit_id) {
                $unit = PlaystationUnit::find($transaction->playstation_unit_id);
                if ($unit) {
                    $unit->update(['status' => 'kosong']);
                    $unit->status = 'kosong';
                }
            }
        }

        foreach ($units as $unit) {
            $activeRental = Transaction::where('playstation_unit_id', $unit->id)
                ->where('status', 'berjalan')
                ->whereNotNull('started_at')
                ->orderByDesc('started_at')
                ->first();

            $unit->active_rental = null;
            $unit->countdown_end = null;
            $unit->remaining_seconds = null;

            if ($activeRental && $activeRental->started_at && $activeRental->duration_minutes > 0) {
                $endTime = $activeRental->started_at->copy()->addMinutes($activeRental->duration_minutes);

                $unit->status = 'digunakan';
                $unit->active_rental = $activeRental;
                $unit->countdown_end = $endTime->toIso8601String();
                $unit->remaining_seconds = max(0, $endTime->diffInSeconds(Carbon::now(), false));
            } elseif ($unit->status === 'digunakan') {
                $unit->status = 'kosong';
            }
        }

        $revenueChart = Transaction::selectRaw('DATE(created_at) as date, sum(total) as total')
            ->whereDate('created_at', '>=', $month)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueChartData = $revenueChart->map(function ($item) {
            return [
                'date' => $item->date,
                'label' => Carbon::parse($item->date)->translatedFormat('d M'),
                'total' => (float) $item->total,
            ];
        })->values();

        return view('dashboard', [
            'transactionsToday' => $transactionsToday,
            'incomeToday' => $incomeToday,
            'incomeWeek' => $incomeWeek,
            'incomeMonth' => $incomeMonth,
            'totalRevenue' => $totalRevenue,
            'psRevenue' => $psRevenue,
            'foodDrinkRevenue' => $foodDrinkRevenue,
            'psRevenueToday' => $psRevenueToday,
            'foodDrinkRevenueToday' => $foodDrinkRevenueToday,
            'totalExpenses' => $totalExpenses,
            'totalAdditionalFunds' => $totalAdditionalFunds,
            'netIncome' => $netIncome,
            'totalKeuangan' => $totalKeuangan,
            'statusCounts' => $statusCounts,
            'upcomingBookings' => $upcomingBookings,
            'units' => $units,
            'revenueChart' => $revenueChart,
            'revenueChartData' => $revenueChartData,
        ]);
    }

    public function dailyRevenuePs()
    {
        $data = $this->buildRevenueData('rental', Carbon::today()->subDays(30), Carbon::today());

        return view('revenue.daily', [
            'title' => 'Pendapatan Harian PS',
            'description' => 'Daftar pendapatan harian dari transaksi rental PS.',
            'metricLabel' => 'Pendapatan PS',
            'type' => 'ps',
            'entries' => $data['entries'],
            'totalRevenue' => $data['totalRevenue'],
            'transactionCount' => $data['transactionCount'],
        ]);
    }

    public function dailyRevenueFoodDrinks()
    {
        $data = $this->buildRevenueData('food-drinks', Carbon::today()->subDays(30), Carbon::today());

        return view('revenue.daily', [
            'title' => 'Pendapatan Harian Makanan & Minuman',
            'description' => 'Daftar pendapatan harian dari transaksi makanan dan minuman.',
            'metricLabel' => 'Pendapatan Makanan & Minuman',
            'type' => 'food-drinks',
            'entries' => $data['entries'],
            'totalRevenue' => $data['totalRevenue'],
            'transactionCount' => $data['transactionCount'],
        ]);
    }

    public function archiveRevenuePs()
    {
        $data = $this->buildRevenueData('rental', Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth());

        return view('revenue.daily', [
            'title' => 'Pendapatan Arsip PS Bulan Lalu',
            'description' => 'Daftar pendapatan arsip PS dari bulan lalu.',
            'metricLabel' => 'Pendapatan PS Bulan Lalu',
            'type' => 'ps-archive',
            'entries' => $data['entries'],
            'totalRevenue' => $data['totalRevenue'],
            'transactionCount' => $data['transactionCount'],
        ]);
    }

    public function archiveRevenueFoodDrinks()
    {
        $data = $this->buildRevenueData('food-drinks', Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth());

        return view('revenue.daily', [
            'title' => 'Pendapatan Arsip Makanan & Minuman Bulan Lalu',
            'description' => 'Daftar pendapatan arsip makanan dan minuman dari bulan lalu.',
            'metricLabel' => 'Pendapatan Makanan & Minuman Bulan Lalu',
            'type' => 'food-drinks-archive',
            'entries' => $data['entries'],
            'totalRevenue' => $data['totalRevenue'],
            'transactionCount' => $data['transactionCount'],
        ]);
    }

    public function exportArchiveRevenuePs(): Response
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();
        $data = $this->buildRevenueData('rental', $startDate, $endDate);

        $rows = [];
        foreach ($data['entries'] as $entry) {
            $rows[] = [
                'Tanggal' => $entry['label'],
                'Jumlah Transaksi' => $entry['transactions'],
                'Total Pendapatan' => $entry['total'],
            ];
        }

        return $this->downloadArchiveCsv($rows, 'arsip-pendapatan-ps-bulan-lalu.csv');
    }

    public function exportArchiveRevenueFoodDrinks(): Response
    {
        $startDate = Carbon::now()->subMonth()->startOfMonth();
        $endDate = Carbon::now()->subMonth()->endOfMonth();
        $data = $this->buildRevenueData('food-drinks', $startDate, $endDate);

        $rows = [];
        foreach ($data['entries'] as $entry) {
            $rows[] = [
                'Tanggal' => $entry['label'],
                'Jumlah Transaksi' => $entry['transactions'],
                'Total Pendapatan' => $entry['total'],
            ];
        }

        return $this->downloadArchiveCsv($rows, 'arsip-pendapatan-makanan-minuman-bulan-lalu.csv');
    }

    private function downloadArchiveCsv(array $rows, string $filename): Response
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, ['Tanggal', 'Jumlah Transaksi', 'Total Pendapatan']);

        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['Tanggal'],
                $row['Jumlah Transaksi'],
                $row['Total Pendapatan'],
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function buildRevenueData(string $mode, Carbon $startDate, Carbon $endDate): array
    {
        $transactions = Transaction::with('items')
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderByDesc('created_at')
            ->get();

        $filteredTransactions = $transactions->filter(function ($transaction) use ($mode) {
            return $transaction->items->contains(function ($item) use ($mode) {
                if ($mode === 'food-drinks') {
                    return in_array($item->type, ['food', 'drink']);
                }

                return $item->type === 'rental';
            });
        });

        $grouped = $filteredTransactions->groupBy(function ($transaction) {
            return $transaction->created_at->toDateString();
        });

        $entries = $grouped->map(function ($dayTransactions, $date) use ($mode) {
            $total = 0;
            $details = [];

            foreach ($dayTransactions as $transaction) {
                foreach ($transaction->items as $item) {
                    $matches = $mode === 'food-drinks'
                        ? in_array($item->type, ['food', 'drink'])
                        : $item->type === 'rental';

                    if (! $matches) {
                        continue;
                    }

                    $total += (float) $item->total_price;
                    $details[] = [
                        'transaction_number' => $transaction->transaction_number,
                        'customer_name' => $transaction->customer_name,
                        'description' => $item->description,
                        'amount' => (float) $item->total_price,
                    ];
                }
            }

            return [
                'date' => $date,
                'label' => Carbon::parse($date)->translatedFormat('d M Y'),
                'transactions' => $dayTransactions->count(),
                'total' => $total,
                'details' => $details,
            ];
        })->sortByDesc('date')->values();

        $totalRevenue = $entries->sum('total');
        $transactionCount = $entries->sum('transactions');

        return [
            'entries' => $entries,
            'totalRevenue' => $totalRevenue,
            'transactionCount' => $transactionCount,
        ];
    }
}
