<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NetExt extends Model
{    
    public $table = "nominal_codes";
    protected $primaryKey = 'net_ext_ID';
    public $timestamps = false;
}
