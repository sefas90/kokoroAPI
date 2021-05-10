<?php

namespace App\Http\Controllers\API;

use App\Models\Material;
use App\Models\PlaningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class MaterialController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('materials')
            ->select('materials.id', 'material_name as materialName', 'duration', 'rates.show', 'guides.guide_name as guideName', 'guides.id as guideId',
                'rates.id as rateId', 'rates.cost')
            ->join('guides', 'guides.id', '=', 'materials.guide_id')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->where('materials.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'materials.id' : 'materials.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'materialName' => 'required',
            'duration'     => 'required',
            'guideId'      => 'required',
            'rateId'       => 'required',
            'timesPerDay'  => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $material = new Material(array(
            'material_name' => trim($request->materialName),
            'duration'      => trim($request->duration),
            'guide_id'      => trim($request->guideId),
            'rate_id'       => trim($request->rateId),
        ));

        if($material->save())  {
            $success = false;
            foreach ($request['timesPerDay'] as $key => $row) {
                $materialPlaning = new PlaningMaterial(array(
                    'material_id' => $material['id'],
                    'times_per_day' => $row['timesPerDay'],
                    'broadcast_day' => $row['date']
                ));

                if ($materialPlaning->save()) {
                    $success = true;
                } else {
                    $success = false;
                }
            }
            return $success ?
                $this->sendResponse('', 'El material ' . $material->material_name . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo material.');
        } else {
            return $this->sendError('Ocurrio un error al crear un nuevo material.');
        }
    }

    public function show($id) {
        $material = Material::find($id);
        if (!$material) {
            return $this->sendError('No se contro el material');
        }
        return $this->sendResponse($material, '');
    }

    public function update(Request $request, $id) {
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
