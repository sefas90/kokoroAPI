<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CampaignTable extends Migration {

    public function up() {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('campaign_name');
            $table->dateTime('date_ini');
            $table->dateTime('date_end');
            $table->bigInteger('plan_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('plan_id')->references('id')->on('plan');
            $table->engine = 'InnoDB';
        });
    }

    public function down() {
        Schema::dropIfExists('campaigns');
    }
}
