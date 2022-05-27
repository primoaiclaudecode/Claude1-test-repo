<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Http\Controllers\Traits\UserUnits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\Unit;
use App\Vending;
use Session;
use Yajra\Datatables\Datatables;

class VendingController extends Controller
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
        return view('vendings.index');
    }

    // This function is returning json data to display in Grid
    public function json(Request $request)
    {
        $vendings = DB::table('vend_management as vm')
            ->select(
                [
                    'vm.vend_management_id',
                    'vm.vend_management_id',
                    'u.unit_name',
                    'vm.vend_name',
                    'vm.machine_brand',
                    'vm.machine_contents',
                    'vm.unit_id',
                ]
            )
            ->leftJoin('units as u', 'u.unit_id', '=', 'vm.unit_id');

        return Datatables::of($vendings)
            ->setRowId(function ($vending)
            {
                return 'tr_' . $vending->vend_management_id;
            })
            ->addColumn('checkbox', function ($vending)
            {
                return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $vending->vend_management_id . '">';
            }, 0)
            ->addColumn('action', function ($vending)
            {
                return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'vendings/' . $vending->vend_management_id . '/edit\'"><i class="fa fa-edit"></i></button>
                <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
            })
            ->editColumn('unit_name', function ($vending)
            {
                return '<a href="units/' . $vending->unit_id . '/edit">' . $vending->unit_name . '</a>';
            })
            ->removeColumn('unit_id')
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
        $units = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        // Machine
        $machine_brand_arr = [];
        $machine_brand_arr['lucozade'] = 'Lucozade';
        $machine_brand_arr['nestle'] = 'Nestle';
        $machine_brand_arr['coca_cola'] = 'Coca-Cola';
        $machine_brand_arr['bewleys'] = 'Bewleys';
        $machine_brand_arr['cereal_biscuits'] = 'Tabletop Sweets';
        $machine_brand_arr['nestle_hot'] = 'Nestle Hot Drinks';
        $machine_brand_arr['java'] = 'Java';
        $machine_brand_arr['autobar'] = 'Autobar';
        $machine_brand_arr['corp_cater'] = 'Corporate Catering';

        $machine_contents_arr = [];
        $machine_contents_arr['cans'] = 'Cans';
        $machine_contents_arr['bottles'] = 'Bottles';
        $machine_contents_arr['cans_bottles'] = 'Cans & Bottles';
        $machine_contents_arr['choc_crisps'] = 'Chocolate + Crisps';
        $machine_contents_arr['coffee_tea'] = 'Coffee/Tea/Sugar/Cappuccino';
        $machine_contents_arr['tabletop_sweets'] = 'Cereals/Fruits/Pot Noodle/Choc/Biscuits/Sandwiches';
        $machine_contents_arr['nestle_hot'] = 'Nestle Hot Drinks';
        $machine_contents_arr['lucozade'] = 'Lucozade';
        $machine_contents_arr['coffee_bean'] = 'Coffee Bean (Bean to Cup)';

        // Currencies
        $currencies = Currency::pluck('currency_name', 'currency_id');

        return view('vendings.create', [
            'heading'              => 'Create New Vending Machine',
            'machine_brand_arr'    => $machine_brand_arr,
            'machine_contents_arr' => $machine_contents_arr,
            'btn_caption'          => 'Create Vending Machine',
            'units'                => $units,
            'currencies'           => $currencies,
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
            'unit_id'     => 'required',
            'vend_name'   => 'required',
            'currency_id' => 'required|integer',
        ]);

        $vending = new Vending;
        $vending->unit_id = $request->unit_id;
        $vending->unit_name = '';
        $vending->vend_name = $request->vend_name;
        $vending->machine_brand = $request->machine_brand;
        $vending->machine_contents = $request->machine_contents;
        $vending->currency_id = $request->currency_id;
        $vending->save();

        Session::flash('flash_message', 'Vending machine has been added successfully!'); //<--FLASH MESSAGE

        return redirect('/vendings');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $vending = Vending::find($id);

        // Unit Name Dropdown
        $units = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        // Machine
        $machine_brand_arr = [
            'lucozade'        => 'Lucozade',
            'nestle'          => 'Nestle',
            'coca_cola'       => 'Coca-Cola',
            'bewleys'         => 'Bewleys',
            'cereal_biscuits' => 'Tabletop Sweets',
            'nestle_hot'      => 'Nestle Hot Drinks',
            'java'            => 'Java',
            'autobar'         => 'Autobar',
            'corp_cater'      => 'Corporate Catering',
        ];

        $machine_contents_arr = [
            'cans'            => 'Cans',
            'bottles'         => 'Bottles',
            'cans_bottles'    => 'Cans & Bottles',
            'choc_crisps'     => 'Chocolate + Crisps',
            'coffee_tea'      => 'Coffee/Tea/Sugar/Cappuccino',
            'tabletop_sweets' => 'Cereals/Fruits/Pot Noodle/Choc/Biscuits/Sandwiches',
            'nestle_hot'      => 'Nestle Hot Drinks',
            'lucozade'        => 'Lucozade',
            'coffee_bean'     => 'Coffee Bean (Bean to Cup)',
        ];

        // Currencies
        $currencies = Currency::pluck('currency_name', 'currency_id');

        return view('vendings.create', [
            'heading'              => 'Edit Vending Machine',
            'btn_caption'          => 'Edit Vending Machine',
            'vending'              => $vending,
            'units'                => $units,
            'machine_brand_arr'    => $machine_brand_arr,
            'machine_contents_arr' => $machine_contents_arr,
            'currencies'           => $currencies,
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
            'unit_id'     => 'required',
            'vend_name'   => 'required',
            'currency_id' => 'required|integer',
        ]);

        $vending = Vending::find($id);
        $vending->unit_id = $request->unit_id;
        $vending->unit_name = '';
        $vending->vend_name = $request->vend_name;
        $vending->machine_brand = $request->machine_brand;
        $vending->machine_contents = $request->machine_contents;
        $vending->currency_id = $request->currency_id;
        $vending->save();

        Session::flash('flash_message', 'Vending machine has been updated successfully!'); //<--FLASH MESSAGE

        return redirect('/vendings');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $vendingIds = explode(',', $id);
        foreach ($vendingIds as $vendingId) {
            $vending = Vending::find($vendingId);
            $vending->delete();
        }
        echo $id;
    }
}
