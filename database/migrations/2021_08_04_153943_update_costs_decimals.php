<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCostsDecimals extends Migration {

    public function up() {
        Schema::table('rates', function (Blueprint $table) {
            $table->float('cost', 15, 8)->change();
        });
        Schema::table('auspices', function (Blueprint $table) {
            $table->float('cost', 15, 8)->change();
        });
        Schema::table('auspice_materials', function (Blueprint $table) {
            $table->float('total_cost', 15, 8)->change();
        });
    }
}
