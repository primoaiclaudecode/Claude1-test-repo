<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabourHour extends Model
{
    public $timestamps = false;

    /**
     * Get the unit names associated with registers.
     */
    public function labourType()
    {
        return $this->belongsTo('App\LabourType');
    }
}
