<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxCode extends Model
{    
    public $table = "tax_codes";
    protected $primaryKey = 'tax_code_ID';
    public $timestamps = false;

	public function vendingSaleTaxCodes()
	{
		return $this->belongsToMany('App\NetExt', 'vending_sale_tax_codes');
	}
}
