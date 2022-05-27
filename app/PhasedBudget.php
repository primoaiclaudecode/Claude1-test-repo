<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhasedBudget extends Model
{
    public $table = "trading_account";
    protected $primaryKey = 'trading_account_id';
    public $timestamps = false;

    /**
     * Get contract type.
     */
    public function contractType()
    {
        return $this->belongsTo('App\ContractType');
    }

    /**
     * Get contract type.
     */
    public function unit()
    {
        return $this->belongsTo('App\Unit');
    }
}
