<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::create([
            'name'=> 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('pass123')
        ]);

        $user->role = 'admin';
        $user->is_active = true;
        $user->save();
    }
}
