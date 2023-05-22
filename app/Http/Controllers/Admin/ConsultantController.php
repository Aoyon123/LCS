<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\LcsCase;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use App\Http\Helper\SMSHelper;
use Illuminate\Http\Response;

class ConsultantController extends Controller
{
    use ResponseTrait;
    public function index()
    {
        DB::beginTransaction();

        try {
            $consultants = User::where('type', 'consultant')->get();
           // info($consultants);
            if ($consultants != null) {
                $message = "";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $consultants);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }


    public function approvalConsultant(Request $request)
    {
        $consultantData = User::where(['id' => $request->id])->first();

        if ($consultantData) {
            $consultantData->update([
                'approval' => (int) $request->approvalStatus,
                'approved_by' => auth()->user()->id
            ]);

            $messageSuccess = SMSHelper::sendSMS($consultantData->phone, $request->message);

            if ($messageSuccess && $consultantData) {
                $message = "Consultant Approval Update And Message Send Successfully";
                return $this->responseSuccess(200, true, $message, $consultantData);
            } else {
                return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, 'Something wrong');
            }
        }

    }


    public function adminConsultantInformation(Request $request)
    {

        $data = [];
        $totalConsultant = User::Consultant()->Approval()->count();
        $totalActiveConsulatnt = User::Consultant()->Status()->Approval()->Active()->count();
      
        $newRegisterCitizen = User::where(['type' => 'citizen'])
                      ->whereDate('created_at', '>=', date('Y-m-d H:i:s',strtotime('-7 days')) )
                      ->count();

       $todaysTotalConsultation = LcsCase::Completed()->whereDate('updated_at', Carbon::today())->count();


        $waitingForService = LcsCase::InProgress()->count();

        $cancelConsultation = LcsCase::Cancel()->count();

        $serviceHelpRequest = LcsCase::Initial()->count();

        // $totalNewApplyConsultant = User::Consultant()->Initial()->count();
        // $totalRejectedConsulatnt = User::Consultant()->Rejected()->count();

        $topRatedConsulatnt = User::Consultant()->Status()->Approval()
                           ->where('users.rates', '>=', 4.0)->count();
        
        // $data['totalConsultant'] = $totalConsultant;
        $data['nowActiveConsulatnt'] = $totalActiveConsulatnt;
        $data['newRegisterCitizen'] = $newRegisterCitizen;
        $data['waitingForService'] = $waitingForService;
        $data['cancelConsultation'] = $cancelConsultation;
        $data['serviceHelpRequest'] = $serviceHelpRequest;
        $data['todaysTotalConsultation'] = $todaysTotalConsultation;
        $data['topRatingConsulatnt'] = $topRatedConsulatnt;

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }






    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
