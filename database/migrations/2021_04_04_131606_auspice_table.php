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
            $table->bigInteger('duration'); // in seconds
            $table->bigInteger('client_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('auspices');
    }
}
