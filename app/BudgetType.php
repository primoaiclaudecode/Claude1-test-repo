<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BudgetType extends Model
{
	/**
	 * Contact types
	 */
	const BUDGET_TYPE_GP = 1;
	const BUDGET_TYPE_NET = 2;
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title'
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
