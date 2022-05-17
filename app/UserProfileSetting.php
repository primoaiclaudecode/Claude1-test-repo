<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProfileSetting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'show_sidebar'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;
}
