<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kokoro2022 extends Migration {
    public function up() {
        Schema::table('guides', function (Blueprint $table) {
            $table->string('product')->default('')->after('billing_number');
            $table->float('cost', 15)->nullable()->default(0)->after('product');
            $table->boolean('manual_apportion')->nullable()->default(1)->after('cost');
            $table->bigInteger('guide_parent_id')->unsigned()->nullable()->after('manual_apportion');
            $table->foreign('guide_parent_id')->references('id')->on('guides');
            $table->engine = 'InnoDB';
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 30)->unique()->change();
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->float('total_cost', 15)->nullable()->default(0)->after('duration');
        });
    }
}
