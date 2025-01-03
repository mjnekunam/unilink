<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Vite;

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

        User::factory()->count(20)->create();
        User::factory()->create([
            'name' => 'محمدجواد نکونام',
            'email' => "mjnekunam@gmail.com",
            'role' => 'teacher',
            'password' => Hash::make('m0j@N411376')
        ]);
        User::factory()->create([
            'name' => 'حسین نکونام',
            'email' => "jnekounam777@gmail.com",
            'role' => 'student',
            'password' => Hash::make('m0j@N411376')
        ]);
    }
}
