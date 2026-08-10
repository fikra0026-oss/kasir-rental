<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNetIncomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_total_revenue_after_expenses(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Transaction::create([
            'transaction_number' => 'TRXNET001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'unknown',
            'payment_method' => 'cash',
            'subtotal' => 1500000,
            'discount' => 0,
            'total' => 1500000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 60,
        ]);

        Expense::create([
            'category' => 'listrik',
            'description' => 'Tagihan listrik',
            'amount' => 200000,
            'date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Laba Bersih');
        $response->assertSee('Grafik Pendapatan Harian');
        $response->assertSee('Rp 1.300.000');
    }

    public function test_dashboard_shows_separate_revenue_totals_for_ps_and_food_drinks(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $psTransaction = Transaction::create([
            'transaction_number' => 'TRXPS001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'ps customer',
            'payment_method' => 'cash',
            'subtotal' => 120000,
            'discount' => 0,
            'total' => 120000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 60,
        ]);

        $foodTransaction = Transaction::create([
            'transaction_number' => 'TRXFD001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'food customer',
            'payment_method' => 'cash',
            'subtotal' => 90000,
            'discount' => 0,
            'total' => 90000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 0,
        ]);

        TransactionItem::create([
            'transaction_id' => $psTransaction->id,
            'product_id' => null,
            'type' => 'rental',
            'description' => 'Paket PS',
            'quantity' => 1,
            'unit_price' => 120000,
            'total_price' => 120000,
        ]);

        TransactionItem::create([
            'transaction_id' => $foodTransaction->id,
            'product_id' => null,
            'type' => 'food',
            'description' => 'Nasi',
            'quantity' => 1,
            'unit_price' => 90000,
            'total_price' => 90000,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSeeText('Pendapatan PS');
        $response->assertSeeText('Pendapatan Makanan & Minuman');
        $response->assertSeeText('Pendapatan PS Hari Ini');
        $response->assertSeeText('Pendapatan Makanan & Minuman Hari Ini');
        $response->assertSee('Rp 120.000');
        $response->assertSee('Rp 90.000');
    }

    public function test_dashboard_shows_only_current_calendar_month_revenue(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $currentMonthTransaction = new Transaction([
            'transaction_number' => 'TRXCURR001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'current month',
            'payment_method' => 'cash',
            'subtotal' => 500000,
            'discount' => 0,
            'total' => 500000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 60,
        ]);
        $currentMonthTransaction->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->save();

        $lastMonthTransaction = new Transaction([
            'transaction_number' => 'TRXPREV001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'last month',
            'payment_method' => 'cash',
            'subtotal' => 400000,
            'discount' => 0,
            'total' => 400000,
            'status' => 'selesai',
            'started_at' => now()->subMonth(),
            'ended_at' => now()->subMonth(),
            'duration_minutes' => 60,
        ]);
        $lastMonthTransaction->forceFill([
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ])->save();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSeeText('Pendapatan Bulan Ini');
        $response->assertSeeInOrder(['Pendapatan Bulan Ini', 'Rp 500.000']);
        $response->assertDontSee('Rp 400.000');
    }

    public function test_daily_revenue_pages_show_separate_ps_and_food_drink_lists(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $psTransaction = Transaction::create([
            'transaction_number' => 'TRXPS002',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'ps customer',
            'payment_method' => 'cash',
            'subtotal' => 150000,
            'discount' => 0,
            'total' => 150000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 60,
        ]);

        $foodTransaction = Transaction::create([
            'transaction_number' => 'TRXFD002',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'food customer',
            'payment_method' => 'cash',
            'subtotal' => 80000,
            'discount' => 0,
            'total' => 80000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 0,
        ]);

        TransactionItem::create([
            'transaction_id' => $psTransaction->id,
            'product_id' => null,
            'type' => 'rental',
            'description' => 'Paket PS',
            'quantity' => 1,
            'unit_price' => 150000,
            'total_price' => 150000,
        ]);

        TransactionItem::create([
            'transaction_id' => $foodTransaction->id,
            'product_id' => null,
            'type' => 'drink',
            'description' => 'Es Teh',
            'quantity' => 1,
            'unit_price' => 80000,
            'total_price' => 80000,
        ]);

        $psResponse = $this->actingAs($user)->get('/revenue/daily/ps');
        $psResponse->assertStatus(200);
        $psResponse->assertSeeText('Pendapatan Harian PS');
        $psResponse->assertSee('Rp 150.000');

        $foodResponse = $this->actingAs($user)->get('/revenue/daily/food-drinks');
        $foodResponse->assertStatus(200);
        $foodResponse->assertSeeText('Pendapatan Harian Makanan & Minuman');
        $foodResponse->assertSee('Rp 80.000');
    }

    public function test_last_month_archive_pages_show_previous_month_revenue(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $lastMonthPsTransaction = new Transaction([
            'transaction_number' => 'TRXPSARCH001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'archive ps customer',
            'payment_method' => 'cash',
            'subtotal' => 180000,
            'discount' => 0,
            'total' => 180000,
            'status' => 'selesai',
            'started_at' => now()->subMonth()->startOfMonth()->addDays(5),
            'ended_at' => now()->subMonth()->startOfMonth()->addDays(5),
            'duration_minutes' => 60,
        ]);
        $lastMonthPsTransaction->forceFill([
            'created_at' => now()->subMonth()->startOfMonth()->addDays(5),
            'updated_at' => now()->subMonth()->startOfMonth()->addDays(5),
        ])->save();

        $lastMonthFoodTransaction = new Transaction([
            'transaction_number' => 'TRXFDARCH001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'archive food customer',
            'payment_method' => 'cash',
            'subtotal' => 95000,
            'discount' => 0,
            'total' => 95000,
            'status' => 'selesai',
            'started_at' => now()->subMonth()->startOfMonth()->addDays(10),
            'ended_at' => now()->subMonth()->startOfMonth()->addDays(10),
            'duration_minutes' => 0,
        ]);
        $lastMonthFoodTransaction->forceFill([
            'created_at' => now()->subMonth()->startOfMonth()->addDays(10),
            'updated_at' => now()->subMonth()->startOfMonth()->addDays(10),
        ])->save();

        $currentMonthTransaction = new Transaction([
            'transaction_number' => 'TRXCURRARCH001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'current month customer',
            'payment_method' => 'cash',
            'subtotal' => 250000,
            'discount' => 0,
            'total' => 250000,
            'status' => 'selesai',
            'started_at' => now(),
            'ended_at' => now(),
            'duration_minutes' => 60,
        ]);
        $currentMonthTransaction->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->save();

        TransactionItem::create([
            'transaction_id' => $lastMonthPsTransaction->id,
            'product_id' => null,
            'type' => 'rental',
            'description' => 'Paket PS',
            'quantity' => 1,
            'unit_price' => 180000,
            'total_price' => 180000,
        ]);

        TransactionItem::create([
            'transaction_id' => $lastMonthFoodTransaction->id,
            'product_id' => null,
            'type' => 'drink',
            'description' => 'Jus Alpukat',
            'quantity' => 1,
            'unit_price' => 95000,
            'total_price' => 95000,
        ]);

        TransactionItem::create([
            'transaction_id' => $currentMonthTransaction->id,
            'product_id' => null,
            'type' => 'rental',
            'description' => 'Paket PS Saat Ini',
            'quantity' => 1,
            'unit_price' => 250000,
            'total_price' => 250000,
        ]);

        $psArchiveResponse = $this->actingAs($user)->get('/revenue/archive/ps');
        $psArchiveResponse->assertStatus(200);
        $psArchiveResponse->assertSeeText('Pendapatan Arsip PS Bulan Lalu');
        $psArchiveResponse->assertSee('Rp 180.000');
        $psArchiveResponse->assertDontSee('Rp 250.000');

        $foodArchiveResponse = $this->actingAs($user)->get('/revenue/archive/food-drinks');
        $foodArchiveResponse->assertStatus(200);
        $foodArchiveResponse->assertSeeText('Pendapatan Arsip Makanan & Minuman Bulan Lalu');
        $foodArchiveResponse->assertSee('Rp 95.000');
        $foodArchiveResponse->assertDontSee('Rp 250.000');
    }

    public function test_archive_pages_can_export_excel_file(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/revenue/archive/ps/export');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="arsip-pendapatan-ps-bulan-lalu.csv"');
    }
}
