<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StockControl extends Model
{
    public $table = "stock_control";
    protected $primaryKey = 'stock_control_id';
    public $timestamps = false;
}
