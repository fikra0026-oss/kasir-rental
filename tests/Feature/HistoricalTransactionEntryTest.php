<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\PlaystationUnit;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalTransactionEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_historical_rental_is_saved_as_completed_and_does_not_change_unit_status(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $unit = PlaystationUnit::create([
            'name' => 'PS1',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'digunakan',
            'hourly_rate' => 30000,
        ]);
        $member = Member::create(['name' => 'Budi']);
        $product = Product::create([
            'name' => 'Air Mineral',
            'category' => 'minuman',
            'price' => 5000,
            'stock' => 5,
            'active' => true,
        ]);
        $occurredAt = now()->subDay()->setTime(10, 30)->startOfMinute();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'playstation_unit_id' => $unit->id,
            'manual_minutes' => 60,
            'member_id' => $member->id,
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'occurred_at' => $occurredAt->format('Y-m-d\TH:i'),
            'products' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect(route('transactions.index'));
        $transaction = Transaction::latest('id')->firstOrFail();
        $this->assertSame('selesai', $transaction->status);
        $this->assertSame('lunas', $transaction->payment_status);
        $this->assertSame($occurredAt->timestamp, $transaction->created_at->timestamp);
        $this->assertSame('digunakan', $unit->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(4, $member->fresh()->points);
        $this->assertSame(1, $member->fresh()->total_transactions);
    }

    public function test_historical_food_order_is_completed_and_adjusts_stock_without_changing_unit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $unit = PlaystationUnit::create([
            'name' => 'PS2',
            'type' => 'PS4',
            'tv' => 'TV Test',
            'status' => 'digunakan',
            'hourly_rate' => 25000,
        ]);
        $member = Member::create(['name' => 'Siti']);
        $product = Product::create([
            'name' => 'Roti',
            'category' => 'makanan',
            'price' => 10000,
            'stock' => 4,
            'active' => true,
        ]);
        $occurredAt = now()->subDays(2)->setTime(14, 0)->startOfMinute();

        $response = $this->actingAs($user)->post(route('food-drinks.store'), [
            'playstation_unit_id' => $unit->id,
            'member_id' => $member->id,
            'customer_name' => 'Siti',
            'payment_method' => 'qris',
            'occurred_at' => $occurredAt->format('Y-m-d\TH:i'),
            'products' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect(route('transactions.index'));
        $transaction = Transaction::latest('id')->firstOrFail();
        $this->assertSame('selesai', $transaction->status);
        $this->assertSame('lunas', $transaction->payment_status);
        $this->assertSame($occurredAt->timestamp, $transaction->created_at->timestamp);
        $this->assertSame('digunakan', $unit->fresh()->status);
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(1, $member->fresh()->points);
        $this->assertSame(1, $member->fresh()->total_transactions);
    }

    public function test_future_transaction_date_is_rejected(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $unit = PlaystationUnit::create([
            'name' => 'PS3',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'kosong',
            'hourly_rate' => 30000,
        ]);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'playstation_unit_id' => $unit->id,
            'manual_minutes' => 60,
            'payment_method' => 'cash',
            'occurred_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertSessionHasErrors('occurred_at');
        $this->assertSame(0, Transaction::count());
    }

    public function test_current_rental_still_starts_and_marks_unit_in_use(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $unit = PlaystationUnit::create([
            'name' => 'PS4',
            'type' => 'PS5',
            'tv' => 'TV Test',
            'status' => 'kosong',
            'hourly_rate' => 30000,
        ]);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'playstation_unit_id' => $unit->id,
            'manual_minutes' => 60,
            'payment_method' => 'cash',
            'occurred_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('transactions.index'));
        $transaction = Transaction::latest('id')->firstOrFail();
        $this->assertSame('berjalan', $transaction->status);
        $this->assertSame('belum_bayar', $transaction->payment_status);
        $this->assertSame('digunakan', $unit->fresh()->status);
    }

    public function test_both_transaction_forms_render(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('transactions.create'))
            ->assertOk()
            ->assertSee('occurred_at');

        $this->actingAs($user)
            ->get(route('food-drinks'))
            ->assertOk()
            ->assertSee('occurred_at')
            ->assertSee('Member (opsional)');
    }
}
