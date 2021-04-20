<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CitiesTable extends Migration {

    public function up() {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('city');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('cities');
    }
}
