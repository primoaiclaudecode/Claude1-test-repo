<?php

namespace App\Http\Controllers;

use App\BudgetType;
use App\ContactType;
use App\ContractType;
use App\CreditSaleGood;
use App\CustomerFeedback;
use App\Event;
use App\FeedbackType;
use App\File;
use App\Http\Controllers\Traits\UserUnits;
use App\Lodgement;
use App\OperationsScorecard;
use App\PhasedBudgetUnitRow;
use App\Region;
use App\Register;
use App\TradingAccount;
use App\UserGroup;
use App\VendingSaleGood;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Unit;
use App\NetExt;
use App\Vending;
use App\TaxCode;
use App\Supplier;
use App\Purchase;
use App\CashSales;
use Carbon\Carbon;
use App\UnitClosed;
use App\CreditSales;
use App\VendingSales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Mail\OperationScorecard as OperationScorecardEmail;


class SheetController extends Controller
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
		$this->middleware('role:operations')->only(
			'customerFeedback', 'customerFeedbackConfirmation', 'customerFeedbackPost',
			'operationsScorecard', 'operationsScorecardConfirmation', 'operationsScorecardPost'
		);

		$this->middleware('role:admin')->only('operationsScorecardEdit', 'operationsScorecardSave');
	}

	/**
	 * Deprecated!!!
	 * Replaced with checkUnitClose()
	 *
	 * @param Request $request
	 */
	public function unitCloseCheck(Request $request)
	{
		$closedUnitArr = array();
		$isClosed = 0;
		$unit_name = 0;
		if ($request->has('unit_name')) {
			$unit_name = $request->unit_name;
		} elseif ($request->has('unitName')) {
			$unit_name = $request->unitName;
		}
		$month1 = str_pad($request->month, 2, "0", STR_PAD_LEFT);
		$year = $request->year;
		$unitCloseData = UnitClosed:: getUnitClosedByID($unit_name, $month1, $year);
		if (!empty($unitCloseData) && count($unitCloseData) > 0) {
			if ($unitCloseData->closed == 1) {
				$isClosed = 1;
			}
		}
		/*
		  if($unit_name > 0)
		  {
			  $purchases = \DB::select(
				  "SELECT closed
					  FROM `purchases`
					  WHERE EXTRACT( MONTH FROM receipt_invoice_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM receipt_invoice_date ) = '".$request->year."'
					  AND unit_id='".$unit_name."'"
			  );
		  }
		  else
		  {
			  $purchases = \DB::select(
				  "SELECT closed
					  FROM `purchases`
					  WHERE EXTRACT( MONTH FROM receipt_invoice_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM receipt_invoice_date ) = '".$request->year."'"
			  );
		  }
  
		  if(isset($purchases[0]->closed) && $purchases[0]->closed == 1)
			  $isClosed = 1;
  
		  if($unit_name > 0)
		  {
			  $cashSales = \DB::select(
				  "SELECT closed
					  FROM `cash_sales`
					  WHERE EXTRACT( MONTH FROM sale_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM sale_date ) = '".$request->year."'
					  AND unit_id='".$unit_name."'"
			  );
		  }
		  else
		  {
			  $cashSales = \DB::select(
				  "SELECT closed
					  FROM `cash_sales`
					  WHERE EXTRACT( MONTH FROM sale_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM sale_date ) = '".$request->year."'"
			  );
		  }
  
		  if(isset($cashSales[0]->closed) && $cashSales[0]->closed == 1)
			  $isClosed = 1;
  
		  if($unit_name > 0)
		  {
			  $creditSales = \DB::select(
				  "SELECT closed
					  FROM `credit_sales`
					  WHERE EXTRACT( MONTH FROM sale_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM sale_date ) = '".$request->year."'
					  AND unit_id='".$unit_name."'"
			  );
		  }
		  else
		  {
			  $creditSales = \DB::select(
				  "SELECT closed
					  FROM `credit_sales`
					  WHERE EXTRACT( MONTH FROM sale_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM sale_date ) = '".$request->year."'"
			  );
		  }
  
		  if(isset($creditSales[0]->closed) && $creditSales[0]->closed == 1)
			  $isClosed = 1;
  
		  if($unit_name > 0)
		  {
			  $vendingSales = \DB::select(
				  "SELECT closed
					  FROM `vending_sales`
					  WHERE EXTRACT( MONTH FROM sale_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM sale_date ) = '".$request->year."'
					  AND unit_id='".$unit_name."'"
			  );
		  }
		  else
		  {
			  $vendingSales = \DB::select(
				  "SELECT closed
					  FROM `vending_sales`
					  WHERE EXTRACT( MONTH FROM sale_date ) = '".$request->month."'
					  AND EXTRACT( YEAR FROM sale_date ) = '".$request->year."'"
			  );
		  }
  
		  if(isset($vendingSales[0]->closed) && $vendingSales[0]->closed == 1)
			  $isClosed = 1;
		   */
		switch ($request->month) {
			case '01':
				$month = 'January';
				break;
			case '02':
				$month = 'February';
				break;
			case '03':
				$month = 'March';
				break;
			case '04':
				$month = 'April';
				break;
			case '05':
				$month = 'May';
				break;
			case '06':
				$month = 'June';
				break;
			case '07':
				$month = 'July';
				break;
			case '08':
				$month = 'August';
				break;
			case '09':
				$month = 'September';
				break;
			case '10':
				$month = 'October';
				break;
			case '11':
				$month = 'November';
				break;
			case '12':
				$month = 'December';
				break;
		}

		$closedUnitArr['closedUnitErrorMsg'] = $isClosed == 1 ? "This unit has been closed for <strong>$month</strong> / <strong>" . $request->year . "</strong>." : '';

		echo json_encode($closedUnitArr);
	}

	/**
	 * Check if unit is closed.
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function checkUnitClose(Request $request)
	{
		$unitId = $request->unit_id;
		$date = Carbon::parse($request->date);

		$unitClosed = UnitClosed::getUnitClosedByID($unitId, $date->format('m'), $date->format('Y'));

		return response()->json(
			[
				'closeDate' => !is_null($unitClosed) && $unitClosed->closed == 1 ? $date->format('F / Y') : ''
			]
		);
	}


	/**
	 * Cash/Credit purchase page
	 *
	 * @param Request $request
	 * @param string $purchType
	 * @param null $sheetId
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function purchase(Request $request, $purchType, $sheetId = NULL)
	{
		$unitId = Cookie::get('unitIdCookie', '');
		$supplier = '';
		$supplierId = '';
		$invoiceDate = Carbon::now()->format('d-m-Y');
		$invoiceNumber = '';
		$receiptDate = Carbon::now()->format('d-m-Y');
		$referenceNumber = '';
		$purchaseDetails = '';
		$purchaseItems = [];

		// Back from the confirm page
		if ($request->isMethod('post')) {
			$backData = unserialize($request->back_data);

			$unitId = $backData['unit_id'];
			$supplier = $backData['supplier'];
			$supplierId = $backData['supplier_id'];
			$invoiceDate = $backData['invoice_date'];
			$invoiceNumber = $backData['invoice_number'];
			$receiptDate = $backData['receipt_date'];
			$referenceNumber = $backData['reference_number'];
			$purchaseDetails = $backData['purchase_details'];
			$purchaseItems = $backData['purchase_items'];
		}

		// Request contain sheet id
		$purchases = Purchase::where('unique_id', $sheetId)->where('deleted', 0)->get();

		if (count($purchases) > 0) {
			$purchase = $purchases->first();

			$unitId = $purchase->unit_id;

			$supplier = $purchase->supplier;
			$supplierId = $purchase->suppliers_id;
			$purchaseDetails = $purchase->purchase_details;

			if ($purchase->purch_type === 'credit') {
				$invoiceDate = Carbon::parse($purchase->receipt_invoice_date)->format('d-m-Y');
				$invoiceNumber = $purchase->reference_invoice_number;
			} else {
				$receiptDate = Carbon::parse($purchase->receipt_invoice_date)->format('d-m-Y');
				$referenceNumber = $purchase->reference_invoice_number;
			}

			foreach ($purchases as $purchase) {
				$purchaseItems[] = [
					'netExt' => $purchase->net_ext_ID,
					'goods' => $purchase->goods,
					'tax' => $purchase->tax_code_id,
					'vat' => $purchase->vat,
					'gross' => $purchase->gross,
				];
			}
		}

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		// Suppliers
		$suppliers = [];

		$unit = Unit::find($unitId);

		if (!is_null($unit)) {
			$suppliers = Supplier::whereIn('suppliers_id', explode(',', $unit->unitsuppliers))->orderBy('supplier_name')->pluck('supplier_name', 'suppliers_id');

			if (count($suppliers) > 0) {
				$suppliers = [0 => 'Choose Supplier'] + $suppliers->toArray();
			}
		}

		// DB field
		$dbField = $purchType . '_purch';

		// Net exts
		$netExts = NetExt::where($dbField, 1)->pluck('net_ext', 'net_ext_ID');

		// Tax rates
		$taxCodeTitles = TaxCode::where($dbField, 1)->orderBy('tax_code_display_rate')->pluck('tax_code_display_rate', 'tax_code_ID');
		$taxCodeRates = TaxCode::where($dbField, 1)->pluck('tax_rate', 'tax_code_ID');

		return view(
			'sheets.purchases.index', [
				'sheetId' => $sheetId,
				'purchType' => $purchType,
				'userUnits' => $userUnits,
				'selectedUnit' => $unitId,
				'supplier' => $supplier,
				'suppliers' => $suppliers,
				'selectedSupplier' => $supplierId,
				'invoiceDate' => $invoiceDate,
				'invoiceNumber' => $invoiceNumber,
				'receiptDate' => $receiptDate,
				'referenceNumber' => $referenceNumber,
				'purchaseDetails' => $purchaseDetails,
				'purchaseItems' => $purchaseItems,
				'netExt' => $netExts,
				'taxCodeTitles' => $taxCodeTitles,
				'taxCodeRates' => $taxCodeRates
			]
		);
	}

	public function purchaseConfirmation(Request $request, $purchType)
	{
		$selectedUnit = Unit::find($request->unit_id);

		$supplierName = $request->supplier;

		if ($request->supplier_id && $request->supplier_id > 0) {
			$supplier = Supplier::find($request->supplier_id);

			$supplierName = $supplier->supplier_name;
		}

		$purchaseItems = [];

		foreach ($request->net_ext as $index => $value) {
			$purchaseItems[$index]['netExt'] = $value;
		}

		foreach ($request->goods as $index => $value) {
			$purchaseItems[$index]['goods'] = $value;
		}

		foreach ($request->tax_rate as $index => $value) {
			$purchaseItems[$index]['tax'] = $value;
		}

		foreach ($request->vat as $index => $value) {
			$purchaseItems[$index]['vat'] = $value;
		}

		foreach ($request->gross as $index => $value) {
			$purchaseItems[$index]['gross'] = $value;
		}

		$backData = [
			'unit_id' => $request->unit_id,
			'supplier' => $request->supplier,
			'supplier_id' => $request->supplier_id,
			'invoice_date' => $request->invoice_date,
			'invoice_number' => $request->invoice_number,
			'receipt_date' => $request->receipt_date,
			'reference_number' => $request->reference_number,
			'purchase_details' => $request->purchase_details,
			'purchase_items' => $purchaseItems,
		];

		return view(
			'sheets.purchases.confirmation', [
				'sheetId' => $request->sheet_id,
				'purchType' => $purchType,
				'unitId' => $selectedUnit->unit_id,
				'unitName' => $selectedUnit->unit_name,
				'supplierName' => $supplierName,
				'supplierId' => $request->supplier_id,
				'invoiceDate' => $request->invoice_date,
				'invoiceNumber' => $request->invoice_number,
				'receiptDate' => $request->receipt_date,
				'referenceNumber' => $request->reference_number,
				'purchaseDetails' => $request->purchase_details,
				'totalGoods' => $request->goods_total,
				'totalVat' => $request->vat_total,
				'totalGross' => $request->gross_total,
				'analysisGoodsTotal' => $request->analysis_goods_total,
				'purchaseItems' => serialize($purchaseItems),
				'backData' => serialize($backData)
			]
		);
	}

	public function purchasePost(Request $request, $purchType)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		DB::beginTransaction();

		try {
			$eventAction = '';

			if ($request->has('sheet_id')) {
				$eventAction = 'Edit ' . ucfirst($purchType) . ' Purchase';
				$uniqueId = $request->sheet_id;

				$purchase = Purchase::where('unique_id', $uniqueId)->where('deleted', 0)->first();

				$supervisor = $purchase->supervisor;
				$supervisorId = $purchase->supervisor_id;
				$createdAt = $purchase->time_inserted;

				// Delete previous purchase
				Purchase::where('unique_id', $uniqueId)->update(['deleted' => 1]);
			} else {
				$eventAction = 'Create ' . ucfirst($purchType) . ' Purchase';
				$lastPurchase = Purchase::orderBy('purchase_id', 'desc')->first();

				$supervisor = $userName;
				$supervisorId = $userId;
				$createdAt = Carbon::now();

				$uniqueId = !is_null($lastPurchase) ? time() . '_' . $lastPurchase->purchase_id : time() . '_1';
			}

			// Track action
			Event::trackAction($eventAction);

			$purchaseItems = unserialize($request->purchase_items);

			// Store data
			foreach ($purchaseItems as $purchaseItem) {
				$purchase = new Purchase;

				$purchase->unique_id = $uniqueId;
				$purchase->encrypted_unique_id = 0;
				$purchase->date = Carbon::now()->format('Y-m-d');
				$purchase->unit_id = $request->unit_id;
				$purchase->unit_name = $request->unit_name;
				$purchase->suppliers_id = $request->input('supplier_id', 0);
				$purchase->supplier = $request->supplier_name;
				$purchase->purchase_details = $request->purchase_details;

				// Created at/by
				$purchase->supervisor = $supervisor;
				$purchase->supervisor_id = $supervisorId;
				$purchase->time_inserted = $createdAt;

				// Updated at/by
				if ($request->has('sheet_id')) {
					$purchase->updated_by = $userId;
					$purchase->time_updated = Carbon::now();
				}

				if (isset($purchType) && $purchType == 'cash') {
					$purchase->purch_type = 'cash';
					$purchase->receipt_invoice_date = Carbon::parse($request->receipt_date)->format('Y-m-d');
					$purchase->reference_invoice_number = $request->reference_number;
				} else {
					$purchase->purch_type = 'credit';
					$purchase->receipt_invoice_date = Carbon::parse($request->invoice_date)->format('Y-m-d');
					$purchase->reference_invoice_number = $request->invoice_number;
				}

				// Items
				$purchase->net_ext_ID = $purchaseItem['netExt'];
				$purchase->goods = str_replace(',', '', $purchaseItem['goods']);
				$purchase->vat = str_replace(',', '', $purchaseItem['vat']);
				$purchase->gross = str_replace(',', '', $purchaseItem['gross']);

				// Tax code
				$taxCode = TaxCode::find($purchaseItem['tax']);
				$purchase->tax_code_id = $purchaseItem['tax'];
				$purchase->tax = $taxCode->tax_code_display_rate;
				$purchase->tax_code_title = $taxCode->tax_code_title;

				// Totals
				$purchase->goods_total = str_replace(',', '', $request->total_goods);
				$purchase->vat_total = str_replace(',', '', $request->total_vat);
				$purchase->gross_total = str_replace(',', '', $request->total_gross);

				// Other
				$purchase->goods_0 = 0;
				$purchase->goods_9 = 0;
				$purchase->goods_13 = 0;
				$purchase->goods_21 = 0;
				$purchase->goods_23 = 0;

				$purchase->vat_0 = 0;
				$purchase->vat_9 = 0;
				$purchase->vat_13 = 0;
				$purchase->vat_21 = 0;
				$purchase->vat_23 = 0;

				$purchase->gross_0 = 0;
				$purchase->gross_9 = 0;
				$purchase->gross_13 = 0;
				$purchase->gross_21 = 0;
				$purchase->gross_23 = 0;

				$purchase->stmnt_chk = 0;
				$purchase->stmnt_chk_user = 0;
				$purchase->opening_stock = 0;
				$purchase->closing_stock = 0;
				$purchase->close_temp = 0;
				$purchase->notes = '';
				$purchase->cash_sales_record_id = 0;
				$purchase->vis_by = 0;
				$purchase->date_vis = Carbon::now()->format('Y-m-d');

				$purchase->save();
			}

			DB::commit();

			Session::flash('flash_message', 'The form was successfully completed!');
		} catch (\Exception $e) {
			DB::rollBack();

			Session::flash('error_message', 'Error while saving form!');
		}

		return redirect('/sheets/purchases/' . $purchType)->cookie('unitIdCookie', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	public function suppliersJson(Request $request)
	{
		$unit = Unit::find($request->unit_id);

		$suppliers = [];

		if (!is_null($unit)) {
			$suppliers = Supplier::whereIn('suppliers_id', explode(',', $unit->unitsuppliers))
				->orderBy('supplier_name')
				->get(['suppliers_id', 'supplier_name']);

			if (count($suppliers) > 0) {
				$suppliers->prepend(
					[
						'suppliers_id' => 0,
						'supplier_name' => 'Choose Supplier'
					]
				);
			}
		}

		return response()->json($suppliers);
	}

	public function supplierInvoiceNoUnique(Request $request)
	{
		$sheetId = $request->sheet_id;

		$supplierInvoiceNo = Purchase::select(['reference_invoice_number'])
			->where('suppliers_id', $request->supplier)
			->where('reference_invoice_number', $request->invoice_number)
			->where('deleted', 0)
			->when($sheetId, function ($query) use ($sheetId) {
				return $query->where('unique_id', '!=', $sheetId);
			})
			->first();

		return response()->json(
			[
				'available' => is_null($supplierInvoiceNo)
			]
		);
	}

	/**
	 * Credit Sales Sheet.
	 */
	public function creditSales(Request $request, $sheetId = NULL)
	{
		$unitId = Cookie::get('unitIdCookieCreditSalesSheet', '');
		$saleDate = Carbon::now()->format('d-m-Y');
		$docketNumber = '';
		$creditReference = '';
		$costCentre = '';
		$saleItems = [];

		// Back from the confirm page
		if ($request->isMethod('post')) {
			$backData = unserialize($request->back_data);

			$unitId = $backData['unit_id'];
			$saleDate = $backData['sale_date'];;
			$docketNumber = $backData['docket_number'];;
			$creditReference = $backData['credit_reference'];;
			$costCentre = $backData['cost_centre'];;
			$saleItems = $backData['sale_items'];;
		}

		// Request contain sheet id
		$creditSales = CreditSales::where('credit_sales_id', $sheetId)->first();

		if (!is_null($creditSales)) {
			$unitId = $creditSales->unit_id;
			$docketNumber = $creditSales->docket_number;
			$saleDate = Carbon::parse($creditSales->sale_date)->format('d-m-Y');
			$creditReference = $creditSales->credit_reference;
			$costCentre = $creditSales->cost_centre;

			$creditSaleGoods = CreditSaleGood::where('credit_sales_id', $sheetId)->get();

			$saleItems = [];
			foreach ($creditSaleGoods as $creditSaleGood) {
				$saleItems[$creditSaleGood->tax_code_id] = $creditSaleGood->amount;
			}
		}

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		// Tax codes. Temporary exclude 13.5% tax code from Sheet, but show on Report
		$taxCodes = TaxCode::where('credit_sales', 1)
			->where('tax_code_ID', '!=', 3)
			->get();

		$saleTaxCodes = [];
		foreach ($taxCodes as $taxCode) {
			$saleTaxCodes[$taxCode->tax_code_ID] = [
				'tax' => $taxCode->tax_code_display_rate,
				'rate' => $taxCode->tax_rate,
				'gross' => isset($saleItems[$taxCode->tax_code_ID]) ? $saleItems[$taxCode->tax_code_ID] : 0,
			];
		}

		return view(
			'sheets.credit-sales.index', [
				'sheetId' => $sheetId,
				'userUnits' => $userUnits,
				'selectedUnit' => $unitId,
				'saleDate' => $saleDate,
				'docketNumber' => $docketNumber,
				'creditReference' => $creditReference,
				'costCentre' => $costCentre,
				'saleTaxCodes' => $saleTaxCodes
			]
		);
	}

	public function creditSalesConfirmation(Request $request)
	{
		$selectedUnit = Unit::find($request->unit_id);

		$saleItems = [];
		foreach ($request->tax_code as $index => $value) {
			$saleItems[$value] = isset($request->gross[$index]) ? $request->gross[$index] : 0;
		}

		$backData = [
			'unit_id' => $request->unit_id,
			'sale_date' => $request->sale_date,
			'docket_number' => $request->docket_number,
			'credit_reference' => $request->credit_reference,
			'cost_centre' => $request->cost_centre,
			'sale_items' => $saleItems
		];

		return view(
			'sheets.credit-sales.confirmation', [
				'sheetId' => $request->sheet_id,
				'unitId' => $request->unit_id,
				'unitName' => $selectedUnit->unit_name,
				'saleDate' => $request->sale_date,
				'docketNumber' => $request->docket_number,
				'creditReference' => $request->credit_reference,
				'costCentre' => $request->cost_centre,
				'grossTotal' => $request->gross_total,
				'goodsTotal' => $request->goods_total,
				'vatTotal' => $request->vat_total,
				'saleItems' => serialize($saleItems),
				'backData' => serialize($backData)
			]
		);
	}

	public function creditSalesPost(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		DB::beginTransaction();

		try {
			$creditSales = new CreditSales;
			$eventAction = 'Create Credit Sale';

			if ($request->has('sheet_id')) {
				$eventAction = 'Edit Credit Sale';
				$creditSaleId = $request->sheet_id;

				$creditSales = CreditSales::findOrFail($creditSaleId);

				// Delete previous sale goods
				CreditSaleGood::where('credit_sales_id', $creditSaleId)->delete();
			}

			// Track action
			Event::trackAction($eventAction);

			// Store data
			$creditSales->unit_name = $request->unit_name;
			$creditSales->unit_id = $request->unit_id;

			$creditSales->supervisor = $userName;
			$creditSales->supervisor_id = $userId;

			$creditSales->date = Carbon::now()->format('Y-m-d');
			$creditSales->sale_date = Carbon::parse($request->sale_date)->format('Y-m-d');

			$creditSales->docket_number = $request->docket_number;
			$creditSales->credit_reference = $request->credit_reference;
			$creditSales->cost_centre = $request->cost_centre;

			$creditSales->goods_total = (float)str_replace(',', '', $request->total_goods);
			$creditSales->vat_total = (float)str_replace(',', '', $request->total_vat);
			$creditSales->gross_total = (float)str_replace(',', '', $request->total_gross);

			$creditSales->cash_sales_record_id = 0;
			$creditSales->vis_by = 0;
			$creditSales->date_vis = Carbon::now()->format('Y-m-d');

			$creditSales->save();

			// Save items
			$saleItems = unserialize($request->sale_items);

			foreach ($saleItems as $taxCodeId => $amount) {
				$goodAmount = (float)str_replace(',', '', $amount);

				if ($goodAmount == 0) {
					continue;
				}

				$saleGood = new CreditSaleGood();

				$saleGood->credit_sales_id = $creditSales->credit_sales_id;
				$saleGood->amount = $goodAmount;
				$saleGood->tax_code_id = $taxCodeId;

				$saleGood->save();
			}

			DB::commit();

			Session::flash('flash_message', 'The form was successfully completed!');
		} catch (\Exception $e) {
			DB::rollBack();

			Session::flash('error_message', 'Error while saving form!');
		}

		return redirect('/sheets/credit-sales/')->cookie('unitIdCookieCreditSalesSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	/**
	 * Cash Sales Sheet.
	 */
	public function cashSales(Request $request, $sheetId = NULL)
	{
		// Request contain sheet id
		$cashSalesData = CashSales::where('cash_sales_id', $sheetId)->first();

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		$selectedUnit = !is_null($cashSalesData) ? $cashSalesData->unit_id : $request->unit_id;
		$unitName = !is_null($cashSalesData) ? $cashSalesData->unit_name : $request->unit_name;
		$selectedRegNumber = !is_null($cashSalesData) ? $cashSalesData->reg_management_id : $request->reg_number;
		$reg_number = !is_null($cashSalesData) ? $cashSalesData->reg_number : $request->reg_number;
		$saleDate = !is_null($cashSalesData) ? Carbon::parse($cashSalesData->sale_date)->format('d-m-Y') : $request->sale_date;
		$zNumber = !is_null($cashSalesData) ? $cashSalesData->z_number : $request->z_number;
		$zFood = !is_null($cashSalesData) ? $cashSalesData->z_food : $request->z_food;
		$zConfectFood = !is_null($cashSalesData) ? $cashSalesData->z_confect_food : $request->z_confect_food;
		$zFruit = !is_null($cashSalesData) ? $cashSalesData->z_fruit : $request->z_fruit;
		$zMinerals = !is_null($cashSalesData) ? $cashSalesData->z_minerals : $request->z_minerals;
		$zConfect = !is_null($cashSalesData) ? $cashSalesData->z_confect : $request->z_confect;
		$cashCount = !is_null($cashSalesData) ? $cashSalesData->cash_count : $request->cash_count;
		$creditCard = !is_null($cashSalesData) ? $cashSalesData->credit_card : $request->credit_card;
		$staffCards = !is_null($cashSalesData) ? $cashSalesData->staff_cards : $request->staff_cards;
		$cashCreditCard = !is_null($cashSalesData) ? $cashSalesData->cash_credit_card : $request->cash_credit_card;
		$variance = !is_null($cashSalesData) ? $cashSalesData->variance : $request->variance;
		$zRead = !is_null($cashSalesData) ? $cashSalesData->z_read : $request->z_read;
		$overRing = !is_null($cashSalesData) ? $cashSalesData->over_ring : $request->over_ring;
		$saleDetails = !is_null($cashSalesData) ? $cashSalesData->sale_details : $request->sale_details;

		return view(
			'sheets.cash-sales.index', [
				'todayDate' => Carbon::now()->format('d-m-Y'),
				'userUnits' => $userUnits,
				'selectedUnit' => isset($selectedUnit) ? $selectedUnit : $request->cookie('unitIdCookieCashSalesSheet'),
				'unitName' => $unitName,
				'saleDate' => $saleDate,
				'selectedRegNumber' => $selectedRegNumber,
				'zNumber' => $zNumber,
				'zFood' => $zFood,
				'zConfectFood' => $zConfectFood,
				'zFruit' => $zFruit,
				'zMinerals' => $zMinerals,
				'zConfect' => $zConfect,
				'cashCreditCard' => $cashCreditCard,
				'zRead' => $zRead,
				'variance' => $variance,
				'cashCount' => $cashCount,
				'creditCard' => $creditCard,
				'staffCards' => $staffCards,
				'overRing' => $overRing,
				'saleDetails' => $saleDetails,
				'reg_number' => $reg_number,
				'sheetId' => $request->has('sheet_id') ? $request->sheet_id : $sheetId
			]

		);
	}

	public function cashSalesConfimation(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		$reg_number = \App\Register::where('reg_management_id', $request->reg_number)->value('reg_number');

		// Petty Cash
		$purchIdsArr = array();
		$purchTotal = 0;
		$purchStr = '';

		for ($i = 1; $i < 30; $i++) {
			if ($request->has('cash_purch_chk_' . $i)) {
				$purchIdsArr[] = $request->input('purch_id_' . $i);
				$purchTotal = $purchTotal + str_replace(',', '', $request->input('cash_purch_chk_' . $i));

				$purchStr .= "<input type ='hidden' name='purch_id_" . $i . "' value='" . $request->input('purch_id_' . $i) . "' />";
				$purchStr .= "<input type ='hidden' name='cash_purchase' value='" . $purchTotal . "' />";
			}
		}

		session(['purchIdsArrSession' => $purchIdsArr]);

		// Credit Sales
		$creditSalesIdsArr = array();
		$creditSalesTotal = 0;
		$creditSalesStr = '';

		for ($i = 1; $i < 30; $i++) {
			if ($request->has('credit_sales_chk_' . $i)) {
				$creditSalesIdsArr[] = $request->input('credit_sales_id_' . $i);
				$creditSalesTotal = $creditSalesTotal + str_replace(',', '', $request->input('credit_sales_chk_' . $i));

				$creditSalesStr .= "<input type ='hidden' name='credit_sales_id_" . $i . "' value='" . $request->input('credit_sales_id_' . $i) . "' />";
				$creditSalesStr .= "<input type ='hidden' name='credit_sale' value='" . $creditSalesTotal . "' />";
			}
		}

		session(['creditSalesIdsArrSession' => $creditSalesIdsArr]);

		return view(
			'sheets.cash-sales.confirmation', [
				'userId' => $userId,
				'userName' => $userName,
				'unitId' => $request->unit_name,
				'unitName' => $request->hidden_unit_name,
				'regNumber' => $reg_number,
				'selectedRegNumber' => $request->reg_number,
				'saleDate' => $request->sale_date,
				'zNumber' => $request->z_number,
				'zFood' => $request->z_food,
				'zConfectFood' => $request->z_confect_food,
				'zFruit' => $request->z_fruit,
				'zMinerals' => $request->z_minerals,
				'zConfect' => $request->z_confect,
				'cashCreditCard' => $request->cash_credit_card,
				'zRead' => $request->z_read,
				'variance' => $request->variance,
				'cashPurchase' => $purchTotal,
				'creditSales' => $creditSalesTotal,
				'cashCount' => $request->cash_count,
				'creditCard' => $request->credit_card,
				'staffCards' => $request->staff_cards,
				'overRing' => $request->over_ring,
				'saleDetails' => $request->sale_details,
				'sheetId' => $request->has('sheet_id') ? $request->sheet_id : ''
			]
		);
	}

	public function cashSalesPost(Request $request)
	{
		$cashSales = new CashSales;
		$eventAction = 'Create Cash Sale';

		if ($request->has('sheet_id')) {
			$eventAction = 'Edit Cash Sale';
			$cash_sales_id = $request->sheet_id;

			$cashSales = CashSales::findOrFail($cash_sales_id);
			$cashSales->cash_sales_id = $request->sheet_id;
		}

		// Track action
		Event::trackAction($eventAction);

		// Store data
		$z_read = preg_replace('/[^\d\.]/', '', $request->z_read);
		$cash_count = preg_replace('/[^\d\-\.]/', '', $request->cash_count);
		$variance = preg_replace('/[^\d\.]/', '', $request->variance);
		$cash_purchase = preg_replace('/[^\d\.]/', '', $request->cash_purchase);
		$credit_sale = preg_replace('/[^\d\.]/', '', $request->credit_sale);
		$over_ring = preg_replace('/[^\d\.]/', '', $request->over_ring);
		$z_food = preg_replace('/[^\d\.]/', '', $request->z_food);
		$z_fruit = preg_replace('/[^\d\.]/', '', $request->z_fruit);
		$z_minerals = preg_replace('/[^\d\.]/', '', $request->z_minerals);
		$z_confect = preg_replace('/[^\d\.]/', '', $request->z_confect);
		$credit_card = preg_replace('/[^\d\.]/', '', $request->credit_card);
		$z_confect_food = preg_replace('/[^\d\.]/', '', $request->z_confect_food);
		$cash_credit_card = preg_replace('/[^\d\.]/', '', $request->cash_credit_card);
		$staff_cards = preg_replace('/[^\d\.]/', '', $request->staff_cards);

		if ($request->has('unit_name_1')) {
			$unit_name = $request->unit_name_1;
		} else {
			$unit_name = $request->unit_name;
		}

		if ($request->has('reg_number_1')) {
			$reg_number = $request->reg_number_1;
		} else {
			$reg_number = $request->reg_number;
		}

		$cashSales->date = Carbon::now()->format('Y-m-d');
		$cashSales->unit_name = $unit_name;
		$cashSales->reg_number = $reg_number;
		$cashSales->z_number = $request->z_number;
		$cashSales->sale_date = Carbon::createFromFormat('d-m-Y', $request->sale_date)->format('Y-m-d');
		$cashSales->z_read = $z_read;
		$cashSales->cash_count = $cash_count;
		$cashSales->variance = $variance;
		$cashSales->cash_purchase = $cash_purchase;
		$cashSales->credit_sale = $credit_sale;
		$cashSales->over_ring = $over_ring;
		$cashSales->z_food = $z_food;
		$cashSales->z_fruit = $z_fruit;
		$cashSales->z_minerals = $z_minerals;
		$cashSales->z_confect = $z_confect;
		$cashSales->sale_details = $request->sale_details;
		$cashSales->credit_card = $credit_card;
		$cashSales->z_confect_food = $z_confect_food;
		$cashSales->cash_credit_card = $cash_credit_card;
		$cashSales->cash_purchases_id = implode(',', Session::get('purchIdsArrSession'));
		$cashSales->credit_sales_id = implode(',', Session::get('creditSalesIdsArrSession'));
		$cashSales->unit_id = $request->unit_id;
		$cashSales->reg_management_id = $request->reg_management_id;
		$cashSales->staff_cards = $staff_cards;

		if ($request->has('sheet_id') && $request->sheet_id > 0) {
			$cashSales->updated_by = session()->get('userId');
			$cashSales->updated_at = Carbon::now()->format('Y-m-d H:i:s');
		} else {
			$cashSales->created_by = session()->get('userId');
			$cashSales->supervisor_id = $request->supervisor_id;
			$cashSales->supervisor = $request->supervisor_name;
		}

		$save = $cashSales->save();
		$lastInsertId = DB::getPdo()->lastInsertId();

		if ($lastInsertId > 0) {
			\DB::statement('UPDATE purchases SET cash_sales_record_id = "' . $lastInsertId . '" where unique_id IN ("' . implode('", "', Session::get('purchIdsArrSession')) . '")');
		}

		if ($save && Session::has('purchIdsArrSession')) {
			\DB::statement('UPDATE purchases SET cash_sale_vis = 0, vis_by = "", date_vis = "0000-00-00" where unique_id IN ("' . implode('", "', Session::get('purchIdsArrSession')) . '")');
		}

		if ($save && Session::has('creditSalesIdsArrSession')) {
			$credit_sales = array(
				'cash_sale_vis' => 0,
				'vis_by' => '',
				'date_vis' => '0000-00-00'
			);
			$session_credit_sale = Session::get('creditSalesIdsArrSession');
			if (count($session_credit_sale) > 0 && !empty($session_credit_sale)) {
				foreach ($session_credit_sale as $csVal) {
					if ($csVal > 0) {
						DB::table('credit_sales')->where('credit_sales_id', $csVal)->where('cash_sale_vis', 1)->update($credit_sales);
					}
				}
			}
		}

		session()->forget(['purchIdsArrSession', 'creditSalesIdsArrSession']);

		Session::flash('flash_message', 'The form was successfully completed!'); //<--FLASH MESSAGE

		return redirect('/sheets/cash-sales/')->cookie('unitIdCookieCashSalesSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	/**
	 * Lodgement Sheet.
	 */
	public function lodgements(Request $request, $lodgementId = NULL)
	{
		$unitId = Cookie::get('unitIdCookieLodgementSheet', '');
		$date = Carbon::now()->format('d-m-Y');
		$cash = 0;
		$coin = 0;
		$slipNumber = '';
		$bagNumber = '';
		$remarks = '';
		$selectedCashSales = [];
		$selectedVendingSales = [];

		// Back from the confirm page
		if ($request->isMethod('post')) {
			$backData = unserialize($request->back_data);

			$unitId = $backData['unit_id'];
			$date = $backData['date'];
			$cash = $backData['cash'];
			$coin = $backData['coin'];
			$slipNumber = $backData['slip_number'];
			$bagNumber = $backData['bag_number'];
			$remarks = $backData['remarks'];
			$selectedCashSales = $backData['selected_cash_sales'];
			$selectedVendingSales = $backData['selected_vending_sales'];
		}

		// Request contain sheet id
		$lodgement = Lodgement::where('lodgement_id', $lodgementId)->first();

		if (!is_null($lodgement)) {
			$unitId = $lodgement->unit_id;;
			$date = Carbon::parse($lodgement->date)->format('d-m-Y');
			$cash = $lodgement->cash;
			$coin = $lodgement->coin;
			$slipNumber = $lodgement->slip_number;
			$bagNumber = $lodgement->bag_number;
			$remarks = $lodgement->remarks;

			$selectedCashSales = CashSales::where('lodgement_id', $lodgementId)->pluck('cash_sales_id')->toArray();
			$selectedVendingSales = VendingSales::where('lodgement_id', $lodgementId)->pluck('vending_sales_id')->toArray();
		}

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		return view(
			'sheets.lodgements.index', [
				'lodgementId' => $lodgementId,
				'userUnits' => $userUnits,
				'selectedUnit' => $unitId,
				'date' => $date,
				'cash' => $cash,
				'coin' => $coin,
				'slipNumber' => $slipNumber,
				'bagNumber' => $bagNumber,
				'remarks' => $remarks,
				'selectedCashSales' => implode(',', $selectedCashSales),
				'selectedVendingSales' => implode(',', $selectedVendingSales)
			]
		);
	}

	public function lodgementsConfirmation(Request $request)
	{
		// Unit
		$selectedUnit = Unit::find($request->unit_id);

		// Cash Sales
		$cashSales = $request->input('cash_sales', []);
		$vendingSales = $request->input('vending_sales', []);

		$backData = [
			'unit_id' => $request->unit_id,
			'date' => $request->date,
			'cash' => $request->cash,
			'coin' => $request->coin,
			'slip_number' => $request->slip_number,
			'bag_number' => $request->bag_number,
			'remarks' => $request->remarks,
			'selected_cash_sales' => $cashSales,
			'selected_vending_sales' => $vendingSales,
		];

		return view(
			'sheets.lodgements.confirmation', [
				'lodgementId' => $request->lodgement_id,
				'unitId' => $request->unit_id,
				'unitName' => $selectedUnit->unit_name,
				'date' => $request->date,
				'cash' => $request->date,
				'coin' => $request->coin,
				'slipNumber' => $request->slip_number,
				'bagNumber' => $request->bag_number,
				'remarks' => $request->remarks,
				'total' => $request->total,
				'selectedCashSales' => serialize($cashSales),
				'selectedVendingSales' => serialize($vendingSales),
				'backData' => serialize($backData)
			]
		);
	}

	public function lodgementsPost(Request $request)
	{
		$userId = session()->get('userId');

		DB::beginTransaction();

		try {
			if ($request->has('lodgement_id')) {
				// Edit Lodgement
				$eventAction = 'Edit Lodgement';

				$lodgementId = $request->lodgement_id;

				$lodgement = Lodgement::findOrFail($lodgementId);

				$lodgement->updated_by = $userId;

				// Detach lodgement from Cash Sales
				CashSales::where('lodgement_id', $lodgementId)
					->update(
						[
							'lodgement_id' => null
						]
					);

				// Detach lodgement from Vending Sales
				VendingSales::where('lodgement_id', $lodgementId)
					->update(
						[
							'lodgement_id' => null
						]
					);
			} else {
				// Create Lodgement
				$eventAction = 'Create Lodgement';

				$lodgement = new Lodgement();

				$lodgement->created_by = $userId;
			}

			// Track action
			Event::trackAction($eventAction);

			$cash = preg_replace('/[^\d\.]/', '', $request->cash);
			$coin = preg_replace('/[^\d\.]/', '', $request->coin);

			$lodgement->unit_id = $request->unit_id;
			$lodgement->date = Carbon::parse($request->date)->format('Y-m-d');
			$lodgement->cash = $cash;
			$lodgement->coin = $coin;
			$lodgement->slip_number = $request->slip_number;
			$lodgement->bag_number = $request->bag_number;
			$lodgement->remarks = $request->remarks;

			$lodgement->save();

			// Update Cash Sales with Lodgement ID
			$cashSales = unserialize($request->selected_cash_sales);
			foreach ($cashSales as $cashSaleId) {
				CashSales::where('cash_sales_id', $cashSaleId)
					->update(
						[
							'lodgement_id' => $lodgement->lodgement_id
						]
					);
			}

			// Update Vending Sales with Lodgement ID
			$vendingSales = unserialize($request->selected_vending_sales);
			foreach ($vendingSales as $vendingSaleId) {
				VendingSales::where('vending_sales_id', $vendingSaleId)
					->update(
						[
							'lodgement_id' => $lodgement->lodgement_id
						]
					);
			}

			DB::commit();

			Session::flash('flash_message', 'The form was successfully completed!');
		} catch (\Exception $e) {
			DB::rollBack();

			Session::flash('error_message', 'Error while saving form!');
		}

		return redirect('/sheets/lodgements/')->cookie('unitIdCookieLodgementSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	public function lodgementSales(Request $request)
	{
		$unitId = $request->input('unit_id', 0);
		$lodgementId = $request->input('lodgement_id', 0);

		$cashSalesList = [];
		$cashSales = CashSales::where('unit_id', $unitId)
			->whereDate('sale_date', '>=', Carbon::now()->subMonth())
			->whereNull('lodgement_id')
			->orWhere('lodgement_id', $lodgementId)
			->orderBy('sale_date', 'desc')
			->get(
				[
					'cash_sales_id',
					'sale_date',
					'cash_count'
				]
			);

		foreach ($cashSales as $cashSale) {
			$cashSalesList[] = [
				'id' => $cashSale->cash_sales_id,
				'title' => $cashSale->cash_count . ' - ' . Carbon::parse($cashSale->sale_date)->format('d-m-Y')
			];
		}

		$vendingSalesList = [];
		$vendingSales = VendingSales::where('unit_id', $unitId)
			->whereDate('sale_date', '>=', Carbon::now()->subMonth())
			->whereNull('lodgement_id')
			->orWhere('lodgement_id', $lodgementId)
			->orderBy('sale_date', 'desc')
			->get(
				[
					'vending_sales_id',
					'sale_date',
					'total'
				]
			);

		foreach ($vendingSales as $vendingSale) {
			$vendingSalesList[] = [
				'id' => $vendingSale->vending_sales_id,
				'title' => $vendingSale->total . ' - ' . Carbon::parse($vendingSale->sale_date)->format('d-m-Y')
			];
		}

		return response()->json(
			[
				'cashSales' => $cashSalesList,
				'vendingSales' => $vendingSalesList
			]
		);
	}

	/**
	 * Vending Sales Sheet.
	 */
	public function vendingSales(Request $request, $sheetId = NULL)
	{
		$unitId = Cookie::get('unitIdCookieVendingSalesSheet', '');
		$saleDate = Carbon::now()->format('d-m-Y');
		$vendMachineId = 0;
		$regNumberId = 0;
		$opening = 0;
		$closing = 0;
		$cash = 0;
		$tillNumber = '';
		$zRead = '';
		$goodItems = [];
		$total = 0;

		// Back from the confirm page
		if ($request->isMethod('post')) {
			$backData = unserialize($request->back_data);

			$unitId = $backData['unit_id'];
			$saleDate = $backData['sale_date'];
			$vendMachineId = $backData['vend_machine_id'];
			$regNumberId = $backData['till_number_id'];
			$opening = $backData['opening'];
			$closing = $backData['closing'];
			$cash = $backData['cash'];
			$zRead = $backData['z_read'];
			$goodItems = $backData['good_items'];
		}

		// Request contain sheet id
		$vendingSale = VendingSales::where('vending_sales_id', $sheetId)->first();

		if (!is_null($vendingSale)) {
			$unitId = $vendingSale->unit_id;
			$saleDate = Carbon::parse($vendingSale->date)->format('d-m-Y');
			$vendMachineId = $vendingSale->vend_id;
			$regNumberId = $vendingSale->till_number_id;
			$opening = $vendingSale->opening;
			$closing = $vendingSale->closing;
			$cash = $vendingSale->cash;
			$zRead = $vendingSale->z_read;

			$vendingSaleGoods = VendingSaleGood::where('vending_sales_id', $sheetId)->get();

			$goodItems = [];
			foreach ($vendingSaleGoods as $vendingSaleGood) {
				$goodItems[$vendingSaleGood->net_ext_id] = [
					'amount' => $vendingSaleGood->amount,
					'tax' => $vendingSaleGood->tax_code_id
				];
			}
		}

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		// Vending machines + Reg number
		$vendMachines = Vending::where('unit_id', $unitId)
			->orderBy('vend_name')
			->pluck('vend_name', 'vend_management_id');

		$machines = [0 => 'Choose Machine'] + $vendMachines->toArray();

		$regNumbers = Register::where('unit_id', $unitId)
			->orderBy('reg_number')
			->get(['reg_management_id', 'reg_number']);

		// Goods
		$taxCodes = TaxCode::with('vendingSaleTaxCodes')->where('vending_sales', 1)->get();

		$goods = [];
		foreach ($taxCodes as $taxCode) {
			foreach ($taxCode->vendingSaleTaxCodes as $netExtItem) {
				if (!isset($goods[$netExtItem->net_ext_ID])) {
					$goods[$netExtItem->net_ext_ID] = [
						'name' => ucfirst($netExtItem->net_ext),
						'amount' => isset($goodItems[$netExtItem->net_ext_ID]) ? $goodItems[$netExtItem->net_ext_ID]['amount'] : 0,
						'taxCode' => isset($goodItems[$netExtItem->net_ext_ID]) ? $goodItems[$netExtItem->net_ext_ID]['tax'] : 0,
						'taxCodes' => []
					];
				}

				$goods[$netExtItem->net_ext_ID]['taxCodes'][] = [
					'id' => $taxCode->tax_code_ID,
					'title' => $taxCode->tax_code_display_rate,
				];
			}
		}

		return view(
			'sheets.vending-sales.index', [
				'sheetId' => $sheetId,
				'userUnits' => $userUnits,
				'selectedUnit' => $unitId,
				'saleDate' => $saleDate,
				'vendMachines' => $machines,
				'selectedVendMachine' => $vendMachineId,
				'regNumbers' => $regNumbers,
				'selectedRegNumber' => $regNumberId,
				'opening' => $opening,
				'closing' => $closing,
				'selectedTillNumber' => $tillNumber,
				'zRead' => $zRead,
				'cash' => $cash,
				'goods' => $goods,
				'total' => $total
			]
		);
	}

	public function vendingSalesConfirmation(Request $request)
	{
		$selectedUnit = Unit::find($request->unit_id);

		// Vending machine
		$machine = '';
		if ($request->vend_machine_id && $request->vend_machine_id > 0) {
			$vendMachine = Vending::find($request->vend_machine_id);

			$machine = $vendMachine->vend_name;
		}

		// Reg number
		$regNumber = '';
		if ($request->till_number_id && $request->till_number_id > 0) {
			$reg = Register::find($request->till_number_id);

			$regNumber = $reg->reg_number;
		}

		// Goods
		$goods = [];
		$taxes = [];
		$taxCodes = TaxCode::where('vending_sales', 1)->pluck('tax_code_display_rate', 'tax_code_ID');

		foreach ($request->good_id as $index => $value) {
			$goods[$value] = [
				'amount' => isset($request->good_amount[$index]) ? str_replace(',', '', $request->good_amount[$index]) : 0,
				'tax' => isset($request->good_tax_rate[$index]) ? $request->good_tax_rate[$index] : 0,
			];
		}

		foreach ($request->good_tax_rate as $index => $value) {
			if ($value == 0) {
				continue;
			}

			if (!isset($taxes[$value])) {
				$taxes[$value] = [
					'title' => isset($taxCodes[$value]) ? $taxCodes[$value] : '',
					'amount' => 0
				];
			}

			$taxes[$value]['amount'] += isset($request->good_amount[$index]) ? str_replace(',', '', $request->good_amount[$index]) : 0;
		}

		$backData = [
			'unit_id' => $request->unit_id,
			'sale_date' => $request->sale_date,
			'vend_machine_id' => $request->vend_machine_id,
			'till_number_id' => $request->till_number_id,
			'opening' => $request->opening,
			'closing' => $request->closing,
			'cash' => $request->cash,
			'z_read' => $request->z_read,
			'good_items' => $goods
		];

		return view(
			'sheets.vending-sales.confirmation', [
				'sheetId' => $request->sheet_id,
				'unitId' => $request->unit_id,
				'unitName' => $selectedUnit->unit_name,
				'saleDate' => $request->sale_date,
				'vendName' => $machine,
				'vendId' => $request->vend_machine_id,
				'opening' => $request->opening,
				'closing' => $request->closing,
				'tillNumber' => $regNumber,
				'tillNumberId' => $request->till_number_id,
				'zRead' => $request->z_read,
				'cash' => $request->cash,
				'taxes' => $taxes,
				'total' => $request->total,
				'goodItems' => serialize($goods),
				'backData' => serialize($backData)
			]
		);
	}

	public function vendingSalesPost(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		DB::beginTransaction();

		try {
			$vendingSales = new VendingSales;

			if ($request->has('sheet_id')) {
				$vendingSalesId = $request->sheet_id;

				$vendingSales = VendingSales::findOrFail($vendingSalesId);

				// Delete previous sale goods
				VendingSaleGood::where('vending_sales_id', $vendingSalesId)->delete();
			}

			// Store data
			$vendingSales->unit_id = $request->unit_id;
			$vendingSales->unit_name = $request->unit_name;

			$vendingSales->supervisor = $userName;
			$vendingSales->supervisor_id = $userId;

			$vendingSales->date = Carbon::now()->format('Y-m-d');
			$vendingSales->sale_date = Carbon::parse($request->sale_date)->format('Y-m-d');

			$vendingSales->vend_name = $request->vend_name;
			$vendingSales->vend_id = $request->vend_id;

			$vendingSales->till_number = $request->till_number_name;
			$vendingSales->till_number_id = $request->till_number_id;

			$vendingSales->opening = str_replace(',', '', $request->opening);
			$vendingSales->closing = str_replace(',', '', $request->closing);
			$vendingSales->z_read = $request->z_read;
			$vendingSales->cash = str_replace(',', '', $request->cash);

			$vendingSales->total = str_replace(',', '', $request->total);

			$vendingSales->save();

			// Save items
			$goodItems = unserialize($request->good_items);

			foreach ($goodItems as $netExtId => $goodItem) {
				if ($goodItem['amount'] == 0) {
					continue;
				}

				$saleGood = new VendingSaleGood();

				$saleGood->vending_sales_id = $vendingSales->vending_sales_id;
				$saleGood->net_ext_id = $netExtId;
				$saleGood->amount = $goodItem['amount'];
				$saleGood->tax_code_id = $goodItem['tax'];

				$saleGood->save();
			}

			DB::commit();

			Session::flash('flash_message', 'The form was successfully completed!');
		} catch (\Exception $e) {
			DB::rollBack();

			Session::flash('error_message', 'Error while saving form!');
		}

		return redirect('/sheets/vending-sales/')->cookie('unitIdCookieVendingSalesSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	public function machineNameJson(Request $request)
	{
		$unitId = $request->input('unit_id', 0);

		$machines = Vending::where('unit_id', $unitId)
			->orderBy('vend_name')
			->get(['vend_management_id', 'vend_name']);

		$regNumbers = Register::where('unit_id', $unitId)
			->orderBy('reg_number')
			->get(['reg_management_id', 'reg_number']);

		return response()->json(
			[
				'machines' => $machines,
				'regNumbers' => $regNumbers
			]
		);
	}

	public function closingReadingJson(Request $request)
	{
		$vendingSale = VendingSales::where('unit_id', $request->unit_id)
			->where('vend_id', $request->selected_machine)
			->orderBy('vending_sales_id', 'desc')
			->first();

		return response()->json(
			[
				'closing' => !is_null($vendingSale) ? $vendingSale->closing : ''
			]
		);
	}

	/**
	 * Phased Budget Sheet.
	 */
	public function phasedBudget(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		$selectedUnit = $request->unit_id;
		$unitName = $request->unit_name;
		$budgetYear = date('d-m') == '01-01' ? date("Y") : date("Y") . ' - ' . (date("Y") + 1);

		// Getting Change Log Budget
		if ($request->has('budget_id')) {
			$budgetId = $request->input('budget_id');
			$changeLogBudget = \App\PhasedBudget::find($budgetId);
			$contractTypeLegend = $changeLogBudget->contractType->title;

			$budgetStartYear = date('Y', strtotime($changeLogBudget->budget_start_date));
			$budgetEndYear = date('Y', strtotime($changeLogBudget->budget_end_date));
			$budgetYear = $budgetStartYear == $budgetEndYear ? $budgetStartYear : $budgetStartYear . ' - ' . $budgetEndYear;
		} else {
			$changeLogBudget = '';
			$contractTypeLegend = '';
		}

		// Rows visibility
		$unitId = $changeLogBudget ? $changeLogBudget->unit_id : $selectedUnit;

		$hiddenUnitRows = PhasedBudgetUnitRow::where('user_id', $userId)->where('unit_id', $unitId)->get();

		$unitRows = [];

		foreach (PhasedBudgetUnitRow::$rows as $rowIndex => $rowName) {
			$unitRows[$rowIndex] = [
				'name' => $rowName,
				'hidden' => $hiddenUnitRows->contains(function ($value) use ($rowIndex) {
					return $rowIndex == $value->row_index;
				})
			];
		}

		// Contact Types
		$contractTypes = ContractType::all()->pluck('title', 'id');

		// Budget Types
		$budgetTypes = BudgetType::all()->pluck('title', 'id');

		return view(
			'sheets.phased-budget.index', [
				'userName' => $userName,
				'todayDate' => Carbon::now()->format('d-m-Y'),
				'userUnits' => $userUnits,
				'selectedUnit' => isset($selectedUnit) ? $selectedUnit : $request->cookie('unitIdCookiePhasedBudgetSheet'),
				'unitName' => $unitName,
				'budgetStartDate' => $request->budget_start_date,
				'budgetEndDate' => $request->budget_end_date,
				'changeLogBudget' => $changeLogBudget,
				'headCountInSession' => Session::has('headCountInSession') ? Session::get('headCountInSession') : '',
				'budgetYear' => $budgetYear,
				'enteredBy' => $request->entered_by,
				'approvedBy' => $request->approved_by,
				'unitRows' => $unitRows,
				'contractTypes' => $contractTypes,
				'selectedContractType' => $changeLogBudget ? $changeLogBudget->contract_type_id : $request->input('contract_type', 0),
				'contractTypeLegend' => $contractTypeLegend,
				'budgetTypes' => $budgetTypes,
				'selectedBudgetType' => $changeLogBudget ? $changeLogBudget->budget_type_id : $request->input('budget_type', 0),
				'headCountMonth1' => $request->head_count_month_1 ? $request->head_count_month_1 : 0,
				'headCountMonth2' => $request->head_count_month_2 ? $request->head_count_month_2 : 0,
				'headCountMonth3' => $request->head_count_month_3 ? $request->head_count_month_3 : 0,
				'headCountMonth4' => $request->head_count_month_4 ? $request->head_count_month_4 : 0,
				'headCountMonth5' => $request->head_count_month_5 ? $request->head_count_month_5 : 0,
				'headCountMonth6' => $request->head_count_month_6 ? $request->head_count_month_6 : 0,
				'headCountMonth7' => $request->head_count_month_7 ? $request->head_count_month_7 : 0,
				'headCountMonth8' => $request->head_count_month_8 ? $request->head_count_month_8 : 0,
				'headCountMonth9' => $request->head_count_month_9 ? $request->head_count_month_9 : 0,
				'headCountMonth10' => $request->head_count_month_10 ? $request->head_count_month_10 : 0,
				'headCountMonth11' => $request->head_count_month_11 ? $request->head_count_month_11 : 0,
				'headCountMonth12' => $request->head_count_month_12 ? $request->head_count_month_12 : 0,
				'numTradingDaysTotals' => $request->num_trading_days_totals,
				'numTradingDaysMonth1' => $request->num_trading_days_month_1,
				'numTradingDaysMonth2' => $request->num_trading_days_month_2,
				'numTradingDaysMonth3' => $request->num_trading_days_month_3,
				'numTradingDaysMonth4' => $request->num_trading_days_month_4,
				'numTradingDaysMonth5' => $request->num_trading_days_month_5,
				'numTradingDaysMonth6' => $request->num_trading_days_month_6,
				'numTradingDaysMonth7' => $request->num_trading_days_month_7,
				'numTradingDaysMonth8' => $request->num_trading_days_month_8,
				'numTradingDaysMonth9' => $request->num_trading_days_month_9,
				'numTradingDaysMonth10' => $request->num_trading_days_month_10,
				'numTradingDaysMonth11' => $request->num_trading_days_month_11,
				'numTradingDaysMonth12' => $request->num_trading_days_month_12,
				'numOfWeeksTotals' => $request->num_of_weeks_totals,
				'numOfWeeksMonth1' => $request->num_of_weeks_month_1,
				'numOfWeeksMonth2' => $request->num_of_weeks_month_2,
				'numOfWeeksMonth3' => $request->num_of_weeks_month_3,
				'numOfWeeksMonth4' => $request->num_of_weeks_month_4,
				'numOfWeeksMonth5' => $request->num_of_weeks_month_5,
				'numOfWeeksMonth6' => $request->num_of_weeks_month_6,
				'numOfWeeksMonth7' => $request->num_of_weeks_month_7,
				'numOfWeeksMonth8' => $request->num_of_weeks_month_8,
				'numOfWeeksMonth9' => $request->num_of_weeks_month_9,
				'numOfWeeksMonth10' => $request->num_of_weeks_month_10,
				'numOfWeeksMonth11' => $request->num_of_weeks_month_11,
				'numOfWeeksMonth12' => $request->num_of_weeks_month_12,
				'grossSalesTotals' => $request->gross_sales_totals,
				'grossSalesMonth1' => $request->gross_sales_month_1,
				'grossSalesMonth2' => $request->gross_sales_month_2,
				'grossSalesMonth3' => $request->gross_sales_month_3,
				'grossSalesMonth4' => $request->gross_sales_month_4,
				'grossSalesMonth5' => $request->gross_sales_month_5,
				'grossSalesMonth6' => $request->gross_sales_month_6,
				'grossSalesMonth7' => $request->gross_sales_month_7,
				'grossSalesMonth8' => $request->gross_sales_month_8,
				'grossSalesMonth9' => $request->gross_sales_month_9,
				'grossSalesMonth10' => $request->gross_sales_month_10,
				'grossSalesMonth11' => $request->gross_sales_month_11,
				'grossSalesMonth12' => $request->gross_sales_month_12,
				'vatTotals' => $request->vat_totals,
				'vatMonth1' => $request->vat_month_1,
				'vatMonth2' => $request->vat_month_2,
				'vatMonth3' => $request->vat_month_3,
				'vatMonth4' => $request->vat_month_4,
				'vatMonth5' => $request->vat_month_5,
				'vatMonth6' => $request->vat_month_6,
				'vatMonth7' => $request->vat_month_7,
				'vatMonth8' => $request->vat_month_8,
				'vatMonth9' => $request->vat_month_9,
				'vatMonth10' => $request->vat_month_10,
				'vatMonth11' => $request->vat_month_11,
				'vatMonth12' => $request->vat_month_12,
				'netSalesTotals' => $request->net_sales_totals,
				'netSalesMonth1' => $request->net_sales_month_1,
				'netSalesMonth2' => $request->net_sales_month_2,
				'netSalesMonth3' => $request->net_sales_month_3,
				'netSalesMonth4' => $request->net_sales_month_4,
				'netSalesMonth5' => $request->net_sales_month_5,
				'netSalesMonth6' => $request->net_sales_month_6,
				'netSalesMonth7' => $request->net_sales_month_7,
				'netSalesMonth8' => $request->net_sales_month_8,
				'netSalesMonth9' => $request->net_sales_month_9,
				'netSalesMonth10' => $request->net_sales_month_10,
				'netSalesMonth11' => $request->net_sales_month_11,
				'netSalesMonth12' => $request->net_sales_month_12,
				'costOfSalesTotals' => $request->cost_of_sales_totals,
				'costOfSalesMonth1' => $request->cost_of_sales_month_1,
				'costOfSalesMonth2' => $request->cost_of_sales_month_2,
				'costOfSalesMonth3' => $request->cost_of_sales_month_3,
				'costOfSalesMonth4' => $request->cost_of_sales_month_4,
				'costOfSalesMonth5' => $request->cost_of_sales_month_5,
				'costOfSalesMonth6' => $request->cost_of_sales_month_6,
				'costOfSalesMonth7' => $request->cost_of_sales_month_7,
				'costOfSalesMonth8' => $request->cost_of_sales_month_8,
				'costOfSalesMonth9' => $request->cost_of_sales_month_9,
				'costOfSalesMonth10' => $request->cost_of_sales_month_10,
				'costOfSalesMonth11' => $request->cost_of_sales_month_11,
				'costOfSalesMonth12' => $request->cost_of_sales_month_12,
				'grossProfitTotals' => $request->gross_profit_totals,
				'grossProfitMonth1' => $request->gross_profit_month_1,
				'grossProfitMonth2' => $request->gross_profit_month_2,
				'grossProfitMonth3' => $request->gross_profit_month_3,
				'grossProfitMonth4' => $request->gross_profit_month_4,
				'grossProfitMonth5' => $request->gross_profit_month_5,
				'grossProfitMonth6' => $request->gross_profit_month_6,
				'grossProfitMonth7' => $request->gross_profit_month_7,
				'grossProfitMonth8' => $request->gross_profit_month_8,
				'grossProfitMonth9' => $request->gross_profit_month_9,
				'grossProfitMonth10' => $request->gross_profit_month_10,
				'grossProfitMonth11' => $request->gross_profit_month_11,
				'grossProfitMonth12' => $request->gross_profit_month_12,
				'grossProfitNetTotals' => $request->gross_profit_net_totals,
				'grossProfitNetMonth1' => $request->gross_profit_net_month_1,
				'grossProfitNetMonth2' => $request->gross_profit_net_month_2,
				'grossProfitNetMonth3' => $request->gross_profit_net_month_3,
				'grossProfitNetMonth4' => $request->gross_profit_net_month_4,
				'grossProfitNetMonth5' => $request->gross_profit_net_month_5,
				'grossProfitNetMonth6' => $request->gross_profit_net_month_6,
				'grossProfitNetMonth7' => $request->gross_profit_net_month_7,
				'grossProfitNetMonth8' => $request->gross_profit_net_month_8,
				'grossProfitNetMonth9' => $request->gross_profit_net_month_9,
				'grossProfitNetMonth10' => $request->gross_profit_net_month_10,
				'grossProfitNetMonth11' => $request->gross_profit_net_month_11,
				'grossProfitNetMonth12' => $request->gross_profit_net_month_12,
				'gppOnGrossSalesTotals' => $request->gpp_on_gross_sales_totals,
				'gppOnGrossSalesMonth1' => $request->gpp_on_gross_sales_month_1,
				'gppOnGrossSalesMonth2' => $request->gpp_on_gross_sales_month_2,
				'gppOnGrossSalesMonth3' => $request->gpp_on_gross_sales_month_3,
				'gppOnGrossSalesMonth4' => $request->gpp_on_gross_sales_month_4,
				'gppOnGrossSalesMonth5' => $request->gpp_on_gross_sales_month_5,
				'gppOnGrossSalesMonth6' => $request->gpp_on_gross_sales_month_6,
				'gppOnGrossSalesMonth7' => $request->gpp_on_gross_sales_month_7,
				'gppOnGrossSalesMonth8' => $request->gpp_on_gross_sales_month_8,
				'gppOnGrossSalesMonth9' => $request->gpp_on_gross_sales_month_9,
				'gppOnGrossSalesMonth10' => $request->gpp_on_gross_sales_month_10,
				'gppOnGrossSalesMonth11' => $request->gpp_on_gross_sales_month_11,
				'gppOnGrossSalesMonth12' => $request->gpp_on_gross_sales_month_12,
				'gppOnNetSalesTotals' => $request->gpp_on_net_sales_totals,
				'gppOnNetSalesMonth1' => $request->gpp_on_net_sales_month_1,
				'gppOnNetSalesMonth2' => $request->gpp_on_net_sales_month_2,
				'gppOnNetSalesMonth3' => $request->gpp_on_net_sales_month_3,
				'gppOnNetSalesMonth4' => $request->gpp_on_net_sales_month_4,
				'gppOnNetSalesMonth5' => $request->gpp_on_net_sales_month_5,
				'gppOnNetSalesMonth6' => $request->gpp_on_net_sales_month_6,
				'gppOnNetSalesMonth7' => $request->gpp_on_net_sales_month_7,
				'gppOnNetSalesMonth8' => $request->gpp_on_net_sales_month_8,
				'gppOnNetSalesMonth9' => $request->gpp_on_net_sales_month_9,
				'gppOnNetSalesMonth10' => $request->gpp_on_net_sales_month_10,
				'gppOnNetSalesMonth11' => $request->gpp_on_net_sales_month_11,
				'gppOnNetSalesMonth12' => $request->gpp_on_net_sales_month_12,
				'labourTotals' => $request->input('labour_totals', $changeLogBudget ? number_format($changeLogBudget->labour_totals) : 0),
				'labourMonth1' => $request->input('labour_month_1', $changeLogBudget ? number_format($changeLogBudget->labour_month_1) : 0),
				'labourMonth2' => $request->input('labour_month_2', $changeLogBudget ? number_format($changeLogBudget->labour_month_2) : 0),
				'labourMonth3' => $request->input('labour_month_3', $changeLogBudget ? number_format($changeLogBudget->labour_month_3) : 0),
				'labourMonth4' => $request->input('labour_month_4', $changeLogBudget ? number_format($changeLogBudget->labour_month_4) : 0),
				'labourMonth5' => $request->input('labour_month_5', $changeLogBudget ? number_format($changeLogBudget->labour_month_5) : 0),
				'labourMonth6' => $request->input('labour_month_6', $changeLogBudget ? number_format($changeLogBudget->labour_month_6) : 0),
				'labourMonth7' => $request->input('labour_month_7', $changeLogBudget ? number_format($changeLogBudget->labour_month_7) : 0),
				'labourMonth8' => $request->input('labour_month_8', $changeLogBudget ? number_format($changeLogBudget->labour_month_8) : 0),
				'labourMonth9' => $request->input('labour_month_9', $changeLogBudget ? number_format($changeLogBudget->labour_month_9) : 0),
				'labourMonth10' => $request->input('labour_month_10', $changeLogBudget ? number_format($changeLogBudget->labour_month_10) : 0),
				'labourMonth11' => $request->input('labour_month_11', $changeLogBudget ? number_format($changeLogBudget->labour_month_11) : 0),
				'labourMonth12' => $request->input('labour_month_12', $changeLogBudget ? number_format($changeLogBudget->labour_month_12) : 0),
				'trainingTotals' => $request->input('training_totals', $changeLogBudget ? number_format($changeLogBudget->training_totals) : 0),
				'trainingMonth1' => $request->input('training_month_1', $changeLogBudget ? number_format($changeLogBudget->training_month_1) : 0),
				'trainingMonth2' => $request->input('training_month_2', $changeLogBudget ? number_format($changeLogBudget->training_month_2) : 0),
				'trainingMonth3' => $request->input('training_month_3', $changeLogBudget ? number_format($changeLogBudget->training_month_3) : 0),
				'trainingMonth4' => $request->input('training_month_4', $changeLogBudget ? number_format($changeLogBudget->training_month_4) : 0),
				'trainingMonth5' => $request->input('training_month_5', $changeLogBudget ? number_format($changeLogBudget->training_month_5) : 0),
				'trainingMonth6' => $request->input('training_month_6', $changeLogBudget ? number_format($changeLogBudget->training_month_6) : 0),
				'trainingMonth7' => $request->input('training_month_7', $changeLogBudget ? number_format($changeLogBudget->training_month_7) : 0),
				'trainingMonth8' => $request->input('training_month_8', $changeLogBudget ? number_format($changeLogBudget->training_month_8) : 0),
				'trainingMonth9' => $request->input('training_month_9', $changeLogBudget ? number_format($changeLogBudget->training_month_9) : 0),
				'trainingMonth10' => $request->input('training_month_10', $changeLogBudget ? number_format($changeLogBudget->training_month_10) : 0),
				'trainingMonth11' => $request->input('training_month_11', $changeLogBudget ? number_format($changeLogBudget->training_month_11) : 0),
				'trainingMonth12' => $request->input('training_month_12', $changeLogBudget ? number_format($changeLogBudget->training_month_12) : 0),
				'cleaningTotals' => $request->input('cleaning_totals', $changeLogBudget ? number_format($changeLogBudget->cleaning_totals) : 0),
				'cleaningMonth1' => $request->input('cleaning_month_1', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_1) : 0),
				'cleaningMonth2' => $request->input('cleaning_month_2', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_2) : 0),
				'cleaningMonth3' => $request->input('cleaning_month_3', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_3) : 0),
				'cleaningMonth4' => $request->input('cleaning_month_4', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_4) : 0),
				'cleaningMonth5' => $request->input('cleaning_month_5', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_5) : 0),
				'cleaningMonth6' => $request->input('cleaning_month_6', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_6) : 0),
				'cleaningMonth7' => $request->input('cleaning_month_7', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_7) : 0),
				'cleaningMonth8' => $request->input('cleaning_month_8', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_8) : 0),
				'cleaningMonth9' => $request->input('cleaning_month_9', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_9) : 0),
				'cleaningMonth10' => $request->input('cleaning_month_10', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_10) : 0),
				'cleaningMonth11' => $request->input('cleaning_month_11', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_11) : 0),
				'cleaningMonth12' => $request->input('cleaning_month_12', $changeLogBudget ? number_format($changeLogBudget->cleaning_month_12) : 0),
				'disposablesTotals' => $request->input('disposables_totals', $changeLogBudget ? number_format($changeLogBudget->disposables_totals) : 0),
				'disposablesMonth1' => $request->input('disposables_month_1', $changeLogBudget ? number_format($changeLogBudget->disposables_month_1) : 0),
				'disposablesMonth2' => $request->input('disposables_month_2', $changeLogBudget ? number_format($changeLogBudget->disposables_month_2) : 0),
				'disposablesMonth3' => $request->input('disposables_month_3', $changeLogBudget ? number_format($changeLogBudget->disposables_month_3) : 0),
				'disposablesMonth4' => $request->input('disposables_month_4', $changeLogBudget ? number_format($changeLogBudget->disposables_month_4) : 0),
				'disposablesMonth5' => $request->input('disposables_month_5', $changeLogBudget ? number_format($changeLogBudget->disposables_month_5) : 0),
				'disposablesMonth6' => $request->input('disposables_month_6', $changeLogBudget ? number_format($changeLogBudget->disposables_month_6) : 0),
				'disposablesMonth7' => $request->input('disposables_month_7', $changeLogBudget ? number_format($changeLogBudget->disposables_month_7) : 0),
				'disposablesMonth8' => $request->input('disposables_month_8', $changeLogBudget ? number_format($changeLogBudget->disposables_month_8) : 0),
				'disposablesMonth9' => $request->input('disposables_month_9', $changeLogBudget ? number_format($changeLogBudget->disposables_month_9) : 0),
				'disposablesMonth10' => $request->input('disposables_month_10', $changeLogBudget ? number_format($changeLogBudget->disposables_month_10) : 0),
				'disposablesMonth11' => $request->input('disposables_month_11', $changeLogBudget ? number_format($changeLogBudget->disposables_month_11) : 0),
				'disposablesMonth12' => $request->input('disposables_month_12', $changeLogBudget ? number_format($changeLogBudget->disposables_month_12) : 0),
				'uniformTotals' => $request->input('uniform_totals', $changeLogBudget ? number_format($changeLogBudget->uniform_totals) : 0),
				'uniformMonth1' => $request->input('uniform_month_1', $changeLogBudget ? number_format($changeLogBudget->uniform_month_1) : 0),
				'uniformMonth2' => $request->input('uniform_month_2', $changeLogBudget ? number_format($changeLogBudget->uniform_month_2) : 0),
				'uniformMonth3' => $request->input('uniform_month_3', $changeLogBudget ? number_format($changeLogBudget->uniform_month_3) : 0),
				'uniformMonth4' => $request->input('uniform_month_4', $changeLogBudget ? number_format($changeLogBudget->uniform_month_4) : 0),
				'uniformMonth5' => $request->input('uniform_month_5', $changeLogBudget ? number_format($changeLogBudget->uniform_month_5) : 0),
				'uniformMonth6' => $request->input('uniform_month_6', $changeLogBudget ? number_format($changeLogBudget->uniform_month_6) : 0),
				'uniformMonth7' => $request->input('uniform_month_7', $changeLogBudget ? number_format($changeLogBudget->uniform_month_7) : 0),
				'uniformMonth8' => $request->input('uniform_month_8', $changeLogBudget ? number_format($changeLogBudget->uniform_month_8) : 0),
				'uniformMonth9' => $request->input('uniform_month_9', $changeLogBudget ? number_format($changeLogBudget->uniform_month_9) : 0),
				'uniformMonth10' => $request->input('uniform_month_10', $changeLogBudget ? number_format($changeLogBudget->uniform_month_10) : 0),
				'uniformMonth11' => $request->input('uniform_month_11', $changeLogBudget ? number_format($changeLogBudget->uniform_month_11) : 0),
				'uniformMonth12' => $request->input('uniform_month_12', $changeLogBudget ? number_format($changeLogBudget->uniform_month_12) : 0),
				'delphAndCutleryTotals' => $request->input('delph_and_cutlery_totals', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_totals) : 0),
				'delphAndCutleryMonth1' => $request->input('delph_and_cutlery_month_1', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_1) : 0),
				'delphAndCutleryMonth2' => $request->input('delph_and_cutlery_month_2', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_2) : 0),
				'delphAndCutleryMonth3' => $request->input('delph_and_cutlery_month_3', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_3) : 0),
				'delphAndCutleryMonth4' => $request->input('delph_and_cutlery_month_4', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_4) : 0),
				'delphAndCutleryMonth5' => $request->input('delph_and_cutlery_month_5', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_5) : 0),
				'delphAndCutleryMonth6' => $request->input('delph_and_cutlery_month_6', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_6) : 0),
				'delphAndCutleryMonth7' => $request->input('delph_and_cutlery_month_7', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_7) : 0),
				'delphAndCutleryMonth8' => $request->input('delph_and_cutlery_month_8', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_8) : 0),
				'delphAndCutleryMonth9' => $request->input('delph_and_cutlery_month_9', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_9) : 0),
				'delphAndCutleryMonth10' => $request->input('delph_and_cutlery_month_10', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_10) : 0),
				'delphAndCutleryMonth11' => $request->input('delph_and_cutlery_month_11', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_11) : 0),
				'delphAndCutleryMonth12' => $request->input('delph_and_cutlery_month_12', $changeLogBudget ? number_format($changeLogBudget->delph_and_cutlery_month_12) : 0),
				'bankChargesTotals' => $request->input('bank_charges_totals', $changeLogBudget ? number_format($changeLogBudget->bank_charges_totals) : 0),
				'bankChargesMonth1' => $request->input('bank_charges_month_1', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_1) : 0),
				'bankChargesMonth2' => $request->input('bank_charges_month_2', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_2) : 0),
				'bankChargesMonth3' => $request->input('bank_charges_month_3', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_3) : 0),
				'bankChargesMonth4' => $request->input('bank_charges_month_4', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_4) : 0),
				'bankChargesMonth5' => $request->input('bank_charges_month_5', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_5) : 0),
				'bankChargesMonth6' => $request->input('bank_charges_month_6', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_6) : 0),
				'bankChargesMonth7' => $request->input('bank_charges_month_7', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_7) : 0),
				'bankChargesMonth8' => $request->input('bank_charges_month_8', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_8) : 0),
				'bankChargesMonth9' => $request->input('bank_charges_month_9', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_9) : 0),
				'bankChargesMonth10' => $request->input('bank_charges_month_10', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_10) : 0),
				'bankChargesMonth11' => $request->input('bank_charges_month_11', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_11) : 0),
				'bankChargesMonth12' => $request->input('bank_charges_month_12', $changeLogBudget ? number_format($changeLogBudget->bank_charges_month_12) : 0),
				'investmentTotals' => $request->input('investment_totals', $changeLogBudget ? number_format($changeLogBudget->investment_totals) : 0),
				'investmentMonth1' => $request->input('investment_month_1', $changeLogBudget ? number_format($changeLogBudget->investment_month_1) : 0),
				'investmentMonth2' => $request->input('investment_month_2', $changeLogBudget ? number_format($changeLogBudget->investment_month_2) : 0),
				'investmentMonth3' => $request->input('investment_month_3', $changeLogBudget ? number_format($changeLogBudget->investment_month_3) : 0),
				'investmentMonth4' => $request->input('investment_month_4', $changeLogBudget ? number_format($changeLogBudget->investment_month_4) : 0),
				'investmentMonth5' => $request->input('investment_month_5', $changeLogBudget ? number_format($changeLogBudget->investment_month_5) : 0),
				'investmentMonth6' => $request->input('investment_month_6', $changeLogBudget ? number_format($changeLogBudget->investment_month_6) : 0),
				'investmentMonth7' => $request->input('investment_month_7', $changeLogBudget ? number_format($changeLogBudget->investment_month_7) : 0),
				'investmentMonth8' => $request->input('investment_month_8', $changeLogBudget ? number_format($changeLogBudget->investment_month_8) : 0),
				'investmentMonth9' => $request->input('investment_month_9', $changeLogBudget ? number_format($changeLogBudget->investment_month_9) : 0),
				'investmentMonth10' => $request->input('investment_month_10', $changeLogBudget ? number_format($changeLogBudget->investment_month_10) : 0),
				'investmentMonth11' => $request->input('investment_month_11', $changeLogBudget ? number_format($changeLogBudget->investment_month_11) : 0),
				'investmentMonth12' => $request->input('investment_month_12', $changeLogBudget ? number_format($changeLogBudget->investment_month_12) : 0),
				'managementFeeTotals' => $request->input('management_fee_totals', $changeLogBudget ? number_format($changeLogBudget->management_fee_totals) : 0),
				'managementFeeMonth1' => $request->input('management_fee_month_1', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_1) : 0),
				'managementFeeMonth2' => $request->input('management_fee_month_2', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_2) : 0),
				'managementFeeMonth3' => $request->input('management_fee_month_3', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_3) : 0),
				'managementFeeMonth4' => $request->input('management_fee_month_4', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_4) : 0),
				'managementFeeMonth5' => $request->input('management_fee_month_5', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_5) : 0),
				'managementFeeMonth6' => $request->input('management_fee_month_6', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_6) : 0),
				'managementFeeMonth7' => $request->input('management_fee_month_7', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_7) : 0),
				'managementFeeMonth8' => $request->input('management_fee_month_8', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_8) : 0),
				'managementFeeMonth9' => $request->input('management_fee_month_9', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_9) : 0),
				'managementFeeMonth10' => $request->input('management_fee_month_10', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_10) : 0),
				'managementFeeMonth11' => $request->input('management_fee_month_11', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_11) : 0),
				'managementFeeMonth12' => $request->input('management_fee_month_12', $changeLogBudget ? number_format($changeLogBudget->management_fee_month_12) : 0),
				'insuranceAndRelatedCostsTotals' => $request->input('insurance_and_related_costs_totals', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_totals) : 0),
				'insuranceAndRelatedCostsMonth1' => $request->input('insurance_and_related_costs_month_1', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_1) : 0),
				'insuranceAndRelatedCostsMonth2' => $request->input('insurance_and_related_costs_month_2', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_2) : 0),
				'insuranceAndRelatedCostsMonth3' => $request->input('insurance_and_related_costs_month_3', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_3) : 0),
				'insuranceAndRelatedCostsMonth4' => $request->input('insurance_and_related_costs_month_4', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_4) : 0),
				'insuranceAndRelatedCostsMonth5' => $request->input('insurance_and_related_costs_month_5', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_5) : 0),
				'insuranceAndRelatedCostsMonth6' => $request->input('insurance_and_related_costs_month_6', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_6) : 0),
				'insuranceAndRelatedCostsMonth7' => $request->input('insurance_and_related_costs_month_7', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_7) : 0),
				'insuranceAndRelatedCostsMonth8' => $request->input('insurance_and_related_costs_month_8', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_8) : 0),
				'insuranceAndRelatedCostsMonth9' => $request->input('insurance_and_related_costs_month_9', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_9) : 0),
				'insuranceAndRelatedCostsMonth10' => $request->input('insurance_and_related_costs_month_10', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_10) : 0),
				'insuranceAndRelatedCostsMonth11' => $request->input('insurance_and_related_costs_month_11', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_11) : 0),
				'insuranceAndRelatedCostsMonth12' => $request->input('insurance_and_related_costs_month_12', $changeLogBudget ? number_format($changeLogBudget->insurance_and_related_costs_month_12) : 0),
				'coffeeMachineRentalTotals' => $request->input('coffee_machine_rental_totals', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_totals) : 0),
				'coffeeMachineRentalMonth1' => $request->input('coffee_machine_rental_month_1', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_1) : 0),
				'coffeeMachineRentalMonth2' => $request->input('coffee_machine_rental_month_2', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_2) : 0),
				'coffeeMachineRentalMonth3' => $request->input('coffee_machine_rental_month_3', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_3) : 0),
				'coffeeMachineRentalMonth4' => $request->input('coffee_machine_rental_month_4', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_4) : 0),
				'coffeeMachineRentalMonth5' => $request->input('coffee_machine_rental_month_5', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_5) : 0),
				'coffeeMachineRentalMonth6' => $request->input('coffee_machine_rental_month_6', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_6) : 0),
				'coffeeMachineRentalMonth7' => $request->input('coffee_machine_rental_month_7', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_7) : 0),
				'coffeeMachineRentalMonth8' => $request->input('coffee_machine_rental_month_8', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_8) : 0),
				'coffeeMachineRentalMonth9' => $request->input('coffee_machine_rental_month_9', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_9) : 0),
				'coffeeMachineRentalMonth10' => $request->input('coffee_machine_rental_month_10', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_10) : 0),
				'coffeeMachineRentalMonth11' => $request->input('coffee_machine_rental_month_11', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_11) : 0),
				'coffeeMachineRentalMonth12' => $request->input('coffee_machine_rental_month_12', $changeLogBudget ? number_format($changeLogBudget->coffee_machine_rental_month_12) : 0),
				'otherRentalTotals' => $request->input('other_rental_totals', $changeLogBudget ? number_format($changeLogBudget->other_rental_totals) : 0),
				'otherRentalMonth1' => $request->input('other_rental_month_1', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_1) : 0),
				'otherRentalMonth2' => $request->input('other_rental_month_2', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_2) : 0),
				'otherRentalMonth3' => $request->input('other_rental_month_3', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_3) : 0),
				'otherRentalMonth4' => $request->input('other_rental_month_4', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_4) : 0),
				'otherRentalMonth5' => $request->input('other_rental_month_5', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_5) : 0),
				'otherRentalMonth6' => $request->input('other_rental_month_6', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_6) : 0),
				'otherRentalMonth7' => $request->input('other_rental_month_7', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_7) : 0),
				'otherRentalMonth8' => $request->input('other_rental_month_8', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_8) : 0),
				'otherRentalMonth9' => $request->input('other_rental_month_9', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_9) : 0),
				'otherRentalMonth10' => $request->input('other_rental_month_10', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_10) : 0),
				'otherRentalMonth11' => $request->input('other_rental_month_11', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_11) : 0),
				'otherRentalMonth12' => $request->input('other_rental_month_12', $changeLogBudget ? number_format($changeLogBudget->other_rental_month_12) : 0),
				'itSupportTotals' => $request->input('it_support_totals', $changeLogBudget ? number_format($changeLogBudget->it_support_totals) : 0),
				'itSupportMonth1' => $request->input('it_support_month_1', $changeLogBudget ? number_format($changeLogBudget->it_support_month_1) : 0),
				'itSupportMonth2' => $request->input('it_support_month_2', $changeLogBudget ? number_format($changeLogBudget->it_support_month_2) : 0),
				'itSupportMonth3' => $request->input('it_support_month_3', $changeLogBudget ? number_format($changeLogBudget->it_support_month_3) : 0),
				'itSupportMonth4' => $request->input('it_support_month_4', $changeLogBudget ? number_format($changeLogBudget->it_support_month_4) : 0),
				'itSupportMonth5' => $request->input('it_support_month_5', $changeLogBudget ? number_format($changeLogBudget->it_support_month_5) : 0),
				'itSupportMonth6' => $request->input('it_support_month_6', $changeLogBudget ? number_format($changeLogBudget->it_support_month_6) : 0),
				'itSupportMonth7' => $request->input('it_support_month_7', $changeLogBudget ? number_format($changeLogBudget->it_support_month_7) : 0),
				'itSupportMonth8' => $request->input('it_support_month_8', $changeLogBudget ? number_format($changeLogBudget->it_support_month_8) : 0),
				'itSupportMonth9' => $request->input('it_support_month_9', $changeLogBudget ? number_format($changeLogBudget->it_support_month_9) : 0),
				'itSupportMonth10' => $request->input('it_support_month_10', $changeLogBudget ? number_format($changeLogBudget->it_support_month_10) : 0),
				'itSupportMonth11' => $request->input('it_support_month_11', $changeLogBudget ? number_format($changeLogBudget->it_support_month_11) : 0),
				'itSupportMonth12' => $request->input('it_support_month_12', $changeLogBudget ? number_format($changeLogBudget->it_support_month_12) : 0),
				'freeIssuesTotals' => $request->input('free_issues_totals', $changeLogBudget ? number_format($changeLogBudget->free_issues_totals) : 0),
				'freeIssuesMonth1' => $request->input('free_issues_month_1', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_1) : 0),
				'freeIssuesMonth2' => $request->input('free_issues_month_2', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_2) : 0),
				'freeIssuesMonth3' => $request->input('free_issues_month_3', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_3) : 0),
				'freeIssuesMonth4' => $request->input('free_issues_month_4', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_4) : 0),
				'freeIssuesMonth5' => $request->input('free_issues_month_5', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_5) : 0),
				'freeIssuesMonth6' => $request->input('free_issues_month_6', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_6) : 0),
				'freeIssuesMonth7' => $request->input('free_issues_month_7', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_7) : 0),
				'freeIssuesMonth8' => $request->input('free_issues_month_8', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_8) : 0),
				'freeIssuesMonth9' => $request->input('free_issues_month_9', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_9) : 0),
				'freeIssuesMonth10' => $request->input('free_issues_month_10', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_10) : 0),
				'freeIssuesMonth11' => $request->input('free_issues_month_11', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_11) : 0),
				'freeIssuesMonth12' => $request->input('free_issues_month_12', $changeLogBudget ? number_format($changeLogBudget->free_issues_month_12) : 0),
				'marketingTotals' => $request->input('marketing_totals', $changeLogBudget ? number_format($changeLogBudget->marketing_totals) : 0),
				'marketingMonth1' => $request->input('marketing_month_1', $changeLogBudget ? number_format($changeLogBudget->marketing_month_1) : 0),
				'marketingMonth2' => $request->input('marketing_month_2', $changeLogBudget ? number_format($changeLogBudget->marketing_month_2) : 0),
				'marketingMonth3' => $request->input('marketing_month_3', $changeLogBudget ? number_format($changeLogBudget->marketing_month_3) : 0),
				'marketingMonth4' => $request->input('marketing_month_4', $changeLogBudget ? number_format($changeLogBudget->marketing_month_4) : 0),
				'marketingMonth5' => $request->input('marketing_month_5', $changeLogBudget ? number_format($changeLogBudget->marketing_month_5) : 0),
				'marketingMonth6' => $request->input('marketing_month_6', $changeLogBudget ? number_format($changeLogBudget->marketing_month_6) : 0),
				'marketingMonth7' => $request->input('marketing_month_7', $changeLogBudget ? number_format($changeLogBudget->marketing_month_7) : 0),
				'marketingMonth8' => $request->input('marketing_month_8', $changeLogBudget ? number_format($changeLogBudget->marketing_month_8) : 0),
				'marketingMonth9' => $request->input('marketing_month_9', $changeLogBudget ? number_format($changeLogBudget->marketing_month_9) : 0),
				'marketingMonth10' => $request->input('marketing_month_10', $changeLogBudget ? number_format($changeLogBudget->marketing_month_10) : 0),
				'marketingMonth11' => $request->input('marketing_month_11', $changeLogBudget ? number_format($changeLogBudget->marketing_month_11) : 0),
				'marketingMonth12' => $request->input('marketing_month_12', $changeLogBudget ? number_format($changeLogBudget->marketing_month_12) : 0),
				'setUpCostsTotals' => $request->input('set_up_costs_totals', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_totals) : 0),
				'setUpCostsMonth1' => $request->input('set_up_costs_month_1', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_1) : 0),
				'setUpCostsMonth2' => $request->input('set_up_costs_month_2', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_2) : 0),
				'setUpCostsMonth3' => $request->input('set_up_costs_month_3', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_3) : 0),
				'setUpCostsMonth4' => $request->input('set_up_costs_month_4', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_4) : 0),
				'setUpCostsMonth5' => $request->input('set_up_costs_month_5', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_5) : 0),
				'setUpCostsMonth6' => $request->input('set_up_costs_month_6', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_6) : 0),
				'setUpCostsMonth7' => $request->input('set_up_costs_month_7', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_7) : 0),
				'setUpCostsMonth8' => $request->input('set_up_costs_month_8', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_8) : 0),
				'setUpCostsMonth9' => $request->input('set_up_costs_month_9', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_9) : 0),
				'setUpCostsMonth10' => $request->input('set_up_costs_month_10', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_11) : 0),
				'setUpCostsMonth11' => $request->input('set_up_costs_month_11', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_12) : 0),
				'setUpCostsMonth12' => $request->input('set_up_costs_month_12', $changeLogBudget ? number_format($changeLogBudget->set_up_costs_month_13) : 0),
				'creditCardMachinesTotals' => $request->input('credit_card_machines_totals', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_totals) : 0),
				'creditCardMachinesMonth1' => $request->input('credit_card_machines_month_1', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_1) : 0),
				'creditCardMachinesMonth2' => $request->input('credit_card_machines_month_2', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_2) : 0),
				'creditCardMachinesMonth3' => $request->input('credit_card_machines_month_3', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_3) : 0),
				'creditCardMachinesMonth4' => $request->input('credit_card_machines_month_4', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_4) : 0),
				'creditCardMachinesMonth5' => $request->input('credit_card_machines_month_5', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_5) : 0),
				'creditCardMachinesMonth6' => $request->input('credit_card_machines_month_6', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_6) : 0),
				'creditCardMachinesMonth7' => $request->input('credit_card_machines_month_7', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_7) : 0),
				'creditCardMachinesMonth8' => $request->input('credit_card_machines_month_8', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_8) : 0),
				'creditCardMachinesMonth9' => $request->input('credit_card_machines_month_9', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_9) : 0),
				'creditCardMachinesMonth10' => $request->input('credit_card_machines_month_10', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_10) : 0),
				'creditCardMachinesMonth11' => $request->input('credit_card_machines_month_11', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_11) : 0),
				'creditCardMachinesMonth12' => $request->input('credit_card_machines_month_12', $changeLogBudget ? number_format($changeLogBudget->credit_card_machines_month_12) : 0),
				'bizimplyCostTotals' => $request->input('bizimply_cost_totals', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_totals) : 0),
				'bizimplyCostMonth1' => $request->input('bizimply_cost_month_1', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_1) : 0),
				'bizimplyCostMonth2' => $request->input('bizimply_cost_month_2', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_2) : 0),
				'bizimplyCostMonth3' => $request->input('bizimply_cost_month_3', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_3) : 0),
				'bizimplyCostMonth4' => $request->input('bizimply_cost_month_4', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_4) : 0),
				'bizimplyCostMonth5' => $request->input('bizimply_cost_month_5', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_5) : 0),
				'bizimplyCostMonth6' => $request->input('bizimply_cost_month_6', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_6) : 0),
				'bizimplyCostMonth7' => $request->input('bizimply_cost_month_7', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_7) : 0),
				'bizimplyCostMonth8' => $request->input('bizimply_cost_month_8', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_8) : 0),
				'bizimplyCostMonth9' => $request->input('bizimply_cost_month_9', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_9) : 0),
				'bizimplyCostMonth10' => $request->input('bizimply_cost_month_10', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_10) : 0),
				'bizimplyCostMonth11' => $request->input('bizimply_cost_month_11', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_11) : 0),
				'bizimplyCostMonth12' => $request->input('bizimply_cost_month_12', $changeLogBudget ? number_format($changeLogBudget->bizimply_cost_month_12) : 0),
				'kitchtechTotals' => $request->input('kitchtech_totals', $changeLogBudget ? number_format($changeLogBudget->kitchtech_totals) : 0),
				'kitchtechMonth1' => $request->input('kitchtech_month_1', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_1) : 0),
				'kitchtechMonth2' => $request->input('kitchtech_month_2', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_2) : 0),
				'kitchtechMonth3' => $request->input('kitchtech_month_3', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_3) : 0),
				'kitchtechMonth4' => $request->input('kitchtech_month_4', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_4) : 0),
				'kitchtechMonth5' => $request->input('kitchtech_month_5', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_5) : 0),
				'kitchtechMonth6' => $request->input('kitchtech_month_6', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_6) : 0),
				'kitchtechMonth7' => $request->input('kitchtech_month_7', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_7) : 0),
				'kitchtechMonth8' => $request->input('kitchtech_month_8', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_8) : 0),
				'kitchtechMonth9' => $request->input('kitchtech_month_9', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_9) : 0),
				'kitchtechMonth10' => $request->input('kitchtech_month_10', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_10) : 0),
				'kitchtechMonth11' => $request->input('kitchtech_month_11', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_11) : 0),
				'kitchtechMonth12' => $request->input('kitchtech_month_12', $changeLogBudget ? number_format($changeLogBudget->kitchtech_month_12) : 0),
			]
		);
	}

	public function phasedBudgetConfimation(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		// Contact type
		$contractType = ContractType::find($request->contract_type);
		$contractTypeLegend = $contractType ? $contractType->title : '';

		// Budget type
		$budgetType = BudgetType::find($request->budget_type);
		$budgetTypeLegend = $budgetType ? $budgetType->title : '';

		return view(
			'sheets.phased-budget.confirmation', [
				'userId' => $userId,
				'userName' => $userName,
				'unitId' => $request->unit_name,
				'unitName' => $request->hidden_unit_name,
				'tradingAccountDate' => $request->trading_account_date,
				'budgetStartDate' => $request->budget_start_date,
				'budgetEndDate' => $request->budget_end_date,
				'budgetYear' => $request->budget_year,
				'enteredBy' => $request->entered_by,
				'approvedBy' => $request->approved_by,
				'contractType' => $request->contract_type,
				'contractTypeLegend' => $contractTypeLegend,
				'budgetType' => $request->budget_type,
				'budgetTypeLegend' => $budgetTypeLegend,
				'month1Header' => $request->month_1_header,
				'month2Header' => $request->month_2_header,
				'month3Header' => $request->month_3_header,
				'month4Header' => $request->month_4_header,
				'month5Header' => $request->month_5_header,
				'month6Header' => $request->month_6_header,
				'month7Header' => $request->month_7_header,
				'month8Header' => $request->month_8_header,
				'month9Header' => $request->month_9_header,
				'month10Header' => $request->month_10_header,
				'month11Header' => $request->month_11_header,
				'month12Header' => $request->month_12_header,
				'headCountTotals' => $request->head_count_totals,
				'headCountMonth1' => $request->head_count_month_1,
				'headCountMonth2' => $request->head_count_month_2,
				'headCountMonth3' => $request->head_count_month_3,
				'headCountMonth4' => $request->head_count_month_4,
				'headCountMonth5' => $request->head_count_month_5,
				'headCountMonth6' => $request->head_count_month_6,
				'headCountMonth7' => $request->head_count_month_7,
				'headCountMonth8' => $request->head_count_month_8,
				'headCountMonth9' => $request->head_count_month_9,
				'headCountMonth10' => $request->head_count_month_10,
				'headCountMonth11' => $request->head_count_month_11,
				'headCountMonth12' => $request->head_count_month_12,
				'numTradingDaysTotals' => $request->num_trading_days_totals,
				'numTradingDaysMonth1' => $request->num_trading_days_month_1,
				'numTradingDaysMonth2' => $request->num_trading_days_month_2,
				'numTradingDaysMonth3' => $request->num_trading_days_month_3,
				'numTradingDaysMonth4' => $request->num_trading_days_month_4,
				'numTradingDaysMonth5' => $request->num_trading_days_month_5,
				'numTradingDaysMonth6' => $request->num_trading_days_month_6,
				'numTradingDaysMonth7' => $request->num_trading_days_month_7,
				'numTradingDaysMonth8' => $request->num_trading_days_month_8,
				'numTradingDaysMonth9' => $request->num_trading_days_month_9,
				'numTradingDaysMonth10' => $request->num_trading_days_month_10,
				'numTradingDaysMonth11' => $request->num_trading_days_month_11,
				'numTradingDaysMonth12' => $request->num_trading_days_month_12,
				'numOfWeeksTotals' => $request->num_of_weeks_totals,
				'numOfWeeksMonth1' => $request->num_of_weeks_month_1,
				'numOfWeeksMonth2' => $request->num_of_weeks_month_2,
				'numOfWeeksMonth3' => $request->num_of_weeks_month_3,
				'numOfWeeksMonth4' => $request->num_of_weeks_month_4,
				'numOfWeeksMonth5' => $request->num_of_weeks_month_5,
				'numOfWeeksMonth6' => $request->num_of_weeks_month_6,
				'numOfWeeksMonth7' => $request->num_of_weeks_month_7,
				'numOfWeeksMonth8' => $request->num_of_weeks_month_8,
				'numOfWeeksMonth9' => $request->num_of_weeks_month_9,
				'numOfWeeksMonth10' => $request->num_of_weeks_month_10,
				'numOfWeeksMonth11' => $request->num_of_weeks_month_11,
				'numOfWeeksMonth12' => $request->num_of_weeks_month_12,
				'labourHoursTotals' => $request->labour_hours_totals,
				'labourHoursMonth1' => $request->labour_hours_month_1,
				'labourHoursMonth2' => $request->labour_hours_month_2,
				'labourHoursMonth3' => $request->labour_hours_month_3,
				'labourHoursMonth4' => $request->labour_hours_month_4,
				'labourHoursMonth5' => $request->labour_hours_month_5,
				'labourHoursMonth6' => $request->labour_hours_month_6,
				'labourHoursMonth7' => $request->labour_hours_month_7,
				'labourHoursMonth8' => $request->labour_hours_month_8,
				'labourHoursMonth9' => $request->labour_hours_month_9,
				'labourHoursMonth10' => $request->labour_hours_month_10,
				'labourHoursMonth11' => $request->labour_hours_month_11,
				'labourHoursMonth12' => $request->labour_hours_month_12,
				'grossSalesTotals' => $request->gross_sales_totals,
				'grossSalesMonth1' => $request->gross_sales_month_1,
				'grossSalesMonth2' => $request->gross_sales_month_2,
				'grossSalesMonth3' => $request->gross_sales_month_3,
				'grossSalesMonth4' => $request->gross_sales_month_4,
				'grossSalesMonth5' => $request->gross_sales_month_5,
				'grossSalesMonth6' => $request->gross_sales_month_6,
				'grossSalesMonth7' => $request->gross_sales_month_7,
				'grossSalesMonth8' => $request->gross_sales_month_8,
				'grossSalesMonth9' => $request->gross_sales_month_9,
				'grossSalesMonth10' => $request->gross_sales_month_10,
				'grossSalesMonth11' => $request->gross_sales_month_11,
				'grossSalesMonth12' => $request->gross_sales_month_12,
				'netSalesTotals' => $request->net_sales_totals,
				'netSalesMonth1' => $request->net_sales_month_1,
				'netSalesMonth2' => $request->net_sales_month_2,
				'netSalesMonth3' => $request->net_sales_month_3,
				'netSalesMonth4' => $request->net_sales_month_4,
				'netSalesMonth5' => $request->net_sales_month_5,
				'netSalesMonth6' => $request->net_sales_month_6,
				'netSalesMonth7' => $request->net_sales_month_7,
				'netSalesMonth8' => $request->net_sales_month_8,
				'netSalesMonth9' => $request->net_sales_month_9,
				'netSalesMonth10' => $request->net_sales_month_10,
				'netSalesMonth11' => $request->net_sales_month_11,
				'netSalesMonth12' => $request->net_sales_month_12,
				'costOfSalesTotals' => $request->cost_of_sales_totals,
				'costOfSalesMonth1' => $request->cost_of_sales_month_1,
				'costOfSalesMonth2' => $request->cost_of_sales_month_2,
				'costOfSalesMonth3' => $request->cost_of_sales_month_3,
				'costOfSalesMonth4' => $request->cost_of_sales_month_4,
				'costOfSalesMonth5' => $request->cost_of_sales_month_5,
				'costOfSalesMonth6' => $request->cost_of_sales_month_6,
				'costOfSalesMonth7' => $request->cost_of_sales_month_7,
				'costOfSalesMonth8' => $request->cost_of_sales_month_8,
				'costOfSalesMonth9' => $request->cost_of_sales_month_9,
				'costOfSalesMonth10' => $request->cost_of_sales_month_10,
				'costOfSalesMonth11' => $request->cost_of_sales_month_11,
				'costOfSalesMonth12' => $request->cost_of_sales_month_12,
				'vatTotals' => $request->vat_totals,
				'vatMonth1' => $request->vat_month_1,
				'vatMonth2' => $request->vat_month_2,
				'vatMonth3' => $request->vat_month_3,
				'vatMonth4' => $request->vat_month_4,
				'vatMonth5' => $request->vat_month_5,
				'vatMonth6' => $request->vat_month_6,
				'vatMonth7' => $request->vat_month_7,
				'vatMonth8' => $request->vat_month_8,
				'vatMonth9' => $request->vat_month_9,
				'vatMonth10' => $request->vat_month_10,
				'vatMonth11' => $request->vat_month_11,
				'vatMonth12' => $request->vat_month_12,
				'grossProfitTotals' => $request->gross_profit_totals,
				'grossProfitMonth1' => $request->gross_profit_month_1,
				'grossProfitMonth2' => $request->gross_profit_month_2,
				'grossProfitMonth3' => $request->gross_profit_month_3,
				'grossProfitMonth4' => $request->gross_profit_month_4,
				'grossProfitMonth5' => $request->gross_profit_month_5,
				'grossProfitMonth6' => $request->gross_profit_month_6,
				'grossProfitMonth7' => $request->gross_profit_month_7,
				'grossProfitMonth8' => $request->gross_profit_month_8,
				'grossProfitMonth9' => $request->gross_profit_month_9,
				'grossProfitMonth10' => $request->gross_profit_month_10,
				'grossProfitMonth11' => $request->gross_profit_month_11,
				'grossProfitMonth12' => $request->gross_profit_month_12,
				'grossProfitNetTotals' => $request->gross_profit_net_totals,
				'grossProfitNetMonth1' => $request->gross_profit_net_month_1,
				'grossProfitNetMonth2' => $request->gross_profit_net_month_2,
				'grossProfitNetMonth3' => $request->gross_profit_net_month_3,
				'grossProfitNetMonth4' => $request->gross_profit_net_month_4,
				'grossProfitNetMonth5' => $request->gross_profit_net_month_5,
				'grossProfitNetMonth6' => $request->gross_profit_net_month_6,
				'grossProfitNetMonth7' => $request->gross_profit_net_month_7,
				'grossProfitNetMonth8' => $request->gross_profit_net_month_8,
				'grossProfitNetMonth9' => $request->gross_profit_net_month_9,
				'grossProfitNetMonth10' => $request->gross_profit_net_month_10,
				'grossProfitNetMonth11' => $request->gross_profit_net_month_11,
				'grossProfitNetMonth12' => $request->gross_profit_net_month_12,
				'gppOnGrossSalesTotals' => $request->gpp_on_gross_sales_totals,
				'gppOnGrossSalesMonth1' => $request->gpp_on_gross_sales_month_1,
				'gppOnGrossSalesMonth2' => $request->gpp_on_gross_sales_month_2,
				'gppOnGrossSalesMonth3' => $request->gpp_on_gross_sales_month_3,
				'gppOnGrossSalesMonth4' => $request->gpp_on_gross_sales_month_4,
				'gppOnGrossSalesMonth5' => $request->gpp_on_gross_sales_month_5,
				'gppOnGrossSalesMonth6' => $request->gpp_on_gross_sales_month_6,
				'gppOnGrossSalesMonth7' => $request->gpp_on_gross_sales_month_7,
				'gppOnGrossSalesMonth8' => $request->gpp_on_gross_sales_month_8,
				'gppOnGrossSalesMonth9' => $request->gpp_on_gross_sales_month_9,
				'gppOnGrossSalesMonth10' => $request->gpp_on_gross_sales_month_10,
				'gppOnGrossSalesMonth11' => $request->gpp_on_gross_sales_month_11,
				'gppOnGrossSalesMonth12' => $request->gpp_on_gross_sales_month_12,
				'gppOnNetSalesTotals' => $request->gpp_on_net_sales_totals,
				'gppOnNetSalesMonth1' => $request->gpp_on_net_sales_month_1,
				'gppOnNetSalesMonth2' => $request->gpp_on_net_sales_month_2,
				'gppOnNetSalesMonth3' => $request->gpp_on_net_sales_month_3,
				'gppOnNetSalesMonth4' => $request->gpp_on_net_sales_month_4,
				'gppOnNetSalesMonth5' => $request->gpp_on_net_sales_month_5,
				'gppOnNetSalesMonth6' => $request->gpp_on_net_sales_month_6,
				'gppOnNetSalesMonth7' => $request->gpp_on_net_sales_month_7,
				'gppOnNetSalesMonth8' => $request->gpp_on_net_sales_month_8,
				'gppOnNetSalesMonth9' => $request->gpp_on_net_sales_month_9,
				'gppOnNetSalesMonth10' => $request->gpp_on_net_sales_month_10,
				'gppOnNetSalesMonth11' => $request->gpp_on_net_sales_month_11,
				'gppOnNetSalesMonth12' => $request->gpp_on_net_sales_month_12,
				'labourTotals' => $request->labour_totals,
				'labourMonth1' => $request->labour_month_1,
				'labourMonth2' => $request->labour_month_2,
				'labourMonth3' => $request->labour_month_3,
				'labourMonth4' => $request->labour_month_4,
				'labourMonth5' => $request->labour_month_5,
				'labourMonth6' => $request->labour_month_6,
				'labourMonth7' => $request->labour_month_7,
				'labourMonth8' => $request->labour_month_8,
				'labourMonth9' => $request->labour_month_9,
				'labourMonth10' => $request->labour_month_10,
				'labourMonth11' => $request->labour_month_11,
				'labourMonth12' => $request->labour_month_12,
				'trainingTotals' => $request->training_totals,
				'trainingMonth1' => $request->training_month_1,
				'trainingMonth2' => $request->training_month_2,
				'trainingMonth3' => $request->training_month_3,
				'trainingMonth4' => $request->training_month_4,
				'trainingMonth5' => $request->training_month_5,
				'trainingMonth6' => $request->training_month_6,
				'trainingMonth7' => $request->training_month_7,
				'trainingMonth8' => $request->training_month_8,
				'trainingMonth9' => $request->training_month_9,
				'trainingMonth10' => $request->training_month_10,
				'trainingMonth11' => $request->training_month_11,
				'trainingMonth12' => $request->training_month_12,
				'cleaningTotals' => $request->cleaning_totals,
				'cleaningMonth1' => $request->cleaning_month_1,
				'cleaningMonth2' => $request->cleaning_month_2,
				'cleaningMonth3' => $request->cleaning_month_3,
				'cleaningMonth4' => $request->cleaning_month_4,
				'cleaningMonth5' => $request->cleaning_month_5,
				'cleaningMonth6' => $request->cleaning_month_6,
				'cleaningMonth7' => $request->cleaning_month_7,
				'cleaningMonth8' => $request->cleaning_month_8,
				'cleaningMonth9' => $request->cleaning_month_9,
				'cleaningMonth10' => $request->cleaning_month_10,
				'cleaningMonth11' => $request->cleaning_month_11,
				'cleaningMonth12' => $request->cleaning_month_12,
				'disposablesTotals' => $request->disposables_totals,
				'disposablesMonth1' => $request->disposables_month_1,
				'disposablesMonth2' => $request->disposables_month_2,
				'disposablesMonth3' => $request->disposables_month_3,
				'disposablesMonth4' => $request->disposables_month_4,
				'disposablesMonth5' => $request->disposables_month_5,
				'disposablesMonth6' => $request->disposables_month_6,
				'disposablesMonth7' => $request->disposables_month_7,
				'disposablesMonth8' => $request->disposables_month_8,
				'disposablesMonth9' => $request->disposables_month_9,
				'disposablesMonth10' => $request->disposables_month_10,
				'disposablesMonth11' => $request->disposables_month_11,
				'disposablesMonth12' => $request->disposables_month_12,
				'uniformTotals' => $request->uniform_totals,
				'uniformMonth1' => $request->uniform_month_1,
				'uniformMonth2' => $request->uniform_month_2,
				'uniformMonth3' => $request->uniform_month_3,
				'uniformMonth4' => $request->uniform_month_4,
				'uniformMonth5' => $request->uniform_month_5,
				'uniformMonth6' => $request->uniform_month_6,
				'uniformMonth7' => $request->uniform_month_7,
				'uniformMonth8' => $request->uniform_month_8,
				'uniformMonth9' => $request->uniform_month_9,
				'uniformMonth10' => $request->uniform_month_10,
				'uniformMonth11' => $request->uniform_month_11,
				'uniformMonth12' => $request->uniform_month_12,
				'delphAndCutleryTotals' => $request->delph_and_cutlery_totals,
				'delphAndCutleryMonth1' => $request->delph_and_cutlery_month_1,
				'delphAndCutleryMonth2' => $request->delph_and_cutlery_month_2,
				'delphAndCutleryMonth3' => $request->delph_and_cutlery_month_3,
				'delphAndCutleryMonth4' => $request->delph_and_cutlery_month_4,
				'delphAndCutleryMonth5' => $request->delph_and_cutlery_month_5,
				'delphAndCutleryMonth6' => $request->delph_and_cutlery_month_6,
				'delphAndCutleryMonth7' => $request->delph_and_cutlery_month_7,
				'delphAndCutleryMonth8' => $request->delph_and_cutlery_month_8,
				'delphAndCutleryMonth9' => $request->delph_and_cutlery_month_9,
				'delphAndCutleryMonth10' => $request->delph_and_cutlery_month_10,
				'delphAndCutleryMonth11' => $request->delph_and_cutlery_month_11,
				'delphAndCutleryMonth12' => $request->delph_and_cutlery_month_12,
				'bankChargesTotals' => $request->bank_charges_totals,
				'bankChargesMonth1' => $request->bank_charges_month_1,
				'bankChargesMonth2' => $request->bank_charges_month_2,
				'bankChargesMonth3' => $request->bank_charges_month_3,
				'bankChargesMonth4' => $request->bank_charges_month_4,
				'bankChargesMonth5' => $request->bank_charges_month_5,
				'bankChargesMonth6' => $request->bank_charges_month_6,
				'bankChargesMonth7' => $request->bank_charges_month_7,
				'bankChargesMonth8' => $request->bank_charges_month_8,
				'bankChargesMonth9' => $request->bank_charges_month_9,
				'bankChargesMonth10' => $request->bank_charges_month_10,
				'bankChargesMonth11' => $request->bank_charges_month_11,
				'bankChargesMonth12' => $request->bank_charges_month_12,
				'investmentTotals' => $request->investment_totals,
				'investmentMonth1' => $request->investment_month_1,
				'investmentMonth2' => $request->investment_month_2,
				'investmentMonth3' => $request->investment_month_3,
				'investmentMonth4' => $request->investment_month_4,
				'investmentMonth5' => $request->investment_month_5,
				'investmentMonth6' => $request->investment_month_6,
				'investmentMonth7' => $request->investment_month_7,
				'investmentMonth8' => $request->investment_month_8,
				'investmentMonth9' => $request->investment_month_9,
				'investmentMonth10' => $request->investment_month_10,
				'investmentMonth11' => $request->investment_month_11,
				'investmentMonth12' => $request->investment_month_12,
				'managementFeeTotals' => $request->management_fee_totals,
				'managementFeeMonth1' => $request->management_fee_month_1,
				'managementFeeMonth2' => $request->management_fee_month_2,
				'managementFeeMonth3' => $request->management_fee_month_3,
				'managementFeeMonth4' => $request->management_fee_month_4,
				'managementFeeMonth5' => $request->management_fee_month_5,
				'managementFeeMonth6' => $request->management_fee_month_6,
				'managementFeeMonth7' => $request->management_fee_month_7,
				'managementFeeMonth8' => $request->management_fee_month_8,
				'managementFeeMonth9' => $request->management_fee_month_9,
				'managementFeeMonth10' => $request->management_fee_month_10,
				'managementFeeMonth11' => $request->management_fee_month_11,
				'managementFeeMonth12' => $request->management_fee_month_12,
				'insuranceAndRelatedCostsTotals' => $request->insurance_and_related_costs_totals,
				'insuranceAndRelatedCostsMonth1' => $request->insurance_and_related_costs_month_1,
				'insuranceAndRelatedCostsMonth2' => $request->insurance_and_related_costs_month_2,
				'insuranceAndRelatedCostsMonth3' => $request->insurance_and_related_costs_month_3,
				'insuranceAndRelatedCostsMonth4' => $request->insurance_and_related_costs_month_4,
				'insuranceAndRelatedCostsMonth5' => $request->insurance_and_related_costs_month_5,
				'insuranceAndRelatedCostsMonth6' => $request->insurance_and_related_costs_month_6,
				'insuranceAndRelatedCostsMonth7' => $request->insurance_and_related_costs_month_7,
				'insuranceAndRelatedCostsMonth8' => $request->insurance_and_related_costs_month_8,
				'insuranceAndRelatedCostsMonth9' => $request->insurance_and_related_costs_month_9,
				'insuranceAndRelatedCostsMonth10' => $request->insurance_and_related_costs_month_10,
				'insuranceAndRelatedCostsMonth11' => $request->insurance_and_related_costs_month_11,
				'insuranceAndRelatedCostsMonth12' => $request->insurance_and_related_costs_month_12,
				'coffeeMachineRentalTotals' => $request->coffee_machine_rental_totals,
				'coffeeMachineRentalMonth1' => $request->coffee_machine_rental_month_1,
				'coffeeMachineRentalMonth2' => $request->coffee_machine_rental_month_2,
				'coffeeMachineRentalMonth3' => $request->coffee_machine_rental_month_3,
				'coffeeMachineRentalMonth4' => $request->coffee_machine_rental_month_4,
				'coffeeMachineRentalMonth5' => $request->coffee_machine_rental_month_5,
				'coffeeMachineRentalMonth6' => $request->coffee_machine_rental_month_6,
				'coffeeMachineRentalMonth7' => $request->coffee_machine_rental_month_7,
				'coffeeMachineRentalMonth8' => $request->coffee_machine_rental_month_8,
				'coffeeMachineRentalMonth9' => $request->coffee_machine_rental_month_9,
				'coffeeMachineRentalMonth10' => $request->coffee_machine_rental_month_10,
				'coffeeMachineRentalMonth11' => $request->coffee_machine_rental_month_11,
				'coffeeMachineRentalMonth12' => $request->coffee_machine_rental_month_12,
				'otherRentalTotals' => $request->other_rental_totals,
				'otherRentalMonth1' => $request->other_rental_month_1,
				'otherRentalMonth2' => $request->other_rental_month_2,
				'otherRentalMonth3' => $request->other_rental_month_3,
				'otherRentalMonth4' => $request->other_rental_month_4,
				'otherRentalMonth5' => $request->other_rental_month_5,
				'otherRentalMonth6' => $request->other_rental_month_6,
				'otherRentalMonth7' => $request->other_rental_month_7,
				'otherRentalMonth8' => $request->other_rental_month_8,
				'otherRentalMonth9' => $request->other_rental_month_9,
				'otherRentalMonth10' => $request->other_rental_month_10,
				'otherRentalMonth11' => $request->other_rental_month_11,
				'otherRentalMonth12' => $request->other_rental_month_12,
				'itSupportTotals' => $request->it_support_totals,
				'itSupportMonth1' => $request->it_support_month_1,
				'itSupportMonth2' => $request->it_support_month_2,
				'itSupportMonth3' => $request->it_support_month_3,
				'itSupportMonth4' => $request->it_support_month_4,
				'itSupportMonth5' => $request->it_support_month_5,
				'itSupportMonth6' => $request->it_support_month_6,
				'itSupportMonth7' => $request->it_support_month_7,
				'itSupportMonth8' => $request->it_support_month_8,
				'itSupportMonth9' => $request->it_support_month_9,
				'itSupportMonth10' => $request->it_support_month_10,
				'itSupportMonth11' => $request->it_support_month_11,
				'itSupportMonth12' => $request->it_support_month_12,
				'freeIssuesTotals' => $request->free_issues_totals,
				'freeIssuesMonth1' => $request->free_issues_month_1,
				'freeIssuesMonth2' => $request->free_issues_month_2,
				'freeIssuesMonth3' => $request->free_issues_month_3,
				'freeIssuesMonth4' => $request->free_issues_month_4,
				'freeIssuesMonth5' => $request->free_issues_month_5,
				'freeIssuesMonth6' => $request->free_issues_month_6,
				'freeIssuesMonth7' => $request->free_issues_month_7,
				'freeIssuesMonth8' => $request->free_issues_month_8,
				'freeIssuesMonth9' => $request->free_issues_month_9,
				'freeIssuesMonth10' => $request->free_issues_month_10,
				'freeIssuesMonth11' => $request->free_issues_month_11,
				'freeIssuesMonth12' => $request->free_issues_month_12,
				'marketingTotals' => $request->marketing_totals,
				'marketingMonth1' => $request->marketing_month_1,
				'marketingMonth2' => $request->marketing_month_2,
				'marketingMonth3' => $request->marketing_month_3,
				'marketingMonth4' => $request->marketing_month_4,
				'marketingMonth5' => $request->marketing_month_5,
				'marketingMonth6' => $request->marketing_month_6,
				'marketingMonth7' => $request->marketing_month_7,
				'marketingMonth8' => $request->marketing_month_8,
				'marketingMonth9' => $request->marketing_month_9,
				'marketingMonth10' => $request->marketing_month_10,
				'marketingMonth11' => $request->marketing_month_11,
				'marketingMonth12' => $request->marketing_month_12,
				'setUpCostsTotals' => $request->set_up_costs_totals,
				'setUpCostsMonth1' => $request->set_up_costs_month_1,
				'setUpCostsMonth2' => $request->set_up_costs_month_2,
				'setUpCostsMonth3' => $request->set_up_costs_month_3,
				'setUpCostsMonth4' => $request->set_up_costs_month_4,
				'setUpCostsMonth5' => $request->set_up_costs_month_5,
				'setUpCostsMonth6' => $request->set_up_costs_month_6,
				'setUpCostsMonth7' => $request->set_up_costs_month_7,
				'setUpCostsMonth8' => $request->set_up_costs_month_8,
				'setUpCostsMonth9' => $request->set_up_costs_month_9,
				'setUpCostsMonth10' => $request->set_up_costs_month_10,
				'setUpCostsMonth11' => $request->set_up_costs_month_11,
				'setUpCostsMonth12' => $request->set_up_costs_month_12,
				'creditCardMachinesTotals' => $request->credit_card_machines_totals,
				'creditCardMachinesMonth1' => $request->credit_card_machines_month_1,
				'creditCardMachinesMonth2' => $request->credit_card_machines_month_2,
				'creditCardMachinesMonth3' => $request->credit_card_machines_month_3,
				'creditCardMachinesMonth4' => $request->credit_card_machines_month_4,
				'creditCardMachinesMonth5' => $request->credit_card_machines_month_5,
				'creditCardMachinesMonth6' => $request->credit_card_machines_month_6,
				'creditCardMachinesMonth7' => $request->credit_card_machines_month_7,
				'creditCardMachinesMonth8' => $request->credit_card_machines_month_8,
				'creditCardMachinesMonth9' => $request->credit_card_machines_month_9,
				'creditCardMachinesMonth10' => $request->credit_card_machines_month_10,
				'creditCardMachinesMonth11' => $request->credit_card_machines_month_11,
				'creditCardMachinesMonth12' => $request->credit_card_machines_month_12,
				'bizimplyCostTotals' => $request->bizimply_cost_totals,
				'bizimplyCostMonth1' => $request->bizimply_cost_month_1,
				'bizimplyCostMonth2' => $request->bizimply_cost_month_2,
				'bizimplyCostMonth3' => $request->bizimply_cost_month_3,
				'bizimplyCostMonth4' => $request->bizimply_cost_month_4,
				'bizimplyCostMonth5' => $request->bizimply_cost_month_5,
				'bizimplyCostMonth6' => $request->bizimply_cost_month_6,
				'bizimplyCostMonth7' => $request->bizimply_cost_month_7,
				'bizimplyCostMonth8' => $request->bizimply_cost_month_8,
				'bizimplyCostMonth9' => $request->bizimply_cost_month_9,
				'bizimplyCostMonth10' => $request->bizimply_cost_month_10,
				'bizimplyCostMonth11' => $request->bizimply_cost_month_11,
				'bizimplyCostMonth12' => $request->bizimply_cost_month_12,
				'kitchtechTotals' => $request->kitchtech_totals,
				'kitchtechMonth1' => $request->kitchtech_month_1,
				'kitchtechMonth2' => $request->kitchtech_month_2,
				'kitchtechMonth3' => $request->kitchtech_month_3,
				'kitchtechMonth4' => $request->kitchtech_month_4,
				'kitchtechMonth5' => $request->kitchtech_month_5,
				'kitchtechMonth6' => $request->kitchtech_month_6,
				'kitchtechMonth7' => $request->kitchtech_month_7,
				'kitchtechMonth8' => $request->kitchtech_month_8,
				'kitchtechMonth9' => $request->kitchtech_month_9,
				'kitchtechMonth10' => $request->kitchtech_month_10,
				'kitchtechMonth11' => $request->kitchtech_month_11,
				'kitchtechMonth12' => $request->kitchtech_month_12,
				'budgetTotal' => $request->budget_total_totals,
				'netSub' => $request->net_sub_totals,
			]
		);
	}

	public function phasedBudgetPost(Request $request)
	{
		$phasedBudget = new \App\PhasedBudget;
		$phasedBudget->user_id = $request->supervisor_id;
		$phasedBudget->trading_account_date = Carbon::now()->format('Y-m-d');

		if ($request->has('unit_name_1')) {
			$phasedBudget->unit_name = $request->unit_name_1;
		} else {
			$phasedBudget->unit_name = $request->unit_name;
		}

		$phasedBudget->budget_start_date = Carbon::createFromFormat('d-m-Y', $request->budget_start_date)->format('Y-m-d');
		$phasedBudget->budget_end_date = Carbon::createFromFormat('d-m-Y', $request->budget_end_date)->format('Y-m-d');
		$phasedBudget->entered_by = $request->entered_by;
		$phasedBudget->approved_by = $request->approved_by;
		$phasedBudget->contract_type_id = $request->contract_type;
		$phasedBudget->budget_type_id = $request->budget_type;
		$phasedBudget->head_count_totals = $request->head_count_totals;
		$phasedBudget->head_count_month_1 = $request->head_count_month_1;
		$phasedBudget->head_count_month_2 = $request->head_count_month_2;
		$phasedBudget->head_count_month_3 = $request->head_count_month_3;
		$phasedBudget->head_count_month_4 = $request->head_count_month_4;
		$phasedBudget->head_count_month_5 = $request->head_count_month_5;
		$phasedBudget->head_count_month_6 = $request->head_count_month_6;
		$phasedBudget->head_count_month_7 = $request->head_count_month_7;
		$phasedBudget->head_count_month_8 = $request->head_count_month_8;
		$phasedBudget->head_count_month_9 = $request->head_count_month_9;
		$phasedBudget->head_count_month_10 = $request->head_count_month_10;
		$phasedBudget->head_count_month_11 = $request->head_count_month_11;
		$phasedBudget->head_count_month_12 = $request->head_count_month_12;
		$phasedBudget->num_trading_days_totals = $request->num_trading_days_totals;
		$phasedBudget->num_trading_days_month_1 = $request->num_trading_days_month_1;
		$phasedBudget->num_trading_days_month_2 = $request->num_trading_days_month_2;
		$phasedBudget->num_trading_days_month_3 = $request->num_trading_days_month_3;
		$phasedBudget->num_trading_days_month_4 = $request->num_trading_days_month_4;
		$phasedBudget->num_trading_days_month_5 = $request->num_trading_days_month_5;
		$phasedBudget->num_trading_days_month_6 = $request->num_trading_days_month_6;
		$phasedBudget->num_trading_days_month_7 = $request->num_trading_days_month_7;
		$phasedBudget->num_trading_days_month_8 = $request->num_trading_days_month_8;
		$phasedBudget->num_trading_days_month_9 = $request->num_trading_days_month_9;
		$phasedBudget->num_trading_days_month_10 = $request->num_trading_days_month_10;
		$phasedBudget->num_trading_days_month_11 = $request->num_trading_days_month_11;
		$phasedBudget->num_trading_days_month_12 = $request->num_trading_days_month_12;
		$phasedBudget->num_of_weeks_totals = $request->num_of_weeks_totals;
		$phasedBudget->num_of_weeks_month_1 = $request->num_of_weeks_month_1;
		$phasedBudget->num_of_weeks_month_2 = $request->num_of_weeks_month_2;
		$phasedBudget->num_of_weeks_month_3 = $request->num_of_weeks_month_3;
		$phasedBudget->num_of_weeks_month_4 = $request->num_of_weeks_month_4;
		$phasedBudget->num_of_weeks_month_5 = $request->num_of_weeks_month_5;
		$phasedBudget->num_of_weeks_month_6 = $request->num_of_weeks_month_6;
		$phasedBudget->num_of_weeks_month_7 = $request->num_of_weeks_month_7;
		$phasedBudget->num_of_weeks_month_8 = $request->num_of_weeks_month_8;
		$phasedBudget->num_of_weeks_month_9 = $request->num_of_weeks_month_9;
		$phasedBudget->num_of_weeks_month_10 = $request->num_of_weeks_month_10;
		$phasedBudget->num_of_weeks_month_11 = $request->num_of_weeks_month_11;
		$phasedBudget->num_of_weeks_month_12 = $request->num_of_weeks_month_12;
		$phasedBudget->gross_sales_totals = str_replace(',', '', $request->gross_sales_totals);
		$phasedBudget->gross_sales_month_1 = str_replace(',', '', $request->gross_sales_month_1);
		$phasedBudget->gross_sales_month_2 = str_replace(',', '', $request->gross_sales_month_2);
		$phasedBudget->gross_sales_month_3 = str_replace(',', '', $request->gross_sales_month_3);
		$phasedBudget->gross_sales_month_4 = str_replace(',', '', $request->gross_sales_month_4);
		$phasedBudget->gross_sales_month_5 = str_replace(',', '', $request->gross_sales_month_5);
		$phasedBudget->gross_sales_month_6 = str_replace(',', '', $request->gross_sales_month_6);
		$phasedBudget->gross_sales_month_7 = str_replace(',', '', $request->gross_sales_month_7);
		$phasedBudget->gross_sales_month_8 = str_replace(',', '', $request->gross_sales_month_8);
		$phasedBudget->gross_sales_month_9 = str_replace(',', '', $request->gross_sales_month_9);
		$phasedBudget->gross_sales_month_10 = str_replace(',', '', $request->gross_sales_month_10);
		$phasedBudget->gross_sales_month_11 = str_replace(',', '', $request->gross_sales_month_11);
		$phasedBudget->gross_sales_month_12 = str_replace(',', '', $request->gross_sales_month_12);
		$phasedBudget->vat_totals = str_replace(',', '', $request->vat_totals);
		$phasedBudget->vat_month_1 = str_replace(',', '', $request->vat_month_1);
		$phasedBudget->vat_month_2 = str_replace(',', '', $request->vat_month_2);
		$phasedBudget->vat_month_3 = str_replace(',', '', $request->vat_month_3);
		$phasedBudget->vat_month_4 = str_replace(',', '', $request->vat_month_4);
		$phasedBudget->vat_month_5 = str_replace(',', '', $request->vat_month_5);
		$phasedBudget->vat_month_6 = str_replace(',', '', $request->vat_month_6);
		$phasedBudget->vat_month_7 = str_replace(',', '', $request->vat_month_7);
		$phasedBudget->vat_month_8 = str_replace(',', '', $request->vat_month_8);
		$phasedBudget->vat_month_9 = str_replace(',', '', $request->vat_month_9);
		$phasedBudget->vat_month_10 = str_replace(',', '', $request->vat_month_10);
		$phasedBudget->vat_month_11 = str_replace(',', '', $request->vat_month_11);
		$phasedBudget->vat_month_12 = str_replace(',', '', $request->vat_month_12);
		$phasedBudget->net_sales_totals = str_replace(',', '', $request->net_sales_totals);
		$phasedBudget->net_sales_month_1 = str_replace(',', '', $request->net_sales_month_1);
		$phasedBudget->net_sales_month_2 = str_replace(',', '', $request->net_sales_month_2);
		$phasedBudget->net_sales_month_3 = str_replace(',', '', $request->net_sales_month_3);
		$phasedBudget->net_sales_month_4 = str_replace(',', '', $request->net_sales_month_4);
		$phasedBudget->net_sales_month_5 = str_replace(',', '', $request->net_sales_month_5);
		$phasedBudget->net_sales_month_6 = str_replace(',', '', $request->net_sales_month_6);
		$phasedBudget->net_sales_month_7 = str_replace(',', '', $request->net_sales_month_7);
		$phasedBudget->net_sales_month_8 = str_replace(',', '', $request->net_sales_month_8);
		$phasedBudget->net_sales_month_9 = str_replace(',', '', $request->net_sales_month_9);
		$phasedBudget->net_sales_month_10 = str_replace(',', '', $request->net_sales_month_10);
		$phasedBudget->net_sales_month_11 = str_replace(',', '', $request->net_sales_month_11);
		$phasedBudget->net_sales_month_12 = str_replace(',', '', $request->net_sales_month_12);
		$phasedBudget->cost_of_sales_totals = str_replace(',', '', $request->cost_of_sales_totals);
		$phasedBudget->cost_of_sales_month_1 = str_replace(',', '', $request->cost_of_sales_month_1);
		$phasedBudget->cost_of_sales_month_2 = str_replace(',', '', $request->cost_of_sales_month_2);
		$phasedBudget->cost_of_sales_month_3 = str_replace(',', '', $request->cost_of_sales_month_3);
		$phasedBudget->cost_of_sales_month_4 = str_replace(',', '', $request->cost_of_sales_month_4);
		$phasedBudget->cost_of_sales_month_5 = str_replace(',', '', $request->cost_of_sales_month_5);
		$phasedBudget->cost_of_sales_month_6 = str_replace(',', '', $request->cost_of_sales_month_6);
		$phasedBudget->cost_of_sales_month_7 = str_replace(',', '', $request->cost_of_sales_month_7);
		$phasedBudget->cost_of_sales_month_8 = str_replace(',', '', $request->cost_of_sales_month_8);
		$phasedBudget->cost_of_sales_month_9 = str_replace(',', '', $request->cost_of_sales_month_9);
		$phasedBudget->cost_of_sales_month_10 = str_replace(',', '', $request->cost_of_sales_month_10);
		$phasedBudget->cost_of_sales_month_11 = str_replace(',', '', $request->cost_of_sales_month_11);
		$phasedBudget->cost_of_sales_month_12 = str_replace(',', '', $request->cost_of_sales_month_12);
		$phasedBudget->gross_profit_totals = str_replace(',', '', $request->gross_profit_totals);
		$phasedBudget->gross_profit_month_1 = str_replace(',', '', $request->gross_profit_month_1);
		$phasedBudget->gross_profit_month_2 = str_replace(',', '', $request->gross_profit_month_2);
		$phasedBudget->gross_profit_month_3 = str_replace(',', '', $request->gross_profit_month_3);
		$phasedBudget->gross_profit_month_4 = str_replace(',', '', $request->gross_profit_month_4);
		$phasedBudget->gross_profit_month_5 = str_replace(',', '', $request->gross_profit_month_5);
		$phasedBudget->gross_profit_month_6 = str_replace(',', '', $request->gross_profit_month_6);
		$phasedBudget->gross_profit_month_7 = str_replace(',', '', $request->gross_profit_month_7);
		$phasedBudget->gross_profit_month_8 = str_replace(',', '', $request->gross_profit_month_8);
		$phasedBudget->gross_profit_month_9 = str_replace(',', '', $request->gross_profit_month_9);
		$phasedBudget->gross_profit_month_10 = str_replace(',', '', $request->gross_profit_month_10);
		$phasedBudget->gross_profit_month_11 = str_replace(',', '', $request->gross_profit_month_11);
		$phasedBudget->gross_profit_month_12 = str_replace(',', '', $request->gross_profit_month_12);
		$phasedBudget->gross_profit_net_totals = str_replace(',', '', $request->gross_profit_net_totals);
		$phasedBudget->gross_profit_net_month_1 = str_replace(',', '', $request->gross_profit_net_month_1);
		$phasedBudget->gross_profit_net_month_2 = str_replace(',', '', $request->gross_profit_net_month_2);
		$phasedBudget->gross_profit_net_month_3 = str_replace(',', '', $request->gross_profit_net_month_3);
		$phasedBudget->gross_profit_net_month_4 = str_replace(',', '', $request->gross_profit_net_month_4);
		$phasedBudget->gross_profit_net_month_5 = str_replace(',', '', $request->gross_profit_net_month_5);
		$phasedBudget->gross_profit_net_month_6 = str_replace(',', '', $request->gross_profit_net_month_6);
		$phasedBudget->gross_profit_net_month_7 = str_replace(',', '', $request->gross_profit_net_month_7);
		$phasedBudget->gross_profit_net_month_8 = str_replace(',', '', $request->gross_profit_net_month_8);
		$phasedBudget->gross_profit_net_month_9 = str_replace(',', '', $request->gross_profit_net_month_9);
		$phasedBudget->gross_profit_net_month_10 = str_replace(',', '', $request->gross_profit_net_month_10);
		$phasedBudget->gross_profit_net_month_11 = str_replace(',', '', $request->gross_profit_net_month_11);
		$phasedBudget->gross_profit_net_month_12 = str_replace(',', '', $request->gross_profit_net_month_12);
		$phasedBudget->gpp_on_gross_sales_totals = str_replace('%', '', $request->gpp_on_gross_sales_totals);
		$phasedBudget->gpp_on_gross_sales_month_1 = str_replace('%', '', $request->gpp_on_gross_sales_month_1);
		$phasedBudget->gpp_on_gross_sales_month_2 = str_replace('%', '', $request->gpp_on_gross_sales_month_2);
		$phasedBudget->gpp_on_gross_sales_month_3 = str_replace('%', '', $request->gpp_on_gross_sales_month_3);
		$phasedBudget->gpp_on_gross_sales_month_4 = str_replace('%', '', $request->gpp_on_gross_sales_month_4);
		$phasedBudget->gpp_on_gross_sales_month_5 = str_replace('%', '', $request->gpp_on_gross_sales_month_5);
		$phasedBudget->gpp_on_gross_sales_month_6 = str_replace('%', '', $request->gpp_on_gross_sales_month_6);
		$phasedBudget->gpp_on_gross_sales_month_7 = str_replace('%', '', $request->gpp_on_gross_sales_month_7);
		$phasedBudget->gpp_on_gross_sales_month_8 = str_replace('%', '', $request->gpp_on_gross_sales_month_8);
		$phasedBudget->gpp_on_gross_sales_month_9 = str_replace('%', '', $request->gpp_on_gross_sales_month_9);
		$phasedBudget->gpp_on_gross_sales_month_10 = str_replace('%', '', $request->gpp_on_gross_sales_month_10);
		$phasedBudget->gpp_on_gross_sales_month_11 = str_replace('%', '', $request->gpp_on_gross_sales_month_11);
		$phasedBudget->gpp_on_gross_sales_month_12 = str_replace('%', '', $request->gpp_on_gross_sales_month_12);
		$phasedBudget->gpp_on_net_sales_totals = str_replace('%', '', $request->gpp_on_net_sales_totals);
		$phasedBudget->gpp_on_net_sales_month_1 = str_replace('%', '', $request->gpp_on_net_sales_month_1);
		$phasedBudget->gpp_on_net_sales_month_2 = str_replace('%', '', $request->gpp_on_net_sales_month_2);
		$phasedBudget->gpp_on_net_sales_month_3 = str_replace('%', '', $request->gpp_on_net_sales_month_3);
		$phasedBudget->gpp_on_net_sales_month_4 = str_replace('%', '', $request->gpp_on_net_sales_month_4);
		$phasedBudget->gpp_on_net_sales_month_5 = str_replace('%', '', $request->gpp_on_net_sales_month_5);
		$phasedBudget->gpp_on_net_sales_month_6 = str_replace('%', '', $request->gpp_on_net_sales_month_6);
		$phasedBudget->gpp_on_net_sales_month_7 = str_replace('%', '', $request->gpp_on_net_sales_month_7);
		$phasedBudget->gpp_on_net_sales_month_8 = str_replace('%', '', $request->gpp_on_net_sales_month_8);
		$phasedBudget->gpp_on_net_sales_month_9 = str_replace('%', '', $request->gpp_on_net_sales_month_9);
		$phasedBudget->gpp_on_net_sales_month_10 = str_replace('%', '', $request->gpp_on_net_sales_month_10);
		$phasedBudget->gpp_on_net_sales_month_11 = str_replace('%', '', $request->gpp_on_net_sales_month_11);
		$phasedBudget->gpp_on_net_sales_month_12 = str_replace('%', '', $request->gpp_on_net_sales_month_12);
		$phasedBudget->labour_hours_totals = str_replace(',', '', $request->labour_hours_totals);
		$phasedBudget->labour_hours_month_1 = str_replace(',', '', $request->labour_hours_month_1);
		$phasedBudget->labour_hours_month_2 = str_replace(',', '', $request->labour_hours_month_2);
		$phasedBudget->labour_hours_month_3 = str_replace(',', '', $request->labour_hours_month_3);
		$phasedBudget->labour_hours_month_4 = str_replace(',', '', $request->labour_hours_month_4);
		$phasedBudget->labour_hours_month_5 = str_replace(',', '', $request->labour_hours_month_5);
		$phasedBudget->labour_hours_month_6 = str_replace(',', '', $request->labour_hours_month_6);
		$phasedBudget->labour_hours_month_7 = str_replace(',', '', $request->labour_hours_month_7);
		$phasedBudget->labour_hours_month_8 = str_replace(',', '', $request->labour_hours_month_8);
		$phasedBudget->labour_hours_month_9 = str_replace(',', '', $request->labour_hours_month_9);
		$phasedBudget->labour_hours_month_10 = str_replace(',', '', $request->labour_hours_month_10);
		$phasedBudget->labour_hours_month_11 = str_replace(',', '', $request->labour_hours_month_11);
		$phasedBudget->labour_hours_month_12 = str_replace(',', '', $request->labour_hours_month_12);
		$phasedBudget->labour_totals = str_replace(',', '', $request->labour_totals);
		$phasedBudget->labour_month_1 = str_replace(',', '', $request->labour_month_1);
		$phasedBudget->labour_month_2 = str_replace(',', '', $request->labour_month_2);
		$phasedBudget->labour_month_3 = str_replace(',', '', $request->labour_month_3);
		$phasedBudget->labour_month_4 = str_replace(',', '', $request->labour_month_4);
		$phasedBudget->labour_month_5 = str_replace(',', '', $request->labour_month_5);
		$phasedBudget->labour_month_6 = str_replace(',', '', $request->labour_month_6);
		$phasedBudget->labour_month_7 = str_replace(',', '', $request->labour_month_7);
		$phasedBudget->labour_month_8 = str_replace(',', '', $request->labour_month_8);
		$phasedBudget->labour_month_9 = str_replace(',', '', $request->labour_month_9);
		$phasedBudget->labour_month_10 = str_replace(',', '', $request->labour_month_10);
		$phasedBudget->labour_month_11 = str_replace(',', '', $request->labour_month_11);
		$phasedBudget->labour_month_12 = str_replace(',', '', $request->labour_month_12);
		$phasedBudget->training_totals = str_replace(',', '', $request->training_totals);
		$phasedBudget->training_month_1 = str_replace(',', '', $request->training_month_1);
		$phasedBudget->training_month_2 = str_replace(',', '', $request->training_month_2);
		$phasedBudget->training_month_3 = str_replace(',', '', $request->training_month_3);
		$phasedBudget->training_month_4 = str_replace(',', '', $request->training_month_4);
		$phasedBudget->training_month_5 = str_replace(',', '', $request->training_month_5);
		$phasedBudget->training_month_6 = str_replace(',', '', $request->training_month_6);
		$phasedBudget->training_month_7 = str_replace(',', '', $request->training_month_7);
		$phasedBudget->training_month_8 = str_replace(',', '', $request->training_month_8);
		$phasedBudget->training_month_9 = str_replace(',', '', $request->training_month_9);
		$phasedBudget->training_month_10 = str_replace(',', '', $request->training_month_10);
		$phasedBudget->training_month_11 = str_replace(',', '', $request->training_month_11);
		$phasedBudget->training_month_12 = str_replace(',', '', $request->training_month_12);
		$phasedBudget->cleaning_totals = str_replace(',', '', $request->cleaning_totals);
		$phasedBudget->cleaning_month_1 = str_replace(',', '', $request->cleaning_month_1);
		$phasedBudget->cleaning_month_2 = str_replace(',', '', $request->cleaning_month_2);
		$phasedBudget->cleaning_month_3 = str_replace(',', '', $request->cleaning_month_3);
		$phasedBudget->cleaning_month_4 = str_replace(',', '', $request->cleaning_month_4);
		$phasedBudget->cleaning_month_5 = str_replace(',', '', $request->cleaning_month_5);
		$phasedBudget->cleaning_month_6 = str_replace(',', '', $request->cleaning_month_6);
		$phasedBudget->cleaning_month_7 = str_replace(',', '', $request->cleaning_month_7);
		$phasedBudget->cleaning_month_8 = str_replace(',', '', $request->cleaning_month_8);
		$phasedBudget->cleaning_month_9 = str_replace(',', '', $request->cleaning_month_9);
		$phasedBudget->cleaning_month_10 = str_replace(',', '', $request->cleaning_month_10);
		$phasedBudget->cleaning_month_11 = str_replace(',', '', $request->cleaning_month_11);
		$phasedBudget->cleaning_month_12 = str_replace(',', '', $request->cleaning_month_12);
		$phasedBudget->disposables_totals = str_replace(',', '', $request->disposables_totals);
		$phasedBudget->disposables_month_1 = str_replace(',', '', $request->disposables_month_1);
		$phasedBudget->disposables_month_2 = str_replace(',', '', $request->disposables_month_2);
		$phasedBudget->disposables_month_3 = str_replace(',', '', $request->disposables_month_3);
		$phasedBudget->disposables_month_4 = str_replace(',', '', $request->disposables_month_4);
		$phasedBudget->disposables_month_5 = str_replace(',', '', $request->disposables_month_5);
		$phasedBudget->disposables_month_6 = str_replace(',', '', $request->disposables_month_6);
		$phasedBudget->disposables_month_7 = str_replace(',', '', $request->disposables_month_7);
		$phasedBudget->disposables_month_8 = str_replace(',', '', $request->disposables_month_8);
		$phasedBudget->disposables_month_9 = str_replace(',', '', $request->disposables_month_9);
		$phasedBudget->disposables_month_10 = str_replace(',', '', $request->disposables_month_10);
		$phasedBudget->disposables_month_11 = str_replace(',', '', $request->disposables_month_11);
		$phasedBudget->disposables_month_12 = str_replace(',', '', $request->disposables_month_12);
		$phasedBudget->uniform_totals = str_replace(',', '', $request->uniform_totals);
		$phasedBudget->uniform_month_1 = str_replace(',', '', $request->uniform_month_1);
		$phasedBudget->uniform_month_2 = str_replace(',', '', $request->uniform_month_2);
		$phasedBudget->uniform_month_3 = str_replace(',', '', $request->uniform_month_3);
		$phasedBudget->uniform_month_4 = str_replace(',', '', $request->uniform_month_4);
		$phasedBudget->uniform_month_5 = str_replace(',', '', $request->uniform_month_5);
		$phasedBudget->uniform_month_6 = str_replace(',', '', $request->uniform_month_6);
		$phasedBudget->uniform_month_7 = str_replace(',', '', $request->uniform_month_7);
		$phasedBudget->uniform_month_8 = str_replace(',', '', $request->uniform_month_8);
		$phasedBudget->uniform_month_9 = str_replace(',', '', $request->uniform_month_9);
		$phasedBudget->uniform_month_10 = str_replace(',', '', $request->uniform_month_10);
		$phasedBudget->uniform_month_11 = str_replace(',', '', $request->uniform_month_11);
		$phasedBudget->uniform_month_12 = str_replace(',', '', $request->uniform_month_12);
		$phasedBudget->delph_and_cutlery_totals = str_replace(',', '', $request->delph_and_cutlery_totals);
		$phasedBudget->delph_and_cutlery_month_1 = str_replace(',', '', $request->delph_and_cutlery_month_1);
		$phasedBudget->delph_and_cutlery_month_2 = str_replace(',', '', $request->delph_and_cutlery_month_2);
		$phasedBudget->delph_and_cutlery_month_3 = str_replace(',', '', $request->delph_and_cutlery_month_3);
		$phasedBudget->delph_and_cutlery_month_4 = str_replace(',', '', $request->delph_and_cutlery_month_4);
		$phasedBudget->delph_and_cutlery_month_5 = str_replace(',', '', $request->delph_and_cutlery_month_5);
		$phasedBudget->delph_and_cutlery_month_6 = str_replace(',', '', $request->delph_and_cutlery_month_6);
		$phasedBudget->delph_and_cutlery_month_7 = str_replace(',', '', $request->delph_and_cutlery_month_7);
		$phasedBudget->delph_and_cutlery_month_8 = str_replace(',', '', $request->delph_and_cutlery_month_8);
		$phasedBudget->delph_and_cutlery_month_9 = str_replace(',', '', $request->delph_and_cutlery_month_9);
		$phasedBudget->delph_and_cutlery_month_10 = str_replace(',', '', $request->delph_and_cutlery_month_10);
		$phasedBudget->delph_and_cutlery_month_11 = str_replace(',', '', $request->delph_and_cutlery_month_11);
		$phasedBudget->delph_and_cutlery_month_12 = str_replace(',', '', $request->delph_and_cutlery_month_12);
		$phasedBudget->bank_charges_totals = str_replace(',', '', $request->bank_charges_totals);
		$phasedBudget->bank_charges_month_1 = str_replace(',', '', $request->bank_charges_month_1);
		$phasedBudget->bank_charges_month_2 = str_replace(',', '', $request->bank_charges_month_2);
		$phasedBudget->bank_charges_month_3 = str_replace(',', '', $request->bank_charges_month_3);
		$phasedBudget->bank_charges_month_4 = str_replace(',', '', $request->bank_charges_month_4);
		$phasedBudget->bank_charges_month_5 = str_replace(',', '', $request->bank_charges_month_5);
		$phasedBudget->bank_charges_month_6 = str_replace(',', '', $request->bank_charges_month_6);
		$phasedBudget->bank_charges_month_7 = str_replace(',', '', $request->bank_charges_month_7);
		$phasedBudget->bank_charges_month_8 = str_replace(',', '', $request->bank_charges_month_8);
		$phasedBudget->bank_charges_month_9 = str_replace(',', '', $request->bank_charges_month_9);
		$phasedBudget->bank_charges_month_10 = str_replace(',', '', $request->bank_charges_month_10);
		$phasedBudget->bank_charges_month_11 = str_replace(',', '', $request->bank_charges_month_11);
		$phasedBudget->bank_charges_month_12 = str_replace(',', '', $request->bank_charges_month_12);
		$phasedBudget->investment_totals = str_replace(',', '', $request->investment_totals);
		$phasedBudget->investment_month_1 = str_replace(',', '', $request->investment_month_1);
		$phasedBudget->investment_month_2 = str_replace(',', '', $request->investment_month_2);
		$phasedBudget->investment_month_3 = str_replace(',', '', $request->investment_month_3);
		$phasedBudget->investment_month_4 = str_replace(',', '', $request->investment_month_4);
		$phasedBudget->investment_month_5 = str_replace(',', '', $request->investment_month_5);
		$phasedBudget->investment_month_6 = str_replace(',', '', $request->investment_month_6);
		$phasedBudget->investment_month_7 = str_replace(',', '', $request->investment_month_7);
		$phasedBudget->investment_month_8 = str_replace(',', '', $request->investment_month_8);
		$phasedBudget->investment_month_9 = str_replace(',', '', $request->investment_month_9);
		$phasedBudget->investment_month_10 = str_replace(',', '', $request->investment_month_10);
		$phasedBudget->investment_month_11 = str_replace(',', '', $request->investment_month_11);
		$phasedBudget->investment_month_12 = str_replace(',', '', $request->investment_month_12);
		$phasedBudget->management_fee_totals = str_replace(',', '', $request->management_fee_totals);
		$phasedBudget->management_fee_month_1 = str_replace(',', '', $request->management_fee_month_1);
		$phasedBudget->management_fee_month_2 = str_replace(',', '', $request->management_fee_month_2);
		$phasedBudget->management_fee_month_3 = str_replace(',', '', $request->management_fee_month_3);
		$phasedBudget->management_fee_month_4 = str_replace(',', '', $request->management_fee_month_4);
		$phasedBudget->management_fee_month_5 = str_replace(',', '', $request->management_fee_month_5);
		$phasedBudget->management_fee_month_6 = str_replace(',', '', $request->management_fee_month_6);
		$phasedBudget->management_fee_month_7 = str_replace(',', '', $request->management_fee_month_7);
		$phasedBudget->management_fee_month_8 = str_replace(',', '', $request->management_fee_month_8);
		$phasedBudget->management_fee_month_9 = str_replace(',', '', $request->management_fee_month_9);
		$phasedBudget->management_fee_month_10 = str_replace(',', '', $request->management_fee_month_10);
		$phasedBudget->management_fee_month_11 = str_replace(',', '', $request->management_fee_month_11);
		$phasedBudget->management_fee_month_12 = str_replace(',', '', $request->management_fee_month_12);
		$phasedBudget->insurance_and_related_costs_totals = str_replace(',', '', $request->insurance_and_related_costs_totals);
		$phasedBudget->insurance_and_related_costs_month_1 = str_replace(',', '', $request->insurance_and_related_costs_month_1);
		$phasedBudget->insurance_and_related_costs_month_2 = str_replace(',', '', $request->insurance_and_related_costs_month_2);
		$phasedBudget->insurance_and_related_costs_month_3 = str_replace(',', '', $request->insurance_and_related_costs_month_3);
		$phasedBudget->insurance_and_related_costs_month_4 = str_replace(',', '', $request->insurance_and_related_costs_month_4);
		$phasedBudget->insurance_and_related_costs_month_5 = str_replace(',', '', $request->insurance_and_related_costs_month_5);
		$phasedBudget->insurance_and_related_costs_month_6 = str_replace(',', '', $request->insurance_and_related_costs_month_6);
		$phasedBudget->insurance_and_related_costs_month_7 = str_replace(',', '', $request->insurance_and_related_costs_month_7);
		$phasedBudget->insurance_and_related_costs_month_8 = str_replace(',', '', $request->insurance_and_related_costs_month_8);
		$phasedBudget->insurance_and_related_costs_month_9 = str_replace(',', '', $request->insurance_and_related_costs_month_9);
		$phasedBudget->insurance_and_related_costs_month_10 = str_replace(',', '', $request->insurance_and_related_costs_month_10);
		$phasedBudget->insurance_and_related_costs_month_11 = str_replace(',', '', $request->insurance_and_related_costs_month_11);
		$phasedBudget->insurance_and_related_costs_month_12 = str_replace(',', '', $request->insurance_and_related_costs_month_12);
		$phasedBudget->coffee_machine_rental_totals = str_replace(',', '', $request->coffee_machine_rental_totals);
		$phasedBudget->coffee_machine_rental_month_1 = str_replace(',', '', $request->coffee_machine_rental_month_1);
		$phasedBudget->coffee_machine_rental_month_2 = str_replace(',', '', $request->coffee_machine_rental_month_2);
		$phasedBudget->coffee_machine_rental_month_3 = str_replace(',', '', $request->coffee_machine_rental_month_3);
		$phasedBudget->coffee_machine_rental_month_4 = str_replace(',', '', $request->coffee_machine_rental_month_4);
		$phasedBudget->coffee_machine_rental_month_5 = str_replace(',', '', $request->coffee_machine_rental_month_5);
		$phasedBudget->coffee_machine_rental_month_6 = str_replace(',', '', $request->coffee_machine_rental_month_6);
		$phasedBudget->coffee_machine_rental_month_7 = str_replace(',', '', $request->coffee_machine_rental_month_7);
		$phasedBudget->coffee_machine_rental_month_8 = str_replace(',', '', $request->coffee_machine_rental_month_8);
		$phasedBudget->coffee_machine_rental_month_9 = str_replace(',', '', $request->coffee_machine_rental_month_9);
		$phasedBudget->coffee_machine_rental_month_10 = str_replace(',', '', $request->coffee_machine_rental_month_10);
		$phasedBudget->coffee_machine_rental_month_11 = str_replace(',', '', $request->coffee_machine_rental_month_11);
		$phasedBudget->coffee_machine_rental_month_12 = str_replace(',', '', $request->coffee_machine_rental_month_12);
		$phasedBudget->other_rental_totals = str_replace(',', '', $request->other_rental_totals);
		$phasedBudget->other_rental_month_1 = str_replace(',', '', $request->other_rental_month_1);
		$phasedBudget->other_rental_month_2 = str_replace(',', '', $request->other_rental_month_2);
		$phasedBudget->other_rental_month_3 = str_replace(',', '', $request->other_rental_month_3);
		$phasedBudget->other_rental_month_4 = str_replace(',', '', $request->other_rental_month_4);
		$phasedBudget->other_rental_month_5 = str_replace(',', '', $request->other_rental_month_5);
		$phasedBudget->other_rental_month_6 = str_replace(',', '', $request->other_rental_month_6);
		$phasedBudget->other_rental_month_7 = str_replace(',', '', $request->other_rental_month_7);
		$phasedBudget->other_rental_month_8 = str_replace(',', '', $request->other_rental_month_8);
		$phasedBudget->other_rental_month_9 = str_replace(',', '', $request->other_rental_month_9);
		$phasedBudget->other_rental_month_10 = str_replace(',', '', $request->other_rental_month_10);
		$phasedBudget->other_rental_month_11 = str_replace(',', '', $request->other_rental_month_11);
		$phasedBudget->other_rental_month_12 = str_replace(',', '', $request->other_rental_month_12);
		$phasedBudget->it_support_totals = str_replace(',', '', $request->it_support_totals);
		$phasedBudget->it_support_month_1 = str_replace(',', '', $request->it_support_month_1);
		$phasedBudget->it_support_month_2 = str_replace(',', '', $request->it_support_month_2);
		$phasedBudget->it_support_month_3 = str_replace(',', '', $request->it_support_month_3);
		$phasedBudget->it_support_month_4 = str_replace(',', '', $request->it_support_month_4);
		$phasedBudget->it_support_month_5 = str_replace(',', '', $request->it_support_month_5);
		$phasedBudget->it_support_month_6 = str_replace(',', '', $request->it_support_month_6);
		$phasedBudget->it_support_month_7 = str_replace(',', '', $request->it_support_month_7);
		$phasedBudget->it_support_month_8 = str_replace(',', '', $request->it_support_month_8);
		$phasedBudget->it_support_month_9 = str_replace(',', '', $request->it_support_month_9);
		$phasedBudget->it_support_month_10 = str_replace(',', '', $request->it_support_month_10);
		$phasedBudget->it_support_month_11 = str_replace(',', '', $request->it_support_month_11);
		$phasedBudget->it_support_month_12 = str_replace(',', '', $request->it_support_month_12);
		$phasedBudget->free_issues_totals = str_replace(',', '', $request->free_issues_totals);
		$phasedBudget->free_issues_month_1 = str_replace(',', '', $request->free_issues_month_1);
		$phasedBudget->free_issues_month_2 = str_replace(',', '', $request->free_issues_month_2);
		$phasedBudget->free_issues_month_3 = str_replace(',', '', $request->free_issues_month_3);
		$phasedBudget->free_issues_month_4 = str_replace(',', '', $request->free_issues_month_4);
		$phasedBudget->free_issues_month_5 = str_replace(',', '', $request->free_issues_month_5);
		$phasedBudget->free_issues_month_6 = str_replace(',', '', $request->free_issues_month_6);
		$phasedBudget->free_issues_month_7 = str_replace(',', '', $request->free_issues_month_7);
		$phasedBudget->free_issues_month_8 = str_replace(',', '', $request->free_issues_month_8);
		$phasedBudget->free_issues_month_9 = str_replace(',', '', $request->free_issues_month_9);
		$phasedBudget->free_issues_month_10 = str_replace(',', '', $request->free_issues_month_10);
		$phasedBudget->free_issues_month_11 = str_replace(',', '', $request->free_issues_month_11);
		$phasedBudget->free_issues_month_12 = str_replace(',', '', $request->free_issues_month_12);
		$phasedBudget->marketing_totals = str_replace(',', '', $request->marketing_totals);
		$phasedBudget->marketing_month_1 = str_replace(',', '', $request->marketing_month_1);
		$phasedBudget->marketing_month_2 = str_replace(',', '', $request->marketing_month_2);
		$phasedBudget->marketing_month_3 = str_replace(',', '', $request->marketing_month_3);
		$phasedBudget->marketing_month_4 = str_replace(',', '', $request->marketing_month_4);
		$phasedBudget->marketing_month_5 = str_replace(',', '', $request->marketing_month_5);
		$phasedBudget->marketing_month_6 = str_replace(',', '', $request->marketing_month_6);
		$phasedBudget->marketing_month_7 = str_replace(',', '', $request->marketing_month_7);
		$phasedBudget->marketing_month_8 = str_replace(',', '', $request->marketing_month_8);
		$phasedBudget->marketing_month_9 = str_replace(',', '', $request->marketing_month_9);
		$phasedBudget->marketing_month_10 = str_replace(',', '', $request->marketing_month_10);
		$phasedBudget->marketing_month_11 = str_replace(',', '', $request->marketing_month_11);
		$phasedBudget->marketing_month_12 = str_replace(',', '', $request->marketing_month_12);
		$phasedBudget->set_up_costs_totals = str_replace(',', '', $request->set_up_costs_totals);
		$phasedBudget->set_up_costs_month_1 = str_replace(',', '', $request->set_up_costs_month_1);
		$phasedBudget->set_up_costs_month_2 = str_replace(',', '', $request->set_up_costs_month_2);
		$phasedBudget->set_up_costs_month_3 = str_replace(',', '', $request->set_up_costs_month_3);
		$phasedBudget->set_up_costs_month_4 = str_replace(',', '', $request->set_up_costs_month_4);
		$phasedBudget->set_up_costs_month_5 = str_replace(',', '', $request->set_up_costs_month_5);
		$phasedBudget->set_up_costs_month_6 = str_replace(',', '', $request->set_up_costs_month_6);
		$phasedBudget->set_up_costs_month_7 = str_replace(',', '', $request->set_up_costs_month_7);
		$phasedBudget->set_up_costs_month_8 = str_replace(',', '', $request->set_up_costs_month_8);
		$phasedBudget->set_up_costs_month_9 = str_replace(',', '', $request->set_up_costs_month_9);
		$phasedBudget->set_up_costs_month_10 = str_replace(',', '', $request->set_up_costs_month_10);
		$phasedBudget->set_up_costs_month_11 = str_replace(',', '', $request->set_up_costs_month_11);
		$phasedBudget->set_up_costs_month_12 = str_replace(',', '', $request->set_up_costs_month_12);
		$phasedBudget->credit_card_machines_totals = str_replace(',', '', $request->credit_card_machines_totals);
		$phasedBudget->credit_card_machines_month_1 = str_replace(',', '', $request->credit_card_machines_month_1);
		$phasedBudget->credit_card_machines_month_2 = str_replace(',', '', $request->credit_card_machines_month_2);
		$phasedBudget->credit_card_machines_month_3 = str_replace(',', '', $request->credit_card_machines_month_3);
		$phasedBudget->credit_card_machines_month_4 = str_replace(',', '', $request->credit_card_machines_month_4);
		$phasedBudget->credit_card_machines_month_5 = str_replace(',', '', $request->credit_card_machines_month_5);
		$phasedBudget->credit_card_machines_month_6 = str_replace(',', '', $request->credit_card_machines_month_6);
		$phasedBudget->credit_card_machines_month_7 = str_replace(',', '', $request->credit_card_machines_month_7);
		$phasedBudget->credit_card_machines_month_8 = str_replace(',', '', $request->credit_card_machines_month_8);
		$phasedBudget->credit_card_machines_month_9 = str_replace(',', '', $request->credit_card_machines_month_9);
		$phasedBudget->credit_card_machines_month_10 = str_replace(',', '', $request->credit_card_machines_month_10);
		$phasedBudget->credit_card_machines_month_11 = str_replace(',', '', $request->credit_card_machines_month_11);
		$phasedBudget->credit_card_machines_month_12 = str_replace(',', '', $request->credit_card_machines_month_12);
		$phasedBudget->bizimply_cost_totals = str_replace(',', '', $request->bizimply_cost_totals);
		$phasedBudget->bizimply_cost_month_1 = str_replace(',', '', $request->bizimply_cost_month_1);
		$phasedBudget->bizimply_cost_month_2 = str_replace(',', '', $request->bizimply_cost_month_2);
		$phasedBudget->bizimply_cost_month_3 = str_replace(',', '', $request->bizimply_cost_month_3);
		$phasedBudget->bizimply_cost_month_4 = str_replace(',', '', $request->bizimply_cost_month_4);
		$phasedBudget->bizimply_cost_month_5 = str_replace(',', '', $request->bizimply_cost_month_5);
		$phasedBudget->bizimply_cost_month_6 = str_replace(',', '', $request->bizimply_cost_month_6);
		$phasedBudget->bizimply_cost_month_7 = str_replace(',', '', $request->bizimply_cost_month_7);
		$phasedBudget->bizimply_cost_month_8 = str_replace(',', '', $request->bizimply_cost_month_8);
		$phasedBudget->bizimply_cost_month_9 = str_replace(',', '', $request->bizimply_cost_month_9);
		$phasedBudget->bizimply_cost_month_10 = str_replace(',', '', $request->bizimply_cost_month_10);
		$phasedBudget->bizimply_cost_month_11 = str_replace(',', '', $request->bizimply_cost_month_11);
		$phasedBudget->bizimply_cost_month_12 = str_replace(',', '', $request->bizimply_cost_month_12);
		$phasedBudget->kitchtech_totals = str_replace(',', '', $request->kitchtech_totals);
		$phasedBudget->kitchtech_month_1 = str_replace(',', '', $request->kitchtech_month_1);
		$phasedBudget->kitchtech_month_2 = str_replace(',', '', $request->kitchtech_month_2);
		$phasedBudget->kitchtech_month_3 = str_replace(',', '', $request->kitchtech_month_3);
		$phasedBudget->kitchtech_month_4 = str_replace(',', '', $request->kitchtech_month_4);
		$phasedBudget->kitchtech_month_5 = str_replace(',', '', $request->kitchtech_month_5);
		$phasedBudget->kitchtech_month_6 = str_replace(',', '', $request->kitchtech_month_6);
		$phasedBudget->kitchtech_month_7 = str_replace(',', '', $request->kitchtech_month_7);
		$phasedBudget->kitchtech_month_8 = str_replace(',', '', $request->kitchtech_month_8);
		$phasedBudget->kitchtech_month_9 = str_replace(',', '', $request->kitchtech_month_9);
		$phasedBudget->kitchtech_month_10 = str_replace(',', '', $request->kitchtech_month_10);
		$phasedBudget->kitchtech_month_11 = str_replace(',', '', $request->kitchtech_month_11);
		$phasedBudget->kitchtech_month_12 = str_replace(',', '', $request->kitchtech_month_12);

		$phasedBudget->unit_id = $request->unit_id;
		$phasedBudget->save();

		Session::flash('flash_message', 'The form was successfully completed!'); //<--FLASH MESSAGE
		return redirect('/sheets/phased-budget/')->cookie('unitIdCookiePhasedBudgetSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	public function changeLogJson(Request $request)
	{
		$changeLogArr = array();

		if ($request->has('unit_name')) {
			$changeLogArr['head_count'] = Unit::where('unit_id', $request->unit_name)->value('head_count');
			session(['headCountInSession' => $changeLogArr['head_count']]);

			$changeLogData = \DB::select("SELECT trading_account_id, budget_start_date, budget_end_date, DATE_FORMAT(datetime_created,'%b %d, %Y %h:%i %p') formatted_datetime_created, entered_by FROM trading_account WHERE unit_id = " . $request->unit_name . " ORDER BY trading_account_id DESC");

			$changeLogTable = '
                <thead>
                    <tr>
                        <th>Budget</th>
                        <th>Created</th>
                        <th>Created / Modified By</th>
                    </tr>
                </thead>
                <tbody>
            ';

			foreach ($changeLogData as $cld) {
				$explodeBudgetStartDate = explode('-', $cld->budget_start_date);
				$formattedBudgetStartDate = $explodeBudgetStartDate[2] . '-' . $explodeBudgetStartDate[1] . '-' . $explodeBudgetStartDate[0];

				$explodeBudgetEndDate = explode('-', $cld->budget_end_date);
				$formattedBudgetEndDate = $explodeBudgetEndDate[2] . '-' . $explodeBudgetEndDate[1] . '-' . $explodeBudgetEndDate[0];

				$changeLogTable .= '<tr>
                    <td>
                        <a href="phased-budget?budget_id=' . $cld->trading_account_id . '">' . $formattedBudgetStartDate . ' - ' . $formattedBudgetEndDate . '</a>
                    </td>
                    <td>
                        ' . $cld->formatted_datetime_created . '
                    </td>
                    <td>
                        ' . $cld->entered_by . '
                    </td>
                </tr>';
			}

			$changeLogTable .= '
                </tbody>
            ';

			$changeLogArr['change_log_table'] = $changeLogTable;

		}

		$userId = session()->get('userId');
		$unitId = $request->input('unit_name', 0);

		// Rows visibility
		$hiddenUnitRows = PhasedBudgetUnitRow::where('user_id', $userId)->where('unit_id', $unitId)->get(['row_index']);
		$unitRows = [];

		foreach (PhasedBudgetUnitRow::$rows as $rowIndex => $rowName) {
			$unitRows[] = [
				'rowIndex' => $rowIndex,
				'hidden' => $hiddenUnitRows->contains(function ($value) use ($rowIndex) {
					return $rowIndex == $value->row_index;
				})
			];
		}

		$changeLogArr['unit_rows'] = $unitRows;

		echo json_encode($changeLogArr);
	}

	public function toggleRowVisibility(Request $request)
	{
		$userId = session()->get('userId');
		$unitId = $request->input('unitId', 0);
		$rowIndex = $request->input('rowIndex', '');

		if ($userId && $unitId && $rowIndex) {
			$unitRow = PhasedBudgetUnitRow::where('user_id', $userId)->where('unit_id', $unitId)->where('row_index', $rowIndex)->first();

			if (!is_null($unitRow)) {
				$unitRow->delete();
			} else {
				PhasedBudgetUnitRow::create(
					[
						'user_id' => $userId,
						'unit_id' => $unitId,
						'row_index' => $rowIndex,
					]
				);
			}
		}
	}

	/**
	 * Labour Hours Sheet.
	 */
	public function labourHours(Request $request, $sheetId = NULL)
	{
		// Get list of units for current user level
		$userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

		$selectedUnit = $request->unit_id;
		$unitName = $request->unit_name;

		if ($request->has('data_table_hidden')) {
			$dataTableStr = $request->data_table_hidden;
			$findTr = '<TR>';
			$posTr = strpos($dataTableStr, $findTr);
		}

		if ($request->has('data_table_hidden') && $posTr !== false) {
			$dataTableStrShow = str_replace(' style="display:none"', '', $dataTableStr);
		}

		$labourTypes = \App\LabourType::pluck('labour_type', 'id');

		if (($sheetId && $request->return_from != 'confirm') || ($request->sheet_id && $request->return_from != 'confirm')) {
			$labHrsSum = 0;
			$labourHoursData = \DB::select("SELECT *, DATE_FORMAT(labour_date,'%d-%m-%Y') formatted_date FROM labour_hours WHERE unique_id = '$sheetId'");
			$labourHoursArrCount = count($labourHoursData);

			if ($labourHoursArrCount >= 1) {
				$reportDataTableStr = '<TABLE id="dataTable" class="table table-bordered table-striped table-small">';

				$i = 0;
				$hoursTabIndex = 3;
				$dateTabIndex = 4;
				$labourTypeTabIndex = 5;
				foreach ($labourHoursData as $lhd) {
					$labHrsSum += $lhd->labour_hours;

					$reportDataTableStr .= '<TR width="5%"><TD width="5%"><INPUT type="checkbox" id="chkbox_' . $i . '" name="chkbox_' . $i . '" class="margin-top-10" /></TD>';
					$reportDataTableStr .= '<TD width="20%"><INPUT class="form-control" type="text" name="hours_' . $i . '" id="hours_' . $i . '" value="' . $lhd->labour_hours . '" tabindex="' . $hoursTabIndex . '" /></TD>';
					$reportDataTableStr .= '<TD width="20%"><INPUT type="text" class="form-control datepick cursor-pointer" name="date_' . $i . '" id="date_' . $i . '" value="' . $lhd->formatted_date . '" tabindex="' . $dateTabIndex . '" readonly="" /></TD>
                        <TD width="55%"><SELECT name="labour_type_' . $i . '" id="labour_type_' . $i . '" class="form-control" tabindex="' . $labourTypeTabIndex . '"><option value="">Choose Labour Type</option>';

					foreach ($labourTypes as $labourTypeID => $labourTypeVal) {
						$selectedLabourType = $lhd->labour_type_id == $labourTypeID ? 'selected="selected"' : '';
						$reportDataTableStr .= '<OPTION value="' . $labourTypeID . '" ' . $selectedLabourType . '>' . $labourTypeVal . '</OPTION>';
					}

					$reportDataTableStr .= '</SELECT></TD></TR>';
					$hoursTabIndex += 3;
					$dateTabIndex += 3;
					$labourTypeTabIndex += 3;
					$i++;
				}

				$reportDataTableStr .= '</TABLE>';
			}
		}

		return view(
			'sheets.labour-hours.index', [
				'todayDate' => Carbon::now()->format('d-m-Y'),
				'userUnits' => $userUnits,
				'selectedUnit' => isset($selectedUnit) ? $selectedUnit : $request->cookie('unitIdCookieLabourHoursSheet'),
				'unitName' => $unitName,
				'dataTableStrShow' => isset($dataTableStrShow) ? $dataTableStrShow : '',
				'labourTypes' => $labourTypes,
				'rowsCounter' => $sheetId ? $labourHoursArrCount : ($request->has('rows_counter') ? $request->rows_counter : 1),
				'supervisor' => session()->get('userName'),
				'reportDataTableStr' => isset($reportDataTableStr) ? $reportDataTableStr : '',
				'labourHoursArrCount' => isset($labourHoursArrCount) ? $labourHoursArrCount : 0,
				'sheetId' => $sheetId
			]

		);
	}

	public function labourHoursConfimation(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		$labourHoursUsed = 0;

		$labourTypes = \App\LabourType::pluck('labour_type', 'id');

		$rowsCounter = $request->has('rows_counter') ? $request->rows_counter : 1;
		if ($rowsCounter >= 1) {
			$hoursTabIndex = 3;
			$dateTabIndex = 4;
			$labourTypeTabIndex = 5;
			$dataTableStr = '<TABLE id="dataTable" class="table table-bordered table-striped table-small" style="display:none">';
			for ($i = 0; $i < $rowsCounter; $i++) {
				$hoursIndex = 'hours_' . $i;
				$hours = $request->has($hoursIndex) ? $request->$hoursIndex : '';
				$dateIndex = 'date_' . $i;
				$date = $request->has($dateIndex) ? $request->$dateIndex : '';
				$dataTableStr .= '<TR><TD width="5%"><INPUT type="checkbox" class="margin-top-10" id="chkbox_' . $i . '" name="chkbox_' . $i . '"/></TD>';
				$dataTableStr .= '<TD width="20%"><INPUT class="form-control" type="text" name="hours_' . $i . '" id="hours_' . $i . '" value="' . $hours . '" tabindex="' . $hoursTabIndex . '" /></TD>';
				$dataTableStr .= '<TD width="20%"><INPUT type="text" class="form-control datepick cursor-pointer" name="date_' . $i . '" id="date_' . $i . '" value="' . $date . '" tabindex="' . $dateTabIndex . '" readonly="" /></TD>
                    <TD width="55%"><SELECT name="labour_type_' . $i . '" id="labour_type_' . $i . '" class="form-control" tabindex="' . $labourTypeTabIndex . '"><option value="">Choose Labour Type</option>';

				foreach ($labourTypes as $labourTypeID => $labourTypeVal) {
					$selectedLabourType = $request->has('labour_type_' . $i) && $request->input('labour_type_' . $i) == $labourTypeID ? 'selected="selected"' : '';
					$dataTableStr .= '<OPTION value="' . $labourTypeID . '" ' . $selectedLabourType . '>' . $labourTypeVal . '</OPTION>';
				}

				$dataTableStr .= '</SELECT></TD></TR>';
				$hoursTabIndex += 3;
				$dateTabIndex += 3;
				$labourTypeTabIndex += 3;
			}
			$dataTableStr .= '</TABLE>';
		}

		$labourHoursArr = array();
		$labourDateArr = array();
		$labourTypeArr = array();

		for ($i = 0; $i <= 50; $i++) {
			$hoursIndexes = 'hours_' . $i;
			$dateIndexes = 'date_' . $i;
			$labourTypeIndexes = 'labour_type_' . $i;

			$labourHoursUsed += $request->has($hoursIndexes) ? $request->$hoursIndexes : 0;

			$labourHoursArr[] = $request->has($hoursIndexes) && $request->$hoursIndexes > 0 ? $request->$hoursIndexes : '';
			$labourDateArr[] = $request->has($dateIndexes) && $request->$dateIndexes > 0 ? $request->$dateIndexes : '';
			$labourTypeArr[] = $request->has($labourTypeIndexes) && $request->$labourTypeIndexes > 0 ? $request->$labourTypeIndexes : '';
		}

		session(['labourHoursArr' => array_filter($labourHoursArr)]);
		session(['labourDateArr' => array_filter($labourDateArr)]);
		session(['labourTypeArr' => array_filter($labourTypeArr)]);

		$labourHoursRemaining = $request->labour_hours_remaining - $labourHoursUsed;

		return view(
			'sheets.labour-hours.confirmation', [
				'userId' => $userId,
				'userName' => $userName,
				'unitId' => $request->unit_name,
				'unitName' => $request->hidden_unit_name,
				'labourHoursUsed' => $labourHoursUsed,
				'labourHoursRemaining' => $labourHoursRemaining,
				'dataTableStr' => $dataTableStr,
				'rowsCounter' => $rowsCounter,
				'sheetId' => $request->sheet_id
			]
		);
	}

	public function labourHoursPost(Request $request)
	{
		$labourHoursArr = Session::get('labourHoursArr');
		$labourDateArr = Session::get('labourDateArr');
		$labourTypeArr = Session::get('labourTypeArr');

		$count = count($labourHoursArr);

		if ($request->has('sheet_id')) {
			$uniqueId = $request->sheet_id;
			\DB::table('labour_hours')->where('unique_id', '=', $uniqueId)->delete();
		} else {
			$lastLabourHoursId = \App\LabourHour::orderBy('id', 'desc')->first();
			if ($lastLabourHoursId)
				$uniqueId = time() . '_' . $lastLabourHoursId->id;
			else
				$uniqueId = time() . '_1';
		}

		for ($i = 0; $i < $count; $i++) {
			$labourHours = new \App\LabourHour;
			$labourHours->unit_name = $request->unit_name_1;
			$labourHours->supervisor = $request->supervisor_name;
			$labourHours->date = Carbon::now()->format('Y-m-d');
			$labourHours->labour_hours = isset($labourHoursArr[$i]) && $labourHoursArr[$i] > 0 ? $labourHoursArr[$i] : 0;
			$labourHours->labour_date = isset($labourDateArr[$i]) && $labourDateArr[$i] > 0 ? Carbon::createFromFormat('d-m-Y', $labourDateArr[$i])->format('Y-m-d') : '';
			$labourHours->labour_type_id = isset($labourTypeArr[$i]) && $labourTypeArr[$i] > 0 ? $labourTypeArr[$i] : 0;
			$labourHours->unit_id = $request->unit_id;
			$labourHours->supervisor_id = $request->supervisor_id;
			$labourHours->unique_id = $uniqueId;
			$labourHours->save();
		}

		Session::flash('flash_message', 'The form was successfully completed!'); //<--FLASH MESSAGE

		return redirect('/sheets/labour-hours/')->cookie('unitIdCookieLabourHoursSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	public function labourHoursRemainingJson(Request $request)
	{
		$labourHoursRemainingArr = array();
		$totalLabourHours = 0;
		$totalUsedHours = 0;

		if ($request->has('unit_name')) {
			$currentMonth = date("n");
			$currentDay = date("j");
			$currentDate = date("Y-m-d");

			$tradingAccountData = \DB::select("
                SELECT budget_start_date
                FROM units u
                JOIN trading_account t ON u.unit_id = t.unit_id
                WHERE (EXTRACT( YEAR FROM budget_start_date ) = EXTRACT( YEAR FROM NOW( ) ) OR
                EXTRACT( YEAR FROM budget_end_date ) = EXTRACT( YEAR FROM NOW( ) ))
                AND t.unit_id = '$request->unit_name' ORDER BY t.trading_account_id DESC LIMIT 1
            ");

			if (isset($tradingAccountData[0]->budget_start_date)) {
				$expDate = explode('-', $tradingAccountData[0]->budget_start_date);
				$budgetStartMonth = (int)$expDate[1];

				if ($currentMonth - $budgetStartMonth == 0)
					$month = 1;

				if ($currentMonth - $budgetStartMonth < 0)
					$month = ($currentMonth - $budgetStartMonth) + 13;

				if ($currentMonth - $budgetStartMonth > 0)
					$month = ($currentMonth - $budgetStartMonth) + 1;

				$tradingAccountMonthData = \DB::select("
                    SELECT t.labour_hours_month_$month
                    FROM units u
                    JOIN trading_account t ON u.unit_id = t.unit_id
                    WHERE (EXTRACT( YEAR FROM budget_start_date ) = EXTRACT( YEAR FROM NOW( ) ) OR
                    EXTRACT( YEAR FROM budget_end_date ) = EXTRACT( YEAR FROM NOW( ) ))
                    AND t.unit_id = '$request->unit_name' ORDER BY t.trading_account_id DESC LIMIT 1
                ");
				$labourHoursMonth = 'labour_hours_month_' . $month;
				$totalLabourHours = $tradingAccountMonthData[0]->$labourHoursMonth;
			}

			$usedHoursData = \DB::select("
                SELECT SUM( labour_hours ) AS labour_hours_sum
                FROM labour_hours
                WHERE date
                BETWEEN '$currentDate'
                AND DATE_ADD( '$currentDate', INTERVAL 1
                MONTH ) AND unit_id = '$request->unit_name'
            ");

			$totalUsedHours = $usedHoursData[0]->labour_hours_sum;

			$labourHoursRemainingArr['labour_hours_remaining'] = $totalLabourHours - $totalUsedHours;
			echo json_encode($labourHoursRemainingArr);
		}
	}

	/**
	 * Stock Control Sheet.
	 */
	public function stockControl(Request $request)
	{
		// Get list of units for current user level
		$userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

		$selectedUnit = $request->unit_id;
		$unitName = $request->unit_name;

		return view(
			'sheets.stock-control.index', [
				'todayDate' => Carbon::now()->format('d-m-Y'),
				'userUnits' => $userUnits,
				'selectedUnit' => isset($selectedUnit) ? $selectedUnit : $request->cookie('unitIdCookieStockControlSheet'),
				'unitName' => $unitName,
				'stockTakeDate' => $request->stock_take_date,
				'foods' => $request->foods,
				'minerals' => $request->minerals,
				'choc_snacks' => $request->choc_snacks,
				'vending' => $request->vending,
				'foodsPlusMinerals' => $request->foods_plus_minerals,
				'chemicals' => $request->chemicals,
				'cleanDisp' => $request->clean_disp,
				'freeIssues' => $request->free_issues,
				'totalChemicalsCleanDispFreeIssues' => $request->total_chemicals_clean_disp_free_issues,
				'total' => $request->total,
				'comments' => $request->comments
			]

		);
	}

	public function stockControlConfimation(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		return view(
			'sheets.stock-control.confirmation', [
				'userId' => $userId,
				'userName' => $userName,
				'unitId' => $request->unit_name,
				'unitName' => $request->hidden_unit_name,
				'stockDate' => $request->stock_take_date,
				'foodsPlusMinerals' => $request->foods_plus_minerals,
				'totalChemicalsCleanDispFreeIssues' => $request->total_chemicals_clean_disp_free_issues,
				'total' => $request->total,
				'comments' => $request->comments,
				'foods' => $request->foods,
				'minerals' => $request->minerals,
				'chemicals' => $request->chemicals,
				'cleanDisp' => $request->clean_disp,
				'chocSnacks' => $request->choc_snacks,
				'vending' => $request->vending,
				'freeIssues' => $request->free_issues,
				'foodsDelta' => $request->foods_delta,
				'mineralsDelta' => $request->minerals_delta,
				'chocSnacksDelta' => $request->choc_snacks_delta,
				'vendingDelta' => $request->vending_delta,
				'chemicalsDelta' => $request->chemicals_delta,
				'cleanDispDelta' => $request->clean_disp_delta,
				'freeIssuesDelta' => $request->free_issues_delta,
				'totalDelta' => $request->total_delta,
				'overallTotal' => $request->foods + $request->minerals + $request->choc_snacks + $request->vending + $request->chemicals + $request->clean_disp + $request->free_issues
			]
		);
	}

	public function stockControlPost(Request $request)
	{
		$stockControl = new \App\StockControl;
		$stockControl->unit_id = $request->unit_id;
		$stockControl->stock_take_date = Carbon::createFromFormat('d-m-Y', $request->stock_take_date)->format('Y-m-d');
		$stockControl->comments = $request->comments;
		$stockControl->choc_snacks = $request->choc_snacks;
		$stockControl->foods = $request->foods;
		$stockControl->minerals = $request->minerals;
		$stockControl->clean_disp = $request->clean_disp;
		$stockControl->vending = $request->vending;
		$stockControl->chemicals = $request->chemicals;
		$stockControl->free_issues = $request->free_issues;
		$stockControl->total = $request->total;
		$stockControl->foods_delta = $request->foods_delta;
		$stockControl->minerals_delta = $request->minerals_delta;
		$stockControl->choc_snacks_delta = $request->choc_snacks_delta;
		$stockControl->vending_delta = $request->vending_delta;
		$stockControl->chemicals_delta = $request->chemicals_delta;
		$stockControl->clean_disp_delta = $request->clean_disp_delta;
		$stockControl->free_issues_delta = $request->free_issues_delta;
		$stockControl->total_delta = $request->total_delta;
		$stockControl->user_id = $request->supervisor_id;
		$stockControl->save();

		Session::flash('flash_message', 'The form was successfully completed!'); //<--FLASH MESSAGE
		return redirect('/sheets/stock-control/')->cookie('unitIdCookieStockControlSheet', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	public function previousStockJson(Request $request)
	{
		$stockControlArr = array();
		$previousStockArr = array();
		if ($request->has('unit_name')) {
			$stockControlData = \DB::select("SELECT * FROM stock_control WHERE unit_id = " . $request->unit_name . " ORDER BY created_at DESC LIMIT 1");

			if (isset($stockControlData[0]->stock_control_id) && $stockControlData[0]->stock_control_id != 0) {
				$previousStockArr[2] = $stockControlData[0]->stock_take_date;
				$previousStockArr[3] = $stockControlData[0]->comments;
				$previousStockArr[14] = $stockControlData[0]->foods;
				$previousStockArr[15] = $stockControlData[0]->minerals;
				$previousStockArr[11] = $stockControlData[0]->choc_snacks;
				$previousStockArr[19] = $stockControlData[0]->vending;
				$previousStockArr[20] = $stockControlData[0]->chemicals;
				$previousStockArr[16] = $stockControlData[0]->clean_disp;
				$previousStockArr[21] = $stockControlData[0]->free_issues;
				$stockControlArr['stock_control_row'] = $previousStockArr;
			} else
				$stockControlArr['stock_control_row'] = 0;
		}
		echo json_encode($stockControlArr);
	}

	/**
	 * Problem Report Sheet.
	 */
	public function problemReport(Request $request, $id = NULL)
	{
		$appUrl = trim(config('app.url'), '/');
		$userId = session()->get('userId');
		$userName = session()->get('userName');

		$selectAll = Gate::allows('hq-user-group') ? 1 : 0;

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		$carNum = $id ? $id : ($request->has('sheet_id') ? $request->sheet_id : \App\Problem::max('id') + 1);

		$problemTypes = \App\ProblemType::orderBy('title')->pluck('title', 'id');
		$dir_file_arr = array();

		if (($id && $request->return_from != 'confirm') || ($request->id && $request->return_from != 'confirm')) {
			//echo 'DDDDDDDDDDDDDDDD';
			$problemReportData = \DB::select("SELECT *, DATE_FORMAT(problem_date,'%d-%m-%Y') formatted_problem_date, DATE_FORMAT(closed_date,'%d-%m-%Y') formatted_closed_date FROM problems WHERE id = '$id'");
			$file_id = $problemReportData[0]->file_id;
			if ($file_id > 0) {
				$query = DB::table('file_system as F');
				$query->whereIn('F.id', explode(",", $file_id));
				$file_systemData = $query->get();
				if (count($file_systemData) > 0 && !empty($file_systemData)) {
					foreach ($file_systemData as $file_systemData1) {
						$dir_file_name = @$file_systemData1->dir_file_name;
						$dir_path = @$file_systemData1->dir_path;
						if ($dir_file_name != '' && $dir_path != '') {
							$dir_path = str_replace("/opt/bitnami/apache2/htdocs/", "", $dir_path);
							$dir_file_arr[$dir_file_name] = $dir_path . $dir_file_name;
						}
					}
				}
			}
		}

		$selectedUnit = isset($problemReportData[0]->unit_id) ? $problemReportData[0]->unit_id : $request->unit_id;
		$unitName = isset($problemReportData[0]->unit_name) ? $problemReportData[0]->unit_name : $request->unit_name;
		$problemDate = isset($problemReportData[0]->formatted_problem_date) ? $problemReportData[0]->formatted_problem_date : $request->problem_date;
		$selectedProblemType = isset($problemReportData[0]->problem_type) ? $problemReportData[0]->problem_type : $request->problem_type;
		$selectedSupplier = isset($problemReportData[0]->problem_type_val) ? $problemReportData[0]->problem_type_val : $request->supplier;
		$supplierName = isset($problemReportData[0]->suppliers_feedback_title) ? $problemReportData[0]->suppliers_feedback_title : $request->hidden_supplier;
		$details = isset($problemReportData[0]->details) ? $problemReportData[0]->details : $request->details;

		$comments = isset($problemReportData[0]->comments) ? $problemReportData[0]->comments : $request->comments;

		$rootCauseAnalysisDesc = isset($problemReportData[0]->root_cause_analysis_desc) ? $problemReportData[0]->root_cause_analysis_desc : $request->root_cause_analysis_desc;
		$rootCauseAnalysisAction = isset($problemReportData[0]->root_cause_analysis_action) ? $problemReportData[0]->root_cause_analysis_action : $request->root_cause_analysis_action;
		$closingComments = isset($problemReportData[0]->closing_comments) ? $problemReportData[0]->closing_comments : $request->closing_comments;
		$closedDate = isset($problemReportData[0]->formatted_closed_date) ? $problemReportData[0]->formatted_closed_date : $request->closed_date;

		return view(
			'sheets.problem-report.index', [
				'carNum' => $carNum,
				'userId' => $userId,
				'userName' => $userName,
				'todayDate' => Carbon::now()->format('d-m-Y'),
				'problemDate' => $problemDate,
				'userUnits' => $userUnits,
				'selectAll' => $selectAll,
				'selectedUnit' => isset($selectedUnit) ? $selectedUnit : '',
				'unitName' => $unitName,
				'problemTypes' => $problemTypes,
				'selectedProblemType' => $selectedProblemType,
				'problemType' => $request->hidden_problem_type,
				'selectedSupplier' => $selectedSupplier,
				'supplierName' => $supplierName,
				'feedbackPositive' => (isset($problemReportData[0]->problem_type) && $problemReportData[0]->problem_type == 6 && $problemReportData[0]->problem_type_val == 1) || $request->feedback == 1 || $request->feedback == '' ? 'checked="checked"' : '',
				'feedbackNegative' => (isset($problemReportData[0]->problem_type) && $problemReportData[0]->problem_type == 6 && $problemReportData[0]->problem_type_val == 2) || $request->feedback == 2 ? 'checked="checked"' : '',
				'feedbackComment' => (isset($problemReportData[0]->problem_type) && $problemReportData[0]->problem_type == 6 && $problemReportData[0]->problem_type_val == 3) || $request->feedback == 3 ? 'checked="checked"' : '',
				'details' => $details,
				'comments' => $comments,
				'rootCauseAnalysisYes' => (isset($problemReportData[0]->root_cause_analysis) && $problemReportData[0]->root_cause_analysis == 1) || $request->root_cause_analysis == 1 ? 'checked="checked"' : '',
				'rootCauseAnalysisNo' => (isset($problemReportData[0]->root_cause_analysis) && $problemReportData[0]->root_cause_analysis == 2) || $request->root_cause_analysis == 2 || (!isset($problemReportData[0]->root_cause_analysis) && !isset($request->root_cause_analysis)) ? 'checked="checked"' : '',
				'rootCauseAnalysisDesc' => $rootCauseAnalysisDesc,
				'rootCauseAnalysisAction' => $rootCauseAnalysisAction,
				'problemStatusOpen' => (isset($problemReportData[0]->problem_status) && $problemReportData[0]->problem_status == 1) || $request->problem_status == 1 || $request->problem_status == '' ? 'checked="checked"' : '',
				'problemStatusClosed' => (isset($problemReportData[0]->problem_status) && $problemReportData[0]->problem_status == 0) || ($request->has('problem_status') && $request->problem_status == 0) ? 'checked="checked"' : '',
				'closingComments' => $closingComments,
				'closedDate' => $closedDate,
				'sheetId' => $carNum,
				'appUrl' => $appUrl,
				'dir_file_arr1' => $dir_file_arr
			]

		);
	}

	public function problemReportConfimation(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');
		$fpath = config('app.fpath');

		if ($request->feedback == 1) {
			$feedback = 'Positive';
		} elseif ($request->feedback == 2) {
			$feedback = 'Negative';
		} else {
			$feedback = 'Comment';
		}

		$newFileID = NULL;
		$newFileName = NULL;
		$newFileArr = array();
		if ($request->file_id != '') {
			$fileArr = explode(",", $request->file_id);
			$newArr = array();
			if (count($fileArr) > 0 && !empty($fileArr)) {
				foreach ($fileArr as $key => $value) {
					$filesInFolder = $fpath . $value;
					$path_parts = pathinfo($filesInFolder);
					$dirname = $path_parts['dirname'] . '/';
					$basename = $path_parts['basename'];

					$query = DB::table('file_system as F');
					$query->where('F.is_dir', '=', 0);
					$query->where('F.dir_path', '=', $dirname);
					$query->where('F.dir_file_name', '=', $basename);
					$file_systemData = $query->first();
					$newFileID1 = @$file_systemData->id;
					if ($newFileID1 > 0) {
						$newArr[] = $newFileID1;
						$newFileArr[] = @$file_systemData->dir_file_name;
					}
				}
			}
			$newFileID = implode(",", $newArr);
			$newFileName = implode(",", $newFileArr);

		}

		return view(
			'sheets.problem-report.confirmation', [
				'userId' => $userId,
				'userName' => $userName,
				'unitId' => $request->unit_name,
				'unitName' => $request->hidden_unit_name,
				'carNum' => $request->id,
				'problemDate' => $request->problem_date,
				'problemType' => $request->problem_type,
				'problemTypeText' => $request->hidden_problem_type,
				'feedback' => $request->feedback,
				'commentsText' => $request->comments,
				'feedbackText' => $feedback,
				'supplier' => $request->supplier,
				'supplierText' => $request->hidden_supplier,
				'details' => $request->details,
				'rootCauseAnalysis' => $request->root_cause_analysis,
				'rootCauseAnalysisText' => $request->root_cause_analysis == 1 ? 'Yes' : 'No',
				'rootCauseAnalysisDesc' => $request->root_cause_analysis_desc,
				'rootCauseAnalysisAction' => $request->action,
				'problemStatus' => $request->problem_status,
				'problemStatusText' => $request->problem_status == 1 ? 'Open' : 'Closed',
				'closingComments' => $request->closing_comments,
				'closedDate' => $request->closed_date,
				'sheetId' => $request->sheet_id,
				'newFileID' => $newFileID,
				'file_id' => $newFileName
			]
		);
	}

	public function problemReportPost(Request $request)
	{
		$problem = new \App\Problem;

		if ($request->has('id')) {
			$problemId = $request->id;
			\DB::table('problems')->where('id', '=', $problemId)->delete();
		}

		// increment the id if already exists
		$j = 1;
		$found = true;
		while ($found == true) {
			$problemId = \App\Problem::where('id', '=', $request->id)->first();

			if (!$problemId) {
				$problem->id = $request->id;
				$found = false;
			} else {
				$request->id = $request->id + $j;
				$j++;
			}
		}

		$comments = NULL;
		if ($request->comments != '') {
			$comments = $request->comments;
		}

		$problem->problem_date = Carbon::createFromFormat('d-m-Y', $request->problem_date)->format('Y-m-d') . ' ' . date('H:i:s');
		$problem->user_id = session()->get('userId');
		$problem->unit_id = $request->unit_id;
		$problem->problem_type = $request->problem_type;
		$problem->problem_type_val = $request->problem_type_val;
		$problem->details = $request->details;
		$problem->comments = $comments;

		$problem->root_cause_analysis = $request->root_cause_analysis;
		$problem->root_cause_analysis_desc = $request->root_cause_analysis_desc;
		$problem->root_cause_analysis_action = $request->root_cause_analysis_action;
		$problem->problem_status = $request->problem_status;
		$problem->closed_by = $request->problem_status == 0 ? session()->get('userId') : NULL;
		$problem->closed_date = $request->problem_status == 0 && $request->has('closed_date') ? Carbon::createFromFormat('d-m-Y', $request->closed_date)->format('Y-m-d') : NULL;
		$problem->closing_comments = $request->closing_comments;
		$problem->closed_by_username = $request->problem_status == 0 ? $request->user_name : '';
		$problem->problem_status_title = $request->problem_status_title;
		$problem->suppliers_feedback_title = $request->suppliers_feedback_title;
		$problem->file_id = $request->newFileID;
		$problem->save();

		Session::flash('flash_message', 'The form was successfully completed!'); //<--FLASH MESSAGE

		return redirect('/sheets/problem-report/');
	}

	public function fileProblemReportPost(Request $request)
	{
		dd($request);
	}

	public function fileRavindra(Request $request)
	{
		return view('vendor.laravel-filemanager.index');
	}

	public function problemReportJson()
	{
		$files = \DB::select("SELECT id, dir_file_name FROM file_system where parent_dir_id > 0 AND is_dir = 0  ORDER BY dir_file_name");
		$table = json_encode($files);
		echo $table;
	}

	/**
	 * Operations scorecard
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function operationsScorecard(Request $request)
	{
		// Get list of units for current user level
		$userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

		$unitId = Cookie::get('operationsScorecardUnitIdCookie', 0);
		$scorecardDate = Carbon::now()->format('d-m-Y');
		$presentation = 0;
		$presentationNotes = '';
		$presentationPrivate = '';
		$foodcostAwareness = 0;
		$foodcostAwarenessNotes = '';
		$foodcostAwarenessPrivate = '';
		$hrIssues = 0;
		$hrIssuesNotes = '';
		$hrIssuesPrivate = '';
		$morale = 0;
		$moraleNotes = '';
		$moralePrivate = '';
		$purchCompliance = 0;
		$purchComplianceNotes = '';
		$purchCompliancePrivate = '';
		$haccpCompliance = 0;
		$haccpComplianceNotes = '';
		$haccpCompliancePrivate = '';
		$healthSafetyIso = 0;
		$healthSafetyIsoNotes = '';
		$healthSafetyIsoPrivate = '';
		$accidentsIncidents = 0;
		$accidentsIncidentsNotes = '';
		$accidentsIncidentsPrivate = '';
		$securityCashControl = 0;
		$securityCashControlNotes = '';
		$securityCashControlPrivate = '';
		$marketingUpselling = 0;
		$marketingUpsellingNotes = '';
		$marketingUpsellingPrivate = '';
		$training = 0;
		$trainingNotes = '';
		$trainingPrivate = '';
		$objectives = '';
		$objectivesPrivate = '';
		$outstandingIssues = '';
		$outstandingIssuesPrivate = '';
		$spProjectsFunctions = '';
		$spProjectsFunctionsPrivate = '';
		$innovation = '';
		$innovationPrivate = '';
		$addSupportRequired = '';
		$addSupportRequiredPrivate = '';
		$attachedFiles = '';
		$sendEmail = '';

		// Back from the confirm page
		if ($request->isMethod('post')) {
			$backData = unserialize($request->back_data);

			$unitId = $backData['unit_id'];
			$scorecardDate = $backData['scorecard_date'];
			$presentation = $backData['presentation'];
			$presentationNotes = $backData['presentation_notes'];
			$presentationPrivate = $backData['presentation_private'];
			$foodcostAwareness = $backData['foodcost_awareness'];
			$foodcostAwarenessNotes = $backData['foodcost_awareness_notes'];
			$foodcostAwarenessPrivate = $backData['foodcost_awareness_private'];
			$hrIssues = $backData['hr_issues'];
			$hrIssuesNotes = $backData['hr_issues_notes'];
			$hrIssuesPrivate = $backData['hr_issues_private'];
			$morale = $backData['morale'];
			$moraleNotes = $backData['morale_notes'];
			$moralePrivate = $backData['morale_private'];
			$purchCompliance = $backData['purch_compliance'];
			$purchComplianceNotes = $backData['purch_compliance_notes'];
			$purchCompliancePrivate = $backData['purch_compliance_private'];
			$haccpCompliance = $backData['haccp_compliance'];
			$haccpComplianceNotes = $backData['haccp_compliance_notes'];
			$haccpCompliancePrivate = $backData['haccp_compliance_private'];
			$healthSafetyIso = $backData['health_safety_iso'];
			$healthSafetyIsoNotes = $backData['health_safety_iso_notes'];
			$healthSafetyIsoPrivate = $backData['health_safety_iso_private'];
			$accidentsIncidents = $backData['accidents_incidents'];
			$accidentsIncidentsNotes = $backData['accidents_incidents_notes'];
			$accidentsIncidentsPrivate = $backData['accidents_incidents_private'];
			$securityCashControl = $backData['security_cash_ctl'];
			$securityCashControlNotes = $backData['security_cash_ctl_notes'];
			$securityCashControlPrivate = $backData['security_cash_ctl_private'];
			$marketingUpselling = $backData['marketing_upselling'];
			$marketingUpsellingNotes = $backData['marketing_upselling_notes'];
			$marketingUpsellingPrivate = $backData['marketing_upselling_private'];
			$training = $backData['training'];
			$trainingNotes = $backData['training_notes'];
			$trainingPrivate = $backData['training_private'];
			$objectives = $backData['objectives'];
			$objectivesPrivate = $backData['objectives_private'];
			$outstandingIssues = $backData['outstanding_issues'];
			$outstandingIssuesPrivate = $backData['outstanding_issues_private'];
			$spProjectsFunctions = $backData['sp_projects_functions'];
			$spProjectsFunctionsPrivate = $backData['sp_projects_functions_private'];
			$innovation = $backData['innovation'];
			$innovationPrivate = $backData['innovation_private'];
			$addSupportRequired = $backData['add_support_required'];
			$addSupportRequiredPrivate = $backData['add_support_required_private'];
			$attachedFiles = $backData['attached_files'];
			$sendEmail = $backData['send_email'];
		}

		return view(
			'sheets.operations-scorecard.index',
			[
				'userUnits' => ['' => 'Select unit'] + $userUnits->toArray(),
				'selectedUnit' => $unitId,
				'scorecardDate' => $scorecardDate,
				'regionName' => '',
				'operationsManagerName' => '',
				'contractStatus' => '',
				'contractType' => '',
				'onsiteVisits' => '',
				'clientContact' => '',
				'score' => ['Not checked'] + range(0, 10),
				'presentation' => $presentation,
				'presentationNotes' => $presentationNotes,
				'presentationPrivate' => $presentationPrivate,
				'foodcostAwareness' => $foodcostAwareness,
				'foodcostAwarenessNotes' => $foodcostAwarenessNotes,
				'foodcostAwarenessPrivate' => $foodcostAwarenessPrivate,
				'hrIssues' => $hrIssues,
				'hrIssuesNotes' => $hrIssuesNotes,
				'hrIssuesPrivate' => $hrIssuesPrivate,
				'morale' => $morale,
				'moraleNotes' => $moraleNotes,
				'moralePrivate' => $moralePrivate,
				'purchCompliance' => $purchCompliance,
				'purchComplianceNotes' => $purchComplianceNotes,
				'purchCompliancePrivate' => $purchCompliancePrivate,
				'haccpCompliance' => $haccpCompliance,
				'haccpComplianceNotes' => $haccpComplianceNotes,
				'haccpCompliancePrivate' => $haccpCompliancePrivate,
				'healthSafetyIso' => $healthSafetyIso,
				'healthSafetyIsoNotes' => $healthSafetyIsoNotes,
				'healthSafetyIsoPrivate' => $healthSafetyIsoPrivate,
				'accidentsIncidents' => $accidentsIncidents,
				'accidentsIncidentsNotes' => $accidentsIncidentsNotes,
				'accidentsIncidentsPrivate' => $accidentsIncidentsPrivate,
				'securityCashControl' => $securityCashControl,
				'securityCashControlNotes' => $securityCashControlNotes,
				'securityCashControlPrivate' => $securityCashControlPrivate,
				'marketingUpselling' => $marketingUpselling,
				'marketingUpsellingNotes' => $marketingUpsellingNotes,
				'marketingUpsellingPrivate' => $marketingUpsellingPrivate,
				'training' => $training,
				'trainingNotes' => $trainingNotes,
				'trainingPrivate' => $trainingPrivate,
				'objectives' => $objectives,
				'objectivesPrivate' => $objectivesPrivate,
				'outstandingIssues' => $outstandingIssues,
				'outstandingIssuesPrivate' => $outstandingIssuesPrivate,
				'spProjectsFunctions' => $spProjectsFunctions,
				'spProjectsFunctionsPrivate' => $spProjectsFunctionsPrivate,
				'innovation' => $innovation,
				'innovationPrivate' => $innovationPrivate,
				'addSupportRequired' => $addSupportRequired,
				'addSupportRequiredPrivate' => $addSupportRequiredPrivate,
				'attachedFiles' => $attachedFiles,
				'sendEmail' => $sendEmail
			]
		);
	}

	/**
	 * Operations scorecard confirmation
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function operationsScorecardConfirmation(Request $request)
	{
		// Attached files
		$appFilePath = config('app.fpath');
		$appUrl = config('app.url');
		$lfmPrefix = config('lfm.url_prefix');

		$filesIdList = [];
		$filesNameList = [];

		$files = $request->file_id !== '' ? explode(',', $request->file_id) : [];

		foreach ($files as $fileUrl) {
			$realPath = str_replace("{$appUrl}/{$lfmPrefix}/", $appFilePath, $fileUrl);
			$fileInfo = pathinfo($realPath);
			$dirPath = $fileInfo['dirname'] . '/';
			$fileName = $fileInfo['basename'];

			$file = File::where('is_dir', 0)->where('dir_path', $dirPath)->where('dir_file_name', $fileName)->first();

			if (!is_null($file)) {
				$filesIdList[] = $file->id;
				$filesNameList[] = $fileName;
			}
		}

		$attachedFileIds = implode(',', $filesIdList);
		$attachedFileNames = implode(', ', $filesNameList);

		$backData = [
			'unit_id' => $request->unit_id,
			'scorecard_date' => $request->scorecard_date,
			'presentation' => $request->presentation,
			'presentation_notes' => $request->presentation_notes,
			'presentation_private' => $request->presentation_private,
			'foodcost_awareness' => $request->foodcost_awareness,
			'foodcost_awareness_notes' => $request->foodcost_awareness_notes,
			'foodcost_awareness_private' => $request->foodcost_awareness_private,
			'hr_issues' => $request->hr_issues,
			'hr_issues_notes' => $request->hr_issues_notes,
			'hr_issues_private' => $request->hr_issues_private,
			'morale' => $request->morale,
			'morale_notes' => $request->morale_notes,
			'morale_private' => $request->morale_private,
			'purch_compliance' => $request->purch_compliance,
			'purch_compliance_notes' => $request->purch_compliance_notes,
			'purch_compliance_private' => $request->purch_compliance_private,
			'haccp_compliance' => $request->haccp_compliance,
			'haccp_compliance_notes' => $request->haccp_compliance_notes,
			'haccp_compliance_private' => $request->haccp_compliance_private,
			'health_safety_iso' => $request->health_safety_iso,
			'health_safety_iso_notes' => $request->health_safety_iso_notes,
			'health_safety_iso_private' => $request->health_safety_iso_private,
			'accidents_incidents' => $request->accidents_incidents,
			'accidents_incidents_notes' => $request->accidents_incidents_notes,
			'accidents_incidents_private' => $request->accidents_incidents_private,
			'security_cash_ctl' => $request->security_cash_ctl,
			'security_cash_ctl_notes' => $request->security_cash_ctl_notes,
			'security_cash_ctl_private' => $request->security_cash_ctl_private,
			'marketing_upselling' => $request->marketing_upselling,
			'marketing_upselling_notes' => $request->marketing_upselling_notes,
			'marketing_upselling_private' => $request->marketing_upselling_private,
			'training' => $request->training,
			'training_notes' => $request->training_notes,
			'training_private' => $request->training_private,
			'objectives' => $request->objectives,
			'objectives_private' => $request->objectives_private,
			'outstanding_issues' => $request->outstanding_issues,
			'outstanding_issues_private' => $request->outstanding_issues_private,
			'sp_projects_functions' => $request->sp_projects_functions,
			'sp_projects_functions_private' => $request->sp_projects_functions_private,
			'innovation' => $request->innovation,
			'innovation_private' => $request->innovation_private,
			'add_support_required' => $request->add_support_required,
			'add_support_required_private' => $request->add_support_required_private,
			'send_email' => $request->send_email,
			'attached_files' => $attachedFileIds
		];

		// Score
		$scores = [];

		foreach (OperationsScorecard::$scoreFields as $scoreField) {
			if ($request->{$scoreField} != 0) {
				$scores[] = $request->{$scoreField};
			}
		}

		$averageScore = count($scores) > 0 ? round(array_sum($scores) / count($scores)) : 0;

		// Unit
		$selectedUnit = Unit::find($request->unit_id);

		return view(
			'sheets.operations-scorecard.confirmation', [
				'unitId' => $selectedUnit->unit_id,
				'unitName' => $selectedUnit->unit_name,
				'scorecardDate' => $request->scorecard_date,
				'onsiteVisits' => $request->onsite_visits,
				'averageScore' => $averageScore,
				'presentation' => $request->presentation,
				'presentationNotes' => $request->presentation_notes,
				'presentationPrivate' => $request->presentation_private,
				'foodcostAwareness' => $request->foodcost_awareness,
				'foodcostAwarenessNotes' => $request->foodcost_awareness_notes,
				'foodcostAwarenessPrivate' => $request->foodcost_awareness_private,
				'hrIssues' => $request->hr_issues,
				'hrIssuesNotes' => $request->hr_issues_notes,
				'hrIssuesPrivate' => $request->hr_issues_private,
				'morale' => $request->morale,
				'moraleNotes' => $request->morale_notes,
				'moralePrivate' => $request->morale_private,
				'purchCompliance' => $request->purch_compliance,
				'purchComplianceNotes' => $request->purch_compliance_notes,
				'purchCompliancePrivate' => $request->purch_compliance_private,
				'haccpCompliance' => $request->haccp_compliance,
				'haccpComplianceNotes' => $request->haccp_compliance_notes,
				'haccpCompliancePrivate' => $request->haccp_compliance_private,
				'healthSafetyIso' => $request->health_safety_iso,
				'healthSafetyIsoNotes' => $request->health_safety_iso_notes,
				'healthSafetyIsoPrivate' => $request->health_safety_iso_private,
				'accidentsIncidents' => $request->accidents_incidents,
				'accidentsIncidentsNotes' => $request->accidents_incidents_notes,
				'accidentsIncidentsPrivate' => $request->accidents_incidents_private,
				'securityCashControl' => $request->security_cash_ctl,
				'securityCashControlNotes' => $request->security_cash_ctl_notes,
				'securityCashControlPrivate' => $request->security_cash_ctl_private,
				'marketingUpselling' => $request->marketing_upselling,
				'marketingUpsellingNotes' => $request->marketing_upselling_notes,
				'marketingUpsellingPrivate' => $request->marketing_upselling_private,
				'training' => $request->training,
				'trainingNotes' => $request->training_notes,
				'trainingPrivate' => $request->training_private,
				'objectives' => $request->objectives,
				'objectivesPrivate' => $request->objectives_private,
				'outstandingIssues' => $request->outstanding_issues,
				'outstandingIssuesPrivate' => $request->outstanding_issues_private,
				'spProjectsFunctions' => $request->sp_projects_functions,
				'spProjectsFunctionsPrivate' => $request->sp_projects_functions_private,
				'innovation' => $request->innovation,
				'innovationPrivate' => $request->innovation_private,
				'addSupportRequired' => $request->add_support_required,
				'addSupportRequiredPrivate' => $request->add_support_required_private,
				'attachedFiles' => $attachedFileIds,
				'files' => $attachedFileNames,
				'sendEmail' => $request->send_email,
				'backData' => serialize($backData)
			]
		);
	}

	/**
	 * Save operations score card
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function operationsScorecardPost(Request $request)
	{
		$userId = session()->get('userId');

		try {
			$scoreCard = OperationsScorecard::create(
				[
					'unit_id' => $request->unit_id,
					'scorecard_date' => Carbon::parse($request->scorecard_date)->format('Y-m-d H:i'),
					'created_by' => $userId,
					'presentation' => $request->presentation,
					'presentation_notes' => $request->presentation_notes,
					'presentation_private' => $request->presentation_private,
					'foodcost_awareness' => $request->foodcost_awareness,
					'foodcost_awareness_notes' => $request->foodcost_awareness_notes,
					'foodcost_awareness_private' => $request->foodcost_awareness_private,
					'hr_issues' => $request->hr_issues,
					'hr_issues_notes' => $request->hr_issues_notes,
					'hr_issues_private' => $request->hr_issues_private,
					'morale' => $request->morale,
					'morale_notes' => $request->morale_notes,
					'morale_private' => $request->morale_private,
					'purch_compliance' => $request->purch_compliance,
					'purch_compliance_notes' => $request->purch_compliance_notes,
					'purch_compliance_private' => $request->purch_compliance_private,
					'haccp_compliance' => $request->haccp_compliance,
					'haccp_compliance_notes' => $request->haccp_compliance_notes,
					'haccp_compliance_private' => $request->haccp_compliance_private,
					'health_safety_iso' => $request->health_safety_iso,
					'health_safety_iso_notes' => $request->health_safety_iso_notes,
					'health_safety_iso_private' => $request->health_safety_iso_private,
					'accidents_incidents' => $request->accidents_incidents,
					'accidents_incidents_notes' => $request->accidents_incidents_notes,
					'accidents_incidents_private' => $request->accidents_incidents_private,
					'security_cash_ctl' => $request->security_cash_ctl,
					'security_cash_ctl_notes' => $request->security_cash_ctl_notes,
					'security_cash_ctl_private' => $request->security_cash_ctl_private,
					'marketing_upselling' => $request->marketing_upselling,
					'marketing_upselling_notes' => $request->marketing_upselling_notes,
					'marketing_upselling_private' => $request->marketing_upselling_private,
					'training' => $request->training,
					'training_notes' => $request->training_notes,
					'training_private' => $request->training_private,
					'objectives' => $request->objectives,
					'objectives_private' => $request->objectives_private,
					'outstanding_issues' => $request->outstanding_issues,
					'outstanding_issues_private' => $request->outstanding_issues_private,
					'sp_projects_functions' => $request->sp_projects_functions,
					'sp_projects_functions_private' => $request->sp_projects_functions_private,
					'innovation' => $request->innovation,
					'innovation_private' => $request->innovation_private,
					'add_support_req' => $request->add_support_required,
					'add_support_req_private' => $request->add_support_required_private,
					'send_email' => $request->send_email,
					'attached_files' => $request->attached_files,
				]
			);

			if ($request->send_email) {
				// Files
				$attachedFiles = File::whereIn('id', explode(',', $request->attached_files))->get();

				$filePathList = [];
				foreach ($attachedFiles as $attachedFile) {
					$filePathList[] = $attachedFile->dir_path . $attachedFile->dir_file_name;
				}

				// Unit info
				$unit = Unit::findOrFail($request->unit_id);

				$unitOperationGroups = $unit->operations_group !== '' ? explode(',', $unit->operations_group) : [];

				if (is_array($unitOperationGroups) && count($unitOperationGroups) > 0) {
					$region = Region::whereIn('region_id', $unitOperationGroups)->first();
					$regionName = $region->region_name;
				} else {
					$regionName = '';
				}

				$operationManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

				if (is_array($operationManagers) && count($operationManagers) > 0) {
					$operationManager = User::whereIn('user_id', $operationManagers)->first();
					$operationsManagerName = $operationManager->username;
				} else {
					$operationsManagerName = '';
				}

				$contractStatus = $unit->status->name;

				$clientContact = $unit->client_contact_name;

				$unitBudget = TradingAccount::where('unit_id', $unit->unit_id)->orderBy('trading_account_id', 'desc')->first();

				$contractType = ($unitBudget && $unitBudget->contractType) ? $unitBudget->contractType->title : '';

				$onsiteVisits = CustomerFeedback::where('unit_id', $unit->unit_id)
					->where('contact_type_id', ContactType::CONTACT_TYPE_MEETING)
					->whereDate('contact_date', '>=', Carbon::now()->startOfMonth())
					->count();

				$unitInfo = [
					'unitName' => $unit->unit_name,
					'regionName' => $regionName,
					'contractStatus' => $contractStatus,
					'operationsManagerName' => $operationsManagerName,
					'contractType' => $contractType,
					'onsiteVisits' => $onsiteVisits,
					'clientContact' => $clientContact,
				];

				// Add operations managers to the email in CC
				$operationsManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

				$ccEmails = [];

				if (count($operationsManagers) > 0) {
					$ccEmails = User::whereIn('user_id', $operationsManagers)->pluck('user_email');
				}

				// Send email
				Mail::to($unit->email)
					->cc($ccEmails)
					->send(
						new OperationScorecardEmail($scoreCard, $unitInfo, $filePathList)
					);
			}

			Session::flash('flash_message', 'Operations Scorecard has been saved successfully!');
		} catch (\Exception $e) {
			Session::flash('error_message', 'Error saving Operations Scorecard.');
		}

		return redirect('/sheets/operations-scorecard')->cookie('operationsScorecardUnitIdCookie', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	/**
	 * Operations scorecard edit
	 *
	 * @param Request $request
	 * @int $opsScorecardId
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function operationsScorecardEdit(Request $request, $opsScorecardId)
	{
		// Get list of units for current user level
		$userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

		// Scorecard
		$operationsScorecard = OperationsScorecard::with('unit')->find($opsScorecardId);

		$scorecardDate = !is_null($operationsScorecard) ? Carbon::parse($operationsScorecard->scorecard_date) : Carbon::now();

		// Attached files
		$appFilePath = config('app.fpath');

		$attachedFiles = [];

		$files = File::whereIn('id', explode(',', $operationsScorecard->attached_files))->get();

		foreach ($files as $file) {
			$filePath = str_replace($appFilePath, '', $file->dir_path);
			$fileUrl = url('/laravel-filemanager/') . "/{$filePath}/{$file->dir_file_name}";

			$attachedFiles[$file->dir_file_name] = $fileUrl;
		}

		return view(
			'sheets.operations-scorecard.edit',
			[
				'operationsScorecard' => $operationsScorecard,
				'attachedFiles' => $attachedFiles,
				'scorecardDate' => $scorecardDate->format('d-m-Y'),
				'userUnits' => $userUnits->toArray(),
				'score' => ['Not checked'] + range(0, 10),
			]
		);
	}

	/**
	 * Save operations score card
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function operationsScorecardSave(Request $request)
	{
		$userId = session()->get('userId');

		if (Gate::denies('operations-user-group')) {
			abort(403, 'Access denied');
		}

		try {
			$operationsScorecard = OperationsScorecard::findOrFail($request->ops_scorecard_id);

			$operationsScorecard->unit_id = $request->unit_id;
			$operationsScorecard->scorecard_date = Carbon::parse($request->scorecard_date)->format('Y-m-d H:i');
			$operationsScorecard->presentation = $request->presentation;
			$operationsScorecard->presentation_notes = $request->presentation_notes;
			$operationsScorecard->presentation_private = $request->presentation_private;
			$operationsScorecard->foodcost_awareness = $request->foodcost_awareness;
			$operationsScorecard->foodcost_awareness_notes = $request->foodcost_awareness_notes;
			$operationsScorecard->foodcost_awareness_private = $request->foodcost_awareness_private;
			$operationsScorecard->hr_issues = $request->hr_issues;
			$operationsScorecard->hr_issues_notes = $request->hr_issues_notes;
			$operationsScorecard->hr_issues_private = $request->hr_issues_private;
			$operationsScorecard->morale = $request->morale;
			$operationsScorecard->morale_notes = $request->morale_notes;
			$operationsScorecard->morale_private = $request->morale_private;
			$operationsScorecard->purch_compliance = $request->purch_compliance;
			$operationsScorecard->purch_compliance_notes = $request->purch_compliance_notes;
			$operationsScorecard->purch_compliance_private = $request->purch_compliance_private;
			$operationsScorecard->haccp_compliance = $request->haccp_compliance;
			$operationsScorecard->haccp_compliance_notes = $request->haccp_compliance_notes;
			$operationsScorecard->haccp_compliance_private = $request->haccp_compliance_private;
			$operationsScorecard->health_safety_iso = $request->health_safety_iso;
			$operationsScorecard->health_safety_iso_notes = $request->health_safety_iso_notes;
			$operationsScorecard->health_safety_iso_private = $request->health_safety_iso_private;
			$operationsScorecard->accidents_incidents = $request->accidents_incidents;
			$operationsScorecard->accidents_incidents_notes = $request->accidents_incidents_notes;
			$operationsScorecard->accidents_incidents_private = $request->accidents_incidents_private;
			$operationsScorecard->security_cash_ctl = $request->security_cash_ctl;
			$operationsScorecard->security_cash_ctl_notes = $request->security_cash_ctl_notes;
			$operationsScorecard->security_cash_ctl_private = $request->security_cash_ctl_private;
			$operationsScorecard->marketing_upselling = $request->marketing_upselling;
			$operationsScorecard->marketing_upselling_notes = $request->marketing_upselling_notes;
			$operationsScorecard->marketing_upselling_private = $request->marketing_upselling_private;
			$operationsScorecard->training = $request->training;
			$operationsScorecard->training_notes = $request->training_notes;
			$operationsScorecard->training_private = $request->training_private;
			$operationsScorecard->objectives = $request->objectives;
			$operationsScorecard->objectives_private = $request->objectives_private;
			$operationsScorecard->outstanding_issues = $request->outstanding_issues;
			$operationsScorecard->outstanding_issues_private = $request->outstanding_issues_private;
			$operationsScorecard->sp_projects_functions = $request->sp_projects_functions;
			$operationsScorecard->sp_projects_functions_private = $request->sp_projects_functions_private;
			$operationsScorecard->innovation = $request->innovation;
			$operationsScorecard->innovation_private = $request->innovation_private;
			$operationsScorecard->add_support_req = $request->add_support_req;
			$operationsScorecard->add_support_req_private = $request->add_support_required_private;
			$operationsScorecard->send_email = $request->send_email;
			$operationsScorecard->modified_by = $userId;
			$operationsScorecard->date_modified = Carbon::now();

			// Attached files
			$appFilePath = config('app.fpath');
			$appUrl = config('app.url');
			$lfmPrefix = config('lfm.url_prefix');

			$files = $request->file_id !== '' ? explode(',', $request->file_id) : [];

			$filesIdList = [];
			$filesPathList = [];

			foreach ($files as $fileUrl) {
				$realPath = str_replace("{$appUrl}/{$lfmPrefix}/", $appFilePath, $fileUrl);
				$fileInfo = pathinfo($realPath);
				$dirPath = $fileInfo['dirname'] . '/';
				$fileName = $fileInfo['basename'];

				$file = File::where('is_dir', 0)->where('dir_path', $dirPath)->where('dir_file_name', $fileName)->first();

				if (!is_null($file)) {
					$filesIdList[] = $file->id;
					$filesPathList[] = $file->dir_path . $file->dir_file_name;
				}
			}

			$operationsScorecard->attached_files = implode(',', $filesIdList);

			$operationsScorecard->save();

			if ($request->send_email) {
				// Unit info
				$unit = Unit::findOrFail($request->unit_id);

				$unitOperationGroups = $unit->operations_group !== '' ? explode(',', $unit->operations_group) : [];

				if (is_array($unitOperationGroups) && count($unitOperationGroups) > 0) {
					$region = Region::whereIn('region_id', $unitOperationGroups)->first();
					$regionName = $region->region_name;
				} else {
					$regionName = '';
				}

				$operationManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

				if (is_array($operationManagers) && count($operationManagers) > 0) {
					$operationManager = User::whereIn('user_id', $operationManagers)->first();
					$operationsManagerName = $operationManager->username;
				} else {
					$operationsManagerName = '';
				}

				$contractStatus = $unit->status->name;

				$clientContact = $unit->client_contact_name;

				$unitBudget = TradingAccount::where('unit_id', $unit->unit_id)->orderBy('trading_account_id', 'desc')->first();

				$contractType = ($unitBudget && $unitBudget->contractType) ? $unitBudget->contractType->title : '';

				$onsiteVisits = CustomerFeedback::where('unit_id', $unit->unit_id)
					->where('contact_type_id', ContactType::CONTACT_TYPE_MEETING)
					->whereDate('contact_date', '>=', Carbon::now()->startOfMonth())
					->count();

				$unitInfo = [
					'unitName' => $unit->unit_name,
					'regionName' => $regionName,
					'contractStatus' => $contractStatus,
					'operationsManagerName' => $operationsManagerName,
					'contractType' => $contractType,
					'onsiteVisits' => $onsiteVisits,
					'clientContact' => $clientContact,
				];

				// Add operations managers to the email in CC
				$operationsManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

				$ccEmails = [];

				if (count($operationsManagers) > 0) {
					$ccEmails = User::whereIn('user_id', $operationsManagers)->pluck('user_email');
				}

				Mail::to($unit->email)
					->cc($ccEmails)
					->send(
						new OperationScorecardEmail($operationsScorecard, $unitInfo, $filesPathList)
					);
			}

			Session::flash('flash_message', 'Operations Scorecard has been saved successfully!');
		} catch (\Exception $e) {
			Session::flash('error_message', 'Error saving Operations Scorecard.');
		}

		return redirect("/sheets/operations-scorecard/edit/{$request->ops_scorecard_id}");
	}

	/**
	 * Get data for the unit scorecard / feedback
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function scorecardInfo(Request $request)
	{
		if (Gate::denies('operations-user-group')) {
			abort(403, 'Access denied');
		}

		$unitId = $request->input('unit_id', 0);
		$unit = Unit::find($unitId);

		if (!is_null($unit)) {
			// Region
			$unitOperationGroups = $unit->operations_group !== '' ? explode(',', $unit->operations_group) : [];

			if (is_array($unitOperationGroups) && count($unitOperationGroups) > 0) {
				$region = Region::whereIn('region_id', $unitOperationGroups)->first();
				$regionName = $region->region_name;
			} else {
				$regionName = '';
			}

			// Operation manager
			$operationManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

			if (is_array($operationManagers) && count($operationManagers) > 0) {
				$operationManager = User::whereIn('user_id', $operationManagers)->first();
				$operationsManagerName = $operationManager->username;
			} else {
				$operationsManagerName = '';
			}

			// Other
			$contractStatus = $unit->status->name;

			$clientContact = $unit->client_contact_name;

			$unitBudget = TradingAccount::where('unit_id', $unitId)->orderBy('trading_account_id', 'desc')->first();

			$contractType = ($unitBudget && $unitBudget->contractType) ? $unitBudget->contractType->title : '';
		} else {
			$regionName = '';
			$operationsManagerName = '';
			$contractStatus = '';
			$clientContact = '';
			$contractType = '';
		}

		$onsiteVisits = CustomerFeedback::where('unit_id', $unitId)
			->where('contact_type_id', ContactType::CONTACT_TYPE_MEETING)
			->whereDate('contact_date', '>=', Carbon::now()->startOfMonth())
			->count();

		$operationsScorecards = OperationsScorecard::where('unit_id', $unitId)
			->whereDate('scorecard_date', '>=', Carbon::now()->startOfMonth())
			->get();

		$avgScorecards = [];

		foreach (OperationsScorecard::$scoreFields as $scoreField) {
			$avgScorecards[$scoreField] = $operationsScorecards->avg($scoreField);
		}

		return response()->json(
			[
				'regionName' => $regionName,
				'operationsManagerName' => $operationsManagerName,
				'contractStatus' => $contractStatus,
				'contractType' => $contractType,
				'clientContact' => $clientContact,
				'onsiteVisits' => $onsiteVisits,
				'avgScorecards' => $avgScorecards
			]
		);
	}

	/**
	 * Customer feedback
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function customerFeedback(Request $request)
	{
		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		$unitId = Cookie::get('customerFeedbackUnitIdCookie', 0);
		$contactDate = Carbon::now()->format('d-m-Y');
		$contactTime = '';
		$contactType = '';
		$notes = '';
		$customerFeedback = '';

		// Back from the confirm page
		if ($request->isMethod('post')) {
			$backData = unserialize($request->back_data);

			$unitId = $backData['unit_id'];
			$contactDate = $backData['contact_date'];
			$contactTime = $backData['contact_time'];
			$contactType = $backData['contact_type'];
			$notes = $backData['notes'];
			$customerFeedback = $backData['customer_feedback'];
		}

		// Dropdowns
		$contactTypes = ContactType::orderBy('position')->pluck('title', 'id');
		$feedbackTypes = FeedbackType::all()->pluck('title', 'id');

		return view(
			'sheets.customer-feedback.index', [
				'userUnits' => ['' => 'Select unit'] + $userUnits->toArray(),
				'selectedUnit' => $unitId,
				'contactDate' => $contactDate,
				'contactTime' => $contactTime,
				'regionName' => '',
				'operationsManagerName' => '',
				'contractStatus' => '',
				'contractType' => '',
				'onsiteVisits' => '',
				'clientContact' => '',
				'contactType' => $contactType,
				'notes' => $notes,
				'customerFeedback' => $customerFeedback,
				'contactTypes' => $contactTypes,
				'feedbackTypes' => $feedbackTypes
			]
		);
	}

	/**
	 * Customer feedback confirmation
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function customerFeedbackConfirmation(Request $request)
	{
		$backData = [
			'unit_id' => $request->unit_id,
			'contact_date' => $request->contact_date,
			'contact_time' => $request->contact_time,
			'contact_type' => $request->contact_type,
			'notes' => $request->notes,
			'customer_feedback' => $request->customer_feedback
		];

		$selectedUnit = Unit::find($request->unit_id);

		// Dropdowns
		$contactType = ContactType::find($request->contact_type);
		$contactTypeLegend = $contactType ? $contactType->title : '';

		$customerFeedback = FeedbackType::find($request->customer_feedback);
		$customerFeedbackLegend = $customerFeedback ? $customerFeedback->title : '';

		return view(
			'sheets.customer-feedback.confirmation', [
				'unitId' => $selectedUnit->unit_id,
				'unitName' => $selectedUnit->unit_name,
				'contactDateTime' => "{$request->contact_date} {$request->contact_time}",
				'contactType' => $request->contact_type,
				'contactTypeLegend' => $contactTypeLegend,
				'notes' => $request->notes,
				'customerFeedback' => $request->customer_feedback,
				'customerFeedbackLegend' => $customerFeedbackLegend,
				'backData' => serialize($backData)
			]
		);
	}

	/**
	 * Save customer feedback
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
	 */
	public function customerFeedbackPost(Request $request)
	{
		try {
			CustomerFeedback::create(
				[
					'unit_id' => $request->unit_id,
					'contact_type_id' => $request->contact_type,
					'feedback_type_id' => $request->customer_feedback,
					'contact_date' => Carbon::parse($request->contact_date_time)->format('Y-m-d H:i'),
					'notes' => $request->notes
				]
			);

			Session::flash('flash_message', 'The form was successfully completed!');
		} catch (\Exception $e) {
			Session::flash('error_message', 'Error while saving form!');
		}

		return redirect('/sheets/customer-feedback')->cookie('customerFeedbackUnitIdCookie', $request->unit_id, time() + (10 * 365 * 24 * 60 * 60));
	}

	/**
	 * Get data for the unit scorecard / feedback
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function unitInfo(Request $request)
	{
		if (Gate::denies('operations-user-group')) {
			abort(403, 'Access denied');
		}

		$unitId = $request->input('unit_id', 0);
		$unit = Unit::find($unitId);

		if (!is_null($unit)) {
			// Region
			$unitOperationGroups = $unit->operations_group !== '' ? explode(',', $unit->operations_group) : [];

			if (is_array($unitOperationGroups) && count($unitOperationGroups) > 0) {
				$region = Region::whereIn('region_id', $unitOperationGroups)->first();
				$regionName = $region->region_name;
			} else {
				$regionName = '';
			}

			// Operation manager
			$operationManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

			if (is_array($operationManagers) && count($operationManagers) > 0) {
				$operationManager = User::whereIn('user_id', $operationManagers)->first();
				$operationsManagerName = $operationManager->username;
			} else {
				$operationsManagerName = '';
			}

			// Other
			$contractStatus = $unit->status->name;

			$clientContact = $unit->client_contact_name;

			$unitBudget = TradingAccount::where('unit_id', $unitId)->orderBy('trading_account_id', 'desc')->first();

			$contractType = ($unitBudget && $unitBudget->contractType) ? $unitBudget->contractType->title : '';
		} else {
			$regionName = '';
			$operationsManagerName = '';
			$contractStatus = '';
			$clientContact = '';
			$contractType = '';
		}

		$lastFeedbacks = CustomerFeedback::with(['contactType', 'feedbackType'])
			->where('unit_id', $unitId)
			->orderBy('contact_date', 'desc')
			->whereDate('contact_date', '>=', Carbon::now()->startOfMonth())
			->get();

		$onsiteVisits = $lastFeedbacks->count();

		return response()->json(
			[
				'regionName' => $regionName,
				'operationsManagerName' => $operationsManagerName,
				'contractStatus' => $contractStatus,
				'contractType' => $contractType,
				'clientContact' => $clientContact,
				'lastFeedbacks' => $lastFeedbacks,
				'onsiteVisits' => $onsiteVisits
			]
		);
	}

	public function regNumberCashPurchasesCreditSales(Request $request)
	{
		$cashSalesDataArr = array();
		$cashSalesData = array();
		if ($request->has('unit_name') && $request->unit_name > 0) {
			// Reg Number Radio Buttons [Starts]
			$sheet_id = $request->sheet_id;
			$regNumStr = '';
			$regTabIndex = 2;
			$selectedCash = 0;
			if ($sheet_id > 0) {
				$selectedCash = 1;
				$query = \DB::table('cash_sales as CS');
				$query->where('CS.cash_sales_id', '=', $sheet_id);
				$cash_sale_data = $query->first();
			}
			//dd($cash_sale_data);

			$regManagement = \DB::select(
				"SELECT reg_management_id, reg_number
                    FROM `reg_management`
                    WHERE unit_id='" . $request->unit_name . "'
                    ORDER BY reg_number
                "
			);
			if ($regManagement) {
				$cashSalesData['total_reg_nums'] = count($regManagement);

				$k = 1;
				foreach ($regManagement as $regValue) {
					$selectedRegNum = $regValue->reg_management_id == $request->selected_reg_number ? 'checked="checked"' : $k == 1 ? 'checked="checked"' : '';
					$regNumStr .= '<div class="radio"><label><input type="radio" name="reg_number" id="reg_number_' . $k . '" ' . $selectedRegNum . ' tabindex="' . $regTabIndex . '" value="' . $regValue->reg_management_id . '"> ' . $regValue->reg_number . '</label></div>';
					$k++;
				}

				$cashSalesData['reg_num'] = $regNumStr;
			} else {
				$cashSalesData['reg_num'] = "<input class='form-control' tabindex='" . $regTabIndex . "' type='text' name='reg_number' value='' id='reg_number' />";
			}
			// Reg Number Radio Buttons [Ends]

			// Cash Purchases Checkboxes [Starts]
			$cashPurchStr = '';
			$cashPurchTabIndex = 14;

			$cashPurch = \DB::select(
				"SELECT unique_id, receipt_invoice_date, purchase_details, gross_total
                    FROM `purchases`
                    WHERE cash_sale_vis = 1 AND purch_type = 'cash' AND deleted = 0 AND unit_id=" . $request->unit_name . " GROUP BY unique_id
                "
			);
			//credit_sales_id
			//cash_purchases_id
			//selectedCash			
			//dd($cashPurch);
			$selectedLinkPurchase = 0;
			if ($selectedCash == 1) {
				if ($cash_sale_data->cash_purchases_id != '') {
					$purchases = \DB::table('purchases as P');
					$purchases->where('cash_sale_vis', '=', 1);
					$purchases->where('purch_type', '=', 'cash');
					$purchases->where('deleted', '=', 0);
					$purchases->whereIn('P.unique_id', explode(",", $cash_sale_data->cash_purchases_id));
					$purchases_sel_data = $purchases->get();
				}
			}
			if ($cashPurch) {
				$i = 1;
				$checkedCashPurch = '';
				//print_r(Session::get('purchIdsArrSession'));
				foreach ($cashPurch as $cashPurchValue) {
					if (Session::get('purchIdsArrSession')) {
						$checkedCashPurch = in_array($cashPurchValue->unique_id, Session::get('purchIdsArrSession')) ? 'checked="checked"' : '';
					} else {
						$selectedLinkPurchase = 1;
					}

					$cashPurchStr .= '<div class="checkbox"><label><input ' . $checkedCashPurch . ' type="checkbox" tabindex="' . $cashPurchTabIndex . '" id="cash_purch_chk_' . $i . '" name="cash_purch_chk_' . $i . '" value="' . number_format($cashPurchValue->gross_total, 2, ".", ",") . '" class="checkboxes"> &euro;' . number_format($cashPurchValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($cashPurchValue->receipt_invoice_date)) . ' - ' . $cashPurchValue->purchase_details . '</label></div>';
					$cashPurchStr .= '<input type="hidden" name="purch_id_' . $i . '" value="' . $cashPurchValue->unique_id . '">';
					//echo $cashPurchStr;
					$i++;
				}
				if ($selectedLinkPurchase == 1 && $request->sheet_id != '') {
					//////////// Get Sheet ID
					$cashPurch1 = \DB::select(
						"SELECT unique_id, receipt_invoice_date, purchase_details, gross_total
							FROM `purchases`
							WHERE cash_sale_vis = 0 AND purch_type = 'cash' AND deleted = 0 AND cash_sales_record_id=" . $request->sheet_id . " GROUP BY unique_id
						"
					);
					foreach ($cashPurch1 as $cashPurchValue) {

						$cashPurchStr .= '<div class="checkbox"><label><input checked="checked" type="checkbox" tabindex="' . $cashPurchTabIndex . '" id="cash_purch_chk_' . $i . '" name="cash_purch_chk_' . $i . '" value="' . number_format($cashPurchValue->gross_total, 2, ".", ",") . '" class="checkboxes"> &euro;' . number_format($cashPurchValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($cashPurchValue->receipt_invoice_date)) . ' - ' . $cashPurchValue->purchase_details . '</label></div>';
						$cashPurchStr .= '<input type="hidden" name="purch_id_' . $i . '" value="' . $cashPurchValue->unique_id . '">';
						//echo $cashPurchStr;
						$i++;
					}
				}
				if (isset($purchases_sel_data) && count($purchases_sel_data) > 0 && !empty($purchases_sel_data)) {
					foreach ($purchases_sel_data as $cashPurchValue) {
						$cashPurchStr .= '<div class="checkbox"><label><input ' . $checkedCashPurch . ' type="checkbox" tabindex="' . $cashPurchTabIndex . '" id="cash_purch_chk_' . $i . '" name="cash_purch_chk_' . $i . '" value="' . number_format($cashPurchValue->gross_total, 2, ".", ",") . '" class="checkboxes" checked="checked"> &euro;' . number_format($cashPurchValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($cashPurchValue->receipt_invoice_date)) . ' - ' . $cashPurchValue->purchase_details . '</label></div>';
						$cashPurchStr .= '<input type="hidden" name="purch_id_' . $i . '" value="' . $cashPurchValue->unique_id . '">';
						$i++;
					}
				}
				$cashSalesData['cash_purchases_data'] = $cashPurchStr;
			} else {
				$cashPurchStr = '';
				$i = 1;
				$checkedCashPurch = '';
				if (isset($purchases_sel_data) && count($purchases_sel_data) > 0 && !empty($purchases_sel_data)) {
					foreach ($purchases_sel_data as $cashPurchValue) {
						$cashPurchStr .= '<div class="checkbox"><label><input ' . $checkedCashPurch . ' type="checkbox" tabindex="' . $cashPurchTabIndex . '" id="cash_purch_chk_' . $i . '" name="cash_purch_chk_' . $i . '" value="' . number_format($cashPurchValue->gross_total, 2, ".", ",") . '" class="checkboxes" checked="checked"> &euro;' . number_format($cashPurchValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($cashPurchValue->receipt_invoice_date)) . ' - ' . $cashPurchValue->purchase_details . '</label></div>';
						$cashPurchStr .= '<input type="hidden" name="purch_id_' . $i . '" value="' . $cashPurchValue->unique_id . '">';
						$i++;
					}
				}
				$cashSalesData['cash_purchases_data'] = $cashPurchStr;
			}
			// Cash Purchases Checkboxes [Ends]

			// Credit Sales Checkboxes [Starts]
			$creditSalesStr = '';
			$creditSalesTabIndex = 15;

			$creditSales = \DB::select(
				"SELECT credit_sales_id, credit_reference, sale_date, gross_total
                    FROM `credit_sales`
                    WHERE cash_sale_vis = 1 AND unit_id=" . $request->unit_name
			);
			if ($selectedCash == 1) {
				if ($cash_sale_data->credit_sales_id != '') {
					$credit_sales = \DB::table('credit_sales as CS');
					$credit_sales->whereIn('CS.credit_sales_id', explode(",", $cash_sale_data->credit_sales_id));
					$credit_sel_data = $credit_sales->get();
				}
			}
			if ($creditSales) {
				$i = 1;
				$checkedCreditSales = '';
				foreach ($creditSales as $creditSalesValue) {
					if (Session::get('creditSalesIdsArrSession')) {
						$checkedCreditSales = in_array($creditSalesValue->credit_sales_id, Session::get('creditSalesIdsArrSession')) ? 'checked="checked"' : '';
					}

					$creditSalesStr .= '<div class="checkbox"><label><input ' . $checkedCreditSales . ' type="checkbox" tabindex="' . $creditSalesTabIndex . '" id="credit_sales_chk_' . $i . '" name="credit_sales_chk_' . $i . '" value="' . number_format($creditSalesValue->gross_total, 2, ".", ",") . '" class="checkboxes"> &euro;' . number_format($creditSalesValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($creditSalesValue->sale_date)) . ' - ' . $creditSalesValue->credit_reference . '</label></div>';
					$creditSalesStr .= '<input type="hidden" name="credit_sales_id_' . $i . '" value="' . $creditSalesValue->credit_sales_id . '">';
					$i++;
				}
				if (isset($credit_sel_data) && count($credit_sel_data) > 0 && !empty($credit_sel_data)) {
					foreach ($credit_sel_data as $creditSalesValue) {
						$creditSalesStr .= '<div class="checkbox"><label><input ' . $checkedCreditSales . ' type="checkbox" tabindex="' . $creditSalesTabIndex . '" id="credit_sales_chk_' . $i . '" name="credit_sales_chk_' . $i . '" value="' . number_format($creditSalesValue->gross_total, 2, ".", ",") . '" class="checkboxes" checked="checked"> &euro;' . number_format($creditSalesValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($creditSalesValue->sale_date)) . ' - ' . $creditSalesValue->credit_reference . '</label></div>';
						$creditSalesStr .= '<input type="hidden" name="credit_sales_id_' . $i . '" value="' . $creditSalesValue->credit_sales_id . '">';
						$i++;
					}
				}
				$cashSalesData['credit_sales_data'] = $creditSalesStr;
			} else {
				$i = 1;
				$creditSalesStr = '';
				$checkedCreditSales = '';
				if (isset($credit_sel_data) && count($credit_sel_data) > 0 && !empty($credit_sel_data)) {
					foreach ($credit_sel_data as $creditSalesValue) {
						$creditSalesStr .= '<div class="checkbox"><label><input ' . $checkedCreditSales . ' type="checkbox" tabindex="' . $creditSalesTabIndex . '" id="credit_sales_chk_' . $i . '" name="credit_sales_chk_' . $i . '" value="' . number_format($creditSalesValue->gross_total, 2, ".", ",") . '" class="checkboxes" checked="checked" > &euro;' . number_format($creditSalesValue->gross_total, 2, ".", ",") . ' - ' . date('d-m-Y', strtotime($creditSalesValue->sale_date)) . ' - ' . $creditSalesValue->credit_reference . '</label></div>';
						$creditSalesStr .= '<input type="hidden" name="credit_sales_id_' . $i . '" value="' . $creditSalesValue->credit_sales_id . '">';
						$i++;
					}
				}
				$cashSalesData['credit_sales_data'] = $creditSalesStr;
			}

			// Credit Sales Checkboxes [Ends]
		}
		echo json_encode($cashSalesData);
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
		$opsGroupMember = OpsGroup::pluck('ops_name', 'ops_id');

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

		$selectedUserGroups = explode(',', $user->user_group_member);

		// Unit Member Dropdown
		$unitMembers = Unit::pluck('unit_name', 'unit_id');
		$selectedUnitMembers = explode(',', $user->unit_member);

		// Operations Manager Dropdown
		$operationsManager = User::whereRaw('FIND_IN_SET(2, user_group_member)')->select('user_id', 'user_first', 'user_last', 'user_group_member')->get();
		foreach ($operationsManager as $opsMgr) {
			$operationsManagerArr[$opsMgr->user_id] = $opsMgr->user_first . ' ' . $opsMgr->user_last;
		}
		$selectedOperationsManager = explode(',', $user->ops_mgr);

		// Ops Group Member Dropdown
		$opsGroupMember = OpsGroup::pluck('ops_name', 'ops_id');
		$selectedOpsGroupMember = explode(',', $user->ops_group_member);

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
		//$user->username = $request->username;
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
