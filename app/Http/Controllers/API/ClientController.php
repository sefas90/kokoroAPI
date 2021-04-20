<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class ClientController extends BaseController {
    public function index (Request $request) {
        if (Auth::check()) {
            $sort = explode(":", $request->sort);
            return $this->sendResponse(DB::table('clients')
                ->select('id', 'client_name as clientName', 'NIT')
                ->where('deleted_at', '=', null)
                ->orderBy(empty($sort[0]) ? 'clients.id' : 'clients.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function store(Request $request) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'client_name' => 'required',
                'NIT'         => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $client = new Client(array(
                'client_name' => trim($request->client_name),
                'NIT'         => trim($request->NIT)
            ));

            return $client->save() ?
                $this->sendResponse('', 'El cliente ' . $client->client_name . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo cliente.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function show($id) {
        if (Auth::check()){
            $client = Client::find($id);
            if (!$client) {
                return $this->sendError('No se contro el cliente');
            }
            return $this->sendResponse($client, '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function update(Request $request, $id) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'client_name' => 'required',
                'NIT' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $client = Client::find($id);
            if (!$client) {
                return $this->sendError('No se encontro el cliente');
            }

            $client->client_name = trim($request->client_name);
            $client->NIT         = trim($request->NIT);

            return $client->save() ?
                $this->sendResponse('', 'El cliente ' . $client->client_name . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al actualizar el cliente ' . $client->client_name . '.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function destroy($id) {
        $client = Client::find($id);
        if (!$client) {
            return $this->sendError('No se encontro el cliente');
        }

        return $client->delete() ?
            $this->sendResponse('', 'El cliente ' . $client->client_name . ' se elimino correctamente.') :
            $this->sendError('El cliente ' . $client->client_name .' no se pudo eliminar.');
    }
}
