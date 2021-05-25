<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaterialAuspiceTable extends Migration {

    public function up() {
        Schema::create('auspice_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('material_name');
            $table->bigInteger('duration'); // in seconds
            $table->bigInteger('guide_id')->unsigned();
            $table->bigInteger('auspice_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('guide_id')->references('id')->on('guides');
            $table->foreign('auspice_id')->references('id')->on('auspices');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('auspice_materials');
    }
}
