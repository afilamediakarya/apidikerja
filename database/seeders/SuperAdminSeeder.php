<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id_pegawai' => NULL,
            'username' => 'super_admin',
            'email' => 'admin@admin.com',
            'role' => 'super_admin',
            'password' => Hash::make('password'),
        ]);  
    }
}
