<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // food = normal menu order, custom = custom errand request
            $table->string('type', 20)->default('food')->after('order_number');
            $table->foreignId('runner_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();

            // custom errand details
            $table->string('errand_title', 120)->nullable();
            $table->text('errand_details')->nullable();
            $table->string('pickup_location', 120)->nullable();
            $table->decimal('budget', 8, 2)->nullable();

            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            // cash the runner handed over to UniFetch (via wallet) for a cash-on-delivery order
            $table->decimal('remitted', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('runner_id');
            $table->dropColumn([
                'type', 'errand_title', 'errand_details', 'pickup_location',
                'budget', 'accepted_at', 'delivered_at', 'remitted',
            ]);
        });
    }
};
