<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;



class ApplicationController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:SearchApplication', ['only' => ['index']]);
    }

    public function index(Request $request){

        $paginate=$request->session()->get('search_length');

        if($paginate==null){
            $paginate=10;
        }

        $user_id = Auth::user()->id;
        $user_admin = DB::table('users')->where('id', $user_id)->first();
        $user_layer = DB::table('user_layer')->where('user_id', $user_id)->first();
        $notification=array();

        if(!empty($request->all()))
        {
            $data=$request->all();
            $organaization      =!empty($data['organaization'])?$data['organaization']:null;
            $applicant_mobile   =!empty($data['applicant_mobile'])?$data['applicant_mobile']:null;
            $app_id             =!empty($data['app_id'])?$data['app_id']:null;
            $trac_id            =!empty($data['trac_id'])?$data['trac_id']:null;
            $dbid_id            =!empty($data['dbid_id'])?$data['dbid_id']:null;
            if(empty($organaization) && empty($applicant_mobile) && empty($app_id) && empty($trac_id) && empty($dbid_id)){
                $companyinfoes=array();

            }else{
                $companyinfoes = DB::table('companyinfo')
                ->leftjoin('company_applicant', 'company_applicant.application_id', '=', 'companyinfo.application_id')
                ->when($app_id , function($query) use ($app_id ){
                $query->where('companyinfo.application_id',  'like', '%' . $app_id . '%') ;
                })
                ->when($trac_id , function($query) use ($trac_id ){
                $query->where('companyinfo.tracking_no',  'like', '%' . $trac_id . '%') ;
                })
                ->when($organaization , function($query) use ($organaization ){
                        $query->where('companyinfo.company_name_bangla',  'like', '%' . $organaization . '%') ;
                        $query->orWhere('companyinfo.company_name',  'like', '%' . $organaization . '%') ;
                })
                ->when($applicant_mobile , function($query) use ($applicant_mobile ){
                        $query->where('company_applicant.applicant_phone',  'like', '%' . $applicant_mobile . '%') ;
                })
                ->when($dbid_id , function($query) use ($dbid_id ){
                        $query->where('companyinfo.ubid',  'like', '%' . $dbid_id . '%') ;
                })
                ->select('companyinfo.*','companyinfo.id as company_info_id', 'company_applicant.*', 'company_applicant.applicant_designation_id as form_designation')
                ->orderBy('companyinfo.created_at', 'DESC')->paginate($paginate)->withQueryString();
                // dd($companyinfoes);
                foreach($companyinfoes as $key=> $info){

                    $companyinfoes[$key]->comments=DB::table('application_comments')->where('company_info_id', $info->company_info_id)->latest()->first();

                    $companyinfoes[$key]->desk=DB::table('application_rule_log')
                                ->orderBy('id', 'DESC')
                                ->where('log_application_id',$info->company_info_id)
                                ->leftjoin('users', 'users.id', '=', 'application_rule_log.sender_user_id')
                                ->leftjoin('users as sender', 'sender.id', '=', 'application_rule_log.log_user_id')
                                ->leftjoin('department', 'department.id', '=', 'users.department_id')
                                ->leftjoin('department as senderdep', 'senderdep.id', '=', 'sender.department_id')
                                ->leftjoin('designation', 'designation.id', '=', 'users.designation_id')
                                ->leftjoin('designation as senderdes', 'senderdes.id', '=', 'sender.designation_id')
                                ->select('application_rule_log.*', 'users.name','sender.name as sendername', 'department.department_name_bn', 'senderdep.department_name_bn as departmentname', 'designation.designation_bn', 'senderdes.designation_bn as designationname')->latest()->first();
                }
            }
        }else{
            $companyinfoes=array();
        }

        return view('admin.applications.application', compact('companyinfoes', 'user_layer', 'user_admin', 'notification'));
    }


}
