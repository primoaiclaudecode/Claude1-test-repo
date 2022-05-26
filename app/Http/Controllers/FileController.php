<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Gate;
use Zipper;
use Session;
use App\File;
use App\User;
use App\Region;
use ZipArchive;
use App\UserGroup;
use Carbon\Carbon;
use App\Directory;
use App\Permission;
use App\Http\Requests;
use FilesystemIterator;
use Illuminate\Http\Request;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Intervention\Image\ImageManagerStatic as Image;

class FileController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');

		$this->appUrl = config('app.url');

		$this->isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index($dirId = 0)
	{
		// Check permission
		if (Gate::denies('su-user-group') && $dirId == 0) {
			abort(403, 'Access denied');
		}

		$breadCrumbs = $this->breadCrumbs($dirId);

		$directories = $this->viewDirs($dirId);

		$files = $this->viewFiles($dirId);

		$currentDirPath = $this->currentDirPath($dirId);

		$folderList = $this->fsFetchFolderTree(0, '', '');

		// for Groups dropdown in Permissions modal box
		$user_groups = UserGroup::select(['user_group_id', 'user_group_name'])->where('user_group_id', '!=', 5)->get();

		// for users dropdown in Permissions modal box
		$users_data = User::select(['user_id', 'username'])
        ->where('username', '!=', 'super_user')
        ->andWhere('status', '=', User::STATUS_ACTIVE)->get();

		$region_groups = Region::select(['region_id', 'region_name'])->get();
		$d_type = null;

		$dir_type = null;

		$userId = Session::get('userId');

		$writePermissions = Gate::allows('su-user-group');

		if ($dirId) {
			$checkGroupPermission = '';
			$groupId = \DB::table('users')->select('user_group_member')->where('user_id', $userId)->value('user_group_member');
			if (!empty($groupId)) {
				$checkGroupPermissionArray = array();
				$groupIds = explode(',', $groupId);
				foreach ($groupIds as $grpId) {
					$checkGroupPermissionArray[] = 'FIND_IN_SET(' . $grpId . ',group_id_write) > 0';
				}
				$checkGroupPermission = implode(' OR ', $checkGroupPermissionArray);
			}
			$permissions = \DB::select("SELECT * FROM file_system where id = $dirId AND is_dir = 1 AND (FIND_IN_SET($userId,user_id_write) > 0 OR $checkGroupPermission) ORDER BY dir_file_name");

			if ($permissions) {
				$writePermissions = true;
			}
		}

		return view(
			'files.index', [
				'breadCrumbs' => $breadCrumbs,
				'dirId' => $dirId,
				'directories' => $directories,
				'files' => $files,
				'currentDirPath' => $currentDirPath,
				'folderList' => $folderList,
				'user_groups' => $user_groups,
				'users_data' => $users_data,
				'd_type' => $d_type,
				'dir_type' => $dir_type,
				'writePermissions' => $writePermissions,
				'appUrl' => $this->appUrl,
				'region_groups' => $region_groups,
			]
		);
	}

	public function viewDirs($dirId)
	{
		$dirsStr = '';
		$groupIdPos = false;
		$userId = Session::get('userId');
		$isSuLevel = Gate::allows('su-user-group');

		if (!$isSuLevel && (!isset($dirId) || $dirId == 0)) {
			die('You don\'t have access to /Files');
		}

		$checkGroupPermission = '';
		$groupId = \DB::table('users')->select('user_group_member')->where('user_id', $userId)->value('user_group_member');

		if (!empty($groupId)) {
			$checkGroupPermissionArray = array();
			$groupIds = explode(',', $groupId);
			foreach ($groupIds as $grpId) {
				$checkGroupPermissionArray[] = 'FIND_IN_SET(' . $grpId . ',group_id_read) > 0';
			}
			$checkGroupPermission = implode(' OR ', $checkGroupPermissionArray);
		}

		if ($isSuLevel) {
			$files = \DB::select("SELECT * FROM file_system where parent_dir_id = $dirId AND is_dir = 1 ORDER BY dir_file_name");
		} else {
			$files = \DB::select("SELECT * FROM file_system where parent_dir_id = $dirId AND is_dir = 1 AND (FIND_IN_SET($userId,user_id_read) > 0 OR $checkGroupPermission) ORDER BY dir_file_name");
		}

		foreach ($files as $fKey => $fVal) {
			$movePermissionDel = '';
			$lastModifiedTime = $fVal->date_modified == '0000-00-00 00:00:00' ? $fVal->date_created : $fVal->date_modified;
			$lastModifiedTime = date('l, j F Y', strtotime($lastModifiedTime)) . ' at ' . date('g:i A', strtotime($lastModifiedTime));

			// Write Permission Check
			$user_id_write = explode(',', $fVal->user_id_write);
			$userIdPos = in_array($userId, $user_id_write);

			if (strpos($groupId, ',') !== false) {
				$groupIdExp = explode(',', $groupId);
				$groupIdArr = explode(',', $fVal->group_id_write);

				foreach ($groupIdExp as $groupsId) {
					if (in_array($groupsId, $groupIdArr)) {
						$groupIdPos = strpos($fVal->group_id_write, $groupsId);
					}
				}
			} else {
				$groupIdPos = strpos($fVal->group_id_write, $groupId);
			}

			if ($userIdPos !== false || $groupIdPos !== false || $isSuLevel) {
				$movePermissionDel = '
                        <a alt="Move" title="Move" href="javascript: void(0)" class="move_file_link" onclick="move_folder(' . $fVal->id . ')"> <button type="button" class="btn btn-primary"><i class="fa fa-arrows"></i></button> </a>
                        <a alt="Permissions" title="Permissions" href="javascript: void(0)" class="permission_link" onclick="permissions(' . $fVal->id . ')"> <button type="button" class="btn btn-primary"><i class="fa fa-lock"></i></button> </a>
                        <a alt="Delete" title="Delete" href="' . $this->appUrl . 'files/delete-directory/' . $dirId . '/' . $fVal->id . '" onclick="return confirm(\'Are you sure you want to remove this directory?\')"> <button type="button" class="btn btn-primary"><i class="fa fa-trash"></i></button></a>
                    ';
			}

			$dirsStr .= '<div class="row margin-bottom-10">
                    <div class="col-md-1 files-dir col-sm-1 col-xs-1"><a href="' . $this->appUrl . 'files/' . $fVal->id . '"><img src="' . $this->appUrl . 'img/dir.jpg"></a></div>
                    <div class="col-md-3 col-sm-3 col-xs-3 margin-top-10 files-padding-left-10">
                        <a href="' . $this->appUrl . 'files/' . $fVal->id . '">' . $fVal->dir_file_name . '</a>
                    </div>
                    <div class="col-md-5 col-sm-4 col-xs-4 margin-top-10 files-padding-left-10">
                        ' . $lastModifiedTime . '
                    </div>
                    <div class="col-md-3 col-sm-4 col-xs-4 btns-margin files-padding-left-10">
                        <a alt="Download" title="Download" href="' . $this->appUrl . 'files/download-directory/' . $dirId . '/' . $fVal->id . '"> <button type="button" class="btn btn-primary"><i class="fa fa-download"></i></button> </a>
                        ' . $movePermissionDel . '
                    </div>
                </div>';
		}

		return $dirsStr;

	}

	public function viewFiles($dirId)
	{
		$fileFunctions = '';
		$groupIdPos = false;
		$userId = Session::get('userId');
		$groupId = \DB::table('users')->select('user_group_member')->where('user_id', $userId)->value('user_group_member');
		$isSuLevel = Gate::allows('su-user-group');

		$checkGroupPermission = '';

		if (!empty($groupId)) {
			$checkGroupPermissionArray = array();
			$groupIds = explode(',', $groupId);

			foreach ($groupIds as $grpId) {
				$checkGroupPermissionArray[] = 'FIND_IN_SET(' . $grpId . ',group_id_read) > 0';
			}

			$checkGroupPermission = implode(' OR ', $checkGroupPermissionArray);
		}

		if ($isSuLevel) {
			$files = \DB::select("SELECT * FROM file_system where is_dir = 0 AND parent_dir_id = $dirId ORDER BY dir_file_name");
		} else {
			$files = \DB::select("SELECT * FROM file_system where is_dir = 0 AND parent_dir_id = $dirId AND (FIND_IN_SET($userId,user_id_read) > 0 OR $checkGroupPermission) ORDER BY dir_file_name");

			if (empty($files) && count($files) == 0) {
				// Get Region ID From User
				$userRegion = User::select(['ops_group_member'])->where('user_id', '=', $userId)->first();
				if (!is_null($userRegion)) {
					$files = \DB::select("SELECT * FROM file_system where is_dir = 0 AND parent_dir_id = $dirId AND ((FIND_IN_SET('" . $userRegion->ops_group_member . "',region_id_read) > 0) OR (FIND_IN_SET('" . $userRegion->ops_group_member . "',region_id_write) > 0)) ORDER BY dir_file_name");
				}
			}
		}

		foreach ($files as $fKey => $fVal) {
			$movePermissionDel = '';
			$fileIcon = '';
			if (strpos($fVal->file_type, 'text') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/notepad.png';
			} elseif (strpos($fVal->file_type, 'video') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/video.png';
			} elseif (strpos($fVal->file_type, 'audio') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/audio.png';
			} elseif (strpos($fVal->file_type, 'image') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/image.png';
			} elseif (strpos($fVal->file_type, 'pdf') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/pdf.png';
			} elseif (strpos($fVal->file_type, 'word') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/word.png';
			} elseif (strpos($fVal->file_type, 'sheet') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/excel.png';
			} elseif (strpos($fVal->file_type, 'zip') !== false) {
				$fileIcon = $this->appUrl . 'img/file-icons/zip.png';
			}

			$fileId = $fVal->id;

			// Write Permission Check
			// $userIdPos = strpos($fVal->user_id_write, $userId);
			$user_id_write = explode(',', $fVal->user_id_write);
			$userIdPos = in_array($userId, $user_id_write);

			if (strpos($groupId, ',') !== false) {
				$groupIdExp = explode(',', $groupId);
				$groupIdArr = explode(',', $fVal->group_id_write);
				foreach ($groupIdExp as $groupsId) {
					if (in_array($groupsId, $groupIdArr)) {
						$groupIdPos = strpos($fVal->group_id_write, $groupsId);
					}
				}
			} else {
				$groupIdPos = strpos($fVal->group_id_write, $groupId);
			}

			if ($userIdPos !== false || $groupIdPos !== false || $isSuLevel) {
				$movePermissionDel = '
                    <a alt="Move" title="Move" href="javascript:void(0)" class="move_file_link" onclick="move_file(' . $fVal->id . ')"> <button type="button" class="btn btn-primary"><i class="fa fa-arrows"></i></button> </a>
                    <a alt="Permissions" title="Permissions" href="javascript: void(0)" class="file_permission_link" onclick="file_permissions(' . $dirId . ', ' . $fileId . ')"> <button type="button" class="btn btn-primary"><i class="fa fa-lock"></i></button> </a>
                    <a alt="Delete" title="Delete" href="' . $this->appUrl . 'files/delete-file/' . $dirId . '/' . $fileId . '" onclick="return confirm(\'Are you sure you want to remove this file?\')"> <button type="button" class="btn btn-primary"><i class="fa fa-trash"></i></button></a>
                ';
			}

			$fileFunctions .= '<div class="row margin-left-0 margin-right-0 margin-bottom-10">
                <div class="col-md-1 files-dir col-sm-1 col-xs-1"><img src="' . $fileIcon . '"></div>
                <div class="col-md-3 col-sm-3 col-xs-3 margin-top-10 files-padding-left-10">
                    ' . $fVal->dir_file_name . '
                </div>
                <div class="col-md-5 col-sm-4 col-xs-4 margin-top-10 files-padding-left-10">
                    ' . date('l, j F Y', strtotime($fVal->date_created)) . ' at ' . date('g:i A', strtotime($fVal->date_created)) . '
                </div>
                <div class="col-md-3 col-sm-4 col-xs-4 btns-margin files-padding-left-10">
                    <a alt="Download" title="Download" class="link_download" onclick="open_save(' . $fVal->id . ')" href="javascript:void(0)"> <button type="button" class="btn btn-primary"><i class="fa fa-download"></i></button> </a>
                        ' . $movePermissionDel . '
                </div>
            </div>';
		}

		return $fileFunctions;
	}

	private function fs_GetDirectorySize($path)
	{
		$bytestotal = 0;
		$path = realpath($path);
		if ($path !== false) {
			foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object) {
				$bytestotal += $object->getSize();
			}
		}
		return $bytestotal;
	}

	private function fs_getDirCount($path)
	{
		$size = 0;
		$ignore = array('.', '..', 'cgi-bin', '.DS_Store');
		$files = scandir($path);
		foreach ($files as $t) {
			if (in_array($t, $ignore)) continue;
			if (\File::isDirectory(rtrim($path, '/') . '/' . $t)) {
				$size += $this->fs_getDirCount(rtrim($path, '/') . '/' . $t);
				$size++;
			}
		}
		return $size;
	}

	private function fs_getFileCount($path)
	{
		$size = 0;
		$ignore = array('.', '..', 'cgi-bin', '.DS_Store');
		$files = scandir($path);
		foreach ($files as $t) {
			if (in_array($t, $ignore)) continue;
			$check_for_thumb = strpos($t, 'thumb_');
			if ($check_for_thumb !== false) continue;
			if (\File::isDirectory(rtrim($path, '/') . '/' . $t)) {
				$size += $this->fs_getFileCount(rtrim($path, '/') . '/' . $t);
			} else if (is_file(rtrim($path, '/') . '/' . $t)) {
				$size++;
			}
		}
		return $size;
	}

	public function addFileUserGroup(Request $request)
	{
	}

	public function addUserGroup(Request $request)
	{
	}

	public function openDownloadFile(Request $request)
	{
		$submit_download = $request->get('submit_download', '');
		$submit_open = $request->get('submit_open', '');
		$hidden_file_id = $request->get('hidden_file_id', '');

		if (isset($submit_download) && $submit_download == 'Download') {
			if (isset($hidden_file_id) && $hidden_file_id != '') {
				$file_download_row = File::select(['dir_file_name', 'dir_path', 'file_type'])->find($hidden_file_id);
				$download = $file_download_row->dir_path . $file_download_row->dir_file_name;
				if ($this->isWin) {
					$download = dirname(base_path()) . '\\' . str_replace('/var/www', '', $download);
				} else {
					$download = str_replace('/var/www', '', $download);
				}
				header('Content-type:' . $file_download_row->file_type);
				header('Content-Disposition:attachment;filename="' . $file_download_row->dir_file_name . '"');
				readfile($download);
			}
		}

		if (isset($submit_open) && $submit_open == 'Open') {
			if (isset($hidden_file_id) && $hidden_file_id != '') {
				$file_download_row = File::select(['dir_file_name', 'dir_path', 'file_type'])->find($hidden_file_id);
				$download = $file_download_row->dir_path . $file_download_row->dir_file_name;
				if ($this->isWin) {
					$download = dirname(base_path()) . '\\' . str_replace('/var/www', '', $download);
				} else {
					$download = str_replace('/var/www', '', $download);
				}

				if (strpos($file_download_row->file_type, 'mp3') !== false || strpos($file_download_row->file_type, 'mp4') !== false) {
					header('Location: ' . $this->appUrl . $download);
				} else if (strpos($file_download_row->file_type, 'spreadsheet') !== false) {
					header('Location: https://view.officeapps.live.com/op/view.aspx?src=' . $this->appUrl . $download);
				} else if (strpos($file_download_row->file_type, 'msword') !== false || strpos($file_download_row->file_type, 'doc') !== false || strpos($file_download_row->file_type, 'docx') !== false || strpos($file_download_row->file_type, 'text') !== false) {
					header('Location: https://docs.google.com/viewerng/viewer?url=' . $this->appUrl . $download . '&embedded=true');
				} else {
					header('Content-type:' . $file_download_row->file_type);
					header('Content-Disposition:inline;filename="' . $file_download_row->dir_file_name . '"');
					header('Content-Transfer-Encoding: binary');
					header('Accept-Ranges: bytes');
					readfile($download);
				}
			}
		}
	}

	public function setDirectoryPermission(Request $request)
	{
		$dir_id = $request->get('dir_id');
		$folder_id_hidden = $request->get('hidden_folder_id_for_permission');
		$permissions = $request->get('permissions');
		$recursive_permissions = $request->get('recursive_permissions', 0);
		$user = File::select(['dir_file_owner'])->find($folder_id_hidden);
		$user_id_read = $user->dir_file_owner;
		$user_id_write = $user_id_read;
		if (isset($permissions['User']) && is_array($permissions['User'])) {
			foreach ($permissions['User'] as $user_key => $user_value) {
				if (isset($user_value['read']) && $user_value['read'] == 1)
					$user_id_read .= ',' . $user_key;
				if (isset($user_value['write']) && $user_value['write'] == 1)
					$user_id_write .= ',' . $user_key;
			}
			$update_user = File::find($folder_id_hidden)->update(['user_id_read' => $user_id_read, 'user_id_write' => $user_id_write]);
		}
		if (isset($permissions['Group']) && is_array($permissions['Group'])) {
			$group_id_read = $group_id_write = '';
			foreach ($permissions['Group'] as $group_key => $group_value) {
				if (isset($group_value['read']) && $group_value['read'] == 1)
					$group_id_read .= $group_key . ',';
				if (isset($group_value['write']) && $group_value['write'] == 1)
					$group_id_write .= $group_key . ',';
			}
			$group_id_read = rtrim($group_id_read, ',');
			$group_id_write = rtrim($group_id_write, ',');
			$update_user = File::find($folder_id_hidden)->update(['group_id_read' => $group_id_read, 'group_id_write' => $group_id_write]);
		}

		if (isset($permissions['Region']) && is_array($permissions['Region'])) {
			$region_id_read = $region_id_write = '';
			foreach ($permissions['Region'] as $group_key => $group_value) {
				if (isset($group_value['read']) && $group_value['read'] == 1)
					$region_id_read .= $group_key . ',';
				if (isset($group_value['write']) && $group_value['write'] == 1)
					$region_id_write .= $group_key . ',';
			}
			$region_id_read = rtrim($region_id_read, ',');
			$region_id_write = rtrim($region_id_write, ',');

			$update_user = File::find($folder_id_hidden)->update(['region_id_read' => $region_id_read, 'region_id_write' => $region_id_write]);
		}
		//dd('KKKK');

		if (isset($recursive_permissions) && $recursive_permissions == 1) {
			$parent_permissions_row = File::select(['user_id_read', 'group_id_read', 'user_id_write', 'group_id_write', 'region_id_read', 'region_id_write'])->find($folder_id_hidden);
			$pp_user_id_read = $parent_permissions_row->user_id_read;
			$pp_group_id_read = $parent_permissions_row->group_id_read;
			$pp_user_id_write = $parent_permissions_row->user_id_write;
			$pp_group_id_write = $parent_permissions_row->group_id_write;

			$pp_region_id_read = $parent_permissions_row->region_id_read;
			$pp_region_id_write = $parent_permissions_row->region_id_write;

			//$children = str_replace($folder_id_hidden.',', '', $this->fs_get_children($folder_id_hidden));
			$children = $this->fs_get_children($folder_id_hidden);
			$children_exp = explode(',', $children);
			foreach ($children_exp as $value) {
				if ($value == $folder_id_hidden) continue;
				$update_user = File::find($value)->update(['user_id_read' => $pp_user_id_read, 'group_id_read' => $pp_group_id_read, 'user_id_write' => $pp_user_id_write, 'group_id_write' => $pp_group_id_write, 'region_id_read' => $pp_region_id_read, 'region_id_write' => $pp_region_id_write]);
			}
		}
		if (isset($update_user)) {
			Session::flash('flash_message', 'Permissions have been set.');
		}
		return redirect('/files/' . $dir_id);
	}

	public function setFilePermission(Request $request)
	{
		$dir_id = $request->get('dir_id');
		$file_permissions = $request->get('file_permissions');
		$file_id_hidden = $request->get('hidden_file_id_for_permission');
		$user = File::select(['dir_file_owner'])->find($file_id_hidden);
		$user_id_read = $user->dir_file_owner;
		$user_id_write = $user_id_read;
		if (isset($file_permissions['User']) && is_array($file_permissions['User'])) {
			foreach ($file_permissions['User'] as $file_user_key => $file_user_value) {
				if (isset($file_user_value['read']) && $file_user_value['read'] == 1)
					$user_id_read .= ',' . $file_user_key;
				if (isset($file_user_value['write']) && $file_user_value['write'] == 1)
					$user_id_write .= ',' . $file_user_key;
			}
			$update_user = File::find($file_id_hidden)->update(['user_id_read' => $user_id_read, 'user_id_write' => $user_id_write]);
		}
		$group_id_read = $group_id_write = '';
		if (isset($file_permissions['Group']) && is_array($file_permissions['Group'])) {
			foreach ($file_permissions['Group'] as $file_group_key => $file_group_value) {
				if (isset($file_group_value['read']) && $file_group_value['read'] == 1)
					$group_id_read .= $file_group_key . ',';
				if (isset($file_group_value['write']) && $file_group_value['write'] == 1)
					$group_id_write .= $file_group_key . ',';
			}
			$group_id_read = rtrim($group_id_read, ',');
			$group_id_write = rtrim($group_id_write, ',');
			$update_user = File::find($file_id_hidden)->update(['group_id_read' => $group_id_read, 'group_id_write' => $group_id_write]);
		}
		$region_id_read = $region_id_write = '';
		if (isset($file_permissions['Region']) && is_array($file_permissions['Region'])) {
			foreach ($file_permissions['Region'] as $file_group_key => $file_group_value) {
				if (isset($file_group_value['read']) && $file_group_value['read'] == 1)
					$region_id_read .= $file_group_key . ',';
				if (isset($file_group_value['write']) && $file_group_value['write'] == 1)
					$region_id_write .= $file_group_key . ',';
			}
			$region_id_read = rtrim($region_id_read, ',');
			$region_id_write = rtrim($region_id_write, ',');
			$update_user = File::find($file_id_hidden)->update(['region_id_read' => $region_id_read, 'region_id_write' => $region_id_write]);
		}
		if (isset($update_user)) {
			Session::flash('flash_message', 'Permissions have been set.');
		}
		return redirect('/files/' . $dir_id);
	}

	public function permissionsAjax(Request $request)
	{
		$fpath = config('app.fpath');
		$dir_id = $request->get('dir_id');
		$permissions_folder_id = $request->get('permissions_folder_id');
		$permissions_user_group_id = $request->get('permissions_user_group_id');
		$permissions_user = $request->get('permissions_user');
		$file_permissions_file_id = $request->get('file_permissions_file_id');
		$file_permissions_user_group_id = $request->get('file_permissions_user_group_id');
		$file_permissions_user = $request->get('file_permissions_user');
		$file_folder_id = $request->get('file_folder_id');
		$file_id = $request->get('file_id');
		$user_group_id = $request->get('user_group_id', 0);
		$user_group_type = $request->get('user_group_type');
		$permissions_region_group_id = $request->get('permissions_region_group_id');
		$folder_id = $request->get('folder_id');
		$permissions_arr = array();

		if (!empty($dir_id)) {
			$folder_row = File::select('dir_file_name', 'date_created', 'date_modified', 'dir_path', 'dir_file_owner', 'user_id_read', 'group_id_read', 'user_id_write', 'group_id_write', 'region_id_read', 'region_id_write')->find($dir_id);
			$dir_path_new = str_replace($fpath . "file_share/", "Files:/", $folder_row->dir_path);
			$permissions_arr['folder_title'] = $folder_row->dir_file_name . ' Properties';
			$permissions_arr['add_user_group_folder_location'] = $folder_row->dir_file_name;
			//$permissions_arr['folder_location'] = $folder_row->dir_path;
			$permissions_arr['folder_location'] = $dir_path_new;
			// Last Modified
			$last_modified_time = $folder_row->date_modified == '0000-00-00 00:00:00' ? $folder_row->date_created : $folder_row->date_modified;
			$permissions_arr['folder_last_modified'] = date("d/m/Y h:i:s A", strtotime($last_modified_time));
			if ($this->isWin) {
				$directory_path = dirname(base_path()) . '\\' . str_replace('/var/www', '', $folder_row->dir_path);
			} else {
				$directory_path = str_replace('/var/www', '', $folder_row->dir_path);
			}

			$dir_count = $this->fs_getDirCount($directory_path);
			$dir_count_status = $dir_count > 1 ? $dir_count . ' Folders' : $dir_count . ' Folder';
			$file_count = $this->fs_getFileCount($directory_path);
			$file_count_status = $file_count > 1 ? $file_count . ' Files' : $file_count . ' File';
			$permissions_arr['folders_files_count'] = $dir_count_status . ', ' . $file_count_status;

			$dir_size = $this->fs_GetDirectorySize($directory_path);
			$dir_size_bytes = number_format($this->fs_GetDirectorySize($directory_path));
			$dir_size_mb = round($this->fs_GetDirectorySize($directory_path) / (1024 * 1024), 1);
			$permissions_arr['folder_size'] = $dir_size_mb . ' MB (' . $dir_size_bytes . ' B)';

			// for users checkboxes in Permissions modal box
			$is_owner = false;
			$owner_id = $folder_row->dir_file_owner;
			$user_id_read = explode(',', $folder_row->user_id_read);
			$group_id_read = explode(',', $folder_row->group_id_read);

			$region_id_read = explode(',', $folder_row->region_id_read);
			$user_id_write = $folder_row->user_id_write;
			$group_id_write = $folder_row->group_id_write;
			$region_id_write = $folder_row->region_id_write;

			$user_id_write_arr = explode(',', $user_id_write);
			$group_id_write_arr = explode(',', $group_id_write);
			$region_id_write_arr = explode(',', $region_id_write);
			$user_str = '';

			foreach ($user_id_read as $user_id) {
				$user_name = User::select(['username'])->find($user_id)->username;
				$is_owner = $owner_id == $user_id ? true : false;
				$read_only = $is_owner ? 'disabled="disabled"' : '';
				$dir_read_check_boxes_css = $is_owner ? '' : 'class="dir-read-check-boxes"';
				$user_read_checked = 'checked';
				$user_write_checked = in_array($user_id, $user_id_write_arr) ? 'checked' : '';

				$user_str .= '<div class="row user_div" id="user_div_' . $user_id . '">
                    <div class="col-md-6">
                        <label for="permission_radio_read">User / ' . $user_name . '</label>
                    </div>
                    <div class="col-md-3">
                        <input ' . $dir_read_check_boxes_css . ' type="checkbox" name="permissions[User][' . $user_id . '][read]" ' . $user_read_checked . ' ' . $read_only . ' value="1" id="read_' . $user_id . '"><span>R</span>
                        <input type="checkbox" name="permissions[User][' . $user_id . '][write]" ' . $user_write_checked . ' ' . $read_only . ' class="margin-left-20 dir_write_check_boxes" value="1" id="write_' . $user_id . '" onclick="dir_modal_box_disable_read(\'read_' . $user_id . '\')"><span>W</span>
                    </div>
                    <div class="col-md-3">';
				if ($is_owner)
					$user_str .= '<font class="font-weight-bold">Owner</font>';
				else
					$user_str .= '<a href="javascript: void(0)" onclick="remove_div(\'user_div_' . $user_id . '\', ' . $dir_id . ')">Delete</a>';
				$user_str .= '</div>
                </div>';
			}

			if ($group_id_read[0]) {
				foreach ($group_id_read as $group_id) {
					$group = UserGroup::select(['user_group_name'])->find($group_id);
					$group_name = $group->user_group_name;
					$group_read_checked = 'checked';
					$group_write_checked = in_array($group_id, $group_id_write_arr) ? 'checked' : '';
					$user_str .= '<div class="row group_div" id="group_div_' . $group_id . '">
                        <div class="col-md-6">
                            <label for="permission_radio_read">Group / ' . $group_name . '</label>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" name="permissions[Group][' . $group_id . '][read]" ' . $group_read_checked . ' value="1" id="group_read_' . $group_id . '" class="dir-group-read-check-boxes"><span>R</span>
                            <input type="checkbox" name="permissions[Group][' . $group_id . '][write]" ' . $group_write_checked . ' class="margin-left-20 dir_group_write_check_boxes" value="1" id="group_write_' . $group_id . '" onclick="dir_modal_box_disable_read(\'group_read_' . $group_id . '\')"><span>W</span>
                        </div>
                        <div class="col-md-3">
                            <a href="javascript: void(0)" onclick="remove_div(\'group_div_' . $group_id . '\', ' . $dir_id . ')">Delete</a>
                        </div>
                    </div>';
				}
			}


			if ($region_id_read[0]) {
				foreach ($region_id_read as $region_id) {
					$group = Region::select(['region_name'])->find($region_id);
					$group_name = $group->region_name;
					$group_read_checked = 'checked';
					$group_write_checked = in_array($region_id, $region_id_write_arr) ? 'checked' : '';
					$user_str .= '<div class="row region_div" id="region_div_' . $region_id . '">
                        <div class="col-md-6">
                            <label for="permission_radio_read">Region / ' . $group_name . '</label>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" name="permissions[Region][' . $region_id . '][read]" ' . $group_read_checked . ' value="1" id="region_read_' . $region_id . '" class="dir-region-read-check-boxes"><span>R</span>
                            <input type="checkbox" name="permissions[Region][' . $region_id . '][write]" ' . $group_write_checked . ' class="margin-left-20 dir_region_write_check_boxes" value="1" id="region_write_' . $region_id . '" onclick="dir_modal_box_disable_read(\'region_read_' . $region_id . '\')"><span>W</span>
                        </div>
                        <div class="col-md-3">
                            <a href="javascript: void(0)" onclick="remove_div(\'region_div_' . $region_id . '\', ' . $dir_id . ')">Delete</a>
                        </div>
                    </div>';
				}
			}

			$user_str .= '<div class="group_div"></div>';
			$user_str .= '<div class="region_div"></div>';
			$permissions_arr['user_str'] = $user_str;
		}

		if (isset($permissions_folder_id) && $permissions_folder_id != 0
			&& ((isset($permissions_user_group_id) && $permissions_user_group_id != -1)
				|| (isset($permissions_user) && $permissions_user != -1)
				|| (isset($permissions_region_group_id) && $permissions_region_group_id != -1)
			)
		) {
			if ($permissions_user_group_id != -1) {
				$user_group_id = $permissions_user_group_id;
				$user_or_group = 'Group';
				is_array($user_group_id) ?: $user_group_id = [$user_group_id];
				$permissions = File::select(['id'])->where('id', $permissions_folder_id)->whereIn('group_id_read', $user_group_id)->get();
				if (!empty($permissions->id)) {
					$permissions_arr['message'] = 'Group already added.';
				}
			}
			if ($permissions_user != -1) {
				$user_group_id = $permissions_user;
				$user_or_group = 'User';
				is_array($user_group_id) ?: $user_group_id = [$user_group_id];
				$permissions = File::select(['id'])->where('id', $permissions_folder_id)->whereIn('group_id_read', $user_group_id)->get();
				if (!empty($permissions->id)) {
					$permissions_arr['message'] = 'User already added.';
				}
			}
		} else {
			$permissions_arr['message'] = 'Please select at least one from Group,Region or User==>1.';
		}

		// File
		if (isset($file_permissions_file_id) && $file_permissions_file_id != 0
			&& ((isset($file_permissions_user_group_id) && $file_permissions_user_group_id != -1)
				|| (isset($file_permissions_user) && $file_permissions_user != -1)
				|| (isset($permissions_region_group_id) && $permissions_region_group_id != -1)
			)
		) {
		} else {
			$permissions_arr['file_message'] = 'Please select at least one from Group,Region or User ===>2.';
		}
		// File

		// File Permissions Data Start
		if (isset($file_folder_id) && $file_folder_id != '' && isset($file_id) && $file_id != '') {
			$file_row = File::select(['dir_file_name', 'date_created', 'date_modified', 'dir_path', 'dir_file_owner', 'user_id_read',
				'group_id_read', 'user_id_write', 'group_id_write', 'region_id_read', 'region_id_write'])->find($file_id);

			$permissions_arr['file_title'] = $file_row->dir_file_name . ' Properties';
			$permissions_arr['file_location'] = $file_row->dir_path . $file_row->dir_file_name;

			$current_dir_path = $this->getCurrentDirectoryPath($file_folder_id);
			if ($this->isWin) {
				$current_dir_path = dirname(base_path()) . '\\' . str_replace('../', '', $current_dir_path);
			} else {
				$current_dir_path = $fpath . str_replace('../', '', $current_dir_path);
			}
			$file_size = filesize($current_dir_path . $file_row->dir_file_name);
			$file_size_bytes = number_format($file_size);
			$file_size_mb = round($file_size / (1024 * 1024), 1);
			$permissions_arr['file_size'] = $file_size_mb . ' MB (' . $file_size_bytes . ' B)';

			// Last Modified
			$last_modified_time = $file_row->date_modified == '0000-00-00 00:00:00' ? $file_row->date_created : $file_row->date_modified;
			$permissions_arr['file_last_modified'] = date("d/m/Y h:i:s A", strtotime($last_modified_time));

			$permissions_arr['add_user_group_file_title'] = $file_row->dir_file_name;

			// for users checkboxes in Permissions modal box
			$is_owner = false;
			$owner_id = $file_row->dir_file_owner;
			$user_id_read = explode(',', $file_row->user_id_read);
			$group_id_read = explode(',', $file_row->group_id_read);

			$region_id_read = explode(',', $file_row->region_id_read);

			$user_id_write = $file_row->user_id_write;
			$group_id_write = $file_row->group_id_write;
			$region_id_write = $file_row->region_id_write;

			$user_id_write_arr = explode(',', $user_id_write);
			$group_id_write_arr = explode(',', $group_id_write);
			$region_id_write_arr = explode(',', $region_id_write);

			$file_user_str = '';
			foreach ($user_id_read as $user_id) {
				$user = User::select(['username'])->find($user_id);
				$user_name = $user->username;
				$is_owner = $owner_id == $user_id ? true : false;
				$read_only = $is_owner ? 'disabled="disabled"' : '';
				$read_check_boxes_css = $is_owner ? '' : 'class="read-check-boxes"';
				$user_read_checked = 'checked';
				$user_write_checked = in_array($user_id, $user_id_write_arr) ? 'checked' : '';
				$file_user_str .= '<div class="row file_user_div" id="file_user_div_' . $user_id . '">
                    <div class="col-md-4">
                        <label for="permission_radio_read">User / ' . $user_name . '</label>
                    </div>
                    <div class="col-md-3">
                        <input type="checkbox" ' . $read_check_boxes_css . ' name="file_permissions[User][' . $user_id . '][read]" ' . $user_read_checked . ' ' . $read_only . ' value="1" id="file_read_' . $user_id . '"><span>R</span>
                        <input type="checkbox" name="file_permissions[User][' . $user_id . '][write]" ' . $user_write_checked . ' ' . $read_only . ' class="margin-left-20 write_check_boxes" value="1" id="file_write_' . $user_id . '" onclick="disable_read(\'file_read_' . $user_id . '\')"><span>W</span>
                    </div>
                    <div class="col-md-5">';
				if ($is_owner)
					$file_user_str .= '<font class="font-weight-bold">Owner</font>';
				else
					$file_user_str .= '<a href="javascript: void(0)" onclick="file_remove_div(\'file_user_div_' . $user_id . '\', ' . $file_id . ')">Delete</a>';
				$file_user_str .= '</div>
                </div>';
			}

			if ($group_id_read[0]) {
				foreach ($group_id_read as $group_id) {
					$group = UserGroup::select(['user_group_name'])->find($group_id);
					$group_name = $group->user_group_name;
					$group_read_checked = 'checked';
					$group_write_checked = in_array($group_id, $group_id_write_arr) ? 'checked' : '';
					$file_user_str .= '<div class="row file_group_div" id="file_group_div_' . $group_id . '">
                        <div class="col-md-4">
                            <label for="permission_radio_read">Group / ' . $group_name . '</label>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" name="file_permissions[Group][' . $group_id . '][read]" ' . $group_read_checked . ' value="1" class="group_read-check-boxes" id="group_file_read_' . $group_id . '"><span>R</span>
                            <input type="checkbox" name="file_permissions[Group][' . $group_id . '][write]" ' . $group_write_checked . ' class="margin-left-20 group_write_check_boxes" value="1" id="group_file_write_' . $group_id . '" onclick="disable_read(\'group_file_read_' . $group_id . '\')"><span>W</span>
                        </div>
                        <div class="col-md-5">
                            <a href="javascript: void(0)" onclick="file_remove_div(\'file_group_div_' . $group_id . '\', ' . $file_id . ')">Delete</a>
                        </div>
                    </div>';
				}
			}
			//$user_str='';
			if ($region_id_read[0]) {
				foreach ($region_id_read as $region_id) {
					$group = Region::select(['region_name'])->find($region_id);
					$group_name = $group->region_name;
					$group_read_checked = 'checked';
					$group_write_checked = in_array($region_id, $region_id_write_arr) ? 'checked' : '';
					$file_user_str .= '<div class="row file_region_div" id="file_region_div_' . $region_id . '">
                        <div class="col-md-4">
                            <label for="permission_radio_read">Region / ' . $group_name . '</label>
                        </div>
                        <div class="col-md-3">
                            <input type="checkbox" name="file_permissions[Region][' . $region_id . '][read]" ' . $group_read_checked . ' value="1" id="region_file_read_' . $region_id . '" class="dir-region-read-check-boxes"><span>R</span>
                            <input type="checkbox" name="file_permissions[Region][' . $region_id . '][write]" ' . $group_write_checked . ' class="margin-left-20 dir_region_write_check_boxes" value="1" id="region_file_write_' . $region_id . '" onclick="disable_read(\'region_file_read_' . $region_id . '\')"><span>W</span>
                        </div>
                        <div class="col-md-5">
                            <a href="javascript: void(0)" onclick="file_remove_div(\'file_region_div_' . $region_id . '\', ' . $dir_id . ')">Delete</a>
                        </div>
                    </div>';
				}
			}
			$file_user_str .= '<div class="file_group_div"></div>';
			$file_user_str .= '<div class="file_region_div"></div>';
			$permissions_arr['file_user_str'] = $file_user_str;
		}
		// File Permissions Data End

		if (isset($user_group_id) && $user_group_id != '' && isset($user_group_type) && $user_group_type == 'user' && isset($folder_id) && $folder_id != '') {
			$user_id_to_del = $user_group_id;
			\DB::statement("UPDATE file_system SET user_id_read = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_id_read, ','), '," . $user_id_to_del . ",', ',')), user_id_write = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_id_write, ','), '," . $user_id_to_del . ",', ',')) WHERE FIND_IN_SET('" . $user_id_to_del . "', user_id_read) AND id=$folder_id");
		}

		if (isset($user_group_id) && $user_group_id != '' && isset($user_group_type) && $user_group_type == 'group' && isset($folder_id) && $folder_id != '') {
			$group_id_to_del = $user_group_id;
			\DB::statement("UPDATE file_system SET group_id_read = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', group_id_read, ','), '," . $group_id_to_del . ",', ',')), group_id_write = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', group_id_write, ','), '," . $group_id_to_del . ",', ',')) WHERE FIND_IN_SET('" . $group_id_to_del . "', group_id_read) AND id=$folder_id");
		}
		////////////
		if (isset($user_group_id) && $user_group_id != '' && isset($user_group_type) && $user_group_type == 'region' && isset($folder_id) && $folder_id != '') {
			$group_id_to_del = $user_group_id;
			\DB::statement("UPDATE file_system SET region_id_read = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', region_id_read, ','), '," . $group_id_to_del . ",', ',')), region_id_write = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', region_id_write, ','), '," . $group_id_to_del . ",', ',')) WHERE FIND_IN_SET('" . $group_id_to_del . "', region_id_read) AND id=$folder_id");
		}

		if (isset($user_group_id) && $user_group_id != '' && isset($user_group_type) && $user_group_type == 'user' && isset($file_id) && $file_id != '') {
			$user_id_to_del = $user_group_id;
			\DB::statement("UPDATE file_system SET user_id_read = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_id_read, ','), '," . $user_id_to_del . ",', ',')), user_id_write = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', user_id_write, ','), '," . $user_id_to_del . ",', ',')) WHERE FIND_IN_SET('" . $user_id_to_del . "', user_id_read) AND id=$file_id");
		}

		if (isset($user_group_id) && $user_group_id != '' && isset($user_group_type) && $user_group_type == 'group' && isset($file_id) && $file_id != '') {
			$group_id_to_del = $user_group_id;
			\DB::statement("UPDATE file_system SET group_id_read = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', group_id_read, ','), '," . $group_id_to_del . ",', ',')), group_id_write = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', group_id_write, ','), '," . $group_id_to_del . ",', ',')) WHERE FIND_IN_SET('" . $group_id_to_del . "', group_id_read) AND id=$file_id");
		}

		if (isset($user_group_id) && $user_group_id != '' && isset($user_group_type) && $user_group_type == 'region' && isset($file_id) && $file_id != '') {
			$group_id_to_del = $user_group_id;
			\DB::statement("UPDATE file_system SET region_id_read = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', region_id_read, ','), '," . $group_id_to_del . ",', ',')), region_id_write = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', region_id_write, ','), '," . $group_id_to_del . ",', ',')) WHERE FIND_IN_SET('" . $group_id_to_del . "', region_id_read) AND id=$file_id");
		}
		return response()->json($permissions_arr);
	}

	private function fsFetchFolderTree($parent = 0, $spacing = '', $user_tree_array = '')
	{
		$dir_id_arr = array();
		$dir_id_arr_reverse = array();
		if (!is_array($user_tree_array)) {
			$user_tree_array = array();
		}
		$rows = File::select(['id', 'dir_file_name', 'parent_dir_id'])->where('is_dir', 1)->where('parent_dir_id', $parent)->orderBy('dir_file_name')->get();
		foreach ($rows as $row) {
			$dir_path = '';
			$dir_id_arr = $this->fs_get_parents($row->id);
			$dir_id_arr_reverse = array_reverse($dir_id_arr);
			foreach ($dir_id_arr_reverse as $value) {
				$dir_row = File::select(['dir_file_name'])->find($value);
				if (!empty($dir_row->dir_file_name)) {
					$dir_path .= '/' . $dir_row->dir_file_name;
				}
			}
			$user_tree_array[] = array("id" => $row->id, "dir_file_name" => $spacing . $dir_path);
			$user_tree_array = $this->fsFetchFolderTree($row->id, $spacing . '&nbsp;&nbsp;&nbsp;&nbsp;', $user_tree_array);
		}
		return $user_tree_array;
	}

	public function moveFile(Request $request)
	{
		$fpath = config('app.fpath');
		$dir_id = $request->get('dir_id');
		$hidden_move_file_id = $request->get('hidden_move_file_id');
		$folder_to_move = $request->get('folder_to_move');
		$current_dir_path = $this->getCurrentDirectoryPath($dir_id);
		if ($this->isWin) {
			$current_dir_path = dirname(base_path()) . '\\' . str_replace('../', '', $current_dir_path);
		} else {
			$current_dir_path = $fpath . str_replace('../', '', $current_dir_path);
		}
		$folder_to_move_path = '';
		$file_to_move_row = File::select(['dir_file_name', 'file_type'])->find($hidden_move_file_id);
		$dir_id_arr = $this->fs_get_parents($folder_to_move);
		$dir_id_arr_reverse = array_reverse($dir_id_arr);
		foreach ($dir_id_arr_reverse as $value) {
			$dir_row = File::select(['dir_file_name'])->find($value);
			if (!empty($dir_row->dir_file_name)) {
				$folder_to_move_path .= '/' . $dir_row->dir_file_name;
			}
		}

		if (strlen($folder_to_move_path) == 1) {
			$file_full_path_new = $fpath . 'file_share/';
		} else {
			$file_full_path_new = $fpath . 'file_share' . $folder_to_move_path . '/';
		}

		$folder_to_move_path = '../file_share' . $folder_to_move_path . '/';
		if ($this->isWin) {
			$folder_to_move_path = dirname(base_path()) . '\\' . str_replace('../', '', $folder_to_move_path);
		} else {
			$folder_to_move_path = $fpath . str_replace('../', '', $folder_to_move_path);
		}
		if (!\File::exists($folder_to_move_path . $file_to_move_row->dir_file_name)) {
			if (rename($current_dir_path . $file_to_move_row->dir_file_name, $folder_to_move_path . $file_to_move_row->dir_file_name)) {
				if (strpos($file_to_move_row->file_type, 'image') !== false)
					rename($current_dir_path . 'thumb_' . $file_to_move_row->dir_file_name, $folder_to_move_path . '/thumb_' . $file_to_move_row->dir_file_name);
				File::find($hidden_move_file_id)->update(['parent_dir_id' => $folder_to_move, 'dir_path' => $file_full_path_new]);
				if (!empty($dir_id)) {
					File::find($dir_id)->update(['date_modified' => date('Y-m-d H:i:s')]);
				}
				if (!empty($folder_to_move)) {
					File::find($folder_to_move)->update(['date_modified' => date('Y-m-d H:i:s')]);
				}
				Session::flash('flash_message', 'The file has been moved successfully.');
			}
		} else {
			Session::flash('flash_message', "This file (" . $file_to_move_row->dir_file_name . ") already exists in the target directory. (If you cannot see the file or directory, you may not have permissions to read the file or directory.  Please contact your administrator if you require access.");
		}
		return redirect('/files/' . $dir_id);
	}

	private function fs_get_children($pid)
	{
		$str = $pid;
		$rows = File::select('id')->where('parent_dir_id', $pid)->get();
		foreach ($rows as $row) {
			if (!empty($row->id)) {
				$str .= ',' . $this->fs_get_children($row->id);
			}
		}
		return $str;
	}

	function moveDirectory(Request $request)
	{
		$fpath = config('app.fpath');
		$dir_id = $request->get('dir_id');
		$hidden_move_folder_id = $request->get('hidden_move_folder_id');
		$folder_to_move = $request->get('folder_to_move');
		$current_dir_path = $this->getCurrentDirectoryPath($dir_id);
		if ($this->isWin) {
			$current_dir_path_from = dirname(base_path()) . '\\' . str_replace('../', '', $current_dir_path);
		} else {
			$current_dir_path_from = $fpath . str_replace('../', '', $current_dir_path);
		}

		$folder_to_move_path = '';
		$folder_to_move_row = File::select(['dir_file_name'])->find($hidden_move_folder_id);
		$dir_id_arr = $this->fs_get_parents($folder_to_move);
		$dir_id_arr_reverse = array_reverse($dir_id_arr);
		foreach ($dir_id_arr_reverse as $value) {
			$dir_row = File::select(['dir_file_name'])->find($value);
			if (!empty($dir_row->dir_file_name)) {
				$folder_to_move_path .= '/' . $dir_row->dir_file_name;
			}
		}

		$folder_to_move_path = '../file_share' . $folder_to_move_path . '/';
		if ($this->isWin) {
			$folder_to_move_path_to = dirname(base_path()) . '\\' . str_replace('../', '', $folder_to_move_path);
		} else {
			$folder_to_move_path_to = $fpath . str_replace('../', '', $folder_to_move_path);
		}
		if (rename($current_dir_path_from . $folder_to_move_row->dir_file_name, $folder_to_move_path_to . $folder_to_move_row->dir_file_name)) {
			File::find($hidden_move_folder_id)->update(['parent_dir_id' => $folder_to_move]);
			$child_dir = $this->fs_get_children($hidden_move_folder_id);
			$explode_child_dir = explode(',', $child_dir);
			foreach ($explode_child_dir as $value) {
				$child_dir_files = File::select(['dir_file_name', 'dir_path'])->where('parent_dir_id', $value)->get();
				foreach ($child_dir_files as $file) {
					$new_file_full_path = str_replace(substr($current_dir_path, 3), str_replace('../', '', $folder_to_move_path), $file->dir_path);
					File::where('parent_dir_id', $value)->update(['dir_path' => $new_file_full_path]);
				}
			}
			if ($dir_id) {
				File::find($dir_id)->update(['date_modified' => date('Y-m-d H:i:s')]);
			}
			if ($folder_to_move) {
				File::find($folder_to_move)->update(['date_modified' => date('Y-m-d H:i:s')]);
			}
			Session::flash('flash_message', 'The folder has been moved successfully.');
		}
		return redirect('/files/' . $dir_id);
	}

	public function downloadDirectory($dir_id, $dir_to_download_id)
	{
		$fpath = config('app.fpath');
		$dir_name = File::select(['dir_file_name'])->find($dir_to_download_id);
		$current_dir_path = $this->getCurrentDirectoryPath($dir_id);

		if ($this->isWin) {
			$current_dir_path = dirname(base_path()) . '\\' . str_replace('../', '', $current_dir_path);
		} else {
			$current_dir_path = $fpath . str_replace('../', '', $current_dir_path);
		}

		$download_dir_path = '/home/bitnami/backups/for_download/' . $dir_name->dir_file_name . '.zip';

		$this->fs_Zip($current_dir_path . $dir_name->dir_file_name, $download_dir_path, $dir_name->dir_file_name);
	}

	private function fs_Zip($source, $destination, $dir_to_download)
	{
		////////////////////////////////////////////
		if (file_exists($destination)) {
			unlink($destination);
		}

		$zip = new \Chumper\Zipper\Zipper();
		$zip->make($destination);

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			DEFINE('DS', DIRECTORY_SEPARATOR); //for windows
		} else {
			DEFINE('DS', '/'); //for linux
		}

		$source = str_replace('\\', DS, realpath($source));
		if (\File::isDirectory($source) === true) {
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($files as $file) {
				$file = str_replace('\\', DS, $file);
				// Ignore "." and ".." folders
				if (in_array(substr($file, strrpos($file, DS) + 1), array('.', '..')))
					continue;
				$file = realpath($file);
				if (\File::isDirectory($file) === true) {
					//$zip->addEmptyDir(str_replace($source . DS, '', $file . DS));
				} else if (is_file($file) === true) {
					if (strpos($file, 'thumb') !== false) {
						continue;
					} else {
						$file_name_from_path = basename($file);
						$filename = substr(strrchr($file, DS), 1);
						$dir_path = str_replace($filename, '', str_replace($source . DS, '', $file));
						//$zip->addFile($file, $dir_path.$file_name_from_path);
						$zip->add($file);
					}
				}
			}
		} else if (is_file($source) === true) {
			$zip->add($source);
		}
		$zip->close();
		////////////////////////////////////////////
		ob_clean();
		ob_end_flush();
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=" . $dir_to_download . '.zip');
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile($destination);
		exit;
		die();
	}

	public function createDir(Request $request)
	{
		$fpath = config('app.fpath');
		$userId = Session::get('userId');

		$this->validate($request, [
			'folder_name' => 'required'
		]);

		$parentDirId = $request->has('dir_id') ? $request->dir_id : 0;
		if ($this->isWin) {
			$dirPath = dirname(base_path()) . '\\' . str_replace('../', '', $request->current_dir_path);
		} else {
			$dirPath = $fpath . str_replace('../', '', $request->current_dir_path);
		}
		$dirPathDb = $fpath . str_replace('../', '', $request->current_dir_path);

		if (!\File::exists($dirPath . $request->folder_name)) {
			if (!\File::isDirectory($dirPath . $request->folder_name) && !\File::makeDirectory($dirPath . $request->folder_name, 0777, true)) {
				$error = 'Directory not found as well as unable to create new directory ' . $dirPath . $request->folder_name;
				return false;
			}
			$file = new File;
			$file->dir_file_name = $request->folder_name;
			$file->is_dir = 1;
			$file->parent_dir_id = $parentDirId;
			$file->dir_path = $dirPathDb;
			$file->creator_id = $userId;
			$file->dir_file_owner = $userId;
			$file->user_id_read = $userId;
			$file->user_id_write = $userId;
			$file->save();
			Session::flash('flash_message', 'The directory was successfully created.'); //<--FLASH MESSAGE
		} else
			return redirect('/files')->withErrors(['This filename (' . $request->folder_name . ') already exists.  Please choose another name.  (If you cannot see the file or directory, you may not have permissions to read the file or directory.  Please contact your administrator if you require access.']);

		return redirect('/files/' . $parentDirId);
	}

	public function uploadFile(Request $request)
	{
		$this->validate($request, [
			'file_name' => 'required'
		]);
		$fpath = config('app.fpath');
		if ($request->has('submit') && $request->submit == 'Upload File') {
			$userId = Session::get('userId');
			$fileName = $request->file('file_name')->getClientOriginalName();
			$fileType = $request->file('file_name')->getMimeType();
			$fileSize = $request->file('file_name')->getClientSize();
			$dirId = $request->has('dir_id') ? $request->dir_id : 0;
			if ($this->isWin) {
				$path = dirname(base_path()) . '\\' . str_replace('../', '', $request->current_dir_path);
			} else {
				$path = $fpath . str_replace('../', '', $request->current_dir_path);
			}
			$dirPathDb = $fpath . str_replace('../', '', $request->current_dir_path);

			// 100MB file check
			if ($fileSize > 104857600) {
				Session::flash('flash_message', 'Filesize exceeds upload limit (100MB)');
			} else {
				if (!\File::exists($dirPathDb . $fileName)) {
					$fileNameTmp = $request->file_name->getPathName();
					if (!\File::isDirectory($dirPathDb) && !mkdir($dirPathDb, 0777, true)) {
						Session::flash('flash_message', 'Directory not found as well as unable to create new directory ' . $dirPathDb);
						return false;
					}
					if (move_uploaded_file($fileNameTmp, $dirPathDb . $fileName)) {
						// Creates thumbnail if file is image
						if (@is_array(getimagesize($dirPathDb . $fileName))) {
							$image = Image::make($dirPathDb . $fileName);
							$image->encode('png');
							$image->resize(50, 50)->save($dirPathDb . 'thumb_' . $fileName);
						}

						$file = new File;
						$file->dir_file_name = $fileName;
						$file->parent_dir_id = $dirId;
						$file->dir_path = $dirPathDb;
						$file->file_type = $fileType;
						$file->creator_id = $userId;
						$file->dir_file_owner = $userId;
						$file->user_id_read = $userId;
						$file->user_id_write = $userId;
						$file->save();
						Session::flash('flash_message', 'The file was successfully uploaded.'); //<--FLASH MESSAGE
						// Update modified date of the directory as a new file has been uploaded in it
						if ($dirId)
							\DB::statement("UPDATE file_system SET date_modified = NOW() WHERE id = '$dirId'");
					}
				} else {
					return redirect('/files/' . $dirId)->withErrors(["This filename ($fileName) already exists.  Please choose another name.  (If you cannot see the file or directory, you may not have permissions to read the file or directory.  Please contact your administrator if you require access."]);
				}
			}
			return redirect('/files/' . $dirId);
		}
	}

	private function get_children($pid)
	{
		$str = $pid;
		$dirs = File::select('id')->where('parent_dir_id', $pid)->get();
		if (!empty($dirs)) {
			foreach ($dirs as $dir) {
				$str .= ',' . $this->get_children($dir->id);
			}
		}
		return $str;
	}

	private function rrmdir($dir)
	{
		if (\File::isDirectory($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (\File::isDirectory($dir . "/" . $object))
						$this->rrmdir($dir . "/" . $object);
					else
						unlink($dir . "/" . $object);
				}
			}
			rmdir($dir);
		}
	}

	private function fs_get_parents($pid, $found = array())
	{
		array_push($found, $pid);
		$files = File::where('id', $pid)->get();
		foreach ($files as $file) {
			$found = $this->fs_get_parents($file->parent_dir_id, $found);
		}
		return $found;
	}

	private function getCurrentDirectoryPath($dir_id)
	{
		if (isset($dir_id) && $dir_id != 0) {
			$current_dir_path = '../file_share/';
			$dir_id_arr = array();
			$dir_path = '';
			$count = 1;
			$bread_crumbs = '';
			$dir_id_arr_reverse = array();
			$dir_id_arr = $this->fs_get_parents($dir_id);
			$dir_id_arr_reverse = array_reverse($dir_id_arr);
			$dir_arr_count = count($dir_id_arr_reverse);
			foreach ($dir_id_arr_reverse as $value) {
				$dir_row = File::select(['id', 'dir_file_name'])->find($value);
				if (!empty($dir_row->dir_file_name)) {
					$dir_path .= $dir_row->dir_file_name . '/';
					$current_dir_path = '../file_share/' . $dir_path;
					if ($count == $dir_arr_count)
						$bread_crumbs .= '<li class="active">' . $dir_row->dir_file_name . '</li>';
					else
						$bread_crumbs .= '<li><a href="' . $this->appUrl . 'files/' . $dir_row->id . '">' . $dir_row->dir_file_name . '</a></li>';
					$count++;
				}
			}
		} else {
			$current_dir_path = '../file_share/';
		}
		return $current_dir_path;
	}

	public function deleteFile($dir_id, $file_id)
	{
		$fpath = config('app.fpath');
		$parent_id = !empty($dir_id) ? $dir_id : 0;
		$current_dir_path = $this->getCurrentDirectoryPath($dir_id);
		if ($this->isWin) {
			$current_dir_path = dirname(base_path()) . '\\' . str_replace('../', '', $current_dir_path);
		} else {
			$current_dir_path = $fpath . str_replace('../', '', $current_dir_path);
		}
		$file = File::select(['dir_file_name', 'file_type'])->find($file_id);
		$file_to_remove = $file->dir_file_name;
		$file_type = $file->file_type;
		@unlink($current_dir_path . $file_to_remove);
		if (strpos($file_type, 'image') !== false) {
			@unlink($current_dir_path . 'thumb_' . $file_to_remove);
		}
		$del = File::where('id', $file_id)->where('is_dir', 0)->delete();
		if ($del) {
			File::where('id', $parent_id)->update(['date_modified' => date('Y-m-d H:i:s')]);
		}
		Session::flash('flash_message', 'The file was successfully deleted.');
		return redirect('/files/' . $dir_id);
	}

	public function deleteDirectory($dir_id, $dir_to_del_id)
	{
		$fpath = config('app.fpath');
		$parent_id = !empty($dir_id) ? $dir_id : 0;
		$current_dir_path = $this->getCurrentDirectoryPath($dir_id);
		if ($this->isWin) {
			$current_dir_path = dirname(base_path()) . '\\' . str_replace('../', '', $current_dir_path);
		} else {
			$current_dir_path = $fpath . str_replace('../', '', $current_dir_path);
		}
		$dir_name = File::select(['dir_file_name'])->find($dir_to_del_id);
		$this->rrmdir($current_dir_path . '/' . $dir_name->dir_file_name);
		$dir_to_del = $this->get_children($dir_to_del_id);
		File::where('id', $dir_to_del_id)->delete();
		is_array($dir_to_del) ?: $dir_to_del = array($dir_to_del);
		File::whereIn('parent_dir_id', $dir_to_del)->delete();
		File::where('id', $parent_id)->update(['date_modified' => date('Y-m-d H:i:s')]);
		Session::flash('flash_message', 'The directory was successfully deleted.');
		return redirect('/files/' . $dir_id);
	}

	public function currentDirPath($dirId)
	{
		if (isset($dirId) && $dirId != 0) {
			$dirIdArr = array();
			$dirPath = '';
			$dirIdArrReverse = array();
			$dirIdArr = $this->getParents($dirId);
			$dirIdArrReverse = array_reverse($dirIdArr);
			foreach ($dirIdArrReverse as $value) {
				$files = File::select(['id', 'dir_file_name'])->where('id', '=', $value)->first();
				$dirPath .= $files->dir_file_name . '/';
				$currentDirPath = '../file_share/' . $dirPath;
			}
		} else
			$currentDirPath = '../file_share/';
		return $currentDirPath;
	}

	public function breadCrumbs($dirId)
	{
		if (isset($dirId) && $dirId != 0) {
			$count = 1;
			$breadCrumbs = '';
			$dirIdArr = $this->getParents($dirId);
			$dirIdArrReverse = array_reverse($dirIdArr);
			$dirArrCount = count($dirIdArrReverse);

			foreach ($dirIdArrReverse as $value) {
				$files = File::select(['id', 'dir_file_name'])->where('id', $value)->first();

				if ($count == $dirArrCount) {
					$breadCrumbs .= '<li class="active">' . $files->dir_file_name . '</li>';
				} else {
					$breadCrumbs .= '<li><a href="' . $this->appUrl . 'files/' . $files->id . '">' . $files->dir_file_name . '</a></li>';
				}

				$count++;
			}

			$root = Gate::allows('su-user-group') ? '<a href="' . $this->appUrl . 'files">Files</a> <span class="grey-color">/</span> ' : '';

			return $root . $breadCrumbs;
		}

		$breadCrumbs = '<a href="' . $this->appUrl . 'files">Files</a>';

		return $breadCrumbs;
	}

	public function getParents($pid, $found = array())
	{
		array_push($found, $pid);
		$files = File::select(['parent_dir_id'])->where('id', '=', $pid)->get();

		foreach ($files as $file) {
			$parentDirId = $files[0]->parent_dir_id;
			if ($parentDirId > 0)
				$found = $this->getParents($parentDirId, $found);
		}
		return $found;
	}

	// This function is returning json data to display in Grid
	public function json(Request $request)
	{
		$registers = Register::select(['reg_management_id', 'reg_management_id', 'unit_id', 'reg_number']);

		return Datatables::of($registers)
			->setRowId(function ($register) {
				return 'tr_' . $register->reg_management_id;
			})
			->addColumn('checkbox', function ($register) {
				return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $register->reg_management_id . '">';
			}, 0)
			->addColumn('action', function ($register) {
				return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'registers/' . $register->reg_management_id . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
			})
			->editColumn('unit_id', function ($register) {
				return '<a href="units/' . $register->unit_id . '/edit">' . $register->unit['unit_name'] . '</a>';
			})
			->make();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		// Unit Name Dropdown
		$units = Unit::pluck('unit_name', 'unit_id');
		return view('registers.create', [
			'heading' => 'Create New Register',
			'btn_caption' => 'Create Register',
			'units' => $units
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'unit_name' => 'required',
			'reg_number' => 'required'
		]);

		$register = new Register;
		$register->unit_id = $request->unit_name;
		$register->unit_name = '';
		$register->reg_number = $request->reg_number;
		$register->save();
		Session::flash('flash_message', 'Register has been added successfully!'); //<--FLASH MESSAGE
		return redirect('/registers');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$register = Register::find($id);
		// Unit Name Dropdown
		$units = Unit::pluck('unit_name', 'unit_id');
		$selectedUnit = $register->unit_id;
		return view('registers.create', [
			'heading' => 'Edit Register',
			'btn_caption' => 'Edit Register',
			'register' => $register,
			'units' => $units,
			'selectedUnit' => $selectedUnit
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'unit_name' => 'required',
			'reg_number' => 'required'
		]);

		$register = Register::find($id);
		$register->unit_id = $request->unit_name;
		$register->unit_name = '';
		$register->reg_number = $request->reg_number;
		$register->save();
		Session::flash('flash_message', 'Register has been updated successfully!'); //<--FLASH MESSAGE
		return redirect('/registers');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$registerIds = explode(',', $id);
		foreach ($registerIds as $registerId) {
			$register = Register::find($registerId);
			$register->delete();
		}
		echo $id;
	}
}
