<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\MaterialController;

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

//register new user
Route::post('/register', [AuthenticationController::class, 'register']);
//login user
Route::post('/login', [AuthenticationController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/profile', function (Request $request) {
        return auth()->user();
    });

    Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
        Route::post('materials', [MaterialController::class, 'store']);
        Route::put('materials/{id}', [MaterialController::class, 'update']);
        Route::delete('materials/{id}', [MaterialController::class, 'destroy']);
    });

    Route::group(['prefix' => 'teacher', 'middleware' => ['teacher']], function () {
        Route::post('materials', [MaterialController::class, 'store']);
        Route::put('materials/{id}', [MaterialController::class, 'update']);
        Route::delete('materials/{id}', [MaterialController::class, 'destroy']);
    });

    Route::get('materials', [MaterialController::class, 'index']);

    Route::post('/logout', [AuthenticationController::class, 'logout']);
});
