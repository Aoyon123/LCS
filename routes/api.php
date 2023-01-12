<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Admin\ConsultantController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\AcademicQualificationController;
use App\Http\Controllers\Consultant\ConsultantRateController;

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


Route::group(["middleware" => ["auth:api"]], function () {
    ///////// Consultant   ////////////
    Route::get('/admin/consultants', [ConsultantController::class, 'index']);


    //Route::get('/admins', [RegisterController::class, 'index']);
    ////////////  Profile //////////////
    Route::get('/profile', [ProfileController::class, 'profile']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/password/change', [ProfileController::class, 'updatePassword']);
    Route::delete('/profile/experience/{id}/delete', [ProfileController::class, 'experienceDestroy']);
    Route::delete('/profile/academic_qualification/{id}/delete', [ProfileController::class, 'academicQualificationDestroy']);


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
   // Route::get('/consultantRate', [ConsultantRateController::class, 'index']);



});
