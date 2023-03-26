<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Hash;
use DB;
class AkunKeuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         DB::table('users')->insert([
            'username' => 'adminkeuangan',
            'password' => Hash::make('dikerja'),
            'role' => 'keuangan',
        ]);
    }
}
