<?php

namespace App\Http\Controllers\API;

use App\Models\Currency;
use Illuminate\Http\Request;
use Validator;

class CurrencyController extends BaseController {

    public function index() {
        return $this->sendResponse(Currency::all('id', 'currency_name as currencyName', 'currency_value as currencyValue', 'symbol'));
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'currencyName'  => 'required',
            'currencyValue' => 'required',
            'symbol'         => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $currency = new Currency(array(
            'currency_name'  => trim($request->currencyName),
            'currency_value' => trim($request->currencyValue),
            'symbol'         => trim($request->symbol)
        ));

        return $currency->save() ?
            $this->sendResponse('', 'El tipo de cambio ' . $currency->currency_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo tipo de cambio.');
    }

    public function show($id) {
        $currency = Currency::find($id);
        if (!$currency) {
            return $this->sendError('No se contro el tipo de cambio');
        }
        return $this->sendResponse($currency, '');
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'currencyName'  => 'required',
            'currencyValue' => 'required',
            'symbol'         => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Error de validacion.', $validator->errors());
        }

        $currency = Currency::find($id);

        $currency->currency_name  = trim($request->currencyName);
        $currency->currency_value = trim($request->currencyValue);
        $currency->symbol         = trim($request->symbol);

        return $currency->save() ?
            $this->sendResponse('', 'El tipo de cambio ' . $currency->currency_name . ' se guardo correctamente') :
            $this->sendError('Ocurrio un error al crear un nuevo tipo de cambio.');
    }

    public function destroy($id) {
        $currency = Currency::find($id);
        if (!$currency) {
            return $this->sendError('No se encontro el tipo de cambio');
        }
        return $currency->delete() ?
            $this->sendResponse('', 'El tipo de cambio ' . $currency->currency_name . ' se elimino correctamente.') :
            $this->sendError('Ocurrio un error al eliminar un tipo de cambio');
    }
}
