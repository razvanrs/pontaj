<?php

namespace Database\Seeders;

use App\Models\Sanction;
use App\Models\Student;
use App\Models\StudentSanction;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Testing\Fakes\Fake;

class StudentSanctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentSanction::truncate();

        StudentSanction::factory()->count(300)->create();
    }
}
