<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TradingAccount extends Model
{
    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;
    /**
     * Table name
     *
     * @var string
     */
    protected $table = 'trading_account';

    /**
     * Get contract type.
     */
    public function contractType()
    {
        return $this->belongsTo('App\ContractType');
    }
}
