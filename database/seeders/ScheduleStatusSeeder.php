<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ScheduleStatusSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('schedule_statuses')->truncate();

        $statuses = [
            [
                'code' => 'PREZ',
                'name' => 'Prezent',
                'color' => '#3c82f6',
                'background' => 'bg-blue-500',
                'sel_order' => 1
            ],
            [
                'code' => 'CO',
                'name' => 'Concediu de odihnă',
                'color' => '#3c82f7',
                'background' => 'bg-blue-501',
                'sel_order' => 2
            ],
            [
                'code' => 'R',
                'name' => 'Recuperare',
                'color' => '#3c82f8',
                'background' => 'bg-blue-502',
                'sel_order' => 3
            ],
            [
                'code' => 'R*',
                'name' => 'Recuperare cu plată',
                'color' => '#3c82f8',
                'background' => 'bg-blue-502',
                'sel_order' => 4
            ],
            [
                'code' => 'LS',
                'name' => 'Liber după serviciu',
                'color' => '#3c82f9',
                'background' => 'bg-blue-503',
                'sel_order' => 5
            ],
            [
                'code' => 'CM',
                'name' => 'Concediu medical',
                'color' => '#3c82f10',
                'background' => 'bg-blue-504',
                'sel_order' => 6
            ],
            [
                'code' => 'CS',
                'name' => 'Concediu de studiu',
                'color' => '#3c82f11',
                'background' => 'bg-blue-505',
                'sel_order' => 7
            ],
            [
                'code' => 'CIC',
                'name' => 'Concediu pentru îngrijirea copilului',
                'color' => '#3c82f12',
                'background' => 'bg-blue-506',
                'sel_order' => 8
            ],
            [
                'code' => 'L',
                'name' => 'Zile libere fără plată',
                'color' => '#3c82f13',
                'background' => 'bg-blue-507',
                'sel_order' => 9
            ],
            [
                'code' => 'Î',
                'name' => 'Învoiri',
                'color' => '#3c82f14',
                'background' => 'bg-blue-508',
                'sel_order' => 10
            ],
            [
                'code' => 'P',
                'name' => 'Permisii',
                'color' => '#3c82f15',
                'background' => 'bg-blue-509',
                'sel_order' => 11
            ],
            [
                'code' => 'CP',
                'name' => 'Cursuri de pregătire',
                'color' => '#3c82f16',
                'background' => 'bg-blue-510',
                'sel_order' => 12
            ],
            [
                'code' => 'M',
                'name' => 'Misiuni în afara garnizoanei Câmpina',
                'color' => '#3c82f17',
                'background' => 'bg-blue-511',
                'sel_order' => 13
            ],
            [
                'code' => 'S',
                'name' => 'Seminar',
                'color' => '#3c82f18',
                'background' => 'bg-blue-512',
                'sel_order' => 14
            ],
            [
                'code' => 'D',
                'name' => 'Documentare',
                'color' => '#3c82f19',
                'background' => 'bg-blue-513',
                'sel_order' => 15
            ],
            [
                'code' => 'PR',
                'name' => 'Program redus',
                'color' => '#3c82f20',
                'background' => 'bg-blue-514',
                'sel_order' => 16
            ],
            [
                'code' => 'DS',
                'name' => 'Deplasări în străinătate',
                'color' => '#3c82f21',
                'background' => 'bg-blue-515',
                'sel_order' => 17
            ],
        ];

        foreach ($statuses as $status) {
            DB::table('schedule_statuses')->insert([
                'code' => $status['code'],
                'name' => $status['name'],
                'color' => $status['color'],
                'background' => $status['background'],
                'sel_order' => $status['sel_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::enableForeignKeyConstraints();
    }
}