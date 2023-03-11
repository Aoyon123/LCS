<?php

namespace App\Http\Controllers\Frontend\V1\Common;

use App\Models\User;
use App\Models\LcsCase;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\FrequentlyAskedQuestion;

class CommonController extends Controller
{
    use ResponseTrait;
    public function dashboardMobile()
    {
          $service = DB::table('services')->where('status',1)->get();
       // $service = User::serviceList()->get();
       //  return $service;
        $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'type', 'profile_image', 'district_id', 'gender', 'rates', 'active_status', 'years_of_experience', 'schedule'];

        $active = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]
        )->select($consultants_selected_fields)->approval()->consultant()->status()->active()->get();


        $topRated = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]
        )->select($consultants_selected_fields)->orderBy('rates', 'DESC')->take(6)->status()->approval()->consultant()->get();
        if ($topRated && $service && $active) {
            $data = [
                'active' => $active,
                'topRated' => $topRated,
                'services' => $service
            ];
        }

        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function consultation(Request $request)
    {
        $data = [];
        $consultants_selected_fields = [
            'id',
            'name',
            'active_status',
            'phone',
            'email',
            'address',
            'code',
            'type',
            'district_id',
            'profile_image',
            'gender',
            'rates',
            'years_of_experience',
            'schedule'
        ];
        $params = $request->all();
        //   return $params;
        $id = 2;
        //  foreach ($consultants_selected_fields as $consultantData) {
        $rating = LcsCase::select(DB::raw('count(rating) as totalRating'))
            ->where('consultant_id', $id)->completed()->get();

        // $consultantCount = DB::table('users')
        //     ->select(
        //         'users.name',
        //         'users.id as user_id',
        //         'lcs_cases.id',
        //         'lcs_cases.status',
        //         DB::raw('count(rating) as totalRating')
        //     )
        //     ->where(['lcs_cases.status' => 2])
        //     ->join('lcs_cases', 'users.id', '=', 'lcs_cases.consultant_id')
        //     ->get();

        $consultant = User::with(
                [
                    'experianceLatest:user_id,institute_name',
                    'academicLatest:user_id,education_level',
                    'serviceLatest',
                    'serviceList',
                   // 'countRating',
                ]

            )->select($consultants_selected_fields)->status()->approval()->consultant();

        foreach ($params as $key => $param) {

            if ($key === 'services') {
                $consultant = $consultant->whereHas('services', function ($q) use ($param) {
                    $q->where('services.id', $param);
                });
            } elseif ($key === 'active') {
                $consultant = $consultant->where('active_status', $param);

            } elseif ($key === 'ratingValue') {
                $consultant = $consultant->where('rates', $param);
            } elseif ($key === 'search') {
                $userSearchFields = [
                    'name',
                    'email',
                    'address',
                    'code',
                    'schedule',
                    'years_of_experience'
                ];

                $servicesSearchFields = ['title'];
                $experienceSearchFields = ['institute_name'];
                $academicSearchFields = ['education_level'];

                $consultant = $consultant->where(function ($query) use ($userSearchFields, $param) {
                    foreach ($userSearchFields as $userSearchField) {
                        $query->orWhere($userSearchField, 'like', '%' . $param . '%');
                    }
                })
                    ->orWhereHas('services', function ($query) use ($servicesSearchFields, $param) {
                        foreach ($servicesSearchFields as $serviceSearchField) {
                            $query->where($serviceSearchField, 'like', '%' . $param . '%');
                        }
                    })

                    ->orWhereHas('experiances', function ($query) use ($experienceSearchFields, $param) {
                        foreach ($experienceSearchFields as $experienceSearchField) {
                            $query->where($experienceSearchField, 'like', '%' . $param . '%');
                        }
                    })

                    ->orWhereHas('academics', function ($query) use ($academicSearchFields, $param) {
                        foreach ($academicSearchFields as $academicSearchField) {
                            $query->where($academicSearchField, 'like', '%' . $param . '%');
                        }
                    });
                // $data[$key] = $consultant->get();
            } elseif ($key === 'ratingTop') {
                $consultant = $consultant->orderBy('users.rates', $param);
            }
        }

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $data['offset'] = $params['offset'];
                $data['limit'] = $params['limit'];
                $data['list'] = $consultant->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $data['limit'] = $params['limit'];
                $data['list'] = $consultant->limit($params['limit'])->get();
            }
        } else {
            $data['list'] = $consultant->get();
        }

        $message = "Succesfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }


    public function activeServiceList()
    {
        $service = Service::activeservicelist()->get();

        if (!empty($service)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $service);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function allDistricts()
    {
        $districts = DB::table('districts')->select(['id', 'name_bn', 'name_en'])->get();
        if (!empty($districts)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $districts);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }


    public function faqAll()
    {
        $faqData = FrequentlyAskedQuestion::activefrequentlyaskedquestion()->get();
        $groupFaqData = $faqData->groupBy('category_name');

        if ($groupFaqData) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $groupFaqData);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

}
