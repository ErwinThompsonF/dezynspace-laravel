<?php 
namespace App\Traits;

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

trait PaymentTrait

{
    public static function createPayment($amounts, $api_context)
    {
        $pay_amount = $amounts;
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');
        $item = new Item();
        $item->setName('Paypal Payment')->setCurrency('USD')->setQuantity(1)->setPrice($pay_amount);
        $itemList = new ItemList();
        $itemList->setItems(array($item));
        $amount = new Amount();
        $amount->setCurrency('USD')->setTotal($pay_amount);
        $transaction = new Transaction();
        $transaction->setAmount($amount)->setItemList($itemList)
        ->setDescription('Laravel Paypal Payment Tutorial');
        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl("http://localhost:8000/api/confirm")
        ->setCancelUrl(url()->current());
        $payment = new Payment();
        $payment->setIntent('Sale')->setPayer($payer)->setRedirectUrls($redirect_urls)
        ->setTransactions(array($transaction));
        try {
            $payment->create($api_context);
        } catch (PayPalConnectionException $ex){
            return 'Some error occur, sorry for inconvenient';
        } catch (Exception $ex) {
            return 'Some error occur, sorry for inconvenient';
        }
        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }
        if(isset($redirect_url)) {
            return $redirect_url;
        }
        return 'Unknown error occurred';
    }

    public function anotherMethod()
    {
        echo "Trait – anotherMethod() executed";
    }
}