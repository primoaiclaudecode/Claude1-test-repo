<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactType extends Model
{
	/**
	 * Contact types
	 */
	const CONTACT_TYPE_MEETING = 1;
	const CONTACT_TYPE_EMAIL = 2;
	const CONTACT_TYPE_PHONE = 3;
	const CONTACT_TYPE_OTHER = 4;
	const CONTACT_TYPE_VIDEO_CONFERENCE = 5;
	
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
    	'position'
    ];

    /**
     * Disable timestamps
     *
     * @var bool $timestamps
     */
    public $timestamps = false;

}
