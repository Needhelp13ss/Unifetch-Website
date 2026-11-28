<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campus_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // PLACEHOLDER coordinates: the admin edits them (Menu > Campus places) so the distance fee is accurate.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('campus_location_id')->nullable()->constrained('campus_locations')->nullOnDelete();
        });

        // default places (about 0.00009 degrees is roughly 10 metres)
        $places = [
            'Main Gate' => [0, 0],
            'Main Building' => [90, 40],
            'Annex Building' => [150, 120],
            'Library' => [60, 160],
            'Canteen' => [-40, 110],
            'Gymnasium' => [-120, 60],
            'Clinic' => [30, 70],
            'Parking Area' => [-70, -50],
        ];
        $now = now();
        foreach ($places as $name => [$dy, $dx]) {
            DB::table('campus_locations')->insert([
                'name' => $name,
                'latitude' => round(10.3300000 + $dy * 0.00009 / 10, 7),
                'longitude' => round(123.9500000 + $dx * 0.00009 / 10, 7),
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // shops that already exist get the place they are in
        $map = ['Student Bites' => 'Canteen', 'Campus Corner' => 'Library', 'Daily Mart' => 'Main Gate',
                'QuickStop' => 'Annex Building', 'Campus Health Corner' => 'Clinic'];
        foreach ($map as $vendor => $place) {
            $id = DB::table('campus_locations')->where('name', $place)->value('id');
            DB::table('vendors')->where('name', $vendor)->update(['campus_location_id' => $id]);
        }
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campus_location_id');
        });
        Schema::dropIfExists('campus_locations');
    }
};
