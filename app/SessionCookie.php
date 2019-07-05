<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SessionCookie extends Model
{
    use SoftDeletes;

    protected $table = "session_cookies";

    protected $fillable = ['value', 'expires'];

    protected $dates = ['expires'];

    /**
     * Returns true if the token is expired
     *
     * @return boolean
     */
    public function isExpired()
    {
        return $this->expires < Carbon::now();
    }

    /**
     * Returns true if the token is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return !$this->isExpired();
    }
}
