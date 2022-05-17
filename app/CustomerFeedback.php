<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CustomerFeedback extends Model
{
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'unit_id', 'contact_type_id', 'feedback_type_id', 'contact_date', 'notes' 
	];
	
	/**
	 * @var bool
	 */
	public $timestamps = false;

	/**
	 * Get formatted contact date
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function getContactDateAttribute($value)
	{
		return Carbon::parse($value)->format('d-m-Y H:i');
	}

	/**
	 * Get contact type.
	 */
	public function contactType()
	{
		return $this->belongsTo('App\ContactType');
	}

	/**
	 * Get feedback type.
	 */
	public function feedbackType()
	{
		return $this->belongsTo('App\FeedbackType');
	}
}
