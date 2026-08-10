<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Member;
use App\Models\PlaystationUnit;
use App\Models\Product;
use App\Models\RentalPackage;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin PS Rental',
            'email' => 'admin@psrental.local',
            'password' => 'password',
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Kasir PS',
            'email' => 'kasir@psrental.local',
            'password' => 'password',
            'role' => 'kasir',
            'email_verified_at' => now(),
        ]);

        PlaystationUnit::create(['name' => 'PS1', 'type' => 'PS5', 'tv' => 'Samsung 55"', 'status' => 'kosong', 'hourly_rate' => 25000]);
        PlaystationUnit::create(['name' => 'PS2', 'type' => 'PS4 Pro', 'tv' => 'LG 50"', 'status' => 'kosong', 'hourly_rate' => 22000]);
        PlaystationUnit::create(['name' => 'PS3', 'type' => 'PS4 Slim', 'tv' => 'Xiaomi 43"', 'status' => 'kosong', 'hourly_rate' => 18000]);
        PlaystationUnit::create(['name' => 'PS4', 'type' => 'PS3', 'tv' => 'Polytron 40"', 'status' => 'maintenance', 'hourly_rate' => 15000]);

        RentalPackage::create(['name' => '1 Jam', 'price' => 25000, 'duration_minutes' => 60, 'description' => 'Paket rental 1 jam reguler', 'is_member' => false, 'is_weekend' => false, 'is_holiday' => false]);
        RentalPackage::create(['name' => '2 Jam', 'price' => 47000, 'duration_minutes' => 120, 'description' => 'Paket rental 2 jam reguler', 'is_member' => false, 'is_weekend' => false, 'is_holiday' => false]);
        RentalPackage::create(['name' => 'Paket Member', 'price' => 22000, 'duration_minutes' => 60, 'description' => 'Harga khusus member untuk 1 jam', 'is_member' => true, 'is_weekend' => false, 'is_holiday' => false]);
        RentalPackage::create(['name' => 'Weekend Special', 'price' => 28000, 'duration_minutes' => 60, 'description' => 'Tarif khusus hari Sabtu/Minggu', 'is_member' => false, 'is_weekend' => true, 'is_holiday' => false]);

        Member::create(['name' => 'Budi Santoso', 'phone' => '081234567890', 'address' => 'Jakarta', 'points' => 120, 'total_transactions' => 8, 'level' => 'gold']);
        Member::create(['name' => 'Rina Putri', 'phone' => '081298765432', 'address' => 'Bekasi', 'points' => 230, 'total_transactions' => 16, 'level' => 'platinum']);

        Product::create(['name' => 'Coca-Cola', 'category' => 'Soft Drink', 'price' => 12000, 'stock' => 20, 'active' => true]);
        Product::create(['name' => 'Popcorn', 'category' => 'Snack', 'price' => 15000, 'stock' => 15, 'active' => true]);
        Product::create(['name' => 'Indomie Goreng', 'category' => 'Mie Instan', 'price' => 9000, 'stock' => 25, 'active' => true]);
        Product::create(['name' => 'Kopi Tubruk', 'category' => 'Kopi', 'price' => 10000, 'stock' => 12, 'active' => true]);

        Expense::create(['category' => 'listrik', 'description' => 'Tagihan listrik bulan ini', 'amount' => 850000, 'date' => now()->subDays(7)->format('Y-m-d')]);
        Expense::create(['category' => 'internet', 'description' => 'Tagihan internet bulanan', 'amount' => 350000, 'date' => now()->subDays(6)->format('Y-m-d')]);
    }
}
