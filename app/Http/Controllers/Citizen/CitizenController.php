<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenController extends Controller
{
    use ResponseTrait;

    public function conlsultantList()
    {
        $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'type', 'profile_image', 'district_id', 'gender', 'rates', 'active_status', 'years_of_experience', 'schedule'];
        $consultant = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]
        )->select($consultants_selected_fields)->approval()->consultant()->status()->get();
        //  return $consultant;
        if (!empty($consultant)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $consultant);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }
    public function citizenCaseCardInformation(Request $request)
    {
        $data = [];
        $id = auth()->user()->id;
        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        // Count all waiting Consultation->status(0)
        $data['waitingConsultation'] = LcsCase::Initial()
            ->where(['deleted_at' => null])
            ->where('citizen_id', $id)
            // ->where('consultant_id', $userType . '_id')
            ->count();

        // Count all running Consultation->status(1)
        $data['runningConsultation'] = LcsCase::InProgress()
            ->where(['deleted_at' => null])
            ->where('citizen_id', $id)->count();
        // Count all complete Consultation->status(2)
        $data['completeConsultation'] = LcsCase::Completed()
            ->where(['deleted_at' => null])
            ->where('citizen_id', $id)->count();

        // Count all cancel Consultation->status(3)
        $data['cancelConsultation'] = LcsCase::Cancel()
            ->where(['deleted_at' => null])
            ->where('citizen_id', $id)->count();

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }



    public function citizenCaseList(Request $request)
    {
        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';
        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.rating',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.status',
                'lcs_cases.created_at',
                'lcs_cases.updated_at',
                'lcs_cases.deleted_at',
                'users.name',
                'users.code',
                'users.profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )
            ->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('updated_at', 'DESC');

        if ($caseData->exists()) {
            // all waiting Consultation->status(0)
            if ($request->has('initial')) {
                $caseData = $caseData->where('lcs_cases.status', 0);
            }
            // all running Consultation->status(1)
            if ($request->has('running')) {
                $caseData = $caseData->where('lcs_cases.status', 1);
            }
            // all complete Consultation->status(2)
            if ($request->has('complete')) {
                $caseData = $caseData->where('lcs_cases.status', 2);
            }
            // all cancel Consultation->status(3)
            if ($request->has('rejected')) {
                $caseData = $caseData->where('lcs_cases.status', 3);
            }

            // service wise filter
            if ($request->has('service')) {
                $serviceId = $request->input('service');
                $caseData = $caseData->where('service_id', $serviceId);
            }

            // date wise filter
            if ($request->has('dateToDate')) {
                $dates = explode(' to ', str_replace('/', '-', $request->input('dateToDate')));
                // $startDate = Carbon::parse(trim($dates[0]))
                //     ->toDateTimeString();
                // $endDate = Carbon::parse(trim($dates[1]))
                //     ->toDateTimeString();
                $startDate = trim($dates[0]) . ' 00:00:00';
                $endDate = trim($dates[1]) . ' 23:59:59';

                $caseData = $caseData->whereBetween('lcs_cases.created_at', [$startDate, $endDate]);

            }

            // rating wise filter
            if ($request->has('rating')) {
                $rating = $request->input('rating');
                if ($rating) {
                    $caseData = $caseData->where('lcs_cases.rating', $rating);
                }
            }
           

            $limit = $request->limit;
            $allCaseData = $caseData->paginate($limit ?? 20);

            if ($allCaseData) {
                $message = "Case list data succesfully shown";
                return $this->responseSuccess(200, true, $message, $allCaseData);
            }
        } else {
            $message = "No Data Found";
            return $this->responseError(404, false, $message);
        }
    }
}
