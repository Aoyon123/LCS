<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminInformationController extends Controller
{
    use ResponseTrait;

    public function adminDashboardInformation(Request $request)
    {

        $data = [];
        $largeCards = [];
        $activeConsultation = [];
        $todaysTotalConsultation = [];
        $newRequest = [];
        $completeConsultation = [];
        $consultantScheduleTime = [];
        $newRegisterCitizen = [];
        $competition_final = [];
        $competition_day = [];
        $monthList = [];
        $monthData = [];
        $Citizen = [];
        $Consultant = [];
        $competition_complete = [];
        $competition_complete_totalRating = [];
        $competition_cancel = [];
        $competition_rating = [];
        $item = [];
        $item2 = [];
        $monthlyItemAll = [];
        $monthDataConsultant = [];
        $monthDataCitizen = [];

        $id = auth()->user()->id;

        // $activeConsultationCount = User::Consultant()->Status()->Approval()->Active()->count();

        // $newRegisterCitizenCount = User::where(['type' => 'citizen'])
        //     ->where('is_phone_verified', 1)
        //     ->whereDate('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
        //     ->count();
        //   return $newRegisterCitizenCount;
        // $todaysTotalConsultationCount = LcsCase::whereDate('updated_at', Carbon::today())->count();

        $inProgressCount = LcsCase::where(['deleted_at' => null])->InProgress()->count();
        $initialCount = LcsCase::where(['deleted_at' => null])->Initial()->count();
        $completeCount = LcsCase::where(['deleted_at' => null])->Completed()->count();
        $cancelConsultationCount = LcsCase::where(['deleted_at' => null])->Cancel()->count();

        $totalActiveConsultantCount = User::Consultant()->Status()->Approval()->count();
        $totalWaitingConsultantApproveCount = User::Consultant()->Initial()->count();
        $totalRegisterCitizenCount = User::where(['type' => 'citizen'])
            ->where('is_phone_verified', 1)
            ->count();
        $onlineConsultantCount = User::Consultant()->Approval()->Active()->count();

        // $topRatedConsulatntCount = User::Consultant()->Status()->Approval()
        //     ->where('users.rates', '>=', 4.0)->count();

        // $waitForPaymentCount = 0;

        // $newRegisterRating = User::where(['type' => 'citizen'])
        //     ->whereDate('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
        //     ->count();

        // --------  Start Consultant Performance  ------------

        $complete = DB::table('lcs_cases')
            ->where('status', 2)
            ->where(['deleted_at' => null])
            ->selectRaw("DATE_FORMAT(updated_at, '%m') as month")
            ->selectRaw("COUNT(updated_at) as complete")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('month')
            ->get()->toArray();
        // return $complete;

        $cancel = DB::table('lcs_cases')
            ->where('status', 3)
            ->where(['deleted_at' => null])
            ->selectRaw("DATE_FORMAT(updated_at, '%m') as month")
            ->selectRaw("COUNT(updated_at) as cancel")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('month')
            ->get()->toArray();

        //  return $cancel;
        $numberOfMonths = 12;
        $currentMonth = strtotime('now');
        $months = [];

        for ($i = 0; $i < $numberOfMonths; $i++) {
            $completeMonth[] = date('F', $currentMonth);
            $currentMonth = strtotime('last day of previous month', $currentMonth);
        }
        // return $completeMonth;
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

        foreach ($completeMonth as $key => $month) {
            // return $month;
            $flag = 0;
            foreach ($cancel as $value) {
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
                    // array_push($monthList, $month);
                    array_push($competition_cancel, $value->cancel);
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                // array_push($monthList, $month);
                array_push($competition_cancel, 0);
            }
        }

        //  -------------------------- End consultant performance ----------

        // --------------Start New Registration Rating --------------

        $citizenRegistration = DB::table('users')
            ->where('type', 'citizen')
            ->where('is_phone_verified', 1)
            ->selectRaw("DATE_FORMAT(created_at, '%m') as month")
            ->selectRaw("COUNT(created_at) as totalCitizen")
            ->orderBy('created_at', 'ASC')
            ->groupBy('month')
            ->get()->toArray();

        //  return $citizenRegistration;

        $consultantRegistration = DB::table('users')
            ->where('type', 'consultant')
            ->where('is_phone_verified', 1)
            ->whereNotNull('address')
            ->selectRaw("DATE_FORMAT(created_at, '%m') as month")
            ->selectRaw("COUNT(created_at) as totalConsultant")
            ->orderBy('created_at', 'ASC')
            ->groupBy('month')
            ->get()->toArray();
        // return $consultantRegistration;

        $numberOfMonths = 12;
        $currentMonth = strtotime('now');
        $months = [];

        for ($i = 0; $i < $numberOfMonths; $i++) {
            $allMonth[] = date('F', $currentMonth);
            $currentMonth = strtotime('last day of previous month', $currentMonth);
        }
        // return $allMonth;
        foreach ($allMonth as $key => $month) {
            // return $month;
            $flag = 0;
            foreach ($citizenRegistration as $value) {
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
                    array_push($monthData, $month);
                    array_push($Citizen, $value->totalCitizen);
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                array_push($monthData, $month);
                array_push($Citizen, 0);
            }
        }

        foreach ($allMonth as $key => $month) {
            // return $month;
            $flag = 0;
            foreach ($consultantRegistration as $value) {
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
                    // array_push($monthList, $month);
                    array_push($Consultant, $value->totalConsultant);
                    $flag = 1;
                    break;
                }
            }
            if ($flag == 0) {
                // array_push($monthList, $month);
                array_push($Consultant, 0);
            }
        }

        // Start FeedBack rating  -----------

        $feedbackRating = DB::table('lcs_cases')
            ->where('rating', '>=', 0.0)
            ->where('status', 2)
            ->where(['deleted_at' => null])
            ->select(DB::raw('count(lcs_cases.rating) as totalRatingCount'), DB::Raw('(lcs_cases.rating) rating'))
            ->groupBy('rating')
            ->get()->toArray();

// return $feedbackRating;
        $ratingCount = [];
        for ($i = 0; $i <= 5; $i++) {
            $number = $i;
            array_push($ratingCount, $number);
        }

        foreach ($ratingCount as $key => $rating) {
            $flag = 0;
            foreach ($feedbackRating as $ratingValue) {
                if ($ratingValue->rating == $rating) {
                    array_push($competition_rating, $rating);
                    array_push($competition_complete_totalRating, $ratingValue->totalRatingCount);
                    $flag = 1;
                    break;
                }
            }

            if ($flag == 0) {
                array_push($competition_rating, $rating);
                array_push($competition_complete_totalRating, 0);
            }
        }

        //  --------  End FeedBack rating  -----------

        // ---------- Start Top Consultant List -------

        $topConsultant = User::select('id', 'name', 'profile_image', 'current_profession')
            ->Consultant()->Status()->Approval()->orderBy('rates', 'desc')
            ->take(5)->get();

        // ---------- End Top Consultant List -------
        // -------- Start Service Help Request --------

        $serviceRequest = LcsCase::with('service:id,title')
            ->select('id', 'service_id')
            ->where('status', 0)
            // ->where(['deleted_at' => null])
            ->orderBy('id', 'desc')
            ->groupBy('service_id')
            ->take(10)
            ->get();

        //  -------- End Service Help Request ---------

        $inProgressConsultation['inProgressConsultation'] = $inProgressCount;
        $initialConsultationCount['initialConsultation'] = $initialCount;
        $completeCountConsultation['completeConsultation'] = $completeCount;
        $cancelConsultation['cancelConsultation'] = $cancelConsultationCount;

        $totalActiveConsultant['totalActiveConsultant'] = $totalActiveConsultantCount;
        $totalWaitingConsultantApprove['totalWaitingConsultantApprove'] = $totalWaitingConsultantApproveCount;
        $totalRegisterCitizen['totalRegisterCitizen'] = $totalRegisterCitizenCount;
        $onlineConsultant['onlineConsultant'] = $onlineConsultantCount;

        $largeCards = [$inProgressConsultation, $initialConsultationCount, $completeCountConsultation, $cancelConsultation];
        $smallCards = [$totalActiveConsultant, $totalWaitingConsultantApprove, $totalRegisterCitizen, $onlineConsultant];

        $item['month'] = $monthList;
        $item['completeConsultation'] = $competition_complete;
        $item['cancelConsultation'] = $competition_cancel;
        $monthlyItemAll = [$item];

        $item2['month'] = $monthData;
        $item2['citizenRegistration'] = $Citizen;
        $item2['consultantRegistration'] = $Consultant;
        $monthlyRegistrationReport = [$item2];

        $itemFeedbackRating['rating'] = $competition_rating;
        $itemFeedbackRating['totalRatingCount'] = $competition_complete_totalRating;
        $itemAllFeedbackRating = [$itemFeedbackRating];

        $data['firstLayer'] = $largeCards;
        $data['secondLayer'] = $smallCards;
        $data['consultantPerformance'] = $monthlyItemAll;
        $data['newRegistrationRating'] = $monthlyRegistrationReport;
        $data['feedBackRating'] = $itemAllFeedbackRating;
        $data['topConsultantList'] = $topConsultant;
        $data['serviceRequest'] = $serviceRequest;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function adminAllConsultationInformation(Request $request)
    {

        $data = [];
        $id = auth()->user()->id;

        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';

        // $waitingConsultationCount = LcsCase::where(['deleted_at' => null])->Initial()->count();
        // $runningConsultationCount = LcsCase::where(['deleted_at' => null])->inProgress()->count();
        // $cancelConsultationCount = LcsCase::where(['deleted_at' => null])->Cancel()->count();
        // $completeConsultationCount = LcsCase::where(['deleted_at' => null])->Completed()->count();

        // return $runningConsultationCount;
        $waitingConsultationCount = DB::table('lcs_cases')
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 0])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'lcs_cases.citizen_id',
                'lcs_cases.case_initial_date',
                'lcs_cases.status',
                'services.id as service_id',
                'services.title as service_title',
                'citizen.name as citizen_name',
                'consultant.name as consultant_name',
                'citizen.profile_image as citizen_profile_image',
                'consultant.profile_image as consultant_profile_image',
            )->join('users as citizen', 'lcs_cases.' .'citizen_id', '=', 'citizen.id')
            ->join('users as consultant', 'lcs_cases.' .'consultant_id', '=', 'consultant.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        // return $waitingConsultationCount;

        $acceptConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 4])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        $cancelConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 3])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        $completeConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 2])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        //  -------------- Start For Filter Information ----------

        $totalConsultantationDataCount = DB::table('lcs_cases')
        // ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
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
                'citizen.name as citizen_name',
                'consultant.name as consultant_name',
                'citizen.profile_image as citizen_profile_image',
                'consultant.profile_image as consultant_profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )
            ->join('users as citizen', 'lcs_cases.' . 'citizen_id', '=', 'citizen.id')
            ->join('users as consultant', 'lcs_cases.' . 'consultant_id', '=', 'consultant.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        // return $totalConsultantationDataCount;

        $allConsultationData = DB::table('lcs_cases')
        // ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
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
                'citizen.name as citizen_name',
                'consultant.name as consultant_name',
                'citizen.profile_image as citizen_profile_image',
                'consultant.profile_image as consultant_profile_image',
                'services.id as service_id',
                'services.title as service_title'
            )
            ->join('users as citizen', 'lcs_cases.' . 'citizen_id', '=', 'citizen.id')
            ->join('users as consultant', 'lcs_cases.' . 'consultant_id', '=', 'consultant.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC');

        $params = $request->all();
        //-------------- Start filter loop based on parameter---------------
        foreach ($params as $key => $param) {
            if ($key === 'services') {
                $totalConsultantationDataCount = $allConsultationData->where('lcs_cases.service_id', $param)->count();
                $allConsultationData = $allConsultationData->where('lcs_cases.service_id', $param);
            } elseif ($key === 'filterWithDate') {
                $paramDate = explode(',', $param);
                $starBracket = $paramDate[0];
                $startDateBracket = explode('[', $starBracket);
                $startdataFormat = Carbon::createFromFormat('d-m-Y', $startDateBracket[1]);
                $startDate = $startdataFormat->format('Y-m-d');
                $endBracket = $paramDate[1];
                $endDateBracket = explode(']', $endBracket);
                $enddataFormat = Carbon::createFromFormat('d-m-Y', $endDateBracket[0]);
                $endDate = $enddataFormat->format('Y-m-d');
                $totalConsultantationDataCount = $allConsultationData->whereBetween('lcs_cases.case_initial_date', [$startDate, $endDate])->count();
                $allConsultationData = $allConsultationData->whereBetween('lcs_cases.case_initial_date', [$startDate, $endDate]);
            } elseif ($key === 'rating') {
                $totalConsultantationDataCount = $allConsultationData->where('lcs_cases.rating', '>=', $param)
                    ->where('lcs_cases.status', 2)->count();
                $allConsultationData = $allConsultationData->where('lcs_cases.rating', '>=', $param)
                    ->where('lcs_cases.status', 2);

            }
            // return $allConsultationData;

            elseif ($key === 'runningConsultation') {
                $totalConsultantationDataCount = $allConsultationData->where('lcs_cases.status', $param)->count();
                $allConsultationData = $allConsultationData->where('lcs_cases.status', $param);
            } elseif ($key === 'waitForPayment') {
                $totalConsultantationDataCount = $allConsultationData->where('lcs_cases.status', $param)->count();
                $allConsultationData = $allConsultationData->where('lcs_cases.status', $param);
            } elseif ($key === 'cancelConsultation') {
                $totalConsultantationDataCount = $allConsultationData->where('lcs_cases.status', $param)->count();
                $allConsultationData = $allConsultationData->where('lcs_cases.status', $param);
            } elseif ($key === 'serviceRequest') {
                $totalConsultantationDataCount = $allConsultationData->where('lcs_cases.status', $param)->count();
                $allConsultationData = $allConsultationData->where('lcs_cases.status', $param);
            }

        }
        //-------------- END filter loop based on parameter---------------

        $waitingConsultation['waitingConsultation'] = $waitingConsultationCount;
        $acceptConsultation['acceptConsultation'] = $acceptConsultationCount;
        $completeConsultation['completeConsultation'] = $completeConsultationCount;
        $cancelConsultation['cancelConsultation'] = $cancelConsultationCount;

        $Cards = [$waitingConsultation, $acceptConsultation, $completeConsultation, $cancelConsultation];

        $ItemAll = [];

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
                $ItemAll['offset'] = $params['offset'];
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $allConsultationData->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $allConsultationData->limit($params['limit'])->get();
            }
        } else {
            $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
            $ItemAll['consultation'] = $allConsultationData->get();
        }
        $data['cardInformation'] = $Cards;
        $data['filterInformation'] = [$ItemAll];

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
}
