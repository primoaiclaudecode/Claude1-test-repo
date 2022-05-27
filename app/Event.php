<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ip_address',
        'action',
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

    /**
     * Track action
     *
     * @param string $action
     */
    public static function trackAction($action)
    {
        self::create(
            [
                'user_id'    => auth()->user()->user_id,
                'ip_address' => ip2long(request()->ip()),
                'action'     => $action,
            ]
        );
    }
}
