<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'plan','clientId','designerId''paypal_id','start_date','end_date','report_time','timezone','price','status','payment_status'
    ];

    public function client()
    {
        return $this->belongsTo('App\User', 'clientId');
    }
    public function designer()
    {
        return $this->belongsTo('App\Designer', 'designerId');
    }
}
