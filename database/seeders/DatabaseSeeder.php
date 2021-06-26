<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run() {
        Model::unguard();
        $this->call([
            ResourcesTableSeeder::class,
            RolesTableSeeder::class,
            PermissionTableSeeder::class,
            UsersTableSeeder::class,
            CitySeederTable::class,
            MediaTypeSeederTable::class,
            CurrencySeeder::class,
            ]);
        Model::reguard();
    }
}
