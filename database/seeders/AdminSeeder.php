<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'alaa Super admin',
            'email' =>'admin@gmail.com',
            'password'=> 'Aa.123',
            'role_id' => 1,
            'email_verified_at' => now(),
        ]);
    }
}
