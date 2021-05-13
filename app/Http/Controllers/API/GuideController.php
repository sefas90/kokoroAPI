<?php

namespace App\Http\Controllers\API;

use App\Models\Guide;
use App\Models\OrderNumber;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class GuideController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('guides')
            ->select('guides.id', 'guide_name as guideName', 'guides.date_ini as dateIni', 'campaigns.id as campaignId',
                'guides.date_end as dateEnd', 'media_name as mediaName', 'campaign_name as campaignName', 'guides.id as guideId', 'editable'
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
            'dateIni'    => 'required',
            'dateEnd'    => 'required',
            'mediaId'    => 'required',
            'campaignId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $guide = new Guide(array(
            'guide_name'  => trim($request->guideName),
            'date_ini'    => trim($request->dateIni),
            'date_end'    => trim($request->dateEnd),
            'media_id'    => trim($request->mediaId),
            'campaign_id' => trim($request->campaignId),
            'editable'    => true,
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
            'dateIni'    => 'required',
            'dateEnd'    => 'required',
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
        $guide->NIT         = trim($request->NIT);

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

    public function order(Request $request) {
        $validator = Validator::make($request->all(), [
            'guideId'    => 'required'
        ]);

        $result = DB::table('materials')
            ->select('materials.id as id', 'materials.material_name', 'materials.duration', 'materials.guide_id', 'materials.rate_id',
                'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.editable', 'rates.show',
                'rates.hour_ini', 'rates.hour_end', 'rates.cost', 'media.media_name', 'media.business_name', 'media.NIT', 'media.media_type',
                'campaigns.campaign_name', 'campaigns.plan_id', 'campaigns.client_id', 'campaigns.date_ini', 'campaigns.date_end')
            ->join('guides', 'guides.id', '=', 'materials.guide_id')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where('guides.id', '=', $request->guideId)
            ->get();

        foreach ($result as $key => $row) {
            $id = $result[$key]->id;
            $planing = DB::table('material_planing')
                ->select('*')
                ->where('material_planing.material_id', '=', $id)
                ->get();
            if (count($planing)) {
                $result[$key]->planing = $planing;
            }
        }

        $orderNumber = DB::table('order_numbers')
            ->select('*')
            ->where('order_numbers.guide_id', '=', $request->guideId)
            ->get();

        if ($orderNumber) {
            $max_order   = OrderNumber::where('guide_id', '=', $request->guideId)->max('order_number');
            $max_version = OrderNumber::where('guide_id', '=', $request->guideId)->max('version');
            $orderNumber = OrderNumber::create([
                'order_number'  => $max_order,
                'version'       => $max_version + 1,
                'guide_id'      => $request->guideId
            ]);
        } else {
            $order = OrderNumber::all()->max('order_number');
            $orderNumber = OrderNumber::create([
                'order_number'  => $order + 1,
                'version'       => 1,
                'guide_id'      => $request->guideId
            ]);
        }

        $orderNumber = $orderNumber->order_number.'.'.$orderNumber->version;

        $date_ini = new DateTime($result[0]->date_ini);
        $date_end = new DateTime($result[0]->date_end);
        $pages = $date_ini->diff($date_end)->m;



        $response = [
            'result'       => $result,
            'order'        => $orderNumber,
            'businessName' => $result[0]->business_name,
            'guideName'    => $result[0]->guide_name,
            'NIT'          => $result[0]->NIT,
            'date_ini'     => explode(" ", $result[0]->date_ini)[0],
            'date_end'     => explode(" ", $result[0]->date_end)[0],
            'pages'        => $pages,
            'date'         => date("m-d-Y"),
            'month_ini'    => date("m", strtotime(explode(" ", $result[0]->date_ini)[0])),
            'date-'         => date("F Y", strtotime("2021-05-12"))
        ];

        //return $response;

        return $this->exportPdf($response, 'reports', 'reports.pdf');
    }

    public function list() {
        return $this->sendResponse(DB::table('guides')
            ->select('id', 'id as value', 'guide_name as label', 'date_ini as dateIni', 'date_end as dateEnd')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
