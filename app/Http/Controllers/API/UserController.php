<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends BaseController {
    public function index () {
        if (Auth::check()) {
            $users = User::all();
            return $this->sendResponse($users);
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function store (Request $request) {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'name'     => 'required',
                'lastname' => 'required',
                'username' => 'required',
                'password' => 'required',
                'role_id'  => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $user = new User(array(
                'name'     => trim($request->name),
                'lastname' => trim($request->lastname),
                'username' => trim($request->username),
                'password' => trim($request->password),
                'role_id'  => trim($request->role_id)
            ));

            return $user->save() ?
                $this->sendResponse('', 'El usuario ' . $user->username . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo usuario');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function show ($id) {
        if (Auth::check()) {
            $user = User::find($id);
            if (!$user) {
                return $this->sendError('No se encontro el usuario');
            }
            return $this->sendResponse($user);
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function update (Request $request, $id) {
        if (Auth::check()) {
            $user = User::find($id);
            if (!$user) {
                return $this->sendError('No se encontro el usuario');
            }

            $validator = Validator::make($request->all(), [
                'name'     => 'required',
                'lastname' => 'required',
                'username' => 'required',
                'password' => 'required',
                'role_id'  => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $user->name     = trim($request->name);
            $user->lastname = trim($request->lastname);
            $user->username = trim($request->username);
            $user->email    = trim($request->email);
            $user->role_id  = trim($request->rol['id']);

            return $user->save() ?
                $this->sendResponse('', 'El usuario ' . $user->username . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al modificar un usuario');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function destroy ($id) {
        if (Auth::check()) {
            $user = User::find($id);
            if (!$user) {
                return $this->sendError('No se encontro el usuario');
            }
            return $user->delete() ?
                $this->sendResponse('', 'La campaña ' . $user->user_name . ' se elimino correctamente.') :
                $this->sendError('Ocurrio un error al eliminar un usuario');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function changePassword (request $request, $id) {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'newPassword'       => 'required',
                'newPasswordConfirm'=> 'required'
            ]);

            if($validator->fails() || $request->newPassword !== $request->newPasswordConfirm){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $user = User::find($id);
            if (!$user) {
                return $this->sendError('No se encontro el usuario');
            }

            $user->password = bcrypt($request->newPassword);

            return $user->save() ?
                $this->sendResponse('', 'El usuario ' . $user->username . ' se actualizo correctamente la nueva contraseña') :
                $this->sendError('Ocurrio un error al modificar un usuario');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function changeMyPassword (request $request) {
        if (Auth::check()) {
            $validator = Validator::make($request->all(), [
                'newPassword'       => 'required',
                'newPasswordConfirm'=> 'required'
            ]);

            if($validator->fails() || $request->newPassword !== $request->newPasswordConfirm){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $user = User::find(Auth::id());
            if (!$user) {
                return $this->sendError('No se encontro el usuario');
            }

            $user->password = bcrypt($request->newPassword);

            return $user->save() ?
                $this->sendResponse('', 'El usuario ' . $user->username . ' se actualizo correctamente la nueva contraseña') :
                $this->sendError('Ocurrio un error al modificar un usuario');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }
}
