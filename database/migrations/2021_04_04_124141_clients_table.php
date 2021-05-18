<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClientsTable extends Migration {

    public function up() {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_name', 50);
            $table->string('representative', 50);
            $table->string('NIT', 11);
            $table->string('billing_address', 11);
            $table->string('billing_policies', 11);
            $table->timestamps();
            $table->softDeletes();
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('clients');
    }
}
