<?php

namespace App\Http\Controllers;

use App\Currency;
use App\NetExt;
use App\ReportHiddenColumn;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\TaxCode;
use Session;
use Yajra\Datatables\Datatables;

class TaxCodeController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('role:su');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{
		return view('taxcodes.index');
	}

	// This function is returning json data to display in Grid
	public function json(Request $request)
	{
		$taxCodes = TaxCode::select(
			[
				'tax_code_ID',
				'tax_code_ID',
				'tax_code_title',
				'tax_rate',
				'tax_code_display_rate',
				'cash_purch',
				'credit_purch',
				'credit_sales',
				'vending_sales',
			]
		);

		return Datatables::of($taxCodes)
			->setRowId(function ($taxcode) {
				return 'tr_' . $taxcode->tax_code_ID;
			})
			->addColumn('checkbox', function ($taxcode) {
				return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $taxcode->tax_code_ID . '">';
			}, 0)
			->addColumn('netExt', function ($taxcode) {

				return $taxcode->vending_sales == 1 ? '<a href="" onclick="showSettings(event, ' . $taxcode->tax_code_ID . ')">Net Ext</a>' : '';
			})
			->addColumn('action', function ($taxcode) {
				return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'taxcodes/' . $taxcode->tax_code_ID . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
			})
			->editColumn('cash_purch', function ($taxcode) {
				$checked_cash_purch = $taxcode->cash_purch == 1 ? 'checked="checked"' : '';
				return '<input name="cash_purch[]" type="checkbox" ' . $checked_cash_purch . ' class="cash_purch_chk" value="' . $taxcode->tax_code_ID . '">';
			})
			->editColumn('credit_purch', function ($taxcode) {
				$checked_credit_purch = $taxcode->credit_purch == 1 ? 'checked="checked"' : '';
				return '<input name="credit_purch[]" type="checkbox" ' . $checked_credit_purch . ' class="credit_purch_chk" value="' . $taxcode->tax_code_ID . '">';
			})
			->editColumn('credit_sales', function ($taxcode) {
				$checked_credit_sales = $taxcode->credit_sales == 1 ? 'checked="checked"' : '';
				return '<input name="credit_sales[]" type="checkbox" ' . $checked_credit_sales . ' class="credit_sales_chk" value="' . $taxcode->tax_code_ID . '">';
			})
			->editColumn('vending_sales', function ($taxcode) {
				$checked_vending_sales = $taxcode->vending_sales == 1 ? 'checked="checked"' : '';
				return '<input name="vending_sales[]" type="checkbox" ' . $checked_vending_sales . ' class="vending_sales_chk" value="' . $taxcode->tax_code_ID . '">';;
			})
			->make();
	}

	/**
	 * Apply tax codes
	 * 
	 * @param Request $request
	 */
	public function applyTaxCodes(Request $request)
	{
		$this->validate($request, [
			'taxCodes' => 'required|array'
		]);

		$currentVendingSales = TaxCode::where('vending_sales', 1)->get()->implode('tax_code_ID', '_');
		$currentCreditSales = TaxCode::where('credit_sales', 1)->get()->implode('tax_code_ID', '_');
		
		foreach ($request->taxCodes as $taxCodeType => $taxCodes) {
			if (count($taxCodes) === 0) {
				continue;	
			}
			
			TaxCode::query()->update(
					[
						$taxCodeType => 0
					]
				);

			TaxCode::whereIn('tax_code_ID', $taxCodes)
				->update(
					[
						$taxCodeType => 1
					]
				);
		}

		$updatedVendingSales = TaxCode::where('vending_sales', 1)->get()->implode('tax_code_ID', '_');
		$updatedCreditSales = TaxCode::where('credit_sales', 1)->get()->implode('tax_code_ID', '_');

		// Clear columns visibility for Vending/Summary Sales reports
		if ($currentVendingSales != $updatedVendingSales) {
			ReportHiddenColumn::whereIn('report_name', ['vending-sales', 'sales_summary'])->delete();
		}

		// Clear columns visibility for Credit Sales report
		if ($currentCreditSales != $updatedCreditSales) {
			ReportHiddenColumn::where('report_name', 'credit-sales')->delete();
		}
	}
	
	/**
	 * Get list of NetExt for the Vending Sales tax code
	 * 
	 * @param Request $request
	 * 
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function netExtSettings(Request $request)
	{
		$this->validate($request, [
			'tax_code_id' => 'required|numeric',
		]);

		// Tax code
		$taxCode = TaxCode::find($request->tax_code_id);

		// Vending sales net ext for current tax code
		$netExtList = $taxCode->vendingSaleTaxCodes;

		// List of all Net ext items available for the Vending sales
		$vendingSalesNetExt = NetExt::where('vending_sales', 1)->get();

		$vendingSalesGoods = [];

		foreach ($vendingSalesNetExt as $item) {
			$vendingSalesGoods[] = [
				'id' => $item['net_ext_ID'],
				'name' => ucfirst($item['net_ext']),
				'selected' => $netExtList->contains($item->net_ext_ID)
			];
		}

		return response()->json(
			[
				'taxCode' => $taxCode->tax_code_display_rate,
				'goods' => $vendingSalesGoods
			]
		);
	}

	/**
	 * Save list of NetExt for the Vending Sales tax code
	 * 
	 * @param Request $request
	 */
	public function saveNetExtSettings(Request $request)
	{
		$this->validate($request, [
			'tax_code_id' => 'required|numeric',
			'net_ext' => 'required|array',
		]);

		$taxCode = TaxCode::find($request->tax_code_id);

		$taxCode->vendingSaleTaxCodes()->sync($request->net_ext);

		// Clear columns visibility for Vending Sales report
		ReportHiddenColumn::whereIn('report_name', ['vending-sales', 'sales_summary'])->delete();
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create()
	{
		// List of all Net ext items available for the Vending sales
		$vendingSalesNetExt = NetExt::where('vending_sales', 1)->get();

		$vendingSalesGoods = [];

		foreach ($vendingSalesNetExt as $item) {
			$vendingSalesGoods[] = [
				'id' => $item['net_ext_ID'],
				'name' => ucfirst($item['net_ext']),
				'selected' => false
			];
		}
      $currencies = Currency::pluck('currency_name', 'currency_id');

      return view('taxcodes.create', [
          'heading'           => 'Create New Tax Code',
          'btn_caption'       => 'Create Tax Code',
          'vendingSalesGoods' => $vendingSalesGoods,
          'currencies'       => $currencies,
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
			'tax_code_title' => 'required|string|unique:tax_codes,tax_code_title',
			'tax_rate' => 'required|numeric',
			'tax_code_display_rate' => 'required|string',
			'currency_id' => 'required|numeric',
		]);

		$taxCode = new TaxCode;
		$taxCode->tax_code_title = $request->tax_code_title;
		$taxCode->tax_rate = $request->tax_rate;
		$taxCode->tax_code_display_rate = $request->tax_code_display_rate;
		$taxCode->cash_purch = $request->input('cash_purch', 0);
		$taxCode->credit_purch = $request->input('credit_purch', 0);
		$taxCode->credit_sales = $request->input('credit_sales', 0);
		$taxCode->vending_sales = $request->input('vending_sales', 0);
		$taxCode->currency_id = $request->input('currency_id', 1);
		$taxCode->save();

		// If Vending Sales is selected, save net ext for the current tax code
		if ($request->vending_sales == 1) {
			$taxCode->vendingSaleTaxCodes()->sync($request->net_ext);

			// Clear columns visibility for Vending Sales report
			ReportHiddenColumn::whereIn('report_name', ['vending-sales', 'sales_summary'])->delete();
		}

		// Clear columns visibility for Credit Sales report
		if ($request->credit_sales == 1) {
			ReportHiddenColumn::where('report_name', 'credit-sales')->delete();
		}
		
		Session::flash('flash_message', 'Tax Code has been added successfully!');

		return redirect('/taxcodes');
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id)
	{
		// Tax code
		$taxCode = TaxCode::find($id);

		// Vending sales net ext for current tax code
		$netExtList = $taxCode->vendingSaleTaxCodes;

		// List of all Net ext items available for the Vending sales
		$vendingSalesNetExt = NetExt::where('vending_sales', 1)->get();

		$vendingSalesGoods = [];

		foreach ($vendingSalesNetExt as $item) {
			$vendingSalesGoods[] = [
				'id' => $item['net_ext_ID'],
				'name' => ucfirst($item['net_ext']),
				'selected' => $netExtList->contains($item->net_ext_ID)
			];
		}
      $currencies = Currency::pluck('currency_name', 'currency_id');

		return view('taxcodes.create', [
			'heading' => 'Edit Tax Code',
			'btn_caption' => 'Edit Tax Code',
			'taxCode' => $taxCode,
			'vendingSalesGoods' => $vendingSalesGoods,
      'currencies'       => $currencies,
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int $id
	 * 
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'tax_code_title' => 'required',
			'tax_rate' => 'required',
			'tax_code_display_rate' => 'required',
      'currency_id' => 'required|numeric',
		]);

		$taxCode = TaxCode::find($id);

		$currentVendingSales = $taxCode->vending_sales;
		$currentCreditSales = $taxCode->credit_sales;

		// Set new values
		$taxCode->tax_code_title = $request->tax_code_title;
		$taxCode->tax_rate = $request->tax_rate;
		$taxCode->tax_code_display_rate = $request->tax_code_display_rate;
		$taxCode->cash_purch = $request->input('cash_purch', 0);
		$taxCode->credit_purch = $request->input('credit_purch', 0);
		$taxCode->credit_sales = $request->input('credit_sales', 0);
		$taxCode->vending_sales = $request->input('vending_sales', 0);
      $taxCode->currency_id = $request->input('currency_id', 1);
		$taxCode->save();

		// If Vending sales is selected, save net ext for the current tax code
		if ($request->vending_sales == 1) {
			$taxCode->vendingSaleTaxCodes()->sync($request->net_ext);
		}

		// Clear columns visibility for Vending Sales report
		if ($currentVendingSales != $request->vending_sales) {
			ReportHiddenColumn::whereIn('report_name', ['vending-sales', 'sales_summary'])->delete();
		}

		// Clear columns visibility for Credit Sales report
		if ($currentCreditSales != $request->credit_sales) {
			ReportHiddenColumn::where('report_name', 'credit-sales')->delete();
		}
		
		Session::flash('flash_message', 'Tax Code has been updated successfully!');

		return redirect('/taxcodes');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{
		$taxCodeIds = explode(',', $id);

		foreach ($taxCodeIds as $taxCodeId) {
			$taxCode = TaxCode::find($taxCodeId);
			$taxCode->delete();
		}
		
		echo $id;
	}
}
