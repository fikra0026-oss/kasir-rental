<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'package_id')) {
                $table->foreignId('package_id')->nullable()->after('playstation_unit_id')->constrained('rental_packages')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'package_id')) {
                $table->dropConstrainedForeignId('package_id');
            }
        });
    }
};
