<?php

namespace App\Http\Controllers\API;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('media')
            ->select('id', 'media_name as mediaName', 'business_name as businessName', 'NIT', 'city_id as cityId', 'media_type as mediaType')
            ->where('media.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'media.id' : 'media.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'media_name'     => 'required',
            'business_name'  => 'required',
            'NIT'            => 'required',
            'city_id'        => 'required',
            'media_type'     => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $media = new Media(array(
            'media_name'    => trim($request->mediaName),
            'business_name' => trim($request->businessName),
            'NIT'           => trim($request->NIT),
            'city_id'       => trim($request->cityId),
            'media_type'    => trim($request->mediaType)
        ));

        return $media->save() ?
            $this->sendResponse('', 'El media ' . $media->media_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo media.');
    }

    public function show($id) {
        $media = Media::find($id);
        if (!$media) {
            return $this->sendError('No se contro el media');
        }
        return $this->sendResponse($media, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'media_name'     => 'required',
            'business_name'  => 'required',
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
        $media->representative = trim($request->business_name);
        $media->NIT            = trim($request->NIT);
        $media->city_id        = trim($request->city_id);
        $media->media_type     = trim($request->media_type);

        return $media->save() ?
            $this->sendResponse('', 'El media ' . $media->media_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el media ' . $media->media_name . '.');
    }

    public function destroy($id) {
        $media = Media::find($id);
        if (!$media) {
            return $this->sendError('No se encontro el medio');
        }

        return $media->delete() ?
            $this->sendResponse('', 'El media ' . $media->media_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un medio');
    }
}
