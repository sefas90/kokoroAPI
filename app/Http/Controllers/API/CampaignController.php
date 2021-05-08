<?php

namespace App\Http\Controllers\API;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('campaign')
            ->select('campaign.id', 'campaign_name as campaignName', 'client_name as clientName', 'date_ini', 'date_end')
            ->join('plan', 'plan.id', '=', 'campaign.plan_id')
            ->where('campaign.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'campaign.id' : 'campaign.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'campaign_name' => 'required',
            'date_ini'      => 'required',
            'date_end'      => 'required',
            'plan_id'       => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $campaign = new Campaign(array(
            'campaign_name' => trim($request->campaign_name),
            'date_ini'      => trim($request->date_ini),
            'date_end'      => trim($request->date_end),
            'plan_id'       => trim($request->plan_id)
        ));

        return $campaign->save() ?
            $this->sendResponse('', 'El campaign ' . $campaign->campaign_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear una nueva campaña.');
    }

    public function show($id) {
        $campaign = Campaign::find($id);
        if (!$campaign) {
            return $this->sendError('No se contro la campaña');
        }
        return $this->sendResponse($campaign, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'campaign_name' => 'required',
            'date_ini'      => 'required',
            'date_end'      => 'required',
            'plan_id'       => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $campaign = Campaign::find($id);
        if (!$campaign) {
            return $this->sendError('No se encontro la campaña');
        }

        $campaign->campaign_name = trim($request->campaign_name);
        $campaign->date_ini      = trim($request->date_ini);
        $campaign->date_end      = trim($request->date_end);
        $campaign->plan_id       = trim($request->plan_id);

        return $campaign->save() ?
            $this->sendResponse('', 'La campaña ' . $campaign->campaign_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar la campaña ' . $campaign->campaign_name . '.');
    }

    public function destroy($id) {
        $campaign = Campaign::find($id);
        if (!$campaign) {
            return $this->sendError('No se encontro la campaña');
        }

        return $campaign->delete() ?
            $this->sendResponse('', 'La campaña ' . $campaign->campaign_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar una campaña');
    }

    public function list() {
        return $this->sendResponse(DB::table('campaign')
            ->select('id', 'campaign_name as value', 'campaign_name as label')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
