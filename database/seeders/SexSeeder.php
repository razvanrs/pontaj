<?php

namespace Database\Seeders;

use App\Models\Sex;
use Illuminate\Database\Seeder;

class SexSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Sex::create(['name' => 'Masculin', 'sel_order' => 1]);
        Sex::create(['name' => 'Feminin', 'sel_order' => 2]);
    }
}
