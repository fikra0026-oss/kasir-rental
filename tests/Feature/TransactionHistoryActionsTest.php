<?php

namespace Tests\Feature;

use App\Models\PlaystationUnit;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionHistoryActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_food_drink_transaction_shows_payment_action_without_edit_buttons(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Transaction::create([
            'transaction_number' => 'TRXFD0001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'unknown',
            'payment_method' => 'cash',
            'subtotal' => 5000,
            'discount' => 0,
            'total' => 5000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 0,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertDontSee('Edit Jam');
        $response->assertSee('Selesaikan Bayar');
    }

    public function test_running_transaction_with_unit_still_shows_actions(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unit = PlaystationUnit::create([
            'name' => 'PS1',
            'type' => 'reguler',
            'tv' => 'LG',
            'status' => 'digunakan',
            'hourly_rate' => 15000,
        ]);

        Transaction::create([
            'transaction_number' => 'TRXUNIT0001',
            'playstation_unit_id' => $unit->id,
            'member_id' => null,
            'customer_name' => 'unknown',
            'payment_method' => 'cash',
            'subtotal' => 10000,
            'discount' => 0,
            'total' => 10000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertSee('Edit Paket');
        $response->assertSee('Selesaikan');
    }

    public function test_food_drink_transaction_shows_payment_completion_action(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Transaction::create([
            'transaction_number' => 'TRXFD0002',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'subtotal' => 25000,
            'discount' => 0,
            'total' => 25000,
            'status' => 'berjalan',
            'payment_status' => 'belum_bayar',
            'started_at' => now(),
            'duration_minutes' => 0,
        ]);

        $response = $this->actingAs($user)->get('/transactions');

        $response->assertStatus(200);
        $response->assertSee('Selesaikan Bayar');
    }

    public function test_running_transaction_can_add_food_and_drink_items(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $unit = PlaystationUnit::create([
            'name' => 'PS1',
            'type' => 'reguler',
            'tv' => 'LG',
            'status' => 'digunakan',
            'hourly_rate' => 15000,
        ]);

        $food = Product::create([
            'name' => 'Nasi Goreng',
            'category' => 'makanan',
            'price' => 20000,
            'stock' => 10,
            'active' => true,
        ]);

        $drink = Product::create([
            'name' => 'Teh Botol',
            'category' => 'minuman',
            'price' => 8000,
            'stock' => 10,
            'active' => true,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRXRUN0001',
            'playstation_unit_id' => $unit->id,
            'member_id' => null,
            'customer_name' => 'Siti',
            'payment_method' => 'cash',
            'subtotal' => 50000,
            'discount' => 0,
            'total' => 50000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 60,
        ]);

        $response = $this->actingAs($user)->post(route('transactions.food-drinks.store', $transaction), [
            'products' => [
                ['product_id' => $food->id, 'quantity' => 2],
                ['product_id' => $drink->id, 'quantity' => 1],
            ],
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $transaction->id, 'type' => 'food']);
        $this->assertDatabaseHas('transaction_items', ['transaction_id' => $transaction->id, 'type' => 'drink']);
        $this->assertEquals(98000, $transaction->fresh()->total);
    }

    public function test_food_drink_item_can_be_deleted_without_deleting_ps_transaction(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $product = Product::create([
            'name' => 'Kentang Goreng',
            'category' => 'makanan',
            'price' => 15000,
            'stock' => 8,
            'active' => true,
        ]);

        $transaction = Transaction::create([
            'transaction_number' => 'TRXDEL0001',
            'playstation_unit_id' => null,
            'member_id' => null,
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'subtotal' => 35000,
            'discount' => 0,
            'total' => 35000,
            'status' => 'berjalan',
            'started_at' => now(),
            'duration_minutes' => 0,
        ]);

        $foodItem = $transaction->items()->create([
            'product_id' => $product->id,
            'type' => 'food',
            'description' => $product->name,
            'quantity' => 2,
            'unit_price' => 15000,
            'total_price' => 30000,
        ]);
        $rentalItem = $transaction->items()->create([
            'product_id' => null,
            'type' => 'rental',
            'description' => 'Paket 60 menit',
            'quantity' => 1,
            'unit_price' => 5000,
            'total_price' => 5000,
        ]);
        $product->decrement('stock', 2);

        $response = $this->actingAs($user)->delete(route('transactions.food-drinks.destroy', [$transaction, $foodItem]));

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseMissing('transaction_items', ['id' => $foodItem->id]);
        $this->assertDatabaseHas('transaction_items', ['id' => $rentalItem->id]);
        $this->assertEquals(8, $product->fresh()->stock);
        $this->assertEquals(5000, $transaction->fresh()->total);
        $this->assertEquals(5000, $transaction->fresh()->subtotal);
    }
}
