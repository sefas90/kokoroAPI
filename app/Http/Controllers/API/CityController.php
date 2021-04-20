<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CityController extends BaseController {
    public function index() {
        if (Auth::check()) {
            return $this->sendResponse(DB::table('campaign')
                ->select('id', 'city')
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }
}
