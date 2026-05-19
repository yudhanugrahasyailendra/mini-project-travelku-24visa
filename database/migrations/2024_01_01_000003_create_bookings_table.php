<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_package_id')
                ->constrained('travel_packages')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('booking_number', 20)->unique();
            $table->string('name', 150);
            $table->string('contact', 150);
            $table->date('departure_date');
            $table->unsignedTinyInteger('participants')->default(1);
            $table->decimal('price_per_person', 12, 2);

            $table->enum('status', ['Menunggu', 'Dikonfirmasi', 'Selesai', 'Dibatalkan'])
                ->default('Menunggu');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('departure_date');
            $table->index(['travel_package_id', 'status'], 'idx_pkg_status');
            $table->index('booking_number');
        });

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('
                ALTER TABLE bookings
                ADD COLUMN total_price DECIMAL(14,2)
                GENERATED ALWAYS AS (participants * price_per_person) STORED
                COMMENT "Total otomatis"
                AFTER price_per_person
            ');
        } else {
            DB::statement('
                ALTER TABLE bookings
                ADD COLUMN total_price REAL
                GENERATED ALWAYS AS (participants * price_per_person) STORED
            ');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
