<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kokoro2022_3 extends Migration {
    public function up() {
        Schema::table('material_auspice_planing', function (Blueprint $table) {
            $table->dropForeign('material_auspice_id');
        });
        Schema::dropIfExists('material_auspice_planing');

        Schema::table('auspice_materials', function (Blueprint $table) {
            $table->dropForeign('auspice_id');
        });
        Schema::dropIfExists('auspice_materials');

        Schema::table('auspices', function (Blueprint $table) {
            $table->dropForeign('guide_id');
            $table->dropForeign('rate_id');
        });
        Schema::dropIfExists('auspices');

    }
}
