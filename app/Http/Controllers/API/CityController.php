<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CityController extends BaseController {
    public function index() {
        return $this->sendResponse(DB::table('cities')
            ->select('id', 'id as value', 'city as label')
            ->get(), '');
    }
}
