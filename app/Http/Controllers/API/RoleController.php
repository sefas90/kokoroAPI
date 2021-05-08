<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController {
    public function index () {
        $role = Role::all();
        return $this->sendResponse($role);
    }
}
