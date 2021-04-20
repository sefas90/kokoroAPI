<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder {
    public function run () {
        Role::create([
            'id' => 1,
            'role' => 'NONE',
            'description' => 'NONE',
            'created_at' => null,
            'updated_at' => null,
            'deleted_at' => null
        ]);
        Role::create([
            'id' => 2,
            'role' => 'Admin',
            'description' => 'System Administrator',
            'created_at' => null,
            'updated_at' => null,
            'deleted_at' => null
        ]);
        Role::create([
            'id' => 3,
            'role' => 'User',
            'description' => 'Normal User',
            'created_at' => null,
            'updated_at' => null,
            'deleted_at' => null
        ]);
    }
}
