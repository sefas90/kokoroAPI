<?php

namespace App\Http\Controllers\API;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialController extends BaseController {
    public function index (Request $request) {
        if (Auth::check()) {
            $sort = explode(":", $request->sort);
            return $this->sendResponse(DB::table('material')
                ->select('id', 'material_name as materialName', 'duration', 'rates.show', 'guide_name')
                ->join('guides', 'guides.id', '=', 'material.guide_id')
                ->join('rates', 'rates.id', '=', 'material.guide_id')
                ->where('deleted_at', '=', null)
                ->orderBy(empty($sort[0]) ? 'material.id' : 'material.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function store(Request $request) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'material_name' => 'required',
                'NIT'         => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $material = new Material(array(
                'material_name' => trim($request->material_name),
                'NIT'         => trim($request->NIT)
            ));

            return $material->save() ?
                $this->sendResponse('', 'El material ' . $material->material_name . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo material.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function show($id) {
        if (Auth::check()){
            $material = Material::find($id);
            if (!$material) {
                return $this->sendError('No se contro el material');
            }
            return $this->sendResponse($material, '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function update(Request $request, $id) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'material_name' => 'required',
                'NIT' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $material = Material::find($id);
            if (!$material) {
                return $this->sendError('No se encontro el material');
            }

            $material->material_name = trim($request->material_name);
            $material->NIT         = trim($request->NIT);

            return $material->save() ?
                $this->sendResponse('', 'El material ' . $material->material_name . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al actualizar el material ' . $material->material_name . '.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function destroy($id) {
        $material = Material::find($id);
        if (!$material) {
            return $this->sendError('No se encontro el material');
        }

        return $material->delete() ?
            $this->sendResponse('', 'El material ' . $material->material_name . ' se elimino correctamente.') :
            $this->sendError('El material ' . $material->material_name .' no se pudo eliminar.');
    }
}
