<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;
}
