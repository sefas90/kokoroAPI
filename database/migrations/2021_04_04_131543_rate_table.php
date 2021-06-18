<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RateTable extends Migration {

    public function up(){
        Schema::create('rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('show');
            $table->bigInteger('media_id')->unsigned();
            $table->time('hour_ini');
            $table->time('hour_end');
            $table->boolean('brod_mo');
            $table->boolean('brod_tu');
            $table->boolean('brod_we');
            $table->boolean('brod_th');
            $table->boolean('brod_fr');
            $table->boolean('brod_sa');
            $table->boolean('brod_su');
            $table->float('cost', 15, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('media_id')->references('id')->on('media');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('rates');
    }
}
