<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'street','country', 'city', 'zip_code','company','industry','userId',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'userId');
    }
}
