<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class ClientController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('clients')
            ->select('id', 'client_name as clientName', 'NIT', 'representative', 'billing_policies as billingPolicies', 'billing_address as billingAddress')
            ->where('deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'clients.id' : 'clients.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'clientName'    => 'required',
            'representative' => 'required',
            'NIT'            => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $policies = empty(!$request->billingPolicies) ? $request->billingPolicies
            : 'NIT: ' . $request->NIT . ' Nombre: ' . $request->representative;

        $client = new Client(array(
            'client_name'       => trim($request->clientName),
            'representative'    => trim($request->representative),
            'NIT'               => trim($request->NIT),
            'billing_policies'  => trim($policies),
            'billing_address'   => trim($request->billingAddress),
        ));

        return $client->save() ?
            $this->sendResponse('', 'El cliente ' . $client->client_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo cliente.');
    }

    public function show($id) {
        $client = Client::find($id);
        if (!$client) {
            return $this->sendError('No se contro el cliente');
        }
        return $this->sendResponse($client, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'clientName'     => 'required',
            'representative' => 'required',
            'NIT'            => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $client = Client::find($id);
        if (!$client) {
            return $this->sendError('No se encontro el cliente');
        }

        $client->client_name        = trim($request->clientName);
        $client->representative     = trim($request->representative);
        $client->NIT                = trim($request->NIT);
        $client->billing_policies   = trim($request->billingPolicies);
        $client->billing_address    = trim($request->billingAddress);

        return $client->save() ?
            $this->sendResponse('', 'El cliente ' . $client->client_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el cliente ' . $client->client_name . '.');
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

    public function list() {
        return $this->sendResponse(DB::table('clients')
            ->select('id', 'id as value', 'client_name as label')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
