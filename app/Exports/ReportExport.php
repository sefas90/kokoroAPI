<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\Currency;
use App\Models\Material;
use App\Models\PlaningMaterial;
use App\Models\User;
use DateInterval;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
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
        $data = [$campaign, $request['currency']];
        return view('exports.reports', ['datas' => $data]);
    }

    public function getCampaignReport($request): array {
        $where = $this->buildWhere($request, true);
        $result = Client::select('clients.id as client_id', 'client_name', 'representative', 'clients.NIT as clientNit',
            'billing_address', 'billing_policies', 'plan_name', 'campaigns.id as budget', 'plan.id as plan_id',
            'guide_name', 'guides.id as guide_id', 'manual_apportion as manualApportion', 'media.id as media_id',
            'material_name', 'duration', 'materials.id as material_id', 'materials.material_name', 'product', 'materials.total_cost as materialCost',
            'campaign_name', 'guides.billing_number', 'rates.id as rate_id', 'show', 'rates.cost', 'media_name',
            'guides.cost as guideCost',
            'business_name', 'cities.id as city_id', 'city', 'media_types.media_type')
            ->join('plan', 'plan.client_id', '=', 'clients.id')
            ->join('campaigns', 'campaigns.plan_id', '=', 'plan.id')
            ->join('guides', 'guides.campaign_id', '=', 'campaigns.id')
            ->join('materials', 'materials.guide_id', '=', 'guides.id')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->join('cities', 'cities.id', '=', 'media.city_id')
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
            $plan = PlaningMaterial::where($where)
                ->select('*')
                ->get();
            $times_per_day = 0;
            $started = false;
            foreach ($plan as $k => $r) {
                $fila->weekOfYear    = $this->weekOfYear(strtotime($r->broadcast_day));
                if ($started && $aux->weekOfYear != $fila->weekOfYear) {
                    $response[]      = $aux;
                    $times_per_day   = 0;
                } else {
                    $started = true;
                }

                $totalPasses = 0;
                foreach ($plan as $pl => $pla) {
                    $totalPasses += $r->times_per_day;
                }
                $materials = Material::where('guide_id', '=', $row->guide_id)->get();
                $fila->user          = $user;
                $fila->row           = $row;
                $fila->cost          = $this->getUnitCost($row->cost, $row->media_type, $row->duration);
                $fila->passes        = $totalPasses;
                $fila->totalCost     = filter_var($row->manualApportion, FILTER_VALIDATE_BOOLEAN) ?
                    $row->materialCost : $row->guideCost / count($materials);
                $fila->weeksInMonth  = $this->weeksInMonth(new DateTime($r->broadcast_day));
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

    public function getManualGuideCost($guideId): float {
        $total_cost = 0;
        $material = Material::where([
            ['guide_id', '=', $guideId],
            ['deleted_at', '=', null]
        ])->get();

        foreach ($material as $k => $r) {
            $total_passes = PlaningMaterial::where('material_id', '=', $r->id)->sum('times_per_day');
            $total_passes = floor($total_passes);
            $total_cost += $r->total_cost / $total_passes;
        }
        return $total_cost;
    }

    public function getAutoGuideCost($cost, $guideId) {
        $total_passes = 0;
        $countMaterials = count(Material::where('guide_id', $guideId)->get());
        $material = Material::where([
            ['guide_id', '=', $guideId],
            ['deleted_at', '=', null]
        ])->get();

        foreach ($material as $k => $r) {
            $total_passes = PlaningMaterial::where('material_id', '=', $r->id)->sum('times_per_day');
            $total_passes += floor($total_passes);
        }
        return ($cost / $countMaterials) / $total_passes;
    }

    function weeksInMonth($month_date, $count_last=true) {
        $fn = $count_last ? 'ceil' : 'floor';
        $start = new DateTime($month_date->format('Y-m'));
        $days = (clone $start)->add(new DateInterval('P1M'))->diff($start)->days;
        $offset = $month_date->format('N') - 1;
        return $fn(($days + $offset)/7);
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
