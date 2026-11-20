<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // what a runner applicant gives the admin to prove they are a UCLM student
            $table->string('portal_id', 50)->nullable()->unique()->after('student_id');
            $table->boolean('enrolled_confirmed')->default(false)->after('portal_id');
            $table->string('id_photo_path')->nullable()->after('enrolled_confirmed');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['portal_id']);
            $table->dropColumn(['portal_id', 'enrolled_confirmed', 'id_photo_path']);
        });
    }
};
