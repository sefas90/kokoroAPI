<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);
            $table->string('lastname', 30);
            $table->string('username', 30)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->bigInteger('role_id')->unsigned();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('users');
    }
}
