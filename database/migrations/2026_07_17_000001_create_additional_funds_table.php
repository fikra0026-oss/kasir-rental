<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('additional_funds', function (Blueprint $table) {
            $table->id();
            $table->string('source');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('additional_funds');
    }
};
