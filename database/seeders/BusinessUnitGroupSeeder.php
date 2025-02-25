<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessUnitGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, truncate the business_unit_groups table and reset business_unit_group_id
        $this->cleanupExistingData();

        // Create the business unit groups
        $groups = [
            1 => ['code' => 'Default', 'name' => 'Default', 'sel_order' => 1],
            2 => ['code' => 'BCI', 'name' => 'BCI', 'sel_order' => 2],
            // 3 => ['code' => 'SUPORT', 'name' => 'Suport', 'sel_order' => 3],
            // 4 => ['code' => 'EDUCATIONAL', 'name' => 'Educațional', 'sel_order' => 4],
        ];

        // Insert groups
        foreach ($groups as $id => $group) {
            DB::table('business_unit_groups')->insert([
                'id' => $id,
                'code' => $group['code'],
                'name' => $group['name'],
                'sel_order' => $group['sel_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Map business units to groups
        $unitGroupMappings = [
            // BCI - Group 1
            2 => ['3', '17'], // BCI
        ];

        // Update business units with their group IDs
        foreach ($unitGroupMappings as $groupId => $unitCodes) {
            DB::table('business_units')
                ->whereIn('id', $unitCodes)
                ->update(['business_unit_group_id' => $groupId]);
        }

        // Set default group (1) for any unmapped business units
        DB::table('business_units')
            ->whereNull('business_unit_group_id')
            ->update(['business_unit_group_id' => 1]);
    }

    /**
     * Clean up existing data before seeding
     */
    private function cleanupExistingData(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the business_unit_groups table
        DB::table('business_unit_groups')->truncate();

        // Reset all business_unit_group_id values to NULL
        DB::table('business_units')
            ->update(['business_unit_group_id' => 1]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}