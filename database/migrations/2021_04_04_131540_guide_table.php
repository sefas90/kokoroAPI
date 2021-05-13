<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GuideTable extends Migration {

    public function up() {
        Schema::create('guides', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('guide_name');
            $table->dateTime('date_ini');
            $table->dateTime('date_end');
            $table->bigInteger('media_id')->unsigned();
            $table->bigInteger('campaign_id')->unsigned();
            $table->boolean('editable');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('campaign_id')->references('id')->on('campaigns');
            $table->foreign('media_id')->references('id')->on('media');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('guides');
    }
}
