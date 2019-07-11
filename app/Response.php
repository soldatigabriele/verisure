<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    protected $fillable = ['status', 'body', 'header'];

    protected $casts = ['headers' => 'array'];

    /**
     * Return the associated request
     */
    public function request()
    {
        return $this->belongsTo('App\Request');
    }
}
