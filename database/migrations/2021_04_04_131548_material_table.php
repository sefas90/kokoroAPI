<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaterialTable extends Migration {

    public function up() {
        Schema::create('materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('material_name');
            $table->bigInteger('duration'); // in seconds
            $table->float('total_cost', 15)->after('duration');
            $table->bigInteger('guide_id')->unsigned();
            $table->bigInteger('rate_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('guide_id')->references('id')->on('guides');
            $table->foreign('rate_id')->references('id')->on('rates');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('materials');
    }
}
