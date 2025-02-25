<?php

namespace Database\Seeders;

use App\Models\Sanction;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SanctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sanction::truncate();

        Sanction::factory()->count(30)->create();
    }
}
