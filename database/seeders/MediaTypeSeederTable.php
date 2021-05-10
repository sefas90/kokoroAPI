<?php

namespace Database\Seeders;

use App\Models\MediaType;
use Illuminate\Database\Seeder;

class MediaTypeSeederTable extends Seeder {
    public function run() {
        MediaType::create(['media_type' => 'TV']);
        MediaType::create(['media_type' => 'Radio']);
        MediaType::create(['media_type' => 'Impresos']);
        MediaType::create(['media_type' => 'TV paga']);
        MediaType::create(['media_type' => 'Digital']);
        MediaType::create(['media_type' => 'Vía Pública']);
        MediaType::create(['media_type' => 'Otros Medios']);
    }
}
