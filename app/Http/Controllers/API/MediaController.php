<?php

namespace App\Http\Controllers\API;

use App\Models\Guide;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class MediaController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        $response = DB::table('media')
            ->select('media.id', 'media_name as mediaName', 'business_name as businessName', 'NIT', 'city', 'cities.id as cityId', 'media_types.media_type as mediaTypeValue', 'media_types.id as mediaType', 'media_parent_id as mediaParentId')
            ->join('cities', 'cities.id', '=', 'media.city_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where('media.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'media.id' : 'media.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get();
        foreach ($response as $key => $row) {
            $response[$key]->isParent = !!empty($row->mediaParentId);
            if(!empty($row->mediaParentId)) {
                $media_parent = Media::find($row->mediaParentId);
                $response[$key]->mediaParentName = $media_parent->media_name;
                $response[$key]->mediaParent = $row->mediaParentId;
            }
        }
        return $this->sendResponse($response);
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
        $media->media_parent_id = empty($request->mediaParent) ? null : (trim($request->mediaParent));

        if ($media->media_parent_id === null) {
            $media_children = Media::where('media_parent_id', '=', $media->id)->get();
            if ($media_children) {
                foreach ($media_children as $key => $row) {
                    $media_child                  = Media::find($row->id);
                    $media_child->business_name   = trim($media->business_name);
                    $media_child->NIT             = trim($media->NIT);
                    $media_child->city_id         = trim($media->city_id);
                    $media_child->media_type      = trim($media->media_type);
                    $media_child->save();
                }
            }
        }

        return $media->save() ?
            $this->sendResponse('', 'El media ' . $media->media_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el media ' . $media->media_name . '.');
    }

    public function destroy($id) {
        $media = Media::find($id);
        if (!$media) {
            return $this->sendError('No se encontro el medio');
        }

        if (count(Guide::where('media_id', '=', $media->id)->get()) > 0) {
            return $this->sendError('unD_Guide', null, 200);
        }


        return $media->delete() ?
            $this->sendResponse('', 'El media ' . $media->media_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un medio');
    }

    public function parentList($id = 0) {
        return $this->sendResponse(DB::table('media')
            ->select('id', 'id as value', 'media_name as label', 'NIT', 'city_id as cityId', 'business_name as businessName', 'media_type as mediaType')
            ->where([
                ['deleted_at', '=', null],
                ['media_parent_id', '=', null],
                ['media.id', '<>', $id],
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
