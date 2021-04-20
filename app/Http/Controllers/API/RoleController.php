<?php

namespace App\Http\Controllers\API;

use App\Models\Role;
use Illuminate\Support\Facades\Auth;

class RoleController extends BaseController {
    public function index () {
        if (Auth::check()) {
            $role = Role::all();
            return $this->sendResponse($role);
        } else {
            return $this->sendError('No esta autenticado');
        }
    }
}
