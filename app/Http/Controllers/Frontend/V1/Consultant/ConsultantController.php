<?php

namespace App\Http\Controllers\Frontend\V1\Consultant;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

class ConsultantController extends Controller
{
    use ResponseTrait;
    public function consultantList(Request $request)
    {
        $params = $request->all();
        $consultant = User::with('academics', 'services')->active();
        $data = [];

        foreach ($params as $key => $param) {

            if ($key === 'services') {
                $consultant = $consultant->whereHas('services', function ($q) use ($param) {
                    $q->where('services.id', $param);
                });
                $data[$key] = $consultant->get();
               // return $data['list'] = $consultant->get();
            }


            if ($key === 'search') {
                $userSearchFields = ['name', 'phone', 'email', 'address', 'code', 'schedule', 'years_of_experience'];
                $servicesSearchFields = ['title'];
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
                    ->orWhereHas('academics', function ($query) use ($academicSearchFields, $param) {
                        foreach ($academicSearchFields as $academicSearchField) {
                            $query->where($academicSearchField, 'like', '%' . $param . '%');
                        }
                    });

                $data[$key] = $consultant->get();
            }



            if ($key === 'rating') {
                $consultant = $consultant->orderBy('users.rates', $param);
            }
        }

        if (isset($params['count'])) {
            $data['list'] = $consultant->limit($params['count'])->get();
        } else {
            $data['list'] = $consultant->get();
        }

        return $data;

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
    }

    public function topRated(Request $request)
    {
        $route = Route::current();
        //return $route;
        // $topRated = $route->parameter('topRated');
        $params = [
            'topRated' => $route->parameter('topRated'),
            'active' => $route->parameter('active')
        ];
        //  return $params;
        //  return $request->exists($params);
        // $user = User::query();
        // return $user;
        if ($request->exists($params)) {
            $user = User::active()->get();
        }
        //  return $user;
        if ($user) {
            $data = [
                'topRated' => $user,
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

    public function active()
    {
        $user = User::active()->get();

        if ($user) {
            $data = [
                'active' => $user,
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

    public function serviceWiseConsultantList($id)
    {
        $user = Service::with('consultants')->where('id', $id)->whereHas('consultants', function ($query) {
            $query->active();
        })->first();

        if (!empty($user)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $user);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

}
