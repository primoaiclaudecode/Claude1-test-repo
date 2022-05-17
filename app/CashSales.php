<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashSales extends Model
{
    public $table = "cash_sales";
    protected $primaryKey = 'cash_sales_id';
    public $timestamps = false;
}
