<?php

namespace App\Http\Controllers\API;

use App\Models\Material;
use App\Models\PlaningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class MaterialController extends BaseController {
    public function index (Request $request) {

        $guideId = $request->guideId;
        $where = [
            ['materials.deleted_at', '=', null]
        ];
        if ($guideId) {
            $guide = ['guides.id', '=', $guideId];
            array_push($where, $guide);
        }
        $result = DB::table('materials')
            ->select('materials.id', 'material_name as materialName', 'duration', 'rates.show', 'guides.guide_name as guideName', 'guides.id as guideId',
                'rates.id as rateId', 'rates.cost', 'media_types.media_type as mediaType', 'guides.editable as isEditable', 'media_types.id as mediaTypeId')
            ->join('guides', 'guides.id', '=', 'materials.guide_id')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where($where)
            ->get();

            foreach ($result as $key => $row) {
                $material = DB::table('material_planing')
                    ->where('material_id', '=', $row->id)
                    ->sum('times_per_day');

                $material_planing = DB::table('material_planing')
                    ->where('material_id', '=', $row->id)->get();

                $result[$key]->passes = (int)$material;
                $aux = [];
                foreach ($material_planing as $k => $r) {
                    $aux[$r->broadcast_day] = [
                        'date' => date('Y-m-d h:i:s', strtotime($r->broadcast_day)),
                        'timesPerDay' => $r->times_per_day
                    ];
                }
                $result[$key]->timesPerDay = $aux;
            }
        return $this->sendResponse($result);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'materialName' => 'required',
            'guideId'      => 'required',
            'rateId'       => 'required',
            'duration'     => 'numeric|gt:0',
            'timesPerDay'  => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $material = new Material(array(
            'material_name' => trim($request->materialName),
            'duration'      => empty($request->duration) ? 0 : trim($request->duration),
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
            'materialName' => 'required',
            'guideId'      => 'required',
            'rateId'       => 'required',
            'duration'     => 'numeric|gt:0',
            'timesPerDay'  => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $material = Material::find($id);
        if (!$material) {
            return $this->sendError('No se encontro el material');
        }

        $material->material_name = trim($request->materialName);
        $material->duration      = empty($request->duration) ? 0 : trim($request->duration);
        $material->guide_id      = trim($request->guideId);
        $material->rate_id       = trim($request->rateId);

        if($material->save())  {
            $success = false;

            $post = PlaningMaterial::where('material_id', $id)->get();

            foreach ($post as $pos => $rew) {
                $material_planing = PlaningMaterial::find($rew->id);
                $material_planing->delete();
            }

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
                $this->sendResponse('', 'El material ' . $material->material_name . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo material.');
        } else {
            return $this->sendError('Ocurrio un error al actualizar un nuevo material.');
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
