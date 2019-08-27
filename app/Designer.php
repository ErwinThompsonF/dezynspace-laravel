<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Designer extends Model
{
    protected $fillable = [
        'type','userId'
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'userId');
    }

    public function schedules()
    {
        return $this->hasMany('App\Schedule','designerId','id');
    }

    public function booking()
    {
        return $this->hasOne('App\Booking', 'designerId','id');
    }

}
