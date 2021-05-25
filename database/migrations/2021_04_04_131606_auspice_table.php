<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AuspiceTable extends Migration {

    public function up() {
        Schema::create('auspices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('auspice_name');
            $table->float('cost');
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
        Schema::dropIfExists('auspices');
    }
}
