<?php

namespace App\Http\Controllers\API;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class PlanController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        return $this->sendResponse(DB::table('plan')
            ->select('plan.id', 'plan_name as planName', 'client_name as clientName', 'plan.client_id as clientId')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->where('plan.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'plan.id' : 'plan.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
            ->get(), '');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'planName' => 'required',
            'clientId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $plan = new Plan(array(
            'plan_name'   => trim($request->planName),
            'client_id'   => trim($request->clientId)
        ));

        return $plan->save() ?
            $this->sendResponse('', 'El plan ' . $plan->plan_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo plan.');
    }

    public function show($id) {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->sendError('No se contro el plan');
        }
        return $this->sendResponse($plan, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'planName' => 'required',
            'clientId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $plan = Plan::find($id);
        if (!$plan) {
            return $this->sendError('No se encontro el plan');
        }

        $plan->plan_name = trim($request->planName);
        $plan->client_id = trim($request->clientId);

        return $plan->save() ?
            $this->sendResponse('', 'El plan ' . $plan->plan_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el plan ' . $plan->plan_name . '.');
    }

    public function destroy($id) {
        $plan = Plan::find($id);
        if (!$plan) {
            return $this->sendError('No se encontro el plan');
        }

        return $plan->delete() ?
            $this->sendResponse('', 'El plan ' . $plan->plan_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un plan');
    }

    public function list() {
        return $this->sendResponse(DB::table('plan')
            ->select('id', 'id as value', 'plan_name as label')
            ->where('deleted_at', '=', null)
            ->get(), '');
    }
}
