<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(Role::where('name', 'Admin')->exists()){
            return;
        }
        Role::create([
            'name' => 'Admin'
        ]);
        Role::create([
            'name' => 'User'
        ]);
    }
}
