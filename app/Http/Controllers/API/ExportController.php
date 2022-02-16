<?php

namespace App\Http\Controllers\API;

use App\Exports\ReportExport;
use App\Models\Campaign;
use App\Models\Currency;
use App\Models\Guide;
use App\Models\Material;
use App\Models\OrderNumber;
use App\Models\PlaningMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use DateTime;

class ExportController extends BaseController {

    protected $reportExport;
    public function __construct(ReportExport $reportCtrl) {
        $this->reportCtrl = $reportCtrl;
    }

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

            $result = Material::where([
                    ['materials.deleted_at', '=', null],
                    ['guides.id', '=', $request->guideId],
                    ['guides.date_ini', '>', date("Y-01-01")]
                ])
                ->select('materials.id as id', 'materials.material_name', 'materials.duration', 'materials.guide_id', 'materials.rate_id',
                    'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.editable as editable', 'rates.show',
                    'rates.hour_ini', 'rates.hour_end', 'rates.cost', 'media.media_name', 'media.business_name', 'media.NIT', 'media.media_type as mediaTypeId', 'media_types.media_type',
                    'campaigns.campaign_name', 'campaigns.plan_id', 'plan.client_id', 'campaigns.date_ini', 'campaigns.date_end',
                    'rates.hour_ini as hourIni', 'rates.hour_end as hourEnd', 'guides.date_ini as guideDateIni', 'guides.editable',
                    'clients.id as clientId', 'clients.client_name as clientName', 'clients.representative', 'clients.NIT as clientNIT', 'clients.billing_address as billingAddress', 'clients.billing_policies as billingPolicies')
                ->join('guides', 'guides.id', '=', 'materials.guide_id')
                ->join('rates', 'rates.id', '=', 'materials.rate_id')
                ->join('media', 'media.id', '=', 'rates.media_id')
                ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
                ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
                ->join('clients', 'clients.id', '=', 'plan.client_id')
                ->join('media_types', 'media_types.id', '=', 'media.media_type')
                ->get();

            $pla = array();
            $months = array();

            if (count($result) > 0) {
                $total = 0;
                $totalSpots = 0;
                foreach ($result as $key => $row) {
                    $id = $result[$key]->id;
                    if($request['monthsSelected'] && count($request['monthsSelected']) > 0) {
                        foreach ($request['monthsSelected'] as $ke => $ro) {
                            if (is_string($ro)) {
                                $month = date($this->getMonth($ro));
                                $planing = DB::table('material_planing')
                                    ->select('broadcast_day', 'times_per_day')
                                    ->where('material_planing.material_id', '=', $id)
                                    ->whereMonth('broadcast_day',  $month)
                                    ->get();
                                if(count($planing) > 0) {
                                    $pla[$month] = array();
                                    $spots = 0;
                                    foreach ($planing as $k => $r) {
                                        $r->day = date("d", strtotime($planing[$k]->broadcast_day));
                                        $m = date("m", strtotime($planing[$k]->broadcast_day));
                                        if ($month === $m) {
                                            $spots += $r->times_per_day;
                                        }
                                        $pla[$month][] = $r;
                                        $months[] = $month;
                                    }
                                    $result[$key]->spots = $spots;
                                    $totalSpots += $spots;
                                    $result[$key]->planing = $pla;
                                }
                            }
                        }
                    } else {
                        $planing = PlaningMaterial::where('material_planing.material_id', '=', $id)
                            ->select('broadcast_day', 'times_per_day')
                            ->get();
                        $m = date("m", strtotime($planing[0]->broadcast_day));
                        $pla[$m] = array();
                        if (count($planing)) {
                            $spots = 0;
                            foreach ($planing as $k => $r) {
                                $r->day = date("d", strtotime($planing[$k]->broadcast_day));
                                $m = date("m", strtotime($planing[$k]->broadcast_day));
                                $spots += $r->times_per_day;
                                $pla[$m][] = $r;
                                $months[] = $m;
                            }
                            $result[$key]->spots = $spots;
                            $totalSpots += $spots;
                            $result[$key]->planing = $pla;
                        }
                    }
                }

                $months = array_unique($months);

                $orderNumber = OrderNumber::where('guide_id', '=', $request->guideId)->get();

                if($result[0]->editable == 1) {
                    if (count($orderNumber) > 0) {
                        $orderNumber = OrderNumber::find($orderNumber[0]->id);
                        $observation[0] = $request->newOrder ?
                            'Remplazando a la orden '.$orderNumber->order_number.'.'.$orderNumber->version :
                            ($orderNumber->version == 0 ?
                                '' :
                                'Remplazando a la orden '.$orderNumber->order_number.'.'.($orderNumber->version - 1));
                        $orderNumber->version = $request->newOrder ? $orderNumber->version + 1 : $orderNumber->version;
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
                    'status'          => $result[0]->editable,
                    'status_value'    => $this->getStatus($result[0]->editable),
                    'order'           => $orderNumber,
                    'client'          => $result[0]->clientName,
                    'businessName'    => mb_strtoupper($result[0]->business_name),
                    'guideName'       => $result[0]->guide_name,
                    'NIT'             => $result[0]->NIT,
                    'date_ini'        => explode(" ", $result[0]->date_ini)[0],
                    'date_end'        => explode(" ", $result[0]->date_end)[0],
                    'pages'           => $pages,
                    'date'            => date("m-d-Y"),
                    'month_ini'       => $month,
                    'months'          => $months,
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
                    if (isset($fila->planing)) {
                        $response['result'][$llave]->unitCost = $this->getUnitCost($fila->cost, $fila->media_type, $fila->duration);
                        $response['result'][$llave]->totalCost = $this->getTotalCost($fila->cost, $fila->media_type, $fila->duration, $fila->spots);
                        $response['totalMount'] += $this->getTotalCost($fila->cost, $fila->media_type, $fila->duration, $fila->spots);
                    } else {
                        $response['result'][$llave]->unitCost = 0;
                        $response['result'][$llave]->totalCost = 0;
                    }
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
                ->where([
                    ['guides.deleted_at', '=', null],
                    ['campaigns.id', '=', $request->campaignId]
                ])
                ->get();

            if (count($result) > 0) {
                $response = array();
                if ($request['monthsSelected'] === 'ALL' || !isset($request['monthsSelected'])){
                    $request['monthsSelected'] = array();
                } else {
                    $request['monthsSelected'] = $this->cleanEmptyValues($request['monthsSelected']);
                }
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
        $currency = Currency::find($request->currencyId) ? Currency::find($request->currencyId) : Currency::find(1);
        if (!$validator->fails()) {
            $request['currency'] = Currency::find($request->currencyId) ? Currency::find($request->currencyId) : Currency::find(1);
            $campaign = $this->reportCtrl->getCampaignReport($request);
            $datas = [$campaign, $request['currency']];

            return $datas;
        } else {
            return $this->sendError('Error de validacion.', $validator->errors());
        }
    }

    public function export(Request $request) {
        $validator = Validator::make($request->all(), [
            'clientId'   => 'required',
            'dateIni'      => 'before_or_equal:dateEnd',
            'dateEnd'      => 'after_or_equal:dateIni',
        ]);
        if (!$validator->fails()) {
            return (new ReportExport)->request($request);
        } else {
            return $this->sendError('Error de validacion.', $validator->errors());
        }
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

    function weekOfMonth($date) {
        //Get the first day of the month.
        $firstOfMonth = strtotime(date("Y-m-01", $date));
        //Apply above formula.
        return $this->weekOfYear($date) - $this->weekOfYear($firstOfMonth);
    }

    function weekOfYear($date) {
        $weekOfYear = intval(date("W", $date));
        if (date('w') == 0) {            // 0 = Sunday
            $weekOfYear++;
        }
        if (date('n', $date) == "1" && $weekOfYear > 51) {
            // It's the last week of the previos year.
            $weekOfYear = 0;
        }
        return $weekOfYear;
    }

    function verifyWeek($aux) {
        return isset($aux->week) ? $aux->week : 1;
    }

    function getStatus($status) {
        switch ($status) {
            case Guide::STATUS_ACTIVE:
                return 'ACTIVO';
            case Guide::STATUS_FINALIZED:
                return 'FINALIZADO';
            case Guide::STATUS_CANCELED:
                return 'CANCELADO';
        }
    }

    function getMonth($month) {
        switch ($month) {
            case Campaign::CAMPAIGN_JAN:
                return '01';
            case Campaign::CAMPAIGN_FEB:
                return '02';
            case Campaign::CAMPAIGN_MAR:
                return '03';
            case Campaign::CAMPAIGN_APR:
                return '04';
            case Campaign::CAMPAIGN_MAY:
                return '05';
            case Campaign::CAMPAIGN_JUN:
                return '06';
            case Campaign::CAMPAIGN_JUL:
                return '07';
            case Campaign::CAMPAIGN_AUG:
                return '08';
            case Campaign::CAMPAIGN_SEP:
                return '09';
            case Campaign::CAMPAIGN_OCT:
                return '10';
            case Campaign::CAMPAIGN_NOV:
                return '11';
            case Campaign::CAMPAIGN_DEC:
                return '12';
        }
    }

    function cleanEmptyValues($months): array {
        $months = (array)$months;
        $returnMonths = array();
        foreach ($months as $month => $m) {
            if ($m) {
                if (is_string($month)) {
                    $returnMonths[] = $month;
                }
            }
        }
        return $returnMonths;
    }
}
