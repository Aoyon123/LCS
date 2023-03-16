<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ExperienceController;
use App\Http\Controllers\Citizen\CaseController;
use App\Http\Controllers\Admin\BannerController;
//use App\Http\Controllers\Admin\BannerControlle;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Citizen\CitizenController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Consultant\ServiceController;
use App\Http\Controllers\Common\ConversationController;
use App\Http\Controllers\AcademicQualificationController;
use App\Http\Controllers\Consultant\ConsultantRateController;
use App\Http\Controllers\Frontend\V1\Common\CommonController;
use App\Http\Controllers\Admin\FrequentlyAskedQuestionController;
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
Route::post('/otp/verify', [AuthController::class, 'registrationWithOTP']);
Route::post('/otp/refresh', [AuthController::class, 'refreshOTP']);
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
    Route::get("district/list", [ProfileController::class, 'allDistricts']);
    // Route::get('/profile/consultantList', [ProfileController::class, 'consultantList']);

    /////////  Conversations /////////
    Route::get('/conversation/seenMessage/{purpose_id}', [ConversationController::class, 'seenMessage']);
    Route::get('/conversation/{purpose_id}/seeMoreMessage/{offset}', [ConversationController::class, 'seeMoreMessage']);
    Route::get('/conversation/{id}/allMessage', [ConversationController::class, 'allMessage']);
    Route::post('/conversation/store', [ConversationController::class, 'store']);
    Route::post('/conversation/{id}/delete', [ConversationController::class, 'destroy']);

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
    Route::get('/experience/{id}/user', [ExperienceController::class, 'experience']);
    Route::get('/experience/{id}/retrieve', [ExperienceController::class, 'retrieve']);
    Route::put('/experience/{id}/update', [ExperienceController::class, 'update']);
    Route::post('/experience/delete', [ExperienceController::class, 'destroy']);
    Route::post('/experience/store', [ExperienceController::class, 'store']);

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
    Route::get('/case/list', [CaseController::class, 'caseList']);
    Route::get('/case/{case_id}/details', [CaseController::class, 'caseDetailsInfo']);
    Route::post('/case/update', [CaseController::class, 'update']);
    Route::post('/citizen/case/store', [CaseController::class, 'store']);
    Route::delete('/case/{id}/delete', [CaseController::class, 'destroy']);
    Route::get('citizen/case/consultants/{id}/services', [CaseController::class, 'consultantServices']);
    Route::get('/case/all', [CaseController::class, 'allCases']);
    Route::post('/case/statusUpdate', [CaseController::class, 'statusUpdate']);
    Route::get('/case/{consultant_id}/rating', [CaseController::class, 'consultantRating']);

    /////////////// Frequently Asked Question  ////////////////////
    Route::post('/frequentlyAskedQuestion/store', [FrequentlyAskedQuestionController::class, 'store']);
    Route::get('/frequentlyAskedQuestion/all', [FrequentlyAskedQuestionController::class, 'index']);
    Route::put('/frequentlyAskedQuestion/{faq_id}/update', [FrequentlyAskedQuestionController::class, 'update']);
    Route::delete('/frequentlyAskedQuestion/{faq_id}/delete', [FrequentlyAskedQuestionController::class, 'destroy']);

    ////////////////  Banner Controller  /////////////
    Route::get("banner/all", [BannerController::class, 'bannerList']);
    Route::post('/banner/store', [BannerController::class, 'store']);
    Route::post('/banner/update', [BannerController::class, 'update']);
    /////////////// Citizen Controller  //////////////
    Route::get('/citizen/conlsultants/list', [CitizenController::class, 'conlsultantList']);
});


/////////// Frontrend Part ////////////
Route::group(["prefix" => "/frontend/common/", 'namespace' => 'Frontend/V1/'], function () {

    ////////////////   ConsultantController  //////////////////
    Route::get("consultantList", [ConsultantController::class, 'consultantList']);
    Route::get("consultant/{consultant_id}/details", [ConsultantController::class, 'consultantDetails']);
    Route::get("reviewList/{consultant_id}", [ConsultantController::class, 'reviewList']);


    /////// Banner Controller //////////
    Route::get("banner/list", [BannerController::class, 'bannerList']);


    /// CommonController ////
    Route::get("dashboard", [CommonController::class, 'dashboardMobile']);
    Route::get("consultation", [CommonController::class, 'consultation']);
    Route::get("frequentlyAskedQuestion/all", [CommonController::class, 'faqAll']);
    Route::get("serviceList", [CommonController::class, 'activeServiceList']);
    Route::get("district/list", [CommonController::class, 'allDistricts']);
});


// Route::get('frontend/consultant/list', [ConsultantController::class, 'dashboard']);
// Route::get('frontend/consultantList/{topRated?}/{active?}', [ConsultantController::class, 'topRated']);
// Route::get('frontend/consultantList/active', [ConsultantController::class, 'active']);
// Route::get('frontend/serviceWiseConsultantList/{id}', [ConsultantController::class, 'serviceWiseConsultantList']);
