<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('pos/sync')->withoutMiddleware('throttle:api')->middleware([\App\Http\Middleware\PosTransport::class,\App\Http\Middleware\PosDeviceAuth::class,'throttle:pos-sync'])->group(function() {
    $controller=\App\Http\Controllers\Api\PosSyncController::class;
    Route::post('/catalog',[$controller,'catalog']); Route::post('/image',[$controller,'image']);
    Route::get('/orders',[$controller,'feed']); Route::post('/commands',[$controller,'command']);
    $reports=\App\Http\Controllers\Api\PosReportsController::class;
    Route::post('/reports/begin',[$reports,'begin']); Route::post('/reports/chunk',[$reports,'chunk']); Route::post('/reports/complete',[$reports,'complete']); Route::post('/reports/heartbeat',[$reports,'heartbeat']);
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('pos')->middleware(\App\Http\Middleware\PosTransport::class)->group(function () {
    Route::post('/activate', [\App\Http\Controllers\Api\PosController::class, 'activate'])->middleware('throttle:10,1');
    Route::post('/subscription/redeem', [\App\Http\Controllers\Api\PosController::class, 'redeem'])->middleware('throttle:6,1');
    Route::get('/license', [\App\Http\Controllers\Api\PosController::class, 'license'])->middleware(\App\Http\Middleware\PosDeviceAuth::class);
});
