<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The fillable fields
     */
    protected $fillable = ['key', 'value'];
}
