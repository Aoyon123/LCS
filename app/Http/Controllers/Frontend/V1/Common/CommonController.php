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
        $service = DB::table('services')->where('status', 1)->get();
        // $service = User::serviceList()->get();
        //  return $service;
        $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'type', 'profile_image', 'district_id', 'gender', 'rates','totalRating', 'active_status', 'years_of_experience', 'schedule'];

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
            $message = "Successfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function consultation(Request $request)
    {
        $totalConsultant = User::Status()->Approval()->Consultant()->count();
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
            'totalRating',
            'years_of_experience',
            'schedule'
        ];
        $params = $request->all();
     //  return $params;

        $consultant = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
                'serviceList',
            ]
       
        )->select($consultants_selected_fields)->status()->approval()->consultant();

        foreach ($params as $key => $param) {

            if ($key === 'services') {
                $consultant = $consultant->whereHas('services', function ($q) use ($param) {
                    $q->where('services.id', $param);

                });
                $totalConsultant = $consultant->whereHas('services', function ($q) use ($param) {
                    $q->where('services.id', $param);
                })->count();

            } elseif ($key === 'active') {
                $totalConsultant = $consultant->where('active_status', $param)->count();
                $consultant = $consultant->where('active_status', $param);

            } elseif ($key === 'ratingValue') {
                $totalConsultant = $consultant->where('rates', $param)->count();
                $consultant = $consultant->where('rates', $param);
            }
            
            elseif ($key === 'consultantRating') {
                $totalConsultant = $consultant->where('users.rates', '<=', $param)
                                 ->orderBy('users.rates', 'desc')->count();
                           
                $consultant = $consultant->where('users.rates', '<=', $param)
                              ->orderBy('users.rates', 'desc');
            }

            elseif ($key === 'popularity') {
                $totalConsultant =  $consultant->orderBy('users.rates', $param)
                                               ->count();          
                $consultant = $consultant->orderBy('users.rates', $param);                             
            } 

            elseif ($key === 'yearsOfExperience') {

                $totalConsultant =$consultant->orderBy('users.years_of_experience', $param)->count();

                $consultant = $consultant->orderBy('users.years_of_experience', $param);
            }

            elseif ($key === 'ranking') {
                $totalConsultant =  $consultant->orderBy('users.totalRating', $param) 
                                               ->count();
                           
                $consultant = $consultant->orderBy('users.totalRating', $param);
                                      
                
            } 
   
            elseif ($key === 'search') {
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

                $totalConsultant = $consultant->where(function ($query) use ($userSearchFields, $param) {
                    foreach ($userSearchFields as $userSearchField) {
                        $query->orWhere($userSearchField, 'like', '%' . $param . '%');
                    }
                })->count();


                $totalConsultant = $consultant->orWhereHas('academics', function ($query) use ($academicSearchFields, $param) {
                    foreach ($academicSearchFields as $academicSearchField) {
                        $query->where($academicSearchField, 'like', '%' . $param . '%');
                    }
                })->count();

                $totalConsultant = $consultant->orWhereHas('experiances', function ($query) use ($experienceSearchFields, $param) {
                    foreach ($experienceSearchFields as $experienceSearchField) {
                        $query->where($experienceSearchField, 'like', '%' . $param . '%');
                    }
                })->count();


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
                $totalConsultant = $consultant->orderBy('users.rates', $param)->count();
                $consultant = $consultant->orderBy('users.rates', $param);
            }             
        }

        if (isset($params['limit'])) {
            if (isset($params['offset'])) {
                $data['totalConsultant'] = $totalConsultant;
                $data['offset'] = $params['offset'];
                $data['limit'] = $params['limit'];
                $data['list'] = $consultant->offset($params['offset'])->limit($params['limit'])->get();
            } else {
                $data['limit'] = $params['limit'];
                $data['list'] = $consultant->limit($params['limit'])->get();
            }
        } else {
            $data['totalConsultant'] = $totalConsultant;
            $data['list'] = $consultant->get();
        }

        $message = "Successfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }


    public function consultationSelf(Request $request)
    {
        $params = $request->all();

        if (!empty($request->all())) {
            $services = !empty($params['services']) ? $params['services'] : null;
            $active = !empty($params['active']) ? $params['active'] : null;
            $ratingValue = !empty($params['ratingValue']) ? $params['ratingValue'] : null;
            $search = !empty($params['search']) ? $params['search'] : null;
        } else {
            $services = null;
            $active = null;
            $ratingValue = null;
            $search = null;
        }

            $detailsInfo = DB::table('users')
             ->where(['users.status' => 1, 'users.approval' => 1, 'users.type' => 'consultant'])
             ->join('academic_qualifications', 'users.id', '=', 'academic_qualifications.user_id')
             ->join('experiences', 'users.id', '=', 'experiences.user_id')
             ->join('service_user', 'users.id', '=', 'service_user.user_id')
             ->join('services', 'service_user.service_id', '=', 'services.id')

             ->when($services, function ($query) use ($services) {
                 $query->where('service_user.service_id', 'like', '%' . $services . '%');
             })

             ->when($active, function ($query) use ($active) {
                 $query->where('users.active_status', 'like', '%' . $active . '%');
             })

             ->when($ratingValue, function ($query) use ($ratingValue) {
                 $query->where('users.rates', '<', $ratingValue + 1);
                 $query->where('users.rates', '>=', $ratingValue);
             })

             ->when($search, function ($query) use ($search) {
                 $query->where('users.name', 'like', '%' . $search . '%');
                 $query->orWhere('users.address', 'like', '%' . $search . '%');
                 $query->orWhere('users.years_of_experience', 'like', '%' . $search . '%');
                 $query->orWhere('academic_qualifications.education_level', 'like', '%' . $search . '%');
                 $query->orWhere('experiences.institute_name', 'like', '%' . $search . '%');
             })

             ->select(
                 'users.id',
                 'users.name',
                 'users.active_status',
                 'users.phone',
                 'users.email',
                 'users.address',
                 'users.code',
                 'users.type',
                 'users.district_id',
                 'users.profile_image',
                 'users.gender',
                 'users.rates',
                 'users.totalRating',
                 'users.years_of_experience',
                 'users.schedule',

                 'academic_qualifications.education_level',
                 'experiences.institute_name',
                 'service_user.service_id',
                 'services.title',
                 'services.status as services_status',
             )
            ->groupBy('users.id')
            ->get();
             // ->latest();
           // ->get();

            $message = "Successfully Data Shown";
            return $this->responseSuccess(200, true, $message, $detailsInfo);

    }






    public function activeServiceList()
    {
        $service = Service::activeservicelist()->get();

        if (!empty($service)) {
            $message = "Successfully Service Data Shown";
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
            $message = "Successfully Data Shown";
            return $this->responseSuccess(200, true, $message, $districts);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function faqAll(Request $request)
    {

        $data = [];
        $faq_selected_fields = [
            'id',
            'category_name',
            'question',
            'answer',
            'answer_image',
            'status',
        ];

        $params = $request->all();
        $faqData = FrequentlyAskedQuestion::select($faq_selected_fields)
            ->activeFrequentlyAskedQuestion();

        foreach ($params as $key => $param) {
            if ($param === 'citizen') {
                $faqCitizenData = $faqData->where('category_name', $param);
            }

            if ($param === 'consultant') {
                $faqConsultantData = $faqData->where('category_name', $param);
            }
        }

        if (isset($param['citizen'])) {
            $data = $faqCitizenData->get();
        } elseif (isset($param['consultant'])) {
            $data = $faqConsultantData->get();
        } else {
            $data = $faqData->get();
        }

        if ($data) {
            $message = "Successfully FAQ Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

}
