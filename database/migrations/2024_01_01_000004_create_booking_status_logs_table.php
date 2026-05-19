<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('old_status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])->nullable();
            $table->enum('new_status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan']);
            $table->string('changed_by', 100)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('booking_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_status_logs');
    }
};
