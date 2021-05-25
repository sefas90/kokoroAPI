<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaterialAuspicePlaningTable extends Migration {

    public function up() {
        Schema::create('material_auspice_planing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('broadcast_day');
            $table->integer('times_per_day');
            $table->bigInteger('material_auspice_id')->unsigned();
            $table->foreign('material_auspice_id')->references('id')->on('auspice_materials');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('material_auspice_planing');
    }
}
