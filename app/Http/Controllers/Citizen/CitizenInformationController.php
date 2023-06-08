<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LcsCase;
use Illuminate\Http\Request;
use DateTime;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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
        $completeConsultationCount = LcsCase::where('citizen_id', $id)->Completed()->count();
        $runningConsultationCount = LcsCase::where('citizen_id', $id)->InProgress()->count();
        $pendingConsultationCount = LcsCase::where('citizen_id', $id)->Initial()->count();
        $cancelConsultationCount = LcsCase::where('citizen_id', $id)->Cancel()->count();

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

        // return $monthList;
        //  -------------------------- End taking service ----------

        ////// -------------------------- End taking service ----------



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
}
