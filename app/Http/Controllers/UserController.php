<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\User;
use App\UserGroup;
use App\Unit;
use App\OpsGroup;
use App\Region;
use Session;
use Yajra\Datatables\Datatables;

class UserController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('role:admin');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return view('users.index');
	}

	// This function is returning json data to display in Grid
	public function json(Request $request)
	{
		$users = User::select(
			[
				'user_id',
				'user_id',
				'username',
				'user_first',
				'user_last',
				'contact_number',
				'user_email',
				'user_group_member',
				'unit_member'
			]
		);

		return Datatables::of($users)
			->setRowId(function ($user) {
				return 'tr_' . $user->user_id;
			})
			->addColumn('checkbox', function ($user) {
				return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $user->user_id . '">';
			}, 0)
			->editColumn('user_group_member', function ($user) {
				return UserGroup::whereIn('user_group_id', explode(',', $user->user_group_member))->get()->implode('user_group_name', ', ');
			})
			->editColumn('unit_member', function ($user) {
				return Unit::whereIn('unit_id', explode(',', $user->unit_member))->get()->implode('unit_name', ', ');
			})
			->filterColumn('unit_member', function ($query, $keyword) {
				$query->where('unit_members', 'like', "%$keyword%");
			})
			->filterColumn('unit_member', function ($query, $keyword) {
				if (strlen($keyword) === 0) {
					return;
				}

				$units = Unit::where('unit_name', 'like', "%$keyword%")->pluck('unit_id')->toArray();

				if (count($units) === 0) {
					$query->orWhereRaw('FIND_IN_SET(0, unit_member)');
				}

				$query->where(function ($subQuery) use ($query, $units) {
					foreach ($units as $unitId) {
						$subQuery->orWhereRaw('FIND_IN_SET(?, unit_member)', [$unitId]);
					}
				});
			})
			->addColumn('action', function ($user) {
				return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'users/' . $user->user_id . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
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
		$operationsManagerArr = array();

		// User Group Member Dropdown
		$userGroups = UserGroup::where('user_group_id', '<=', UserGroup::USER_GROUP_ADMIN)->pluck('user_group_name', 'user_group_id');

		if (Gate::allows('su-user-group')) {
			$userGroups = UserGroup::pluck('user_group_name', 'user_group_id');
		}

		// Unit Member Dropdown
		$unitMembers = Unit::pluck('unit_name', 'unit_id');

		// Operations Manager Dropdown
		$operationsManager = User::whereRaw('FIND_IN_SET(2, user_group_member)')
			->select('user_id', 'user_first', 'user_last', 'user_group_member')
			->get();

		foreach ($operationsManager as $opsMgr) {
			$operationsManagerArr[$opsMgr->user_id] = $opsMgr->user_first . ' ' . $opsMgr->user_last;
		}

		// Ops Group Member Dropdown
		$opsGroupMember = Region::pluck('region_name', 'region_id');

		return view('users.create')->with('heading', 'Create New User')
			->with('btn_caption', 'Create User')
			->with('userGroups', $userGroups)
			->with('unitMembers', $unitMembers)
			->with('operationsManager', $operationsManagerArr)
			->with('opsGroupMember', $opsGroupMember);
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
			'username' => 'required|min:5|unique:users',
			'password' => 'required|min:5'
		]);

		$user = new User;
		$user->username = $request->username;
		$user->password = \Hash::make($request->password);
		$user->hashed_password = sha1($request->password);
		$user->hashed_pwd = base64_encode($request->password);
		$user->user_first = $request->user_first;
		$user->user_last = $request->user_last;
		$user->contact_number = $request->contact_number;
		$user->user_email = $request->user_email;
		$user->user_group_member = implode(',', (array)$request->user_group_member);
		$user->unit_member = implode(',', (array)$request->unit_member);
		$user->ops_mgr = implode(',', (array)$request->ops_mgr);
		$user->ops_group_member = implode(',', (array)$request->ops_group_member);
		$user->save();

		Session::flash('flash_message', 'User has been added successfully!'); //<--FLASH MESSAGE

		return redirect('/users');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		$operationsManagerArr = array();
		$user = User::find($id);

		$userPassword = base64_decode($user->hashed_pwd);

		// User Group Member Dropdown
		$userGroups = UserGroup::where('user_group_id', '<=', UserGroup::USER_GROUP_ADMIN)->pluck('user_group_name', 'user_group_id');

		if (Gate::allows('su-user-group')) {
			$userGroups = UserGroup::pluck('user_group_name', 'user_group_id');
		}

		$selectedUserGroupsArr = explode(',', $user->user_group_member);
		$selectedGroup = '';

		if (count($selectedUserGroupsArr) > 0 && !empty($selectedUserGroupsArr)) {
			$selectedGroup = UserGroup:: whereIn('user_group_id', $selectedUserGroupsArr)->pluck('user_group_id')->toArray();
		}

		$selectedUserGroups = $selectedGroup;
		$unitMembers = Unit::pluck('unit_name', 'unit_id');
		$selectedUnitMembersArr = explode(',', $user->unit_member);
		$selectedUnit = '';

		if (count($selectedUnitMembersArr) > 0 && !empty($selectedUnitMembersArr)) {
			$selectedUnit = Unit:: whereIn('unit_id', $selectedUnitMembersArr)->pluck('unit_id')->toArray();
		}

		$selectedUnitMembers = $selectedUnit;
		// Operations Manager Dropdown
		$operationsManager = User::whereRaw('FIND_IN_SET(2, user_group_member)')->select('user_id', 'user_first', 'user_last', 'user_group_member')->get();

		foreach ($operationsManager as $opsMgr) {
			$operationsManagerArr[$opsMgr->user_id] = $opsMgr->user_first . ' ' . $opsMgr->user_last;
		}

		$selectedOperationsManagerArr = explode(',', $user->ops_mgr);
		//////// For Selected Operation Manager
		$selectedOpMng = '';

		if (count($selectedOperationsManagerArr) > 0 && !empty($selectedOperationsManagerArr)) {
			$selectedOpMng = User:: whereIn('user_id', $selectedOperationsManagerArr)->pluck('user_id')->toArray();
		}

		$selectedOperationsManager = $selectedOpMng;

		// Ops Region Member Dropdown
		$opsGroupMember = Region::pluck('region_name', 'region_id');
		$selectedOpsGroupMemberArr = explode(',', $user->ops_group_member);

		$selectedRegion = '';

		if (count($selectedOpsGroupMemberArr) > 0 && !empty($selectedOpsGroupMemberArr)) {
			$selectedRegion = Region:: whereIn('region_id', $selectedOpsGroupMemberArr)->pluck('region_id')->toArray();
		}

		$selectedOpsGroupMember = $selectedRegion;

		return view('users.create', [
			'heading' => 'Edit User',
			'btn_caption' => 'Edit User',
			'user' => $user,
			'userPassword' => $userPassword,
			'userGroups' => $userGroups,
			'unitMembers' => $unitMembers,
			'operationsManager' => $operationsManagerArr,
			'opsGroupMember' => $opsGroupMember,
			'selectedUserGroups' => $selectedUserGroups,
			'selectedUnitMembers' => $selectedUnitMembers,
			'selectedOperationsManager' => $selectedOperationsManager,
			'selectedOpsGroupMember' => $selectedOpsGroupMember
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
			'password' => 'required|min:5'
		]);

		$user = User::find($id);
		$user->password = \Hash::make($request->password);
		$user->hashed_password = sha1($request->password);
		$user->hashed_pwd = base64_encode($request->password);
		$user->user_first = $request->user_first;
		$user->user_last = $request->user_last;
		$user->contact_number = $request->contact_number;
		$user->user_email = $request->user_email;
		$user->user_group_member = implode(',', (array)$request->user_group_member);
		$user->unit_member = implode(',', (array)$request->unit_member);
		$user->ops_mgr = implode(',', (array)$request->ops_mgr);
		$user->ops_group_member = implode(',', (array)$request->ops_group_member);
		$user->save();

		Session::flash('flash_message', 'User has been updated successfully!'); //<--FLASH MESSAGE

		return redirect('/users');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$userIds = explode(',', $id);
		foreach ($userIds as $userId) {
			$user = User::find($userId);
			$user->delete();
		}
		echo $id;
	}
}
