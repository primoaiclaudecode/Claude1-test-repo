<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\User;
use App\Region;
use App\OpsGroup;
use App\Supplier;
use Session;
use Yajra\Datatables\Datatables;

class RegionController extends Controller
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
        return view('regions.index');
    }

    // This function is returning json data to display in Grid
    public function json(Request $request)
    {
        $regions = Region::select([ 'region_id', 'region_id', 'region_name' ]);

        return Datatables::of($regions)
            ->setRowId(function ($region)
            {
                return 'tr_' . $region->region_id;
            })
            ->addColumn('checkbox', function ($region)
            {
                return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $region->region_id . '">';
            }, 0)
            ->addColumn('action', function ($region)
            {
                return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'regions/' . $region->region_id . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
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
        return view('regions.create', [
            'heading'     => 'Create New Region',
            'btn_caption' => 'Create Region',
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
            'region_name' => 'required|min:5',
        ]);
        try {
            $userId = $request->session()->all()['userId'];
            $region = new Region;
            $region->region_name = $request->region_name;
            $region->user_id = $userId;
            $region->save();
            Session::flash('flash_message', 'Region has been added successfully!'); //<--FLASH MESSAGE

            return redirect('/regions');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $region = Region::find($id);

        return view('regions.create', [
            'heading'     => 'Edit Region',
            'btn_caption' => 'Edit Region',
            'region'      => $region,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'region_name' => 'required|min:5',
        ]);

        $userId = $request->session()->all()['userId'];
        $region = Region::find($id);
        $region->region_name = $request->region_name;
        $region->user_id = $userId;
        $region->save();
        Session::flash('flash_message', 'Region has been updated successfully!'); //<--FLASH MESSAGE

        return redirect('/regions');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unitIds = explode(',', $id);
        foreach ($unitIds as $unitId) {
            $region = Region::find($unitId);
            $region->delete();
        }
        echo $id;
    }
}