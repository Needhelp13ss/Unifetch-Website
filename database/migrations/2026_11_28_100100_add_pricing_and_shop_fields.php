<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('service_fee', 8, 2)->default(0);   // UniFetch's profit on this order
            $table->unsignedInteger('distance_m')->default(0);  // metres used to price the runner fee
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete(); // shop accounts
            $table->timestamp('last_active_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vendor_id');
            $table->dropColumn('last_active_at');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['service_fee', 'distance_m']);
        });
    }
};
