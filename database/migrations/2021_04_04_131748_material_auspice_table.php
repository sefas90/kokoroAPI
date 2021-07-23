<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MaterialAuspiceTable extends Migration {

    public function up() {
        Schema::create('auspice_materials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('material_name');
            $table->bigInteger('duration'); // in seconds
            $table->bigInteger('auspice_id')->unsigned();
            $table->bigInteger('auspice_id')->unsigned();
            $table->float('total_cost', 15, 2)->nullable()->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('auspice_id')->references('id')->on('auspices');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('auspice_materials');
    }
}
