<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller as Controller;
use Barryvdh\DomPDF\Facade as PDF;

class BaseController extends Controller {

    public function sendResponse($result, $message = '') {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404) {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }

    public function exportPdf($response, $view, $downloadName) {
        view()->share('data', $response);
        $pdf = PDF::loadView($view)->setPaper('letter', 'landscape');
        // $pdf = setOptions(['fontDir' => 'sweet_sans_prolight']);
        $pdf->getDomPDF()->set_option("enable_php", true);
        return $pdf->download($downloadName);
    }
}
