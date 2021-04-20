<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MediaTypesTable extends Migration {

    public function up() {
        Schema::create('media_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('media_type');
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('media_types');
    }
}
