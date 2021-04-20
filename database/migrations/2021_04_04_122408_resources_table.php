<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ResourcesTable extends Migration {
    public function up() {
        Schema::create('resources', function (Blueprint $table) {
            $table->bigIncrements('id')->index();
            $table->string('name');
            $table->integer('level');
            $table->string('url');
            $table->boolean('read_visible');
            $table->boolean('write_visible');
            $table->boolean('create_visible');
            $table->boolean('delete_visible');
            $table->boolean('execute_visible');
            $table->bigInteger('parent_id')->unsigned()->nullable();
            $table->foreign('parent_id')->references('id')->on('resources');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('resources');
    }
}
