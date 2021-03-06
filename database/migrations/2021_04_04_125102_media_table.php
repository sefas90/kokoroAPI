<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MediaTable extends Migration {

    public function up() {
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->string('media_name');
            $table->string('business_name');
            $table->string('NIT', 13);
            $table->bigInteger('city_id')->unsigned();
            $table->bigInteger('media_type')->unsigned();
            $table->bigInteger('media_parent_id')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('city_id')->references('id')->on('cities');
            $table->foreign('media_type')->references('id')->on('media_types');
            $table->foreign('media_parent_id')->references('id')->on('media');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('media');
    }
}
