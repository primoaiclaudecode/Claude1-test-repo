<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UserUnits;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\Unit;
use App\Register;
use Session;
use Yajra\Datatables\Datatables;

class RegisterController extends Controller
{
	use UserUnits;
	
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
        return view('registers.index');
    }

    // This function is returning json data to display in Grid
    public function json(Request $request)
    {
        $registers = Register::select(['reg_management_id', 'reg_management_id', 'unit_id', 'reg_number']);

        return Datatables::of($registers)
            ->setRowId(function($register) {
                return 'tr_'.$register->reg_management_id;
            })
            ->addColumn('checkbox', function ($register) {
                return '<input name="del_chks" type="checkbox" class="checkboxs" value="'.$register->reg_management_id.'">';
            }, 0)
            ->addColumn('action', function ($register) {
                return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'registers/'.$register->reg_management_id.'/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="'.csrf_token().'">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="'.csrf_token().'"><i class="fa fa-trash"></i></button>
                </form>';
            })
            ->editColumn('unit_id', function ($register) {
                return '<a href="units/'.$register->unit_id.'/edit">'.$register->unit['unit_name'].'</a>';
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
	    $units = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');
        return view('registers.create', [
            'heading' => 'Create New Register',
            'btn_caption' => 'Create Register',
            'units' => $units
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
            'unit_name' => 'required',
            'reg_number' => 'required'
        ]);
        
        $register = new Register;
        $register->unit_id = $request->unit_name;
        $register->unit_name = '';
        $register->reg_number = $request->reg_number;
        $register->save();
        Session::flash('flash_message','Register has been added successfully!'); //<--FLASH MESSAGE
        return redirect('/registers');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $register = Register::find($id);
        // Unit Name Dropdown
	    $units = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
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
        Session::flash('flash_message','Register has been updated successfully!'); //<--FLASH MESSAGE
        return redirect('/registers');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $registerIds = explode(',', $id);
        foreach($registerIds as $registerId)
        {
            $register = Register::find($registerId);    
            $register->delete();
        }
        echo $id;        
    }
}
