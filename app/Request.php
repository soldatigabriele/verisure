<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $guarded = []; 

    protected $casts = ["headers" => "array"];

    /**
     * Return the associated response
     */
    public function response()
    {
        return $this->hasOne('App\Response');
    }
}
