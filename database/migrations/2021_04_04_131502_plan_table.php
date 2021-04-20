<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PlanTable extends Migration {

    public function up() {
        Schema::create('plan', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('plan_name');
            $table->bigInteger('client_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('plan');
    }
}
