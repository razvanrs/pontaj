<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'name' => "Claudiu Plesa",
            'email' => "claudiu.plesa@magicpixel.ro",
            'password' => bcrypt('claudiu123!'),
        ]);

        DB::table('users')->insert([
            'name' => "Razvan Stanescu",
            'email' => "razzvan19@yahoo.com",
            'password' => bcrypt('razvan1905!!'),
        ]);

        DB::table('users')->insert([
            'name' => "Marius Cirstea",
            'email' => "marius.cirstea@magicpixel.ro",
            'password' => bcrypt('marius123!'),
        ]);

        DB::table('users')->insert([
            'name' => "Ioana Constantin",
            'email' => "costin.ioana82@gmail.com",
            'password' => bcrypt('ioana123!'),
        ]);

        DB::table('users')->insert([
            'name' => "Anghel Bogdan",
            'email' => "angbogdan78@gmail.com",
            'password' => bcrypt('bogdan123!'),
        ]);

        DB::table('users')->insert([
            'name' => "Codrut Gratie",
            'email' => "andrey_codrut@yahoo.com",
            'password' => bcrypt('codrut123!'),
        ]);
    }
}
