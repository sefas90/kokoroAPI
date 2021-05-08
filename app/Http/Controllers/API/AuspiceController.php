<?php

namespace App\Http\Controllers\API;

use App\Models\Auspice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class AuspiceController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('auspices')
            ->select('auspices.id', 'auspice_name as auspiceName', 'cost', 'duration', 'client_name as clientName')
            ->join('clients', 'clients.id', '=', 'auspices.client_id')
            ->where('auspices.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'auspices.id' : 'auspices.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'auspice_name' => 'required',
            'cost' => 'required',
            'duration' => 'required',
            'client_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $auspice = new Auspice(array(
            'auspice_name' => trim($request->auspice_name),
            'cost'         => trim($request->cost),
            'duration'     => trim($request->duration),
            'client_id'    => trim($request->client_id)
        ));

        return $auspice->save() ?
            $this->sendResponse('', 'El auspicee ' . $auspice->auspice_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo auspicee.');
    }

    public function show($id) {
        $auspice = Auspice::find($id);
        if (!$auspice) {
            return $this->sendError('No se contro el auspicee');
        }
        return $this->sendResponse($auspice, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'auspice_name' => 'required',
            'cost'         => 'required',
            'duration'     => 'required',
            'client_id'    => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $auspice = Auspice::find($id);
        if (!$auspice) {
            return $this->sendError('No se encontro el auspicee');
        }

        $auspice->auspice_name = trim($request->auspice_name);
        $auspice->cost         = trim($request->cost);
        $auspice->duration     = trim($request->duration);
        $auspice->client_id    = trim($request->client_id);

        return $auspice->save() ?
            $this->sendResponse('', 'El auspicee ' . $auspice->auspice_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el auspicee ' . $auspice->auspice_name . '.');
    }

    public function destroy($id) {
        $auspice = Auspice::find($id);
        if (!$auspice) {
            return $this->sendError('No se encontro el auspicio');
        }

        return $auspice->delete() ?
            $this->sendResponse('', 'El auspicio ' . $auspice->auspice_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un auspicio');
    }
}
