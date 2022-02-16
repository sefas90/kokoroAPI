<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Kokoro2022 extends Migration {
    public function up() {
        Schema::table('guides', function (Blueprint $table) {
            $table->float('cost', 15)->after('billing_number');
            $table->boolean('manual_apportion')->nullable()->default(0)->after('cost');
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->float('total_cost', 15)->after('duration');
        });

        /*Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn('guide_id');
            $table->dropForeign('guide_id');
        });*/
    }
}
