<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{

    use ResponseTrait;

    public function index()
    {
        $data = Permission::all();

        $message = "Succesfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
    public function store(Request $request)
    {

        DB::beginTransaction();
        try {
            $data = Permission::create([
                'name' => $request->name,
                'guard_name' => $request->guard_name,
                'module_name' => $request->module_name,

            ]);
            DB::commit();
            $message = "Data Inserted Successfull";
            return $this->responseSuccess(200, true, $message, $data);

        } catch (QueryException $e) {
            DB::rollBack();
            return $this->responseError(Response::HTTP_INTERNAL_SERVER_ERROR, false, $e->getMessage());
        }
    }
    public function retrieve($id)
    {
        DB::beginTransaction();
        try {
            $data = Permission::findOrFail($id);
            $message = "Data Found";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            DB::rollBack();
        }

    }

    public function setPermission($user, $permission)
    {
        $user = User::where('id', $user)->first();
        $permission = Permission::where('id', $permission)->first();

        $user->assignPermission($permission);
        return $user;
    }

    public function update(Request $request, $id)
    {
        $input = Permission::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($input) {
                $input->name = $request['name'];
                $input->guard_name = $request['guard_name'];
                $input->module_name = $request['module_name'];

                $input->save();
                $message = "Updated Succesfully";
                DB::commit();
                return $this->responseSuccess(200, true, $message, $input);
            } else {
                $message = "No Data Found";
                return $this->responseError(404, false, $message);
            }
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $users = DB::table('permissions')->whereIn('id', $request->all());
            $users->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }


}
