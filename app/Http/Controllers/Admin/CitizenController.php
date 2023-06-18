<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        DB::beginTransaction();

        try {
            $citizen = User::where('type', 'citizen')->get();
            if ($citizen != null) {
                $message = "Citizen Data Succesfully Shown";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $citizen);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function adminCitizenInformation(Request $request,$type)
    {

        $data = [];
        $params = $request->all();
        
        $citizenData = User::where('type', 'citizen')->orderBy('id', 'DESC');

        $totalCitizenCount = User::where('type', 'citizen')->count();

        $newRegisterCitizen = User::where(['type' => 'citizen'])
            ->whereDate('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->count();



        // $highestTakingService = LcsCase::with(['citizen'])->get();
        // return  $highestTakingService;



        // $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        // $caseData = DB::table('lcs_cases')
        //     ->where('lcs_cases.' . $type . '_id', $user_id)
        //     ->where('deleted_at', null)
        //     ->select(
        //         'lcs_cases.id',
        //         'lcs_cases.title',
        //         'lcs_cases.document_file',
        //         'lcs_cases.document_link',
        //         'lcs_cases.case_initial_date',
        //         'lcs_cases.case_status_date',
        //         'lcs_cases.description',
        //         'lcs_cases.case_code',
        //         'lcs_cases.rating',
        //         'lcs_cases.status',
        //         'lcs_cases.created_at',
        //         'lcs_cases.updated_at',
        //         'users.name',
        //         'users.code',
        //         'users.profile_image',
        //         'services.title as service_title'
        //     )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
        //     ->join('services', 'lcs_cases.service_id', '=', 'services.id')
        //     ->orderBy('id', 'DESC')->get();



        // foreach ($params as $key => $param) {

        //     if ($key === 'active') {
        //         //active=1
        //         $totalConsultantationDataCount = $consultantData
        //             ->where('users.status', $param)
        //             ->where('users.approval', $param)->count();
        //         $consultantData = $consultantData
        //             ->where('users.status', $param)
        //             ->where('users.approval', $param);
        //     } else if ($key === 'waitingConsultant') {
        //         //waitingConsultant=0
        //         $totalConsultantationDataCount = $consultantData
        //             ->where('users.approval', $param)->count();
        //         $consultantData = $consultantData
        //             ->where('users.approval', $param);
        //     } else if ($key === 'rejectedConsultant') {
        //         //rejectedConsultant=2
        //         $totalConsultantationDataCount = $consultantData
        //             ->where('users.approval', $param)->count();
        //         $consultantData = $consultantData
        //             ->where('users.approval', $param);
        //     } else if ($key === 'highestRatingConsultant') {
        //         //highestRatingConsultant=4.0
        //         $totalConsultantationDataCount = $consultantData
        //             ->where('users.approval', '1')
        //             ->where('users.status', '1')
        //             ->where('users.rates', '>=', $param)
        //             ->count();
        //         $consultantData = $consultantData
        //             ->where('users.approval', '1')
        //             ->where('users.status', '1')
        //             ->where('users.rates', '>=', $param);

        //     } else if ($key === 'deactivatedConsultant') {
        //         //deactivatedConsultant=3
        //         $totalConsultantationDataCount = $consultantData
        //             ->where('users.approval', '>=', $param)
        //             ->count();

        //         $consultantData = $consultantData
        //             ->where('users.approval', '>=', $param);

        //     } else if ($key === 'highestConsultationConsultant') {
        //         //highestConsultationConsultant=100
        //         $totalConsultantationDataCount = $consultantData
        //             ->where('users.approval', '1')
        //             ->where('users.status', '1')
        //             ->where('totalRating', '>=', $param)
        //             ->count();

        //         $consultantData = $consultantData
        //             ->where('users.approval', '1')
        //             ->where('users.status', '1')
        //             ->where('totalRating', '>=', $param);

        //     }

        // }

        // $totalConsultant['totalConsultantCount'] = $totalConsultantationDataCount;
        // $totalActiveConsulatnt['totalActiveConsulatntCount'] = $totalActiveConsulatntCount;
        // $totalWaitingConsultant['totalWaitingConsultantCount'] = $totalWaitingConsultantCount;
        // $totalRejectedConsultant['totalRejectedConsultantCount'] = $totalRejectedConsultantCount;

        // $highestRatingConsultant['highestRatingConsultantCount'] = $highestRatingConsultantCount;
        // $totalDeactivatedConsultant['totalDeactivatedConsultantCount'] = $totalDeactivatedConsultantCount;
        // $highestPaidConsultant['highestPaidConsultantCount'] = $highestPaidConsultantCount;
        // $highestConsultationConsultant['highestConsultationConsultantCount'] = $highestConsultationConsultantCount;
        // $Cards = [$totalConsultant, $totalActiveConsulatnt, $totalWaitingConsultant, $totalRejectedConsultant,
        //     $highestRatingConsultant, $totalDeactivatedConsultant, $highestPaidConsultant, $highestConsultationConsultant];

        // $ItemAll = [];

        // if (isset($params['limit'])) {
        //     if (isset($params['offset'])) {
        //         $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
        //         $ItemAll['offset'] = $params['offset'];
        //         $ItemAll['limit'] = $params['limit'];
        //         $ItemAll['consultation'] = $consultantData->offset($params['offset'])->limit($params['limit'])->get();
        //     } else {
        //         $ItemAll['limit'] = $params['limit'];
        //         $ItemAll['consultation'] = $consultantData->limit($params['limit'])->get();
        //     }
        // } else {
        //     $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
        //     $ItemAll['consultation'] = $consultantData->get();
        // }
        // $data['cardInformation'] = $Cards;
        // $data['filterInformation'] = [$ItemAll];

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
}
