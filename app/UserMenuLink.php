<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserMenuLink extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'link',
        'position',
        'link_id',
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
    ];
    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;
}
