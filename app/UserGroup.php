<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
    /**
     * User groups
     */
    const USER_GROUP_UNIT       = 1;
    const USER_GROUP_OPERATIONS = 2;
    const USER_GROUP_HQ         = 3;
    const USER_GROUP_ADMIN      = 4;
    const USER_GROUP_SU         = 5;
    const USER_GROUP_MANAGEMENT = 6;
    const LIMITED_ACCESS        = 7;
    protected $primaryKey = 'user_group_id';
}
