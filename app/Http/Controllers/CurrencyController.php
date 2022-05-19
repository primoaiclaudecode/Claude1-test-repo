<?php

namespace App\Http\Controllers;

use App\Currency;
use Illuminate\Http\Request;

use App\Http\Requests;
use Session;
use Yajra\Datatables\Datatables;

class CurrencyController extends Controller
{
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 * 
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function index()
	{
		return view('currencies.index');
	}

	/**
	 * Show the form for creating a new resource.
	 * 
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function create()
	{

		return view('currencies.create', [
			'heading' => 'Create New Currency',
			'btn_caption' => 'Create Currency'
		]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
	 * 
	 * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'currency_name' => 'required|string',
			'currency_code' => 'required|string|max:3',
			'currency_symbol' => 'required|string|max:1',
			'is_default' => 'boolean'
		]);

		try {
			// Save currency
			$currency = new Currency();
			$currency->currency_name = $request->currency_name;
			$currency->currency_code = strtoupper($request->currency_code);
			$currency->currency_symbol = $request->currency_symbol;
			$currency->save();

			// Set currency as default
			if ($request->is_default == 1) {
				Currency::where('is_default', 1)
					->update(['is_default' => 0]);
				
				$currency->is_default = 1;
				$currency->save();
			}
			
			Session::flash('flash_message', 'Currency has been added successfully!'); //<--FLASH MESSAGE
			
			return redirect('/currencies');
		} catch (\Exception $e) {
			return redirect()->back()->withErrors($e->getMessage());
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * 
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function edit($id)
	{
		$currency = Currency::find($id);
		
		
		return view('currencies.create', [
			'heading' => 'Edit Currency',
			'btn_caption' => 'Edit Currency',
			'currency' => $currency
		]);
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param Request $request
	 * @param int $id
	 * 
	 * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
	 */
	public function update(Request $request, $id)
	{
		$this->validate($request, [
			'currency_name' => 'required|string',
			'currency_code' => 'required|string|max:3',
			'currency_symbol' => 'required|string|max:1',
			'is_default' => 'boolean'
		]);

		$currency = Currency::find($id);
		$currency->currency_name = $request->currency_name;
		$currency->currency_code = strtoupper($request->currency_code);
		$currency->currency_symbol = $request->currency_symbol;
		$currency->save();

		// Set currency as default
		if ($request->is_default == 1) {
			Currency::where('is_default', 1)
				->update(['is_default' => 0]);

			$currency->is_default = 1;
			$currency->save();
		}
		
		Session::flash('flash_message', 'Currency has been updated successfully!');
		
		return redirect('/currencies');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param string $id
	 * 
	 * @return string
	 */
	public function destroy($id)
	{
		$itemIds = explode(',', $id);

		Currency::destroy($itemIds);
		
		echo $id;
	}

	/**
	 * Get data for the currencies table
	 * 
	 * @return mixed
	 */
	public function json()
	{
		$currencies = Currency::select(
			[
				'currency_id',
				'currency_id',
				'currency_name',
				'currency_code',
				'currency_symbol',
				'is_default'
			]
		);

		return Datatables::of($currencies)
			->setRowId(function ($currency) {
				return 'tr_' . $currency->currency_id;
			})
			->addColumn('checkbox', function ($currency) {
				return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $currency->currency_id . '">';
			}, 0)
			->addColumn('action', function ($currency) {
				return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'currencies/' . $currency->currency_id . '/edit\'"><i class="fa fa-edit"></i></button> <form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
			})
			->editColumn('is_default', function ($currency) {
				$checked = $currency->is_default == 1 ? 'checked="checked"' : '';
				return '<input name="currency_id" type="radio" ' . $checked . ' value="' . $currency->currency_id . '">';
			})
			->make();
	}

	/**
	 * Get info about the currency
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function find(Request $request)
	{
		$currency = Currency::find($request->currency_id);

		if (is_null($currency)) {
			return response()->json([
				'currencyId' => 0,
				'currencySymbol' => ''
			]);
		}

		return response()->json([
			'currencyId' => $currency->currency_id,
			'currencySymbol' => $currency->currency_symbol
		]);
	}

	/**
	 * Set default currency
	 *
	 * @param Request $request
	 *
	 * @return void
	 */
	public function setDefault(Request $request)
	{
		Currency::where('is_default', 1)
			->update(['is_default' => 0]);

		Currency::where('currency_id', $request->currency_id)
			->update(['is_default' => 1]);
	}
	
}