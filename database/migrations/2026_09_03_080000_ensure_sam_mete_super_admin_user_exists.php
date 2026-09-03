<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ensure Samir Mete (samir@posterit.com) exists as super_admin
        User::firstOrCreate(
            ['email' => 'samir@posterit.com'],
            [
                'name' => 'Samir Mete',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );

        // 2. Also ensure Sam Mete (sam@posterit.com) alias exists as super_admin
        User::firstOrCreate(
            ['email' => 'sam@posterit.com'],
            [
                'name' => 'Sam Mete',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }

    public function down(): void
    {
        //
    }
};
