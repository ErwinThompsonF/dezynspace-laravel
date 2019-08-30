<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
/** Paypal Details classes **/
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Transaction;
use PayPal\Exception\PayPalConnectionException;
use App\Booking;
use App\Traits\PaymentTrait;

class PaypalController extends Controller
{
        private $api_context;

        public function __construct()
        {
            $this->api_context = new ApiContext(
                new OAuthTokenCredential(config('paypal.client_id'), config('paypal.secret'))
            );
            $this->api_context->setConfig(config('paypal.settings'));
        }

        public function createPayment(Request $request)
        {
            $payment = PaymentTrait::createPayment($request->amount, $this->api_context);
            return response()->json($payment);
        }
    
        public function confirmPayment(Request $request,$id)
        {
            if (empty($request->query('paymentId')) || empty($request->query('PayerID')) || empty($request->query('token')))
                return response()->json('Payment was not successful.');
            $payment = Payment::get($request->query('paymentId'), $this->api_context);
            $execution = new PaymentExecution();
            $execution->setPayerId($request->query('PayerID'));
            $result = $payment->execute($execution, $this->api_context);
            if ($result->getState() != 'approved')
                return response()->json('Payment was not successful.');

            $booking = booking::find($id);
            $booking->paypal_id = $result->getId();
            $booking->payment_status = "paid";
            $booking->save();
            return response()->json('Payment made successfully');
        }
}
