<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PlanQuestionController;
use App\Http\Controllers\CompetitionUserController;
use App\Http\Controllers\RulesOfCountingController;
use App\Http\Controllers\ExerciseCategoryController;
use App\Http\Controllers\competitionDetailController;
use App\Http\Controllers\OrganisationTypesController;
use App\Http\Controllers\MedicalAssessmentQuestionController;

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect(route('dashboard'));
    });
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('users', UsersController::class);
    Route::resource('organisation-types', OrganisationTypesController::class);
    Route::resource('organisations', OrganisationController::class);
    Route::resource('medical-assessment-questions', MedicalAssessmentQuestionController::class);
    Route::resource('exercise-categories', ExerciseCategoryController::class);
    Route::resource('exercises', ExerciseController::class);
    Route::resource('plan-questions', PlanQuestionController::class);
    Route::resource('competitions', CompetitionController::class);
    Route::get('competition-details/{id}/users', [CompetitionUserController::class, 'index'])->name('competition-users.index');
    Route::get('/competition-users/{id}/edit', [CompetitionUserController::class, 'edit'])->name('competition-users.edit');
    Route::get('/competition-user-totals/{id}/edit', [CompetitionUserController::class, 'editRank'])->name('competition-user-totals.edit');
    Route::put('/competition-user-totals/{id}', [CompetitionUserController::class, 'updateRank'])->name('competition-user-totals.update');
    Route::post('/competitions/{id}/generate-results', [CompetitionUserController::class, 'generateResults'])->name('competitions.generate-results');
    Route::put('/competition-users/{id}', [CompetitionUserController::class, 'update'])->name('competition-users.update');
    Route::resource('competition-details', competitionDetailController::class);
    Route::post('/competitions/{id}/results', [CompetitionController::class, 'storeResults'])->name('competitions.results.store');
    Route::get('/get-organizations/{org_type_id}', [CompetitionController::class, 'getOrganizationsByType'])->name('get.organizations');
    Route::get('competitions-videos', [CompetitionController::class, 'competitionVideos'])->name('competitions.videos');
    Route::get('competitions-appeals', [CompetitionController::class, 'competitionAppeals'])->name('competitions.appeals');
    Route::delete('destroy-appeal/{id}', [CompetitionController::class, 'destroyAppeal'])->name('competitions.destroyAppeal');
    Route::put('update-appeal-status/{id}', [CompetitionController::class, 'updateAppealStatus'])->name('competitions.updateAppealStatus');
    Route::resource('results', ResultController::class);
    Route::resource('plans', PlansController::class);
    Route::resource('rulesof-counting', RulesOfCountingController::class);
});

Route::get('clear-cache', function () {
    Artisan::call('optimize:clear');
    return "Cache is cleared";
});

Route::get('/force-fix-link', function () {
    $publicStorage = public_path('storage');

    if (file_exists($publicStorage) && !is_link($publicStorage)) {
        File::deleteDirectory($publicStorage);
    }

    if (is_link($publicStorage)) {
        unlink($publicStorage);
    }

    Artisan::call('storage:link');

    return '✅ Symlink force-fixed';
});

require __DIR__ . '/auth.php';
