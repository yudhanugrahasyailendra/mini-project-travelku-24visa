<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('code', 20)->unique();
            $table->string('name', 150);
            $table->string('slug', 170)->unique();
            $table->string('destination', 100);
            $table->unsignedTinyInteger('duration_days');
            $table->unsignedTinyInteger('duration_nights');

            $table->decimal('base_price', 12, 2);
            $table->decimal('price_weekend', 12, 2)->nullable();
            $table->decimal('price_holiday', 12, 2)->nullable();

            $table->unsignedTinyInteger('min_participants')->default(1);
            $table->unsignedTinyInteger('max_participants')->default(100);

            $table->string('short_desc', 300)->nullable();
            $table->longText('description')->nullable();
            $table->text('includes')->nullable();
            $table->text('excludes')->nullable();
            $table->string('cover_image', 255)->nullable();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'deleted_at']);
            $table->index('category_id');
            $table->index(['is_featured', 'is_active']);
            $table->index('sort_order');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE travel_packages ADD FULLTEXT idx_pkg_search (name, destination, short_desc)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_packages');
    }
};
