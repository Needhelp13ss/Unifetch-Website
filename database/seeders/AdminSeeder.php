<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates the UniFetch admin account (safe to run more than once).
 *
 *   email:    admin@unifetch.com
 *   password: Admin@12345     <-- change it after your first login
 */
class AdminSeeder extends Seeder
{
    public const EMAIL = 'admin@unifetch.com';
    public const PASSWORD = 'Admin@12345';

    public function run(): void
    {
        $admin = User::firstOrNew(['email' => self::EMAIL]);

        if (!$admin->exists) {
            $admin->name = 'UniFetch Admin';
            $admin->student_id = 'ADMIN-001';
            $admin->password = Hash::make(self::PASSWORD);
        }

        $admin->role = 'admin';
        $admin->runner_status = null;
        $admin->save();
    }
}
