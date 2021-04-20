<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaterialPlaningTable extends Migration {

    public function up() {
        Schema::create('media_planing', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('broadcast_day');
            $table->integer('times_per_day');
            $table->bigInteger('media_id')->unsigned();
            $table->foreign('media_id')->references('id')->on('media');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('media_planing');
    }
}
