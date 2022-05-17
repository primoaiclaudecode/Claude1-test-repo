<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActiveUser extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'session_token', 'ip_address', 'expired_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;

	/**
	 * Get the user.
	 */
	public function user()
	{
		return $this->belongsTo('App\User');
	}
    
}
