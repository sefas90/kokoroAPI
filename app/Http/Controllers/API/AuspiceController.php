<?php

namespace App\Http\Controllers\API;

use App\Models\Auspice;
use App\Models\AuspiceMaterial;
use App\Models\OrderNumber;
use App\Models\PlaningAuspiceMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class AuspiceController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        $auspice = DB::table('auspices')
            ->select('auspices.id', 'auspice_name as auspiceName', 'auspices.cost', 'rates.show', 'rates.id as rateId', 'guides.guide_name as guideName', 'guides.id as guideId', 'guides.editable',
                'guides.date_ini as dateIni', 'guides.date_end as dateEnd', 'brod_mo', 'brod_tu', 'brod_we', 'brod_th', 'brod_fr', 'brod_sa', 'brod_su', 'media_types.media_type as mediaType', 'clients.client_name as clientName')
            ->join('rates', 'rates.id', '=', 'auspices.rate_id')
            ->join('guides', 'guides.id', '=', 'auspices.guide_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where([
                ['auspices.deleted_at', '=', null]
            ])
            ->orderBy(empty($sort[0]) ? 'auspices.id' : 'auspices.'.$sort[0], empty($sort[1]) ? 'desc' : $sort[1])
            ->get();

        foreach ($auspice as $key => $row) {
            $orderNumber = OrderNumber::where('guide_id', '=', $row->guideId)->get();

            $number = 'Orden no exportada';
            if (count($orderNumber) > 0) {
                $orderNumber = $orderNumber[0];
                if ($orderNumber->order_number) {
                    $number = $orderNumber->order_number.'.'.$orderNumber->version;
                }
            }
            $auspice[$key]->orderNumber = $number;
        }

        return $this->sendResponse($auspice);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'auspiceName' => 'required',
            'cost'         => 'required',
            'guideId'     => 'required',
            'rateId'      => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $auspice = new Auspice(array(
            'auspice_name' => trim($request->auspiceName),
            'cost'         => trim($request->cost),
            'guide_id'     => trim($request->guideId),
            'rate_id'      => trim($request->rateId)
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
            'auspiceName' => 'required',
            'cost'         => 'required',
            'guideId'     => 'required',
            'rateId'      => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $auspice = Auspice::find($id);
        if (!$auspice) {
            return $this->sendError('No se encontro el auspicee');
        }

        $auspice->auspice_name = trim($request->auspiceName);
        $auspice->cost         = trim($request->cost);
        $auspice->guide_id     = trim($request->guideId);
        $auspice->rate_id      = trim($request->rateId);

        return $auspice->save() ?
            $this->sendResponse('', 'El auspicee ' . $auspice->auspice_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el auspicee ' . $auspice->auspice_name . '.');
    }

    public function auspiceMaterial(Request $request) {
        $validator = Validator::make($request->all(), [
            'materialName' => 'required',
            'auspiceId'    => 'required',
            'timesPerDay'  => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $material = new AuspiceMaterial(array(
            'material_name' => trim($request->materialName),
            'duration'      => empty($request->duration) ? 0 : trim($request->duration),
            'auspice_id'    => trim($request->auspiceId)
        ));

        if($material->save())  {
            $success = false;
            foreach ($request['timesPerDay'] as $key => $row) {
                $materialPlaning = new PlaningAuspiceMaterial(array(
                    'material_auspice_id'   => $material['id'],
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

    public function updateMaterial(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'materialName' => 'required',
            'auspiceId'    => 'required',
            'timesPerDay'  => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $material = AuspiceMaterial::find($id);
        if (!$material) {
            return $this->sendError('No se encontro el auspicee');
        }

        $material->material_name = trim($request->materialName);
        $material->duration      = empty($request->duration) ? 0 : trim($request->duration);
        $material->auspice_id    = trim($request->auspiceId);

        if($material->save())  {
            $success = false;

            $post = PlaningAuspiceMaterial::where('material_auspice_id', $id)->get();

            foreach ($post as $pos => $rew) {
                $material_planing = PlaningAuspiceMaterial::find($rew->id);
                $material_planing->delete();
            }

            foreach ($request['timesPerDay'] as $key => $row) {
                $materialPlaning = new PlaningAuspiceMaterial(array(
                    'material_auspice_id'   => $material['id'],
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

    public function getAuspiceMaterial($id) {
        $aus = Auspice::where('auspices.id', '=', $id)
            ->select('auspices.id', 'auspice_name as auspiceName', 'auspices.cost', 'rates.show', 'guides.guide_name as guideName',
                'guides.date_ini as dateIni', 'guides.date_end as dateEnd', 'brod_mo', 'brod_tu', 'brod_we', 'brod_th', 'brod_fr', 'brod_sa', 'brod_su', 'media_types.media_type as mediaType')
            ->join('rates', 'rates.id', '=', 'auspices.rate_id')
            ->join('guides', 'guides.id', '=', 'auspices.guide_id')
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->get();

        $aus = $aus[0];
        $auspice = AuspiceMaterial::select('id', 'material_name as materialName', 'duration', 'auspice_id as auspiceId')->where('auspice_id', '=', $id)->get();
        if (!$auspice) {
            return $this->sendResponse([]);
        }

        foreach ($auspice as $key => $row) {
            $material = DB::table('material_auspice_planing')
                ->where('material_auspice_id', '=', $row->id)
                ->sum('times_per_day');

            $material_planing = DB::table('material_auspice_planing')
                ->where('material_auspice_id', '=', $row->id)->get();

            $auspice[$key]->passes = (int)$material;
            $auspice[$key]->cost = number_format($aus->cost / count($auspice), 2, '.', '');
            $auspice[$key]->mediaType = $aus->mediaType;
            $aux = [];
            foreach ($material_planing as $k => $r) {
                $aux[$r->broadcast_day] = [
                    'date' => date('Y-m-d h:i:s', strtotime($r->broadcast_day)),
                    'timesPerDay' => $r->times_per_day
                ];
            }
            $auspice[$key]->timesPerDay = $aux;
        }
        return $this->sendResponse($auspice);
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

    public function deleteAuspiceMaterial($id) {
        $auspice = AuspiceMaterial::find($id);
        if (!$auspice) {
            return $this->sendError('No se encontro el material');
        }

        return $auspice->delete() ?
            $this->sendResponse('', 'El material ' . $auspice->auspice_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un material');
    }

    public function getAuspiceCost($guide_id) {
        $total_cost = 0;
        $auspices = Auspice::where([
            ['guide_id', '=', $guide_id],
            ['auspices.deleted_at', '=', null],
            ['guides.deleted_at', '=', null],
            ['guides.editable', '<>', 2]
        ])->join('guides', 'guides.id', '=', 'auspices.guide_id')->get();
        if (count($auspices) > 0) {
            foreach ($auspices as $key => $row) {
                if (!!$row->manual_apportion) {
                    //calculated value
                    $total_cost = 0;
                } else {
                    //happy path
                    $total_cost += $row->cost;
                }
            }
        }
        return $total_cost;
    }
}
