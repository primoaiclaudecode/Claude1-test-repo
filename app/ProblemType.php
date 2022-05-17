<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProblemType extends Model
{
	/**
	 * Problem types
	 */
	const SUPPLIERS = 1;
	const AUDIT = 2;
	const ENVIRONMENT = 3;
	const HEALTH_AND_SAFETY = 4;
	const HACCP = 5;
	const FEEDBACK = 6;
	const EQUIPMENT_MAINTENANCE = 7;
	const MAINTENANCE = 8;
	const GDPR = 9;
	
    public $timestamps = false;
}
