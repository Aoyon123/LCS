<?php

namespace App\Http\Controllers\Consultant;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LcsCase;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
// use App\Http\Controllers\DateTime;
use DateTime;


class ConsultantInformationController extends Controller
{
    use ResponseTrait;
    public function consultantDashboardInformation(Request $request)
    {
        // $allInformation
        $data = [];  
        $largeCards=[];  
        $activeConsultation=[];
        $todaysTotalConsultation=[];
        $newRequest=[];
        $completeConsultation=[];
        $consultantScheduleTime=[];

        $competition_final=[];
        $competition_day=[];
        $competition_complete =[];
        $competition_complete_totalRating=[];
        $competition_cancel =[];
        $competition_rating =[];
        $item = [];
        $itemFeedbackRating = [];

        $id = auth()->user()->id;  

        // $newRegisterCitizen = User::where(['type' => 'citizen'])
        //               ->whereDate('created_at', '>=', date('Y-m-d H:i:s',strtotime('-7 days')) )
        //               ->count();

        $todaysTotalConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->whereDate('updated_at', Carbon::today())->count();

        $activeConsultationCount = LcsCase::where('consultant_id', $id)->InProgress()->count();

        $newRequestCount = LcsCase::where('consultant_id', $id)->Initial()->count();

        $completeConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->count();
       
        $getConsultantTime = User::where('id', $id)->select('schedule')->get();

        $consultantNewRequest = LcsCase::with('service:id,title')->where('consultant_id', $id)->Initial()->latest()->take(10)->get();

        $feedbackRating = DB::table('lcs_cases')->where('consultant_id', $id)->groupBy('rating')->count('rating');
      // return $ratingCount;
        
       
      
        // $feedbackRating = DB::table('lcs_cases')
        // ->where('consultant_id', $id)
        // ->where('status',2)
        // ->select(DB::raw('count(lcs_cases.rating) as totalRatingCount'),DB::Raw('(lcs_cases.rating) rating'))
        // ->groupBy('rating')
        // ->get();
        
     
        // --------------------------start consultant performance ----------
      
        $completePerformance = DB::table('lcs_cases')
        ->where('consultant_id', $id)
        ->where('status',2)
        ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")

        ->selectRaw("COUNT(updated_at) as complete")
        ->orderBy('updated_at', 'ASC')
        ->groupBy('day')
        ->get()->toArray();


        $cancelConsultation = DB::table('lcs_cases')
        ->where('consultant_id', $id)
        ->where('status',3)
        ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")
        ->selectRaw("COUNT(updated_at) as cancel")
        ->orderBy('updated_at', 'ASC')
        ->groupBy('day')
        ->get()->toArray();


        $competition_all=[];
        for($i=0;$i<=30;$i++)
        {
            $str = -$i.' days';
            $newCompete = date('Y-m-d', strtotime($str));
            array_push($competition_all, $newCompete);
        }
        // return $competition_all;
        $wordCount = count($competition_all);
 
       

   ///for complete Consultation
        
        foreach($competition_all as $key => $date) {
            $flag = 0;
            foreach($completePerformance as $value){
                //  return $value->day;
                if($value->day == $date){
                    // return $date;
                    // $item['day']= $date;
                    // $complete['complete'] = $value->day;
                    // array_push($competition_final, $value);
                    array_push($competition_day, $date);
                    array_push($competition_complete, $value->complete);
                    $flag = 1;
                    break;
                }
            }

            if($flag == 0){
                // $item['day'] = $date;
                // $complete['complete'] = 0;
                // array_push($competition_final, $item);
                array_push($competition_day, $date);
                array_push($competition_complete, 0);
            }

         }

    /// for cancel Consultation
         foreach($competition_all as $key => $date) {
            $flag = 0;
            foreach($cancelConsultation as $value){
               // return $value;
                if($value->day == $date){
                    // return $date;
                    // $item['day']= $date;
                    // $complete['complete'] = $value->day;
                    // array_push($competition_final, $value);
                    array_push($competition_day, $date);
                    array_push($competition_cancel, $value->cancel);
                    $flag = 1;
                    break;
                }
            }

            if($flag == 0){
                // $item['day'] = $date;
                // $complete['complete'] = 0;
                // array_push($competition_final, $item);
                array_push($competition_day, $date);
                array_push($competition_cancel, 0);
            }
           
         }

       //  -------------------------- End consultant performance ----------

     
        //--------- Start Feedback  Rating--------------------

        $feedbackRating = DB::table('lcs_cases')
        ->where('consultant_id', $id)
        ->where('rating', '>=', 0.0)
        ->where('status',2)
        ->select(DB::raw('count(lcs_cases.rating) as totalRatingCount'),DB::Raw('(lcs_cases.rating) rating'))
        ->groupBy('rating')
        ->get()->toArray();
       // return $feedbackRating;
        $ratingCount=[];
        for($i=0;$i<=5;$i++)
        {
            $number = $i;
            array_push($ratingCount, $number);
        }

        foreach($ratingCount as $key => $rating) {
            $flag = 0;
            foreach($feedbackRating as $ratingValue){
                if($ratingValue->rating == $rating){
                    // return $date;
                    // $item['day']= $date;
                    // $complete['complete'] = $value->day;
                    // array_push($competition_final, $value);
                    array_push($competition_rating, $rating);
                    array_push($competition_complete_totalRating, $ratingValue->totalRatingCount);
                    $flag = 1;
                    break;
                }
            }

            if($flag == 0){
                // $item['day'] = $date;
                // $complete['complete'] = 0;
                // array_push($competition_final, $item);
                array_push($competition_rating, $rating);
                array_push($competition_complete_totalRating, 0);
            }

         }
//    return $competition_complete_totalRating;

       //--------- End Feedback  Rating--------------------

        $item['day']= $competition_day;
        $item['complete']= $competition_complete;
        $item['cancel']= $competition_cancel;
        $itemAll=[$item];

        $itemFeedbackRating['rating'] = $competition_rating;
        $itemFeedbackRating['totalRatingCount'] = $competition_complete_totalRating;
        $itemAllFeedbackRating=[$itemFeedbackRating];

        $activeConsultation['activeConsultation'] = $activeConsultationCount;
        $todaysTotalConsultation['todaysTotalConsultation'] = $todaysTotalConsultationCount;
        $newRequest['newRequest'] = $newRequestCount;
        $completeConsultation['completeConsultation'] = $completeConsultationCount;
        $largeCards=[$activeConsultation,$todaysTotalConsultation,$newRequest,$completeConsultation];
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
    

    public function consultantMobileDashboardInformation(Request $request){
      

        $data = []; 

        $competition_final=[];
        $competition_day=[];
        $competition_day2=[];
        $competition_day3=[];
        $competition_complete =[];
        $competition_complete_totalRating=[];
        $competition_cancel =[];
        $competition_rating =[];
        $item = [];
        $item2 = [];
        $item3 = [];
        $itemFeedbackRating = [];

        $id = auth()->user()->id;  

        $todaysTotalConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->whereDate('updated_at', Carbon::today())->count();

        $activeConsultationCount = LcsCase::where('consultant_id', $id)->InProgress()->count();

        $newRequestCount = LcsCase::where('consultant_id', $id)->Initial()->count();

        $completeConsultationCount = LcsCase::where('consultant_id', $id)->Completed()->count();
    

       
         // --------------------------start consultant performance ----------
      
         $completePerformance = DB::table('lcs_cases')
         ->where('consultant_id', $id)
         ->where('status',2)
         ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")
 
         ->selectRaw("COUNT(updated_at) as complete")
         ->orderBy('updated_at', 'ASC')
         ->groupBy('day')
         ->get()->toArray();
 
 
         $cancelConsultation = DB::table('lcs_cases')
         ->where('consultant_id', $id)
         ->where('status',3)
         ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m-%d') as day")
         ->selectRaw("COUNT(updated_at) as cancel")
         ->orderBy('updated_at', 'ASC')
         ->groupBy('day')
         ->get()->toArray();
 
 
         $competition_all=[];
         for($i=0;$i<=30;$i++)
         {
             $str = -$i.' days';
             $newCompete = date('Y-m-d', strtotime($str));
             array_push($competition_all, $newCompete);
         }
         // return $competition_all;
         $wordCount = count($competition_all);
  
        
 
    ///for complete Consultation
         
         foreach($competition_all as $key => $date) {
             $flag = 0;
             foreach($completePerformance as $value){
                 // return $value;
                 if($value->day == $date){
                     array_push($competition_day, $value);
                     $flag = 1;
                     break;
                 }
             }
            //  $competition_day2 = [];
             if($flag == 0){
                 $item['day'] = $date;
                 $item['complete'] = 0;

                 array_push($competition_day, $item);
                 
             }
          }

        
          $count = 0;
        //   return $competition_day[$count];
// return $competition_day;
          foreach($competition_all as $key => $date) {
             $flag = 0;
             foreach($cancelConsultation as $value){
                 if($value->day == $date){
                    //$value->complete = $competition_day[$count]['complete'];
                    //$value['complete'] = $competition_day[$count]['complete'];
                     array_push($competition_day2, $value);
                     $flag = 1;
                     break;
                 }
             }
 
            //dd($competition_day[$key]['complete']);
             if($flag == 0){
                $item2['day'] = $date;
                $item2['cancel'] = 0;
                //$item2['complete'] = 0;//$competition_day[$key]['complete'];
                array_push($competition_day2, $item2);
             } 
             
             $count++;
          }

          //return $competition_day2;

//           return [$]
// //  $finalArray = [];
//           for($i = 0; $i < 30; $i++){
//             $competition_day2[$i]['ffff'] = 3;
//           }

        //  return $competition_day2;

        $finalArray = [];
        $finalArray1 = [];
          foreach($competition_day2 as $key =>  $cancel){
            //return $cancel['complete'] = 0;
           //return $competition_day[$key]['complete'];
            //$cancel['completed'] = 0;//$competition_day[$key]->complete;

            $cancel = array(
                'completed' => $competition_day[$key]['complete'],
                'cancel' => $competition_day2[$key]['cancel'],
                'day' => $competition_day2[$key]['day']
            );

            return $cancel;

            $finalArray->push(new Game(['name' => 'Game1', 'color' => 'red']));
        
            array_push($competition_day2, $cancel);
 
          }

          
           return $finalArray;
// $ff = collect($competition_day2);
//           $competition_day3 = $ff->map(function($item, $key) use($competition_day) {
//             $item['ccc'] = $competition_day[$key]['complete'];
//             return $item;
//         });

        // return $competition_day3;


        // //   return $finalArray;

        // //   return $competition_day2[1]['cancel'];
        // // return $competition_day2->cancel;
        //   foreach($competition_day as $key => $date) {
        //     //   return $date;
        //     foreach($date as $key => $singleDate){
        //         $flag = 0;
        //     foreach($competition_day2 as $key => $date2){
        //         // return $singleDate;
        //         foreach($date2 as $key => $singleDate2){
        //             //    return $singleDate2;
        //         if($singleDate == $singleDate2){
                   
        //             $item3['cancel'] = $competition_day2[1]['cancel'];
        //             array_push($competition_day, $item3);
        //         }
        //     }  
        // }
        //     }                    
        //  }

        //  return $competition_day;


        //   array_push($competition_day, $competition_day2);

        //   return  $competition_day3;

        //  -------------------------- End consultant performance ----------

        $item['day']= $competition_day;
        $item['complete']= $competition_complete;
        $item['cancel']= $competition_cancel;
        $itemAll=[$item];

       return $itemAll;




        $data['todaysTotalConsultation'] = $todaysTotalConsultationCount;
        $data['activeConsultation'] = $activeConsultationCount;
        $data['newRequestCount'] = $newRequestCount;
        $data['completeConsultation'] = $completeConsultationCount;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
    

}
