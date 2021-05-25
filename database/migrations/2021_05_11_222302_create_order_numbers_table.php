<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderNumbersTable extends Migration {
    public function up() {
        Schema::create('order_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('order_number');
            $table->string('version');
            $table->string('observation')->nullable();
            $table->bigInteger('guide_id');
            $table->foreign('guide_id')->references('id')->on('guides');
            $table->timestamps();
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('order_numbers');
    }
}
