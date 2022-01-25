<?php

use App\Http\Controllers\AuthenticatedUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\CodeHistoryController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

// use App\Http\Controllers\ResultController;

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

Route::middleware(['api' => 'return-json'])->group(function () {
    //register new user
    Route::post('/register', [AuthenticationController::class, 'register']);
    //login user
    Route::post('/login', [AuthenticationController::class, 'login']);
    Route::group(['prefix' => 'pages'], function () {
        Route::get('/', [PageController::class, 'index']);
        Route::get('/{slug}', [PageController::class, 'show']);
        Route::get('/{slug}/content', [PageController::class, 'loadContent']);
        Route::post('/{slug}/content', [PageController::class, 'changeContent']);
    });

    Route::post('upload', [ImageUploadController::class, 'upload']);


    Route::post('code-glot', function (Request $request) {
        $input = $request->all();
        $response = Http::withHeaders([
            'Authorization' => env('GLOT_AUTH_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post(env('GLOT_JS_URL'), $input);

        return response()->json($response->json(), 200);
    });

    Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');


    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::get('auth-check', [AuthenticationController::class, 'checkAuth']);
        Route::get('roles', [RoleController::class, 'index']);
        Route::group(['prefix' => 'code'], function () {
            Route::get('histories', [CodeController::class, 'getUserCodes']);
            Route::post('create', [CodeController::class, 'store']);
        });


        // Route::get('code/histories', [CodeHistoryController::class, 'getCodeHistories']);
        Route::post('code/histories/create', [CodeHistoryController::class, 'store']);

        //Email Verification
        Route::post('verify-email', [EmailVerificationController::class, 'sendVerificationEmail']);

        Route::group(['middleware' => ['verified']], function () {

            //Dashboard
            Route::get('fetch-dashboard', [AuthenticatedUserController::class, 'dashboard']);

            Route::get('materials', [MaterialController::class, 'index']);
            Route::get('quizzes', [QuizController::class, 'index']);
            // Route::get('roles', [RoleController::class, 'index']);
            Route::get('quizzes/{quizzes:slug}', [QuizController::class, 'show']);
            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{id}', [UserController::class, 'show']);
            Route::put('profile/{id}/update', [AuthenticationController::class, 'update']);

            Route::group(['middleware' => ['role:admin']], function () {
                Route::group(['prefix' => 'pages'], function () {
                    Route::post('/create', [PageController::class, 'store']);
                    Route::delete('/{slug}/delete', [PageController::class, 'destroy']);
                });
                Route::group(['prefix' => 'quizzes'], function () {
                    Route::post('/create', [QuizController::class, 'store']);
                    Route::put('/{quizzes:slug}/update', [QuizController::class, 'update']);
                    Route::delete('/{quizzes:slug}/delete', [QuizController::class, 'destroy']);
                });
                Route::group(['prefix' => 'users'], function () {
                    Route::post('/create', [UserController::class, 'store']);
                    Route::put('/{id}/update', [UserController::class, 'update']);
                    Route::delete('/{id}/delete', [UserController::class, 'destroy']);
                });
                Route::group(['prefix' => 'materials'], function () {
                    Route::post('/create', [MaterialController::class, 'store']);
                    Route::put('/{id}/update', [MaterialController::class, 'update']);
                    Route::delete('/{id}/delete', [MaterialController::class, 'destroy']);
                });
                Route::group(['prefix' => 'roles'], function () {
                    Route::post('/create', [RoleController::class, 'store']);
                    Route::put('/{id}/update', [RoleController::class, 'update']);
                    Route::delete('/{id}/delete', [RoleController::class, 'destroy']);
                });
            });
            // Route::group(['middleware' => ['role:admin', 'role:teacher']], function () {
            //     Route::group(['prefix' => 'quizzes'], function () {
            //         Route::post('/create', [QuizController::class, 'store']);
            //         Route::put('/{quizzes:slug}/update', [QuizController::class, 'update']);
            //         Route::delete('/{quizzes:slug}/delete', [QuizController::class, 'destroy']);
            //     });
            //     Route::group(['prefix' => 'materials'], function () {
            //         Route::post('/create', [MaterialController::class, 'store']);
            //         Route::put('/{id}/update', [MaterialController::class, 'update']);
            //         Route::delete('/{id}/delete', [MaterialController::class, 'destroy']);
            //     });
            // });
            Route::get('quizzes', [QuizController::class, 'index']);

            // Route::get('materials', [MaterialController::class, 'index']);
        });

        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });
});
