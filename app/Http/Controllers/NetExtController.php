<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\NetExt;
use Session;
use Yajra\Datatables\Datatables;

class NetExtController extends Controller
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
        return view('netexts.index');
    }

    // This function is returning json data to display in Grid
    public function json(Request $request)
    {
        $netExts = NetExt::select(
            [
                'net_ext_ID',
                'net_ext_ID',
                'net_ext',
                'nominal_code',
                'cash_purch',
                'credit_purch',
                'vending_sales',
                'cost_of_sales',
            ]
        );

        return Datatables::of($netExts)
            ->setRowId(function ($netext)
            {
                return 'tr_' . $netext->net_ext_ID;
            })
            ->addColumn('checkbox', function ($netext)
            {
                return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $netext->net_ext_ID . '">';
            }, 0)
            ->addColumn('action', function ($netext)
            {
                return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'netexts/' . $netext->net_ext_ID . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
            })
            ->editColumn('cash_purch', function ($netext)
            {
                $checked_cash_purch = $netext->cash_purch == 1 ? 'checked="checked"' : '';

                return '<input name="cash_purch[]" type="checkbox" ' . $checked_cash_purch . ' class="cash_purch_chk" value="' . $netext->net_ext_ID . '">';
            })
            ->editColumn('credit_purch', function ($netext)
            {
                $checked_credit_purch = $netext->credit_purch == 1 ? 'checked="checked"' : '';

                return '<input name="credit_purch[]" type="checkbox" ' . $checked_credit_purch . ' class="credit_purch_chk" value="' . $netext->net_ext_ID . '">';
            })
            ->editColumn('vending_sales', function ($netext)
            {
                $checked_vending_sales = $netext->vending_sales == 1 ? 'checked="checked"' : '';

                return '<input name="vending_sales[]" type="checkbox" ' . $checked_vending_sales . ' class="vending_sales_chk" value="' . $netext->net_ext_ID . '">';
            })
            ->editColumn('cost_of_sales', function ($netext)
            {
                $checked_cost_of_sales = $netext->cost_of_sales == 1 ? 'checked="checked"' : '';

                return '<input name="cost_of_sales[]" type="checkbox" ' . $checked_cost_of_sales . ' class="cost_of_sales_chk" value="' . $netext->net_ext_ID . '">';
            })
            ->make();
    }

    public function cashCreditPurch(Request $request)
    {
        $success = false;

        if (is_array($request->cashPurch) && count($request->cashPurch) > 0) {
            \DB::table('nominal_codes')->update([ 'cash_purch' => 0 ]);
            \DB::table('nominal_codes')->whereIn('net_ext_ID', $request->cashPurch)->update([ 'cash_purch' => 1 ]);
            $success = true;
        }

        if (is_array($request->creditPurch) && count($request->creditPurch) > 0) {
            \DB::table('nominal_codes')->update([ 'credit_purch' => 0 ]);
            \DB::table('nominal_codes')->whereIn('net_ext_ID', $request->creditPurch)->update([ 'credit_purch' => 1 ]);
            $success = true;
        }

        if (is_array($request->vendingSales) && count($request->vendingSales) > 0) {
            \DB::table('nominal_codes')->update([ 'vending_sales' => 0 ]);
            \DB::table('nominal_codes')
                ->whereIn('net_ext_ID', $request->vendingSales)
                ->update([ 'vending_sales' => 1 ]);
            $success = true;
        }

        if (is_array($request->costOfSale) && count($request->costOfSale) > 0) {
            \DB::table('nominal_codes')->update([ 'cost_of_sales' => 0 ]);
            \DB::table('nominal_codes')->whereIn('net_ext_ID', $request->costOfSale)->update([ 'cost_of_sales' => 1 ]);
            $success = true;
        }

        $message = $success ? '<em> Action has been completed successfully</em>' : '';

        echo json_encode($message);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('netexts.create')->with('heading', 'Create New Net Extension')
            ->with('btn_caption', 'Create Net Extension');
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
            'net_ext'      => 'required',
            'nominal_code' => 'required',
        ]);

        $NetExt = new NetExt;
        $NetExt->net_ext = $request->net_ext;
        $NetExt->nominal_code = $request->nominal_code;
        $NetExt->cash_purch = $request->cash_purch ? $request->cash_purch : 0;
        $NetExt->credit_purch = $request->credit_purch ? $request->credit_purch : 0;
        $NetExt->vending_sales = $request->vending_sales ? $request->vending_sales : 0;
        $NetExt->save();

        Session::flash('flash_message', 'Net Extension has been added successfully!'); //<--FLASH MESSAGE

        return redirect('/netexts');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $netExt = NetExt::find($id);

        return view('netexts.create', [
            'heading'     => 'Edit Net Extension',
            'btn_caption' => 'Edit Net Extension',
            'netExt'      => $netExt,
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
            'net_ext'      => 'required',
            'nominal_code' => 'required',
        ]);

        $NetExt = netExt::find($id);
        $NetExt->net_ext = $request->net_ext;
        $NetExt->nominal_code = $request->nominal_code;
        $NetExt->cash_purch = $request->cash_purch ? $request->cash_purch : 0;
        $NetExt->credit_purch = $request->credit_purch ? $request->credit_purch : 0;
        $NetExt->vending_sales = $request->vending_sales ? $request->vending_sales : 0;
        $NetExt->save();

        Session::flash('flash_message', 'Net Extension has been updated successfully!'); //<--FLASH MESSAGE

        return redirect('/netexts');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $netExtIds = explode(',', $id);
        foreach ($netExtIds as $netExtId) {
            $netExt = NetExt::find($netExtId);
            $netExt->delete();
        }
        echo $id;
    }
}
