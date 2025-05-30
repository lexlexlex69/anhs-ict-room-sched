<?php

namespace Database\Seeders;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([

            'first_name' =>'Admin',
            'email' =>'admin@gmail.com',
            'password' => bcrypt('1234'),
            'user_type' =>'1'
        ]);
    }
}
