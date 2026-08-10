<?php

namespace Tests\Feature;

use App\Models\PlaystationUnit;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodDrinkOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_food_and_drink_order_can_be_created_from_special_page(): void
    {
        $user = User::factory()->create();
        PlaystationUnit::create([
            'name' => 'PS1',
            'type' => 'slim',
            'tv' => false,
            'status' => 'kosong',
            'hourly_rate' => 50000,
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

        $response = $this->actingAs($user)->get('/food-drinks');
        $response->assertStatus(200);

        $response = $this->actingAs($user)->post('/food-drinks', [
            'customer_name' => 'Budi',
            'payment_method' => 'cash',
            'products' => [
                ['product_id' => $food->id, 'quantity' => 2],
                ['product_id' => $drink->id, 'quantity' => 0],
            ],
        ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', ['customer_name' => 'Budi']);
        $this->assertEquals(1, Transaction::first()->items()->count());
    }
}
