<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name', 'email', 'password','mobile_number','roleId',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public function address()
    {
        return $this->hasOne('App\Address', 'userId','id');
    }

    public function designer()
    {
        return $this->hasOne('App\Designer', 'userId','id');
    }

    public function booking()
    {
        return $this->hasMany('App\Booking', 'clientId','id');
    }
}
