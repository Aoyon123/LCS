<?php

namespace App\Http\Controllers\Frontend\V1\Consultant;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Experience;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class ConsultantController extends Controller
{
    use ResponseTrait;

    public function consultantList(Request $request)
    {
        //  return auth()->guard('api')->users();
        $data = [];
        $consultants_selected_fields = ['id', 'name', 'active_status', 'phone', 'email', 'address', 'code', 'type', 'profile_image', 'gender', 'rates', 'years_of_experience', 'schedule'];
        $params = $request->all();

        $consultant = User::with(
            [
                'experianceLatest:user_id,institute_name',
                'academicLatest:user_id,education_level',
                'serviceLatest',
            ]

        )->select($consultants_selected_fields)->active();

        // return $consultant;
        foreach ($params as $key => $param) {

            if ($key === 'services') {
                $consultant = $consultant->whereHas('services', function ($q) use ($param) {
                    $q->where('services.id', $param);
                });
            } elseif ($key === 'active') {
                $consultant = $consultant->where('active_status', $param);
            } elseif ($key === 'search') {
                $userSearchFields = ['name', 'phone', 'email', 'address', 'code', 'schedule', 'years_of_experience'];
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
            } elseif ($key === 'rating') {
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

    // public function consultantList2(Request $request)
    // {
    //     $consultant = DB::table('users')
    //         ->select(
    //             'users.*',
    //             'users.id as user_id',
    //             'academic_qualifications.education_level',
    //            'experiences.institute_name',
    //             'service_user.service_id',
    //            'services.title',
    //            'services.status as services_status',
    //         )
    //         ->where(['users.status' => 1, 'users.approval' => 2, 'users.type' => 'consultant'])
    //         ->join('academic_qualifications', 'users.id', '=', 'academic_qualifications.user_id')
    //         ->join('experiences', 'users.id', '=', 'experiences.user_id')
    //         ->join('service_user', 'users.id', '=', 'service_user.user_id')
    //         ->join('services', 'service_user.service_id', '=', 'services.id')
    //         ->latest('education_level','title','institute_name')
    //         ->groupBy('users.id')
    //         ->get();

    //      return $consultant;
    //     if (!empty($consultant)) {
    //         $message = "Succesfully Data Shown";
    //         return $this->responseSuccess(200, true, $message, $consultant);
    //     } else {
    //         $message = "Invalid credentials";
    //         return $this->responseError(403, false, $message);
    //     }
    // }

    public function details($id)
    {
        $consultant = User::where('id', $id)->with('academics', 'services', 'experiances')->active()->get();
        //return $consultant;
        if (!empty($consultant)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $consultant);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }



// public function dashboard()
// {
//     $service = DB::table('services')->get();

//     $consultants_selected_fields = ['id', 'name', 'phone', 'email', 'address', 'code', 'profile_image', 'gender', 'rates', 'years_of_experience', 'schedule'];

//     $consultant = User::with(
//         [
//             'experianceLatest:user_id,institute_name',
//             'academicLatest:user_id,education_level',
//             'serviceLatest',
//         ]

//     )->select($consultants_selected_fields)->active()->get();

//     if ($consultant && $service) {
//         $data = [
//             'topRated' => $consultant,
//             'active' => $consultant,
//             'service' => $service
//         ];
//     }
//     if (!empty($data)) {
//         $message = "Succesfully Data Shown";
//         return $this->responseSuccess(200, true, $message, $data);
//     } else {
//         $message = "Invalid credentials";
//         return $this->responseError(403, false, $message);
//     }
// }
}

// $consultant = User::with(
//            ['services' => function ($query) {
//     $query->select('id', 'title');
// },
//     'academics' => function ($query) {
//         $query->select('id', 'education_level');
//     },
//     'experiances' => function ($query) {
//         $query->select('id', 'institute_name');
//     }]
// )->get();

// return $consultant;

// $consultant = User::with('academics', 'services', 'experiances')->active();
// $consultant = User::with('academics')->where('id', 2)->first();
// $consultant = User::query()->with(
//     [
//         'services' => function ($query) use ($services_selected_fields) {
//             $query->select($services_selected_fields)->get();
//         },
//     ],


// // [
// //     'academics' => function ($query) use ($academics_selected_fields) {
// //         $query->select($academics_selected_fields);
// //     }
// // ],

// // [
// //     'experiances' => function ($query) use ($experiences_selected_fields) {
// //         $query->select($experiences_selected_fields);
// //     }
// // ],
// )
//     ->with(
//         [

//             'experiances' => function ($query) use ($experiences_selected_fields) {
//                 $query->select($experiences_selected_fields);
//             }
//         ]
//     )
//     ->with(
//         [

//             'academics' => function ($query) use ($academics_selected_fields) {
//                 $query->select($academics_selected_fields);
//             }
//         ]
//     );

// ->whereHas('services', function ($q) use ($services_selected_fields) {
//     $q->select($services_selected_fields);
// })
// ->whereHas('academics', function ($q) use ($academics_selected_fields) {
//     $q->select($academics_selected_fields);
// })
// return $consultant;

// $data = [];
// $params = $request->all();
// // return $params;
// $consultants_selected_fields = ['name', 'phone', 'email', 'address', 'code', 'profile_image', 'gender', 'rates', 'years_of_experience', 'schedule'];
// $academics_selected_fields = ['education_level'];
// $services_selected_fields = ['title'];
// $experiences_selected_fields = ['institute_name'];
// foreach ($params as $key => $param) {

//     if ($key === 'services') {
//         $consultant = $consultant->whereHas('services', function ($q) use ($param) {
//             $q->where('services.id', $param);
//         });
//         // $data[$key] = $consultant->get();

//     } elseif ($key === 'search') {

//         $userSearchFields = ['name', 'phone', 'email', 'address', 'code', 'schedule', 'years_of_experience'];
//         $servicesSearchFields = ['title'];
//         $academicSearchFields = ['education_level'];

//         $consultant = $consultant->where(function ($query) use ($userSearchFields, $param) {
//             foreach ($userSearchFields as $userSearchField) {
//                 $query->orWhere($userSearchField, 'like', '%' . $param . '%');
//             }
//         })
//             ->orWhereHas('services', function ($query) use ($servicesSearchFields, $param) {
//                 foreach ($servicesSearchFields as $serviceSearchField) {
//                     $query->where($serviceSearchField, 'like', '%' . $param . '%');
//                 }
//             })
//             ->orWhereHas('academics', function ($query) use ($academicSearchFields, $param) {
//                 foreach ($academicSearchFields as $academicSearchField) {
//                     $query->where($academicSearchField, 'like', '%' . $param . '%');
//                 }
//             });

//         // $data[$key] = $consultant->get();
//     } elseif ($key === 'rating') {
//         $consultant = $consultant->orderBy('users.rates', $param);
//     }
// }

// if (isset($params['count'])) {
//     $data['data'] = $consultant->limit($params['count'])->get()->toArray();
// } else {
//     $data['data'] = $consultant->get()->toArray();
// }

// $consultant = $consultant['data'];
// $consultant = $data['data']->map(function)

//   return $data['data'];

// $service = Service::all();
// $user = User::active()->get();

// if ($user) {
//     $data = [
//         'topRated' => $user,
//         'active' => $user,
//         'service' => $service
//     ];
// }
// if (!empty($data)) {
//     $message = "Succesfully Data Shown";
//     return $this->responseSuccess(200, true, $message, $data);
// } else {
//     $message = "Invalid credentials";
//     return $this->responseError(403, false, $message);
// }

// }








//     public function active()
//     {
//         $user = User::active()->get();

//         if ($user) {
//             $data = [
//                 'active' => $user,
//             ];
//         }
//         if (!empty($data)) {
//             $message = "Succesfully Data Shown";
//             return $this->responseSuccess(200, true, $message, $data);
//         } else {
//             $message = "Invalid credentials";
//             return $this->responseError(403, false, $message);
//         }
//     }

//     public function serviceWiseConsultantList($id)
//     {
//         $user = Service::with('consultants')->where('id', $id)->whereHas('consultants', function ($query) {
//             $query->active();
//         })->first();

//         if (!empty($user)) {
//             $message = "Succesfully Data Shown";
//             return $this->responseSuccess(200, true, $message, $user);
//         } else {
//             $message = "Invalid credentials";
//             return $this->responseError(403, false, $message);
//         }
//     }
// }
