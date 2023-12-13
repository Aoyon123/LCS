<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LcsCase;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCaseController extends Controller
{
    use ResponseTrait;
    public function adminCaseData(Request $request)
    {

        $CaseData = DB::table('lcs_cases')
            ->where('deleted_at', null)
            ->select(
                'lcs_cases.id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.rating',
                'lcs_cases.status',
                'lcs_cases.service_id',
                'lcs_cases.created_at',
                'lcs_cases.updated_at',
                'consultant.name as consultant_name',
                'consultant.phone as consultant_phone',
                'citizen.name as citizen_name',
                'citizen.phone as citizen_phone',
                'services.title as service_title',

            )
            ->join('users as consultant', 'lcs_cases.consultant_id', '=', 'consultant.id')
            ->join('users as citizen', 'lcs_cases.citizen_id', '=', 'citizen.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->latest();
        // return $CaseData;
        // $CaseData = LcsCase::with(['citizen:id,name,phone', 'consultant:id,name,phone', 'service:id,title'])
        //     ->select('id', 'status', 'citizen_id', 'consultant_id',
        //         'service_id', 'case_code', 'case_initial_date', 'rating', 'created_at','deleted_at')
        //     ->where(['deleted_at' => null])
        //     ->latest();

        // all waiting Consultation->status(0)
        if ($request->has('initial')) {
            $CaseData = $CaseData->where('lcs_cases.status', 0);
        }
        // all running Consultation->status(1)
        if ($request->has('running')) {
            $CaseData = $CaseData->where('lcs_cases.status', 1);
        }
        // all complete Consultation->status(2)
        if ($request->has('complete')) {
            $CaseData = $CaseData->where('lcs_cases.status', 2);
        }
        // all cancel Consultation->status(3)
        if ($request->has('rejected')) {
            $CaseData = $CaseData->where('lcs_cases.status', 3);
        }

        // service wise filter
        if ($request->has('service')) {
            $serviceId = $request->input('service');
            $CaseData = $CaseData->where('lcs_cases.service_id', $serviceId);
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

            $CaseData = $CaseData->whereBetween('lcs_cases.created_at', [$startDate, $endDate]);

        }

        // rating wise filter
        if ($request->has('rating')) {
            $rating = $request->input('rating');
            if ($rating) {
                $CaseData = $CaseData->where('lcs_cases.rating', $rating);
            }
        }

        $limit = $request->limit;
        $data = $CaseData->paginate($limit ?? 20);

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function adminCaseCardInformation(Request $request)
    {
        $data = [];
        // Count all waiting Consultation->status(0)
        $data['waitingConsultation'] = LcsCase::Initial()->where(['deleted_at' => null])->count();
        // Count all running Consultation->status(1)
        $data['runningConsultation'] = LcsCase::InProgress()->where(['deleted_at' => null])->count();
        // Count all complete Consultation->status(2)
        $data['completeConsultation'] = LcsCase::Completed()->where(['deleted_at' => null])->count();
        // Count all cancel Consultation->status(3)
        $data['cancelConsultation'] = LcsCase::Cancel()->where(['deleted_at' => null])->count();

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

}
