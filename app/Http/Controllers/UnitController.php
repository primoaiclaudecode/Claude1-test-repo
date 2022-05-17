<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Session;
use App\User;
use App\Unit;
use App\Region;
use App\OpsGroup;
use App\Supplier;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;
use App\Status;

class UnitController extends Controller
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
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
    public function index()
    {
	    $units = DB::table('units')
		    ->select(
			    [
				    'units.unit_id',
				    'units.unit_name',
				    'statuses.name as unit_status',
				    DB::raw('(SELECT GROUP_CONCAT(users.username) FROM users where FIND_IN_SET(users.user_id, units.ops_manager_user_id)) as operation_manager'),
				    'units.details',
				    'units.location',
				    'units.town',
				    'units.county',
				    'units.contact_number',
				    'units.email',
				    'units.head_count',
			    ]
		    )
		    ->leftJoin('statuses', 'statuses.id', '=', 'units.status_id')
		    ->orderBy('units.status_id', 'asc')
		    ->orderBy('units.unit_name', 'asc')
		    ->get();
    	
        return view(
        	'units.index',
	        [
	        	'units' => $units
	        ]
        );
    }
    
    // This function is returning json data to display in Grid
    public function json(Request $request)
    {
	    $units = DB::table('units')
		    ->select(
			    [
				    'units.unit_id',
				    'units.unit_id',
				    'units.unit_name',
				    'statuses.name as unit_status',
				    DB::raw('(SELECT GROUP_CONCAT(users.username) FROM users where FIND_IN_SET(users.user_id, units.ops_manager_user_id)) as operation_manager'),
				    'units.details',
				    'units.location',
				    'units.town',
				    'units.county',
				    'units.contact_number',
				    'units.email',
				    'units.head_count',
			    ]
		    )
		    ->leftJoin('statuses', 'statuses.id', '=', 'units.status_id');
	    
        return Datatables::of($units)
            ->setRowId(function($unit) {
                return 'tr_'.$unit->unit_id;
            })
            ->addColumn('checkbox', function ($unit) {
                return '<input name="del_chks" type="checkbox" class="checkboxs" value="'.$unit->unit_id.'">';
            }, 0)
            ->addColumn('action', function ($unit) {
                return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'units/'.$unit->unit_id.'/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="'.csrf_token().'">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="'.csrf_token().'"><i class="fa fa-trash"></i></button>
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
        // Operations Group Dropdown
        $opsGroup = Region::pluck('region_name', 'region_id');
        
        // Unit Manager Dropdown
        $unitManager = User::pluck('username', 'user_id');

	    // Operation Manager Dropdown
	    $operationManager = User::where('user_group_member', 2)->pluck('username', 'user_id');
        
        // Associated Suppliers Dropdown
        $associatedSuppliers = Supplier::select('suppliers_id', 'supplier_name')->orderBy('supplier_name', 'asc')->get();

	    // Statuses
	    $statuses = Status::where('type', Status::STATUS_TYPE_UNIT_MANAGER)->pluck('name', 'id');

        return view('units.create', [
            'heading' => 'Create New Unit',
            'btn_caption' => 'Create Unit',
            'opsGroup' => $opsGroup,
            'unitManager' => $unitManager,
	        'operationManager' => $operationManager,
            'associatedSuppliers' => $associatedSuppliers,
	        'statuses' => $statuses,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'unit_name' => 'required|min:5',
            'status' => 'required|integer'
        ]);
        
        $unit = new Unit;
        $unit->unit_name = $request->unit_name;
        $unit->details = $request->details;
        $unit->location = $request->location;
        $unit->town = $request->town;
        $unit->county = $request->county;
        $unit->contact_number = $request->contact_number;        
        $unit->email = $request->email;
        $unit->head_count = $request->head_count ? $request->head_count : 0;
        $unit->operations_group = implode(',', (array)$request->operations_group);
        $unit->unit_manager = implode(',', (array)$request->unit_manager);
	    $unit->ops_manager_user_id = implode(',', (array)$request->operation_manager);
        $unit->unitsuppliers = implode(',', (array)$request->unitsuppliers);
        $unit->users = '';
	    $unit->client_contact_name = $request->client_contact_name;
	    $unit->client_contact_email = $request->client_contact_email;
	    $unit->status_id = $request->status;
        $unit->save();
        
        Session::flash('flash_message','Unit has been added successfully!'); //<--FLASH MESSAGE
	    
        return redirect('/units');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $unit = Unit::find($id);

        // Operations Group Dropdown
        $opsGroup = Region::pluck('region_name', 'region_id');
        $selectedOpsGroupArr = explode(',', $unit->operations_group);
		
		$selectedRegion ='';
		if(count($selectedOpsGroupArr)>0 && !empty($selectedOpsGroupArr)){
			$selectedRegion = Region :: whereIn('region_id',$selectedOpsGroupArr)->pluck('region_id')->toArray();
		}	
		
		$selectedOpsGroup = $selectedRegion;

	    // Operation Manager Dropdown
	    $operationManager = User::where('user_group_member', 2)->pluck('username', 'user_id');
	    $selectedOperationManagerArr = explode(',', $unit->ops_manager_user_id);

	    $selectedOManager ='';
	    if(!empty($selectedOperationManagerArr) && count($selectedOperationManagerArr) > 0){
		    $selectedOManager = User :: whereIn('user_id',$selectedOperationManagerArr)->pluck('user_id')->toArray();
	    }
	    $selectedOperationManager = $selectedOManager;

	    // Unit Manager Dropdown
        $unitManager = User::pluck('username', 'user_id');
        $selectedUnitManagerArr = explode(',', $unit->unit_manager);

		$selectedUManager ='';
		if(count($selectedUnitManagerArr)>0 && !empty($selectedUnitManagerArr)){
			$selectedUManager = User :: whereIn('user_id',$selectedUnitManagerArr)->pluck('user_id')->toArray();
		}	
		$selectedUnitManager = $selectedUManager;		
        
		// Associated Users Table
        $associatedUsers = User::whereRaw("FIND_IN_SET($id, unit_member)")
                                    ->select('username', 'user_first', 'user_last', 'contact_number', 'user_email')
                                    ->orderBy('username', 'asc')
                                    ->get();

        // Associated Suppliers Dropdown
        $associatedSuppliers = Supplier::select('suppliers_id', 'supplier_name')->orderBy('supplier_name', 'asc')->get();
        $selectedAssociatedSuppliers = explode(',', $unit->unitsuppliers);

        // Associated Suppliers Table
        $unitsuppliersIds = explode(",", $unit->unitsuppliers);
        $associatedSuppliersDetails = Supplier::select('supplier_name', 'account_number', 'sage_account_number')
                                                    ->whereIn('suppliers_id', $unitsuppliersIds)
                                                    ->orderBy('supplier_name', 'asc')
                                                    ->get();

	    // Statuses
	    $statuses = Status::where('type', Status::STATUS_TYPE_UNIT_MANAGER)->pluck('name', 'id');
        
        return view('units.create', [
            'heading' => 'Edit Unit',
            'btn_caption' => 'Edit Unit',
            'unit' => $unit,
            'opsGroup' => $opsGroup,
            'unitManager' => $unitManager,
            'associatedSuppliers' => $associatedSuppliers,
            'selectedOpsGroup' => $selectedOpsGroup,
            'selectedUnitManager' => $selectedUnitManager,
            'associatedSuppliersDetails' => $associatedSuppliersDetails,
            'associatedUsers' => $associatedUsers,
            'selectedAssociatedSuppliers' => $selectedAssociatedSuppliers,
	        'operationManager' => $operationManager,
	        'selectedOperationManager' => $selectedOperationManager,
	        'statuses' => $statuses,
	        'selectedStatus' => $unit->status_id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {        
        $this->validate($request, [
            'unit_name' => 'required|min:5'
        ]);
        
        $unit = Unit::find($id);
        $unit->unit_name = $request->unit_name;
        $unit->details = $request->details;
        $unit->location = $request->location;
        $unit->town = $request->town;
        $unit->county = $request->county;
        $unit->contact_number = $request->contact_number;        
        $unit->email = $request->email;
        $unit->head_count = $request->head_count ? $request->head_count : 0;
        $unit->operations_group = implode(',', (array)$request->operations_group);
	    $unit->ops_manager_user_id = implode(',', (array)$request->operation_manager);
        $unit->unit_manager = implode(',', (array)$request->unit_manager);
        $unit->unitsuppliers = implode(',', (array)$request->unitsuppliers);
        $unit->users = '';
	    $unit->client_contact_name = $request->client_contact_name;
	    $unit->client_contact_email = $request->client_contact_email;
	    $unit->status_id = $request->status;
        $unit->save();
        
        Session::flash('flash_message','Unit has been updated successfully!');
        
        return redirect('/units');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unitIds = explode(',', $id);
        foreach($unitIds as $unitId)
        {
            $unit = Unit::find($unitId);    
            $unit->delete();
        }
        echo $id;
    }
}
