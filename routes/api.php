<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\Citizen\CaseController;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Consultant\ServiceController;
use App\Http\Controllers\AcademicQualificationController;
use App\Http\Controllers\Consultant\ConsultantRateController;
use App\Http\Controllers\Frontend\V1\Common\CommonController;
use App\Http\Controllers\Frontend\V1\Consultant\ConsultantController;
use App\Http\Controllers\Admin\CitizenController as AdminCitizenController;
use App\Http\Controllers\Admin\ConsultantController as AdminConsultantController;

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

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/index', [AuthController::class, 'index']);
Route::post('/destroy', [AuthController::class, 'destroy']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/me', [AuthController::class, 'me']);

Route::group(["middleware" => ["auth:api"]], function () {


    ///////// Consultant List  ////////////
    Route::get('/admin/consultants', [AdminConsultantController::class, 'index']);

    ////////// Citizen List ///////////
    Route::get('/admin/citizens', [AdminCitizenController::class, 'index']);
    //Route::get('/admins', [RegisterController::class, 'index']);

    ////////////  Profile //////////////
    Route::get('/profile/{id}', [ProfileController::class, 'profile']);
    Route::get('/profile/active/status', [ProfileController::class, 'activeUser']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/password/change', [ProfileController::class, 'updatePassword']);
    Route::delete('/profile/experience/{id}/delete', [ProfileController::class, 'experienceDestroy']);
    Route::delete('/profile/academic_qualification/{id}/delete', [ProfileController::class, 'academicQualificationDestroy']);
    Route::post('/consultant/approve', [ProfileController::class, 'approved']);
    Route::get('/profile/consultantList', [ProfileController::class, 'consultantList']);


    /////////////  Service //////////////
    Route::post('/services/store', [ServiceController::class, 'store']);
    Route::get('/services/all', [ServiceController::class, 'allServices']);
    Route::get('/services/index', [ServiceController::class, 'index']);
    Route::get('/services/{id}/retrieve', [ServiceController::class, 'retrieve']);
    Route::put('/services/{id}/update', [ServiceController::class, 'update']);
    Route::post('/services/{id}/delete', [ServiceController::class, 'destroy']);

    ////////  Education Qualification   /////////////
    Route::post('/academic_qualification', [AcademicQualificationController::class, 'store']);
    Route::get('/academic_qualification', [AcademicQualificationController::class, 'index']);
    Route::get('/academic_qualification/{id}/retrieve', [AcademicQualificationController::class, 'retrieve']);
    Route::put('/academic_qualification/{id}/update', [AcademicQualificationController::class, 'update']);
    Route::post('/academic_qualification/delete', [AcademicQualificationController::class, 'destroy']);


    /////////  Experince   ///////////
    Route::get('/experience', [ExperienceController::class, 'index']);
    Route::post('/experience', [ExperienceController::class, 'store']);
    Route::get('/experience/{id}/retrieve', [ExperienceController::class, 'retrieve']);
    Route::put('/experience/{id}/update', [ExperienceController::class, 'update']);
    Route::post('/experience/delete', [ExperienceController::class, 'destroy']);


    ////////// Role ///////////
    Route::get('/role', [RoleController::class, 'index']);
    Route::get('/role/{id}/retrieve', [RoleController::class, 'retrieve']);
    Route::post('/role/store', [RoleController::class, 'store']);
    //Route::get('/user/{userId}/setRole/{roleId}', [RoleController::class, 'setRole']);
    Route::put('/role/{id}/update', [RoleController::class, 'update']);
    Route::post('/role/delete', [RoleController::class, 'destroy']);


    ////////////    Permission   ///////////
    Route::get('/permission', [PermissionController::class, 'index']);
    Route::put('/permission/{id}/update', [PermissionController::class, 'update']);
    Route::get('/permission/{id}/retrieve', [PermissionController::class, 'retrieve']);
    Route::post('/permission/delete', [PermissionController::class, 'destroy']);

    /////////// Consultant Rate ///////////////
    Route::get('/consultantRate', [ConsultantRateController::class, 'index']);
    Route::post('/consultantRate/store', [ConsultantRateController::class, 'store']);
    Route::get('/consultantRate/{id}/rateCalculate', [ConsultantRateController::class, 'rateCalculate']);


    ///////////  Case ////////////
    Route::get('/allcase', [CaseController::class, 'index']);
    Route::post('/citizen/case', [CaseController::class, 'store']);
    Route::delete('/citizen/case/{id}/delete', [CaseController::class, 'destroy']);
    Route::get('citizen/case/consultants/{id}/services', [CaseController::class, 'consultantServices']);
    Route::get('/case/all', [ServiceController::class, 'allCases']);

});


/////////// Frontrend Part ////////////
Route::group(["prefix" => "/frontend/common/", 'namespace' => 'Frontend/V1/'], function () {
    Route::get("consultantList", [ConsultantController::class, 'consultantList']);
    //  Route::get("consultantList", [ConsultantController::class, 'consultantList2']);
    Route::get("consultant/{id}/details", [ConsultantController::class, 'details']);
    Route::get("dashboard", [CommonController::class, 'dashboard']);
});


// Route::get('frontend/consultant/list', [ConsultantController::class, 'dashboard']);
// Route::get('frontend/consultantList/{topRated?}/{active?}', [ConsultantController::class, 'topRated']);
// Route::get('frontend/consultantList/active', [ConsultantController::class, 'active']);
// Route::get('frontend/serviceWiseConsultantList/{id}', [ConsultantController::class, 'serviceWiseConsultantList']);
