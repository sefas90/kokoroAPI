<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\City;

class CitySeederTable extends Seeder{
    public function run() {
        City::create(['city' => 'La Paz']);
        City::create(['city' => 'Santa Cruz']);
        City::create(['city' => 'Cochabamba']);
        City::create(['city' => 'Oruro']);
        City::create(['city' => 'Potosi']);
        City::create(['city' => 'Pando']);
        City::create(['city' => 'Beni']);
        City::create(['city' => 'Tarija']);
        City::create(['city' => 'Chuquisaca']);
        City::create(['city' => 'Nacional']);
        City::create(['city' => 'Internacional']);
    }
}
