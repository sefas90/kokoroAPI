<?php

namespace App\Exports;

use App\Http\Controllers\API\ExportController;
use App\Models\City;
use App\Models\Client;
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
                $fila->day           = $this->weekOfMonth(strtotime($r->broadcast_day));
                $fila->month         = date("m", strtotime($r->broadcast_day));
                $fila->year          = date("Y", strtotime($r->broadcast_day));
                $fila->times_per_day = $r->times_per_day;
                $fila->user          = $user;
                $fila->row           = $row;
                $response[]          = $fila;
                $fila                = (object)[];
            }
        }

        return view('exports.reports', [
            'datas' => $response
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
}
