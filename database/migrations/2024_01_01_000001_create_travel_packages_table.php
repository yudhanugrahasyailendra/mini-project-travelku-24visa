<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Nama paket, mis. Bali 4D3N');
            $table->string('duration', 20)->nullable()->comment('Durasi, mis. 4D3N');
            $table->string('destination', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_packages');
    }
};
