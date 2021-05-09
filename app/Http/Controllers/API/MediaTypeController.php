<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaTypeController extends BaseController {
    public function index() {
        return $this->sendResponse(DB::table('media_types')
            ->select('id', 'id as value', 'media_type as label')
            ->get(), '');
    }
}
