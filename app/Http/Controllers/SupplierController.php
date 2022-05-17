<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests;
use App\Supplier;
use Session;
use Yajra\Datatables\Datatables;

class SupplierController extends Controller
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
		return view('suppliers.index');
	}

	// This function is returning json data to display in Grid
	public function json(Request $request)
	{
		//echo "asdads";die;
		$suppliers = Supplier::select(['suppliers_id', 'suppliers_id', 'supplier_name', 'supplier_address', 'supplier_phone', 'supplier_fax',
			'supplier_details', 'sage_account_number', 'account_number', 'accounts_contact', 'accounts_email', 'remit_email']);
		//return $suppliers->count();
		return Datatables::of($suppliers)
			->setRowId(function ($supplier) {
				return 'tr_' . $supplier->suppliers_id;
			})
			->addColumn('checkbox', function ($supplier) {
				return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $supplier->suppliers_id . '">';
			}, 0)
			->addColumn('action', function ($supplier) {
				return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'suppliers/' . $supplier->suppliers_id . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
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
		return view('suppliers.create')
			->with('heading', 'Create New Supplier')
			->with('btn_caption', 'Create Supplier');
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
			'supplier_name' => 'required|min:5'
		]);

		$supplier = new Supplier;
		$supplier->supplier_name = $request->supplier_name;
		$supplier->supplier_address = $request->supplier_address;
		$supplier->supplier_details = $request->supplier_details;
		$supplier->supplier_phone = $request->supplier_phone;
		$supplier->supplier_fax = $request->supplier_fax;
		$supplier->sage_account_number = $request->sage_account_number;
		$supplier->account_number = $request->account_number;
		$supplier->accounts_contact = $request->accounts_contact;
		$supplier->accounts_email = $request->accounts_email;
		$supplier->remit_email = $request->remit_email;
		$supplier->suppliersunit = '';
		$supplier->save();
		Session::flash('flash_message', 'Supplier has been added successfully!'); //<--FLASH MESSAGE
		return redirect('/suppliers');
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
		$supplier = Supplier::find($id);

		return view('suppliers.create', [
			'heading' => 'Edit Supplier',
			'btn_caption' => 'Edit Supplier',
			'supplier' => $supplier
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
			'supplier_name' => 'required|min:5'
		]);

		$supplier = Supplier::find($id);
		$isCopy = false;
		
		// Copy supplier
		if ($request->has('copy_supplier')) {
			$supplier = new Supplier();
			$isCopy = true;
		}
		
		$supplier->supplier_name = $request->supplier_name;
		$supplier->supplier_address = $request->supplier_address;
		$supplier->supplier_details = $request->supplier_details;
		$supplier->supplier_phone = $request->supplier_phone;
		$supplier->supplier_fax = $request->supplier_fax;
		$supplier->sage_account_number = $request->sage_account_number;
		$supplier->account_number = $request->account_number;
		$supplier->accounts_contact = $request->accounts_contact;
		$supplier->accounts_email = $request->accounts_email;
		$supplier->remit_email = $request->remit_email;
		$supplier->suppliersunit = '';
		$supplier->save();

		// Redirect to the page with created supplier copy 
		if ($isCopy) {
			return redirect("/suppliers/$supplier->suppliers_id/edit");
		}
		
		Session::flash('flash_message', 'Supplier has been updated successfully!'); //<--FLASH MESSAGE
		
		return redirect('/suppliers');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$supplierIds = explode(',', $id);
		foreach ($supplierIds as $supplierId) {
			$supplier = Supplier::find($supplierId);
			$supplier->delete();
		}
		echo $id;
	}
}
