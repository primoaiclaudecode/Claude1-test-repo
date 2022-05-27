<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Directory extends Model
{
    public $table = "directories";
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'modified_at';
}
