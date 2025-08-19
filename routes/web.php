<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\CoachAuthController;
use App\Http\Controllers\BranchAuthController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\Branch\CompetitionController as BranchCompetitionController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Branch\CoachController;
use App\Http\Controllers\Coach\ManageCompetitionController;
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
    Route::resource('coaches', App\Http\Controllers\CoachController::class);
    Route::resource('branches', App\Http\Controllers\BranchController::class);
});


Route::get('coaches-login', [CoachAuthController::class, 'showLoginForm'])->name('coaches.login');
Route::post('coaches-login', [CoachAuthController::class, 'login'])->name('coaches.login.submit');

Route::get('branches-login', [BranchAuthController::class, 'showLoginForm'])->name('branches.login');
Route::post('branches-login', [BranchAuthController::class, 'login'])->name('branches.login.submit');

Route::middleware('auth:coach')->prefix('coach')->as('coach.')->group(function () {
    Route::get('/dashboard', function () {
        return view('coach.dashboard');
    })->name('dashboard');
    Route::get('competition-details', [ManageCompetitionController::class, 'getCompetitionDetail'])->name('competition-details');
    Route::get('competition-details/{id}/users', [ManageCompetitionController::class, 'getCompetitionDetailUser'])
        ->name('competition-detail-users');
    Route::get('/competition-detail-users/{id}', [ManageCompetitionController::class, 'getCompetitionDetailUserUpdate'])
        ->name('competition-users-update');
    Route::put('/competition-result-update/{id}', [ManageCompetitionController::class, 'getCompetitionResultUpdate'])
        ->name('competition-result-update');
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware('auth:branch')->prefix('branch')->as('branch.')->group(function () {
    Route::get('/dashboard', function () {
        return view('branch.dashboard');
    })->name('branch.dashboard');
    Route::get('coaches', [CoachController::class, 'index'])->name('getCoaches');
    Route::get('competetions', [BranchCompetitionController::class, 'getCompetitions'])
        ->name('getCompetitions');
    Route::get('create-competetions', [BranchCompetitionController::class, 'createCompetitions'])
        ->name('createCompetitions');
    Route::post('store-competetions', [BranchCompetitionController::class, 'storeCompetitions'])
        ->name('storeCompetitions');
    Route::get('/get-organizations/{org_type_id}', [BranchCompetitionController::class, 'getOrganizationsByType'])
        ->name('get.organizations');
    Route::delete('/delete-competetion/{id}', [BranchCompetitionController::class, 'deleteCompetition'])
        ->name('deleteCompetition');
    Route::get('/show-competetion/{id}', [BranchCompetitionController::class, 'showCompetition'])
        ->name('showCompetition');
    Route::get('/edit-competetion/{id}', [BranchCompetitionController::class, 'editCompetition'])
        ->name('editCompetition');
    Route::put('/update-competetion/{id}', [BranchCompetitionController::class, 'updateCompetition'])
        ->name('updateCompetition');

    Route::get('competition-details', [BranchCompetitionController::class, 'getCompetitionDetail'])->name('competition-details');
    Route::get('competition-details/{id}/users', [BranchCompetitionController::class, 'getCompetitionDetailUser'])
        ->name('getCompetitionDetailUser');
    Route::get('/competition-detail-users/{id}', [BranchCompetitionController::class, 'getCompetitionDetailUserUpdate'])
        ->name('getCompetitionDetailUserUpdate');
    Route::get('/competition-detail-delete/{id}', [BranchCompetitionController::class, 'getCompetitionDetailUserUpdate'])
        ->name('getCompetitionDetailDelete');
    Route::put('/competition-result-update/{id}', [BranchCompetitionController::class, 'getCompetitionResultUpdate'])
        ->name('competition-result-update');
    Route::get('/competition-users/{id}/edit', [BranchCompetitionController::class, 'editCompetitionResultUpdate'])
        ->name('competition-users.edit');
    Route::put('/update-competition-users/{id}', [BranchCompetitionController::class, 'updateCompetitionResultUpdate'])
        ->name('competition-users.update');
    Route::get('/competition-detail-users/{id}', [BranchCompetitionController::class, 'getCompetitionDetailUserUpdate'])
        ->name('competition-users-update');

    Route::get('competitions-videos', [BranchCompetitionController::class, 'competitionVideos'])->name('competitions.videos');
    Route::get('competitions-appeals', [BranchCompetitionController::class, 'competitionAppeals'])->name('competitions.appeals');
    Route::delete('destroy-appeal/{id}', [BranchCompetitionController::class, 'destroyAppeal'])->name('competitions.destroyAppeal');
    Route::put('update-appeal-status/{id}', [BranchCompetitionController::class, 'updateAppealStatus'])->name('competitions.updateAppealStatus');
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
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
