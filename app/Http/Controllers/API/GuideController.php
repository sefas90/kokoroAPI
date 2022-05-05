<?php

namespace App\Http\Controllers\API;

use App\Models\Auspice;
use App\Models\AuspiceMaterial;
use App\Models\Guide;
use App\Models\Material;
use App\Models\OrderNumber;
use App\Models\PlaningAuspiceMaterial;
use App\Models\PlaningMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class GuideController extends BaseController {
    // automatico = 0
    // el costo se divide

    // manual = 1
    // el costo se ingresa
    public function index (Request $request) {
        $sort = explode(":", $request->sort);
        $search = $request->search;
        $where = [
            ['campaigns.deleted_at', '=', null],
            ['guides.deleted_at', '=', null],
            ['guides.editable', '<>', 2]
        ];
        if (isset($search)) {
            array_push($where, ['campaigns.id', '=', $search]);
        }
        $result_guide = DB::table('guides')
            ->select('guides.id', 'guide_name as guideName', 'guides.date_ini as dateIni', 'campaigns.id as budget', 'clients.client_name as clientName',
                'media.NIT as billingNumber', 'media.business_name as billingName', 'guides.date_end as dateEnd', 'media.id as mediaId', 'media_name as mediaName',
                'campaigns.id as campaignId', 'campaign_name as campaignName', 'guides.id as guideId', 'editable as status', 'guides.billing_number as invoiceNumber',
                'media_types.media_type as mediaTypeValue', 'guides.manual_apportion as manualApportion', 'guides.cost',
                'plan.plan_name as planName', 'campaigns.campaign_name as campaignName', 'campaigns.product', 'guides.guide_parent_id')
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where($where)
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
            } else {
                $orderNumber = OrderNumber::where('guide_id', '=', $row->guide_parent_id)->get();
                if (count($orderNumber) > 0) {
                    $orderNumber = $orderNumber[0];
                    if ($orderNumber->order_number) {
                        $number = $orderNumber->order_number . '.' . $orderNumber->version;
                    }
                }
            }

            $result_guide[$key]->totalCost = filter_var($result_guide[$key]->manualApportion, FILTER_VALIDATE_BOOLEAN) ? $this->getManualGuideCost($row->guideId) : $result_guide[$key]->cost;
            $result_guide[$key]->orderNumber = $number;
        }

        return $this->sendResponse($result_guide);
    }

    public function store(Request $request) {
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

        $guide = new Guide(array(
            'guide_name'      => trim($request->guideName),
            'date_ini'        => trim($request->dateIni),
            'date_end'        => trim($request->dateEnd),
            'media_id'        => trim($request->mediaId),
            'campaign_id'     => trim($request->campaignId),
            'guide_parent_id' => trim($request->guideParentId),
            'billing_number'  => null,
            'editable'        => 1,
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

        $guide->guide_name      = trim($request->guideName);
        $guide->date_ini        = trim($request->dateIni);
        $guide->date_end        = trim($request->dateEnd);
        $guide->media_id        = trim($request->mediaId);
        $guide->campaign_id     = trim($request->campaignId);
        $guide->guide_parent_id = trim($request->guideParentId);

        // TODO review this logic with the client
        /*if ($guide->media_id) {
            // remove associated materials
            $materials = Material::where('guide_id', '=', $id)->get();
            foreach ($materials as $key => $row) {
                $material = Material::find($row->id);
                $material->guide_id = 0;
                $material->save();
                // $material->delete();
            }
        }*/

        return $guide->save() ?
            $this->sendResponse('', 'El guide ' . $guide->guide_name . ' se actualizo correctamente') :
            $this->sendError('Ocurrio un error al actualizar el guide ' . $guide->guide_name . '.');
    }

    public function destroy($id) {
        $guide = Guide::find($id);
        if (!$guide) {
            return $this->sendError('No se encontro la guia');
        }
        // TODO WTF with this code
        /*if (count(Material::where('guide_id', '=', $guide->id)->get()) > 0) {
            return $this->sendError('unD_Material', null, 200);
        }*/

        return $guide->delete() ?
            $this->sendResponse('', 'La puta ' . $guide->guide_name . ' se elimino correctamente.') :
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

    public function guideParentList($id) {
        $guides = Guide::where([
            ['guide_parent_id', '=', null],
            ['campaign.id', '=', $id]
        ])
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->get();
        return $this->sendResponse($guides);
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

    function getManualGuideCost($guideId): float {
        $total_cost = 0;
        $material = Material::where([
            ['guide_id', '=', $guideId],
            ['deleted_at', '=', null]
        ])->get();
        foreach ($material as $k => $r) {
            $total_cost += $r->total_cost;
        }
        return round($total_cost, 2);
    }

    function updateCost() {
        $where = [['campaigns.deleted_at', '=', null], ['guides.manual_apportion', '=', 1]];
        $result_guide = DB::table('guides')
            ->select('guides.id as guideId', 'guide_name as guideName', 'guides.date_ini as dateIni', 'campaigns.id as budget', 'clients.client_name as clientName',
                'media.NIT as billingNumber', 'media.business_name as billingName', 'guides.date_end as dateEnd', 'media.id as mediaId', 'media_name as mediaName',
                'campaigns.id as campaignId', 'campaign_name as campaignName', 'guides.id as guideId', 'editable as status', 'guides.billing_number as invoiceNumber',
                'media_types.media_type as mediaTypeValue', 'guides.manual_apportion as manualApportion', 'guides.cost',
                'plan.plan_name as planName', 'campaigns.campaign_name as campaignName', 'campaigns.product')
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('campaigns', 'campaigns.id', '=', 'guides.campaign_id')
            ->join('plan', 'plan.id', '=', 'campaigns.plan_id')
            ->join('clients', 'clients.id', '=', 'plan.client_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->where($where)
            ->orderBy(empty($sort[0]) ? 'guides.id' : 'guides.'.$sort[0], empty($sort[1]) ? 'desc' : $sort[1])
            ->get();

        foreach ($result_guide as $k => $r) {
            $msg = $this->getGuideCost($r->guideId);
        }

        return $this->sendResponse('success', $msg);
    }

    function migrateForReal() {
        $result_guide = DB::table('guides')
            ->select('guides.id', 'guide_name as guideName', 'guides.date_ini', 'guides.date_end',
                'guides.media_id', 'guides.campaign_id', 'guides.editable', 'guides.billing_number',
                'guides.created_at', 'guides.updated_at', 'guides.deleted_at')
            ->join('auspices', 'auspices.guide_id', '=', 'guides.id')
            ->where([
                ['guides.deleted_at', '=', null],
                ['auspices.deleted_at', '=', null]
            ])
            ->get();
        foreach ($result_guide as $k => $r) {
            $auspice = Auspice::where('guide_id', '=', $r->id)->get();
            $orderNumber = OrderNumber::where('guide_id', '=', $r->id)->get();
            foreach ($auspice as $key => $row) {
                Guide::create([
                'guide_name'       => $row->auspice_name,
                'cost'             => $row->cost,
                'manual_apportion' => $row->manual_apportion,
                'date_ini'         => $r->date_ini,
                'date_end'         => $r->date_end,
                'media_id'         => $r->media_id,
                'campaign_id'      => $r->campaign_id,
                'editable'         => $r->editable,
                'guide_parent_id'  => $row->guide_id,
                'billing_number'   => $row->billing_number,
                'created_at'       => $r->created_at,
                'updated_at'       => $r->updated_at,
                'deleted_at'       => $r->deleted_at
                ]);
                OrderNumber::create([
                    'order_number' => $orderNumber[0]->order_number,
                    'version'      => $orderNumber[0]->version,
                    'observation'  => $orderNumber[0]->observation,
                    'guide_id'     => $r->id
                ]);

                $guidesId = DB::table('guides')->get()->last();
                $materials = AuspiceMaterial::where('auspice_id', '=', $row->id)->get();

                foreach ($materials as $m => $material) {
                    Material::create([
                        'material_name' => $material->material_name,
                        'duration'      => $material->duration,
                        'total_cost'    => $material->total_cost,
                        'guide_id'      => $guidesId->id,
                        'rate_id'       => $row->rate_id,
                        'created_at'    => $material->created_at,
                        'updated_at'    => $material->updated_at,
                        'deleted_at'    => $material->deleted_at
                    ]);
                    $materialsId = DB::table('materials')->get()->last();
                    $materialPlaning = PlaningAuspiceMaterial::where('material_auspice_id', '=', $material->id)->get();
                    foreach ($materialPlaning as $p => $planing) {
                        PlaningMaterial::create([
                            'broadcast_day' => $planing->broadcast_day,
                            'times_per_day' => $planing->times_per_day,
                            'material_id'   => $materialsId->id
                        ]);
                    }

                }
            }

        }

        return $this->sendResponse('ok');
    }

    public function getGuideCost($id) {
        $total_cost = 0;
        $result = Material::select('materials.id as id', 'materials.material_name', 'materials.duration', 'materials.guide_id', 'materials.rate_id',
                'guides.guide_name', 'guides.media_id', 'guides.campaign_id', 'guides.editable as editable', 'rates.show',
                'rates.hour_ini', 'rates.hour_end', 'rates.cost', 'media.media_name', 'media.business_name', 'media.NIT',
                'media.media_type as mediaTypeId', 'media_types.media_type', 'campaigns.campaign_name', 'campaigns.plan_id',
                'plan.client_id', 'campaigns.date_ini', 'campaigns.date_end', 'rates.hour_ini as hourIni', 'rates.hour_end as hourEnd',
                'guides.date_ini as guideDateIni', 'guides.editable', 'clients.id as clientId', 'clients.client_name as clientName',
                'clients.representative', 'clients.NIT as clientNIT', 'clients.billing_address as billingAddress',
                'clients.billing_policies as billingPolicies', 'guides.guide_parent_id')
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
                ['guides.editable', '<>', 2],
                ['guides.guide_parent_id', '=', null]
            ])
            ->get();

        foreach ($result as $ke => $ro) {
            $other_id = $ro->id;
            $planing = PlaningMaterial::select('broadcast_day', 'times_per_day')
                ->where('material_planing.material_id', '=', $other_id)->get();
            $spots = 0;
            foreach ($planing as $k => $r) {
                $spots += $r->times_per_day;
                $ro->spots = $spots;
            }
            $ro->spots = $spots;
            $ro->unitCost = $this->getUnitCost($ro->cost, $ro->media_type, $ro->duration);
            $ro->totalCost = $this->getTotalCost($ro->cost, $ro->media_type, $ro->duration, $ro->spots);
            $total_cost += $ro->totalCost;
            $material = Material::find($ro->id);
            $material->total_cost =  $ro->totalCost;
            $material->save();
        }
        return $total_cost;
    }

    public function getGuideMaterials($id) {
        $guide = Guide::where('guides.id', '=', $id)
            ->select('guides.id', 'guide_name as guideName', 'guides.cost', 'manual_apportion', 'guides.date_ini as dateIni', 'guides.date_end as dateEnd', 'media_types.media_type as mediaTypeValue')
            ->join('media', 'media.id', '=', 'guides.media_id')
            ->join('media_types', 'media_types.id', '=', 'media.media_type')
            ->get();

        $guide = $guide[0];
        $material = Material::select('materials.id', 'material_name as materialName', 'duration', 'total_cost', 'rates.show')
            ->join('rates', 'rates.id', '=', 'materials.rate_id')
            ->where('guide_id', '=', $id)->get();
        if (!$material) {
            return $this->sendResponse([]);
        }

        foreach ($material as $key => $row) {
            $material_count = DB::table('material_planing')
                ->where('material_id', '=', $row->id)
                ->sum('times_per_day');

            $material_planing = DB::table('material_planing')
                ->where('material_id', '=', $row->id)->get();

            $material[$key]->passes = (int)$material_count;
            if(filter_var($guide->manual_apportion, FILTER_VALIDATE_BOOLEAN)) {
                $material[$key]->cost = $material[$key]->total_cost;
            } else {
                $material[$key]->cost = number_format($guide->cost / count($material), 2, '.', '');
            }

            $passes = [];
            foreach ($material_planing as $k => $r) {
                $passes[$r->broadcast_day] = [
                    'date' => date('Y-m-d h:i:s', strtotime($r->broadcast_day)),
                    'timesPerDay' => $r->times_per_day
                ];
            }
            $material[$key]->timesPerDay = $passes;
            $material[$key]->guideName = $guide->guideName;
            $material[$key]->mediaTypeValue = $guide->mediaTypeValue;
        }
        return $this->sendResponse($material);
    }
}
