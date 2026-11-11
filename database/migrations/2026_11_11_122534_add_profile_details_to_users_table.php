<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bio', 160)->nullable();
            $table->string('gender', 20)->nullable();
            $table->boolean('show_gender')->default(false);
            $table->date('birthday')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('course', 100)->nullable();
            $table->string('year_level', 20)->nullable();
            $table->string('campus_location', 100)->nullable();
            $table->string('favorite_food', 100)->nullable();
            $table->string('interests', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'gender', 'show_gender', 'birthday', 'phone',
                'course', 'year_level', 'campus_location',
                'favorite_food', 'interests',
            ]);
        });
    }
};