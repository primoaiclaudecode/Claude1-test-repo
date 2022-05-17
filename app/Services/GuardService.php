<?php

namespace App\Services;

use App\User;
use App\UserGroup;

class GuardService
{
    /**
     * Check user group access
     *
     * @param integer $userId
     * @param array $allowedGroups
     *
     * @return boolean
     */
    public static function checkGroupAccess($userId, $allowedGroups)
    {
        return in_array(self::getUserGroupId($userId), $allowedGroups);
    }

	/**
	 * Get user group name
	 *
	 * @param integer $userId
	 *
	 * @return string
	 */
	public static function getUserGroupName($userId)
	{
		return UserGroup::findOrFail(self::getUserGroupId($userId))->user_group_name;
	}

	/**
	 * Get user group
	 *
	 * @param integer $user
	 *
	 * @return integer
	 */
	private static function getUserGroupId($userId)
	{
		$userGroupMember = User::findOrFail($userId)->user_group_member;

		$groupMember = explode(',', $userGroupMember);

		return max($groupMember);
	}

}
