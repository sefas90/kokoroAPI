<?php

use Illuminate\Http\Request;
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
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

$attributes = [
    'prefix' => 'v1'
];

Route::group($attributes, function () {
    Route::get('/auspice', [AuspiceController::class, 'index'])->middleware('auth:api');
    Route::post('/auspice', [AuspiceController::class, 'store'])->middleware('auth:api');
    Route::get('/auspice/{id}', [AuspiceController::class, 'show'])->middleware('auth:api');
    Route::post('/auspice/{id}', [AuspiceController::class, 'update'])->middleware('auth:api');
    Route::delete('/auspice/{id}', [AuspiceController::class, 'destroy'])->middleware('auth:api');

    Route::get('/campaign', [CampaignController::class, 'index'])->middleware('auth:api');
    Route::post('/campaign', [CampaignController::class, 'store'])->middleware('auth:api');
    Route::get('/campaign/{id}', [CampaignController::class, 'show'])->middleware('auth:api');
    Route::post('/campaign/{id}', [CampaignController::class, 'update'])->middleware('auth:api');
    Route::delete('/campaign/{id}', [CampaignController::class, 'destroy'])->middleware('auth:api');

    Route::get('/cities', [CityController::class, 'index'])->middleware('auth:api');

    Route::get('/client', [ClientController::class, 'index'])->middleware('auth:api');
    Route::post('/client', [ClientController::class, 'store'])->middleware('auth:api');
    Route::get('/client/{id}', [ClientController::class, 'show'])->middleware('auth:api');
    Route::post('/client/{id}', [ClientController::class, 'update'])->middleware('auth:api');
    Route::delete('/client/{id}', [ClientController::class, 'destroy'])->middleware('auth:api');

    Route::get('/guide', [GuideController::class, 'index'])->middleware('auth:api');
    Route::post('/guide', [GuideController::class, 'store'])->middleware('auth:api');
    Route::get('/guide/{id}', [GuideController::class, 'show'])->middleware('auth:api');
    Route::post('/guide/{id}', [GuideController::class, 'update'])->middleware('auth:api');
    Route::delete('/guide/{id}', [GuideController::class, 'destroy'])->middleware('auth:api');

    Route::get('/material', [MaterialController::class, 'index'])->middleware('auth:api');
    Route::post('/material', [MaterialController::class, 'store'])->middleware('auth:api');
    Route::get('/material/{id}', [MaterialController::class, 'show'])->middleware('auth:api');
    Route::post('/material/{id}', [MaterialController::class, 'update'])->middleware('auth:api');
    Route::delete('/material/{id}', [MaterialController::class, 'destroy'])->middleware('auth:api');

    Route::get('/media', [MediaController::class, 'index'])->middleware('auth:api');
    Route::post('/media', [MediaController::class, 'store'])->middleware('auth:api');
    Route::get('/media/{id}', [MediaController::class, 'show'])->middleware('auth:api');
    Route::post('/media/{id}', [MediaController::class, 'update'])->middleware('auth:api');
    Route::delete('/media/{id}', [MediaController::class, 'destroy'])->middleware('auth:api');

    Route::get('/mediaTypes', [MediaTypeController::class, 'index'])->middleware('auth:api');

    Route::get('/permissions', [PermissionController::class, 'permissions'])->middleware('auth:api');

    Route::get('/plan', [PlanController::class, 'index'])->middleware('auth:api');
    Route::post('/plan', [PlanController::class, 'store'])->middleware('auth:api');
    Route::get('/plan/{id}', [PlanController::class, 'show'])->middleware('auth:api');
    Route::post('/plan/{id}', [PlanController::class, 'update'])->middleware('auth:api');
    Route::delete('/plan/{id}', [PlanController::class, 'destroy'])->middleware('auth:api');

    Route::get('/rate', [RateController::class, 'index'])->middleware('auth:api');
    Route::post('/rate', [RateController::class, 'store'])->middleware('auth:api');
    Route::get('/rate/{id}', [RateController::class, 'show'])->middleware('auth:api');
    Route::post('/rate/{id}', [RateController::class, 'update'])->middleware('auth:api');
    Route::delete('/rate/{id}', [RateController::class, 'destroy'])->middleware('auth:api');

    Route::get('/resources', [ResourceController::class, 'permissions'])->middleware('auth:api');
    Route::get('/roles', [RoleController::class, 'permissions'])->middleware('auth:api');

    Route::get('/user', [UserController::class, 'index'])->middleware('auth:api');
    Route::post('/user', [UserController::class, 'store'])->middleware('auth:api');
    Route::get('/user/{id}', [UserController::class, 'show'])->middleware('auth:api');
    Route::post('/user/{id}', [UserController::class, 'update'])->middleware('auth:api');
    Route::delete('/user/{id}', [UserController::class, 'destroy'])->middleware('auth:api');
});
