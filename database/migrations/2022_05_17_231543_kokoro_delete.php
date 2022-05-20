<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class KokoroDelete extends Migration {
    public function up() {
        Schema::dropIfExists('material_auspice_planing');
        Schema::dropIfExists('auspice_materials');
        Schema::dropIfExists('auspices');
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn('product');
        });
    }

    public function down() { }
}
