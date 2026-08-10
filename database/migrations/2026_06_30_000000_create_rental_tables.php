<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('kasir')->after('password');
            }
        });

        Schema::create('playstation_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('tv');
            $table->enum('status', ['kosong', 'digunakan', 'booking', 'maintenance'])->default('kosong');
            $table->decimal('hourly_rate', 10, 2)->default(15000);
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        Schema::create('rental_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 12, 2);
            $table->integer('duration_minutes');
            $table->text('description')->nullable();
            $table->boolean('is_member')->default(false);
            $table->boolean('is_weekend')->default(false);
            $table->boolean('is_holiday')->default(false);
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->integer('points')->default(0);
            $table->integer('total_transactions')->default(0);
            $table->enum('level', ['silver', 'gold', 'platinum'])->default('silver');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->enum('status', ['normal', 'rusak', 'maintenance'])->default('normal');
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('phone')->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->integer('duration_minutes');
            $table->foreignId('playstation_unit_id')->constrained('playstation_units')->cascadeOnDelete();
            $table->enum('status', ['pending', 'aktif', 'selesai', 'dibatalkan'])->default('pending');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('playstation_unit_id')->constrained('playstation_units')->cascadeOnDelete();
            $table->foreignId('member_id')->nullable()->constrained('members')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->enum('payment_method', ['cash', 'qris', 'transfer', 'e-wallet'])->default('cash');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['diproses', 'berjalan', 'selesai', 'dibatalkan'])->default('diproses');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_minutes')->default(0);
            $table->timestamps();
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('type');
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['listrik', 'internet', 'gaji', 'servis_ps', 'pembelian_snack', 'pembelian_minuman', 'lain_lain']);
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('products');
        Schema::dropIfExists('members');
        Schema::dropIfExists('rental_packages');
        Schema::dropIfExists('playstation_units');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
