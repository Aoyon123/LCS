<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;

class CitizenController extends Controller
{
    use ResponseTrait;
    // public function index()
    // {
    //     $citizen = User::where('type', 'citizen')
    //         ->where('is_phone_verified', 1)
    //         ->get();

    //     if ($citizen->isEmpty()) {
    //         $message = "No Data Found";
    //         return $this->responseError(404, false, $message);
    //     }

    //     $message = "Citizen Data Succesfully Shown";
    //     return $this->responseSuccess(200, true, $message, $citizen);

    // }

    public function index(Request $request)
    {

        $citizenData = User::Citizen()->where('is_phone_verified', 1)
            ->select('id', 'name', 'phone', 'email', 'address', 'is_phone_verified', 'code', 'profile_image')
            ->latest();

        if ($request->has('all')) {
            $citizenData = $citizenData;
        }

        if ($request->has('newRegistration')) {
            $citizenData = $citizenData
                ->whereDate('users.created_at', '>=', now()->subDays(7));
        }

        if ($request->has('citizenGetServices')) {
            $caseStatus = 2;
            $citizenData = $citizenData->whereHas('citizenCases', function ($query) use ($caseStatus) {
                return $query->where('lcs_cases.status', $caseStatus);
            });
        }

        // if ($request->has('highestCitizenConsultation')) {
        //     $citizenData = User::Citizen()->where('is_phone_verified', 1)
        //     ->select('id', 'name', 'phone', 'email', 'address', 'is_phone_verified', 'code', 'profile_image')
        //         ->withCount(['citizenCompleteCases' => function ($query) {
        //             $query->where('status', 2);
        //         }])
        //         ->orderByDesc('citizen_complete_cases_count');
        // }

        if ($request->has('highestCitizenConsultation')) {
            $citizenData = User::Citizen()->where('is_phone_verified', 1)
            ->select('id', 'name', 'phone', 'email', 'address', 'is_phone_verified', 'code', 'profile_image')
                ->withCount('citizenCompleteCases')
                ->orderByDesc('citizen_complete_cases_count');
        }

        $limit = $request->limit;
        $data = $citizenData->paginate($limit ?? 20);

        $message = "Citizen Data Succesfully Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function adminCardCitizenInformation(Request $request)
    {
        // $data = [];
        // $ItemAll = [];
        // $citizenData = User::Citizen()->where('is_phone_verified', 1);
        // $allCitizenCount = $citizenData->count();

        // $citizenNewRegistrationDataCount = $citizenData
        //     ->whereDate('created_at', '>=', now()->subDays(7))->count();

        // $caseStatus = 2;
        // $citizenGetServicesDataCount = User::Citizen()->where('is_phone_verified', 1)->whereHas('citizenCases', function ($query) use ($caseStatus) {
        //     return $query->where('lcs_cases.status', $caseStatus);
        // })->count();

        // $allCitizenCountArray['allCitizenCount'] = $allCitizenCount;
        // $citizenNewRegistrationDataArray['citizenNewRegistration'] = $citizenNewRegistrationDataCount;
        // $citizenGetServicesDataArray['citizenGetServices'] = $citizenGetServicesDataCount;

        // $Cards = [$allCitizenCountArray, $citizenNewRegistrationDataArray, $citizenGetServicesDataArray];
        // $data['cardInformation'] = $Cards;

        // $message = "Successfully Data Shown";
        // return $this->responseSuccess(200, true, $message, $data);

        $data = [];

        // Count all citizens
        $data['allCitizenCount'] = User::Citizen()->where('is_phone_verified', 1)->count();

        // Count new citizen registrations in the last 7 days
        $data['citizenNewRegistration'] = User::Citizen()
            ->where('is_phone_verified', 1)
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->count();

        // Count citizens with specific case status
        $caseStatus = 2;
        $data['citizenGetServices'] = User::Citizen()
            ->where('is_phone_verified', 1)
            ->whereHas('citizenCases', function ($query) use ($caseStatus) {
                $query->where('lcs_cases.status', $caseStatus);
            })
            ->count();
        $data['top100CitizenConsultation'] = 100;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);

    }

}
