<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Booking;
use App\Schedule;
use App\Designer;
use App\Answer;
use App\Traits\PaymentTrait;

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
        $validator = Validator::make($request, [
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
        if (booking::where([['YEAR(created_at)', '2019'], ['MONTH(created_at)', '09']])->orWhere([['YEAR(created_at)', '2019'], ['MONTH(created_at)', '10']])->count() >= 50)
            return response()->json(["message" => "Booking exceeded"], 412);

        $request->status = "Unpaid";
        $bookings = booking::create($request);
        $request->userId = $request->clientId;
        $answer = answer::create($request);


        return response()->json(["message" =>  $bookings && $answer ? "Added a booking succesfully" : "Internal Server Error"], $bookings && $answer ? 200 : 500);
    }

    public function Read()
    {
        $bookings = booking::with('client')->get();
        return response()->json(["message" => $bookings ? $bookings : "Internal Server Error"], $bookings ? 200 : 500);
    }

    public function UpdateShow($id)
    {
        $bookings = booking::find($id);
        $schedule = schedule::whereBetween('schedule', array($bookings->start_date, $bookings->end_date))->with('designer.user')->get();
        foreach ($schedule as $sku) {
            if ($sku->status == 0) {
                $schedule = $schedule->filter(function ($item) use ($sku) {
                    return $item->designerId != $sku->designerId;
                })->values();
        }}

        return response()->json(["message" => $schedule ?  ["details" => $bookings, "designers" => $schedule->unique('designerId')] : "Internal Server Error"], $schedule ? 200 : 500);
    }


    // LACK EMAIL FEATURE
    public function Update(Request $request, $id)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'start_date' => 'date',
            'end_date' => 'date',
            'designerid' => 'numeric',
            'action_type' => 'string',
            'status' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 412);
        }
        
        $bookings = booking::find($id);
        $bookings->start_date = $input['start_date'];
        $bookings->end_date = $input['end_date'];
        $bookings->designerId = $input['designerid'];
        $bookings->status = $input['status'];
        $bookings->save();

        if($input['status'] == 'assign')
        {
            $schedule = schedule::whereBetween('schedule', array($input['start_date'], $input['end_date']))
            ->where('designerId', $input['designerid'])
            ->update([ "status" => 0 ]);    
        }
        else if($input['status'] == 'reassign')
        {
            $schedule = schedule::whereBetween('schedule', array($bookings->start_date, $bookings->end_date))
            ->where('designerId', $bookings->designerId)
            ->update([ "status" => 1 ]);

            $schedule = schedule::whereBetween('schedule', array($input['start_date'], $input['end_date']))
            ->where('designerId', $input['designerid'])
            ->update([ "status" => 0 ]);    
        }
        else if($input['status'] == 'extending')
        {
            $payment = PaymentTrait::createPayment("100", $this->api_context);

            $schedule = schedule::whereBetween('schedule', array($input['start_date'], $input['end_date']))
            ->where('designerId', $bookings->designerId)
            ->update([ "status" => 0 ]);
        }

        return response()->json(["message" => $schedule && $bookings ?  "Booking updated successfully"  : "Internal Server Error"], $schedule && $bookings ? 200 : 500);
    }

}
