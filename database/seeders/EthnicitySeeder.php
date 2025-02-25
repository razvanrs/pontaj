<?php

namespace Database\Seeders;

use App\Models\Ethnicity;
use Illuminate\Database\Seeder;

class EthnicitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Ethnicity::factory()->count(5)->create();
    }
}
