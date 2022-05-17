<?php

/**
 * This 
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportHiddenColumn extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id', 'report_name', 'column_index'
	];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = [
		'created_at', 'updated_at'
	];

	/**
	 * Disable timestamps
	 *
	 * @var bool $timestamps
	 */
	public $timestamps = false;
	
}
