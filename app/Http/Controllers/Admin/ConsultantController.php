<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Helper\SMSHelper;
use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

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
                'approved_by' => auth()->user()->id,
            ]);

            if($request->approvalStatus == 3){
                $consultantData->update([
                    'active_status' => 0,
                ]);
            }

            if($request->approvalStatus == 2){
                $consultantData->update([
                    'active_status' => 0,
                ]);
            }

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
        $totalConsultantationDataCount = User::Consultant()->where('is_phone_verified', 1)->count();
        // $totalConsultantationDataCount = 35;
        $totalActiveConsulatntCount = User::Consultant()->Status()->Approval()->count();
        $totalWaitingConsultantCount = User::Consultant()->Initial()->count();
        $totalRejectedConsultantCount = User::Consultant()->Rejected()->count();

        $highestRatingConsultantCount = User::Consultant()->Approval()->Status()
            ->where('rates', '>=', '4.0')->count();
            // return $highestRatingConsultantCount;
        // $newRegisterCitizen = User::where(['type' => 'citizen'])
        //     ->whereDate('created_at', '>=', date('Y-m-d H:i:s', strtotime('-7 days')))
        //     ->count();
        $totalDeactivatedConsultantCount = User::Consultant()->Deactivated()->count();
        $highestPaidConsultantCount = 0;
        $highestConsultationConsultantCount = 15;
        // $highestConsultationConsultantCount = User::Consultant()->Approval()->Status()
        //     ->where('totalRating', '>=', '100')->count();

        // $type = auth()->user()->type;
        // $userType = $type === 'consultant' ? 'consultant' : 'citizen';


        // $takingConsultation = LcsCase::with('service:id,title')
        //     ->select('id', 'service_id', 'citizen_id')

        //     ->orderBy('id', 'desc')
        //     ->groupBy('service_id')
        //     ->get();




        //  return $highestConsultationConsultantCount;


        $params = $request->all();

        $consultantData = User::where('type', 'consultant')->orderBy('id', 'DESC');

        foreach ($params as $key => $param) {

            if ($key === 'active') {
                //active=1
                $totalConsultantationDataCount = $consultantData
                    ->where('users.status', $param)
                    ->where('users.approval', $param)->count();
                $consultantData = $consultantData
                    ->where('users.status', $param)
                    ->where('users.approval', $param);
            } else if ($key === 'waitingConsultant') {
                //waitingConsultant=0
                $totalConsultantationDataCount = $consultantData
                    ->where('users.approval', $param)->count();
                $consultantData = $consultantData
                    ->where('users.approval', $param);
            } else if ($key === 'rejectedConsultant') {
                //rejectedConsultant=2
                $totalConsultantationDataCount = $consultantData
                    ->where('users.approval', $param)->count();
                $consultantData = $consultantData
                    ->where('users.approval', $param);
            } else if ($key === 'highestRatingConsultant') {
                //highestRatingConsultant=4.0
                $totalConsultantationDataCount = $consultantData
                    ->where('users.approval', '1')
                    ->where('users.status', '1')
                    ->where('users.rates', '>=', $param)
                    ->count();
                $consultantData = $consultantData
                    ->where('users.approval', '1')
                    ->where('users.status', '1')
                    ->where('users.rates', '>=', $param);

            } else if ($key === 'deactivatedConsultant') {
                //deactivatedConsultant=3
                $totalConsultantationDataCount = $consultantData
                    ->where('users.approval', '>=', $param)
                    ->count();

                $consultantData = $consultantData
                    ->where('users.approval', '>=', $param);

            } else if ($key === 'highestConsultationConsultant') {

                    $consultantData = DB::table('lcs_cases')
                    ->where('lcs_cases.status',2)
                    ->where(['deleted_at' => null])
                    ->select(
                        (DB::raw('COUNT(lcs_cases.consultant_id) AS caseCount')),
                        'users.name',
                        'users.profile_image',
                        'users.code',
                        'users.phone',
                        'users.email',
                        'users.approval',
                        'users.address',
                        'users.district_id',
                    )->join('users', 'lcs_cases.' . $param . '_id', '=', 'users.id')
                    ->join('services', 'lcs_cases.service_id', '=', 'services.id')
                    ->groupBy('consultant_id')
                    ->orderBy('caseCount', 'DESC')
                    ->limit(15);
                    // ->get();

            }

        }

        $totalConsultant['totalConsultantCount'] = $totalConsultantationDataCount;
        $totalActiveConsulatnt['totalActiveConsulatntCount'] = $totalActiveConsulatntCount;
        $totalWaitingConsultant['totalWaitingConsultantCount'] = $totalWaitingConsultantCount;
        $totalRejectedConsultant['totalRejectedConsultantCount'] = $totalRejectedConsultantCount;

        $highestRatingConsultant['highestRatingConsultantCount'] = $highestRatingConsultantCount;
        $totalDeactivatedConsultant['totalDeactivatedConsultantCount'] = $totalDeactivatedConsultantCount;
        $highestPaidConsultant['highestPaidConsultantCount'] = $highestPaidConsultantCount;
        $highestConsultationConsultant['highestConsultationConsultantCount'] = $highestConsultationConsultantCount;
        $Cards = [$totalConsultant, $totalActiveConsulatnt, $totalWaitingConsultant, $totalRejectedConsultant,
            $highestRatingConsultant, $totalDeactivatedConsultant, $highestPaidConsultant, $highestConsultationConsultant];

        $ItemAll = [];

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
                $ItemAll['offset'] = $params['offset'];
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $consultantData->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $ItemAll['limit'] = $params['limit'];
                $ItemAll['consultation'] = $consultantData->limit($params['limit'])->get();
            }
        } else {
            $ItemAll['totalConsultantation'] = $totalConsultantationDataCount;
            $ItemAll['consultation'] = $consultantData->get();
        }
        $data['cardInformation'] = $Cards;
        $data['filterInformation'] = [$ItemAll];

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
