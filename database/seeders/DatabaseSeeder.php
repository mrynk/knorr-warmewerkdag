<?php

namespace Database\Seeders;

use App\Models\Reward;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        Reward::create([
            'name' => 'heater',
            'description' => 'Een energiezuinige desktop kachel',
            'release_at' => now()->subMinutes(1),
        ]);
    }
}
