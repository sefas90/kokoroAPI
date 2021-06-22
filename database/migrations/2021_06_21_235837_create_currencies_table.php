<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration {

    public function up() {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('currency_name');
            $table->string('currency_value');
            $table->string('symbol');
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('currencies');
    }
}
