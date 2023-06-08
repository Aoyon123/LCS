<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LcsCase;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

        $inProgressCount = LcsCase::InProgress()->count();
        $initialCount = LcsCase::Initial()->count();
        $completeCount = LcsCase::Completed()->count();
        $cancelConsultationCount = LcsCase::Cancel()->count();
        $totalActiveConsultantCount = User::Consultant()->Status()->Approval()->Status()->count();
        $totalWaitingConsultantApproveCount = User::Consultant()->Initial()->count();
        $totalRegisterCitizenCount = User::where(['type' => 'citizen'])
            ->where('is_phone_verified', 1)
            ->count();
        $onlineConsultantCount = User::Consultant()->Status()->Approval()->Active()->count();


        // $topRatedConsulatntCount = User::Consultant()->Status()->Approval()
        //     ->where('users.rates', '>=', 4.0)->count();

        // $waitForPaymentCount = 0;

        // $newRegisterRating = User::where(['type' => 'citizen'])
        //     ->whereDate('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
        //     ->count();



    // --------  Start Consultant Performance  ------------

        $complete = DB::table('lcs_cases')
        ->where('status', 2)
        ->selectRaw("DATE_FORMAT(updated_at, '%m') as month")
        ->selectRaw("COUNT(updated_at) as complete")
        ->orderBy('updated_at', 'ASC')
        ->groupBy('month')
        ->get()->toArray();
        // return $complete;

        $cancel = DB::table('lcs_cases')
        ->where('status', 3)
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
}
