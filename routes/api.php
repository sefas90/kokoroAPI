<?php

use App\Http\Controllers\API\CurrencyController;
use App\Http\Controllers\API\ExportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AuspiceController;
use App\Http\Controllers\API\CampaignController;
use App\Http\Controllers\API\CityController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\GuideController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\MediaController;
use App\Http\Controllers\API\MediaTypeController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\RateController;
use App\Http\Controllers\API\ResourceController;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Auth
Route::group(['middleware' => ['cors']], function (){
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});
// Endpoints
$attributes = [
    'prefix' => 'v1',
    'auth' => 'api',
    'middleware' => 'cors'
];

Route::group($attributes, function () {
    Route::get('/currency', [CurrencyController::class, 'index']);
    Route::post('/currency', [CurrencyController::class, 'store']);
    Route::get('/currency/{id}', [CurrencyController::class, 'show']);
    Route::post('/currency/{id}', [CurrencyController::class, 'update']);
    Route::delete('/currency/{id}', [CurrencyController::class, 'destroy']);

    Route::get('/auspiceMaterial/{id}', [AuspiceController::class, 'getAuspiceMaterial']);
    Route::post('/auspiceMaterial', [AuspiceController::class, 'auspiceMaterial']);
    Route::post('/auspiceMaterial/{id}', [AuspiceController::class, 'updateMaterial']);
    Route::delete('/auspiceMaterial/{id}', [AuspiceController::class, 'deleteAuspiceMaterial']);

    Route::get('/guideMaterial/{id}', [GuideController::class, 'getGuideMaterials']);

    Route::get('/campaign', [CampaignController::class, 'index']);
    Route::post('/campaign', [CampaignController::class, 'store']);
    Route::get('/campaign/{id}', [CampaignController::class, 'show']);
    Route::post('/campaign/{id}', [CampaignController::class, 'update']);
    Route::delete('/campaign/{id}', [CampaignController::class, 'destroy']);

    Route::get('/client', [ClientController::class, 'index']);
    Route::post('/client', [ClientController::class, 'store']);
    Route::get('/client/{id}', [ClientController::class, 'show']);
    Route::post('/client/{id}', [ClientController::class, 'update']);
    Route::delete('/client/{id}', [ClientController::class, 'destroy']);

    Route::get('/guide', [GuideController::class, 'index']);
    Route::post('/guide', [GuideController::class, 'store']);
    Route::get('/guide/{id}', [GuideController::class, 'show']);
    Route::post('/guide/{id}', [GuideController::class, 'update']);
    Route::delete('/guide/{id}', [GuideController::class, 'destroy']);
    Route::post('/guide/editInvoice/{id}', [GuideController::class, 'editInvoiceNumber']);
    Route::get('/guideMediaList/{id}', [GuideController::class, 'getMediaListByGuide']);

    Route::get('/material', [MaterialController::class, 'index']);
    Route::post('/material', [MaterialController::class, 'store']);
    Route::get('/material/{id}', [MaterialController::class, 'show']);
    Route::post('/material/{id}', [MaterialController::class, 'update']);
    Route::delete('/material/{id}', [MaterialController::class, 'destroy']);

    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::get('/media/{id}', [MediaController::class, 'show']);
    Route::post('/media/{id}', [MediaController::class, 'update']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);

    Route::get('/permissions', [PermissionController::class, 'permissions']);

    Route::get('/plan', [PlanController::class, 'index']);
    Route::post('/plan', [PlanController::class, 'store']);
    Route::get('/plan/{id}', [PlanController::class, 'show']);
    Route::post('/plan/{id}', [PlanController::class, 'update']);
    Route::delete('/plan/{id}', [PlanController::class, 'destroy']);

    Route::get('/rate', [RateController::class, 'index']);
    Route::post('/rate', [RateController::class, 'store']);
    Route::get('/rate/{id}', [RateController::class, 'show']);
    Route::post('/rate/{id}', [RateController::class, 'update']);
    Route::delete('/rate/{id}', [RateController::class, 'destroy']);

    Route::get('/resources', [ResourceController::class, 'permissions']);
    Route::get('/roles', [RoleController::class, 'index']);

    Route::get('/user', [UserController::class, 'index']);
    Route::post('/user', [UserController::class, 'store']);
    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::post('/user/{id}', [UserController::class, 'update']);
    Route::delete('/user/{id}', [UserController::class, 'destroy']);

    // Dropdowns
    Route::get('/citiesList', [CityController::class, 'index']);
    Route::get('/mediaTypesList', [MediaTypeController::class, 'index']);
    Route::get('/guideList', [GuideController::class, 'list']);
    Route::get('/planList', [PlanController::class, 'list']);
    Route::get('/rateList', [RateController::class, 'list']);
    Route::get('/campaignList', [CampaignController::class, 'list']);
    Route::get('/clientList', [ClientController::class, 'list']);
    Route::get('/mediaList', [MediaController::class, 'list']);
    Route::get('/mediaParentList', [MediaController::class, 'parentList']);
    Route::get('/mediaParentList/{id}', [MediaController::class, 'parentList']);
    Route::get('/showList', [RateController::class, 'showLists']);
    Route::get('/clientsPlanList/{id}', [PlanController::class, 'clientsPlanList']);
    Route::get('/plansCampaignsList/{id}', [CampaignController::class, 'plansCampaignsList']);
    Route::get('/rateGuideList/{id}', [RateController::class, 'rateGuideList']);
    Route::get('/currencyList', [CurrencyController::class, 'list']);

    // export Orders
    Route::post('/exportOrder', [ExportController::class, 'order']);
    Route::post('/exportOrdersByCampaign', [ExportController::class, 'orderByCampaign']);
    Route::post('/exportAuspice', [ExportController::class, 'auspice']);

    // Reports
    Route::post('/reportExcel', [ExportController::class, 'export']);

    //OrderNumber
    Route::get('/orderNumber/{id}', [ExportController::class, 'orderNumber']);
    Route::get('/orderNumber/{id}/auspice', [ExportController::class, 'orderNumberAuspice']);

    //Finalize guide
    Route::post('/finalizeGuide', [GuideController::class, 'finalizeGuide']);
    Route::post('/cancelGuide', [GuideController::class, 'cancelGuide']);
    Route::post('/activateGuide', [GuideController::class, 'activateGuide']);

    // migration
    Route::get('/updateCost', [GuideController::class, 'updateCost']);
    Route::get('/migrateForReal', [GuideController::class, 'migrateForReal']);
});
