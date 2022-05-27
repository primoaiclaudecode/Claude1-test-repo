<?php

namespace App\Http\Controllers;

use App\ContactType;
use App\ContractType;
use App\CreditSaleGood;
use App\Currency;
use App\CreditSales;
use App\CustomerFeedback;
use App\Event;
use App\FeedbackType;
use App\Http\Controllers\Traits\UserUnits;
use App\Lodgement;
use App\LodgementCost;
use App\OperationsScorecard;
use App\PhasedBudgetUnitRow;
use App\Problem;
use App\ProblemType;
use App\Region;
use App\ReportHiddenColumn;
use App\Status;
use App\TradingAccount;
use App\VendingSaleGood;
use App\VendingSales;
use Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Session;
use App\User;
use App\Unit;
use App\TaxCode;
use App\Purchase;
use App\CashSales;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class ReportController extends Controller
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
        $this->middleware('role:management')->only('operationsScorecard', 'operationsScorecardGrid');
        $this->middleware('role:hq')->only('purchasesSummary', 'purchasesSummaryGrid');
        $this->middleware('role:unit');
    }

    public function toggleColumnVisibility(Request $request)
    {
        $userId = session()->get('userId');

        $columnVisibility = ReportHiddenColumn::where('user_id', $userId)
            ->where('report_name', $request->report_name)
            ->where('column_index', $request->column_index)
            ->first();

        if (!is_null($columnVisibility)) {
            $columnVisibility->delete();
        } else {
            ReportHiddenColumn::create(
                [
                    'user_id'      => $userId,
                    'report_name'  => $request->report_name,
                    'column_index' => $request->column_index,
                ]
            );
        }
    }

    /**
     * Cash / Credit Purchases Report.
     */
    public function purchases()
    {
        $unitId = Cookie::get('purchasesReportUnitIdCookie', '');
        $fromDate = Cookie::get('purchasesReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('purchasesReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.purchases.index', [
                'userUnits'    => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function purchasesGrid(Request $request, $sheetId = null)
    {
        $userId = session()->get('userId');
        $unitId = $request->input('unit_name', is_numeric($request->unit_id) ? $request->unit_id : '');
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Track action
        Event::trackAction('Run Purchases Report');

        // Store in cookie
        Cookie::queue('purchasesReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'purchases')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        // Currency symbol
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();

        $currencySymbol = $defaultCurrency->currency_symbol;
        if (!is_null($unit) && !is_null($unit->currency)) {
            $currencySymbol = $unit->currency->currency_symbol;
        }

        return view(
            'reports.purchases.grid', [
                'userId'         => $userId,
                'unitId'         => $unitId,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'allRecords'     => $allRecords,
                'sheetId'        => $sheetId,
                'notVisiable'    => $hiddenColumns,
                'isSuLevel'      => Gate::allows('su-user-group'),
                'isHqLevel'      => Gate::allows('hq-user-group'),
                'currencySymbol' => $currencySymbol,
            ]
        );
    }

    public function purchasesGridJson(Request $request)
    {
        if ($request->has('sheet_id')) {
            \DB::statement('UPDATE purchases SET stmnt_chk = 0, date_stmnt_chk = "", stmnt_chk_user = 0, record_status = "Ok", stmt_ok = 0 WHERE unique_id = "' . $request->sheet_id . '"');
        }

        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Units
        $userUnits = $this->getUserUnits(false)->pluck('unit_id');

        // Currency
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();
        $unitCurrency = !is_null($unit) ? $unit->currency_id : $defaultCurrency->currency_id;

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.deleted', 0);
            } else {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.deleted', 0);
            } else {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $purchases = \DB::table('purchases AS P')
                ->select(
                    [
                        'P.purchase_id',
                        'P.unique_id',
                        'P.purch_type',
                        'UN.unit_name',
                        'P.supplier',
                        'P.supervisor',
                        'P.reference_invoice_number',
                        'P.receipt_invoice_date',
                        'P.purchase_details',
                        'N.net_ext',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'P.goods',
                        'P.vat',
                        'P.gross',
                        'TC.tax_rate',
                        'TC.tax_code_display_rate',
                        'P.goods_total',
                        'P.vat_total',
                        'P.gross_total',
                        'P.record_status',
                        'P.time_inserted',
                        'P.time_updated',
                        'UU.username as updated_by',
                        'P.stmt_ok',
                        'P.stmnt_chk',
                        'U.username as stmnt_chk_user',
                        'P.date_stmnt_chk',
                        'P.purchase_id',
                    ]
                )
                ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'P.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = P.date');
                })
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                ->where('P.deleted', 0);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $purchases = \DB::table('purchases AS P')
                ->select(
                    [
                        'P.purchase_id',
                        'P.unique_id',
                        'P.purch_type',
                        'UN.unit_name',
                        'P.supplier',
                        'P.supervisor',
                        'P.reference_invoice_number',
                        'P.receipt_invoice_date',
                        'P.purchase_details',
                        'N.net_ext',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'P.goods',
                        'P.vat',
                        'P.gross',
                        'TC.tax_rate',
                        'TC.tax_code_display_rate',
                        'P.goods_total',
                        'P.vat_total',
                        'P.gross_total',
                        'P.record_status',
                        'P.time_inserted',
                        'P.time_updated',
                        'UU.username as updated_by',
                        'P.stmt_ok',
                        'P.stmnt_chk',
                        'U.username as stmnt_chk_user',
                        'P.date_stmnt_chk',
                        'P.purchase_id',
                    ]
                )
                ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'P.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = P.date');
                })
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                ->where('P.deleted', 0);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->where('P.deleted', 0);
            } else {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.foreign_currency_id', '=', 'P.currency_id')
                            ->where('ER.domestic_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->where('P.deleted', 0);
            } else {
                $purchases = \DB::table('purchases AS P')
                    ->select(
                        [
                            'P.purchase_id',
                            'P.unique_id',
                            'P.purch_type',
                            'UN.unit_name',
                            'P.supplier',
                            'P.supervisor',
                            'P.reference_invoice_number',
                            'P.receipt_invoice_date',
                            'P.purchase_details',
                            'N.net_ext',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'P.goods',
                            'P.vat',
                            'P.gross',
                            'TC.tax_rate',
                            'TC.tax_code_display_rate',
                            'P.goods_total',
                            'P.vat_total',
                            'P.gross_total',
                            'P.record_status',
                            'P.time_inserted',
                            'P.time_updated',
                            'UU.username as updated_by',
                            'P.stmt_ok',
                            'P.stmnt_chk',
                            'U.username as stmnt_chk_user',
                            'P.date_stmnt_chk',
                            'P.purchase_id',
                        ]
                    )
                    ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                    ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                    ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'P.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = P.date');
                    })
                    ->where('P.unit_id', $unitId)
                    ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                    ->where('P.deleted', 0);
            }
        } else {
            $purchases = \DB::table('purchases AS P')
                ->select(
                    [
                        'P.purchase_id',
                        'P.unique_id',
                        'P.purch_type',
                        'UN.unit_name',
                        'P.supplier',
                        'P.supervisor',
                        'P.reference_invoice_number',
                        'P.receipt_invoice_date',
                        'P.purchase_details',
                        'N.net_ext',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'P.goods',
                        'P.vat',
                        'P.gross',
                        'TC.tax_rate',
                        'TC.tax_code_display_rate',
                        'P.goods_total',
                        'P.vat_total',
                        'P.gross_total',
                        'P.record_status',
                        'P.time_inserted',
                        'P.time_updated',
                        'UU.username as updated_by',
                        'P.stmt_ok',
                        'P.stmnt_chk',
                        'U.username as stmnt_chk_user',
                        'P.date_stmnt_chk',
                        'P.purchase_id',
                    ]
                )
                ->leftJoin('users AS U', 'P.stmnt_chk_user', '=', 'U.user_id')
                ->leftJoin('users AS UU', 'P.updated_by', '=', 'UU.user_id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->leftJoin('nominal_codes AS N', 'P.net_ext_ID', '=', 'N.net_ext_ID')
                ->leftJoin('tax_codes AS TC', 'TC.tax_code_ID', '=', 'P.tax_code_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'P.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'P.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = P.date');
                })
                ->where('P.unit_id', $unitId)
                ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
                ->where('P.deleted', 0);
        }

        if (Gate::allows('su-user-group')) {
            return Datatables::of($purchases)
                ->setRowId(function ($purchase)
                {
                    return 'tr_' . $purchase->purchase_id;
                })
                ->setRowClass(function ($purchase)
                {
                    if ($purchase->stmt_ok == 1) {
                        return 'orange-row';
                    }

                    if ($purchase->stmnt_chk == 1) {
                        return 'green-row';
                    }

                    if ($purchase->exchange_rate != 1) {
                        return 'blue-row';
                    }

                    return '';
                })
                ->addColumn('checkbox', function ($purchase)
                {
                    return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $purchase->purchase_id . '">';
                }, 0)
                ->addColumn('action', function ($purchase)
                {
                    $purchasesData = \DB::table('purchases')
                        ->select('stmt_ok', 'stmnt_chk')
                        ->where('purchase_id', $purchase->purchase_id)
                        ->first();
                    if ($purchasesData->stmt_ok == 0 && $purchasesData->stmnt_chk == 0) {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    } else {
                        return '-';
                    }
                })
                ->editColumn('unique_id', function ($purchase)
                {
                    if ($purchase->record_status == 'Frozen') {
                        return '<a  href="javascript:void(0);">' . $purchase->unique_id . '</a>';
                    } else {
                        return '<a target="_blank" href="/sheets/purchases/' . $purchase->purch_type . '/' . $purchase->unique_id . '">' . $purchase->unique_id . '</a>';
                    }
                })
                ->editColumn('receipt_invoice_date', function ($purchase)
                {
                    return $purchase->receipt_invoice_date ? with(new Carbon($purchase->receipt_invoice_date))->format('d-m-Y') : '';
                })
                ->editColumn('date_stmnt_chk', function ($purchase)
                {
                    return $purchase->date_stmnt_chk != '0000-00-00 00:00:00' ? Carbon::parse($purchase->date_stmnt_chk)
                        ->format('d-m-Y  H:i:s') : '';
                })
                ->editColumn('time_inserted', function ($purchase)
                {
                    return Carbon::parse($purchase->time_inserted)->format('d-m-Y H:i:s');
                })
                ->editColumn('time_updated', function ($purchase)
                {
                    return !is_null($purchase->time_updated) ? Carbon::parse($purchase->time_updated)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('goods', function ($purchase)
                {
                    return $purchase->goods * $purchase->exchange_rate;
                })
                ->editColumn('vat', function ($purchase)
                {
                    return $purchase->vat * $purchase->exchange_rate;
                })
                ->editColumn('gross', function ($purchase)
                {
                    return $purchase->gross * $purchase->exchange_rate;
                })
                ->editColumn('goods_total', function ($purchase)
                {
                    return $purchase->goods_total * $purchase->exchange_rate;
                })
                ->editColumn('vat_total', function ($purchase)
                {
                    return $purchase->vat_total * $purchase->exchange_rate;
                })
                ->editColumn('gross_total', function ($purchase)
                {
                    return $purchase->gross_total * $purchase->exchange_rate;
                })
                ->filterColumn('P.receipt_invoice_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(receipt_invoice_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.date_stmnt_chk', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.date_stmnt_chk,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_inserted', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_inserted,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_updated', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_updated,'%d-%m-%Y  %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('record_status', function ($purchase)
                {
                    $unitId = Cookie::get('purchasesReportUnitIdCookie') != '' ? Cookie::get('purchasesReportUnitIdCookie') : 'All';
                    $fromDate = Cookie::get('purchasesReportFromDateCookie');
                    $toDate = Cookie::get('purchasesReportToDateCookie');

                    if ($purchase->stmt_ok == 0 && $purchase->stmnt_chk == 0) {
                        return $purchase->record_status;
                    } else {
                        return '<a onclick="return confirm_before_unfreeze()" href="/reports/purchases/grid/' . $purchase->unique_id . '/' . $unitId . '/' . $fromDate . '/' . $toDate . '">' . $purchase->record_status . '</a>';
                    }
                })
                ->make();
        } elseif (Gate::allows('hq-user-group')) {
            return Datatables::of($purchases)
                ->setRowId(function ($purchase)
                {
                    return 'tr_' . $purchase->purchase_id;
                })
                ->setRowClass(function ($purchase)
                {
                    if ($purchase->stmt_ok == 1) {
                        return 'orange-row';
                    }

                    if ($purchase->stmnt_chk == 1) {
                        return 'green-row';
                    }

                    if ($purchase->exchange_rate != 1) {
                        return 'blue-row';
                    }

                    return '';
                })
                ->addColumn('action', function ($purchase)
                {
                    $purchasesData = \DB::table('purchases')
                        ->select('stmt_ok', 'stmnt_chk')
                        ->where('purchase_id', $purchase->purchase_id)
                        ->first();
                    if ($purchasesData->stmt_ok == 0 && $purchasesData->stmnt_chk == 0) {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    } else {
                        return '-';
                    }
                })
                ->editColumn('unique_id', function ($purchase)
                {
                    if ($purchase->record_status == 'Frozen') {
                        return '<a  href="javascript:void(0);">' . $purchase->unique_id . '</a>';
                    } else {
                        return '<a target="_blank" href="/sheets/purchases/' . $purchase->purch_type . '/' . $purchase->unique_id . '">' . $purchase->unique_id . '</a>';
                    }
                })
                ->editColumn('receipt_invoice_date', function ($purchase)
                {
                    return $purchase->receipt_invoice_date ? with(new Carbon($purchase->receipt_invoice_date))->format('d-m-Y') : '';
                })
                ->editColumn('date_stmnt_chk', function ($purchase)
                {
                    return $purchase->date_stmnt_chk != '0000-00-00 00:00:00' ? Carbon::parse($purchase->date_stmnt_chk)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('time_inserted', function ($purchase)
                {
                    return Carbon::parse($purchase->time_inserted)->format('d-m-Y H:i:s');
                })
                ->editColumn('time_updated', function ($purchase)
                {
                    return !is_null($purchase->time_updated) ? Carbon::parse($purchase->time_updated)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('goods', function ($purchase)
                {
                    return $purchase->goods * $purchase->exchange_rate;
                })
                ->editColumn('vat', function ($purchase)
                {
                    return $purchase->vat * $purchase->exchange_rate;
                })
                ->editColumn('gross', function ($purchase)
                {
                    return $purchase->gross * $purchase->exchange_rate;
                })
                ->editColumn('goods_total', function ($purchase)
                {
                    return $purchase->goods_total * $purchase->exchange_rate;
                })
                ->editColumn('vat_total', function ($purchase)
                {
                    return $purchase->vat_total * $purchase->exchange_rate;
                })
                ->editColumn('gross_total', function ($purchase)
                {
                    return $purchase->gross_total * $purchase->exchange_rate;
                })
                ->filterColumn('P.receipt_invoice_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(receipt_invoice_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.date_stmnt_chk', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.date_stmnt_chk,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_inserted', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_inserted,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_updated', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_updated,'%d-%m-%Y  %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->make();
        } else {
            return Datatables::of($purchases)
                ->setRowId(function ($purchase)
                {
                    return 'tr_' . $purchase->purchase_id;
                })
                ->setRowClass(function ($purchase)
                {
                    if ($purchase->stmt_ok == 1) {
                        return 'orange-row';
                    }

                    if ($purchase->stmnt_chk == 1) {
                        return 'green-row';
                    }

                    if ($purchase->exchange_rate != 1) {
                        return 'blue-row';
                    }

                    return '';
                })
                ->editColumn('stmnt_chk_user', function ($purchase)
                {
                    return '';
                })
                ->editColumn('date_stmnt_chk', function ($purchase)
                {
                    return '';
                })
                ->addColumn('action', function ($purchase)
                {
                    $purchasesData = \DB::table('purchases')
                        ->select('stmt_ok', 'stmnt_chk')
                        ->where('purchase_id', $purchase->purchase_id)
                        ->first();
                    if ($purchasesData->stmt_ok == 0 && $purchasesData->stmnt_chk == 0) {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    } else {
                        return '-';
                    }
                })
                ->editColumn('unique_id', function ($purchase)
                {
                    if ($purchase->record_status == 'Frozen') {
                        return '<a  href="javascript:void(0);">' . $purchase->unique_id . '</a>';
                    } else {
                        return '<a target="_blank" href="/sheets/purchases/' . $purchase->purch_type . '/' . $purchase->unique_id . '">' . $purchase->unique_id . '</a>';
                    }
                })
                ->editColumn('receipt_invoice_date', function ($purchase)
                {
                    return $purchase->receipt_invoice_date ? with(new Carbon($purchase->receipt_invoice_date))->format('d-m-Y') : '';
                })
                ->editColumn('time_inserted', function ($purchase)
                {
                    return Carbon::parse($purchase->time_inserted)->format('d-m-Y H:i:s');
                })
                ->editColumn('time_updated', function ($purchase)
                {
                    return !is_null($purchase->time_updated) ? Carbon::parse($purchase->time_updated)
                        ->format('d-m-Y H:i:s') : '';
                })
                ->editColumn('goods', function ($purchase)
                {
                    return $purchase->goods * $purchase->exchange_rate;
                })
                ->editColumn('vat', function ($purchase)
                {
                    return $purchase->vat * $purchase->exchange_rate;
                })
                ->editColumn('gross', function ($purchase)
                {
                    return $purchase->gross * $purchase->exchange_rate;
                })
                ->editColumn('goods_total', function ($purchase)
                {
                    return $purchase->goods_total * $purchase->exchange_rate;
                })
                ->editColumn('vat_total', function ($purchase)
                {
                    return $purchase->vat_total * $purchase->exchange_rate;
                })
                ->editColumn('gross_total', function ($purchase)
                {
                    return $purchase->gross_total * $purchase->exchange_rate;
                })
                ->filterColumn('P.receipt_invoice_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.receipt_invoice_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_inserted', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_inserted,'%d-%m-%Y %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('P.time_updated', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(P.time_updated,'%d-%m-%Y  %H:%i:%s') like ?", [ "%$keyword%" ]);
                })
                ->make();
        }
    }

    public function deletePurchasesRecord($id)
    {
        $purchasesIds = explode(',', $id);

        Purchase::whereIn('purchase_id', $purchasesIds)
            ->update(
                [
                    'deleted' => 1,
                ]
            );

        echo $id;
    }

    public function deletePurchasesSheetRecord($id)
    {
        $purchasesIds = explode(',', $id);

        foreach ($purchasesIds as $purchasesId) {
            $purchase = Purchase::find($purchasesId);

            Purchase::where('unique_id', $purchase->unique_id)
                ->update(
                    [
                        'deleted' => 1,
                    ]
                );
        }

        echo $id;
    }

    /**
     * Sales Summary Report.
     */
    public function salesSummary()
    {
        $unitId = Cookie::get('salesSummaryReportUnitIdCookie', '');
        $fromDate = Cookie::get('salesSummaryReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('salesSummaryReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.sales-summary.index', [
                'userUnits'    => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function salesSummaryGrid(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_id;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Store in cookie
        Cookie::queue('salesSummaryReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('salesSummaryReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('salesSummaryReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'sales_summary')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        // Goods columns
        $taxCodes = TaxCode::with('vendingSaleTaxCodes')->where('vending_sales', 1)->get();

        $goods = [];
        foreach ($taxCodes as $taxCode) {
            foreach ($taxCode->vendingSaleTaxCodes as $netExtItem) {
                $goods[$netExtItem->net_ext_ID] = ucfirst($netExtItem->net_ext);
            }
        }

        $goodColumns = range(22, 22 + count($goods) + 1);

        // Currency symbol
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();

        $currencySymbol = $defaultCurrency->currency_symbol;
        if (!is_null($unit) && !is_null($unit->currency)) {
            $currencySymbol = $unit->currency->currency_symbol;
        }

        return view(
            'reports.sales-summary.grid', [
                'userId'         => $userId,
                'unitId'         => $unitId,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'allRecords'     => $allRecords,
                'notVisiable'    => $hiddenColumns,
                'goods'          => $goods,
                'goodColumns'    => json_encode($goodColumns),
                'currencySymbol' => $currencySymbol,
            ]
        );
    }

    public function salesSummaryGridJson(Request $request)
    {
        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;
        $allRecords = $request->all_records;

        // Currencies
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();
        $unitCurrency = !is_null($unit) ? $unit->currency_id : $defaultCurrency->currency_id;

        $sales = DB::table('summary_sales_report as ss')
            ->select([
                'ss.sale_type',
                'ss.entry_date',
                'ss.unit_name',
                'ss.supervisor',
                'ss.reg_number',
                'ss.machine_name',
                'ss.sale_date',
                'ss.z_number',
                'c.currency_code',
                'er.exchange_rate',
                DB::raw('ss.z_food * er.exchange_rate as z_food'),
                DB::raw('ss.z_confect_food * er.exchange_rate as z_confect_food'),
                DB::raw('ss.z_fruit * er.exchange_rate as z_fruit'),
                DB::raw('ss.z_minerals * er.exchange_rate as z_minerals'),
                DB::raw('ss.z_confect * er.exchange_rate as z_confect'),
                DB::raw('ss.cash_count * er.exchange_rate as cash_count'),
                DB::raw('ss.credit_card * er.exchange_rate as credit_card'),
                DB::raw('ss.staff_cards * er.exchange_rate as staff_cards'),
                DB::raw('ss.cash_credit_card * er.exchange_rate as cash_credit_card'),
                DB::raw('ss.z_read * er.exchange_rate as z_read'),
                DB::raw('ss.cash_purchase * er.exchange_rate as cash_purchase'),
                'ss.credit_sales_id',
                'ss.id',
            ])
            ->leftJoin('currencies as c', 'c.currency_id', '=', 'ss.currency_id')
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'ss.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = ss.sale_date');
            })
            ->when($unitId, function ($query) use ($unitId)
            {
                return $query->where('unit_id', $unitId);
            });

        if ($allRecords == 0) {
            $sales->whereBetween('sale_date', [ $fromDate, $toDate ]);
        }

        if (Gate::denies('su-user-group') && Gate::denies('hq-user-group')) {
            // Get list of units for current user level
            $userUnits = $this->getUserUnits()->pluck('unit_id');

            $sales->whereIn('unit_id', $userUnits);
        }

        $dataTable = Datatables::of($sales)
            ->setRowClass(function ($sale)
            {
                return $sale->exchange_rate != 1 ? 'blue-row' : '';
            })
            ->editColumn('credit_sales_id', function ($sale) use ($unitCurrency)
            {
                if ($sale->credit_sales_id == '') {
                    return 0;
                }

                $creditSales = DB::table('credit_sales as cs')
                    ->select(
                        [
                            DB::raw('SUM(cs.gross_total * er.exchange_rate) as grossTotal'),
                        ]
                    )
                    ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
                    {
                        $join->on('er.domestic_currency_id', 'cs.currency_id')
                            ->where('er.foreign_currency_id', $unitCurrency)
                            ->whereRaw('er.date = cs.sale_date');
                    })
                    ->whereIn('cs.credit_sales_id', explode(",", $sale->credit_sales_id))
                    ->first();

                return $creditSales->grossTotal;
            })
            ->editColumn('entry_date', function ($sale)
            {
                return $sale->entry_date ? Carbon::parse($sale->entry_date)->format('d-m-Y') : '';
            })
            ->filterColumn('ss.entry_date', function ($query, $keyword)
            {
                $query->whereRaw("DATE_FORMAT(ss.entry_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
            })
            ->editColumn('sale_date', function ($sale)
            {
                return $sale->sale_date ? Carbon::parse($sale->sale_date)->format('d-m-Y') : '';
            })
            ->filterColumn('ss.sale_date', function ($query, $keyword)
            {
                $query->whereRaw("DATE_FORMAT(ss.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
            });

        // Goods columns
        $taxCodes = TaxCode::with('vendingSaleTaxCodes')->where('vending_sales', 1)->get();

        $goodColumns = [];
        foreach ($taxCodes as $taxCode) {
            foreach ($taxCode->vendingSaleTaxCodes as $netExtItem) {
                $goodColumns[$netExtItem->net_ext_ID] = ucfirst($netExtItem->net_ext);
            }
        }

        // Add Good columns
        foreach ($goodColumns as $netExtId => $netExt) {
            $dataTable->addColumn($netExt, function ($sale) use ($netExtId)
            {
                if ($sale->sale_type != 'vending') {
                    return 0;
                }

                $vendingSaleGood = VendingSaleGood::where('vending_sales_id', $sale->id)
                    ->where('net_ext_id', $netExtId)->first();

                if (is_null($vendingSaleGood)) {
                    return 0;
                }

                return $vendingSaleGood->amount * $sale->exchange_rate;
            });
        }

        // Vend Total column
        $dataTable->addColumn('Vend Total', function ($sale)
        {
            return VendingSaleGood::where('vending_sales_id', $sale->id)->sum('amount');
        });

        // Vend Total column
        $dataTable->addColumn('Total Sales', function ($sale)
        {
            $vendTotal = VendingSaleGood::where('vending_sales_id', $sale->id)->sum('amount');

            return $vendTotal + $sale->z_read;
        });

        // Remove first columns, which contain ID
        $dataTable->removeColumn('id');

        return $dataTable->make();
    }

    /**
     * Cash Sales Report.
     */
    public function cashSales()
    {
        $unitId = Cookie::get('cashSalesReportUnitIdCookie', '');
        $fromDate = Cookie::get('cashSalesReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('cashSalesReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.cash-sales.index', [
                'userUnits'    => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function cashSalesGrid(Request $request, $sheetId = null)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Track event
        Event::trackAction('Run Cash Sales Report');

        // Store in cookie
        Cookie::queue('cashSalesReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('cashSalesReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('cashSalesReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'cash-sales')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        // Currency symbol
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();

        $currencySymbol = $defaultCurrency->currency_symbol;
        if (!is_null($unit) && !is_null($unit->currency)) {
            $currencySymbol = $unit->currency->currency_symbol;
        }

        return view(
            'reports.cash-sales.grid', [
                'userId'         => $userId,
                'unitId'         => $unitId,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'allRecords'     => $allRecords,
                'sheetId'        => $sheetId,
                'notVisiable'    => $hiddenColumns,
                'isSuLevel'      => Gate::allows('su-user-group'),
                'currencySymbol' => $currencySymbol,
            ]
        );
    }

    public function cashSalesGridJson(Request $request)
    {
        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        // Currency
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();
        $unitCurrency = !is_null($unit) ? $unit->currency_id : $defaultCurrency->currency_id;

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    });
            } else {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    });
            } else {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $cashSales = \DB::table('cash_sales AS cs')
                ->select(
                    [
                        'cs.cash_sales_id',
                        'cs.date',
                        'un.unit_name',
                        'u.username',
                        'cs.reg_number',
                        'cs.sale_date',
                        'cs.z_number',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'cs.z_food',
                        'cs.z_confect_food',
                        'cs.z_fruit',
                        'cs.z_minerals',
                        'cs.z_confect',
                        'cs.cash_count',
                        'cs.credit_card',
                        'cs.staff_cards',
                        'cs.cash_credit_card',
                        'cs.z_read',
                        'cs.variance',
                        'cs.cash_purchase',
                        'cs.credit_sales_id',
                        'cs.over_ring',
                        'cs.cash_var',
                        'l.cash as lodge_cash',
                        'l.coin as lodge_coin',
                        DB::raw('(l.cash + l.coin) as lodge_amount'),
                        'l.date as lodge_date',
                        'l.slip_number',
                        'l.bag_number',
                        'cs.sale_details',
                        'cs.cash_sales_id',
                        'uu.username as update_by',
                        'cs.updated_at',
                    ]
                )
                ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                ->whereIn('cs.unit_id', $userUnits)
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'cs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = cs.sale_date');
                })
                ->whereIn('cs.unit_id', $userUnits->pluck('unit_id'))
                ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $cashSales = \DB::table('cash_sales AS cs')
                ->select(
                    [
                        'cs.cash_sales_id',
                        'cs.date',
                        'un.unit_name',
                        'u.username',
                        'cs.reg_number',
                        'cs.sale_date',
                        'cs.z_number',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'cs.z_food',
                        'cs.z_confect_food',
                        'cs.z_fruit',
                        'cs.z_minerals',
                        'cs.z_confect',
                        'cs.cash_count',
                        'cs.credit_card',
                        'cs.staff_cards',
                        'cs.cash_credit_card',
                        'cs.z_read',
                        'cs.variance',
                        'cs.cash_purchase',
                        'cs.credit_sales_id',
                        'cs.over_ring',
                        'cs.cash_var',
                        'l.cash as lodge_cash',
                        'l.coin as lodge_coin',
                        DB::raw('(l.cash + l.coin) as lodge_amount'),
                        'l.date as lodge_date',
                        'l.slip_number',
                        'l.bag_number',
                        'cs.sale_details',
                        'cs.cash_sales_id',
                        'uu.username as update_by',
                        'cs.updated_at',
                    ]
                )
                ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                ->whereIn('cs.unit_id', $userUnits)
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'cs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = cs.sale_date');
                })
                ->whereIn('cs.unit_id', $userUnits->pluck('unit_id'))
                ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId);
            } else {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId)
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId);
            } else {
                $cashSales = \DB::table('cash_sales AS cs')
                    ->select(
                        [
                            'cs.cash_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.reg_number',
                            'cs.sale_date',
                            'cs.z_number',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.z_food',
                            'cs.z_confect_food',
                            'cs.z_fruit',
                            'cs.z_minerals',
                            'cs.z_confect',
                            'cs.cash_count',
                            'cs.credit_card',
                            'cs.staff_cards',
                            'cs.cash_credit_card',
                            'cs.z_read',
                            'cs.variance',
                            'cs.cash_purchase',
                            'cs.credit_sales_id',
                            'cs.over_ring',
                            'cs.cash_var',
                            'l.cash as lodge_cash',
                            'l.coin as lodge_coin',
                            DB::raw('(l.cash + l.coin) as lodge_amount'),
                            'l.date as lodge_date',
                            'l.slip_number',
                            'l.bag_number',
                            'cs.sale_details',
                            'cs.cash_sales_id',
                            'uu.username as update_by',
                            'cs.updated_at',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId)
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } else {
            $cashSales = \DB::table('cash_sales AS cs')
                ->select(
                    [
                        'cs.cash_sales_id',
                        'cs.date',
                        'un.unit_name',
                        'u.username',
                        'cs.reg_number',
                        'cs.sale_date',
                        'cs.z_number',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'cs.z_food',
                        'cs.z_confect_food',
                        'cs.z_fruit',
                        'cs.z_minerals',
                        'cs.z_confect',
                        'cs.cash_count',
                        'cs.credit_card',
                        'cs.staff_cards',
                        'cs.cash_credit_card',
                        'cs.z_read',
                        'cs.variance',
                        'cs.cash_purchase',
                        'cs.credit_sales_id',
                        'cs.over_ring',
                        'cs.cash_var',
                        'l.cash as lodge_cash',
                        'l.coin as lodge_coin',
                        DB::raw('(l.cash + l.coin) as lodge_amount'),
                        'l.date as lodge_date',
                        'l.slip_number',
                        'l.bag_number',
                        'cs.sale_details',
                        'cs.cash_sales_id',
                        'uu.username as update_by',
                        'cs.updated_at',
                    ]
                )
                ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('users AS uu', 'cs.updated_by', '=', 'uu.user_id')
                ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                ->leftJoin('lodgements AS l', 'cs.lodgement_id', '=', 'l.lodgement_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'cs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = cs.sale_date');
                })
                ->where('cs.unit_id', $unitId)
                ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
        }

        if (Gate::allows('su-user-group')) {
            return Datatables::of($cashSales)
                ->setRowId(function ($cashSale)
                {
                    return 'tr_' . $cashSale->cash_sales_id;
                })
                ->setRowClass(function ($cashSales)
                {
                    return $cashSales->exchange_rate != 1 ? 'blue-row' : '';
                })
                ->addColumn('checkbox', function ($cashSale)
                {
                    $cashSalesData = \DB::table('cash_sales')
                        ->select('closed')
                        ->where('cash_sales_id', $cashSale->cash_sales_id)
                        ->first();
                    if ($cashSalesData->closed == 1) {
                        return '<input name="del_chks" disabled type="checkbox" class="checkboxs" value="' . $cashSale->cash_sales_id . '">';
                    } else {
                        return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $cashSale->cash_sales_id . '">';
                    }
                }, 0)
                ->editColumn('cash_sales_id', function ($cashSale)
                {
                    return '<a target="_blank" href="/sheets/cash-sales/' . $cashSale->cash_sales_id . '">' . $cashSale->cash_sales_id . '</a>';
                })
                ->editColumn('z_food', function ($cashSale)
                {
                    return $cashSale->z_food * $cashSale->exchange_rate;
                })
                ->editColumn('z_confect_food', function ($cashSale)
                {
                    return $cashSale->z_confect_food * $cashSale->exchange_rate;
                })
                ->editColumn('z_fruit', function ($cashSale)
                {
                    return $cashSale->z_fruit * $cashSale->exchange_rate;
                })
                ->editColumn('z_minerals', function ($cashSale)
                {
                    return $cashSale->z_minerals * $cashSale->exchange_rate;
                })
                ->editColumn('z_confect', function ($cashSale)
                {
                    return $cashSale->z_confect * $cashSale->exchange_rate;
                })
                ->editColumn('cash_count', function ($cashSale)
                {
                    return $cashSale->cash_count * $cashSale->exchange_rate;
                })
                ->editColumn('credit_card', function ($cashSale)
                {
                    return $cashSale->credit_card * $cashSale->exchange_rate;
                })
                ->editColumn('staff_cards', function ($cashSale)
                {
                    return $cashSale->staff_cards * $cashSale->exchange_rate;
                })
                ->editColumn('cash_credit_card', function ($cashSale)
                {
                    return $cashSale->cash_credit_card * $cashSale->exchange_rate;
                })
                ->editColumn('z_read', function ($cashSale)
                {
                    return $cashSale->z_read * $cashSale->exchange_rate;
                })
                ->editColumn('credit_sales_id', function ($cashSale) use ($unitCurrency)
                {
                    if ($cashSale->credit_sales_id == '') {
                        return 0;
                    }

                    $creditSales = DB::table('credit_sales as cs')
                        ->select(
                            [
                                DB::raw('SUM(cs.gross_total * er.exchange_rate) as grossTotal'),
                            ]
                        )
                        ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
                        {
                            $join->on('er.domestic_currency_id', 'cs.currency_id')
                                ->where('er.foreign_currency_id', $unitCurrency)
                                ->whereRaw('er.date = cs.sale_date');
                        })
                        ->whereIn('cs.credit_sales_id', explode(",", $cashSale->credit_sales_id))
                        ->first();

                    return $creditSales->grossTotal;
                })
                ->editColumn('variance', function ($cashSale) use ($unitCurrency)
                {
                    $returnCrSale = 0;

                    if ($cashSale->credit_sales_id != '') {
                        $creditSales = DB::table('credit_sales as cs')
                            ->select(
                                [
                                    DB::raw('SUM(cs.gross_total * er.exchange_rate) as grossTotal'),
                                ]
                            )
                            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
                            {
                                $join->on('er.domestic_currency_id', 'cs.currency_id')
                                    ->where('er.foreign_currency_id', $unitCurrency)
                                    ->whereRaw('er.date = cs.sale_date');
                            })
                            ->whereIn('cs.credit_sales_id', explode(",", $cashSale->credit_sales_id))
                            ->first();

                        $returnCrSale = $creditSales->grossTotal;
                    }

                    $ztotal = $cashSale->z_food + $cashSale->z_confect_food + $cashSale->z_minerals + $cashSale->z_confect + $cashSale->z_fruit;
                    $cashCreditCardTotal = $cashSale->cash_count + $cashSale->credit_card + $cashSale->staff_cards;

                    $variance_total = ($cashCreditCardTotal + $cashSale->cash_purchase - $ztotal) * $cashSale->exchange_rate + $returnCrSale;

                    return (round($variance_total, 2));
                })
                ->editColumn('cash_purchase', function ($cashSale)
                {
                    return $cashSale->cash_purchase * $cashSale->exchange_rate;
                })
                ->editColumn('over_ring', function ($cashSale)
                {
                    return $cashSale->over_ring * $cashSale->exchange_rate;
                })
                ->editColumn('cash_var', function ($cashSale)
                {
                    return $cashSale->cash_var * $cashSale->exchange_rate;
                })
                ->editColumn('lodge_cash', function ($cashSale)
                {
                    return $cashSale->lodge_cash * $cashSale->exchange_rate;
                })
                ->editColumn('lodge_coin', function ($cashSale)
                {
                    return $cashSale->lodge_coin * $cashSale->exchange_rate;
                })
                ->editColumn('lodge_amount', function ($cashSale)
                {
                    return $cashSale->lodge_amount * $cashSale->exchange_rate;
                })
                ->editColumn('update_by', function ($cashSale)
                {
                    if ($cashSale->update_by != null) {
                        return $cashSale->update_by;
                    } else {
                        return '';
                    }
                })
                ->editColumn('updated_at', function ($cashSale)
                {
                    if ($cashSale->updated_at != null) {
                        return $cashSale->updated_at;
                    } else {
                        return '';
                    }
                })
                ->addColumn('action', function ($cashSale)
                {
                    $cashSalesData = \DB::table('cash_sales')
                        ->select('closed')
                        ->where('cash_sales_id', $cashSale->cash_sales_id)
                        ->first();
                    if ($cashSalesData->closed == 1) {
                        return '-';
                    } else {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    }
                })
                ->editColumn('date', function ($cashSale)
                {
                    return $cashSale->date ? with(new Carbon($cashSale->date))->format('d-m-Y') : '';
                })
                ->filterColumn('cs.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('sale_date', function ($cashSale)
                {
                    return $cashSale->sale_date ? with(new Carbon($cashSale->sale_date))->format('d-m-Y') : '';
                })
                ->filterColumn('cs.sale_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('lodge_date', function ($cashSale)
                {
                    return $cashSale->lodge_date != '0000-00-00' ? with(new Carbon($cashSale->lodge_date))->format('d-m-Y') : '';
                })
                ->filterColumn('l.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(l.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        } else {
            return Datatables::of($cashSales)
                ->setRowId(function ($cashSale)
                {
                    return 'tr_' . $cashSale->cash_sales_id;
                })
                ->setRowClass(function ($cashSales)
                {
                    return $cashSales->exchange_rate != 1 ? 'blue-row' : '';
                })
                ->editColumn('cash_sales_id', function ($cashSale)
                {
                    return '<a target="_blank" href="/sheets/cash-sales/' . $cashSale->cash_sales_id . '">' . $cashSale->cash_sales_id . '</a>';
                })
                ->editColumn('z_food', function ($cashSale)
                {
                    return $cashSale->z_food * $cashSale->exchange_rate;
                })
                ->editColumn('z_confect_food', function ($cashSale)
                {
                    return $cashSale->z_confect_food * $cashSale->exchange_rate;
                })
                ->editColumn('z_fruit', function ($cashSale)
                {
                    return $cashSale->z_fruit * $cashSale->exchange_rate;
                })
                ->editColumn('z_minerals', function ($cashSale)
                {
                    return $cashSale->z_minerals * $cashSale->exchange_rate;
                })
                ->editColumn('z_confect', function ($cashSale)
                {
                    return $cashSale->z_confect * $cashSale->exchange_rate;
                })
                ->editColumn('cash_count', function ($cashSale)
                {
                    return $cashSale->cash_count * $cashSale->exchange_rate;
                })
                ->editColumn('credit_card', function ($cashSale)
                {
                    return $cashSale->credit_card * $cashSale->exchange_rate;
                })
                ->editColumn('staff_cards', function ($cashSale)
                {
                    return $cashSale->staff_cards * $cashSale->exchange_rate;
                })
                ->editColumn('cash_credit_card', function ($cashSale)
                {
                    return $cashSale->cash_credit_card * $cashSale->exchange_rate;
                })
                ->editColumn('z_read', function ($cashSale)
                {
                    return $cashSale->z_read * $cashSale->exchange_rate;
                })
                ->editColumn('credit_sales_id', function ($cashSale) use ($unitCurrency)
                {
                    if ($cashSale->credit_sales_id == '') {
                        return 0;
                    }

                    $creditSales = DB::table('credit_sales as cs')
                        ->select(
                            [
                                DB::raw('SUM(cs.gross_total * er.exchange_rate) as grossTotal'),
                            ]
                        )
                        ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
                        {
                            $join->on('er.domestic_currency_id', 'cs.currency_id')
                                ->where('er.foreign_currency_id', $unitCurrency)
                                ->whereRaw('er.date = cs.sale_date');
                        })
                        ->whereIn('cs.credit_sales_id', explode(",", $cashSale->credit_sales_id))
                        ->first();

                    return $creditSales->grossTotal;
                })
                ->editColumn('variance', function ($cashSale) use ($unitCurrency)
                {
                    $returnCrSale = 0;

                    if ($cashSale->credit_sales_id != '') {
                        $creditSales = DB::table('credit_sales as cs')
                            ->select(
                                [
                                    DB::raw('SUM(cs.gross_total * er.exchange_rate) as grossTotal'),
                                ]
                            )
                            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
                            {
                                $join->on('er.domestic_currency_id', 'cs.currency_id')
                                    ->where('er.foreign_currency_id', $unitCurrency)
                                    ->whereRaw('er.date = cs.sale_date');
                            })
                            ->whereIn('cs.credit_sales_id', explode(",", $cashSale->credit_sales_id))
                            ->first();

                        $returnCrSale = $creditSales->grossTotal;
                    }

                    $ztotal = $cashSale->z_food + $cashSale->z_confect_food + $cashSale->z_minerals + $cashSale->z_confect + $cashSale->z_fruit;
                    $cashCreditCardTotal = $cashSale->cash_count + $cashSale->credit_card + $cashSale->staff_cards;

                    $variance_total = ($cashCreditCardTotal + $cashSale->cash_purchase - $ztotal) * $cashSale->exchange_rate + $returnCrSale;

                    return (round($variance_total, 2));
                })
                ->editColumn('cash_purchase', function ($cashSale)
                {
                    return $cashSale->cash_purchase * $cashSale->exchange_rate;
                })
                ->editColumn('over_ring', function ($cashSale)
                {
                    return $cashSale->over_ring * $cashSale->exchange_rate;
                })
                ->editColumn('cash_var', function ($cashSale)
                {
                    return $cashSale->cash_var * $cashSale->exchange_rate;
                })
                ->editColumn('lodge_cash', function ($cashSale)
                {
                    return $cashSale->lodge_cash * $cashSale->exchange_rate;
                })
                ->editColumn('lodge_coin', function ($cashSale)
                {
                    return $cashSale->lodge_coin * $cashSale->exchange_rate;
                })
                ->editColumn('lodge_amount', function ($cashSale)
                {
                    return $cashSale->lodge_amount * $cashSale->exchange_rate;
                })
                ->editColumn('update_by', function ($cashSale)
                {
                    if ($cashSale->update_by != null) {
                        return $cashSale->update_by;
                    } else {
                        return '';
                    }
                })
                ->editColumn('updated_at', function ($cashSale)
                {
                    if ($cashSale->updated_at != null) {
                        return $cashSale->updated_at;
                    } else {
                        return '';
                    }
                })
                ->addColumn('action', function ($cashSale)
                {
                    $cashSalesData = \DB::table('cash_sales')
                        ->select('closed')
                        ->where('cash_sales_id', $cashSale->cash_sales_id)
                        ->first();
                    if ($cashSalesData->closed == 1) {
                        return '-';
                    } else {
                        return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                    }
                })
                ->editColumn('date', function ($cashSale)
                {
                    return $cashSale->date ? with(new Carbon($cashSale->date))->format('d-m-Y') : '';
                })
                ->filterColumn('cs.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('sale_date', function ($cashSale)
                {
                    return $cashSale->sale_date ? with(new Carbon($cashSale->sale_date))->format('d-m-Y') : '';
                })
                ->filterColumn('cs.sale_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('lodge_date', function ($cashSale)
                {
                    return $cashSale->lodge_date != '0000-00-00' ? with(new Carbon($cashSale->lodge_date))->format('d-m-Y') : '';
                })
                ->filterColumn('l.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(l.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        }
    }

    public function deleteCashSalesRecord($id)
    {
        $cashSalesIds = explode(',', $id);

        foreach ($cashSalesIds as $cashSalesId) {
            $CashSales = \App\CashSales::find($cashSalesId);
            $CashSales->delete();
        }

        echo $id;
    }

    /**
     * Credit Sales Report.
     */
    public function creditSales()
    {
        $unitId = Cookie::get('creditSalesReportUnitIdCookie', '');
        $fromDate = Cookie::get('creditSalesReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('creditSalesReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.credit-sales.index', [
                'userUnits'    => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function creditSalesGrid(Request $request, $sheetId = null)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Track action
        Event::trackAction('Run Credit Sales Report');

        // Store in cookie
        Cookie::queue('creditSalesReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('creditSalesReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('creditSalesReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('creditSalesReportAllRecordsCookie', $allRecords, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'credit-sales')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        // Tax codes columns
        $taxCodes = TaxCode::where('credit_sales', 1)->get();

        $taxes = [];
        foreach ($taxCodes as $taxCode) {
            $taxes[] = $taxCode->tax_code_display_rate;
        }

        $startColumn = Gate::allows('su-user-group') ? 11 : 10;
        $taxColumns = range($startColumn, $startColumn + count($taxes) * 3 + 2);

        // Currency symbol
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();

        $currencySymbol = $defaultCurrency->currency_symbol;
        if (!is_null($unit) && !is_null($unit->currency)) {
            $currencySymbol = $unit->currency->currency_symbol;
        }

        return view(
            'reports.credit-sales.grid', [
                'userId'         => $userId,
                'unitId'         => $unitId,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'allRecords'     => $allRecords,
                'sheetId'        => $sheetId,
                'notVisiable'    => $hiddenColumns,
                'visible'        => $request->visible,
                'taxes'          => $taxes,
                'taxColumns'     => json_encode($taxColumns),
                'isSuLevel'      => Gate::allows('su-user-group'),
                'currencySymbol' => $currencySymbol,
            ]
        );
    }

    public function creditSalesGridJson(Request $request)
    {
        if ($request->has('sheet_id')) {
            $cashSaleVis = $request->has('visible') && $request->visible == 'n' ? 1 : 0;
            $visBy = session()->get('userId');
            $dateVis = date('Y-m-d');
            \DB::statement("UPDATE credit_sales SET cash_sale_vis = '$cashSaleVis', vis_by = '$visBy', date_vis = '$dateVis' WHERE credit_sales_id = " . $request->sheet_id);
        }

        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        // Currency
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();
        $unitCurrency = !is_null($unit) ? $unit->currency_id : $defaultCurrency->currency_id;

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    });
            } else {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    });
            } else {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $creditSales = \DB::table('credit_sales AS cs')
                ->select(
                    [
                        'cs.credit_sales_id',
                        'cs.date',
                        'un.unit_name',
                        'u.username',
                        'cs.docket_number',
                        'cs.sale_date',
                        'cs.credit_reference',
                        'cs.cost_centre',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'cs.goods_total',
                        'cs.vat_total',
                        'cs.gross_total',
                        'cs.credit_sales_id',
                        'cs.cash_sale_vis',
                        'cs.vis_by',
                        'cs.date_vis',
                    ]
                )
                ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'cs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = cs.sale_date');
                })
                ->whereIn('cs.unit_id', $userUnits)
                ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $creditSales = \DB::table('credit_sales AS cs')
                ->select(
                    [
                        'cs.credit_sales_id',
                        'cs.date',
                        'un.unit_name',
                        'u.username',
                        'cs.docket_number',
                        'cs.sale_date',
                        'cs.credit_reference',
                        'cs.cost_centre',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'cs.goods_total',
                        'cs.vat_total',
                        'cs.gross_total',
                        'cs.credit_sales_id',
                        'cs.cash_sale_vis',
                        'cs.vis_by',
                        'cs.date_vis',
                    ]
                )
                ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'cs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = cs.sale_date');
                })
                ->whereIn('cs.unit_id', $userUnits)
                ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId);
            } else {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId)
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId);
            } else {
                $creditSales = \DB::table('credit_sales AS cs')
                    ->select(
                        [
                            'cs.credit_sales_id',
                            'cs.credit_sales_id',
                            'cs.date',
                            'un.unit_name',
                            'u.username',
                            'cs.docket_number',
                            'cs.sale_date',
                            'cs.credit_reference',
                            'cs.cost_centre',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'cs.goods_total',
                            'cs.vat_total',
                            'cs.gross_total',
                            'cs.credit_sales_id',
                            'cs.cash_sale_vis',
                            'cs.vis_by',
                            'cs.date_vis',
                        ]
                    )
                    ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'cs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = cs.sale_date');
                    })
                    ->where('cs.unit_id', $unitId)
                    ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
            }
        } else {
            $creditSales = \DB::table('credit_sales AS cs')
                ->select(
                    [
                        'cs.credit_sales_id',
                        'cs.credit_sales_id',
                        'cs.date',
                        'un.unit_name',
                        'u.username',
                        'cs.docket_number',
                        'cs.sale_date',
                        'cs.credit_reference',
                        'cs.cost_centre',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'cs.goods_total',
                        'cs.vat_total',
                        'cs.gross_total',
                        'cs.credit_sales_id',
                        'cs.cash_sale_vis',
                        'cs.vis_by',
                        'cs.date_vis',
                    ]
                )
                ->leftJoin('users AS u', 'cs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('units AS un', 'cs.unit_id', '=', 'un.unit_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'cs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'cs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = cs.sale_date');
                })
                ->where('cs.unit_id', $unitId)
                ->whereBetween('cs.sale_date', [ $fromDate, $toDate ]);
        }

        if (Gate::allows('su-user-group')) {
            $dataTable = Datatables::of($creditSales)
                ->setRowId(function ($creditSale)
                {
                    return 'tr_' . $creditSale->credit_sales_id;
                })
                ->setRowClass(function ($creditSale)
                {
                    return $creditSale->exchange_rate != 1 ? 'blue-row' : '';
                })
                ->addColumn('checkbox', function ($creditSale)
                {
                    $creditSalesData = \DB::table('credit_sales')
                        ->select('cash_sale_vis', 'closed')
                        ->where('credit_sales_id', $creditSale->credit_sales_id)
                        ->first();
                    if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                        return '<input name="del_chks" disabled type="checkbox" class="checkboxs" value="' . $creditSale->credit_sales_id . '">';
                    } else {
                        return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $creditSale->credit_sales_id . '">';
                    }
                }, 0)
                ->editColumn('credit_sales_id', function ($creditSale)
                {
                    $creditSalesData = \DB::table('credit_sales')
                        ->select('cash_sale_vis', 'closed')
                        ->where('credit_sales_id', $creditSale->credit_sales_id)
                        ->first();
                    if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                        return $creditSale->credit_sales_id;
                    } else {
                        return '<a target="_blank" href="/sheets/credit-sales/' . $creditSale->credit_sales_id . '">' . $creditSale->credit_sales_id . '</a>';
                    }
                })
                ->editColumn('date', function ($creditSale)
                {
                    return $creditSale->date ? with(new Carbon($creditSale->date))->format('d-m-Y') : '';
                })
                ->editColumn('sale_date', function ($creditSale)
                {
                    return $creditSale->sale_date ? with(new Carbon($creditSale->sale_date))->format('d-m-Y') : '';
                })
                ->editColumn('goods_total', function ($creditSale)
                {
                    return $creditSale->goods_total * $creditSale->exchange_rate;
                })
                ->editColumn('vat_total', function ($creditSale)
                {
                    return $creditSale->goods_total * $creditSale->exchange_rate;
                })
                ->editColumn('gross_total', function ($creditSale)
                {
                    return $creditSale->goods_total * $creditSale->exchange_rate;
                })
                ->editColumn('cash_sale_vis', function ($creditSale)
                {
                    $unitId = Cookie::get('creditSalesReportUnitIdCookie') != '' ? Cookie::get('creditSalesReportUnitIdCookie') : 'All';
                    $fromDate = Cookie::get('creditSalesReportFromDateCookie');
                    $toDate = Cookie::get('creditSalesReportToDateCookie');
                    $allRecords = Cookie::get('creditSalesReportAllRecordsCookie');

                    $creditSalesData = \DB::table('credit_sales')
                        ->select('cash_sale_vis', 'closed')
                        ->where('credit_sales_id', $creditSale->credit_sales_id)
                        ->first();

                    if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                        return '<a onclick="return confirm_visibility()" href="/reports/credit-sales/grid/' . $creditSale->credit_sales_id . '/' . $unitId . '/' . $fromDate . '/' . $toDate . '/' . $allRecords . '/n">N</a>';
                    } else {
                        return '<a onclick="return confirm_invisibility()" href="/reports/credit-sales/grid/' . $creditSale->credit_sales_id . '/' . $unitId . '/' . $fromDate . '/' . $toDate . '/' . $allRecords . '/y">Y</a>';
                    }
                })
                ->editColumn('vis_by', function ($creditSale)
                {
                    if ($creditSale->vis_by != 0)
                        return \App\User::where('user_id', $creditSale->vis_by)->value('username');
                })
                ->editColumn('date_vis', function ($creditSale)
                {
                    return $creditSale->vis_by != 0 && $creditSale->date_vis != '0000-00-00' ? with(new Carbon($creditSale->date_vis))->format('d-m-Y') : '';
                })
                ->filterColumn('cs.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('cs.sale_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('cs.date_vis', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.date_vis,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                });
        } else {
            $dataTable = Datatables::of($creditSales)
                ->setRowId(function ($creditSale)
                {
                    return 'tr_' . $creditSale->credit_sales_id;
                })
                ->setRowClass(function ($creditSale)
                {
                    return $creditSale->exchange_rate != 1 ? 'blue-row' : '';
                })
                ->editColumn('credit_sales_id', function ($creditSale)
                {
                    $creditSalesData = \DB::table('credit_sales')
                        ->select('cash_sale_vis', 'closed')
                        ->where('credit_sales_id', $creditSale->credit_sales_id)
                        ->first();
                    if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                        return $creditSale->credit_sales_id;
                    } else {
                        return '<a target="_blank" href="/sheets/credit-sales/' . $creditSale->credit_sales_id . '">' . $creditSale->credit_sales_id . '</a>';
                    }
                })
                ->editColumn('date', function ($creditSale)
                {
                    return $creditSale->date ? with(new Carbon($creditSale->date))->format('d-m-Y') : '';
                })
                ->editColumn('sale_date', function ($creditSale)
                {
                    return $creditSale->sale_date ? with(new Carbon($creditSale->sale_date))->format('d-m-Y') : '';
                })
                ->editColumn('goods_total', function ($creditSale)
                {
                    return $creditSale->goods_total * $creditSale->exchange_rate;
                })
                ->editColumn('vat_total', function ($creditSale)
                {
                    return $creditSale->goods_total * $creditSale->exchange_rate;
                })
                ->editColumn('gross_total', function ($creditSale)
                {
                    return $creditSale->goods_total * $creditSale->exchange_rate;
                })
                ->editColumn('cash_sale_vis', function ($creditSale)
                {
                    $unitId = Cookie::get('creditSalesReportUnitIdCookie') != '' ? Cookie::get('creditSalesReportUnitIdCookie') : 'All';
                    $fromDate = Cookie::get('creditSalesReportFromDateCookie');
                    $toDate = Cookie::get('creditSalesReportToDateCookie');
                    $allRecords = Cookie::get('creditSalesReportAllRecordsCookie');

                    $creditSalesData = \DB::table('credit_sales')
                        ->select('cash_sale_vis', 'closed')
                        ->where('credit_sales_id', $creditSale->credit_sales_id)
                        ->first();

                    if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                        return '<a onclick="return confirm_visibility()" href="/reports/credit-sales/grid/' . $creditSale->credit_sales_id . '/' . $unitId . '/' . $fromDate . '/' . $toDate . '/' . $allRecords . '/n">N</a>';
                    } else {
                        return '<a onclick="return confirm_invisibility()" href="/reports/credit-sales/grid/' . $creditSale->credit_sales_id . '/' . $unitId . '/' . $fromDate . '/' . $toDate . '/' . $allRecords . '/y">Y</a>';
                    }
                })
                ->editColumn('vis_by', function ($creditSale)
                {
                    if ($creditSale->vis_by != 0)
                        return \App\User::where('user_id', $creditSale->vis_by)->value('username');
                })
                ->editColumn('date_vis', function ($creditSale)
                {
                    return $creditSale->vis_by != 0 && $creditSale->date_vis != '0000-00-00' ? with(new Carbon($creditSale->date_vis))->format('d-m-Y') : '';
                })
                ->filterColumn('cs.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('cs.sale_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->filterColumn('cs.date_vis', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(cs.date_vis,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                });
        }

        // Tax columns
        $taxCodes = TaxCode::where('credit_sales', 1)->get();

        // Add Good columns
        $columnIndex = Gate::allows('su-user-group') ? 11 : 10;

        foreach ($taxCodes as $taxCode) {
            $dataTable->addColumn("Goods {$taxCode->tax_code_display_rate}", function ($creditSale) use ($taxCode)
            {
                $creditSaleGood = CreditSaleGood::where('credit_sales_id', $creditSale->credit_sales_id)
                    ->where('tax_code_id', $taxCode->tax_code_ID)->first();

                if (is_null($creditSaleGood)) {
                    return 0;
                }

                $goods = $creditSaleGood->amount / (1 + $taxCode->tax_rate / 100);

                return $goods * $creditSale->exchange_rate;
            }, $columnIndex++);

            $dataTable->addColumn("VAT {$taxCode->tax_code_display_rate}", function ($creditSale) use ($taxCode)
            {
                $creditSaleGood = CreditSaleGood::where('credit_sales_id', $creditSale->credit_sales_id)
                    ->where('tax_code_id', $taxCode->tax_code_ID)->first();

                if (is_null($creditSaleGood)) {
                    return 0;
                }

                $goods = $creditSaleGood->amount / (1 + $taxCode->tax_rate / 100);
                $vat = $goods * $taxCode->tax_rate / 100;

                return $vat * $creditSale->exchange_rate;
            }, $columnIndex++);

            $dataTable->addColumn("Gross {$taxCode->tax_code_display_rate}", function ($creditSale) use ($taxCode)
            {
                $creditSaleGood = CreditSaleGood::where('credit_sales_id', $creditSale->credit_sales_id)
                    ->where('tax_code_id', $taxCode->tax_code_ID)->first();

                if (is_null($creditSaleGood)) {
                    return 0;
                }

                return $creditSaleGood->amount * $creditSale->exchange_rate;
            }, $columnIndex++);
        }

        // Add Action column
        if (Gate::allows('su-user-group')) {
            $dataTable->addColumn('action', function ($creditSale)
            {
                $creditSalesData = \DB::table('credit_sales')
                    ->select('cash_sale_vis', 'closed')
                    ->where('credit_sales_id', $creditSale->credit_sales_id)
                    ->first();
                if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                    return '-';
                } else {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                }
            });
        } else {
            $dataTable->addColumn('action', function ($creditSale)
            {
                $creditSalesData = \DB::table('credit_sales')
                    ->select('cash_sale_vis', 'closed')
                    ->where('credit_sales_id', $creditSale->credit_sales_id)
                    ->first();
                if ($creditSalesData->cash_sale_vis == 0 || $creditSalesData->closed == 1) {
                    return '-';
                } else {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                }
            });
        }

        return $dataTable->make();
    }

    public function deleteCreditSalesRecord($id)
    {
        $creditSalesIds = explode(',', $id);

        DB::beginTransaction();

        try {
            foreach ($creditSalesIds as $creditSalesId) {
                // Delete Credit Sale
                $CreditSales = CreditSales::findOrFail($creditSalesId);
                $CreditSales->delete();

                // Delete Credit Sale Goods
                CreditSaleGood::where('credit_sales_id', $creditSalesId)->delete();
            }

            DB::commit();

            echo $id;
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Vending Sales Report.
     */
    public function vendingSales()
    {
        $unitId = Cookie::get('vendingSalesReportUnitIdCookie', '');
        $vendName = Cookie::get('vendingSalesReportVendingMachineCookie', '');
        $fromDate = Cookie::get('vendingSalesReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('vendingSalesReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.vending-sales.index', [
                'userUnits'       => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit'    => $unitId,
                'selectedMachine' => $vendName,
                'fromDate'        => $fromDate,
                'toDate'          => $toDate,
                'backUrl'         => session()->get('backUrl', '/'),
            ]
        );
    }

    public function vendingSalesGrid(Request $request, $sheetId = null)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $vendingMachine = $request->vending_machine;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Store in cookie
        Cookie::queue('vendingSalesReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('vendingSalesReportVendingMachineCookie', $vendingMachine, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('vendingSalesReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('vendingSalesReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('vendingSalesReportAllRecordsCookie', $allRecords, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'vending-sales')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        // Goods columns
        $taxCodes = TaxCode::with('vendingSaleTaxCodes')->where('vending_sales', 1)->get();

        $goods = [];
        foreach ($taxCodes as $taxCode) {
            foreach ($taxCode->vendingSaleTaxCodes as $netExtItem) {
                $goods[$netExtItem->net_ext_ID] = ucfirst($netExtItem->net_ext);
            }
        }

        $startColumn = Gate::allows('su-user-group') ? 13 : 12;
        $goodColumns = range($startColumn, $startColumn + count($goods));

        // Currency symbol
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();

        $currencySymbol = $defaultCurrency->currency_symbol;
        if (!is_null($unit) && !is_null($unit->currency)) {
            $currencySymbol = $unit->currency->currency_symbol;
        }

        return view(
            'reports.vending-sales.grid', [
                'unitId'         => $unitId,
                'userId'         => $userId,
                'vendingMachine' => $vendingMachine,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'allRecords'     => $allRecords,
                'sheetId'        => $sheetId,
                'notVisiable'    => $hiddenColumns,
                'visible'        => $request->visible,
                'goods'          => $goods,
                'goodColumns'    => json_encode($goodColumns),
                'isSuLevel'      => Gate::allows('su-user-group'),
                'currencySymbol' => $currencySymbol,
            ]
        );
    }

    public function vendingSalesGridJson(Request $request)
    {
        $unitId = $request->unit_id;
        $vendingMachine = $request->vending_machine;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        // Currency
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();
        $unitCurrency = !is_null($unit) ? $unit->currency_id : $defaultCurrency->currency_id;

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    });
            } else {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    })
                    ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    });
            } else {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    })
                    ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $vendingSales = \DB::table('vending_sales AS vs')
                ->select(
                    [
                        'vs.vending_sales_id',
                        'vs.date',
                        'un.unit_name',
                        'u.username',
                        'vs.sale_date',
                        'vm.vend_name',
                        'vs.opening',
                        'vs.closing',
                        'vs.till_number',
                        'vs.z_read',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'vs.cash',
                    ]
                )
                ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'vs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = vs.sale_date');
                })
                ->when($vendingMachine, function ($query) use ($vendingMachine)
                {
                    return $query->where('vend_id', $vendingMachine);
                })
                ->whereIn('vs.unit_id', $userUnits)
                ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $vendingSales = \DB::table('vending_sales AS vs')
                ->select(
                    [
                        'vs.vending_sales_id',
                        'vs.date',
                        'un.unit_name',
                        'u.username',
                        'vs.sale_date',
                        'vm.vend_name',
                        'vs.opening',
                        'vs.closing',
                        'vs.till_number',
                        'vs.z_read',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'vs.cash',
                    ]
                )
                ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'vs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = vs.sale_date');
                })
                ->when($vendingMachine, function ($query) use ($vendingMachine)
                {
                    return $query->where('vend_id', $vendingMachine);
                })
                ->whereIn('vs.unit_id', $userUnits)
                ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    })
                    ->where('vs.unit_id', $unitId);
            } else {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    })
                    ->where('vs.unit_id', $unitId)
                    ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    })
                    ->where('vs.unit_id', $unitId);
            } else {
                $vendingSales = \DB::table('vending_sales AS vs')
                    ->select(
                        [
                            'vs.vending_sales_id',
                            'vs.date',
                            'un.unit_name',
                            'u.username',
                            'vs.sale_date',
                            'vm.vend_name',
                            'vs.opening',
                            'vs.closing',
                            'vs.till_number',
                            'vs.z_read',
                            'C.currency_code',
                            'ER.exchange_rate',
                            'vs.cash',
                        ]
                    )
                    ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                    ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                    ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                    ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                    ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                    {
                        $join->on('ER.domestic_currency_id', 'vs.currency_id')
                            ->where('ER.foreign_currency_id', $unitCurrency)
                            ->whereRaw('ER.date = vs.sale_date');
                    })
                    ->when($vendingMachine, function ($query) use ($vendingMachine)
                    {
                        return $query->where('vend_id', $vendingMachine);
                    })
                    ->where('vs.unit_id', $unitId)
                    ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
            }
        } else {
            $vendingSales = \DB::table('vending_sales AS vs')
                ->select(
                    [
                        'vs.vending_sales_id',
                        'vs.date',
                        'un.unit_name',
                        'u.username',
                        'vs.sale_date',
                        'vm.vend_name',
                        'vs.opening',
                        'vs.closing',
                        'vs.till_number',
                        'vs.z_read',
                        'C.currency_code',
                        'ER.exchange_rate',
                        'vs.cash',
                    ]
                )
                ->leftJoin('users AS u', 'vs.supervisor_id', '=', 'u.user_id')
                ->leftJoin('units AS un', 'vs.unit_id', '=', 'un.unit_id')
                ->leftJoin('vend_management AS vm', 'vs.vend_id', '=', 'vm.vend_management_id')
                ->leftJoin('currencies AS C', 'C.currency_id', '=', 'vs.currency_id')
                ->leftJoin('exchange_rates as ER', function ($join) use ($unitCurrency)
                {
                    $join->on('ER.domestic_currency_id', 'vs.currency_id')
                        ->where('ER.foreign_currency_id', $unitCurrency)
                        ->whereRaw('ER.date = vs.sale_date');
                })
                ->when($vendingMachine, function ($query) use ($vendingMachine)
                {
                    return $query->where('vend_id', $vendingMachine);
                })
                ->where('vs.unit_id', $unitId)
                ->whereBetween('vs.sale_date', [ $fromDate, $toDate ]);
        }

        if (Gate::allows('su-user-group')) {
            $dataTable = Datatables::of($vendingSales)
                ->setRowId(function ($vendingSale)
                {
                    return 'tr_' . $vendingSale->vending_sales_id;
                })
                ->setRowClass(function ($vendingSale)
                {
                    return $vendingSale->exchange_rate != 1 ? 'blue-row' : '';
                })
                ->addColumn('checkbox', function ($vendingSale)
                {
                    $vendingSalesData = \DB::table('vending_sales')
                        ->select('closed')
                        ->where('vending_sales_id', $vendingSale->vending_sales_id)
                        ->first();
                    if ($vendingSalesData->closed == 1) {
                        return '<input name="del_chks" disabled type="checkbox" class="checkboxs" value="' . $vendingSale->vending_sales_id . '">';
                    } else {
                        return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $vendingSale->vending_sales_id . '">';
                    }
                }, 0)
                ->editColumn('vending_sales_id', function ($vendingSale)
                {
                    $vendingSalesData = \DB::table('vending_sales')
                        ->select('closed')
                        ->where('vending_sales_id', $vendingSale->vending_sales_id)
                        ->first();

                    if ($vendingSalesData->closed == 1) {
                        return $vendingSale->vending_sales_id;
                    } else {
                        return '<a target="_blank" href="/sheets/vending-sales/' . $vendingSale->vending_sales_id . '">' . $vendingSale->vending_sales_id . '</a>';
                    }
                })
                ->editColumn('opening', function ($vendingSale)
                {
                    return $vendingSale->opening * $vendingSale->exchange_rate;
                })
                ->editColumn('closing', function ($vendingSale)
                {
                    return $vendingSale->closing * $vendingSale->exchange_rate;
                })
                ->editColumn('cash', function ($vendingSale)
                {
                    return $vendingSale->cash * $vendingSale->exchange_rate;
                })
                ->editColumn('date', function ($vendingSale)
                {
                    return $vendingSale->date ? with(new Carbon($vendingSale->date))->format('d-m-Y') : '';
                })
                ->filterColumn('vs.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(vs.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('sale_date', function ($vendingSale)
                {
                    return $vendingSale->sale_date ? with(new Carbon($vendingSale->sale_date))->format('d-m-Y') : '';
                })
                ->filterColumn('vs.sale_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(vs.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                });
        } else {
            $dataTable = Datatables::of($vendingSales)
                ->setRowId(function ($vendingSale)
                {
                    return 'tr_' . $vendingSale->vending_sales_id;
                })
                ->setRowClass(function ($vendingSale)
                {
                    return $vendingSale->exchange_rate != 1 ? 'blue-row' : '';
                })
                ->editColumn('vending_sales_id', function ($vendingSale)
                {
                    $vendingSalesData = \DB::table('vending_sales')
                        ->select('closed')
                        ->where('vending_sales_id', $vendingSale->vending_sales_id)
                        ->first();
                    if ($vendingSalesData->closed == 1) {
                        return $vendingSale->vending_sales_id;
                    } else {
                        return '<a target="_blank" href="/sheets/vending-sales/' . $vendingSale->vending_sales_id . '">' . $vendingSale->vending_sales_id . '</a>';
                    }
                })
                ->editColumn('opening', function ($vendingSale)
                {
                    return $vendingSale->opening * $vendingSale->exchange_rate;
                })
                ->editColumn('closing', function ($vendingSale)
                {
                    return $vendingSale->closing * $vendingSale->exchange_rate;
                })
                ->editColumn('cash', function ($vendingSale)
                {
                    return $vendingSale->cash * $vendingSale->exchange_rate;
                })
                ->editColumn('date', function ($vendingSale)
                {
                    return $vendingSale->date ? with(new Carbon($vendingSale->date))->format('d-m-Y') : '';
                })
                ->filterColumn('vs.date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(vs.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('sale_date', function ($vendingSale)
                {
                    return $vendingSale->sale_date ? with(new Carbon($vendingSale->sale_date))->format('d-m-Y') : '';
                })
                ->filterColumn('vs.sale_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(vs.sale_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                });
        }

        // Goods columns
        $taxCodes = TaxCode::with('vendingSaleTaxCodes')->where('vending_sales', 1)->get();

        $goodColumns = [];
        foreach ($taxCodes as $taxCode) {
            foreach ($taxCode->vendingSaleTaxCodes as $netExtItem) {
                $goodColumns[$netExtItem->net_ext_ID] = ucfirst($netExtItem->net_ext);
            }
        }

        // Add Good columns
        foreach ($goodColumns as $netExtId => $netExt) {
            $dataTable->addColumn($netExt, function ($vendingSale) use ($netExtId)
            {
                $vendingSaleGood = VendingSaleGood::where('vending_sales_id', $vendingSale->vending_sales_id)
                    ->where('net_ext_id', $netExtId)->first();

                if (is_null($vendingSaleGood)) {
                    return 0;
                }

                return $vendingSaleGood->amount * $vendingSale->exchange_rate;
            });
        }

        // Add Action column
        if (Gate::allows('su-user-group')) {
            $dataTable->addColumn('action', function ($vendingSale)
            {
                $vendingSalesData = \DB::table('vending_sales')
                    ->select('closed')
                    ->where('vending_sales_id', $vendingSale->vending_sales_id)
                    ->first();
                if ($vendingSalesData->closed == 1) {
                    return '-';
                } else {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                }
            });
        } else {
            $dataTable->addColumn('action', function ($vendingSale)
            {
                $vendingSalesData = \DB::table('vending_sales')
                    ->select('closed')
                    ->where('vending_sales_id', $vendingSale->vending_sales_id)
                    ->first();
                if ($vendingSalesData->closed == 1) {
                    return '-';
                } else {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
                }
            });
        }

        return $dataTable->make();
    }

    public function vendingMachinesJson(Request $request)
    {
        $unitMachinesArr = [];
        $vendingMachinesStr = '';

        if ($request->unit_id == '') {
            $unitMachines = \DB::select("SELECT vend_management_id, vend_name FROM vend_management ORDER BY vend_name");
        } else {
            $unitMachines = \DB::select("SELECT vend_management_id, vend_name FROM vend_management WHERE unit_id = '" . $request->unit_id . "' ORDER BY vend_name");
        }

        $vendingMachinesStr .= "<select name='vending_machine' id='vending_machine' class='form-control' tabindex='2'>";
        $vendingMachinesStr .= "<option value='0'>All</option>";

        foreach ($unitMachines as $um) {
            $selected = isset($request->selectedMachine) && $request->selectedMachine == $um->vend_management_id ? 'selected="selected"' : '';
            $vendingMachinesStr .= "<option $selected value='" . $um->vend_management_id . "'>" . $um->vend_name . "</option>";
        }

        $vendingMachinesStr .= "</select>";
        $unitMachinesArr['vendingMachinesData'] = $vendingMachinesStr;

        echo json_encode($unitMachinesArr);
    }

    public function deleteVendingSalesRecord($id)
    {
        $vendingSalesIds = explode(',', $id);

        DB::beginTransaction();

        try {
            foreach ($vendingSalesIds as $vendingSalesId) {
                // Delete Vending Sale
                $VendingSales = VendingSales::findOrFail($vendingSalesId);
                $VendingSales->delete();

                // Delete Vending Sale Goods
                VendingSaleGood::where('vending_sales_id', $vendingSalesId)->delete();
            }

            DB::commit();

            echo $id;
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Unit Trading Account Report.
     */
    public function unitTradingAccount()
    {
        $unitId = Cookie::get('utaUnitIdCookie', '');
        $fromDate = Cookie::get('utaFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('utaToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.unit-trading-account.index', [
                'userUnits'       => $userUnits,
                'selectedUnit'    => $unitId,
                'fromDate'        => $fromDate,
                'toDate'          => $toDate,
                'head_count'      => 0,
                'trading_days'    => 0,
                'weeks'           => 0,
                'budgetType'      => '',
                'backUrl'         => session()->get('backUrl', '/'),
                'currency_symbol' => '',

                'gross_sales_budget'            => number_format(0, 2, '.', ','),
                'gross_sales_actual'            => number_format(0, 2, '.', ','),
                'gross_sales_variance'          => number_format(0, 2, '.', ','),
                'gross_sales_percent_of_budget' => number_format(0, 2, '.', ','),

                'net_sales_budget'            => number_format(0, 2, '.', ','),
                'net_sales_actual'            => number_format(0, 2, '.', ','),
                'net_sales_variance'          => number_format(0, 2, '.', ','),
                'net_sales_percent_of_budget' => number_format(0, 2, '.', ','),

                'cost_of_sales_budget'            => number_format(0, 2, '.', ','),
                'cost_of_sales_actual'            => number_format(0, 2, '.', ','),
                'cost_of_sales_variance'          => number_format(0, 2, '.', ','),
                'cost_of_sales_percent_of_budget' => number_format(0, 2, '.', ','),

                'gross_profit_gross_budget'            => number_format(0, 2, '.', ','),
                'gross_profit_gross_actual'            => number_format(0, 2, '.', ','),
                'gross_profit_gross_variance'          => number_format(0, 2, '.', ','),
                'gross_profit_gross_percent_of_budget' => number_format(0, 2, '.', ','),

                'gross_profit_net_budget'            => number_format(0, 2, '.', ','),
                'gross_profit_net_actual'            => number_format(0, 2, '.', ','),
                'gross_profit_net_variance'          => number_format(0, 2, '.', ','),
                'gross_profit_net_percent_of_budget' => number_format(0, 2, '.', ','),

                'gp_percent_gross_budget'            => number_format(0, 2, '.', ','),
                'gp_percent_gross_actual'            => number_format(0, 2, '.', ','),
                'gp_percent_gross_variance'          => number_format(0, 2, '.', ','),
                'gp_percent_gross_percent_of_budget' => number_format(0, 2, '.', ','),

                'gp_percent_net_budget'            => number_format(0, 2, '.', ','),
                'gp_percent_net_actual'            => number_format(0, 2, '.', ','),
                'gp_percent_net_variance'          => number_format(0, 2, '.', ','),
                'gp_percent_net_percent_of_budget' => number_format(0, 2, '.', ','),

                'phasedBudgetRows' => [],
            ]
        );
    }

    public function postUnitTradingAccount(Request $request)
    {
        $this->validate($request, [
            'unit_name' => 'required',
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
        ]);

        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $start_date = Carbon::parse($request->from_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->to_date)->format('Y-m-d');
        $fromDate = Carbon::parse($request->from_date)->format('d-m-Y');
        $toDate = Carbon::parse($request->to_date)->format('d-m-Y');

        // Store in cookie
        Cookie::queue('utaUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('utaFromDateCookie', $fromDate, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('utaToDateCookie', $toDate, time() + (10 * 365 * 24 * 60 * 60));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        // Currency
        $unitCurrency = Unit::find($unitId)->currency_id;
        $currencySymbol = Currency::find($unitCurrency)->currency_symbol;

        // Budget Column
        $tradingAccount = TradingAccount::where('unit_id', $unitId)
            ->where('budget_start_date', '<=', $end_date)
            ->where('budget_end_date', '>=', $start_date)
            ->orderBy('trading_account_id', 'desc')
            ->first();

        if (!empty($tradingAccount)) {
            // Budget type
            $budgetType = $tradingAccount->budget_type_id;

            // Calculate from/to indexes for the budget totals
            $budget_from = 1;
            $budget_to = 12;
            $budgetDate = Carbon::parse($tradingAccount->budget_start_date);
            $startMonth = Carbon::parse($start_date)->format('Y-m');
            $endMonth = Carbon::parse($end_date)->format('Y-m');

            for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++) {
                $budgetMonth = $budgetDate->format('Y-m');

                if ($startMonth == $budgetMonth) {
                    $budget_from = $monthIndex;
                }

                if ($endMonth == $budgetMonth) {
                    $budget_to = $monthIndex;
                }

                $budgetDate->addMonth();
            }

            // Head counts
            $head_count = $this->getTotal($tradingAccount, 'head_count_month', $budget_from, $budget_to);

            // Trading days/weeks
            $trading_days = $this->getTotal($tradingAccount, 'num_trading_days_month', $budget_from, $budget_to);
            $weeks = $this->getTotal($tradingAccount, 'num_of_weeks_month', $budget_from, $budget_to);

            // Budget values
            $gross_sales_budget = $this->getTotal($tradingAccount, 'gross_sales_month', $budget_from, $budget_to);
            $net_sales_budget = $this->getTotal($tradingAccount, 'net_sales_month', $budget_from, $budget_to);
            $cost_of_sales_budget = $this->getTotal($tradingAccount, 'cost_of_sales_month', $budget_from, $budget_to);

            // Profit values
            $gross_profit_gross_budget = $gross_sales_budget - $cost_of_sales_budget;
            $gross_profit_net_budget = $net_sales_budget - $cost_of_sales_budget;

            // GP % values
            $gp_percent_gross_budget = $gross_sales_budget != 0 ? ($gross_profit_gross_budget / $gross_sales_budget) * 100 : 0;
            $gp_percent_net_budget = $net_sales_budget != 0 ? ($gross_profit_net_budget / $net_sales_budget) * 100 : 0;

            // Display rows from the Phased Budget
            $allRows = PhasedBudgetUnitRow::$rows;

            // Visible rows for the Unit level (show only Cleaning and Disposables)
            $visibleRows = [ 'cleaning', 'disposables' ];

            // Visible rows for the Admin and Operation level
            if (Gate::allows('operations-user-group')) {
                $visibleRows = PhasedBudgetUnitRow::where('user_id', $userId)
                    ->where('unit_id', $unitId)
                    ->pluck('row_index')
                    ->toArray();
            }

            $phasedBudgetRows = [];
            foreach ($allRows as $field => $title) {
                if (!in_array($field, $visibleRows)) {
                    continue;
                }

                $phasedBudgetRows[] = [
                    'title' => $title,
                    'value' => $this->getTotal($tradingAccount, "{$field}_month", $budget_from, $budget_to),
                ];
            }
        } else {
            $head_count = 0;
            $trading_days = 0;
            $weeks = 0;
            $gross_sales_budget = 0;
            $net_sales_budget = 0;
            $cost_of_sales_budget = 0;
            $gross_profit_gross_budget = 0;
            $gross_profit_net_budget = 0;
            $gp_percent_gross_budget = 0;
            $gp_percent_net_budget = 0;
            $budgetType = 0;
            $phasedBudgetRows = [];
        }

        // Actual column
        $cash_sales_row = DB::table('cash_sales as cs')
            ->select(
                [
                    DB::raw('SUM(cs.z_read * er.exchange_rate) as z_read'),
                    DB::raw('SUM(cs.over_ring * er.exchange_rate) as over_ring'),
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'cs.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = cs.sale_date');
            })
            ->whereBetween('cs.sale_date', [ $start_date, $end_date ])
            ->where('cs.unit_id', $unitId)
            ->first();

        $vending_sales_row = DB::table('vending_sales as vs')
            ->select(
                [
                    DB::raw('SUM(vs.total * er.exchange_rate) as total'),
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'vs.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = vs.sale_date');
            })
            ->whereBetween('vs.sale_date', [ $start_date, $end_date ])
            ->where('vs.unit_id', $unitId)
            ->first();

        $gross_sales_actual = ($cash_sales_row->z_read - $cash_sales_row->over_ring + $vending_sales_row->total);
        $net_sales_actual = (($gross_sales_actual * .9) / 1.09) + (($gross_sales_actual * .1) / 1.23);

        $purchases_row = DB::table('purchases as p')
            ->select(
                [
                    DB::raw('SUM(p.goods * er.exchange_rate) as goods_total'),
                ]
            )
            ->leftJoin('nominal_codes as nc', 'p.net_ext_ID', '=', 'nc.net_ext_ID')
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'p.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = p.date');
            })
            ->whereBetween('p.receipt_invoice_date', [ $start_date, $end_date ])
            ->where('p.unit_id', $unitId)
            ->where('p.deleted', 0)
            ->where('nc.cost_of_sales', 1)
            ->first();

        $cost_of_sales_actual = $purchases_row->goods_total;

        $gross_profit_gross_actual = $gross_sales_actual - $cost_of_sales_actual;
        $gross_profit_net_actual = $net_sales_actual - $cost_of_sales_actual;

        $gp_percent_gross_actual = $gross_sales_actual != 0 ? ($gross_profit_gross_actual / $gross_sales_actual) * 100 : 0;
        $gp_percent_net_actual = $net_sales_actual != 0 ? ($gross_profit_net_actual / $net_sales_actual) * 100 : 0;

        // Variance column
        $gross_sales_variance = $gross_sales_budget - $gross_sales_actual;
        $net_sales_variance = $net_sales_budget - $net_sales_actual;
        $cost_of_sales_variance = $cost_of_sales_budget - $cost_of_sales_actual;
        $gross_profit_gross_variance = $gross_profit_gross_budget - $gross_profit_gross_actual;
        $gross_profit_net_variance = $gross_profit_net_budget - $gross_profit_net_actual;
        $gp_percent_gross_variance = $gp_percent_gross_budget - $gp_percent_gross_actual;
        $gp_percent_net_variance = $gp_percent_net_budget - $gp_percent_net_actual;

        // % of Budget column
        $gross_sales_percent_of_budget = $gross_sales_budget != 0 ? ($gross_sales_actual / $gross_sales_budget) * 100 : 0;
        $net_sales_percent_of_budget = $net_sales_budget != 0 ? ($net_sales_actual / $net_sales_budget) * 100 : 0;
        $cost_of_sales_percent_of_budget = $cost_of_sales_budget != 0 ? ($cost_of_sales_actual / $cost_of_sales_budget) * 100 : 0;
        $gross_profit_gross_percent_of_budget = $gross_profit_gross_budget != 0 ? ($gross_profit_gross_actual / $gross_profit_gross_budget) * 100 : 0;
        $gross_profit_net_percent_of_budget = $gross_profit_net_budget != 0 ? ($gross_profit_net_actual / $gross_profit_net_budget) * 100 : 0;
        $gp_percent_gross_percent_of_budget = $gp_percent_gross_budget != 0 ? ($gp_percent_gross_actual / $gp_percent_gross_budget) * 100 : 0;
        $gp_percent_net_percent_of_budget = $gp_percent_net_budget != 0 ? ($gp_percent_net_actual / $gp_percent_net_budget) * 100 : 0;

        return view(
            'reports.unit-trading-account.index', [
                'userUnits'       => $userUnits,
                'selectedUnit'    => $unitId,
                'fromDate'        => Carbon::parse($start_date)->format('d-m-Y'),
                'toDate'          => Carbon::parse($end_date)->format('d-m-Y'),
                'head_count'      => $head_count,
                'trading_days'    => $trading_days,
                'weeks'           => $weeks,
                'budgetType'      => $budgetType,
                'backUrl'         => session()->get('backUrl', '/'),
                'currency_symbol' => $currencySymbol,

                'gross_sales_budget'            => number_format($gross_sales_budget, 2, '.', ','),
                'gross_sales_actual'            => number_format($gross_sales_actual, 2, '.', ','),
                'gross_sales_variance'          => number_format($gross_sales_variance, 2, '.', ','),
                'gross_sales_percent_of_budget' => number_format($gross_sales_percent_of_budget, 2, '.', ','),

                'net_sales_budget'            => number_format($net_sales_budget, 2, '.', ','),
                'net_sales_actual'            => number_format($net_sales_actual, 2, '.', ','),
                'net_sales_variance'          => number_format($net_sales_variance, 2, '.', ','),
                'net_sales_percent_of_budget' => number_format($net_sales_percent_of_budget, 2, '.', ','),

                'cost_of_sales_budget'            => number_format($cost_of_sales_budget, 2, '.', ','),
                'cost_of_sales_actual'            => number_format($cost_of_sales_actual, 2, '.', ','),
                'cost_of_sales_variance'          => number_format($cost_of_sales_variance, 2, '.', ','),
                'cost_of_sales_percent_of_budget' => number_format($cost_of_sales_percent_of_budget, 2, '.', ','),

                'gross_profit_gross_budget'            => number_format($gross_profit_gross_budget, 2, '.', ','),
                'gross_profit_gross_actual'            => number_format($gross_profit_gross_actual, 2, '.', ','),
                'gross_profit_gross_variance'          => number_format($gross_profit_gross_variance, 2, '.', ','),
                'gross_profit_gross_percent_of_budget' => number_format($gross_profit_gross_percent_of_budget, 2, '.', ','),

                'gross_profit_net_budget'            => number_format($gross_profit_net_budget, 2, '.', ','),
                'gross_profit_net_actual'            => number_format($gross_profit_net_actual, 2, '.', ','),
                'gross_profit_net_variance'          => number_format($gross_profit_net_variance, 2, '.', ','),
                'gross_profit_net_percent_of_budget' => number_format($gross_profit_net_percent_of_budget, 2, '.', ','),

                'gp_percent_gross_budget'            => number_format($gp_percent_gross_budget, 0, '.', ','),
                'gp_percent_gross_actual'            => number_format($gp_percent_gross_actual, 0, '.', ','),
                'gp_percent_gross_variance'          => number_format($gp_percent_gross_variance, 0, '.', ','),
                'gp_percent_gross_percent_of_budget' => number_format($gp_percent_gross_percent_of_budget, 0, '.', ','),

                'gp_percent_net_budget'            => number_format($gp_percent_net_budget, 0, '.', ','),
                'gp_percent_net_actual'            => number_format($gp_percent_net_actual, 0, '.', ','),
                'gp_percent_net_variance'          => number_format($gp_percent_net_variance, 0, '.', ','),
                'gp_percent_net_percent_of_budget' => number_format($gp_percent_net_percent_of_budget, 0, '.', ','),

                'phasedBudgetRows' => $phasedBudgetRows,
            ]
        );
    }

    /**
     * Unit Trading Account Stock Report.
     */
    public function unitTradingAccountStock()
    {
        $unitId = Cookie::get('utasUnitIdCookie', '');
        $fromDate = Cookie::get('utasFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('utasToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.unit-trading-account-stock.index', [
                'userUnits'       => $userUnits,
                'selectedUnit'    => $unitId,
                'fromDate'        => $fromDate,
                'toDate'          => $toDate,
                'head_count'      => 0,
                'trading_days'    => 0,
                'weeks'           => 0,
                'budgetType'      => '',
                'backUrl'         => session()->get('backUrl', '/'),
                'currency_symbol' => '',

                'gross_sales_budget'            => number_format(0, 2, '.', ','),
                'gross_sales_actual'            => number_format(0, 2, '.', ','),
                'gross_sales_variance'          => number_format(0, 2, '.', ','),
                'gross_sales_percent_of_budget' => number_format(0, 2, '.', ','),

                'net_sales_budget'            => number_format(0, 2, '.', ','),
                'net_sales_actual'            => number_format(0, 2, '.', ','),
                'net_sales_variance'          => number_format(0, 2, '.', ','),
                'net_sales_percent_of_budget' => number_format(0, 2, '.', ','),

                'cost_of_sales_budget'            => number_format(0, 2, '.', ','),
                'cost_of_sales_actual'            => number_format(0, 2, '.', ','),
                'cost_of_sales_purchases'         => number_format(0, 2, '.', ','),
                'cost_of_sales_stock_delta'       => number_format(0, 2, '.', ','),
                'cost_of_sales_variance'          => number_format(0, 2, '.', ','),
                'cost_of_sales_percent_of_budget' => number_format(0, 2, '.', ','),

                'gross_profit_gross_budget'            => number_format(0, 2, '.', ','),
                'gross_profit_gross_actual'            => number_format(0, 2, '.', ','),
                'gross_profit_gross_variance'          => number_format(0, 2, '.', ','),
                'gross_profit_gross_percent_of_budget' => number_format(0, 2, '.', ','),

                'gross_profit_net_budget'            => number_format(0, 2, '.', ','),
                'gross_profit_net_actual'            => number_format(0, 2, '.', ','),
                'gross_profit_net_variance'          => number_format(0, 2, '.', ','),
                'gross_profit_net_percent_of_budget' => number_format(0, 2, '.', ','),

                'gp_percent_gross_budget'            => number_format(0, 2, '.', ','),
                'gp_percent_gross_actual'            => number_format(0, 2, '.', ','),
                'gp_percent_gross_variance'          => number_format(0, 2, '.', ','),
                'gp_percent_gross_percent_of_budget' => number_format(0, 2, '.', ','),

                'gp_percent_net_budget'            => number_format(0, 2, '.', ','),
                'gp_percent_net_actual'            => number_format(0, 2, '.', ','),
                'gp_percent_net_variance'          => number_format(0, 2, '.', ','),
                'gp_percent_net_percent_of_budget' => number_format(0, 2, '.', ','),

                'phasedBudgetRows' => [],
            ]
        );
    }

    public function postUnitTradingAccountStock(Request $request)
    {
        $this->validate($request, [
            'unit_name' => 'required',
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
        ]);

        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $start_date = Carbon::parse($request->from_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->to_date)->format('Y-m-d');
        $fromDate = Carbon::parse($request->from_date)->format('d-m-Y');
        $toDate = Carbon::parse($request->to_date)->format('d-m-Y');

        // Store in cookie
        Cookie::queue('utasUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('utasFromDateCookie', $fromDate, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('utasToDateCookie', $toDate, time() + (10 * 365 * 24 * 60 * 60));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        // Currency
        $unitCurrency = Unit::find($unitId)->currency_id;
        $currencySymbol = Currency::find($unitCurrency)->currency_symbol;

        // Budget Column
        $tradingAccount = TradingAccount::where('unit_id', $unitId)
            ->where('budget_start_date', '<=', $end_date)
            ->where('budget_end_date', '>=', $start_date)
            ->orderBy('trading_account_id', 'desc')
            ->first();

        if (!empty($tradingAccount)) {
            // Budget type
            $budgetType = $tradingAccount->budget_type_id;

            // Calculate from/to indexes for the budget totals
            $budget_from = 1;
            $budget_to = 12;
            $budgetDate = Carbon::parse($tradingAccount->budget_start_date);
            $startMonth = Carbon::parse($start_date)->format('Y-m');
            $endMonth = Carbon::parse($end_date)->format('Y-m');

            for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++) {
                $budgetMonth = $budgetDate->format('Y-m');

                if ($startMonth == $budgetMonth) {
                    $budget_from = $monthIndex;
                }

                if ($endMonth == $budgetMonth) {
                    $budget_to = $monthIndex;
                }

                $budgetDate->addMonth();
            }

            // head counts
            $head_count = $this->getTotal($tradingAccount, 'head_count_month', $budget_from, $budget_to);

            // Trading days/weeks
            $trading_days = $this->getTotal($tradingAccount, 'num_trading_days_month', $budget_from, $budget_to);
            $weeks = $this->getTotal($tradingAccount, 'num_of_weeks_month', $budget_from, $budget_to);

            // Budget values
            $gross_sales_budget = $this->getTotal($tradingAccount, 'gross_sales_month', $budget_from, $budget_to);
            $net_sales_budget = $this->getTotal($tradingAccount, 'net_sales_month', $budget_from, $budget_to);
            $cost_of_sales_budget = $this->getTotal($tradingAccount, 'cost_of_sales_month', $budget_from, $budget_to);

            // Profit values
            $gross_profit_gross_budget = $gross_sales_budget - $cost_of_sales_budget;
            $gross_profit_net_budget = $net_sales_budget - $cost_of_sales_budget;

            // GP % values
            $gp_percent_gross_budget = $gross_sales_budget != 0 ? ($gross_profit_gross_budget / $gross_sales_budget) * 100 : 0;
            $gp_percent_net_budget = $net_sales_budget != 0 ? ($gross_profit_net_budget / $net_sales_budget) * 100 : 0;

            // Display rows from the Phased Budget
            $allRows = PhasedBudgetUnitRow::$rows;

            // Visible rows for the Unit level (show only Cleaning and Disposables)
            $visibleRows = [ 'cleaning', 'disposables' ];

            // Visible rows for the Admin and Operation level
            if (Gate::allows('operations-user-group')) {
                $visibleRows = PhasedBudgetUnitRow::where('user_id', $userId)
                    ->where('unit_id', $unitId)
                    ->pluck('row_index')
                    ->toArray();
            }

            $phasedBudgetRows = [];
            foreach ($allRows as $field => $title) {
                if (!in_array($field, $visibleRows)) {
                    continue;
                }

                $phasedBudgetRows[] = [
                    'title' => $title,
                    'value' => $this->getTotal($tradingAccount, "{$field}_month", $budget_from, $budget_to),
                ];
            }
        } else {
            $head_count = 0;
            $trading_days = 0;
            $weeks = 0;
            $gross_sales_budget = 0;
            $net_sales_budget = 0;
            $cost_of_sales_budget = 0;
            $gross_profit_gross_budget = 0;
            $gross_profit_net_budget = 0;
            $gp_percent_gross_budget = 0;
            $gp_percent_net_budget = 0;
            $budgetType = 0;
            $phasedBudgetRows = [];
        }

        // Actual column
        $cash_sales_row = DB::table('cash_sales as cs')
            ->select(
                [
                    DB::raw('SUM(cs.z_read * er.exchange_rate) as z_read'),
                    DB::raw('SUM(cs.over_ring * er.exchange_rate) as over_ring'),
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'cs.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = cs.sale_date');
            })
            ->whereBetween('cs.sale_date', [ $start_date, $end_date ])
            ->where('cs.unit_id', $unitId)
            ->first();

        $vending_sales_row = DB::table('vending_sales as vs')
            ->select(
                [
                    DB::raw('SUM(vs.total * er.exchange_rate) as total'),
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'vs.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = vs.sale_date');
            })
            ->whereBetween('vs.sale_date', [ $start_date, $end_date ])
            ->where('vs.unit_id', $unitId)
            ->first();

        $gross_sales_actual = ($cash_sales_row->z_read - $cash_sales_row->over_ring + $vending_sales_row->total);
        $net_sales_actual = (($gross_sales_actual * .9) / 1.09) + (($gross_sales_actual * .1) / 1.23);

        $purchases_row = DB::table('purchases as p')
            ->select(
                [
                    DB::raw('SUM(p.goods * er.exchange_rate) as goods_total'),
                ]
            )
            ->leftJoin('nominal_codes as nc', 'p.net_ext_ID', '=', 'nc.net_ext_ID')
            ->leftJoin('exchange_rates as er', function ($join) use ($unitCurrency)
            {
                $join->on('er.domestic_currency_id', 'p.currency_id')
                    ->where('er.foreign_currency_id', $unitCurrency)
                    ->whereRaw('er.date = p.date');
            })
            ->whereBetween('p.receipt_invoice_date', [ $start_date, $end_date ])
            ->where('p.unit_id', $unitId)
            ->where('p.deleted', 0)
            ->where('nc.cost_of_sales', 1)
            ->first();

        $stock_control_row = DB::table('stock_control')
            ->select(
                [
                    DB::raw('SUM(foods_delta + minerals_delta + choc_snacks_delta + vending_delta) as stock_delta'),
                ]
            )
            ->where('unit_id', $unitId)
            ->whereBetween('stock_take_date', [ $start_date, $end_date ])
            ->first();

        $purchases_goods_total = $purchases_row->goods_total;
        $stock_delta = $stock_control_row->stock_delta;
        $cost_of_sales_actual = $purchases_goods_total + $stock_delta;

        $gross_profit_gross_actual = $gross_sales_actual - $cost_of_sales_actual;
        $gross_profit_net_actual = $net_sales_actual - $cost_of_sales_actual;

        $gp_percent_gross_actual = $gross_sales_actual != 0 ? ($gross_profit_gross_actual / $gross_sales_actual) * 100 : 0;
        $gp_percent_net_actual = $net_sales_actual != 0 ? ($gross_profit_net_actual / $net_sales_actual) * 100 : 0;

        // Variance column
        $gross_sales_variance = $gross_sales_budget - $gross_sales_actual;
        $net_sales_variance = $net_sales_budget - $net_sales_actual;
        $cost_of_sales_variance = $cost_of_sales_budget - $cost_of_sales_actual;
        $gross_profit_gross_variance = $gross_profit_gross_budget - $gross_profit_gross_actual;
        $gross_profit_net_variance = $gross_profit_net_budget - $gross_profit_net_actual;
        $gp_percent_gross_variance = $gp_percent_gross_budget - $gp_percent_gross_actual;
        $gp_percent_net_variance = $gp_percent_net_budget - $gp_percent_net_actual;

        // % of Budget column
        $gross_sales_percent_of_budget = $gross_sales_budget != 0 ? ($gross_sales_actual / $gross_sales_budget) * 100 : 0;
        $net_sales_percent_of_budget = $net_sales_budget != 0 ? ($net_sales_actual / $net_sales_budget) * 100 : 0;
        $cost_of_sales_percent_of_budget = $cost_of_sales_budget != 0 ? ($cost_of_sales_actual / $cost_of_sales_budget) * 100 : 0;
        $gross_profit_gross_percent_of_budget = $gross_profit_gross_budget != 0 ? ($gross_profit_gross_actual / $gross_profit_gross_budget) * 100 : 0;
        $gross_profit_net_percent_of_budget = $gross_profit_net_budget != 0 ? ($gross_profit_net_actual / $gross_profit_net_budget) * 100 : 0;
        $gp_percent_gross_percent_of_budget = $gp_percent_gross_budget != 0 ? ($gp_percent_gross_actual / $gp_percent_gross_budget) * 100 : 0;
        $gp_percent_net_percent_of_budget = $gp_percent_net_budget != 0 ? ($gp_percent_net_actual / $gp_percent_net_budget) * 100 : 0;

        return view(
            'reports.unit-trading-account-stock.index', [
                'userUnits'       => $userUnits,
                'selectedUnit'    => $unitId,
                'fromDate'        => $start_date,
                'toDate'          => $end_date,
                'head_count'      => $head_count,
                'trading_days'    => $trading_days,
                'weeks'           => $weeks,
                'budgetType'      => $budgetType,
                'backUrl'         => session()->get('backUrl', '/'),
                'currency_symbol' => $currencySymbol,

                'gross_sales_budget'            => number_format($gross_sales_budget, 2, '.', ','),
                'gross_sales_actual'            => number_format($gross_sales_actual, 2, '.', ','),
                'gross_sales_variance'          => number_format($gross_sales_variance, 2, '.', ','),
                'gross_sales_percent_of_budget' => number_format($gross_sales_percent_of_budget, 2, '.', ','),

                'net_sales_budget'            => number_format($net_sales_budget, 2, '.', ','),
                'net_sales_actual'            => number_format($net_sales_actual, 2, '.', ','),
                'net_sales_variance'          => number_format($net_sales_variance, 2, '.', ','),
                'net_sales_percent_of_budget' => number_format($net_sales_percent_of_budget, 2, '.', ','),

                'cost_of_sales_budget'            => number_format($cost_of_sales_budget, 2, '.', ','),
                'cost_of_sales_actual'            => number_format($cost_of_sales_actual, 2, '.', ','),
                'cost_of_sales_purchases'         => number_format($purchases_goods_total, 2, '.', ','),
                'cost_of_sales_stock_delta'       => number_format($stock_delta, 2, '.', ','),
                'cost_of_sales_variance'          => number_format($cost_of_sales_variance, 2, '.', ','),
                'cost_of_sales_percent_of_budget' => number_format($cost_of_sales_percent_of_budget, 2, '.', ','),

                'gross_profit_gross_budget'            => number_format($gross_profit_gross_budget, 2, '.', ','),
                'gross_profit_gross_actual'            => number_format($gross_profit_gross_actual, 2, '.', ','),
                'gross_profit_gross_variance'          => number_format($gross_profit_gross_variance, 2, '.', ','),
                'gross_profit_gross_percent_of_budget' => number_format($gross_profit_gross_percent_of_budget, 2, '.', ','),

                'gross_profit_net_budget'            => number_format($gross_profit_net_budget, 2, '.', ','),
                'gross_profit_net_actual'            => number_format($gross_profit_net_actual, 2, '.', ','),
                'gross_profit_net_variance'          => number_format($gross_profit_net_variance, 2, '.', ','),
                'gross_profit_net_percent_of_budget' => number_format($gross_profit_net_percent_of_budget, 2, '.', ','),

                'gp_percent_gross_budget'            => number_format($gp_percent_gross_budget, 0, '.', ','),
                'gp_percent_gross_actual'            => number_format($gp_percent_gross_actual, 0, '.', ','),
                'gp_percent_gross_variance'          => number_format($gp_percent_gross_variance, 0, '.', ','),
                'gp_percent_gross_percent_of_budget' => number_format($gp_percent_gross_percent_of_budget, 0, '.', ','),

                'gp_percent_net_budget'            => number_format($gp_percent_net_budget, 0, '.', ','),
                'gp_percent_net_actual'            => number_format($gp_percent_net_actual, 0, '.', ','),
                'gp_percent_net_variance'          => number_format($gp_percent_net_variance, 0, '.', ','),
                'gp_percent_net_percent_of_budget' => number_format($gp_percent_net_percent_of_budget, 0, '.', ','),

                'phasedBudgetRows' => $phasedBudgetRows,
            ]
        );
    }

    /**
     * Labour Hours Report.
     */
    public function labourHours()
    {
        $unitId = Cookie::get('labourHoursReportUnitIdCookie', '');
        $fromDate = Cookie::get('labourHoursReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('labourHoursReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.labour-hours.index', [
                'userUnits'    => $userUnits,
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function labourHoursGrid(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Store in cookie
        Cookie::queue('labourHoursReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('labourHoursReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('labourHoursReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'labour-hours')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        return view(
            'reports.labour-hours.grid', [
                'userId'      => $userId,
                'unitId'      => $unitId,
                'fromDate'    => $fromDate,
                'toDate'      => $toDate,
                'allRecords'  => $allRecords,
                'notVisiable' => $hiddenColumns,
                'isSuLevel'   => Gate::allows('su-user-group'),
            ]
        );
    }

    public function labourHoursGridJson(Request $request)
    {
        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ]);
            } else {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ])
                    ->whereBetween('labour_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ]);
            } else {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ])
                    ->whereBetween('labour_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $labourHours = \DB::table('labour_hours AS LH')
                ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                ->select([ 'LH.id',
                    'LH.unique_id',
                    'UN.unit_name',
                    'LH.supervisor',
                    'LH.labour_hours',
                    'LH.labour_date',
                    'LT.labour_type',
                    'LH.id' ])
                ->whereIn('LH.unit_id', $userUnits)
                ->whereBetween('labour_date', [ $fromDate, $toDate ]);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $labourHours = \DB::table('labour_hours AS LH')
                ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                ->select([ 'LH.id',
                    'LH.unique_id',
                    'UN.unit_name',
                    'LH.supervisor',
                    'LH.labour_hours',
                    'LH.labour_date',
                    'LT.labour_type',
                    'LH.id' ])
                ->whereIn('LH.unit_id', $userUnits)
                ->whereBetween('labour_date', [ $fromDate, $toDate ]);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ])
                    ->where('LH.unit_id', $unitId);
            } else {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ])
                    ->where('LH.unit_id', $unitId)
                    ->whereBetween('labour_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ])
                    ->where('LH.unit_id', $unitId);
            } else {
                $labourHours = \DB::table('labour_hours AS LH')
                    ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                    ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                    ->select([ 'LH.id',
                        'LH.unique_id',
                        'UN.unit_name',
                        'LH.supervisor',
                        'LH.labour_hours',
                        'LH.labour_date',
                        'LT.labour_type',
                        'LH.id' ])
                    ->where('LH.unit_id', $unitId)
                    ->whereBetween('labour_date', [ $fromDate, $toDate ]);
            }
        } else {
            $labourHours = \DB::table('labour_hours AS LH')
                ->leftJoin('labour_types AS LT', 'LH.labour_type_id', '=', 'LT.id')
                ->leftJoin('units AS UN', 'LH.unit_id', '=', 'UN.unit_id')
                ->select([ 'LH.id',
                    'LH.id',
                    'LH.unique_id',
                    'UN.unit_name',
                    'LH.supervisor',
                    'LH.labour_hours',
                    'LH.labour_date',
                    'LT.labour_type',
                    'LH.id' ])
                ->where('LH.unit_id', $unitId)
                ->whereBetween('labour_date', [ $fromDate, $toDate ]);
        }

        if (Gate::allows('su-user-group')) {
            return Datatables::of($labourHours)
                ->setRowId(function ($labourHour)
                {
                    return 'tr_' . $labourHour->id;
                })
                ->addColumn('checkbox', function ($labourHour)
                {
                    return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $labourHour->id . '">';
                }, 0)
                ->addColumn('action', function ($labourHour)
                {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
                })
                ->editColumn('unique_id', function ($labourHour)
                {
                    return '<a target="_blank" href="/sheets/labour-hours/' . $labourHour->unique_id . '">' . $labourHour->unique_id . '</a>';
                })
                ->editColumn('labour_date', function ($labourHour)
                {
                    return $labourHour->labour_date ? with(new Carbon($labourHour->labour_date))->format('d-m-Y') : '';
                })
                ->filterColumn('LH.labour_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(labour_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        } else {
            return Datatables::of($labourHours)
                ->setRowId(function ($labourHour)
                {
                    return 'tr_' . $labourHour->id;
                })
                ->addColumn('action', function ($labourHour)
                {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
                })
                ->editColumn('unique_id', function ($labourHour)
                {
                    return '<a target="_blank" href="/sheets/labour-hours/' . $labourHour->unique_id . '">' . $labourHour->unique_id . '</a>';
                })
                ->editColumn('labour_date', function ($labourHour)
                {
                    return $labourHour->labour_date ? with(new Carbon($labourHour->labour_date))->format('d-m-Y') : '';
                })
                ->filterColumn('LH.labour_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(labour_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        }
    }

    public function deleteRecord($id)
    {
        $labourHoursIds = explode(',', $id);

        foreach ($labourHoursIds as $labourHoursId) {
            $labourHour = \App\LabourHour::find($labourHoursId);
            $labourHour->delete();
        }

        echo $id;
    }

    /**
     * Stock Control Report.
     */
    public function stockControl()
    {
        $unitId = Cookie::get('stockControlReportUnitIdCookie', '');
        $fromDate = Cookie::get('stockControlReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('stockControlReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.stock-control.index', [
                'userUnits'    => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function stockControlGrid(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Store in cookie
        Cookie::queue('stockControlReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('stockControlReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('stockControlReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'stock-control')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        return view(
            'reports.stock-control.grid', [
                'userId'      => $userId,
                'unitId'      => $unitId,
                'fromDate'    => $fromDate,
                'toDate'      => $toDate,
                'allRecords'  => $allRecords,
                'notVisiable' => $hiddenColumns,
                'isSuLevel'   => Gate::allows('su-user-group'),
            ]
        );
    }

    public function stockControlGridJson(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('(SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta) AS pfmsv_total'),
                        \DB::raw('(SC.foods + SC.minerals + SC.choc_snacks + SC.vending) AS fmsv_total'),
                        \DB::raw('(SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta) AS fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('(SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta) AS pcdf_total'),
                        \DB::raw('(SC.chemicals + SC.clean_disp + SC.free_issues) AS cdf_total'),
                        \DB::raw('(SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta) AS cdf_total_delta'),
                        \DB::raw('(SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta) AS poa_total'),
                        \DB::raw('(SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues) AS oa_total'),
                        \DB::raw('(SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta) AS oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ]);
            } else {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ])
                    ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ]);
            } else {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ])
                    ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $stockControl = \DB::table('stock_control AS SC')
                ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                ->select([ 'SC.stock_control_id',
                    'UN.unit_name',
                    'U.username',
                    'SC.stock_take_date',
                    'SC.foods',
                    'SC.minerals',
                    'SC.choc_snacks',
                    'SC.vending',
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                    \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                    'SC.chemicals',
                    'SC.clean_disp',
                    'SC.free_issues',
                    \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                    \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                    \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                    \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                    'SC.comments',
                    'SC.stock_control_id' ])
                ->whereIn('SC.unit_id', $userUnits)
                ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $stockControl = \DB::table('stock_control AS SC')
                ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                ->select([ 'SC.stock_control_id',
                    'UN.unit_name',
                    'U.username',
                    'SC.stock_take_date',
                    'SC.foods',
                    'SC.minerals',
                    'SC.choc_snacks',
                    'SC.vending',
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                    \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                    'SC.chemicals',
                    'SC.clean_disp',
                    'SC.free_issues',
                    \DB::raw('(SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta) pcdf_total'),
                    \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                    \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                    \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                    'SC.comments',
                    'SC.stock_control_id' ])
                ->whereIn('SC.unit_id', $userUnits)
                ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ])
                    ->where('SC.unit_id', $unitId);
            } else {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ])
                    ->where('SC.unit_id', $unitId)
                    ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ])
                    ->where('SC.unit_id', $unitId);
            } else {
                $stockControl = \DB::table('stock_control AS SC')
                    ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                    ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                    ->select([ 'SC.stock_control_id',
                        'UN.unit_name',
                        'U.username',
                        'SC.stock_take_date',
                        'SC.foods',
                        'SC.minerals',
                        'SC.choc_snacks',
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                        'SC.vending',
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                        'SC.chemicals',
                        'SC.clean_disp',
                        'SC.free_issues',
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                        \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                        \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                        \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                        \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                        'SC.comments',
                        'SC.stock_control_id' ])
                    ->where('SC.unit_id', $unitId)
                    ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
            }
        } else {
            $stockControl = \DB::table('stock_control AS SC')
                ->leftJoin('users AS U', 'SC.user_id', '=', 'U.user_id')
                ->leftJoin('units AS UN', 'SC.unit_id', '=', 'UN.unit_id')
                ->select([ 'SC.stock_control_id',
                    'SC.stock_control_id',
                    'UN.unit_name',
                    'U.username',
                    'SC.stock_take_date',
                    'SC.foods',
                    'SC.minerals',
                    'SC.choc_snacks',
                    'SC.vending',
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta pfmsv_total'),
                    'SC.vending',
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending fmsv_total'),
                    \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta fmsv_total_delta'),
                    'SC.chemicals',
                    'SC.clean_disp',
                    'SC.free_issues',
                    \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta pcdf_total'),
                    \DB::raw('SC.chemicals + SC.clean_disp + SC.free_issues cdf_total'),
                    \DB::raw('SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta cdf_total_delta'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending - SC.foods_delta - SC.minerals_delta - SC.choc_snacks_delta - SC.vending_delta + SC.chemicals + SC.clean_disp + SC.free_issues - SC.chemicals_delta - SC.clean_disp_delta - SC.free_issues_delta poa_total'),
                    \DB::raw('SC.foods + SC.minerals + SC.choc_snacks + SC.vending + SC.chemicals + SC.clean_disp + SC.free_issues oa_total'),
                    \DB::raw('SC.foods_delta + SC.minerals_delta + SC.choc_snacks_delta + SC.vending_delta + SC.chemicals_delta + SC.clean_disp_delta + SC.free_issues_delta oa_total_delta'),
                    'SC.comments',
                    'SC.stock_control_id' ])
                ->where('SC.unit_id', $unitId)
                ->whereBetween('stock_take_date', [ $fromDate, $toDate ]);
        }

        if (Gate::allows('su-user-group')) {
            return Datatables::of($stockControl)
                ->setRowId(function ($stockControl)
                {
                    return 'tr_' . $stockControl->stock_control_id;
                })
                ->addColumn('checkbox', function ($stockControl)
                {
                    return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $stockControl->stock_control_id . '">';
                }, 0)
                ->addColumn('action', function ($stockControl)
                {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
                })
                ->editColumn('stock_take_date', function ($stockControl)
                {
                    return $stockControl->stock_take_date ? with(new Carbon($stockControl->stock_take_date))->format('d-m-Y') : '';
                })
                ->filterColumn('SC.stock_take_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(stock_take_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        } else {
            return Datatables::of($stockControl)
                ->setRowId(function ($stockControl)
                {
                    return 'tr_' . $stockControl->stock_control_id;
                })
                ->addColumn('action', function ($stockControl)
                {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
                })
                ->editColumn('stock_take_date', function ($stockControl)
                {
                    return $stockControl->stock_take_date ? with(new Carbon($stockControl->stock_take_date))->format('d-m-Y') : '';
                })
                ->filterColumn('SC.stock_take_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(stock_take_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        }
    }

    public function deleteStockControlRecord($id)
    {
        $stockControlIds = explode(',', $id);

        foreach ($stockControlIds as $stockControlId) {
            $StockControl = \App\StockControl::find($stockControlId);
            $StockControl->delete();
        }

        echo $id;
    }

    /**
     * Purchases Summary Report.
     */
    public function purchasesSummary()
    {
        $unitId = Cookie::get('purchasesSummaryUnitIdCookie', '');
        $fromDate = Cookie::get('purchasesSummaryFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('purchasesSummaryToDateCookie', Carbon::now()->format('d-m-Y'));
        $purchaseType = Cookie::get('purchasesSummaryPurchaseTypeCookie', '');

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.purchases-summary.index', [
                'userUnits'            => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit'         => $unitId,
                'fromDate'             => $fromDate,
                'toDate'               => $toDate,
                'selectedPurchaseType' => $purchaseType,
                'backUrl'              => session()->get('backUrl', '/'),
            ]
        );
    }

    public function purchasesSummaryGrid(Request $request)
    {
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $purchaseType = $request->purchase_type;

        // Track event
        Event::trackAction('Run Purchases Summary Report');

        // Store in cookie
        Cookie::queue('purchasesSummaryUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesSummaryFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesSummaryToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesSummaryPurchaseTypeCookie', $purchaseType, time() + (10 * 365 * 24 * 60 * 60));

        $uniqueIds = [];
        $goodsTotal = 0;
        $vatTotal = 0;
        $grossTotal = 0;

        $unitIDStr = $unitId != '' ? ' AND p.unit_id = ' . $unitId : '';
        $purchaseTypeStr = $purchaseType != 'both' ? ' AND p.purch_type = "' . $purchaseType . '"' : '';

        $purchases = \DB::table('purchases AS P')
            ->select('P.unique_id', 'P.purch_type', 'P.supplier', 'P.reference_invoice_number', \DB::raw('DATE_FORMAT(P.receipt_invoice_date,"%d-%m-%Y") receipt_invoice_date'), 'P.purchase_details', 'P.net_ext_ID', 'P.goods', 'UN.unit_name', 'P.goods_total', 'P.vat_total', 'P.gross_total')
            ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
            ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
            ->when($unitId, function ($query) use ($unitId)
            {
                return $query->where('P.unit_id', $unitId);
            })
            ->when(isset($purchaseType) && $purchaseType != 'both', function ($query) use ($purchaseType)
            {
                return $query->where('purch_type', $purchaseType);
            })
            ->where('P.deleted', 0)
            ->get();

        $nominalCodes = \DB::table('nominal_codes')
            ->select('*',
                \DB::raw("
                            (SELECT COUNT(*) FROM purchases p WHERE nominal_codes.net_ext_ID = p.net_ext_ID AND p.receipt_invoice_date
                            BETWEEN '$fromDate' AND '$toDate' $unitIDStr $purchaseTypeStr AND deleted = 0) AS total
                        ")
            )
            ->having('total', '>', 0)
            ->get();

        $nominalCodesArr = [];
        foreach ($nominalCodes as $nc) {
            $nominalCodesArr[$nc->net_ext_ID] = [ 'title' => $nc->net_ext, 'total' => 0 ];
        }

        $table = '
            <table class="table table-hover table-striped margin-bottom-0">
                <thead>
                    <tr>
                        <th class="text-center">Sheet ID</th>
                        <th class="text-center">Unit Name</th>
                        <th class="text-center">Purch Type</th>
                        <th class="text-center">Supplier</th>
                        <th class="text-center">Inv/Ref #</th>
                        <th class="text-center">Inv/Rcpt date</th>
                        <th class="text-center">Purch Details</th>';

        foreach ($nominalCodesArr as $ncVal) {
            $table .= '<th class="text-center">' . ucwords($ncVal['title']) . '</th>';
        }

        $table .= '
                        <th class="text-center">Total Goods</th>
                        <th class="text-center">Total VAT</th>
                        <th class="text-center">Total Gross</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($purchases as $pVal) {
            $table .= '<tr>
                <td>' . $pVal->unique_id . '</td>
                <td>' . $pVal->unit_name . '</td>
                <td>' . $pVal->purch_type . '</td>
                <td>' . $pVal->supplier . '</td>
                <td>' . $pVal->reference_invoice_number . '</td>
                <td class="text-center">' . $pVal->receipt_invoice_date . '</td>
                <td>' . $pVal->purchase_details . '</td>';

            foreach ($nominalCodesArr as $ncKey => $ncVal) {
                $table .= '<td>';
                if ($ncKey == $pVal->net_ext_ID) {
                    $table .= \Helpers::formatEuroAmounts($pVal->goods);
                    $nominalCodesArr[$ncKey]['total'] += $pVal->goods;
                } else
                    $table .= \Helpers::formatEuroAmounts('0.00');
                $table .= '</td>';
            }

            if (!in_array($pVal->unique_id, $uniqueIds)) {
                $uniqueIds[] = $pVal->unique_id;
                $goodsTotal += $pVal->goods_total;
                $vatTotal += $pVal->vat_total;
                $grossTotal += $pVal->gross_total;
                $table .= '<td>' . \Helpers::formatEuroAmounts($pVal->goods_total) . '</td>
                    <td>' . \Helpers::formatEuroAmounts($pVal->vat_total) . '</td>
                    <td>' . \Helpers::formatEuroAmounts($pVal->gross_total) . '</td>';
            } else {
                $table .= '<td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>';
            }

            $table .= '
                </tr>';
        }

        $table .= '<tr><td colspan="7"><strong>Totals</strong></td>';
        foreach ($nominalCodesArr as $ncVal) {
            $table .= '<td><strong>' . \Helpers::formatEuroAmounts($ncVal['total']) . '</strong></td>';
        }

        $table .= '<td><strong>' . \Helpers::formatEuroAmounts($goodsTotal) . '</strong></td>';
        $table .= '<td><strong>' . \Helpers::formatEuroAmounts($vatTotal) . '</strong></td>';
        $table .= '<td><strong>' . \Helpers::formatEuroAmounts($grossTotal) . '</strong></td>';
        $table .= '</tr>';

        $table .= '
                </tbody>
            </table>
        ';

        return view(
            'reports.purchases-summary.grid', [
                'unitId'       => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'purchaseType' => $purchaseType,
                'table'        => $table,
            ]
        );
    }

    public function exportToCSV(Request $request)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Purchases-Summary-Report-for-' . date('d-m-Y') . '.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        $output = fopen('php://output', 'w');

        $headerRow = [ 'Sheet ID',
            'Unit Name',
            'Purch Type',
            'Supplier',
            'Inv/Ref #',
            'Inv/Rcpt date',
            'Purch Details' ];

        $unitId = $request->unit_id;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $purchaseType = $request->purch_type;

        // Store in cookie
        Cookie::queue('purchasesSummaryUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesSummaryFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesSummaryToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('purchasesSummaryPurchaseTypeCookie', $purchaseType, time() + (10 * 365 * 24 * 60 * 60));

        $unitIDStr = $unitId != '' ? ' AND p.unit_id = ' . $unitId : '';
        $purchaseTypeStr = $purchaseType != 'both' ? ' AND p.purch_type = "' . $purchaseType . '"' : '';

        $purchases = \DB::table('purchases AS P')
            ->select('P.unique_id', 'P.purch_type', 'P.supplier', 'P.reference_invoice_number', \DB::raw('DATE_FORMAT(P.receipt_invoice_date,"%d-%m-%Y") receipt_invoice_date'), 'P.purchase_details', 'P.net_ext_ID', 'P.goods', 'UN.unit_name', 'P.goods_total', 'P.vat_total', 'P.gross_total')
            ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
            ->whereBetween('P.receipt_invoice_date', [ $fromDate, $toDate ])
            ->when($unitId, function ($query) use ($unitId)
            {
                return $query->where('P.unit_id', $unitId);
            })
            ->when(isset($purchaseType) && $purchaseType != 'both', function ($query) use ($purchaseType)
            {
                return $query->where('purch_type', $purchaseType);
            })
            ->where('P.deleted', 0)
            ->get();

        $nominalCodes = \DB::table('nominal_codes')
            ->select('*',
                \DB::raw("
                            (SELECT COUNT(*) FROM purchases p WHERE nominal_codes.net_ext_ID = p.net_ext_ID AND p.receipt_invoice_date
                            BETWEEN '$fromDate' AND '$toDate' $unitIDStr $purchaseTypeStr AND deleted = 0) AS total
                        ")
            )
            ->having('total', '>', 0)
            ->get();

        $nominalCodesArr = [];

        foreach ($nominalCodes as $nc) {
            $nominalCodesArr[$nc->net_ext_ID] = [ 'title' => $nc->net_ext, 'total' => 0 ];
        }

        foreach ($nominalCodesArr as $ncVal) {
            $headerRow[] = ucwords($ncVal['title']);
        }

        $headerRow[] = 'Total Goods';
        $headerRow[] = 'Total VAT';
        $headerRow[] = 'Total Gross';

        fputcsv($output, $headerRow);

        $rowData = [];
        $uniqueIds = [];
        $goodsTotal = 0;
        $vatTotal = 0;
        $grossTotal = 0;

        foreach ($purchases as $pVal) {
            $rowData[] = $pVal->unique_id;
            $rowData[] = $pVal->unit_name;
            $rowData[] = $pVal->purch_type;
            $rowData[] = $pVal->supplier;
            $rowData[] = $pVal->reference_invoice_number;
            $rowData[] = $pVal->receipt_invoice_date;
            $rowData[] = $pVal->purchase_details;

            foreach ($nominalCodesArr as $ncKey => $ncVal) {
                if ($ncKey == $pVal->net_ext_ID) {
                    $rowData[] = \Helpers::formatEuroAmountsForCSV($pVal->goods);
                    $nominalCodesArr[$ncKey]['total'] += $pVal->goods;
                } else
                    $rowData[] = \Helpers::formatEuroAmountsForCSV('0.00');
            }

            if (!in_array($pVal->unique_id, $uniqueIds)) {
                $uniqueIds[] = $pVal->unique_id;
                $goodsTotal += $pVal->goods_total;
                $vatTotal += $pVal->vat_total;
                $grossTotal += $pVal->gross_total;

                $rowData[] = \Helpers::formatEuroAmountsForCSV($pVal->goods_total);
                $rowData[] = \Helpers::formatEuroAmountsForCSV($pVal->vat_total);
                $rowData[] = \Helpers::formatEuroAmountsForCSV($pVal->gross_total);
            } else {
                $rowData[] = '';
                $rowData[] = '';
                $rowData[] = '';
            }

            fputcsv($output, $rowData);
            $rowData = [];
        }

        $lastRow = [ '', '', '', '', '', '', 'Total' ];

        foreach ($nominalCodesArr as $ncVal) {
            $lastRow[] = \Helpers::formatEuroAmountsForCSV($ncVal['total']);
        }
        $lastRow[] = \Helpers::formatEuroAmountsForCSV($goodsTotal);
        $lastRow[] = \Helpers::formatEuroAmountsForCSV($vatTotal);
        $lastRow[] = \Helpers::formatEuroAmountsForCSV($grossTotal);
        fputcsv($output, $lastRow);
        fclose($output);
    }

    /**
     * Problem Report.
     */
    public function problemReport()
    {
        $unitId = Cookie::get('problemReportUnitIdCookie', '');
        $fromDate = Cookie::get('problemReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('problemReportToDateCookie', Carbon::now()->format('d-m-Y'));
        $problemType = Cookie::get('problemReportProblemTypeCookie', '');

        // Problem types
        $problemTypes = ProblemType::orderBy('title')->get();

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.problem-report.index', [
                'userUnits'           => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit'        => $unitId,
                'fromDate'            => $fromDate,
                'toDate'              => $toDate,
                'problemTypes'        => $problemTypes,
                'selectedProblemType' => $problemType,
                'backUrl'             => session()->get('backUrl', '/'),
            ]
        );
    }

    public function problemReportGrid(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $problemType = $request->problem_type;
        $allRecords = $request->input('all_records', 0);

        // Store in cookie
        Cookie::queue('problemReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('problemReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('problemReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('problemReportProblemTypeCookie', $problemType, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'problem')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        return view(
            'reports.problem-report.grid', [
                'userId'      => $userId,
                'unitId'      => $unitId,
                'fromDate'    => $fromDate,
                'toDate'      => $toDate,
                'problemType' => $problemType,
                'allRecords'  => $allRecords,
                'notVisiable' => $hiddenColumns,
                'isSuLevel'   => Gate::allows('su-user-group'),
            ]
        );
    }

    public function problemReportGridJson(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_id;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d 00:00:00');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d 23:59:59');
        $problemType = $request->problem_type;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        if ($unitId == '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            } else {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->whereBetween('problem_date', [ $fromDate, $toDate ])
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            }
        } elseif ($unitId == '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            } else {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->whereBetween('problem_date', [ $fromDate, $toDate ])
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            }
        } elseif ($unitId == '' && Gate::allows('operations-user-group')) {
            $problemReport = \DB::table('problems AS P')
                ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->select([ 'P.id',
                    'P.problem_status_title',
                    'P.problem_date',
                    'U.username',
                    'UN.unit_name',
                    'PT.title',
                    'P.suppliers_feedback_title',
                    'P.details',
                    'P.root_cause_analysis',
                    'P.root_cause_analysis_desc',
                    'P.root_cause_analysis_action',
                    'P.closing_comments',
                    'P.closed_by_username',
                    'P.closed_date',
                    'P.id' ])
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('problem_date', [ $fromDate, $toDate ])
                ->when($problemType, function ($query) use ($problemType)
                {
                    return $query->where('problem_type', $problemType);
                });
        } elseif ($unitId == '' && Gate::allows('unit-user-group')) {
            $problemReport = \DB::table('problems AS P')
                ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->select([ 'P.id',
                    'P.problem_status_title',
                    'P.problem_date',
                    'U.username',
                    'UN.unit_name',
                    'PT.title',
                    'P.suppliers_feedback_title',
                    'P.details',
                    'P.root_cause_analysis',
                    'P.root_cause_analysis_desc',
                    'P.root_cause_analysis_action',
                    'P.closing_comments',
                    'P.closed_by_username',
                    'P.closed_date',
                    'P.id' ])
                ->whereIn('P.unit_id', $userUnits)
                ->whereBetween('problem_date', [ $fromDate, $toDate ])
                ->when($problemType, function ($query) use ($problemType)
                {
                    return $query->where('problem_type', $problemType);
                });
        } elseif ($unitId != '' && Gate::allows('su-user-group')) {
            if ($request->all_records) {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->where('P.unit_id', $unitId)
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            } else {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->where('P.unit_id', $unitId)
                    ->whereBetween('problem_date', [ $fromDate, $toDate ])
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            }
        } elseif ($unitId != '' && Gate::allows('hq-user-group')) {
            if ($request->all_records) {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->where('P.unit_id', $unitId)
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            } else {
                $problemReport = \DB::table('problems AS P')
                    ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                    ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                    ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                    ->select([ 'P.id',
                        'P.problem_status_title',
                        'P.problem_date',
                        'U.username',
                        'UN.unit_name',
                        'PT.title',
                        'P.suppliers_feedback_title',
                        'P.details',
                        'P.root_cause_analysis',
                        'P.root_cause_analysis_desc',
                        'P.root_cause_analysis_action',
                        'P.closing_comments',
                        'P.closed_by_username',
                        'P.closed_date',
                        'P.id' ])
                    ->where('P.unit_id', $unitId)
                    ->whereBetween('problem_date', [ $fromDate, $toDate ])
                    ->when($problemType, function ($query) use ($problemType)
                    {
                        return $query->where('problem_type', $problemType);
                    });
            }
        } else {
            $problemReport = \DB::table('problems AS P')
                ->leftJoin('users AS U', 'P.user_id', '=', 'U.user_id')
                ->leftJoin('problem_types AS PT', 'P.problem_type', '=', 'PT.id')
                ->leftJoin('units AS UN', 'P.unit_id', '=', 'UN.unit_id')
                ->select([ 'P.id',
                    'P.problem_status_title',
                    'P.problem_date',
                    'U.username',
                    'UN.unit_name',
                    'PT.title',
                    'P.suppliers_feedback_title',
                    'P.details',
                    'P.root_cause_analysis',
                    'P.root_cause_analysis_desc',
                    'P.root_cause_analysis_action',
                    'P.closing_comments',
                    'P.closed_by_username',
                    'P.closed_date',
                    'P.id' ])
                ->where('P.unit_id', $unitId)
                ->whereBetween('problem_date', [ $fromDate, $toDate ])
                ->when($problemType, function ($query) use ($problemType)
                {
                    return $query->where('problem_type', $problemType);
                });
        }

        if (Gate::allows('su-user-group')) {
            return Datatables::of($problemReport)
                ->setRowId(function ($problemReport)
                {
                    return 'tr_' . $problemReport->id;
                })
                ->addColumn('checkbox', function ($problemReport)
                {
                    return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $problemReport->id . '">';
                }, 0)
                ->addColumn('action', function ($problemReport)
                {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
                })
                ->editColumn('id', function ($problemReport)
                {
                    return '<a target="_blank" href="/sheets/problem-report/' . $problemReport->id . '">' . $problemReport->id . '</a>';
                })
                ->editColumn('problem_date', function ($problemReport)
                {
                    return $problemReport->problem_date ? with(new Carbon($problemReport->problem_date))->format('d-m-Y') : '';
                })
                ->filterColumn('P.problem_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(problem_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('root_cause_analysis', function ($problemReport)
                {
                    return $problemReport->root_cause_analysis == 1 ? 'Yes' : 'No';
                })
                ->filterColumn('P.root_cause_analysis', function ($query, $keyword)
                {
                    $query->whereRaw("IF(root_cause_analysis = 1, 'Yes', 'No') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('closed_date', function ($problemReport)
                {
                    return $problemReport->closed_date ? with(new Carbon($problemReport->closed_date))->format('d-m-Y') : '';
                })
                ->filterColumn('P.closed_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(closed_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        } else {
            return Datatables::of($problemReport)
                ->setRowId(function ($problemReport)
                {
                    return 'tr_' . $problemReport->id;
                })
                ->addColumn('action', function ($problemReport)
                {
                    return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                <input name="_method" type="hidden" value="DELETE">
                <input name="_token" type="hidden" value="' . csrf_token() . '">
                <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                </form>';
                })
                ->editColumn('id', function ($problemReport)
                {
                    return '<a target="_blank" href="/sheets/problem-report/' . $problemReport->id . '">' . $problemReport->id . '</a>';
                })
                ->editColumn('problem_date', function ($problemReport)
                {
                    return $problemReport->problem_date ? with(new Carbon($problemReport->problem_date))->format('d-m-Y') : '';
                })
                ->filterColumn('P.problem_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(problem_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('root_cause_analysis', function ($problemReport)
                {
                    return $problemReport->root_cause_analysis == 1 ? 'Yes' : 'No';
                })
                ->filterColumn('P.root_cause_analysis', function ($query, $keyword)
                {
                    $query->whereRaw("IF(root_cause_analysis = 1, 'Yes', 'No') like ?", [ "%$keyword%" ]);
                })
                ->editColumn('closed_date', function ($problemReport)
                {
                    return $problemReport->closed_date ? with(new Carbon($problemReport->closed_date))->format('d-m-Y') : '';
                })
                ->filterColumn('P.closed_date', function ($query, $keyword)
                {
                    $query->whereRaw("DATE_FORMAT(closed_date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
                })
                ->make();
        }
    }

    public function deleteProblemReportRecord($id)
    {
        $problemReportIds = explode(',', $id);

        foreach ($problemReportIds as $problemReportId) {
            $problemReport = \App\Problem::find($problemReportId);
            $problemReport->delete();
        }

        echo $id;
    }

    /**
     * Lodgements Report.
     */
    public function lodgements()
    {
        $unitId = Cookie::get('lodgementsReportUnitIdCookie', '');
        $fromDate = Cookie::get('lodgementsReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('lodgementsReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.lodgements.index', [
                'userUnits'    => Gate::allows('hq-user-group') ? [ '' => 'Select All' ] + $userUnits->toArray() : $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    public function lodgementsGrid(Request $request)
    {
        $userId = session()->get('userId');
        $unitId = $request->unit_name;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');
        $allRecords = $request->input('all_records', 0);

        // Store in cookie
        Cookie::queue('lodgementsReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('lodgementsReportFromDateCookie', $request->from_date, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('lodgementsReportToDateCookie', $request->to_date, time() + (10 * 365 * 24 * 60 * 60));

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'lodgements')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        // Currency symbol
        $unit = Unit::find($unitId);
        $defaultCurrency = Currency::where('is_default', 1)->first();

        $currencySymbol = $defaultCurrency->currency_symbol;
        if (!is_null($unit) && !is_null($unit->currency)) {
            $currencySymbol = $unit->currency->currency_symbol;
        }

        // Currencies
        $currencies = Currency::all();

        // Cost columns
        $startColumn = Gate::allows('su-user-group') ? 7 : 6;
        $costColumns = range($startColumn, $startColumn + count($currencies) * 2 + 1);

        // Columns currency symbols
        $columnSymbols = [];

        return view(
            'reports.lodgements.grid', [
                'unitId'         => $unitId,
                'userId'         => $userId,
                'fromDate'       => $fromDate,
                'toDate'         => $toDate,
                'allRecords'     => $allRecords,
                'notVisiable'    => $hiddenColumns,
                'isSuLevel'      => Gate::allows('su-user-group'),
                'costColumns'    => json_encode($costColumns),
                'currencies'     => $currencies,
                'currencySymbol' => $currencySymbol,
            ]
        );
    }

    public function lodgementsGridJson(Request $request)
    {
        $unitId = $request->unit_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_id');

        $selectColumns = [
            'l.lodgement_id',
            'l.date',
            'un.unit_name',
            'u.username',
            'l.slip_number',
            'l.bag_number',
            'l.cash',
            'l.coin',
            'l.remarks',
        ];

        if (Gate::allows('su-user-group')) {
            // Add one more column for checkbox to prevent wrong sort/search
            array_unshift($selectColumns, 'l.lodgement_id');
        }

        $lodgements = \DB::table('lodgements AS l')
            ->leftJoin('users AS u', 'l.created_by', '=', 'u.user_id')
            ->leftJoin('units AS un', 'l.unit_id', '=', 'un.unit_id')
            ->select($selectColumns)
            ->when($unitId, function ($query) use ($unitId)
            {
                return $query->where('l.unit_id', $unitId);
            });

        if ($request->all_records == 0) {
            $lodgements->whereBetween('l.date', [ $fromDate, $toDate ]);
        }

        if (Gate::denies('su-user-group') && Gate::denies('hq-user-group')) {
            $lodgements->whereIn('l.unit_id', $userUnits);
        }

        $dataTable = Datatables::of($lodgements)
            ->setRowId(function ($lodgement)
            {
                return 'tr_' . $lodgement->lodgement_id;
            })
            ->editColumn('lodgement_id', function ($lodgement)
            {
                return '<a target="_blank" href="/sheets/lodgements/' . $lodgement->lodgement_id . '">' . $lodgement->lodgement_id . '</a>';
            })
            ->editColumn('date', function ($lodgement)
            {
                return $lodgement->date ? Carbon::parse($lodgement->date)->format('d-m-Y') : '';
            })
            ->filterColumn('l.date', function ($query, $keyword)
            {
                $query->whereRaw("DATE_FORMAT(l.date,'%d-%m-%Y') like ?", [ "%$keyword%" ]);
            })
            ->addColumn('action', function ()
            {
                return '<form method="POST" action="" accept-charset="UTF-8" class="display-inline">
                        <input name="_method" type="hidden" value="DELETE">
                        <input name="_token" type="hidden" value="' . csrf_token() . '">
                        <button type="button" class="btn btn-danger btn-xs delete" data-token="' . csrf_token() . '"><i class="fa fa-trash"></i></button>
                        </form>';
            });

        if (Gate::allows('su-user-group')) {
            $dataTable->addColumn('checkbox', function ($lodgement)
            {
                return '<input name="del_chks" type="checkbox" class="checkboxs" value="' . $lodgement->lodgement_id . '">';
            }, 0);
        }

        // Add Lodgement Costs
        $currencies = Currency::all();
        $columnIndex = Gate::allows('su-user-group') ? 7 : 6;

        foreach ($currencies as $currency) {
            $dataTable->addColumn("Cash {$currency->currency_code}", function ($lodgement) use ($currency)
            {
                $lodgementCost = LodgementCost::where('lodgement_id', $lodgement->lodgement_id)
                    ->where('currency_id', $currency->currency_id)
                    ->first();

                if (is_null($lodgementCost)) {
                    return 0;
                }

                return $lodgementCost->cash;
            }, $columnIndex++);

            $dataTable->addColumn("Coin {$currency->currency_code}", function ($lodgement) use ($currency)
            {
                $lodgementCost = LodgementCost::where('lodgement_id', $lodgement->lodgement_id)
                    ->where('currency_id', $currency->currency_id)
                    ->first();

                if (is_null($lodgementCost)) {
                    return 0;
                }

                return $lodgementCost->coin;
            }, $columnIndex++);
        }

        return $dataTable->make();
    }

    public function deleteLodgementsRecord($id)
    {
        $lodgementIds = explode(',', $id);

        DB::beginTransaction();

        try {
            foreach ($lodgementIds as $lodgementId) {
                // Remove lodgement
                $lodgement = Lodgement::findOrFail($lodgementId);

                $lodgement->delete();

                // Delete  Lodgement costs
                LodgementCost::where('lodgement_id', $lodgementId)->delete();

                // Detach lodgement from Cash Sales
                CashSales::where('lodgement_id', $lodgementId)
                    ->update(
                        [
                            'lodgement_id' => null,
                        ]
                    );

                // Detach lodgement from Vending Sales
                VendingSales::where('lodgement_id', $lodgementId)
                    ->update(
                        [
                            'lodgement_id' => null,
                        ]
                    );
            }

            DB::commit();

            echo $id;
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    /**
     * Client Feedback Report.
     */
    public function clientFeedback(Request $request)
    {
        $unitId = Cookie::get('clientFeedbackReportUnitIdCookie', '');
        $fromDate = Cookie::get('clientFeedbackReportFromDateCookie', Carbon::now()->subMonth()->format('d-m-Y'));
        $toDate = Cookie::get('clientFeedbackReportToDateCookie', Carbon::now()->format('d-m-Y'));

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        return view(
            'reports.client-feedback.index', [
                'userUnits'    => $userUnits,
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    /**
     * Client Feedback Report Grid.
     */
    public function clientFeedbackGrid(Request $request)
    {
        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        // Read request params
        $currentDateTime = Carbon::now();
        $todayDate = $currentDateTime->format('d-m-Y');
        $oneMonthBeforeTodayDate = $currentDateTime->subMonth()->format('d-m-Y');

        $unitId = $request->input('unit_id', Cookie::get('clientFeedbackReportUnitIdCookie', ''));
        $fromDate = $request->input('from_date', Cookie::get('clientFeedbackReportFromDateCookie', $oneMonthBeforeTodayDate));
        $toDate = $request->input('to_date', Cookie::get('clientFeedbackReportToDateCookie', $todayDate));

        if ($unitId !== '') {
            Cookie::queue('clientFeedbackReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
            Cookie::queue('clientFeedbackReportFromDateCookie', $fromDate, time() + (10 * 365 * 24 * 60 * 60));
            Cookie::queue('clientFeedbackReportToDateCookie', $toDate, time() + (10 * 365 * 24 * 60 * 60));
        }

        // Prepare data
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

            // Onsite visits
            $onsiteVisits = CustomerFeedback::where('unit_id', $unitId)
                ->whereDate('contact_date', '>=', Carbon::now()->startOfMonth())
                ->count();

            // Other
            $contractStatus = $unit->status->name;

            $clientContact = $unit->client_contact_name;

            $unitBudget = TradingAccount::where('unit_id', $unitId)
                ->orderBy('trading_account_id', 'desc')
                ->first([ 'contract_type_id' ]);

            $contractType = ($unitBudget && $unitBudget->contractType) ? $unitBudget->contractType->title : '';
        } else {
            $regionName = '';
            $operationsManagerName = '';
            $contractStatus = '';
            $clientContact = '';
            $contractType = '';
            $onsiteVisits = '';
        }

        return view(
            'reports.client-feedback.grid', [
                'userUnits'             => $userUnits,
                'selectedUnit'          => $unitId,
                'fromDate'              => $fromDate,
                'toDate'                => $toDate,
                'regionName'            => $regionName,
                'operationsManagerName' => $operationsManagerName,
                'contractStatus'        => $contractStatus,
                'contractType'          => $contractType,
                'clientContact'         => $clientContact,
                'onsiteVisits'          => $onsiteVisits,
            ]
        );
    }

    /**
     * Client Feedback Report Post.
     */
    public function clientFeedbackGridJson(Request $request)
    {
        $unitId = $request->unit_id;
        $fromDate = Carbon::parse($request->from_date)->format('Y-m-d');
        $toDate = Carbon::parse($request->to_date)->format('Y-m-d');

        $feedbacks = \DB::table('customer_feedbacks')
            ->select(
                [
                    'customer_feedbacks.contact_date',
                    'contact_types.title as contact_type',
                    'customer_feedbacks.notes',
                    'feedback_types.title as customer_feedback',
                ]
            )
            ->leftJoin('contact_types', 'contact_types.id', '=', 'customer_feedbacks.contact_type_id')
            ->leftJoin('feedback_types', 'feedback_types.id', '=', 'customer_feedbacks.feedback_type_id')
            ->where('unit_id', $unitId)
            ->whereDate('contact_date', '>=', $fromDate)
            ->whereDate('contact_date', '<=', $toDate);

        return Datatables::of($feedbacks)
            ->editColumn('contact_date', function ($feedbackRow)
            {
                return Carbon::parse($feedbackRow->contact_date)->format('d-m-Y H:i');
            })
            ->make();
    }

    /**
     * Operations scorecard Report.
     */
    public function operationsScorecard()
    {
        if (Gate::denies('management-user-group')) {
            abort(403, 'Access denied');
        }

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        $currentDateTime = Carbon::now();
        $todayDate = $currentDateTime->format('d-m-Y');
        $oneMonthBeforeTodayDate = $currentDateTime->subMonth()->format('d-m-Y');

        $unitId = Cookie::get('operationsScorecardReportUnitIdCookie', 0);
        $fromDate = Cookie::get('operationsScorecardReportFromDateCookie', $oneMonthBeforeTodayDate);
        $toDate = Cookie::get('operationsScorecardReportToDateCookie', $todayDate);

        return view(
            'reports.operations-scorecard.index', [
                'userUnits'    => [ 0 => 'All' ] + $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'backUrl'      => session()->get('backUrl', '/'),
            ]
        );
    }

    /**
     * Operations scorecard Report Grid.
     */
    public function operationsScorecardGrid(Request $request)
    {
        if (Gate::denies('management-user-group')) {
            abort(403, 'Access denied');
        }

        // Get list of units for current user level
        $userUnits = $this->getUserUnits()->pluck('unit_name', 'unit_id');

        // Read request params
        $currentDateTime = Carbon::now();
        $todayDate = $currentDateTime->format('d-m-Y');
        $oneMonthBeforeTodayDate = $currentDateTime->subMonth()->format('d-m-Y');

        $unitId = $request->input('unit_id', Cookie::get('operationsScorecardReportUnitIdCookie', 0));
        $fromDate = $request->input('from_date', Cookie::get('operationsScorecardReportFromDateCookie', $oneMonthBeforeTodayDate));
        $toDate = $request->input('to_date', Cookie::get('operationsScorecardReportToDateCookie', $todayDate));

        Cookie::queue('operationsScorecardReportUnitIdCookie', $unitId, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('operationsScorecardReportFromDateCookie', $fromDate, time() + (10 * 365 * 24 * 60 * 60));
        Cookie::queue('operationsScorecardReportToDateCookie', $toDate, time() + (10 * 365 * 24 * 60 * 60));

        $parsedFromDate = Carbon::parse($fromDate);
        $parsedToDate = Carbon::parse($toDate);

        $reportUnits = $unitId ? [ $unitId ] : $userUnits->keys()->toArray();

        // Collect data from the contact_types table
        $contactTypes = ContactType::orderBy('position')->pluck('title', 'id')->toArray();

        // Collect data from the feedback_types table
        $feedbackScores = FeedbackType::pluck('score', 'id')->toArray();

        // Collect data from the contract_types table
        $contractTypes = ContractType::pluck('title', 'id')->toArray();

        // Collect data from the ops_scorecard table
        $opsScorecards = OperationsScorecard::whereIn('unit_id', $reportUnits)
            ->whereDate('scorecard_date', '>=', $parsedFromDate)
            ->whereDate('scorecard_date', '<=', $parsedToDate)
            ->where('deleted', 0)
            ->get();

        $scorecardData = [];
        foreach ($opsScorecards as $opsScorecard) {
            if (!isset($scorecardData[$opsScorecard->unit_id])) {
                $scorecardData[$opsScorecard->unit_id] = [
                    'cnt'                   => 0,
                    // scorecards ids
                    'ids'                   => [],
                    // scores
                    'avg'                   => [],
                    'presentation'          => [],
                    'foodcost_awareness'    => [],
                    'hr_issues'             => [],
                    'morale'                => [],
                    'purch_compliance'      => [],
                    'haccp_compliance'      => [],
                    'health_safety_iso'     => [],
                    'accidents_incidents'   => [],
                    'security_cash_ctl'     => [],
                    'marketing_upselling'   => [],
                    'training'              => [],
                    // notes
                    'objectives'            => [],
                    'outstanding_issues'    => [],
                    'sp_projects_functions' => [],
                    'innovation'            => [],
                    'add_support_req'       => [],
                ];
            }

            // Total count
            $scorecardData[$opsScorecard->unit_id]['cnt']++;

            // ID's
            $scorecardData[$opsScorecard->unit_id]['ids'][] = $opsScorecard->ops_scorecard_id;

            // Average values
            foreach (OperationsScorecard::$scoreFields as $scoreField) {
                if ($opsScorecard->{$scoreField} != 0) {
                    $scorecardData[$opsScorecard->unit_id]['avg'][] = $opsScorecard->{$scoreField};
                }

                $scorecardData[$opsScorecard->unit_id][$scoreField][] = $opsScorecard->{$scoreField};
            }

            // Notes
            $scorecardData[$opsScorecard->unit_id]['objectives'][] = [
                'date'  => $opsScorecard->scorecard_date,
                'value' => $opsScorecard->objectives,
            ];
            $scorecardData[$opsScorecard->unit_id]['outstanding_issues'][] = [
                'date'  => $opsScorecard->scorecard_date,
                'value' => $opsScorecard->outstanding_issues,
            ];
            $scorecardData[$opsScorecard->unit_id]['sp_projects_functions'][] = [
                'date'  => $opsScorecard->scorecard_date,
                'value' => $opsScorecard->sp_projects_functions,
            ];
            $scorecardData[$opsScorecard->unit_id]['innovation'][] = [
                'date'  => $opsScorecard->scorecard_date,
                'value' => $opsScorecard->innovation,
            ];
            $scorecardData[$opsScorecard->unit_id]['add_support_req'][] = [
                'date'  => $opsScorecard->scorecard_date,
                'value' => $opsScorecard->add_support_req,
            ];
        }

        // Collect data from the customer_feedback table
        $customerFeedbacks = CustomerFeedback::whereIn('unit_id', $reportUnits)
            ->whereDate('contact_date', '>=', $parsedFromDate)
            ->whereDate('contact_date', '<=', $parsedToDate)
            ->get();

        $feedbackData = [];
        foreach ($customerFeedbacks as $customerFeedback) {
            if (!isset($feedbackData[$customerFeedback->unit_id])) {
                $feedbackData[$customerFeedback->unit_id] = [
                    'cnt'                                      => 0,
                    'score'                                    => 0,
                    ContactType::CONTACT_TYPE_MEETING          => 0,
                    ContactType::CONTACT_TYPE_EMAIL            => 0,
                    ContactType::CONTACT_TYPE_PHONE            => 0,
                    ContactType::CONTACT_TYPE_VIDEO_CONFERENCE => 0,
                    ContactType::CONTACT_TYPE_OTHER            => 0,
                ];
            }

            $feedbackData[$customerFeedback->unit_id]['cnt'] += 1;
            $feedbackData[$customerFeedback->unit_id]['score'] += $feedbackScores[$customerFeedback->feedback_type_id] * 2.5;
            $feedbackData[$customerFeedback->unit_id][$customerFeedback->contact_type_id] += 1;
        }

        // Collect data from the cash_sales table
        $cashSales = CashSales::whereIn('unit_id', $reportUnits)
            ->whereDate('sale_date', '>=', $parsedFromDate)
            ->whereDate('sale_date', '<=', $parsedToDate)
            ->get([ 'unit_id', 'z_read', 'over_ring' ]);

        $grossSalesActual = [];
        foreach ($cashSales as $cashSale) {
            if (!isset($grossSalesActual[$cashSale->unit_id])) {
                $grossSalesActual[$cashSale->unit_id] = 0;
            }

            $grossSalesActual[$cashSale->unit_id] += ($cashSale['z_read'] - $cashSale['over_ring']);
        }

        // Collect data from the trading_account table
        $tradingAccounts = TradingAccount::whereIn('unit_id', $reportUnits)
            ->orderBy('trading_account_id', 'desc')
            ->get([ 'unit_id', 'contract_type_id' ]);

        $unitBudgets = [];
        foreach ($tradingAccounts as $tradingAccount) {
            // find the last budget
            if (isset($unitBudgets[$tradingAccount->unit_id])) {
                continue;
            }

            $unitBudgets[$tradingAccount->unit_id] = $contractTypes[$tradingAccount->contract_type_id];
        }

        $tradingAccounts = TradingAccount::whereIn('unit_id', $reportUnits)
            ->whereDate('budget_start_date', '<=', $parsedToDate)
            ->whereDate('budget_end_date', '>=', $parsedFromDate)
            ->orderBy('trading_account_id', 'desc')
            ->get([
                'unit_id',
                'budget_start_date',
                'gross_sales_month_1',
                'gross_sales_month_2',
                'gross_sales_month_3',
                'gross_sales_month_4',
                'gross_sales_month_5',
                'gross_sales_month_6',
                'gross_sales_month_7',
                'gross_sales_month_8',
                'gross_sales_month_9',
                'gross_sales_month_10',
                'gross_sales_month_11',
                'gross_sales_month_12',
            ]);

        $grossSalesBudget = [];
        foreach ($tradingAccounts as $tradingAccount) {
            // find the last budget for period
            if (isset($grossSalesBudget[$tradingAccount->unit_id])) {
                continue;
            }

            // Calculate from/to indexes for the budget totals
            $budget_from = 0;
            $budget_to = 12;
            $budgetDate = Carbon::parse($tradingAccount->budget_start_date);
            $startMonth = $parsedFromDate->format('Y-m');
            $endMonth = $parsedToDate->format('Y-m');

            for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++) {
                $budgetMonth = $budgetDate->format('Y-m');

                if ($startMonth == $budgetMonth) {
                    $budget_from = $monthIndex;
                }

                if ($endMonth == $budgetMonth) {
                    $budget_to = $monthIndex;
                }

                $budgetDate->addMonth();
            }

            $grossSalesBudget[$tradingAccount->unit_id] = $this->getTotal($tradingAccount, 'gross_sales_month', $budget_from, $budget_to);
        }

        // Collect data from the problems table
        $problems = Problem::whereIn('unit_id', $reportUnits)
            ->whereDate('problem_date', '>=', $parsedFromDate)
            ->whereDate('problem_date', '<=', $parsedToDate)
            ->where('problem_type', ProblemType::GDPR)
            ->get([ 'unit_id' ]);

        $gdpr = [];
        foreach ($problems as $problem) {
            if (!isset($gdpr[$problem->unit_id])) {
                $gdpr[$problem->unit_id] = 0;
            }

            $gdpr[$problem->unit_id] += 1;
        }

        // Collect info from the units table
        $units = Unit::with('status')
            ->where('status_id', Status::STATUS_UNIT_ACTIVE)
            ->whereIn('unit_id', $reportUnits)
            ->orderBy('unit_name', 'asc')
            ->get();

        $reportData = [];

        foreach ($units as $unit) {
            $dataRow['unitId'] = $unit->unit_id;
            $dataRow['unitName'] = $unit->unit_name;
            $dataRow['unitStatus'] = $unit->status->name;

            // Contract type
            $dataRow['contractType'] = isset($unitBudgets[$unit->unit_id]) ? $unitBudgets[$unit->unit_id] : '';

            // Operation manager
            $dataRow['operationsManager'] = '';
            $operationManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

            if (is_array($operationManagers) && count($operationManagers) > 0) {
                $operationManager = User::whereIn('user_id', $operationManagers)->first();
                $dataRow['operationsManager'] = $operationManager->username;
            }

            // Region
            $dataRow['region'] = '';
            $unitOperationGroups = $unit->operations_group !== '' ? explode(',', $unit->operations_group) : [];

            if (is_array($unitOperationGroups) && count($unitOperationGroups) > 0) {
                $region = Region::whereIn('region_id', $unitOperationGroups)->first();
                $dataRow['region'] = $region->region_name;
            }

            // Customer feedback
            $dataRow['customerFeedback'] = isset($feedbackData[$unit->unit_id])
                ? round($feedbackData[$unit->unit_id]['score'] / $feedbackData[$unit->unit_id]['cnt'])
                : 0;

            // Client contacts
            $clientContacts = [];

            foreach ($contactTypes as $contactTypeId => $contactTypeTitle) {
                $amount = isset($feedbackData[$unit->unit_id]) ? $feedbackData[$unit->unit_id][$contactTypeId] : 0;
                $clientContacts[] = "{$contactTypeTitle}: {$amount}";
            }

            $dataRow['clientContacts'] = $clientContacts;
            $dataRow['clientContactsTotal'] = isset($feedbackData[$unit->unit_id]) ? $feedbackData[$unit->unit_id]['cnt'] : 0;

            // Scorecards reports
            $dataRow['scorecardsReports'] = isset($scorecardData[$unit->unit_id]) ? $scorecardData[$unit->unit_id]['ids'] : [];
            $dataRow['scorecardsReportsTotal'] = isset($scorecardData[$unit->unit_id]) ? $scorecardData[$unit->unit_id]['cnt'] : 0;

            // Budget performance
            $unitGrossSalesActual = isset($grossSalesActual[$unit->unit_id]) ? $grossSalesActual[$unit->unit_id] : 0;
            $unitGrossSalesBudget = isset($grossSalesBudget[$unit->unit_id]) ? $grossSalesBudget[$unit->unit_id] : 0;

            $dataRow['budgetPerformance'] = $unitGrossSalesBudget != 0 ? round($unitGrossSalesActual / $unitGrossSalesBudget * 100, 2) . '%' : 0;

            // GDPR
            $dataRow['gdpr'] = isset($gdpr[$unit->unit_id]) ? $gdpr[$unit->unit_id] : 0;

            // Ops Scorecard
            $dataRow['aggregateScore'] = 0;
            $dataRow['presentation'] = 0;
            $dataRow['foodcostAwareness'] = 0;
            $dataRow['hrIssues'] = 0;
            $dataRow['morale'] = 0;
            $dataRow['purchCompliance'] = 0;
            $dataRow['haccpCompliance'] = 0;
            $dataRow['healthSafetyIso'] = 0;
            $dataRow['accidentsIncidents'] = 0;
            $dataRow['securityCashControl'] = 0;
            $dataRow['marketingUpselling'] = 0;
            $dataRow['training'] = 0;
            $dataRow['objectives'] = [];
            $dataRow['outstandingIssues'] = [];
            $dataRow['spProjectsFunctions'] = [];
            $dataRow['innovation'] = [];
            $dataRow['addSupportReq'] = [];
            $dataRow['highlightClass'] = '';

            if (isset($scorecardData[$unit->unit_id])) {
                $unitScorecard = $scorecardData[$unit->unit_id];

                // Score
                $dataRow['aggregateScore'] = count($unitScorecard['avg']) !== 0
                    ? round(array_sum($unitScorecard['avg']) / count($unitScorecard['avg']))
                    : 0;
                $dataRow['presentation'] = count($unitScorecard['presentation']) !== 0
                    ? round(array_sum($unitScorecard['presentation']) / count($unitScorecard['presentation']))
                    : 0;
                $dataRow['foodcostAwareness'] = count($unitScorecard['foodcost_awareness']) !== 0
                    ? round(array_sum($unitScorecard['foodcost_awareness']) / count($unitScorecard['foodcost_awareness']))
                    : 0;
                $dataRow['hrIssues'] = count($unitScorecard['hr_issues']) !== 0
                    ? round(array_sum($unitScorecard['hr_issues']) / count($unitScorecard['hr_issues']))
                    : 0;
                $dataRow['morale'] = count($unitScorecard['morale']) !== 0
                    ? round(array_sum($unitScorecard['morale']) / count($unitScorecard['morale']))
                    : 0;
                $dataRow['purchCompliance'] = count($unitScorecard['purch_compliance']) !== 0
                    ? round(array_sum($unitScorecard['purch_compliance']) / count($unitScorecard['purch_compliance']))
                    : 0;
                $dataRow['haccpCompliance'] = count($unitScorecard['haccp_compliance']) !== 0
                    ? round(array_sum($unitScorecard['haccp_compliance']) / count($unitScorecard['haccp_compliance']))
                    : 0;
                $dataRow['healthSafetyIso'] = count($unitScorecard['health_safety_iso']) !== 0
                    ? round(array_sum($unitScorecard['health_safety_iso']) / count($unitScorecard['health_safety_iso']))
                    : 0;
                $dataRow['accidentsIncidents'] = count($unitScorecard['accidents_incidents']) !== 0
                    ? round(array_sum($unitScorecard['accidents_incidents']) / count($unitScorecard['accidents_incidents']))
                    : 0;
                $dataRow['securityCashControl'] = count($unitScorecard['security_cash_ctl']) !== 0
                    ? round(array_sum($unitScorecard['security_cash_ctl']) / count($unitScorecard['security_cash_ctl']))
                    : 0;
                $dataRow['marketingUpselling'] = count($unitScorecard['marketing_upselling']) !== 0
                    ? round(array_sum($unitScorecard['marketing_upselling']) / count($unitScorecard['marketing_upselling']))
                    : 0;
                $dataRow['training'] = count($unitScorecard['training']) !== 0
                    ? round(array_sum($unitScorecard['training']) / count($unitScorecard['training']))
                    : 0;

                // Notes
                $dataRow['objectives'] = $unitScorecard['objectives'];
                $dataRow['outstandingIssues'] = $unitScorecard['outstanding_issues'];
                $dataRow['spProjectsFunctions'] = $unitScorecard['sp_projects_functions'];
                $dataRow['innovation'] = $unitScorecard['innovation'];
                $dataRow['addSupportReq'] = $unitScorecard['add_support_req'];

                // Highlight class
                if ($dataRow['aggregateScore'] >= 1 && $dataRow['aggregateScore'] <= 3) {
                    $dataRow['highlightClass'] = 'highlight highlight-red';
                }
                if ($dataRow['aggregateScore'] >= 4 && $dataRow['aggregateScore'] <= 6) {
                    $dataRow['highlightClass'] = 'highlight highlight-yellow';
                }
                if ($dataRow['aggregateScore'] >= 7 && $dataRow['aggregateScore'] <= 9) {
                    $dataRow['highlightClass'] = 'highlight highlight-green';
                }
                if ($dataRow['aggregateScore'] == 10) {
                    $dataRow['highlightClass'] = 'highlight highlight-blue';
                }
            }

            $reportData[] = $dataRow;
        }

        // Sort data by Aggregate Score
        usort($reportData, function ($a, $b)
        {
            // Place zero values to the end
            $valA = $a['aggregateScore'];
            $valB = $b['aggregateScore'];

            if ($valA == $valB) {
                return 0;
            }

            return ($valA < $valB) ? -1 : 1;
        });

        $userId = auth()->user()->user_id;

        // Columns visibility
        $hiddenColumns = ReportHiddenColumn::where('report_name', 'operations-scorecard')
            ->where('user_id', $userId)
            ->get()
            ->implode('column_index', ',');

        return view(
            'reports.operations-scorecard.grid', [
                'userId'       => $userId,
                'userUnits'    => [ 0 => 'All' ] + $userUnits->toArray(),
                'selectedUnit' => $unitId,
                'fromDate'     => $fromDate,
                'toDate'       => $toDate,
                'reportData'   => $reportData,
                'notVisible'   => $hiddenColumns,
            ]
        );
    }

    /**
     * Get total for months' in the UTA reports
     */
    private function getTotal($data, $field, $start, $end)
    {
        $total = 0;

        for ($i = $start; $i <= $end; $i++) {
            $fields = $field . '_' . $i;
            $total += $data->$fields;
        }

        return $total;
    }
}
