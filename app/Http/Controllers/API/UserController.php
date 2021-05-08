<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class UserController extends BaseController {
    public function index () {
        $users = User::all();
        return $this->sendResponse($users);
    }

    public function store (Request $request) {
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
    }

    public function show ($id) {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('No se encontro el usuario');
        }
        return $this->sendResponse($user);
    }

    public function update (Request $request, $id) {
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
    }

    public function destroy ($id) {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('No se encontro el usuario');
        }
        return $user->delete() ?
            $this->sendResponse('', 'La campaña ' . $user->user_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un usuario');
    }

    public function changePassword (request $request, $id) {
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
    }

    public function changeMyPassword (request $request) {
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
    }
}
