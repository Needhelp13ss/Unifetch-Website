<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_open')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            // meals, groceries, drinks, medicine, essentials
            $table->string('category', 30)->index();
            $table->decimal('price', 8, 2);
            $table->string('prep_time', 20)->default('15-20 min');
            $table->string('emoji', 10)->default('🍽️');
            $table->string('bg_color', 7)->default('#FDE7C8');
            // optional photo, e.g. "products/lunch-combo.jpg" inside public/
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('vendors');
    }
};
