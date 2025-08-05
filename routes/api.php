<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoalController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompetitionController;
use App\Http\Controllers\API\PlanController;
use App\Http\Controllers\API\ProfileController;
use App\Http\Controllers\API\ExercisesController;
use App\Http\Controllers\API\PlanAnswerController;
use App\Http\Controllers\API\PersonalInfoController;
use App\Http\Controllers\API\PlanQuestionController;
use App\Http\Controllers\API\ForgetPasswordController;
use App\Http\Controllers\API\UserAssessmentExerciseController;
use App\Http\Controllers\API\MedicalAssessmentAnswerController;
use App\Http\Controllers\API\MedicalAssessmentQuestionController;

// ==========================================================================public routes=================================================================
Route::prefix('v1')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgot-password']);
    Route::post('reset-password', [AuthController::class, 'reset-password']);
    Route::get('organisation-types', [PersonalInfoController::class, 'getOrganisationTypes']);
    Route::get('organisations/{id}', [PersonalInfoController::class, 'getOrganisations']);
    Route::get('countries', [PersonalInfoController::class, 'get_countries']);
    Route::get('provinces/{id}', [PersonalInfoController::class, 'get_provinces']);
    Route::get('cities/{id}', [PersonalInfoController::class, 'get_cities']);

    // forget password
    Route::post('send-otp', [ForgetPasswordController::class, 'sendotp']);
    Route::post('check-otp', [ForgetPasswordController::class, 'checkOtp']);
    Route::post('forget-update-password', [ForgetPasswordController::class, 'forgetUpdatePassword']);
});

// ==========================================================================protected routes==============================================================

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/user-personal-info', [PersonalInfoController::class, 'profile']);
    Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('/check-username', [ProfileController::class, 'checkUserName']);
    Route::post('update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/physical-assessment', [PersonalInfoController::class, 'physical_assessment']);
    Route::post('/medical-assessment-answers', MedicalAssessmentAnswerController::class);
    Route::get('/medical-assessment-questions', MedicalAssessmentQuestionController::class);
    Route::get('/exercises', ExercisesController::class);
    Route::get('/get-category', [ExercisesController::class, 'getCategory']);
    Route::get('/get-category-exercises/{id}', [ExercisesController::class, 'getCategoryExercises']);
    Route::post('/user-assessment-exercises', UserAssessmentExerciseController::class);
    Route::post('/store-goal', [GoalController::class, 'store']);
    Route::get('/plan-questions', PlanQuestionController::class);
    Route::post('/plan-answers', PlanAnswerController::class);
    Route::get('/plans', PlanController::class);
    Route::get('/get-competitions', [CompetitionController::class, 'getCompetition']);
    Route::get('/competition-detail/{id}', [CompetitionController::class, 'competitionDetail']);
    Route::post('/accept-or-reject/{id}', [CompetitionController::class, 'acceptOrReject']);
    Route::get('/get-results', [CompetitionController::class, 'getResult']);
    Route::get('/get-appeal/{id}', [CompetitionController::class, 'getAppeal']);
    Route::post('/store-appeal', [CompetitionController::class, 'writeAppeal']);
    Route::get('/view-result/{id}', [CompetitionController::class, 'viewResult']);
    Route::get('/rules-of-count', [CompetitionController::class, 'RulesOfCount']);
    Route::get('/rules-of-count-detail/{id}', [CompetitionController::class, 'RulesOfCountDetail']);
});
