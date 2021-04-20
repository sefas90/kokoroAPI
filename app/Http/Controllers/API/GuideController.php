<?php

namespace App\Http\Controllers\API;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GuideController extends BaseController {
    public function index (Request $request) {
        if (Auth::check()) {
            $sort = explode(":", $request->sort);
            return $this->sendResponse(DB::table('guides')
                ->select('id', 'guide_name as guideName', 'date_ini', 'date_end', 'media_name as mediaName', 'representative', 'campaign_name as campaignName')
                ->join('media', 'media.id', '=', 'guides.media_id')
                ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
                ->where('deleted_at', '=', null)
                ->orderBy(empty($sort[0]) ? 'guide.id' : 'guide.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function store(Request $request) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'guide_name' => 'required',
                'NIT'         => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $guide = new Guide(array(
                'guide_name' => trim($request->guide_name),
                'NIT'         => trim($request->NIT)
            ));

            return $guide->save() ?
                $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo guide.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function show($id) {
        if (Auth::check()){
            $guide = Guide::find($id);
            if (!$guide) {
                return $this->sendError('No se contro el guide');
            }
            return $this->sendResponse($guide, '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function update(Request $request, $id) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'guide_name' => 'required',
                'NIT' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $guide = Guide::find($id);
            if (!$guide) {
                return $this->sendError('No se encontro el guide');
            }

            $guide->guide_name = trim($request->guide_name);
            $guide->NIT         = trim($request->NIT);

            return $guide->save() ?
                $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al actualizar el guide ' . $guide->guide_name . '.');
        } else {
            return $this->sendError('No esta autenticado');
        }
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
}
