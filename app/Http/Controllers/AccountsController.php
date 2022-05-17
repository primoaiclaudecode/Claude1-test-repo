<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\UserUnits;
use File;
use Cookie;
use Illuminate\Support\Facades\Gate;
use Session;
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
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\Validator;

class AccountsController extends Controller
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
		$this->middleware('role:hq');
	}

	/**
	 * Statement Check
	 */
	public function statementCheck(Request $request)
	{
		$userId = session()->get('userId');

		// Submit form in the table
		if ($request->has('submit') && $request->submit == 'Submit') {
			$stmntChk = 1;
			$uniqueId = '';
			$frozen = '';

			foreach ($_POST as $key => $val) {
				$findme = '-';
				$pos = strpos($val, $findme);

				if ($pos !== false) {
					$explodeVal = explode('-', $val);
					$uniqueId = $explodeVal[0];
				}

				if (isset($explodeVal[1]) && $explodeVal[1] == 'stmt_chk') {
					$frozen = \DB::statement("UPDATE purchases SET stmnt_chk = '$stmntChk', date_stmnt_chk = NOW(), stmnt_chk_user = $userId, record_status = 'Frozen' WHERE unique_id = '$uniqueId' AND deleted = 0");
				}

				if (isset($explodeVal[1]) && $explodeVal[1] == 'nsr') {
					$frozen = \DB::statement("UPDATE purchases SET stmnt_chk = '$stmntChk', date_stmnt_chk = NOW(), stmnt_chk_user = $userId, record_status = 'Frozen', stmt_ok = 1 WHERE unique_id = '$uniqueId' AND deleted = 0");
				}
			}

			if ($frozen) {
				Session::flash('flash_message', 'Records have been locked successfully!'); //<--FLASH MESSAGE
			}
		}

		// Show data table
		$unitId = $request->input('unit_name', Cookie::get('statementCheckUnitIdCookie', ''));
		$supplier = $request->input('supplier', Cookie::get('statementCheckSupplierCookie', ''));
		$fromDate = $request->input('from_date', Cookie::get('statementCheckFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y')));
		$toDate = $request->input('to_date', Cookie::get('statementCheckToDateCookie', Carbon::now()->format('d-m-Y')));
		$fromDateFormatted = Carbon::parse($fromDate)->format('Y-m-d');
		$toDateFormatted = Carbon::parse($toDate)->format('Y-m-d');

		// Store in cookie
		Cookie::queue('statementCheckUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
		Cookie::queue('statementCheckSupplierCookie', $supplier, time() + (10 * 365 * 24 * 60 * 60));
		Cookie::queue('statementCheckFromDateCookie', $fromDate, time() + (10 * 365 * 24 * 60 * 60));
		Cookie::queue('statementCheckToDateCookie', $toDate, time() + (10 * 365 * 24 * 60 * 60));

		// Get data
		$statementCheckData = \DB::table('purchases AS P')
			->select('P.purchase_id', 'P.unique_id', 'P.purch_type', 'P.supplier', 'P.reference_invoice_number', \DB::raw('DATE_FORMAT(P.receipt_invoice_date,"%d-%m-%Y") receipt_invoice_date'), 'P.purchase_details', 'UN.unit_name', 'P.goods_total', 'P.vat_total', 'P.gross_total')
			->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
			->when($unitId, function ($query) use ($unitId) {
				return $query->where('P.unit_id', $unitId);
			})
			->when($supplier, function ($query) use ($supplier) {
				return $query->where('P.suppliers_id', $supplier);
			})
			->whereBetween('P.receipt_invoice_date', [$fromDateFormatted, $toDateFormatted])
			->where('P.record_status', 'Ok')
			->where('P.purch_type', 'credit')
			->where('P.deleted', 0)
			->groupBy('P.unique_id')
			->orderBy('UN.unit_name')
			->orderBy('P.supplier')
			->orderBy('P.receipt_invoice_date')
			->orderBy('P.reference_invoice_number')
			->get();

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		// Get suppliers list
		$unit = Unit::find($unitId);

		$suppliers = Supplier::when($unit, function ($query) use ($unit) {
			return $query->whereIn('suppliers_id', explode(',', $unit->unitsuppliers));
		})->orderBy('supplier_name')->pluck('supplier_name', 'suppliers_id');

		return view(
			'accounts.statement-check.index', [
				'userUnits' => Gate::allows('hq-user-group') ? ['' => 'Select All'] + $userUnits->toArray() : $userUnits->toArray(),
				'selectedUnit' => $unitId,
				'suppliers' => ['' => 'All'] + $suppliers->toArray(),
				'selectedSupplier' => $supplier,
				'fromDate' => $fromDate,
				'toDate' => $toDate,
				'statementCheckData' => $statementCheckData,
				'totalGoods' => 0,
				'totalVat' => 0,
				'totalGross' => 0,
				'backUrl' => session()->get('backUrl', '/')
			]
		);
	}

	public function supplierJson(Request $request)
	{
		$unit = Unit::find($request->unit_id);

		$suppliers = Supplier::when($unit, function ($query) use ($unit) {
			return $query->whereIn('suppliers_id', explode(',', $unit->unitsuppliers));
		})->orderBy('supplier_name')->get(['supplier_name', 'suppliers_id']);

		return response()->json($suppliers);
	}

	/**
	 * Batch Suppliers Invoice Report
	 */
	public function bsiReport(Request $request)
	{
		$userId = session()->get('userId');

		\DB::table('bsi_report')->where('user_id', '=', $userId)->delete();

		if ($request->has('submit') && $request->submit == 'Get Report') {
			if ($request->has('unit_name'))
				$unitId = $request->unit_name;
			elseif (isset($request->unit_name) && empty($request->unit_name))
				$unitId = '';
			else
				$unitId = '';

			if ($request->has('supplier'))
				$supplier = $request->supplier;
			elseif (isset($request->supplier) && empty($request->supplier))
				$supplier = '';
			else
				$supplier = '';

			// Store in cookie
			if ($request->has('unit_name') || (isset($request->unit_name) && empty($request->unit_name)))
				Cookie::queue('bsiReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));

			if ($request->has('supplier') || (isset($request->supplier) && empty($request->supplier)))
				Cookie::queue('bsiReportSupplierCookie', $supplier, time() + (10 * 365 * 24 * 60 * 60));

			if ($request->has('from_date')) {
				$fromDate = $request->from_date;
				Cookie::queue('bsiReportFromDateCookie', $fromDate, time() + (10 * 365 * 24 * 60 * 60));
			}

			if ($request->has('to_date')) {
				$toDate = $request->to_date;
				Cookie::queue('bsiReportToDateCookie', $toDate, time() + (10 * 365 * 24 * 60 * 60));
			}

			$fromDate = $request->from_date;
			$fromDateFormatted = Carbon::createFromFormat('d-m-Y', $fromDate)->format('Y-m-d');

			$toDate = $request->to_date;
			$toDateFormatted = Carbon::createFromFormat('d-m-Y', $toDate)->format('Y-m-d');

			$bsiReportData = \DB::table('purchases AS P')
				->select('P.purchase_id', 'P.unique_id', 'P.purch_type', 'P.supplier', 'P.reference_invoice_number', \DB::raw('DATE_FORMAT(P.receipt_invoice_date,"%d-%m-%Y") receipt_invoice_date'), 'P.purchase_details', 'UN.unit_name', 'P.goods_total', 'P.vat_total', 'P.gross_total', 'P.net_ext_ID', 'P.unit_id', 'P.suppliers_id', 'P.tax', 'P.vat', 'P.gross', 'P.goods', 'P.tax_code_title')
				->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
				->when($unitId, function ($query) use ($unitId) {
					return $query->where('P.unit_id', $unitId);
				})
				->when($supplier, function ($query) use ($supplier) {
					return $query->where('P.suppliers_id', $supplier);
				})
				->when(isset($fromDate) && $fromDate != '', function ($query) use ($fromDateFormatted, $toDateFormatted) {
					return $query->whereBetween('P.receipt_invoice_date', [$fromDateFormatted, $toDateFormatted]);
				})
				->where('P.sage_imp', 0)
				->where('P.stmnt_chk', 1)
				->where('P.purch_type', 'credit')
				->get();

			foreach ($bsiReportData as $bsirKey => $bsirValue) {
				$netExtIdBsi = $bsirValue->net_ext_ID;
				$purchaseIdBsi = $bsirValue->purchase_id;
				$unitNameBsi = $bsirValue->unit_name;
				$supplierBsi = $bsirValue->supplier;
				$invoiceDateBsi = Carbon::createFromFormat('d-m-Y', $bsirValue->receipt_invoice_date)->format('Y-m-d');
				$invoiceNumberBsi = $bsirValue->reference_invoice_number;
				$purchaseDetailsBsi = $bsirValue->purchase_details;
				$unitIdBsi = $bsirValue->unit_id;
				$supplierIdBsi = $bsirValue->suppliers_id;
				$taxBsi = $bsirValue->tax_code_title;

				$sageAccountNumberData = \DB::table('suppliers')->select('sage_account_number')->where('suppliers_id', $supplierIdBsi)->first();
				$sageAccountNumber = $sageAccountNumberData->sage_account_number;

				$netExtData = \DB::table('nominal_codes')->select('net_ext', 'nominal_code')->where('net_ext_ID', $netExtIdBsi)->first();
				$netExtBsi = $netExtData->net_ext;
				$nominalCodeBsi = $netExtData->nominal_code;
				$netBsi = $bsirValue->goods;
				$taxCodeTitle = $taxBsi;
				$vatBsi = $bsirValue->vat;
				$grossBsi = $bsirValue->gross;
				$transactionType = ($vatBsi >= 0 && $grossBsi >= 0) ? 'PI' : 'PC';

				\DB::table('bsi_report')->insert
				(
					[
						'supplier' => $supplierBsi, 'invoice_date' => $invoiceDateBsi, 'invoice_number' => $invoiceNumberBsi, 'nom_code' => $nominalCodeBsi,
						'net_ext' => $netExtBsi, 'purchase_details' => $purchaseDetailsBsi, 'net' => $netBsi, 'tax_code' => $taxCodeTitle, 'vat' => $vatBsi,
						'gross' => $grossBsi, 'user_id' => $userId, 'sage_account_number' => $sageAccountNumber, 'transaction_type' => $transactionType,
						'unit_name' => $unitNameBsi, 'id' => $purchaseIdBsi, 'unit_id' => $unitIdBsi, 'net_ext_ID' => $netExtIdBsi
					]
				);
			}

			return redirect('/accounts/bsi-report-grid/');
		}

		$dateStart = new Carbon('first day of last month');
		$dateStartFormatted = $dateStart->format('d-m-Y');
		$dateEnd = new Carbon('last day of last month');
		$dateEndFormatted = $dateEnd->format('d-m-Y');

		$unitIdCookie = Cookie::get('bsiReportUnitIdCookie', '');
		$supplierCookie = Cookie::get('bsiReportSupplierCookie', '');
		$fromDateCookie = Cookie::get('bsiReportFromDateCookie', '');
		$toDateCookie = Cookie::get('bsiReportToDateCookie', '');

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		return view(
			'accounts.bsi-report.index', [
				'userUnits' => [0 => 'All'] + $userUnits->toArray(),
				'selectedUnit' => isset($unitIdCookie) ? $unitIdCookie : '',
				'selectedSupplier' => isset($supplierCookie) ? $supplierCookie : '',
				'fromDate' => $fromDateCookie ?: $dateStartFormatted,
				'toDate' => $toDateCookie ?: $dateEndFormatted,
				'backUrl' => session()->get('backUrl', '/')
			]
		);
	}

	public function bsiReportGrid()
	{
		return view(
			'accounts.bsi-report.grid', [
			]
		);
	}

	public function bsiReportGridJson()
	{
		$userId = session()->get('userId');

		$bsiReportData = \DB::table('bsi_report')
			->select(['id', 'unit_name', 'supplier', 'sage_account_number', 'invoice_date', 'invoice_number', 'nom_code', 'net_ext', 'purchase_details', 'net', 'tax_code', 'vat', 'gross', 'transaction_type'])
			->where('user_id', $userId);

		return Datatables::of($bsiReportData)
			->editColumn('invoice_date', function ($bsiReportData) {
				return $bsiReportData->invoice_date ? with(new Carbon($bsiReportData->invoice_date))->format('d-m-Y') : '';
			})
			->filterColumn('invoice_date', function ($query, $keyword) {
				$query->whereRaw("DATE_FORMAT(invoice_date,'%d-%m-%Y') like ?", ["%$keyword%"]);
			})
			->make();
	}

	/**
	 * Sage Confirm
	 */
	public function sageConfirm(Request $request)
	{
		$userId = session()->get('userId');

		if ($request->has('sage_confirm') && $request->sage_confirm == 'Sage Confirm') {
			$ids = \DB::select("SELECT GROUP_CONCAT(id) AS ids FROM bsi_csv WHERE user_id = $userId");
			$idsWithCommas = $ids[0]->ids;
			$update = \DB::statement("UPDATE purchases SET sage_imp = 1 WHERE purchase_id IN ($idsWithCommas)");

			if ($update) {
				Session::flash('flash_message', 'Operation has been successful!'); //<--FLASH MESSAGE
			}

			return redirect('/accounts/sage-confirm-grid/');
		}

		if ($request->has('submit') && $request->submit == 'Submit') {
			$this->validate($request, [
				'csv_file' => 'required|mimes:csv,txt|max:2048'
			]);

			\DB::table('bsi_csv')->where('user_id', '=', $userId)->delete();

			$csvArr = array();

			$uploadDir = 'csv_uploads/';

			if (File::isWritable($uploadDir)) {
				$csvFileName = $request->csv_file->getClientOriginalName();
				$csvFileNameTmp = $request->csv_file->getPathName();
				move_uploaded_file($csvFileNameTmp, $uploadDir . $csvFileName);

				$file = fopen($uploadDir . $csvFileName, "r");

				while (!feof($file)) {
					$csvArr[] = fgetcsv($file);
				}

				fclose($file);

				$csvArrCount = count(array_filter($csvArr));
				$csvArrUpdated = array_splice($csvArr, 1, $csvArrCount - 1);

				foreach ($csvArrUpdated as $csvKey => $csvValue) {
					$id = $csvValue[0];
					$unitName = $csvValue[1];
					$supplier = $csvValue[2];
					$sageAccountNumber = $csvValue[3];
					$invoiceDate = date('Y-m-d', strtotime($csvValue[4]));
					$invoiceNumber = $csvValue[5];
					$nomCode = $csvValue[6];
					$netExt = $csvValue[7];
					$purchaseDetails = $csvValue[8];
					$net = $csvValue[9];
					$taxCode = $csvValue[10];
					$vat = $csvValue[11];
					$gross = $csvValue[12];
					$transactionType = $csvValue[13];

					if ($taxCode != 'Tax Code') {
						\DB::table('bsi_csv')->insert
						(
							[
								'supplier' => $supplier, 'invoice_date' => $invoiceDate, 'invoice_number' => $invoiceNumber, 'nom_code' => $nomCode,
								'net_ext' => $netExt, 'purchase_details' => $purchaseDetails, 'net' => $net, 'tax_code' => $taxCode, 'vat' => $vat,
								'gross' => $gross, 'user_id' => $userId, 'sage_account_number' => $sageAccountNumber, 'transaction_type' => $transactionType,
								'unit_name' => $unitName, 'id' => $id
							]
						);
					}
				}

			}

			$bsiData = \DB::table('bsi_csv')->select('supplier')->where('user_id', $userId)->first();

			if ($bsiData->supplier) {
				return redirect('/accounts/sage-confirm-grid/');
			} else {
				Session::flash('flash_message', 'There are no records to display in grid.'); //<--FLASH MESSAGE
			}
		}

		return view('accounts.sage-confirm.index',
			[
				'backUrl' => session()->get('backUrl', '/')
			]
		);
	}

	public function sageConfirmGrid()
	{
		return view(
			'accounts.sage-confirm.grid', [
			]
		);
	}

	public function sageConfirmGridJson()
	{
		$userId = session()->get('userId');

		$bsiReportData = \DB::table('bsi_csv')
			->select(['unit_name', 'supplier', 'sage_account_number', 'invoice_date', 'invoice_number', 'nom_code', 'net_ext', 'purchase_details', 'net', 'tax_code', 'vat', 'gross', 'transaction_type'])
			->where('user_id', $userId);

		return Datatables::of($bsiReportData)
			->editColumn('invoice_date', function ($bsiReportData) {
				return $bsiReportData->invoice_date ? with(new Carbon($bsiReportData->invoice_date))->format('d-m-Y') : '';
			})
			->filterColumn('invoice_date', function ($query, $keyword) {
				$query->whereRaw("DATE_FORMAT(invoice_date,'%d-%m-%Y') like ?", ["%$keyword%"]);
			})
			->make();
	}

	/**
	 * Unit Month End Closing
	 */
	public function unitMonthEndClosing(Request $request)
	{
		$selectedUnit = $request->input('unit_id', Cookie::get('unitIdCookieLabourHoursSheet', ''));
		$unitName = $request->unit_name;
		$supervisor = $request->input('supervisor', session()->get('userName', ''));

		// Get list of units for current user level
		$userUnits = $this->getUserUnits(true)->pluck('unit_name', 'unit_id');

		return view(
			'accounts.unit-month-end-closing.index', [
				'todayDate' => Carbon::now()->format('d-m-Y'),
				'userUnits' => [0 => 'All'] + $userUnits->toArray(),
				'selectedUnit' => $selectedUnit,
				'unitName' => $unitName,
				'supervisor' => $supervisor,
				'month' => $request->month,
				'year' => $request->year,
				'isSuLevel' => Gate::allows('su-user-group')
			]
		);
	}

	public function unitMonthEndClosingConfimation(Request $request)
	{
		$userId = session()->get('userId');
		$userName = session()->get('userName');
		$confStr = '';
		$month = '';
		$year = '';
		$unitId = 0;
		$unitName = '';

		if ($request->has('unit_name') && $request->unit_name > 0)
			$unitData = \DB::select("SELECT unit_id, unit_name FROM units WHERE unit_id = $request->unit_name");

		if (isset($unitData)) {
			$unitId = $unitData[0]->unit_id;
			$unitName = $unitData[0]->unit_name;
		}

		switch ($request->month) {
			case '1':
				$month = 'January';
				break;
			case '2':
				$month = 'February';
				break;
			case '3':
				$month = 'March';
				break;
			case '4':
				$month = 'April';
				break;
			case '5':
				$month = 'May';
				break;
			case '6':
				$month = 'June';
				break;
			case '7':
				$month = 'July';
				break;
			case '8':
				$month = 'August';
				break;
			case '9':
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

		if ($request->has('unit_name') && $request->unit_name > 0) {
			if ($request->has('submit') && $request->submit == 'Un-close') {
				$confStr .= "<p>You are about to reverse a \"Month-End-Close\" for <strong>" . $unitName . "</strong> for the Period: <strong>$month</strong> / <strong>" . $request->year . "</strong>. Do you wish to proceed?</p>";
				$confStr .= "<input type='hidden' name='month' value='" . $request->month . "' />";
				$confStr .= "<input type='hidden' name='year' value='" . $request->year . "' />";
				$confStr .= "<input type='hidden' name='unit_id' value='$unitId' />";
				$confStr .= "<input type='submit' class='btn btn-primary btn-md' name='submit' value='Confirm' />";
			} elseif ($request->has('submit') && $request->submit == 'Submit') {
				$confStr .= "<p>You are about to freeze the Unit: <strong>" . $unitName . "</strong> for the Period: <strong>$month</strong> / <strong>" . $request->year . "</strong>. This process cannot be undone. Do you wish to proceed?</p>";
				$confStr .= "<input type='hidden' name='month' value='" . $request->month . "' />";
				$confStr .= "<input type='hidden' name='year' value='" . $request->year . "' />";
				$confStr .= "<input type='hidden' name='unit_id' value='$unitId' />";
				$confStr .= "<input type='hidden' name='confirm_btn' value='Confirm Freeze Unit / Month' />";
				$confStr .= "<input type='submit' class='btn btn-primary btn-md' name='submit' value='Confirm' />";
			}
		} else {
			if ($request->has('submit') && $request->submit == 'Un-close') {
				$confStr .= "<p>You are about to reverse a \"Month-End-Close\" for <strong>All Units</strong> for the Period: <strong>$month</strong> / <strong>" . $request->year . "</strong>. Do you wish to proceed?</p>";
				$confStr .= "<input type='hidden' name='month' value='" . $request->month . "' />";
				$confStr .= "<input type='hidden' name='year' value='" . $request->year . "' />";
				$confStr .= "<input type='hidden' name='unit_id' value='0' />";
				$confStr .= "<input type='submit' class='btn btn-primary btn-md' name='submit' value='Confirm' />";
			} elseif ($request->has('submit') && $request->submit == 'Submit') {
				$confStr .= "<p>You are about to freeze <strong>All Units</strong> for the Period: <strong>$month</strong> / <strong>" . $request->year . "</strong>. This process cannot be undone. Do you wish to proceed?</p>";
				$confStr .= "<input type='hidden' name='month' value='" . $request->month . "' />";
				$confStr .= "<input type='hidden' name='year' value='" . $request->year . "' />";
				$confStr .= "<input type='hidden' name='unit_id' value='0' />";
				$confStr .= "<input type='hidden' name='confirm_btn' value='Confirm Freeze Unit / Month' />";
				$confStr .= "<input type='submit' class='btn btn-primary btn-md' name='submit' value='Confirm' />";
			}
		}

		return view(
			'accounts.unit-month-end-closing.confirmation', [
				'userId' => $userId,
				'userName' => $userName,
				'unitId' => $unitId,
				'unitName' => $unitName,
				'confStr' => $confStr,
				'month' => $request->month,
				'year' => $request->year,
				'supervisor' => $request->supervisor
			]
		);
	}

	public function unitMonthEndClosingPost(Request $request)
	{
		$month = str_pad($request->month, 2, "0", STR_PAD_LEFT);
		$year = $request->year;
		$startDate = $year . '-' . $month . '-01';
		$endDate = $year . '-' . $month . '-' . cal_days_in_month(CAL_GREGORIAN, $month, $year);
		$unitArr = array();
		$isClosed = 0;
		$closedUnitErrorMsg = '';
		$userID = $request->session()->get('userId');

		if ($request->has('confirm_btn') && $request->confirm_btn == 'Confirm Freeze Unit / Month') {
			$isClosed = 1;
		}
		if ($request->unit_id == 0) {
			$unitList = Unit::getUnitList();
			if (!empty($unitList) && count($unitList) > 0) {
				foreach ($unitList as $key => $value) {
					$purchasesData = Purchase:: getPurchasesSales($month, $year, $key);
					$cashSalesData = Purchase:: getCashSales($month, $year, $key);
					$creditSalesData = Purchase:: getCreditSales($month, $year, $key);
					$vendingSalesData = Purchase:: getVendingSales($month, $year, $key);
					$totalNet = 0;
					$totalVat = 0;
					$totalGross = 0;
					$totalSales = 0;
					$cashCreditCard = 0;
					$creditSalesGoodsTotal = 0;
					$vendingSalesTotal = 0;

					if (!empty($purchasesData) && count($purchasesData) > 0) {
						$totalNet = number_format(@$purchasesData[0]->total_goods, 2, '.', '');
						$totalVat = number_format(@$purchasesData[0]->total_vat, 2, '.', '');
						$totalGross = number_format(@$purchasesData[0]->total_gross, 2, '.', '');

						if (!empty($cashSalesData) && count($cashSalesData) > 0) {
							$cashCreditCard = @$cashSalesData[0]->cash_credit_card;
						}

						if (!empty($creditSalesData) && count($creditSalesData) > 0) {
							$creditSalesGoodsTotal = @$creditSalesData[0]->goods_total;
						}

						if (!empty($vendingSalesData) && count($vendingSalesData) > 0) {
							$vendingSalesTotal = @$vendingSalesData[0]->total;
						}

						$totalSales = number_format($cashCreditCard + $creditSalesGoodsTotal + $vendingSalesTotal, 2, '.', '');
					}
					$unitClosedData = array(
						'date' => $startDate,
						'user_id' => $userID,
						'unit_id' => $key,
						'month' => $month,
						'year' => $year,
						'closed' => $isClosed,
						'purch_net' => $totalNet,
						'purch_vat' => $totalVat,
						'purch_gross' => $totalGross,
						'total_sales' => $totalSales,
						'modified_by' => $userID,
						'is_deleted' => 0,
						'unit_all' => 1,
						'close_date' => $endDate,
					);
					$unitCloseID = '';
					$unitClosedDataID = UnitClosed:: getUnitClosedByID($key, $month, $year);

					if (!is_null($unitClosedDataID)) {
						$unitCloseID = $unitClosedDataID->id;
					}

					UnitClosed:: storeData($unitClosedData, $unitCloseID);

					if ($isClosed == 1) {
						\DB::statement("UPDATE credit_sales SET closed = '1',cash_sale_vis=0 WHERE unit_id = '" . $key . "' and sale_date >= '" . $startDate . "' and sale_date <= '" . $endDate . "' ");

						\DB::statement("UPDATE cash_sales SET closed = '1' WHERE unit_id = '" . $key . "' and sale_date >= '" . $startDate . "' and sale_date <= '" . $endDate . "' ");

						\DB::statement("UPDATE purchases SET closed = 1,cash_sale_vis=0 WHERE unit_id = '" . $key . "' and receipt_invoice_date >= '" . $startDate . "' and receipt_invoice_date <= '" . $endDate . "' and purch_type='cash'");
					}
				}
			}
		} else {
			$purchasesData = Purchase:: getPurchasesSales($month, $year, $request->unit_id);
			$cashSalesData = Purchase:: getCashSales($month, $year, $request->unit_id);
			$creditSalesData = Purchase:: getCreditSales($month, $year, $request->unit_id);
			$vendingSalesData = Purchase:: getVendingSales($month, $year, $request->unit_id);
			$totalNet = 0;
			$totalVat = 0;
			$totalGross = 0;
			$totalSales = 0;
			$cashCreditCard = 0;
			$creditSalesGoodsTotal = 0;
			$vendingSalesTotal = 0;

			if (!empty($purchasesData) && count($purchasesData) > 0) {
				$totalNet = number_format(@$purchasesData[0]->total_goods, 2, '.', '');
				$totalVat = number_format(@$purchasesData[0]->total_vat, 2, '.', '');
				$totalGross = number_format(@$purchasesData[0]->total_gross, 2, '.', '');

				if (!empty($cashSalesData) && count($cashSalesData) > 0) {
					$cashCreditCard = @$cashSalesData[0]->cash_credit_card;
				}

				if (!empty($creditSalesData) && count($creditSalesData) > 0) {
					$creditSalesGoodsTotal = @$creditSalesData[0]->goods_total;
				}

				if (!empty($vendingSalesData) && count($vendingSalesData) > 0) {
					$vendingSalesTotal = @$vendingSalesData[0]->total;
				}

				$totalSales = number_format($cashCreditCard + $creditSalesGoodsTotal + $vendingSalesTotal, 2, '.', '');
			}

			$unitClosedData = array(
				'date' => $startDate,
				'user_id' => $userID,
				'unit_id' => $request->unit_id,
				'month' => $month,
				'year' => $year,
				'closed' => $isClosed,
				'purch_net' => $totalNet,
				'purch_vat' => $totalVat,
				'purch_gross' => $totalGross,
				'total_sales' => $totalSales,
				'modified_by' => $userID,
				'is_deleted' => 0,
				'close_date' => $endDate,
			);

			$unitCloseID = '';
			$unitClosedDataID = UnitClosed:: getUnitClosedByID($request->unit_id, $month, $year);

			if (!is_null($unitClosedDataID)) {
				$unitCloseID = $unitClosedDataID->id;
			}

			UnitClosed:: storeData($unitClosedData, $unitCloseID);

			/////////////////// Update Table Data For Relative Table
			if ($isClosed == 1) {
				\DB::statement("UPDATE credit_sales SET closed = '1',cash_sale_vis=0 WHERE unit_id = '" . $request->unit_id . "' and sale_date >= '" . $startDate . "' and sale_date <= '" . $endDate . "' ");

				\DB::statement("UPDATE cash_sales SET closed = '1' WHERE unit_id = '" . $request->unit_id . "' and sale_date >= '" . $startDate . "' and sale_date <= '" . $endDate . "' ");

				\DB::statement("UPDATE purchases SET closed = 1,cash_sale_vis=0 WHERE unit_id = '" . $request->unit_id . "' and receipt_invoice_date >= '" . $startDate . "' and receipt_invoice_date <= '" . $endDate . "' and purch_type='cash'");
			}
		}

		Session::flash('flash_message', 'The form was successfully completed!'); //<--FLASH MESSAGE

		return redirect('/accounts/unit-month-end-closing/');
	}

	// Unit close check
	public function purchasesSalesUnitCloseJson(Request $request)
	{
		$unitArr = array();
		$isClosed = 0;
		$totalNet = 0;
		$totalVat = 0;
		$totalGross = 0;
		$closedUnitErrorMsg = '';
		$month1 = str_pad($request->month, 2, "0", STR_PAD_LEFT);
		$year = $request->year;
		$unit_id = $request->unit_name;

		$cusMonth = str_pad($request->month, 2, "0", STR_PAD_LEFT);
		$startDate = date("$request->year-$cusMonth-01");
		$endDate = date('Y-m-t', strtotime($startDate));

		// Total Net, Total VAT, Total Gross Calculations
		if ($request->has('unit_name') && $request->unit_name > 0) {
			$purchasesData = \DB::select("SELECT SUM(goods) AS total_goods, SUM(vat) AS total_vat, SUM(gross) AS total_gross
                    FROM `purchases`
                    WHERE  receipt_invoice_date >= '" . $startDate . "' and receipt_invoice_date <= '" . $endDate . "'  AND unit_id='" . $request->unit_name . "'");
		} else {
			$purchasesData = \DB::select("SELECT SUM(goods) AS total_goods, SUM(vat) AS total_vat, SUM(gross) AS total_gross
                    FROM `purchases`
                    WHERE  receipt_invoice_date >= '" . $startDate . "' and receipt_invoice_date <= '" . $endDate . "'");
		}

		if ($purchasesData) {
			$total_goods = 0;
			$total_vat = 0;
			$total_gross = 0;
			if (count($purchasesData) > 0 && !empty($purchasesData)) {
				foreach ($purchasesData as $pValue) {
					$total_goods += $pValue->total_goods;
					$total_vat += $pValue->total_vat;
					$total_gross += $pValue->total_gross;
				}
			}
			$totalNet = number_format($total_goods, 2, '.', '');
			$totalVat = number_format($total_vat, 2, '.', '');
			$totalGross = number_format($total_gross, 2, '.', '');
		}

		if ($request->has('unit_name') && $request->unit_name > 0) {
			$cashSalesData = \DB::select(
				"SELECT SUM(cash_credit_card) AS cash_credit_card
                    FROM `cash_sales`
                    WHERE EXTRACT( MONTH FROM sale_date ) = '" . $request->month . "'
                    AND EXTRACT( YEAR FROM sale_date ) = '" . $request->year . "'
                    AND unit_id='" . $request->unit_name . "'"
			);
		} else {
			$cashSalesData = \DB::select(
				"SELECT SUM(cash_credit_card) AS cash_credit_card
                    FROM `cash_sales`
                    WHERE EXTRACT( MONTH FROM sale_date ) = '" . $request->month . "'
                    AND EXTRACT( YEAR FROM sale_date ) = '" . $request->year . "'"
			);
		}

		$cashCreditCard = $cashSalesData[0]->cash_credit_card;

		if ($request->has('unit_name') && $request->unit_name > 0) {
			$creditSalesData = \DB::select(
				"SELECT SUM( goods_total ) AS goods_total
                    FROM `credit_sales`
                    WHERE EXTRACT( MONTH FROM sale_date ) = '" . $request->month . "'
                    AND EXTRACT( YEAR FROM sale_date ) = '" . $request->year . "'
                    AND unit_id='" . $request->unit_name . "'"
			);
		} else {
			$creditSalesData = \DB::select(
				"SELECT SUM( goods_total ) AS goods_total
                    FROM `credit_sales`
                    WHERE EXTRACT( MONTH FROM sale_date ) = '" . $request->month . "'
                    AND EXTRACT( YEAR FROM sale_date ) = '" . $request->year . "'"
			);
		}

		$creditSalesGoodsTotal = $creditSalesData[0]->goods_total;

		if ($request->has('unit_name') && $request->unit_name > 0) {
			$vendingSalesData = \DB::select(
				"SELECT SUM( total ) AS total
                    FROM `vending_sales`
                    WHERE EXTRACT( MONTH FROM sale_date ) = '" . $request->month . "'
                    AND EXTRACT( YEAR FROM sale_date ) = '" . $request->year . "'
                    AND unit_id='" . $request->unit_name . "'"
			);
		} else {
			$vendingSalesData = \DB::select(
				"SELECT SUM( total ) AS total
                    FROM `vending_sales`
                    WHERE EXTRACT( MONTH FROM sale_date ) = '" . $request->month . "'
                    AND EXTRACT( YEAR FROM sale_date ) = '" . $request->year . "'"
			);
		}

		$vendingSalesTotal = $vendingSalesData[0]->total;

		$totalSales = number_format($cashCreditCard + $creditSalesGoodsTotal + $vendingSalesTotal, 2, '.', '');

		if ($request->has('unit_name') && $request->unit_name > 0) {
			$unitCloseData = UnitClosed::getUnitClosedByID($unit_id, $month1, $year);
		} else {
			$unitCloseData = UnitClosed::getUnitClosedByAll(1, $month1, $year);
		}

		if (!is_null($unitCloseData) && $unitCloseData->closed == 1) {
			$isClosed = 1;
		}

		switch ($request->month) {
			case '1':
				$month = 'January';
				break;
			case '2':
				$month = 'February';
				break;
			case '3':
				$month = 'March';
				break;
			case '4':
				$month = 'April';
				break;
			case '5':
				$month = 'May';
				break;
			case '6':
				$month = 'June';
				break;
			case '7':
				$month = 'July';
				break;
			case '8':
				$month = 'August';
				break;
			case '9':
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

		if ($request->has('unit_name') && $request->unit_name > 0 && $isClosed == 1) {
			$closedUnitErrorMsg = "This unit has been closed for <strong>$month</strong> / <strong>" . $request->year . "</strong>.";
		} elseif ($request->has('unit_name') && $request->unit_name == 0 && $isClosed == 1) {
			$closedUnitErrorMsg = "All the units have been closed for <strong>$month</strong> / <strong>" . $request->year . "</strong>.";
		}

		$unitArr = array('total_net' => $totalNet, 'total_vat' => $totalVat, 'total_gross' => $totalGross, 'total_sales' => $totalSales, 'closed_unit_error_msg' => $closedUnitErrorMsg);

		echo json_encode($unitArr);
	}
}