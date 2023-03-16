<?php

namespace App\Http\Controllers;

use App\Models\Experience;
use Illuminate\Http\Request;
use App\Traits\ResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ExperienceRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\Response;


class ExperienceController extends Controller
{
    use ResponseTrait;
  //  $facilitiesArr = Facilities::orderBy('title', 'asc')->pluck('title', 'id')->toArray();

  public function experience($id)
  {
      $user = Experience::with('user')->where('id', $id)->first();
      return $user;

  }

    public function index()
    {

        $data = Experience::all();
        if (!empty($data)) {
            $message = "Succesfully Data Shown";
            return $this->responseSuccess(200, true, $message, $data);
        } else {
            $message = "Invalid credentials";
            return $this->responseError(403, false, $message);
        }
    }

    public function store(ExperienceRequest $request)
    {

        DB::beginTransaction();
        try {
            $data = Experience::create([
                'Institute_name' => $request->Institute_name,
                'address' => $request->address,
                'designation' => $request->designation,
                'department' => $request->department,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'current_working' => $request->current_working,
                'user_id' => $request->user_id
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
            $data = Experience::findOrFail($id);
            $message = "Data Found";
            DB::commit();
            return $this->responseSuccess(200, true, $message, $data);
        } catch (QueryException $e) {
            DB::rollBack();
        }
    }

    public function update(ExperienceRequest $request, $id)
    {

        $input = Experience::findOrFail($id);
        DB::beginTransaction();
        try {
            if ($input) {
                $input->Institute_name = $request['Institute_name'];
                $input->address = $request['address'];
                $input->designation = $request['designation'];
                $input->department = $request['department'];
                $input->start_date = $request['start_date'];
                $input->end_date = $request['end_date'];
                $input->current_working = $request['current_working'];

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

            $users = DB::table('experiences')->whereIn('id', $request->all());
            $users->delete();
            $message = "Deleted Succesfully";
            DB::commit();
            return $this->responseSuccess(200, true, $message, []);

        } catch (QueryException $e) {
            DB::rollBack();
        }
    }
}
