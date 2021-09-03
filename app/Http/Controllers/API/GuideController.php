<?php

namespace App\Http\Controllers\API;

use App\Models\Auspice;
use App\Models\Guide;
use App\Models\Material;
use App\Models\OrderNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class GuideController extends BaseController {
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        $result_guide = DB::table('guides')
            ->select('guides.id', 'guide_name as guideName', 'guides.date_ini as dateIni', 'campaigns.id as budget', 'clients.client_name as clientName',
                'media.NIT as billingNumber', 'media.business_name as billingName', 'guides.date_end as dateEnd', 'media.id as mediaId', 'media_name as mediaName',
                'campaigns.id as campaignId', 'campaign_name as campaignName', 'guides.id as guideId', 'editable as status', 'guides.billing_number as invoiceNumber',
                'media_types.media_type as mediaTypeValue')
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where('guides.deleted_at', '=', null)
            ->orderBy(empty($sort[0]) ? 'guides.id' : 'guides.'.$sort[0], empty($sort[1]) ? 'desc' : $sort[1])
            ->get();

        foreach ($result_guide as $key => $row) {
            $orderNumber = OrderNumber::where('guide_id', '=', $row->guideId)->get();

            $number = 'Orden no exportada';
            if (count($orderNumber) > 0) {
                $orderNumber = $orderNumber[0];
                if ($orderNumber->order_number) {
                    $number = $orderNumber->order_number.'.'.$orderNumber->version;
                }
            }

            $result_guide[$key]->totalCost = $this->getGuideCost($row->guideId);
            $result_guide[$key]->orderNumber = $number;
        }

        return $this->sendResponse($result_guide);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'guideName'  => 'required',
            'dateIni'      => ['required', 'before_or_equal:dateEnd'],
            'dateEnd'      => ['required', 'after_or_equal:dateIni'],
            'mediaId'    => 'required',
            'campaignId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $guide = new Guide(array(
            'guide_name'     => trim($request->guideName),
            'date_ini'       => trim($request->dateIni),
            'date_end'       => trim($request->dateEnd),
            'media_id'       => trim($request->mediaId),
            'campaign_id'    => trim($request->campaignId),
            'billing_number' => null,
            'editable'       => 1,
        ));

        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo guide.');
    }

    public function show($id) {
        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se contro el guide');
        }
        return $this->sendResponse($guide, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'guideName'  => 'required',
            'dateIni'    => ['required', 'before_or_equal:dateEnd'],
            'dateEnd'    => ['required', 'after_or_equal:dateIni'],
            'mediaId'    => 'required',
            'campaignId' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se encontro el guide');
        }

        $guide->guide_name = trim($request->guideName);
        $guide->date_ini   = trim($request->dateIni);
        $guide->date_end   = trim($request->dateEnd);
        $guide->media_id   = trim($request->mediaId);
        $guide->campaign_id   = trim($request->campaignId);

        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el guide ' . $guide->guide_name . '.');
    }

    public function destroy($id) {
        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se encontro la guia');
        }

        if (count(Material::where('guide_id', '=', $guide->id)->get()) > 0) {
            return $this->sendError('unD_Material', null, 200);
        }

        if (count(Auspice::where('guide_id', '=', $guide->id)->get()) > 0) {
            return $this->sendError('unD_Auspice', null, 200);
        }

        return $guide->delete() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se elimino correctamente.') :
            $this->sendError('El guide ' . $guide->guide_name .' no se pudo eliminar.');
    }

    public function list() {
        return $this->sendResponse(DB::table('guides')
            ->select('id', 'id as value', 'guide_name as label', 'date_ini as dateIni', 'date_end as dateEnd')
            ->where([
                ['deleted_at', '=', null],
                ['editable', '=', 1],
            ])
            ->get(), '');
    }

    public function finalizeGuide(Request $request) {
        $guide = Guide::find($request->guideId);
        if (!$guide) {
            return $this->sendError('No se encontro la pauta');
        }
        $guide->editable = 0;
        $guide->billing_number = $request->billingNumber;

        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se finalizo correctamente') :
            $this->sendError('Ocurrio un error al finalizar la pauta ' . $guide->guide_name . '.');
    }

    public function cancelGuide(Request $request) {
        $guide = Guide::find($request->guideId);
        if (!$guide) {
            return $this->sendError('No se encontro la pauta');
        }

        $guide->editable = 2;
        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se cancelo correctamente') :
            $this->sendError('Ocurrio un error al cancelar la pauta ' . $guide->guide_name . '.');
    }

    public function activateGuide(Request $request) {
        $guide = Guide::find($request->guideId);
        if (!$guide) {
            return $this->sendError('No se encontro la pauta');
        }

        $guide->editable = 1;
        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se activo correctamente') :
            $this->sendError('Ocurrio un error al activar la pauta ' . $guide->guide_name . '.');
    }

    public function editInvoiceNumber(Request $request, $id) {
        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se encontro la pauta');
        }

        $guide->billing_number = $request->billingNumber;
        return $guide->save() ?
            $this->sendResponse('', 'La guia ' . $guide->guide_name . ' se modifico correctamente') :
            $this->sendError('Ocurrio un error al modificar la pauta ' . $guide->guide_name . '.');
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

    public function getMediaListByGuide($id) {
        return $this->sendResponse(DB::table('guides')
            ->select('id', 'id as value', 'guide_name as label', 'date_ini as dateIni', 'date_end as dateEnd')
            ->where([
                ['media_id', '=', $id],
                ['deleted_at', '=', null],
                ['editable', '=', 1],
            ])
            ->get(), '');
    }

    public function getGuideCost($id) {
        $total_cost = 0;
        $result = DB::table('materials')
            ->select('materials.id as id', 'materials.material_name', 'materials.duration', 'materials.guide_id', 'materials.rate_id',
                'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.editable as editable', 'rates.show',
                'rates.hour_ini', 'rates.hour_end', 'rates.cost', 'media.media_name', 'media.business_name', 'media.NIT',
                'media.media_type as mediaTypeId', 'media_types.media_type', 'campaigns.campaign_name', 'campaigns.plan_id',
                'plan.client_id', 'campaigns.date_ini', 'campaigns.date_end', 'rates.hour_ini as hourIni', 'rates.hour_end as hourEnd',
                'guides.date_ini as guideDateIni', 'guides.editable', 'clients.id as clientId', 'clients.client_name as clientName',
                'clients.representative', 'clients.NIT as clientNIT', 'clients.billing_address as billingAddress',
                'clients.billing_policies as billingPolicies')
            ->join('guides', 'guides.id', '=', 'materials.guide_id')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->join('media', 'media.id', '=', 'rates.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where([
                ['materials.deleted_at', '=', null],
                ['guides.deleted_at', '=', null],
                ['rates.deleted_at', '=', null],
                ['media.deleted_at', '=', null],
                ['campaigns.deleted_at', '=', null],
                ['plan.deleted_at', '=', null],
                ['clients.deleted_at', '=', null],
                ['guides.id', '=', $id],
                ['guides.editable', '<>', 2]
            ])
            ->get();

        foreach ($result as $ke => $ro) {
            $id = $result[$ke]->id;
            $planing = DB::table('material_planing')
                ->select('broadcast_day', 'times_per_day')
                ->where('material_planing.material_id', '=', $id)->get();
            $spots = 0;
            foreach ($planing as $k => $r) {
                $spots += $r->times_per_day;
                $result[$ke]->spots = $spots;
            }
            $result[$ke]->spots = $spots;
            $result[$ke]->unitCost = $this->getUnitCost($result[$ke]->cost, $result[$ke]->media_type, $result[$ke]->duration);
            $result[$ke]->totalCost = $this->getTotalCost($result[$ke]->cost, $result[$ke]->media_type, $result[$ke]->duration, $result[$ke]->spots);
            $total_cost += $result[$ke]->totalCost;
        }
        return $total_cost;
    }
}
