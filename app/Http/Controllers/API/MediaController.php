<?php

namespace App\Http\Controllers\API;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaController extends BaseController {
    public function index (Request $request) {
        if (Auth::check()) {
            $sort = explode(":", $request->sort);
            return $this->sendResponse(DB::table('media')
                ->select('media.id', 'media_name as mediaName', 'representative', 'NIT', 'city_id', 'media_type')
                ->join('clients', 'clients.id', '=', 'media.client_id')
                ->where('media.deleted_at', '=', null)
                ->orderBy(empty($sort[0]) ? 'media.id' : 'media.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function store(Request $request) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'media_name'     => 'required',
                'representative' => 'required',
                'NIT'            => 'required',
                'city_id'        => 'required',
                'media_type'     => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $media = new Media(array(
                'media_name' => trim($request->media_name),
                'cost'         => trim($request->cost),
                'duration'     => trim($request->duration),
                'client_id'    => trim($request->client_id)
            ));

            return $media->save() ?
                $this->sendResponse('', 'El media ' . $media->media_name . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo media.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function show($id) {
        if (Auth::check()){
            $media = Media::find($id);
            if (!$media) {
                return $this->sendError('No se contro el media');
            }
            return $this->sendResponse($media, '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function update(Request $request, $id) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'media_name'     => 'required',
                'representative' => 'required',
                'NIT'            => 'required',
                'city_id'        => 'required',
                'media_type'     => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $media = Media::find($id);
            if (!$media) {
                return $this->sendError('No se encontro el media');
            }

            $media->media_name     = trim($request->media_name);
            $media->representative = trim($request->representative);
            $media->NIT            = trim($request->NIT);
            $media->city_id        = trim($request->city_id);
            $media->media_type     = trim($request->media_type);

            return $media->save() ?
                $this->sendResponse('', 'El media ' . $media->media_name . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al actualizar el media ' . $media->media_name . '.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function destroy($id) {
        $media = Media::find($id);
        if (!$media) {
            return response()->json([
                'error' => [
                    'message' => 'No se encontro el media.'
                ]
            ]);
        }

        return $media->delete() ?
            response()->json([
                'message' => 'El media ' . $media->media_name . ' se elimino correctamente.'
            ]) :
            response()->json([
                'error' => [
                    'message' => 'El media ' . $media->media_name .' no se pudo eliminar.'
                ]
            ]);
    }
}
