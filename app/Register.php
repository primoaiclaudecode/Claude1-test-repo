<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Register extends Model
{
	public $table = "reg_management";
    protected $primaryKey = 'reg_management_id';
    public $timestamps = false;

    /**
     * Get the unit names associated with registers.
     */
    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }
}
