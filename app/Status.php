<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'status';

    /**
     * Fillable fields
     *
     * @var array
     */
    protected $fillable = ['house', 'garage'];
}
