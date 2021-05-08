<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionController extends BaseController {
    public function permissions () {
        $user = User::findOrFail(Auth::id());
        $resources = DB::table('permissions')
            ->select('permissions.id as id', 'resources.id as resource_id', 'resources.name', 'pexecute', 'parent_id', 'url')
            ->join('resources', 'resources.id', '=', 'permissions.resource_id')
            ->where([
                ['role_id', $user->role_id],
                ['pexecute', 1],
                ['parent_id', 1]
            ])
            ->get();
        $count = 0;
        foreach ($resources as $resource) {
            $child = DB::table('permissions')
                ->select('permissions.id as id', 'resources.id as resource_id', 'name', 'pread', 'pwrite', 'pcreate', 'pdelete', 'pexecute', 'parent_id', 'url')
                ->join('resources', 'resources.id', '=', 'permissions.resource_id')
                ->where('parent_id', $resource->resource_id)
                ->where('role_id', $user->role_id)
                ->where('pexecute', 1)
                ->get();
            $resources[$count]->children = $child;
            $count++;
        }
        return $this->sendResponse($resources);
    }
}
