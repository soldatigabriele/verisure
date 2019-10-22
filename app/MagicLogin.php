<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;

class MagicLogin extends Model
{
    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
