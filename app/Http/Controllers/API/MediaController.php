<?php

namespace App\Http\Controllers\API;

use App\Models\Media;
use App\Models\PlaningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class MediaController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('media')
            ->select('media.id', 'media_name as mediaName', 'business_name as businessName', 'NIT', 'city', 'cities.id as cityId', 'media_types.media_type as mediaTypeValue', 'media_types.id as mediaType', 'media_parent_id as mediaParentId')
            ->join('cities', 'cities.id', '=', 'media.city_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where('media.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'media.id' : 'media.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'mediaName'     => 'required',
            'businessName'  => 'required',
            'NIT'           => 'required',
            'cityId'        => 'required',
            'mediaType'     => 'required',
        ]);

        if($validator->fails()) {
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $media = new Media(array(
            'media_name'      => trim($request->mediaName),
            'business_name'   => trim($request->businessName),
            'NIT'             => trim($request->NIT),
            'city_id'         => trim($request->cityId),
            'media_type'      => trim($request->mediaType),
            'media_parent_id' => empty($request->mediaParent) ? null : (trim($request->mediaParent))
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
            'mediaName'     => 'required',
            'businessName'  => 'required',
            'NIT'            => 'required',
            'cityId'        => 'required',
            'mediaType'     => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $media = Media::find($id);
        if (!$media) {
            return $this->sendError('No se encontro el media');
        }

        $media->media_name      = trim($request->mediaName);
        $media->business_name   = trim($request->businessName);
        $media->NIT             = trim($request->NIT);
        $media->city_id         = trim($request->cityId);
        $media->media_type      = trim($request->mediaType);
        $media->media_parent_id = trim($request->mediaParent);

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

    public function parentList() {
        return $this->sendResponse(DB::table('media')
            ->select('id', 'id as value', 'media_name as label')
            ->where([
                ['deleted_at', '=', null],
                ['media_parent_id', '=', null],
            ])
            ->get(), '');
    }

    public function list() {
        $mediaList = DB::table('media')
            ->select('id', 'id as value', 'media_name as label')
            ->where([
                ['deleted_at', '=', null],
                ['media_parent_id', '=', null],
            ])
            ->get();
        foreach ($mediaList as $key => $row) {
            $mediaList[$key]->children = DB::table('media')
                ->select('id', 'id as value', 'media_name as label')
                ->where([
                    ['deleted_at', '=', null],
                    ['media_parent_id', '=', $row->id],
                ])
                ->get();
        }

        return $this->sendResponse($mediaList);
    }
}
