<?php

namespace App\Http\Controllers\API;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Validator;

class PlanController extends BaseController {
    public function index (Request $request) {
        if (Auth::check()) {
            $sort = explode(":", $request->sort);
            return $this->sendResponse(DB::table('plan')
                ->select('plan.id', 'plan_name as planName', 'client_name as clientName')
                ->join('clients', 'clients.id', '=', 'plan.client_id')
                ->where('plan.deleted_at', '=', null)
                ->orderBy(empty($sort[0]) ? 'plan.id' : 'plan.'.$sort[0], empty($sort[1]) ? 'asc' : $sort[1])
                ->get(), '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function store(Request $request) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'plan_name' => 'required',
                'client_id' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $plan = new Plan(array(
                'plan_name'   => trim($request->plan_name),
                'client_id'   => trim($request->client_id)
            ));

            return $plan->save() ?
                $this->sendResponse('', 'El plan ' . $plan->plan_name . ' se guardo correctamente') :
                $this->sendError('Ocurrio un error al crear un nuevo plan.');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function show($id) {
        if (Auth::check()){
            $plan = Plan::find($id);
            if (!$plan) {
                return $this->sendError('No se contro el plan');
            }
            return $this->sendResponse($plan, '');
        } else {
            return $this->sendError('No esta autenticado');
        }
    }

    public function update(Request $request, $id) {
        if (Auth::check()){
            $validator = Validator::make($request->all(), [
                'plan_name' => 'required',
                'client_id' => 'required',
            ]);

            if($validator->fails()){
                return $this->sendError('Error de validacion.', $validator->errors());
            }

            $plan = Plan::find($id);
            if (!$plan) {
                return $this->sendError('No se encontro el plan');
            }

            $plan->plan_name = trim($request->plan_name);
            $plan->client_id = trim($request->client_id);

            return $plan->save() ?
                $this->sendResponse('', 'El plan ' . $plan->plan_name . ' se actualizo correctamente') :
                $this->sendError('Ocurrio un error al actualizar el plan ' . $plan->plan_name . '.');
        } else {
            return $this->sendError('No esta autenticado');
        }
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
}
