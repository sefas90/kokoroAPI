<?php

namespace App\Http\Controllers\API;

use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class CampaignController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('campaigns')
            ->select('campaigns.id', 'campaign_name as campaignName', 'clients.client_name as clientName', 'clients.id as clientId', 'date_ini as dateIni', 'date_end as dateEnd', 'plan.plan_name as planName', 'plan.id as planId', 'product')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->where('campaigns.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'campaigns.id' : 'campaigns.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'campaignName' => 'required',
            'product'      => 'required',
            'dateIni'      => ['required', 'before:dateEnd'],
            'dateEnd'      => ['required', 'after:dateIni'],
            'planId'       => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $campaign = new Campaign(array(
            'campaign_name' => trim($request->campaignName),
            'product'       => trim($request->product),
            'date_ini'      => trim($request->dateIni),
            'date_end'      => trim($request->dateEnd),
            'plan_id'       => trim($request->planId),
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
            'campaignName' => 'required',
            'product'      => 'required',
            'dateIni'      => ['required', 'before:dateEnd'],
            'dateEnd'      => ['required', 'after:dateIni'],
            'planId'       => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $campaign = Campaign::find($id);
        if (!$campaign) {
            return $this->sendError('No se encontro la campaña');
        }

        $campaign->campaign_name = trim($request->campaignName);
        $campaign->product       = trim($request->product);
        $campaign->date_ini      = trim($request->dateIni);
        $campaign->date_end      = trim($request->dateEnd);
        $campaign->plan_id       = trim($request->planId);

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
        return $this->sendResponse(DB::table('campaigns')
            ->select('id', 'id as value', 'campaign_name as label', 'date_ini as dateIni', 'date_end as dateEnd')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }

    protected function plansCampaignsList($id) {
        return $this->sendResponse(DB::table('campaigns')
            ->select('id', 'id as value', 'campaign_name as label')
            ->where([
                ['deleted_at', '=', null],
                ['plan_id', '=', $id],
            ])
            ->get(), '');
    }
}
