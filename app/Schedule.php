<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'schedule','status','designerId'
    ];

    public function designer()
    {
        return $this->belongsTo('App\Designer', 'designerId');
    }
}
