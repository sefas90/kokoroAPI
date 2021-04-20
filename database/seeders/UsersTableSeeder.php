<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersTableSeeder extends Seeder {
    public function run () {
        User::create([
            'name'          => 'System',
            'lastname'     => 'Administrator',
            'username'      => 'adminc',
            'email'         => 'admin@admin.com',
            'password'      => bcrypt('Control123'),
            'role_id'       => 2,
            'created_at'    => null,
            'updated_at'    => null,
            'deleted_at'    => null
        ]);
        User::create([
            'name'          => 'Normal',
            'lastname'     => 'User',
            'username'      => 'user',
            'email'         => 'noadmin@admin.com',
            'password'      => bcrypt('Noadmin123'),
            'role_id'       => 3,
            'created_at'    => null,
            'updated_at'    => null,
            'deleted_at'    => null
        ]);
    }
}
