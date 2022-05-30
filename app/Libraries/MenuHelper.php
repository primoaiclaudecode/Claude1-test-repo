<?php

namespace App\Libraries;

use App\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MenuHelper
{
    public static function getFavouritesList()
    {
        $linkTitles = Menu::getAll()->pluck('name', 'link')->toArray();

        foreach (self::getRootDirNames() as $dirName) {
            $linkTitles["/files/{$dirName->id}"] = $dirName->dir_file_name;
        }

        return $linkTitles;
    }

    public static function getRootDirNames()
    {
        $userId = auth()->user()->user_id;

        $groupId = DB::table('users')
            ->select('user_group_member')
            ->where('user_id', '=', $userId)
            ->value('user_group_member');

        if (strpos($groupId, ',') !== false) {
            $groupIdExplode = explode(',', $groupId);
            $checkGroupPermission = '';

            foreach ($groupIdExplode as $grpId) {
                $checkGroupPermission .= 'FIND_IN_SET(' . $grpId . ',group_id_read) > 0 OR ';
            }

            $checkGroupPermission = rtrim($checkGroupPermission, 'OR ');
        } else {
            if ($groupId == '') {
                $groupId = 0;
            }

            $checkGroupPermission = 'FIND_IN_SET(' . $groupId . ',group_id_read) > 0';
        }

        if (Gate::allows('su-user-group')) {
            $files = \DB::select("SELECT id, dir_file_name FROM file_system where parent_dir_id = 0 AND is_dir = 1 ORDER BY dir_file_name");
        } else {
            $files = \DB::select("SELECT id, dir_file_name FROM file_system where parent_dir_id = 0 AND is_dir = 1 AND (FIND_IN_SET($userId,user_id_read) > 0 OR $checkGroupPermission) ORDER BY dir_file_name");
        }

        return $files;
    }

    public static function getMenuList(): Collection
    {
        return Menu::getAll()->groupBy('section');
    }
}