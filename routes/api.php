<?php

use App\Http\Controllers\Admin\AdminCaseController;
use App\Http\Controllers\Admin\AdminInformationController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CitizenController as AdminCitizenController;
use App\Http\Controllers\Admin\ConsultantController as AdminConsultantController;
use App\Http\Controllers\Admin\FrequentlyAskedQuestionController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\QuestionBankController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Citizen\CaseController;
use App\Http\Controllers\Citizen\CitizenController;
// use App\Http\Controllers\Consultant\ConsultantController;
use App\Http\Controllers\Citizen\CitizenInformationController;
use App\Http\Controllers\Common\ConversationController;
use App\Http\Controllers\Common\ProfileController;
use App\Http\Controllers\Consultant\ConsultantInformationController;
use App\Http\Controllers\Consultant\ConsultantRateController;
use App\Http\Controllers\Consultant\ServiceController;
use App\Http\Controllers\Frontend\V1\Common\CommonController;
use App\Http\Controllers\Frontend\V1\Consultant\ConsultantController;use Illuminate\Support\Facades\Artisan;use Illuminate\Support\Facades\Route;

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
Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize');
    return '<h1>Optimized class loader</h1>';
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/loginAdmin', [AuthController::class, 'loginAdmin']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/otp/verify', [AuthController::class, 'registrationWithOTP']);
Route::post('/otp/refresh', [AuthController::class, 'refreshOTP']);
Route::post('/forget/password', [AuthController::class, 'forgetPasswordVerification']);
Route::post('/set/password', [AuthController::class, 'setPassword']);
Route::post('/set/registration/password', [AuthController::class, 'setRegistrationPassword']);
Route::post('/index', [AuthController::class, 'index']);
Route::post('/destroy', [AuthController::class, 'destroy']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::get('/me', [AuthController::class, 'me']);
// Route::get('/maintenance', 'mobileMaintenance');
Route::get('/maintenance', [AuthController::class, 'mobileMaintenance']);
Route::get('/web-maintenance', [AuthController::class, 'consultantRegistrationOff']);
Route::get('/get-ip', [AuthController::class, 'getIp']);
Route::post('/user-delete/{phoneNumber}', [AuthController::class, 'numberDelete']);

Route::group(["middleware" => ["auth:api"]], function () {

    ///////// AdminConsultantController  ////////////

    Route::get('/admin/consultants', [AdminConsultantController::class, 'index']);
    Route::get('/admin/consultants/serialList', [AdminConsultantController::class, 'approveConlsultantList']);
    Route::post("/consultant/approval", [AdminConsultantController::class, 'approvalConsultant']);
    Route::get('/admin/consultants/information', [AdminConsultantController::class, 'adminConsultantInformation']);
    Route::get('/receverable/consultants', [AdminConsultantController::class, 'transferableConsultantList']);
    Route::get('/consultant/change', [AdminConsultantController::class, 'changeConsultant']);
    ////////// Citizen List ///////////
    Route::get('/admin/citizens', [AdminCitizenController::class, 'index']);
    Route::get('/admin/citizen/cardInformation', [AdminCitizenController::class, 'adminCardCitizenInformation']);

    //Route::get('/admins', [RegisterController::class, 'index']);
    /////////////// Admin Case Controller   //////////
    Route::get('/admin/allcase', [AdminCaseController::class, 'adminCaseData']);
    Route::get('/admin/allcase/cardInformation', [AdminCaseController::class, 'adminCaseCardInformation']);
    ////////////  Profile //////////////
    Route::get('/profile/{id}', [ProfileController::class, 'profile']);
    Route::get('/profile/active/status', [ProfileController::class, 'activeUser']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/password/change', [ProfileController::class, 'updatePassword']);
    Route::post('/active_status/{consultant_id}/change', [ProfileController::class, 'activeStatusChange']);
    Route::delete('/profile/experience/{id}/delete', [ProfileController::class, 'experienceDestroy']);
    Route::delete('/profile/academic_qualification/{id}/delete', [ProfileController::class, 'academicQualificationDestroy']);
    Route::post('/consultant/serialize', [ProfileController::class, 'consultantSerialize']);

    // Route::post('/consultant/approve', [ProfileController::class, 'approved']);
    Route::get("district/list", [ProfileController::class, 'allDistricts']);
    Route::post('/imagefile/download', [ProfileController::class, 'getDownload']);
    // Route::get('/profile/consultantList', [ProfileController::class, 'consultantList']);
    Route::post('/profile/image/update', [ProfileController::class, 'profileImageUpdateMobile']);
    Route::get('address/divisions', [ProfileController::class, 'divisionList']);
    Route::get('address/districts/{division_id}', [ProfileController::class, 'divisionWiseDistrict']);
    Route::get('address/upazilas/{district_id}', [ProfileController::class, 'districtWiseUpazila']);
    Route::get('address/unions/{upazila_id}', [ProfileController::class, 'upazilaWiseUnion']);

    /////////  Conversations /////////
    Route::get('/conversation/seenMessage/{purpose_id}', [ConversationController::class, 'seenMessage']);
    Route::get('/conversation/{purpose_id}/seeMoreMessage/{offset}', [ConversationController::class, 'seeMoreMessage']);
    Route::get('/conversation/{id}/allMessage', [ConversationController::class, 'allMessage']);
    Route::get('/conversation/{purpose_id}/allMessage/mobile', [ConversationController::class, 'allMessageMobile']);
    Route::post('/conversation/store', [ConversationController::class, 'store']);
    // Route::post('/conversation/{id}/delete', [ConversationController::class, 'destroy']);
    Route::get('/chat_board/messages', [ConversationController::class, 'chatBoardMessage']);
    Route::get('/chat_board/messages/count', [ConversationController::class, 'chatBoardMessageCount']);
    Route::get('/consultation/new_message_count/{case_id}', [ConversationController::class, 'caseDetailsNewMessageCount']);
    Route::post('/conversations/{id}/delete', [ConversationController::class, 'destroy']);
    Route::post('/conversations/{id}/update', [ConversationController::class, 'conversation_update']);
    /////////////  Service //////////////
    Route::post('/services/store', [ServiceController::class, 'store']);
    Route::get('/services/all', [ServiceController::class, 'allServices']);
    Route::get('/services/index', [ServiceController::class, 'index']);
    Route::get('/services/{id}/retrieve', [ServiceController::class, 'retrieve']);
    Route::post('/services/{id}/update', [ServiceController::class, 'serviceUpdate']);
    Route::post('/services/{id}/delete', [ServiceController::class, 'destroy']);

    ////////  Education Qualification   /////////////
    // Route::post('/academic_qualification', [AcademicQualificationController::class, 'store']);
    // Route::get('/academic_qualification', [AcademicQualificationController::class, 'index']);
    // Route::get('/academic_qualification/{id}/retrieve', [AcademicQualificationController::class, 'retrieve']);
    // Route::put('/academic_qualification/{id}/update', [AcademicQualificationController::class, 'update']);
    // Route::post('/academic_qualification/delete', [AcademicQualificationController::class, 'destroy']);

    /////////  Experince   ///////////
    // Route::get('/experience', [ExperienceController::class, 'index']);
    // Route::get('/experience/{id}/user', [ExperienceController::class, 'experience']);
    // Route::get('/experience/{id}/retrieve', [ExperienceController::class, 'retrieve']);
    // Route::put('/experience/{id}/update', [ExperienceController::class, 'update']);
    // Route::post('/experience/delete', [ExperienceController::class, 'destroy']);
    // Route::post('/experience/store', [ExperienceController::class, 'store']);

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

    // Route::get('/admin/allcase', [CaseController::class, 'index']);
    Route::get('/case/list', [CaseController::class, 'caseList']);
    Route::get('/case/{case_id}/details', [CaseController::class, 'caseDetailsInfo']);
    Route::get('/admin/case/{case_id}/details', [CaseController::class, 'adminCaseDetailsInfo']);
    Route::post('/case/update', [CaseController::class, 'update']);
    Route::post('/case/push-notification', [CaseController::class, 'pushNotification']);
    Route::post('/citizen/case/store', [CaseController::class, 'store']);
    Route::delete('/case/{id}/delete', [CaseController::class, 'destroy']);
    Route::get('citizen/case/consultants/{id}/services', [CaseController::class, 'consultantServices']);
    Route::get('/case/all', [CaseController::class, 'allCases']);
    Route::post('/case/statusUpdate', [CaseController::class, 'statusUpdate']);
    Route::get('/case/{consultant_id}/rating', [CaseController::class, 'consultantRating']);
    Route::get('/admin/case/{type}/{user_id}/list', [CaseController::class, 'adminCaseList']);
    Route::get('/transferable/consultations', [CaseController::class, 'initialCaseList']);
    Route::post('/transferable-consultations/update', [CaseController::class, 'transferableConsultationUpdate']);
    Route::get('/case/list/mobile', [CaseController::class, 'caseListMobile']);
    Route::get('/serviceWise/{service_id}/consultants', [CaseController::class, 'serviceWiseConsultant']);

    /////////////// Frequently Asked Question  ////////////////////
    Route::post('/frequentlyAskedQuestion/store', [FrequentlyAskedQuestionController::class, 'store']);
    Route::get('/frequentlyAskedQuestion/all', [FrequentlyAskedQuestionController::class, 'index']);
    Route::get('/frequentlyAskedQuestion/{faq_id}/retrieve', [FrequentlyAskedQuestionController::class, 'retrieve']);
    Route::put('/frequentlyAskedQuestion/{faq_id}/update', [FrequentlyAskedQuestionController::class, 'update']);
    Route::delete('/frequentlyAskedQuestion/{faq_id}/delete', [FrequentlyAskedQuestionController::class, 'destroy']);
    Route::get('/frequentlyAskedQuestion/admin/all', [FrequentlyAskedQuestionController::class, 'adminFaqAll']);
    // Route::get('/admin/frequently-asked-queation/reports', [FrequentlyAskedQuestionController::class, 'adminFaqReport']);
    ////////////////  Banner Controller  /////////////
    Route::get("banner/all", [BannerController::class, 'bannerList']);
    Route::post('/banner/store', [BannerController::class, 'store']);
    Route::post('/banner/update', [BannerController::class, 'update']);

    /////////////// Citizen Controller  //////////////
    Route::get('/citizen/conlsultants/list', [CitizenController::class, 'conlsultantList']);
    Route::get('/citizen/case/list', [CitizenController::class, 'citizenCaseList']);
    Route::get('/citizen/allcase/cardInformation', [CitizenController::class, 'citizenCaseCardInformation']);

    ////////////// Consultant Controller ////////////
    Route::get('/consultant/case/list', [ConsultantInformationController::class, 'consultantCaseList']);
    Route::get('/consultant/allcase/cardInformation', [ConsultantInformationController::class, 'consultantCaseCardInformation']);
    ///////////// ConsultantInformationController   ////////////

    Route::get('consultant/dashboard', [ConsultantInformationController::class, 'consultantDashboardInformation']);
    Route::get('consultants/mobile/dashboard/', [ConsultantInformationController::class, 'consultantMobileDashboardInformation']);
    // Route::get('consultant/performance/graph', [ConsultantInformationController::class, 'consultantPerformance']);
    Route::get('consultant/consultation', [ConsultantInformationController::class, 'consultantConsultationInformation']);

    /////////////////// CitizenInformationController   //////////////////////////
    Route::get('citizen/dashboard', [CitizenInformationController::class, 'citizenDashboardInformation']);
    Route::get('citizen/consultation', [CitizenInformationController::class, 'citizenConsultationInformation']);

    /////////////////     AdminInformationController   ////////////////////////
    Route::get('admin/dashboard', [AdminInformationController::class, 'adminDashboardInformation']);
    Route::get('admin/all/consultations', [AdminInformationController::class, 'adminAllConsultationInformation']);

    //////////////// CommonController //////////////////

    Route::get("/frontend/common/dashboard", [CommonController::class, 'dashboardMobile']);
    ///////////////////    QuestionBank Controller     ////////////////

    Route::controller(QuestionBankController::class)->group(function () {
        Route::post('/question-bank/store', 'questionBankStore');
        Route::post('/question-bank/{id}/update', 'questionBankUpdate');
        Route::get('/question-bank/{id}/retrieve', 'questionBank');
        Route::get('/question-bank/all', 'questionBankAll');
        Route::get('/question-bank/all/mobile', 'questionBankAllMobile');
        Route::get('/question-bank/card-information', 'questionBankCardInformation');
        Route::get('/admin/question-bank/reports', 'adminFaqReport');
        Route::get('/remote-consultant/list', 'remoteConsultantList');
    });
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
    Route::get("homePage/counting", [CommonController::class, 'portalHomePageCount']);
    Route::get("consultation", [CommonController::class, 'consultation']);
    Route::get("consultationadmin", [CommonController::class, 'consultationAdmin']);
    Route::get("consultationSelf", [CommonController::class, 'consultationSelf']);
    Route::get("frequentlyAskedQuestion/all", [CommonController::class, 'faqAll']);
    Route::get("serviceList", [CommonController::class, 'activeServiceList']);
    Route::get("district/list", [CommonController::class, 'allDistricts']);
    Route::post("store/general/question", [CommonController::class, 'storeGeneralAskingQuestion']);

    Route::get("rating", [CaseController::class, 'citizenRating']);

    Route::get("reportFile/consultation/running", [CommonController::class, 'reportFileConsultationRunning']);
    Route::get("reportFile/consultation/waiting", [CommonController::class, 'reportFileConsultationWaiting']);
    Route::get("reportFile/consultation/complete", [CommonController::class, 'reportFileConsultationComplete']);
    Route::get("reportFile/consultation/cancel", [CommonController::class, 'reportFileConsultationCancel']);
});

// Route::get('frontend/consultant/list', [ConsultantController::class, 'dashboard']);
// Route::get('frontend/consultantList/{topRated?}/{active?}', [ConsultantController::class, 'topRated']);
// Route::get('frontend/consultantList/active', [ConsultantController::class, 'active']);
// Route::get('frontend/serviceWiseConsultantList/{id}', [ConsultantController::class, 'serviceWiseConsultantList']);
