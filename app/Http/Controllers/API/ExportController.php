<?php

namespace App\Http\Controllers\API;

use App\Exports\ReportExport;
use App\Models\Client;
use App\Models\Currency;
use App\Models\OrderNumber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;

class ExportController extends BaseController {

    public function order(Request $request) {
        $validator = Validator::make($request->all(), [
            'guideId'    => 'required'
        ]);

        $currency = Currency::find($request->currencyId) ? Currency::find($request->currencyId) : (object)['currency_value' => 1, 'symbol' => 'BOB'];

        if (!$validator->fails()){
            $observation = [
                0 => '',
                1 => $request->observations
            ];

            $result = DB::table('materials')
                ->select('materials.id as id', 'materials.material_name', 'materials.duration', 'materials.guide_id', 'materials.rate_id',
                    'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.editable as editable', 'rates.show',
                    'rates.hour_ini', 'rates.hour_end', 'rates.cost', 'media.media_name', 'media.business_name', 'media.NIT', 'media.media_type as mediaTypeId', 'media_types.media_type',
                    'campaigns.campaign_name', 'campaigns.plan_id', 'plan.client_id', 'campaigns.date_ini', 'campaigns.date_end',
                    'rates.hour_ini as hourIni', 'rates.hour_end as hourEnd',
                    'clients.id as clientId', 'clients.client_name as clientName', 'clients.representative', 'clients.NIT as clientNIT', 'clients.billing_address as billingAddress', 'clients.billing_policies as billingPolicies')
                ->join('guides', 'guides.id', '=', 'materials.guide_id')
                ->join('rates', 'rates.id', '=', 'materials.rate_id')
                ->join('media', 'media.id', '=', 'rates.media_id')
                ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
                ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
                ->join('clients', 'clients.id', '=', 'plan.client_id')
                ->join('media_types', 'media_types.id', '=', 'media.media_type')
                ->where('guides.id', '=', $request->guideId)
                ->where('materials.deleted_at', '=', null)
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
                }

                $orderNumber = OrderNumber::where('guide_id', '=', $request->guideId)->get();

                if($result[0]->editable == 1) {
                    if (count($orderNumber) > 0) {
                        $orderNumber = OrderNumber::find($orderNumber[0]->id);
                        $observation[0] = 'Remplazando a la orden '.$orderNumber->order_number.'.'.$orderNumber->version;
                        $orderNumber->version = $orderNumber->version +1;
                        $orderNumber->observation = $observation[0].' - '.$observation[1];
                        $orderNumber->save();
                    } else {
                        $order = OrderNumber::all()->max('order_number');
                        $orderNumber = OrderNumber::create([
                            'order_number'  => $order + 1,
                            'version'       => 0,
                            'guide_id'      => $request->guideId,
                            'observation'  => $observation[1]
                        ]);
                    }
                } else {
                    $orderNumber = OrderNumber::find($orderNumber[0]->id);
                }
                $orderNumber = $orderNumber->order_number.'.'.$orderNumber->version;

                $date_ini = new DateTime($result[0]->date_ini);
                $date_end = new DateTime($result[0]->date_end);
                $pages = $date_ini->diff($date_end)->m;

                $month = date("m", strtotime(explode(" ", $result[0]->date_ini)[0]));
                $year = date("Y", strtotime(explode(" ", $result[0]->date_ini)[0]));

                $user = User::find($request->userId);
                $user = empty($user) ? 'System' : $user->name . ' ' .$user->lastname;

                $response = [
                    'result'          => $result,
                    'order'           => $orderNumber,
                    'client'          => $result[0]->clientName,
                    'businessName'    => strtoupper($result[0]->business_name),
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
                    'billingToName'   => 'Nombre: '. $result[0]->representative,
                    'billingToNit'    => 'NIT: ' .$result[0]->clientNIT,
                    'billingAddress'  => $result[0]->billingAddress,
                    'billingPolicies' => $result[0]->billingPolicies,
                    'observation1'    => $observation[0],
                    'observation2'    => $observation[1],
                    'clientName'      => $result[0]->clientName,
                    'user'            => $user,
                    'currency'        => $currency->symbol,
                    'currencyValue'   => $currency->currency_value
                ];

                foreach ($response['result'] as $llave => $fila) {
                    $response['result'][$llave]->unitCost = $this->getUnitCost($fila->cost, $fila->media_type, $fila->duration);
                    $response['result'][$llave]->totalCost = $this->getTotalCost($fila->cost, $fila->media_type, $fila->duration, $fila->spots);
                    $response['totalMount'] += $this->getTotalCost($fila->cost, $fila->media_type, $fila->duration, $fila->spots);
                }

                return !$request->isOrderCampaign ? $this->exportPdf($response, 'orderGuide', 'orderGuide.pdf') : $response;
            } else {
                $request['isOrderCampaign'] = !!$request->isOrderCampaign;
                return !$request->isOrderCampaign ? $this->sendError('No tiene materiales') : [];
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
            $request['isOrderCampaign'] = !!$request->isOrderCampaign;
            $result = DB::table('guides')
                ->select('guides.id as guide_id')
                ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
                ->where('campaigns.id', '=', $request->campaignId)
                ->where('guides.deleted_at', '=', null)
                ->get();
            if (count($result) > 0) {
                $response = array();
                foreach ($result as $k => $r) {
                    $request['guideId'] = $r->guide_id;
                    $request['observation'] = empty($request->observation) ? '' : $request->observation;
                    $request['isOrderCampaign'] = true;
                    $res = $this->order($request);
                    if (count($res) > 0) {
                        $response[] = $res;
                    }
                }
                return $request->isOrderCampaign ? $this->exportPdf($response, 'campaign', 'campaña.pdf') : $response;
            } else {
                return $request->isOrderCampaign ? $this->sendError('', 'No tiene materiales', '') : [];
            }
        } else {
            return $this->sendError('Error de validacion.', $validator->errors());
        }
    }

    public function orderNumber($id) {
        $orderNumber = DB::table('order_numbers')
            ->select('*')
            ->join('guides', 'guides.id', '=', 'order_numbers.guide_id')
            ->where('order_numbers.guide_id', '=', $id)
            ->get();

        if (count($orderNumber) > 0) {
            if($orderNumber[0]->editable == 1) {
                $max_order   = OrderNumber::where('guide_id', '=', $id)->get()->max('order_number');
                $max_version = OrderNumber::where('guide_id', '=', $id)->get()->max('version') + 1;
                $observation[0] = 'Remplaza a la orden ' . $max_order.'.'.$max_version.'';
                return $this->sendResponse([
                    'order_number'  => ''. $max_order . '.' . $max_version
                ]);
            } else {
                $max_order   = OrderNumber::where('guide_id', '=', $id)->get()->max('order_number');
                $max_version = OrderNumber::where('guide_id', '=', $id)->get()->max('version');
                return $this->sendResponse([
                    'order_number'  => ''. $max_order . '.' . $max_version
                ]);
            }
        } else {
            $order = OrderNumber::all()->max('order_number') + 1;
            return $this->sendResponse([
                'order_number'  => $order.'.0'
            ]);
        }
    }

    public function exportReport(Request $request) {
        $validator = Validator::make($request->all(), [
            'clientId'   => 'required',
            'planId'     => 'required',
            'campaignId' => 'required'
        ]);

        $response = array();
        if (!$validator->fails()) {
            $result = Client::select('clients.id as client_id', 'client_name', 'representative', 'clients.NIT as clientNit', 'billing_address', 'billing_policies',
                'plan_name', 'campaigns.id as budget', 'plan.id as plan_id', 'guide_name', 'guides.id as guide_id', 'order_number', 'order_numbers.version',
                'media.id as media_id', 'material_name', 'duration', 'materials.id as material_id', 'product', 'campaign_name',
                'rates.id as rate_id', 'show', 'cost', 'media_name', 'business_name', 'cities.id as city_id', 'city', 'media_types.media_type')
                ->join('plan', 'plan.client_id', '=', 'clients.id')
                ->join('campaigns', 'campaigns.plan_id', '=', 'plan.id')
                ->join('guides', 'guides.campaign_id', '=', 'campaigns.id')
                ->join('materials', 'materials.guide_id', '=', 'guides.id')
                ->join('rates', 'rates.id', '=', 'materials.rate_id')
                ->join('media', 'media.id', '=', 'guides.media_id')
                ->join('media_types', 'media_types.id', '=', 'media.media_type')
                ->join('cities', 'cities.id', '=', 'media.city_id')
                ->join('order_numbers', 'order_numbers.guide_id', '=', 'guides.id')
                ->where([
                    ['clients.deleted_at', '=', null],
                    ['clients.id', '=', $request->clientId],
                    ['plan.id', '=', $request->planId],
                    ['campaigns.id', '=', $request->campaignId]
                ])
                ->get();

            $user = User::find($request->userId);
            $user = empty($user) ? 'System' : $user->name . ' ' .$user->lastname;
            $fila = (object)[];
            foreach ($result as $key => $row) {
                $plan = DB::table('material_planing')
                    ->select('*')
                    ->where('material_id', '=', $row->material_id)
                    ->get();
                foreach ($plan as $k => $r) {
                    $fila->broadcast_day = $r->broadcast_day;
                    $fila->day           = $date = date("d", strtotime($r->broadcast_day));
                    $fila->month         = $date = date("m", strtotime($r->broadcast_day));
                    $fila->year          = $date = date("Y", strtotime($r->broadcast_day));
                    $fila->times_per_day = $r->times_per_day;
                    $fila->user          = $user;
                    $fila->row           = $row;
                    $response[]          = $fila;
                    $fila                = (object)[];
                }
            }

            return $response;
        } else {
            return $this->sendError('Error de validacion.', $validator->errors());
        }
    }

    public function export(Request $request) {
        return (new ReportExport)->request($request);
    }

    public function getUnitCost($unitCost, $mediaType, $duration) {
        $mediaType = strtoupper($mediaType);
        if ($mediaType === "TV" || $mediaType === "TV PAGA") {
            return $duration * $unitCost;
        }
        else {
            return $unitCost;
        }
    }

    public function getTotalCost($unitCost, $mediaType, $duration, $passes) {
        $mediaType = strtoupper($mediaType);
        if ($mediaType === "TV" || $mediaType === "TV PAGA") {
            return $duration * $unitCost * $passes;
        }
        else {
            return $unitCost * $passes;
        }
    }
}
