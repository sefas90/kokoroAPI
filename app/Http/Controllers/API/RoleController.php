<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController {
    public function index () {
        $role = Role::select('id', 'id as value', 'role as label')
            ->where('id', '>', 1)
            ->get();
        return $this->sendResponse($role);
    }
}
