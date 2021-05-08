<?php

namespace App\Http\Controllers\API;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class ResourceController extends BaseController {
    public function index () {
        $resources = DB::table('resources')
            ->where('parent_id', 1)
            ->get();
        $count = 0;
        foreach ($resources as $resource) {
            $child = DB::table('resources')->where('parent_id', $resource->id)->get();
            $resources[$count]->children = $child;
            $count++;
        }
        return $this->sendResponse($resources, '');
    }
}
