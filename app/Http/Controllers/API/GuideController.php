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

        if (!$validator->fails()){
            $observation = [
                0 => '',
                1 => $request->observation
            ];

            $result = DB::table('materials')
                ->select('materials.id as id', 'materials.material_name', 'materials.duration', 'materials.guide_id', 'materials.rate_id',
                    'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.editable', 'rates.show',
                    'rates.hour_ini', 'rates.hour_end', 'rates.cost', 'media.media_name', 'media.business_name', 'media.NIT', 'media.media_type',
                    'campaigns.campaign_name', 'campaigns.plan_id', 'campaigns.client_id', 'campaigns.date_ini', 'campaigns.date_end',
                    'clients.id as clientId', 'clients.client_name as clientName', 'clients.representative', 'clients.NIT as clientNIT', 'clients.billing_address as billingAddress', 'clients.billing_policies as billingPolicies')
                ->join('guides', 'guides.id', '=', 'materials.guide_id')
                ->join('rates', 'rates.id', '=', 'materials.rate_id')
                ->join('media', 'media.id', '=', 'rates.media_id')
                ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
                ->join('clients', 'clients.id', '=', 'campaigns.client_id')
                ->join('media_types', 'media_types.id', '=', 'media.media_type')
                ->where('guides.id', '=', $request->guideId)
                ->get();

            if (count($result) > 0) {
                $total = 0;
                $totalSpots = 0;
                foreach ($result as $key => $row) {
                    $id = $result[$key]->id;
                    $planing = DB::table('material_planing')
                        ->select('broadcast_day', 'times_per_day')
                        ->where('material_planing.material_id', '=', $id)
                        ->get();
                    if (count($planing)) {
                        $spots = 0;
                        foreach ($planing as $k => $r) {
                            $planing[$k]->day = date("d", strtotime($planing[$k]->broadcast_day));
                            $spots += $planing[$k]->times_per_day;
                        }
                        $result[$key]->spots = $spots;
                        $totalSpots += $spots;
                        $result[$key]->planing = $planing;
                    }
                    $total += $result[$key]->spots * $result[$key]->cost;
                }

                $orderNumber = DB::table('order_numbers')
                    ->select('*')
                    ->where('order_numbers.guide_id', '=', $request->guideId)
                    ->get();

                if (count($orderNumber) > 0) {
                    $max_order   = OrderNumber::where('guide_id', '=', $request->guideId)->get()->max('order_number');
                    $max_version = OrderNumber::where('guide_id', '=', $request->guideId)->get()->max('version');
                    $observation[0] = 'Remplaza a la orden ' . $max_order.'.'.$max_version.'';
                    $orderNumber = OrderNumber::create([
                        'order_number'  => $max_order,
                        'version'       => $max_version + 1,
                        'guide_id'      => $request->guideId,
                        'observation'  => $observation[0].' - '.$observation[1]
                    ]);
                } else {
                    $order = OrderNumber::all()->max('order_number');
                    $orderNumber = OrderNumber::create([
                        'order_number'  => $order + 1,
                        'version'       => 0,
                        'guide_id'      => $request->guideId,
                        'observation'  => $observation[1]
                    ]);
                }

                $orderNumber = $orderNumber->order_number.'.'.$orderNumber->version;

                $date_ini = new DateTime($result[0]->date_ini);
                $date_end = new DateTime($result[0]->date_end);
                $pages = $date_ini->diff($date_end)->m;

                $month = date("m", strtotime(explode(" ", $result[0]->date_ini)[0]));
                $year = date("Y", strtotime(explode(" ", $result[0]->date_ini)[0]));

                $response = [
                    'result'          => $result,
                    'order'           => $orderNumber,
                    'businessName'    => $result[0]->business_name,
                    'guideName'       => $result[0]->guide_name,
                    'NIT'             => $result[0]->NIT,
                    'date_ini'        => explode(" ", $result[0]->date_ini)[0],
                    'date_end'        => explode(" ", $result[0]->date_end)[0],
                    'pages'           => $pages,
                    'date'            => date("m-d-Y"),
                    'month_ini'       => $month,
                    'year'            => $year,
                    'daysInMonth'     => cal_days_in_month(CAL_GREGORIAN, $month, $year),
                    'date-'           => date("F Y", strtotime("2021-05-12")),
                    'totalMount'      => $total,
                    'totalSpots'      => $totalSpots,
                    'billingAddress'  => $result[0]->billingAddress,
                    'billingPolicies' => empty($result[0]->billingPolicies) ? 'Nombre: '. $result[0]->representative . ' NIT: ' . $result[0]->clientNIT :   $result[0]->billingPolicies,
                    'observation1'    => $observation[0],
                    'observation2'    => $observation[1],
                    'clientName'      => $result[0]->clientName
                ];

                return !$request->isOrderCampaign ? $this->exportPdf($response, 'orderGuide', 'orderGuide.pdf') : $response;
            } else {
                return [];
            }
        } else {
            return $this->sendError('Error de validacion.', $validator->errors());
        }
    }

    public function orderByCampaign(Request $request) {
        $validator = Validator::make($request->all(), [
            'campaignId' => 'required'
        ]);

        if (!$validator->fails()){
            $result = DB::table('guides')
                ->select('guides.id as guide_id')
                ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
                ->where('campaigns.id', '=', $request->campaignId)
                ->get();
            if (count($result) > 0) {
                $response = array();
                foreach ($result as $k => $r) {
                    $request['guideId'] = $r->guide_id;
                    $request->observation = empty($request->observation) ? '' : $request->observation;
                    $request->isOrderCampaign = true;
                    $res = $this->order($request);
                    if (count($res) > 0) {
                        $response[] = $res;
                    }
                }

                return $this->exportPdf($response, 'campaign', 'campaña.pdf');
            } else {
                return $this->sendError('', 'No tiene materiales', '');
            }
        } else {
            return $this->sendError('Error de validacion.', $validator->errors());
        }
    }

    public function orderNumber(Request $request) {
        $orderNumber = DB::table('order_numbers')
            ->select('*')
            ->where('order_numbers.guide_id', '=', $request->guideId)
            ->get();

        if (count($orderNumber) > 0) {
            $max_order   = OrderNumber::where('guide_id', '=', $request->guideId)->get()->max('order_number');
            $max_version = OrderNumber::where('guide_id', '=', $request->guideId)->get()->max('version') + 1;
            $observation[0] = 'Remplaza a la orden ' . $max_order.'.'.$max_version.'';
            return $this->sendResponse([
                'order_number'  => ''. $max_order . '.' . $max_version
            ]);
        } else {
            $order = OrderNumber::all()->max('order_number') + 1;
            return $this->sendResponse([
                'order_number'  => $order.'.0'
            ]);
        }
    }

    public function list() {
        return $this->sendResponse(DB::table('guides')
            ->select('id', 'id as value', 'guide_name as label', 'date_ini as dateIni', 'date_end as dateEnd')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
