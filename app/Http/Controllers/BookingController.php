<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Booking;
use App\Schedule;
use App\Designer;
use App\Answer;
use App\Traits\PaymentTrait;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Illuminate\Support\Facades\Log;
use DateTime;
use Carbon\Carbon;


class BookingController extends Controller
{

    private $api_context;

    public function __construct()
    {
        $this->api_context = new ApiContext(
            new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret'))
        );
        $this->api_context->setConfig(config('paypal.settings'));
    }


    public function create(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'plan' => 'required|string',
            'clientId' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'report_time' => 'required|string',
            'timezone' => 'required|string',
            'price' => 'required|numeric',
            'ans1' => 'required|string',
            'ans2' => 'required|string',
            'ans3' => 'required|string',
            'ans4' => 'required|string',
            'ans5' => 'required|string',
            'ans6' => 'required|string',
            'ans7' => 'required|string',
            'ans8' => 'required|string',
            'ans9' => 'required|string',
            'ans10' => 'required|string',
            'ans11' => 'required|string',

        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }
        if (booking::whereRaw('YEAR(created_at) = "2019" AND MONTH(created_at) = "09"')->orWhereRaw('YEAR(created_at) = "2019" AND MONTH(created_at) = "10"')->count() >= 50)
            return response()->json(["message" => "Booking exceeded"], 412);

        $input['status'] = "Unpaid";
        $bookings = booking::create($input);
        $input['bookingId'] = $bookings->id;
        $answer = answer::create($input);

        $payment = PaymentTrait::createPayment($input['price'], $this->api_context);

        return response()->json(["message" =>  $bookings && $answer ? $payment : "Internal Server Error"], $bookings && $answer ? 200 : 500);
    }

    public function Read()
    {
        $bookings = booking::with('client')->get();
        return response()->json(["message" => $bookings ? $bookings : "Internal Server Error"], $bookings ? 200 : 500);
    }

    public function UpdateShow($id)
    {
        $bookings = booking::with('client')->find($id);
        $schedule = schedule::selectRaw('count(schedule) as schedulecount,designerId,status')
                ->whereBetween('schedule', array($bookings->start_date, $bookings->end_date))
                ->with('designer.user')
                ->groupBy(['designerId', 'status'])        
                ->get();
                
        $from = Carbon::parse($bookings->start_date);
        $to = Carbon::parse($bookings->end_date);
        $days = $to->diffInDays($from);
        $schedule = $schedule->filter(function ($item) use($days) {
            return $item->schedulecount == $days + 1 && $item->status != 0;
        })->values();

        return response()->json(["message" => $schedule ?  ["details" => $bookings, "designers" => $schedule->unique('designerId')] : "Internal Server Error"], $schedule ? 200 : 500);
    }


    // LACK EMAIL FEATURE
    public function Update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'start_date' => 'date',
            'end_date' => 'date',
            'designerId' => 'numeric',
            'status' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }
        
        $bookings = booking::find($id);
       

        if($input['status'] == 'assign')
        {
            $schedule = schedule::whereBetween('schedule', array($input['start_date'], $input['end_date']))
            ->where('designerId', $input['designerId'])
            ->update([ "status" => 0 ]);    
        }
        else if($input['status'] == 'reassign')
        {
            $schedule = schedule::whereBetween('schedule', array($bookings->start_date, $bookings->end_date))
            ->where('designerId', $bookings->designerId)
            ->update([ "status" => 1 ]);

            $schedule = schedule::whereBetween('schedule', array($input['start_date'], $input['end_date']))
            ->where('designerId', $input['designerId'])
            ->update([ "status" => 0 ]);    
        }
        else if($input['status'] == 'extending')
        {
            $from = Carbon::parse($bookings->end_date);
            $to = Carbon::parse($input['end_date']);
            $payment = PaymentTrait::createPayment($bookings->price / $bookings->plan * $to->diffInWeekdays($from), $this->api_context);
            
            $schedule = schedule::whereBetween('schedule', array($input['start_date'], $input['end_date']))
            ->where('designerId', $bookings->designerId)
            ->update([ "status" => 0 ]);

            $bookings->start_date = $input['start_date'];
            $bookings->end_date = $input['end_date'];
            $bookings->designerId = $input['designerId'];
            $bookings->status = $input['status'];
            $bookings->save();

            return response()->json(["message" => $schedule && $bookings ?  $payment  : "Internal Server Error"], $schedule && $bookings ? 200 : 500);    
        }

        $bookings->start_date = $input['start_date'];
        $bookings->end_date = $input['end_date'];
        $bookings->designerId = $input['designerId'];
        $bookings->status = $input['status'];
        $bookings->save();

        return response()->json(["message" => $schedule && $bookings ?  "Booking updated successfully"  : "Internal Server Error"], $schedule && $bookings ? 200 : 500);
    }

}
