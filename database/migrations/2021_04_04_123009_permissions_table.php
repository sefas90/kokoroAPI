<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PermissionsTable extends Migration {
    public function up() {
        Schema::create('permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('pread');
            $table->boolean('pwrite');
            $table->boolean('pcreate');
            $table->boolean('pdelete');
            $table->boolean('pexecute');
            $table->bigInteger('role_id')->unsigned();
            $table->bigInteger('resource_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('resource_id')->references('id')->on('resources');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('permissions');
    }
}
