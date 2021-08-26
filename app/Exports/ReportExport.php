<?php

namespace App\Exports;

use App\Models\AuspiceMaterial;
use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

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
        $datas = [$campaign, $request['currency'], $auspice];

        return view('exports.reports', [
            'datas' => $datas
        ]);
    }

    public function getCampaignReport($request): array {
        $where = $this->buildWhere($request);
        $result = Client::select('clients.id as client_id', 'client_name', 'representative', 'clients.NIT as clientNit', 'billing_address', 'billing_policies',
            'plan_name', 'campaigns.id as budget', 'plan.id as plan_id', 'guide_name', 'guides.id as guide_id', 'order_number', 'order_numbers.version',
            'media.id as media_id', 'material_name', 'duration', 'materials.id as material_id', 'product', 'campaign_name', 'guides.billing_number',
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
            $plan = DB::table('material_planing')
                ->select('*')
                ->where('material_id', '=', $row->material_id)
                ->get();
            $week = 1;
            $times_per_day = 0;
            foreach ($plan as $k => $r) {
                $fila->week          = $this->weekOfMonth(strtotime($r->broadcast_day));
                if ($this->verifyWeek($aux) == $fila->week){
                } else {
                    $times_per_day       = 0;
                    $response[]          = $aux;
                    $aux                 = null;
                    $week++;
                }
                $fila->user          = $user;
                $fila->row           = $row;
                $fila->cost          = $this->getUnitCost($row->cost, $row->media_type, $row->duration);
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
        }
        return $response;
    }

    public function getAuspiceReport($request): array {
        $where = $this->buildWhere($request);
        $result = Client::select('clients.id as client_id', 'client_name', 'representative', 'clients.NIT as clientNit', 'billing_address', 'billing_policies',
            'plan_name', 'campaigns.id as budget', 'plan.id as plan_id', 'guide_name', 'guides.id as guide_id', 'order_number', 'order_numbers.version',
            'media.id as media_id', 'material_name', 'duration', 'auspice_materials.id as material_id', 'product', 'campaign_name', 'guides.billing_number',
            'rates.id as rate_id', 'show', 'auspices.cost', 'media_name', 'business_name', 'cities.id as city_id', 'city', 'media_types.media_type')
            ->join('plan', 'plan.client_id', '=', 'clients.id')
            ->join('campaigns', 'campaigns.plan_id', '=', 'plan.id')
            ->join('guides', 'guides.campaign_id', '=', 'campaigns.id')
            ->join('auspices', 'auspices.guide_id', '=', 'guides.id')
            ->join('auspice_materials', 'auspice_materials.auspice_id', '=', 'auspices.id')
            ->join('rates', 'rates.id', '=', 'auspice_materials.auspice_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->join('cities', 'cities.id', '=', 'media.city_id')
            ->join('order_numbers', 'order_numbers.guide_id', '=', 'guides.id')
            ->where($where)
            ->orderBy('auspice_materials.id')
            ->get();
        $user = User::find($request->userId);
        $user = empty($user) ? 'System' : $user->name . ' ' .$user->lastname;
        $fila = (object)[];
        $aux = null;
        $response = array();

        foreach ($result as $key => $row) {
            $material = AuspiceMaterial::where('id', '=', $row->material_id)->get();
            $plan = DB::table('material_auspice_planing')
                ->select('*')
                ->where('material_auspice_id', '=', $row->material_id)
                ->get();
            $total_passes = DB::table('material_auspice_planing')
                ->where('material_auspice_id', '=', $row->material_id)
                ->sum('times_per_day');

            $week = 1;
            $times_per_day = 0;
            foreach ($plan as $k => $r) {
                $fila->week          = $this->weekOfMonth(strtotime($r->broadcast_day));
                if ($this->verifyWeek($aux) == $fila->week){
                } else {
                    $times_per_day       = 0;
                    $response[]          = $aux;
                    $aux                 = null;
                    $week++;
                }
                $fila->user          = $user;
                $fila->row           = $row;
                $fila->cost          = $this->getAuspiceUnitCost($row->cost, $total_passes, count($material));
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

    function buildWhere($request): array {
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

        if ($request['dateIni'] && $request['dateEnd']) {
            $where[] = ['broadcast_day', '>=', $request['dateIni']];
            $where[] = ['broadcast_day', '<=', $request['dateEnd']];
        }

        if ($request['dateIni'] && !$request['dateEnd']) {
            $where[] = [];
        }

        if (!$request['dateIni'] && $request['dateEnd']) {
            $where[] = [];
        }
        return $where;
    }
}
