<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\LcsCase;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
// use App\Http\Controllers\DateTime;
use Illuminate\Support\Facades\DB;

class ConsultantInformationController extends Controller
{
    use ResponseTrait;
    public function consultantDashboardInformation(Request $request)
    {
        // $allInformation
        $data = [];
        $largeCards = [];
        $activeConsultation = [];
        $todaysTotalConsultation = [];
        $newRequest = [];
        $completeConsultation = [];
        $consultantScheduleTime = [];

        $competition_final = [];
        $competition_day = [];
        $competition_complete = [];
        $competition_complete_totalRating = [];
        $competition_cancel = [];
        $competition_rating = [];
        $item = [];
        $itemFeedbackRating = [];

        $id = auth()->user()->id;

        // $newRegisterCitizen = User::where(['type' => 'citizen'])
        //               ->whereDate('created_at', '>=', date('Y-m-d H:i:s',strtotime('-7 days')) )
        //               ->count();

        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';

        $totalConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();
        // return $totalConsultationCount;

        // $totalConsultationCount = LcsCase::where('lcs_cases.consultant_id', $id)->where(['lcs_cases.deleted_at' => null])->count();
        // return $totalConsultationCount;
        // $todaysTotalConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->whereDate('updated_at', Carbon::today())->count();

        // $activeConsultationCount = LcsCase::where('consultant_id', $id)->InProgress()->count();

        $activeConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 1])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();
        //   return $activeConsultationCount;

        $todaysTotalConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 2])
            ->whereDate('lcs_cases.updated_at', Carbon::today())
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();
        // return $todaysTotalConsultationCount;

        // $newRequestCount = LcsCase::where('consultant_id', $id)->Initial()->count();

        // $completeConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->count();

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
        // return $completeConsultationCount;

        $newRequestCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 0])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        $getConsultantTime = User::where('id', $id)->select('schedule')->get();

        $consultantNewRequest = LcsCase::with('service:id,title')->where('consultant_id', $id)->Initial()->latest()->take(10)->get();

        $feedbackRating = DB::table('lcs_cases')->where('consultant_id', $id)->groupBy('rating')->count('rating');

        // --------------------------start consultant performance ----------////

        $completePerformance = DB::table('lcs_cases')
            ->where('consultant_id', $id)
            ->where('status', 2)
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")
            ->selectRaw("COUNT(updated_at) as complete")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('day')
            ->get()->toArray();

        $cancelConsultation = DB::table('lcs_cases')
            ->where('consultant_id', $id)
            ->where('status', 3)
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")
            ->selectRaw("COUNT(updated_at) as cancel")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('day')
            ->get()->toArray();

        $competition_all = [];
        for ($i = 0; $i <= 30; $i++) {
            $str = -$i . ' days';
            $newCompete = date('Y-m-d', strtotime($str));
            array_push($competition_all, $newCompete);
        }

        $wordCount = count($competition_all);

        ///for complete Consultation

        foreach ($competition_all as $key => $date) {
            $flag = 0;
            foreach ($completePerformance as $value) {

                if ($value->day == $date) {
                    array_push($competition_day, $date);
                    array_push($competition_complete, $value->complete);
                    $flag = 1;
                    break;
                }
            }

            if ($flag == 0) {
                array_push($competition_day, $date);
                array_push($competition_complete, 0);
            }
        }

        /// for cancel Consultation
        foreach ($competition_all as $key => $date) {
            $flag = 0;
            foreach ($cancelConsultation as $value) {

                if ($value->day == $date) {
                    array_push($competition_day, $date);
                    array_push($competition_cancel, $value->cancel);
                    $flag = 1;
                    break;
                }
            }

            if ($flag == 0) {
                array_push($competition_day, $date);
                array_push($competition_cancel, 0);
            }
        }

        //  -------------------------- End consultant performance ----------

        //--------- Start Feedback  Rating--------------------

        $feedbackRating = DB::table('lcs_cases')
            ->where('consultant_id', $id)
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

        //--------- End Feedback  Rating--------------------

        $item['day'] = $competition_day;
        $item['complete'] = $competition_complete;
        $item['cancel'] = $competition_cancel;
        $itemAll = [$item];

        $itemFeedbackRating['rating'] = $competition_rating;
        $itemFeedbackRating['totalRatingCount'] = $competition_complete_totalRating;
        $itemAllFeedbackRating = [$itemFeedbackRating];

        $activeConsultation['activeConsultation'] = $activeConsultationCount;
        $totalConsultation['totalConsultation'] = $totalConsultationCount;
        $todaysTotalConsultation['todaysTotalConsultation'] = $todaysTotalConsultationCount;
        $newRequest['newRequest'] = $newRequestCount;
        $completeConsultation['completeConsultation'] = $completeConsultationCount;
        $largeCards = [$activeConsultation, $todaysTotalConsultation, $newRequest, $completeConsultation, $totalConsultation];
        $consultantScheduleTime = $getConsultantTime;
        $newRequest = $consultantNewRequest;

        $data['largeCards'] = $largeCards;
        $data['consultantPerformance'] = $itemAll;
        $data['feedBackRating'] = $itemAllFeedbackRating;
        $data['consultantScheduleTime'] = $consultantScheduleTime;
        $data['newRequest'] = $newRequest;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function consultantMobileDashboardInformation(Request $request)
    {

        $data = [];
        $competition_final = [];
        $competition_day = [];
        $competition_day2 = [];
        $feedback_rating = [];
        $item = [];
        $item2 = [];
        $item3 = [];
        $itemFeedbackRating = [];

        $id = auth()->user()->id;

        // $todaysTotalConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->whereDate('updated_at', Carbon::today())->count();

        // $activeConsultationCount = LcsCase::where('consultant_id', $id)->InProgress()->count();

        // $newRequestCount = LcsCase::where('consultant_id', $id)->Initial()->count();

        // $completeConsultationCount = LcsCase::where('consultant_id', $id)
        //     ->where(['deleted_at' => null])->Completed()->count();

        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';

        $totalConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['deleted_at' => null])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();
        // return $totalConsultationCount;

        $activeConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 1])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        $todaysTotalConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 2])
            ->whereDate('lcs_cases.updated_at', Carbon::today())
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

        $newRequestCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 0])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

        // --------------------------start consultant performance ----------

        $completePerformance = DB::table('lcs_cases')
            ->where('consultant_id', $id)
            ->where('status', 2)
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")

            ->selectRaw("COUNT(updated_at) as complete")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('day')
            ->get()->toArray();

        $cancelConsultation = DB::table('lcs_cases')
            ->where('consultant_id', $id)
            ->where('status', 3)
            ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")
            ->selectRaw("COUNT(updated_at) as cancel")
            ->orderBy('updated_at', 'ASC')
            ->groupBy('day')
            ->get()->toArray();

        $competition_all = [];
        for ($i = 0; $i <= 30; $i++) {
            $str = -$i . ' days';
            $newCompete = date('Y-m-d', strtotime($str));
            array_push($competition_all, $newCompete);
        }
        // return $competition_all;
        $wordCount = count($competition_all);

        ///for complete Consultation
        foreach ($competition_all as $key => $date) {
            $flag = 0;
            foreach ($completePerformance as $value) {
                // return $value;
                if ($value->day == $date) {
                    $item['day'] = $value->day;
                    $item['complete'] = $value->complete;
                    array_push($competition_day, $item);
                    $flag = 1;
                    break;
                }
            }
            //  $competition_day2 = [];
            if ($flag == 0) {
                $item['day'] = $date;
                $item['complete'] = 0;
                array_push($competition_day, $item);
            }
        }

        $count = 0;
        foreach ($competition_all as $key => $date) {
            $flag = 0;
            foreach ($cancelConsultation as $value) {
                if ($value->day == $date) {
                    $item2['day'] = $value->day;
                    $item2['cancel'] = $value->cancel;
                    array_push($competition_day2, $item2);
                    $flag = 1;
                    break;
                }
            }

            //dd($competition_day[$key]['complete']);
            if ($flag == 0) {
                $item2['day'] = $date;
                $item2['cancel'] = 0;
                array_push($competition_day2, $item2);
            }

            $count++;
        }

        $finalArray = [];
        foreach ($competition_day2 as $key => $cancel) {
            $cancel['complete'] = $competition_day[$key]['complete'];
            array_push($finalArray, $cancel);
        }

        //  -------------------------- End consultant performance ----------

        //--------- Start Feedback  Rating--------------------

        $feedbackRating = DB::table('lcs_cases')
            ->where('consultant_id', $id)
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
            // return $rating;
            $flag = 0;
            foreach ($feedbackRating as $ratingValue) {
                // return $ratingValue;
                // $key = array_search('$rating', array_column($feedbackRating, 'rating'));

                if ($ratingValue->rating == $rating) {
                    $item3['totalRatingCount'] = $ratingValue->totalRatingCount;
                    $item3['rating'] = $ratingValue->rating;
                    array_push($feedback_rating, $item3);
                    $flag = 1;
                    break;
                }
            }

            if ($flag == 0) {
                $item3['totalRatingCount'] = 0;
                $item3['rating'] = $rating;
                array_push($feedback_rating, $item3);
            }
        }

        //--------- End Feedback  Rating--------------------

        $data['todaysTotalConsultation'] = $todaysTotalConsultationCount;
        $data['cancelConsultation'] = $cancelConsultationCount;
        $data['activeConsultation'] = $activeConsultationCount;
        $data['newRequestCount'] = $newRequestCount;
        $data['completeConsultation'] = $completeConsultationCount;
        $data['consultantPerformance'] = $finalArray;
        $data['feedbackRating'] = $feedback_rating;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }

    public function consultantConsultationInformation(Request $request)
    {

        $data = [];
        $id = auth()->user()->id;

        $type = auth()->user()->type;
        $userType = $type === 'citizen' ? 'consultant' : 'citizen';

        // $runningConsultationCount = LcsCase::where('consultant_id', $id)->InProgress()->count();
        // $acceptConsultationCount = LcsCase::where('consultant_id', $id)->Accepted()->count();
        // $cancelConsultationCount = LcsCase::where('consultant_id', $id)->Cancel()->count();
        // $completeConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->count();

        $runningConsultationCount = DB::table('lcs_cases')
            ->where('lcs_cases.' . $type . '_id', auth()->user()->id)
            ->where(['lcs_cases.deleted_at' => null])
            ->where(['lcs_cases.status' => 1])
            ->select(
                'lcs_cases.id as case_id',
                'lcs_cases.consultant_id',
                'services.id as service_id',
                'services.title as service_title'
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
            ->join('services', 'lcs_cases.service_id', '=', 'services.id')
            ->orderBy('case_id', 'DESC')->count();

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

        // return $acceptConsultationCount;

        //  -------------- Start For Filter Information ----------

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

        $allConsultationData = DB::table('lcs_cases')
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
                'lcs_cases.created_at',
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

        $runningConsultation['runningConsultation'] = $runningConsultationCount;
        $acceptConsultation['acceptConsultation'] = $acceptConsultationCount;
        $cancelConsultation['cancelConsultation'] = $cancelConsultationCount;
        $completeConsultation['completeConsultation'] = $completeConsultationCount;

        $Cards = [$runningConsultation, $acceptConsultation, $cancelConsultation, $completeConsultation];

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

    public function consultantCaseCardInformation(Request $request)
    {
        $data = [];
        $id = auth()->user()->id;
        // Count all waiting Consultation->status(0)
        $data['waitingConsultation'] = LcsCase::Initial()
            ->where(['deleted_at' => null])
            ->where('consultant_id', $id)->count();

        // Count all running Consultation->status(1)
        $data['runningConsultation'] = LcsCase::InProgress()
            ->where(['deleted_at' => null])
            ->where('consultant_id', $id)->count();
        // Count all complete Consultation->status(2)
        $data['completeConsultation'] = LcsCase::Completed()
            ->where(['deleted_at' => null])
            ->where('consultant_id', $id)->count();

        // Count all cancel Consultation->status(3)
        $data['cancelConsultation'] = LcsCase::Cancel()
            ->where(['deleted_at' => null])
            ->where('consultant_id', $id)->count();

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
    public function consultantCaseList(Request $request)
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
            )->join('users', 'lcs_cases.' . $userType . '_id', '=', 'users.id')
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
            // }

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
