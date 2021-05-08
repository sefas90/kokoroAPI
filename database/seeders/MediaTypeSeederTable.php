<?php

namespace Database\Seeders;

use App\Models\MediaType;
use Illuminate\Database\Seeder;

class MediaTypeSeederTable extends Seeder {
    public function run() {
        MediaType::create(['media_type' => 'TV']);
        MediaType::create(['media_type' => 'Radio']);
    }
}
