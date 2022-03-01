<?php

namespace App\Http\Controllers\API;

use App\Models\Material;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class RateController extends BaseController {
    public function index (Request $request) {
        $sort = explode(':', $request->sort);
        $rates = DB::table('rates')
            ->select('rates.id', 'show', 'hour_ini as hourIni', 'hour_end as hourEnd', 'brod_mo as brodMo', 'brod_tu as brodTu',
                'brod_we as brodWe', 'brod_th as brodTh', 'brod_fr as brodFr', 'brod_sa as brodSa', 'brod_su as brodSu', 'cost', 'media_name as mediaName', 'media.id as mediaId',
                'media_types.media_type as mediaTypeValue')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where('rates.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'rates.id' : 'rates.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get();

        foreach ($rates as $key => $row) {
            $rates[$key]->emitDays = [
                'Lunes'     => $row->brodMo,
                'Martes'    => $row->brodTu,
                'Miercoles' => $row->brodWe,
                'Jueves'    => $row->brodTh,
                'Viernes'   => $row->brodFr,
                'Sabado'    => $row->brodSa,
                'Domingo'   => $row->brodSu,
            ];
        }


        return $this->sendResponse($rates, '');
    }

    public function store(Request $request) {
        if(!empty($request->hourIni) || !empty($request->hourEnd)) {
            $validator = Validator::make($request->all(), [
                'show'     => 'required',
                'hourIni'  => 'before_or_equal:hourEnd',
                'hourEnd'  => 'after_or_equal:hourIni',
                'cost'     => 'required|numeric',
                'emitDays' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'show'     => 'required',
                'cost'     => 'required|numeric',
                'emitDays' => 'required',
            ]);
        }

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $rate = new Rate(array(
            'show'     => trim($request->show),
            'media_id' => trim($request->mediaId),
            'hour_ini' => trim($request->hourIni),
            'hour_end' => trim($request->hourEnd),
            'cost'     => trim($request->cost),
            'brod_mo'  => trim($request->emitDays['Lunes']) || 0,
            'brod_tu'  => trim($request->emitDays['Martes']) || 0,
            'brod_we'  => trim($request->emitDays['Miercoles']) || 0,
            'brod_th'  => trim($request->emitDays['Jueves']) || 0,
            'brod_fr'  => trim($request->emitDays['Viernes']) || 0,
            'brod_sa'  => trim($request->emitDays['Sabado']) || 0,
            'brod_su'  => trim($request->emitDays['Domingo']) || 0
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
        if(!empty($request->hourIni) || !empty($request->hourEnd)) {
            $validator = Validator::make($request->all(), [
                'show'     => 'required',
                'hourIni'  => 'before_or_equal:hourEnd',
                'hourEnd'  => 'after_or_equal:hourIni',
                'cost'     => 'required|numeric',
                'emitDays' => 'required',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'show'     => 'required',
                'cost'     => 'required|numeric',
                'emitDays' => 'required',
            ]);
        }


        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $rate = Rate::find($id);
        if (!$rate) {
            return $this->sendError('No se encontro la tarifa');
        }

        $rate->show     = trim($request->show);
        $rate->media_id = trim($request->mediaId);
        $rate->hour_ini = trim($request->hourIni);
        $rate->hour_end = trim($request->hourEnd);
        $rate->cost     = trim($request->cost);
        $rate->brod_mo  = trim($request->emitDays['Lunes']) || 0;
        $rate->brod_tu  = trim($request->emitDays['Martes']) || 0;
        $rate->brod_we  = trim($request->emitDays['Miercoles']) || 0;
        $rate->brod_th  = trim($request->emitDays['Jueves']) || 0;
        $rate->brod_fr  = trim($request->emitDays['Viernes']) || 0;
        $rate->brod_sa  = trim($request->emitDays['Sabado']) || 0;
        $rate->brod_su  = trim($request->emitDays['Domingo']) || 0;

        return $rate->save() ?
            $this->sendResponse('', 'La tarifa ' . $rate->rate_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar la tarifa ' . $rate->rate_name . '.');
    }

    public function destroy($id) {
        $rate = Rate::find($id);
        if (!$rate) {
            return $this->sendError('No se encontro la tarifa');
        }

        if (count(Material::where('rate_id', '=', $rate->id)->get()) > 0) {
            return $this->sendError('unD_Rate', null, 200);
        }

        return $rate->delete() ?
            $this->sendResponse('', 'La tarifa ' . $rate->rate_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar una tarifa');
    }

    public function list() {
        return $this->sendResponse(DB::table('rates')
            ->select('rates.id', 'rates.id as value', 'show as label', 'brod_mo', 'brod_tu', 'brod_we', 'brod_th', 'brod_fr', 'brod_sa', 'brod_su', 'media_types.media_type as mediaType', 'cost')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where('rates.deleted_at', '=', null)
            ->get(), '');
    }

    public function rateGuideList($id) {
        $rates = DB::table('rates')
            ->select('rates.id', 'rates.id as value', 'show as label', 'brod_mo', 'brod_tu', 'brod_we', 'brod_th', 'brod_fr', 'brod_sa', 'brod_su', 'media_types.media_type as mediaType', 'rates.cost as rateCost', 'guides.cost as guideCost', 'media.id as mediaId')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->join('guides', 'guides.media_id', '=', 'media.id')
            ->where([
                ['rates.deleted_at', '=', null],
                ['guides.id', '=', $id]
            ])
            ->get();
        foreach ($rates as $key => $row) {
            $rates[$key]->children = DB::table('rates')
                ->select('rates.id', 'rates.id as value', 'show as label', 'brod_mo', 'brod_tu', 'brod_we', 'brod_th', 'brod_fr', 'brod_sa', 'brod_su', 'media_types.media_type as mediaType', 'cost', 'media.id as mediaId')
                ->join('media', 'media.id', '=', 'rates.media_id')
                ->join('media_types', 'media_types.id', '=', 'media.media_type')
                ->where([
                    ['rates.deleted_at', '=', null],
                    ['media_parent_id', '=', $row->mediaId],
                ])
                ->get();
        }
        return $this->sendResponse($rates);
    }
}
