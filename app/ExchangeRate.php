<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'domestic_currency_id',
        'foreign_currency_id',
        'exchange_rate',
        'date',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at',
    ];
}
