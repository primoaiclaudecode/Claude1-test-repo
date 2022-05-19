<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'suppliers_id';
    public $timestamps = false;

	/**
	 * Get currency.
	 */
	public function currency()
	{
		return $this->belongsTo('App\Currency');
	}
}
