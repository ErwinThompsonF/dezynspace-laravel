<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'bookingId','ans1','ans2','ans3','ans4','ans5','ans6','ans7','ans8','ans9','ans10','ans11'
    ];

    public function booking()
    {
        return $this->belongsTo('App\Booking', 'bookingId');
    }
}
