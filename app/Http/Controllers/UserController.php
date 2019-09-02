<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Address;
use App\Designer;
use App\Schedule;
use Carbon\CarbonPeriod;


class UserController extends Controller
{

    // ------------------------ ADMINS -----------------

    public function LoginAdmin()
    {
        $Credentials = ['email' => request('email'), 'password' => request('password'), 'roleId' => 1];
        $authenticate = Auth::attempt($Credentials);
        if ($authenticate) {
            $accesstoken = request('accesstoken');
            $user = Auth::user();

            $success['token'] = $user->createToken($accesstoken, ['Admin'])->accessToken;

            return response()->json(["message" => $success['token'] ? $success['token'] : "Internal Server Error"], $success['token'] ? 200 : 500);
        } else {
            return response()->json(["error" => 'Email or Password is Invalid / Account not Activated'], 412);
        }
    }
    public function RegisterAdmin(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'mobile_number' => 'required|digits:11|numeric',
            'street' => 'required|max:100',
            'country' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|max:4',
            'company' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }

        $input['password'] = bcrypt($input['password']);
        $input['roleId'] = 1;
        $user = user::create($input);
        $input['userId'] = $user->id;
        $address = address::create($input);

        return response()->json(["message" => $address && $user ? "Registered succesfully" : "Internal Server Error"], $address && $user ? 200 : 500);
    }

    // ------------------------ CLIENTS -----------------

    public function Login()
    {
        $Credentials = ['email' => request('email'), 'password' => request('password'), 'roleId' => 2];
        $authenticate = Auth::attempt($Credentials);
        if ($authenticate) {
            $accesstoken = request('accesstoken');
            $user = Auth::user();

            $success['token'] =$user->createToken($accesstoken, ['Clients'])->accessToken;

            return response()->json(["message" => $success['token'] ? $success['token'] : "Internal Server Error"], $success['token'] ? 200 : 500);
        } else {
            return response()->json(["error" => 'Email or Password is Invalid / Account not Activated'], 412);
        }
    }

    public function Register(Request $request)
    {

        $input = $request->all();
        $validator = Validator::make($input, [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            // 'street' => 'required|max:100',
            'country' => 'required|string',
            // 'city' => 'required|string',
            // 'zip_code' => 'required|max:4',
            'company' => 'required|string',
            'industry' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }

        $input['password'] = bcrypt($input['password']);
        $input['roleId'] = 2;
        $user = user::create($input);
        $input['userId'] = $user->id;
        $address = address::create($input);

        return response()->json(["message" => $address && $user ? "Registered succesfully" : "Internal Server Error"], $address && $user ? 200 : 500);
    }

    // ------------------------ DESIGNERS -----------------

    // note* type == 1 ? "Full Time" : "Project Based"
    // note* Schedule status == 1 ? "Available" : "unavailable"
    public function Create(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'mobile_number' => 'required|digits:11|numeric',
            'street' => 'required|max:100',
            'country' => 'required|string',
            'city' => 'required|string',
            'zip_code' => 'required|max:4',
            'company' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'type' => 'required|boolean',
            'start_date' => 'required|string',
            'end_date' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }

        $period = new CarbonPeriod($input['start_date'], '1 day', $input['end_date']);

        $input['password'] = bcrypt($input['first_name']);
        $input['roleId'] = 3;
        $user = user::create($input);
        $input['userId'] = $user->id;
        $address = address::create($input);
        $designer = designer::create($input);
        $schedule = collect($period)->each(function ($value) use ($designer) {
            $input['schedule'] = $value;
            $input['status'] = 1;
            $input['designerId'] = $designer->id;
            schedule::create($input);
        });
        return response()->json(["message" => $address && $user ? "Added a designer succesfully" : "Internal Server Error"], $address && $user ? 200 : 500);
    }

    public function CreateSchedule(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'designerId' => 'required|numeric',
            'schedule' => 'required|array'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }
        $input2 = $input['designId'];
        $designer = collect($input['schedule'])->each(function ($value) use ($input2) {
            $input['schedule'] = $value;
            $input['status'] = 1;
            $input['designerId'] = $input2;
            designer::create($input);
        });
        return response()->json(["message" => $designer ? "Added a schedule succesfully" : "Internal Server Error"], $designer ? 200 : 500);
    }

    public function Read()
    {
        $designer = designer::with('user')->get();
        return response()->json(["message" => $designer ? ["designer" => $designer] : "Internal Server Error"], $designer ? 200 : 500);
    }

    public function UpdateShow($id)
    {
        $designer = designer::with(['schedules', 'user'])->find($id);
        return response()->json(["message" => $designer? $designer : "Internal Server Error"], $designer ? 200 : 500);
    }

    // note* type == 1 ? "Full Time" : "Project Based"
    public function Update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'first_name' => 'string|max:50',
            'last_name' => 'string|max:50',
            'mobile_number' => 'digits:11|numeric',
            'street' => 'max:100',
            'country' => 'string',
            'city' => 'string',
            'zip_code' => 'max:4',
            'company' => 'string',
            'email' => 'email|unique:users,email',
            'type' => 'boolean',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }

        $user                 = user::find($id);
        $user->first_name     = $input['first_name'];
        $user->last_name      = $input['last_name'];
        $user->mobile_number  = $input['mobile_number'];
        $user->email          = $input['email'];
        $user->save();

        $address            = address::where('userId', $id)->first();
        $address->street    = $input['street'];
        $address->country   = $input['country'];
        $address->city      = $input['city'];
        $address->zip_code  = $input['zip_code'];
        $address->save();

        $designer          = designer::where('userId', $id)->first();
        $designer->type    = $input['type'];
        $designer->save();

        return response()->json(["message" => $address && $user && $designer ? "Edited the information designer succesfully" : "Internal Server Error"], $address && $user && $designer ? 200 : 500);
    }

    public function UpdateSchedule(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'schedule' => 'date',
            'status' => 'boolean'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }

        $schedules              = schedule::find($id);
        $schedules->schedule    = $input['schedule'];
        $schedules->status      = $input['status'];
        $schedules->save();

        return response()->json(["message" => $schedules ? "Edited the schedule succesfully" : "Internal Server Error"], $schedules ? 200 : 500);
    }

   
}
