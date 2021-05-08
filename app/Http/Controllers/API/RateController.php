<?php

namespace App\Http\Controllers\API;

use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RateController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('rates')
            ->select('rate.id', 'show', 'hour_ini', 'hour_end', 'brod_mo', 'brod_tu',
                             'brod_we', 'brod_th', 'brod_fr', 'brod_sa', 'brod_su', 'cost', 'media_name as mediaName')
            ->join('media', 'media.id', '=', 'rate.media_id')
            ->where('rate.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'rate.id' : 'rate.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'show'     => 'required',
            'media_id' => 'required',
            'hour_ini' => 'required',
            'cost'     => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $rate = new Rate(array(
            'show'     => trim($request->show),
            'media_id' => trim($request->media_id),
            'hour_ini' => trim($request->hour_ini),
            'cost'     => trim($request->cost),
            'brod_mo'  => trim($request->brod_mo),
            'brod_tu'  => trim($request->brod_tu),
            'brod_we'  => trim($request->brod_we),
            'brod_th'  => trim($request->brod_th),
            'brod_fr'  => trim($request->brod_fr),
            'brod_sa'  => trim($request->brod_sa),
            'brod_su'  => trim($request->brod_su)
        ));

        return $rate->save() ?
            $this->sendResponse('', 'El rate ' . $rate->rate_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear una nueva tarifa.');
    }

    public function show($id) {
        $rate = Rate::find($id);
        if (!$rate) {
            return $this->sendError('No se contro la tarifa');
        }
        return $this->sendResponse($rate, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'rate_name' => 'required',
            'client_id' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $rate = Rate::find($id);
        if (!$rate) {
            return $this->sendError('No se encontro la tarifa');
        }

        $rate->rate_name = trim($request->rate_name);
        $rate->date_ini      = trim($request->date_ini);
        $rate->date_end      = trim($request->date_end);
        $rate->plan_id       = trim($request->plan_id);

        return $rate->save() ?
            $this->sendResponse('', 'La tarifa ' . $rate->rate_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar la tarifa ' . $rate->rate_name . '.');
    }

    public function destroy($id) {
        $rate = Rate::find($id);
        if (!$rate) {
            return $this->sendError('No se encontro la tarifa');
        }

        return $rate->delete() ?
            $this->sendResponse('', 'La tarifa ' . $rate->rate_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar una tarifa');
    }

    public function list() {
        return $this->sendResponse(DB::table('rate')
            ->select('id', 'show as value', 'show as label')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
