<?php

namespace App\Exports;

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

    private $fileName = 'something.xlsx';

    public function request($req) {
        $this->req = $req;
        return $this;
    }

    public function view(): View {
        $request = $this->req;
        $currency = Currency::find($request->currencyId) ? Currency::find($request->currencyId) : Currency::find(1);
        $result = Client::select('clients.id as client_id', 'client_name', 'representative', 'clients.NIT as clientNit', 'billing_address', 'billing_policies',
            'plan_name', 'campaigns.id as budget', 'plan.id as plan_id', 'guide_name', 'guides.id as guide_id', 'order_number', 'order_numbers.version',
            'media.id as media_id', 'material_name', 'duration', 'materials.id as material_id', 'product', 'campaign_name',
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
            ->where([
                ['clients.deleted_at', '=', null],
                ['clients.id', '=', $request->clientId],
                ['plan.id', '=', $request->planId],
                ['campaigns.id', '=', $request->campaignId]
            ])
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
                $fila->currencyValue = $currency->currency_value;
                $fila->duration      = $row->duration;
                $fila->broadcast_day = $r->broadcast_day;
                $fila->month         = date("m", strtotime($r->broadcast_day));
                $fila->year          = date("Y", strtotime($r->broadcast_day));
                $times_per_day       += $r->times_per_day;
                $fila->times_per_day = $times_per_day;
                $aux   = $fila;
                $fila  = (object)[];
            }
        }

        $datas = [$response, $currency];

        return view('exports.reports', [
            'datas' => $datas
        ]);
    }

    function weekOfMonth($date) {
        //Get the first day of the month.
        $firstOfMonth = strtotime(date("Y-m-01", $date));
        //Apply above formula.
        return $this->weekOfYear($date) - $this->weekOfYear($firstOfMonth) + 1;
    }

    function weekOfYear($date) {
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

    function verifyWeek($aux) {
        return isset($aux->week) ? $aux->week : 1;
    }
}
