<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\QuizController;
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

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::get('materials', [MaterialController::class, 'index']);

        Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {
            Route::post('quizzes', [QuizController::class, 'store']);
            Route::put('quizzes/{quizzes:slug}', [QuizController::class, 'update']);
            Route::delete('quizzes/{quizzes:slug}', [QuizController::class, 'destroy']);

            Route::post('materials', [MaterialController::class, 'store']);
            Route::put('materials/{id}', [MaterialController::class, 'update']);
            Route::delete('materials/{id}', [MaterialController::class, 'destroy']);
        });

        Route::group(['prefix' => 'teacher', 'middleware' => ['teacher']], function () {
            // Route::post('result/{slug}/quiz', [ResultController::class, 'quizStore']);
            Route::post('quizzes', [QuizController::class, 'store']);
            Route::put('quizzes/{quizzes:slug}', [QuizController::class, 'update']);
            Route::delete('quizzes/{quizzes:slug}', [QuizController::class, 'destroy']);
            // Route::delete('quizzes/questions/{id}/file', [QuizController::class, 'deleteQuestionFile']);
            // Route::delete('quizzes/options/{id}', [QuizController::class, 'deleteOption']);

            // Route::get('result/{slug}/notsubmitted', [ResultController::class, 'resultNotSubmitted']);
            // Route::get('result/{slug}/quiz', [ResultController::class, 'quizResultSubmitted']);
            // Route::get('result/{slug}/essay', [ResultController::class, 'essayResultSubmitted']);
            // Route::put('result/{id}', [ResultController::class, 'createScoreEssay']);

            Route::post('materials', [MaterialController::class, 'store']);
            Route::put('materials/{id}', [MaterialController::class, 'update']);
            Route::delete('materials/{id}', [MaterialController::class, 'destroy']);
        });
        Route::get('quizzes', [QuizController::class, 'index']);

        // Route::get('materials', [MaterialController::class, 'index']);

        Route::post('/logout', [AuthenticationController::class, 'logout']);
    });
});
