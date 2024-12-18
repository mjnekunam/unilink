<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::factory()->count(20)->sequence(
            ['role' => 'student'],
            ['role' => 'professor']
        )->create();

        User::factory()->create([
            'name' => 'محمدجواد نکونام',
            'email' => "mjnekunam@gmail.com",
            'role' => 'professor',
            'password' => Hash::make('m0j@N411376')
        ]);
    }
}
