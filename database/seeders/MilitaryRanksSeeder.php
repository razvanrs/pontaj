<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MilitaryRanksSeeder extends Seeder
{
    public function run(): void
    {

        Schema::disableForeignKeyConstraints();

        DB::table('military_ranks')->truncate();

        $ranks = [
            // Officers
            ['name' => 'Chestor general de poliție', 'abbreviation' => 'Chst.gen.', 'military_rank_type_id' => 1],
            ['name' => 'Chestor-șef de poliție', 'abbreviation' => 'Chst.șef', 'military_rank_type_id' => 1],
            ['name' => 'Chestor principal de poliție', 'abbreviation' => 'Chst.pr.', 'military_rank_type_id' => 1],
            ['name' => 'Chestor de poliție', 'abbreviation' => 'Chst.', 'military_rank_type_id' => 1],
            ['name' => 'Comisar-șef de poliție', 'abbreviation' => 'Cms.șef', 'military_rank_type_id' => 1],
            ['name' => 'Comisar de poliție', 'abbreviation' => 'Cms.', 'military_rank_type_id' => 1],
            ['name' => 'Subcomisar de poliție', 'abbreviation' => 'Scms.', 'military_rank_type_id' => 1],
            ['name' => 'Inspector principal de poliție', 'abbreviation' => 'Insp.pr.', 'military_rank_type_id' => 1],
            ['name' => 'Inspector de poliție', 'abbreviation' => 'Insp.', 'military_rank_type_id' => 1],
            ['name' => 'Subinspector de poliție', 'abbreviation' => 'Sinsp.', 'military_rank_type_id' => 1],

            // Agents
            ['name' => 'Agent-șef principal de poliție', 'abbreviation' => 'Ag.șef pr.', 'military_rank_type_id' => 2],
            ['name' => 'Agent-șef de poliție', 'abbreviation' => 'Ag.șef', 'military_rank_type_id' => 2],
            ['name' => 'Agent-șef adjunct de poliție', 'abbreviation' => 'Ag.șef adj', 'military_rank_type_id' => 2],
            ['name' => 'Agent principal de poliție', 'abbreviation' => 'Ag.pr.', 'military_rank_type_id' => 2],
            ['name' => 'Agent de poliție', 'abbreviation' => 'Ag.', 'military_rank_type_id' => 2],

            // Civil Personnel
            ['name' => 'Personal civil', 'abbreviation' => 'P.c.', 'military_rank_type_id' => 3],
        ];

        foreach ($ranks as $rank) {
            DB::table('military_ranks')->insert([
                'name' => $rank['name'],
                'abbreviation' => $rank['abbreviation'],
                'military_rank_type_id' => $rank['military_rank_type_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }
}
