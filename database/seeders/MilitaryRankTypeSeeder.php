<?php

namespace Database\Seeders;

use App\Models\MilitaryRankType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class MilitaryRankTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Schema::disableForeignKeyConstraints();

        MilitaryRankType::create([
            'name' => 'Ofițer de poliție',
            'sel_order' => 1,
        ]);

        MilitaryRankType::create([
            'name' => 'Agent de poliție',
            'sel_order' => 2,
        ]);

        MilitaryRankType::create([
            'name' => 'Personal contractual',
            'sel_order' => 3,
        ]);

        Schema::enableForeignKeyConstraints();
    }
}
