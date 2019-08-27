<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = [
        'questionId','userId','answer'
    ];

    public function answer()
    {
        return $this->belongsTo('App\Question', 'questionId');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'userId');
    }
}
