<?php

use App\Http\Controllers\AuthenticatedUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CodeController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\CodeHistoryController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\MaterialController;
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
    //Post Upload any file types to ImageKit
    Route::post('upload', [ImageUploadController::class, 'upload']);
    //Post Code to Glot
    Route::post('code-glot', function (Request $request) {
        $input = $request->all();
        $response = Http::withHeaders([
            'Authorization' => env('GLOT_AUTH_TOKEN'),
            'Content-Type' => 'application/json'
        ])->post(env('GLOT_JS_URL'), $input);
        return response()->json($response->json(), 200);
    });
    //Fetch Codes and Single Code Histories
    Route::get('codes', [CodeController::class, 'index']);
    Route::get('codes/single/{codes:slug}', [CodeController::class, 'show']);
    //Fetch Materials and Single Material
    Route::get('materials', [MaterialController::class, 'index']);
    Route::get('materials/latest', [MaterialController::class, 'indexWithFilter']);
    Route::get('materials/single/{materials:slug}', [MaterialController::class, 'show']);
    //Check Email Verification User
    Route::get('check-verification/{users:id}', [EmailVerificationController::class, 'checkVerification']);
    //Get Hashcode email verification
    Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    //Route Middleware Laravel Sanctum
    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::get('me', [AuthenticatedUserController::class, 'getAuthUser']);
        //Dashboard
        Route::get('fetch-dashboard', [AuthenticatedUserController::class, 'dashboard']);
        //Auth Check
        Route::get('auth-check', [AuthenticationController::class, 'checkAuth']);
        //Get All Roles
        Route::get('roles', [RoleController::class, 'index']);
        //CRUD Code Histories
        Route::group(['prefix' => 'code'], function () {
            Route::get('histories', [CodeController::class, 'getUserCodes']);
            Route::post('create', [CodeController::class, 'store']);
            Route::get('webpage-builder/{slug}', [CodeController::class, 'loadWebPageBuilder']);
            Route::post('webpage-builder/{slug}/store', [CodeController::class, 'storeWebPageBuilder']);
            Route::put('{slug}/update', [CodeController::class, 'update']);
            Route::delete('{id}/delete', [CodeController::class, 'destroy']);
        });
        //Email Verification
        Route::post('verify-email', [EmailVerificationController::class, 'sendVerificationEmail']);
        //Route Middleware when user already verified by email
        Route::group(['middleware' => ['verified']], function () {
            //Quizzes
            Route::get('quizzes', [QuizController::class, 'index']);
            Route::get('quizzes/single/{quizzes:slug}', [QuizController::class, 'show']);
            Route::group(['prefix' => 'result'], function () {
                Route::post('/{quizzes:slug}/quiz', [ResultController::class, 'quizStore']);
                Route::post('/{quizzes:slug}/essay', [ResultController::class, 'essayStore']);
                Route::get('/single/{quizId}/{userId}', [ResultController::class, 'showSingleResult']);
                Route::get('/user/submitted', [ResultController::class, 'submittedResultsByUserId']);
            });
            //Users
            Route::get('users', [UserController::class, 'index']);
            Route::get('users/{id}', [UserController::class, 'show']);
            Route::put('profile/{id}/update', [AuthenticationController::class, 'update']);
            //Role Admin or Teacher
            Route::group(['middleware' => ['role:admin|teacher']], function () {
                Route::group(['prefix' => 'users'], function () {
                    Route::post('/create', [UserController::class, 'store']);
                    Route::put('/{id}/update', [UserController::class, 'update']);
                    Route::delete('/{id}/delete', [UserController::class, 'destroy']);
                });
                //Result
                Route::group(['prefix' => 'result'], function () {
                    Route::get('/all/submitted', [ResultController::class, 'submittedResults']);
                    Route::get('/{quizzes:slug}/notsubmitted', [ResultController::class, 'resultNotSubmitted']);
                    Route::get('/{quizzes:slug}/quiz', [ResultController::class, 'quizResultSubmitted']);
                    Route::get('/{quizzes:slug}/essay', [ResultController::class, 'essayResultSubmitted']);
                    Route::put('/{results:id}/essay', [ResultController::class, 'createScoreEssay']);
                });
                Route::group(['prefix' => 'quizzes'], function () {
                    Route::post('/create', [QuizController::class, 'store']);
                    Route::put('/{quizzes:slug}/update', [QuizController::class, 'update']);
                    Route::delete('/{quizzes:id}/delete', [QuizController::class, 'destroy']);
                });
                Route::group(['prefix' => 'materials'], function () {
                    Route::post('/create', [MaterialController::class, 'store']);
                    Route::get('/screenshot', [MaterialController::class, 'storeScreenshotPage']);
                    Route::put('/{id}/update', [MaterialController::class, 'update']);
                    Route::delete('/{id}/delete', [MaterialController::class, 'destroy']);
                });
            });
            //Role Admin
            Route::group(['middleware' => ['role:admin']], function () {
                Route::group(['prefix' => 'pages'], function () {
                    Route::post('/create', [PageController::class, 'store']);
                    Route::delete('/{slug}/delete', [PageController::class, 'destroy']);
                });
                Route::group(['prefix' => 'roles'], function () {
                    Route::post('/create', [RoleController::class, 'store']);
                    Route::put('/{id}/update', [RoleController::class, 'update']);
                    Route::delete('/{id}/delete', [RoleController::class, 'destroy']);
                });
            });
        });
        //Logout
        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });
});
