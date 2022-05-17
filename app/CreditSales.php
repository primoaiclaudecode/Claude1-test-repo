<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditSales extends Model
{
    public $table = "credit_sales";
    protected $primaryKey = 'credit_sales_id';
    public $timestamps = false;
}
