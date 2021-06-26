<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder {
    public function run(){
        Currency::create(['currency_value' => '6.96', 'currency_name' => 'Dolar', 'symbol' => '$us']);
    }
}
