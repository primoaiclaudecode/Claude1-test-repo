<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vending extends Model
{
	public $table = "vend_management";
    protected $primaryKey = 'vend_management_id';
    public $timestamps = false;

    /**
     * Get the unit names associated with vendings.
     */
    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }
}
