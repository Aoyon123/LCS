<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitizenInformationController extends Controller
{

    use ResponseTrait;

    public function citizenDashboardInformation(Request $request)
    {
        $data = [];
        $completeMonth = [];
        $competition_complete = [];
        $monthList = [];

        $id = auth()->user()->id;
        $completeConsultationCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->Completed()->count();
        $runningConsultationCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->InProgress()->count();
        $pendingConsultationCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->Initial()->count();
        $cancelConsultationCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->Cancel()->count();

        $topConsultant = User::select('id', 'name', 'profile_image', 'current_profession')
            ->Consultant()->Status()->Approval()->orderBy('rates', 'desc')
            ->take(5)->get();

        $takingConsultation = LcsCase::with('service:id,title')
            ->select('id', 'service_id', 'citizen_id')
            ->where('citizen_id', $id)->Completed()
            ->orderBy('id', 'desc')
            ->groupBy('service_id')
            ->get();
        // --------------------------start taking service  ----------

        $complete = DB::table('lcs_cases')
            ->where('citizen_id', $id)
            ->where('status', 2)
            ->where(['deleted_at' => null])
            ->selectRaw("DATE_FORMAT(updated_at, '%m') as month")
            ->selectRaw("COUNT(updated_at) as complete")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('month')
            ->get()->toArray();

        $numberOfMonths = 12;
        $currentMonth = strtotime('now');
        $months = [];

        for ($i = 0; $i < $numberOfMonths; $i++) {
            $completeMonth[] = date('F', $currentMonth);
            $currentMonth = strtotime('last day of previous month', $currentMonth);
        }
        //  return $completeMonth;
        foreach ($completeMonth as $key => $month) {
            // return $month;
            $flag = 0;
            foreach ($complete as $value) {
                if ($value->month == '01') {
                    $value->month = "January";
                } else if ($value->month == '02') {
                    $value->month = "February";
                } else if ($value->month == '03') {
                    $value->month = "March";
                } else if ($value->month == '04') {
                    $value->month = "April";
                } else if ($value->month == '05') {
                    $value->month = "May";
                } else if ($value->month == '06') {
                    $value->month = "June";
                } else if ($value->month == '07') {
                    $value->month = "July";
                } else if ($value->month == '08') {
                    $value->month = "August";
                } else if ($value->month == '09') {
                    $value->month = "September";
                } else if ($value->month == '10') {
                    $value->month = "October";
                } else if ($value->month == '11') {
                    $value->month = "November";
                } else if ($value->month == '12') {
                    $value->month = "December";
                }
                if ($value->month == $month) {
                    array_push($monthList, $month);
                    array_push($competition_complete, $value->complete);
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                array_push($monthList, $month);
                array_push($competition_complete, 0);
            }
        }

        //  -------------------------- End taking service ----------

        $item['month'] = $completeMonth;
        $item['complete'] = $competition_complete;
        $itemAll = [$item];

        $pendingConsultation['pendingConsultation'] = $pendingConsultationCount;
        $runningConsultation['runningConsultation'] = $runningConsultationCount;
        $completeConsultation['completeConsultation'] = $completeConsultationCount;
        $cancelConsultation['cancelConsultation'] = $cancelConsultationCount;

        $largeCards = [$pendingConsultation, $runningConsultation, $completeConsultation, $cancelConsultation];
        $data['largeCards'] = $largeCards;
        $data['takingService'] = $itemAll;
        $data['topConsultantList'] = $topConsultant;
        $data['takingConsultation'] = $takingConsultation;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function citizenConsultationInformation(Request $request)
    {

        $data = [];
        $id = auth()->user()->id;

        $activeServiceCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->InProgress()->count();
        $waitForPaymentCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->Accepted()->count();
        $cancelConsultationCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->Cancel()->count();
        $serviceRequestCount = LcsCase::where('citizen_id', $id)->where(['deleted_at' => null])->Initial()->count();

        //  -------------- Start For Filter Information ----------
        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';

        $totalConsultantationDataCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.rating',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.status',
                'users.name',
                'users.code',
                'users.profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        $caseData = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.title',
                'lcs_cases.document_file',
                'lcs_cases.rating',
                'lcs_cases.document_link',
                'lcs_cases.case_initial_date',
                'lcs_cases.case_status_date',
                'lcs_cases.description',
                'lcs_cases.case_code',
                'lcs_cases.status',
                'users.name',
                'users.code',
                'users.profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC');

        $params = $request->all();

        foreach ($params as $key => $param) {

            if ($key === 'active') {
                $totalConsultantationDataCount = $caseData->where('lcs_cases.status', $param)->count();
                $caseData = $caseData->where('lcs_cases.status', $param);
            } elseif ($key === 'waitForPayment') {
                $totalConsultantationDataCount = $caseData->where('lcs_cases.status', $param)->count();
                $caseData = $caseData->where('lcs_cases.status', $param);
            } elseif ($key === 'cancelConsultation') {
                $totalConsultantationDataCount = $caseData->where('lcs_cases.status', $param)->count();
                $caseData = $caseData->where('lcs_cases.status', $param);
            } elseif ($key === 'serviceRequest') {
                $totalConsultantationDataCount = $caseData->where('lcs_cases.status', $param)->count();
                $caseData = $caseData->where('lcs_cases.status', $param);
            }

        }

        $activeService['activeService'] = $activeServiceCount;
        $waitForPayment['waitForPayment'] = $waitForPaymentCount;
        $cancelConsultation['cancelConsultation'] = $cancelConsultationCount;
        $serviceRequest['serviceRequest'] = $serviceRequestCount;

        $Cards = [$activeService, $waitForPayment, $cancelConsultation, $serviceRequest];

        $ItemAll = [];
        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
                $ItemAll['offset'] = $params['offset'];
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $caseData->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $caseData->limit($params['limit'])->get();
            }
        } else {
            $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
            $ItemAll['consultation'] = $caseData->get();
        }
        $data['cardInformation'] = $Cards;
        $data['filterInformation'] = [$ItemAll];

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

}
