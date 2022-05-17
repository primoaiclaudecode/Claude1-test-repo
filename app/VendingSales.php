<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VendingSales extends Model
{
    public $table = "vending_sales";
    protected $primaryKey = 'vending_sales_id';
    public $timestamps = false;
}
