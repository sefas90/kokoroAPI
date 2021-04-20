<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaTypeController extends BaseController {
    public function index() {
        if (Auth::check()) {
            return $this->sendResponse(DB::table('media_types')
                ->select('id', 'media_type as mediaType')
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }
}
