<?php

namespace App\Http\Controllers\API;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class GuideController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('guides')
            ->select('guides.id', 'guide_name as guideName', 'guides.date_ini as dateIni', 'campaigns.id as budget',
                'guides.date_end as dateEnd', 'media_name as mediaName', 'campaign_name as campaignName', 'guides.id as guideId', 'editable as status'
            )
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->where('guides.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'guides.id' : 'guides.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'guideName'  => 'required',
            'dateIni'      => ['required', 'before:dateEnd'],
            'dateEnd'      => ['required', 'after:dateIni'],
            'mediaId'    => 'required',
            'campaignId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $guide = new Guide(array(
            'guide_name'     => trim($request->guideName),
            'date_ini'       => trim($request->dateIni),
            'date_end'       => trim($request->dateEnd),
            'media_id'       => trim($request->mediaId),
            'campaign_id'    => trim($request->campaignId),
            'billing_number' => null,
            'editable'       => true,
        ));

        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo guide.');
    }

    public function show($id) {
        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se contro el guide');
        }
        return $this->sendResponse($guide, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'guideName'  => 'required',
            'dateIni'      => ['required', 'before:dateEnd'],
            'dateEnd'      => ['required', 'after:dateIni'],
            'mediaId'    => 'required',
            'campaignId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se encontro el guide');
        }

        $guide->guide_name = trim($request->guide_name);
        $guide->NIT        = trim($request->NIT);
        $guide->date_ini   = trim($request->dateIni);
        $guide->date_end   = trim($request->dateEnd);
        $guide->media_id   = trim($request->mediaId);

        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el guide ' . $guide->guide_name . '.');
    }

    public function destroy($id) {
        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se encontro la guia');
        }

        return $guide->delete() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se elimino correctamente.') :
            $this->sendError('El guide ' . $guide->guide_name .' no se pudo eliminar.');
    }

    public function list() {
        return $this->sendResponse(DB::table('guides')
            ->select('id', 'id as value', 'guide_name as label', 'date_ini as dateIni', 'date_end as dateEnd')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
