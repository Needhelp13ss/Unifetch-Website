<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // customer | runner | admin
            $table->string('role', 20)->default('customer')->after('password');
            // null = never applied, pending | approved | rejected
            $table->string('runner_status', 20)->nullable()->after('role');
            $table->timestamp('runner_applied_at')->nullable()->after('runner_status');
            $table->decimal('wallet_balance', 10, 2)->default(0)->after('runner_applied_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'runner_status', 'runner_applied_at', 'wallet_balance']);
        });
    }
};
