<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaterialPlaningTable extends Migration {

    public function up() {
        Schema::create('material_planing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('broadcast_day');
            $table->integer('times_per_day');
            $table->bigInteger('material_id')->unsigned();
            $table->foreign('material_id')->references('id')->on('materials');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('material_planing');
    }
}
