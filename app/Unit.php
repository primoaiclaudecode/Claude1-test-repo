<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $primaryKey = 'unit_id';
    public $timestamps = false;
    
   public static function getUnitList()
   {
      $arrCredit = Unit::orderBy('unit_name')->pluck('unit_name', 'unit_id');
      return ($arrCredit ? $arrCredit : []);
   }

	/**
	 * Get status.
	 */
	public function status()
	{
		return $this->belongsTo('App\Status');
	}
	
}
