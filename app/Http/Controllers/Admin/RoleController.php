<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Traits\ResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Spatie\Permission\Models\Permission;


class RoleController extends Controller
{
    use ResponseTrait;

    public function index()
    {
        $data = Role::all();

        $message = "Succesfully Data Shown";
        return $this->responseSuccess(200, true, $message, $data);
    }
    public function store(Request $request)
    {
        $role = Role::create(['name' => $request->role]);
        $permissionsIds = [];
        foreach ($request->permissions as $permission) {
            array_push($permissionsIds, Permission::create(['module_name' => $request->module_name, 'name' => $permission])->id);
        }
        $role->syncPermissions($permissionsIds);
        return $permissionsIds;
    }

    public function retrieve($id)
    {
        DB::beginTransaction();
        try {
            $data = Role::findOrFail($id);
            $message = "Data Found";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            DB::rollBack();
        }

    }

    public function setRole($user, $role)
    {
        $user = User::where('id', $user)->first();
        $role = Role::where('id', $role)->first();

        $user->assignRole($role);
        return $user;
    }

    public function update(Request $request, $id)
    {

        $input = Role::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($input) {
                $input->name = $request['name'];
                $input->guard_name = $request['guard_name'];

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

            $users = DB::table('roles')->whereIn('id', $request->all());
            $users->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
