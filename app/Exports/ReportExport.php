<?php

namespace App\Exports;

use App\Models\AuspiceMaterial;
use App\Models\Client;
use App\Models\Currency;
use App\Models\PlaningAuspiceMaterial;
use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Cache\CacheManager;
use DateTime;

class ReportExport implements FromView, Responsable, ShouldAutoSize {
    use Exportable;

    private $fileName = 'reporte.xlsx';
    private $req;

    public function request($req): ReportExport {
        $this->req = $req;
        return $this;
    }

    public function view(): View {

        $request = $this->req;
        $request['currency'] = Currency::find($request->currencyId) ? Currency::find($request->currencyId) : Currency::find(1);
        $campaign = $this->getCampaignReport($request);
        $auspice = $this->getAuspiceReport($request);
        $data = [$campaign, $request['currency'], $auspice];

        return view('exports.reports', [
            'datas' => $data
        ]);
    }

    public function getCampaignReport($request): array {
        $where = $this->buildWhere($request, true);
        $result = Client::select('clients.id as client_id', 'client_name', 'representative', 'clients.NIT as clientNit', 'billing_address', 'billing_policies',
            'plan_name', 'campaigns.id as budget', 'plan.id as plan_id', 'guide_name', 'guides.id as guide_id', 'order_number', 'order_numbers.version',
            'media.id as media_id', 'material_name', 'duration', 'materials.id as material_id', 'materials.material_name', 'product', 'campaign_name', 'guides.billing_number',
            'rates.id as rate_id', 'show', 'cost', 'media_name', 'business_name', 'cities.id as city_id', 'city', 'media_types.media_type')
            ->join('plan', 'plan.client_id', '=', 'clients.id')
            ->join('campaigns', 'campaigns.plan_id', '=', 'plan.id')
            ->join('guides', 'guides.campaign_id', '=', 'campaigns.id')
            ->join('materials', 'materials.guide_id', '=', 'guides.id')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->join('cities', 'cities.id', '=', 'media.city_id')
            ->join('order_numbers', 'order_numbers.guide_id', '=', 'guides.id')
            ->where($where)
            ->orderBy('materials.id')
            ->get();

        $user = User::find($request->userId);
        $user = empty($user) ? 'System' : $user->name . ' ' .$user->lastname;
        $fila = (object)[];
        $aux = null;
        $response = array();
        foreach ($result as $key => $row) {
            $where = $this->buildDatesWhere($request, true, $row->material_id);
            $plan = DB::table('material_planing')
                ->select('*')
                ->where($where)
                ->get();
            $times_per_day = 0;
            $started = false;
            foreach ($plan as $k => $r) {
                $fila->weekOfYear    = $this->weekOfYear(strtotime($r->broadcast_day));
                if ($started && $aux->weekOfYear != $fila->weekOfYear) {
                    $response[]    = $aux;
                    $times_per_day = 0;
                } else {
                    $started = true;
                }
                $fila->user          = $user;
                $fila->row           = $row;
                $fila->cost          = $this->getUnitCost($row->cost, $row->media_type, $row->duration);
                $fila->currencyValue = $request['currency']->currency_value;
                $fila->duration      = $row->duration;
                $fila->broadcast_day = $r->broadcast_day;
                $fila->month         = date("m", strtotime($r->broadcast_day));
                $fila->year          = date("Y", strtotime($r->broadcast_day));
                $times_per_day       += $r->times_per_day;
                $fila->times_per_day = $times_per_day;
                $aux                 = $fila;
                $fila                = (object)[];
            }
            $response[] = $aux;
            $aux        = null;
        }
        return $response;
    }

    public function getAuspiceReport($request): array {
        $where = $this->buildWhere($request, false);
        $result = DB::table('auspices')
            ->select('auspices.id as auspiceId', 'auspice_materials.id as material_id', 'auspices.auspice_name',
                'auspices.guide_id', 'auspices.rate_id', 'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.billing_number',
                'guides.editable as editable', 'rates.show', 'auspice_materials.duration', 'auspice_materials.material_name',
                'rates.hour_ini', 'rates.hour_end', 'auspices.cost', 'media.media_name', 'media.business_name', 'order_number', 'order_numbers.version',
                'media.NIT', 'media.media_type as mediaTypeId', 'media_types.media_type', 'campaigns.campaign_name', 'campaigns.id as budget',
                'campaigns.plan_id', 'plan.client_id', 'plan.plan_name as plan_name', 'campaigns.date_ini', 'campaigns.date_end', 'campaigns.product',
                'rates.hour_ini as hourIni', 'rates.hour_end as hourEnd', 'clients.id as clientId', 'auspices.manual_apportion',
                'clients.client_name as client_name', 'clients.representative', 'clients.NIT as clientNIT',
                'auspice_materials.total_cost as materialCost', 'clients.billing_address as billingAddress',
                'clients.billing_policies as billingPolicies', 'cities.id as city_id', 'city')
            ->join('auspice_materials', 'auspice_materials.auspice_id', '=', 'auspices.id')
            ->join('guides', 'guides.id', '=', 'auspices.guide_id')
            ->join('rates', 'rates.id', '=', 'auspices.rate_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->join('cities', 'cities.id', '=', 'media.city_id')
            ->join('order_numbers', 'order_numbers.guide_id', '=', 'guides.id')
            ->where($where)
            ->get();
        $user = User::find($request->userId);
        $user = empty($user) ? 'System' : $user->name . ' ' .$user->lastname;
        $fila = (object)[];
        $aux = null;
        $response = array();

        foreach ($result as $key => $row) {
            $material = AuspiceMaterial::where([
                ['auspice_id', '=', $row->auspiceId],
                ['deleted_at', '=', null]
            ])->get();
            $where = $this->buildDatesWhere($request, false, $row->material_id);
            $plan = PlaningAuspiceMaterial::where($where)->select('*')->get();
            $total_passes = PlaningAuspiceMaterial::where('material_auspice_id', '=', $row->material_id)
                ->sum('times_per_day');

            $times_per_day = 0;
            $total_passes = floor($total_passes);
            $started = false;
            if (!empty($material) && count($material) > 0 && $total_passes > 0) {
                foreach ($plan as $k => $r) {
                    $fila->weekOfYear    = $this->weekOfYear(strtotime($r->broadcast_day));
                    if ($started && $aux->weekOfYear != $fila->weekOfYear) {
                        $response[]    = $aux;
                        $times_per_day = 0;
                    } else {
                        $started = true;
                    }
                    if (filter_var($result[$key]->manual_apportion, FILTER_VALIDATE_BOOLEAN)) {
                        $fila->cost      = $result[$key]->materialCost > 0 ? $result[$key]->materialCost / $total_passes : 0;
                    } else {
                        $fila->cost      = $this->getAuspiceUnitCost($row->cost, $total_passes, count($material));
                    }
                    $fila->user          = $user;
                    $fila->row           = $row;
                    $fila->currencyValue = $request['currency']->currency_value;
                    $fila->duration      = $row->duration;
                    $fila->broadcast_day = $r->broadcast_day;
                    $fila->weekOfYear    = $this->weekOfYear(strtotime($r->broadcast_day));
                    $fila->month         = date("m", strtotime($r->broadcast_day));
                    $fila->year          = date("Y", strtotime($r->broadcast_day));
                    $times_per_day       += $r->times_per_day;
                    $fila->times_per_day = $times_per_day;
                    $aux   = $fila;
                    $fila  = (object)[];
                }
                $response[] = $aux;
                $aux        = null;
            }
        }

        return $response;
    }

    function weekOfMonth($date): int {
        //Get the first day of the month.
        $firstOfMonth = strtotime(date("Y-m-01", $date));
        //Apply above formula.
        return $this->weekOfYear($date) - $this->weekOfYear($firstOfMonth) + 1;
    }

    function weekOfYear($date): int {
        $weekOfYear = intval(date("W", $date));
        if (date('n', $date) == "1" && $weekOfYear > 51) {
            // It's the last week of the previos year.
            $weekOfYear = 0;
        }
        return $weekOfYear;
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

    public function getAuspiceUnitCost($cost, $passes, $totalMaterial) {
        $total_cost = $cost / $totalMaterial;
        return $total_cost / $passes;
    }

    function verifyWeek($aux): int {
        return $aux->week ?? 1;
    }

    function buildWhere($request, $isCampaign): array {
        $where = [['clients.deleted_at', '=', null], ['guides.editable', '<>', 2], ['guides.deleted_at', '=', null]];
        if ($request['clientId']) {
            $where[] = ['clients.id', '=', $request->clientId];
        }

        if ($request['planId']) {
            $where[] = ['plan.id', '=', $request->planId];
        }

        if ($request['campaignId']) {
            $where[] = ['campaigns.id', '=', $request->campaignId];
        }

        if ($isCampaign) {
            $where[] = ['materials.deleted_at', '=', null];
        } else {
            $where[] = ['auspice_materials.deleted_at', '=', null];
            $where[] = ['auspices.deleted_at', '=', null];
        }
        $where[] = ['guides.deleted_at', '=', null];

        return $where;
    }

    function buildDatesWhere($request, $isCampaign, $materialId): array {
        $where = $isCampaign ? [['material_id', '=', $materialId]] : [['material_auspice_id', '=', $materialId]];
        if ($request['dateIni'] && $request['dateEnd']) {
            $where[] = ['broadcast_day', '>=', $this->getDate($request['dateIni'])];
            $where[] = ['broadcast_day', '<=', $this->getDate($request['dateEnd'])];
        }

        if ($request['dateIni'] && !$request['dateEnd']) {
            $where[] = ['broadcast_day', '>=', $this->getDate($request['dateIni'])];
        }

        if (!$request['dateIni'] && $request['dateEnd']) {
            $where[] = ['broadcast_day', '<=', $this->getDate($request['dateEnd'])];
        }
        return $where;
    }

    function getDate($timezone): string {
        $date = new DateTime($timezone);
        return $date->format('Y-m-d');
    }
}
