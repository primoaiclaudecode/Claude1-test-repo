<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    /**
     * Types
     */
    const STATUS_TYPE_UNIT_MANAGER = 'unit_manager';

    /**
     * Statuses
     */
	const STATUS_UNIT_ACTIVE = 1;
	const STATUS_UNIT_ON_HOLD = 2;
	const STATUS_UNIT_INACTIVE = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'type'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'type'
    ];

    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;

}
