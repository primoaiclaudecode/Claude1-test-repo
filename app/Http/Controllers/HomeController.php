<?php

namespace App\Http\Controllers;

use App\BudgetType;
use App\Currency;
use App\Http\Controllers\Traits\UserUnits;
use App\PhasedBudget;
use App\Problem;
use App\TradingAccount;
use App\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

use App\Http\Controllers\DashboardDataUtils;


class HomeController extends Controller
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
        $this->middleware('role:unit')->except('index');
    }

    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index()
    {
        $userName = Session::get('userName', '');

        if (Gate::allows('limited-access-user-group')) {
            return view('home', [
                'userName' => $userName,
            ]);
        }

        $currencies = Currency::pluck('currency_name', 'currency_id');

        return view('dashboard', [
            'userName' => $userName,
            'fromDate' => Carbon::now()->startOfMonth()->format('d-m-Y'),
            'toDate'   => Carbon::now()->format('d-m-Y'),
            'currencies' => $currencies
        ]);
    }

    /**
     * Get car reminder data for the dashboard
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReminderData(Request $request)
    {
        $unitId = $request->input('unitId', 0);

        $userUnits = $unitId == 0 ? $this->getUserUnits()->pluck('unit_id') : [ $unitId ];

        // Budget reminder
        $budgets = PhasedBudget::with('unit')
            ->whereIn('unit_id', $userUnits)
            ->orderBy('trading_account_id', 'desc')
            ->get();

        $lastBudgets = [];

        foreach ($budgets as $budget) {
            // Show only last budget
            if (array_key_exists($budget->unit->unit_id, $lastBudgets)) {
                continue;
            }

            $lastBudgets[$budget->unit->unit_id] = [
                'unitId'     => $budget->unit->unit_id,
                'unitName'   => $budget->unit->unit_name,
                'budgetDate' => $budget->budget_end_date,
            ];
        }

        $expirationDate = Carbon::now()->addDays(30);
        $budgetsReminder = [];

        foreach ($lastBudgets as $lastBudget) {
            // Show only expired budget
            if (Carbon::parse($lastBudget['budgetDate'])->greaterThan($expirationDate)) {
                continue;
            }

            $budgetsReminder[] = $lastBudget;
        }

        // Problems reminder
        $carProblems = Problem::select(
            [
                'problems.id',
                'problems.problem_date as problemDate',
                'units.unit_name as unitName',
                'problem_types.title as problemName',
            ]
        )
            ->leftJoin('problem_types', 'problem_types.id', '=', 'problems.problem_type')
            ->leftJoin('units', 'units.unit_id', '=', 'problems.unit_id')
            ->whereNull('problems.closed_date')
            ->whereIn('problems.unit_id', $userUnits)
            ->orderBy('units.unit_name')
            ->get();

        // Add duration
        $problemsReminder = $carProblems->map(function ($item)
        {
            $duration = Carbon::now()->diffAsCarbonInterval(Carbon::parse($item->problemDate));
            $yearsDuration = $duration->format('%y');
            $monthsDuration = $duration->format('%m');
            $daysDuration = $duration->format('%d');
            $timeDuration = $duration->format('%H hours %i minutes %s seconds');

            $item->problemDuration = ($yearsDuration > 0 ? $yearsDuration . ' ' . Str::plural('year', $yearsDuration) . ' ' : '')
                . ($monthsDuration > 0 ? $monthsDuration . ' ' . Str::plural('month', $monthsDuration) . ' ' : '')
                . ($daysDuration > 0 ? $daysDuration . ' ' . Str::plural('day', $daysDuration) . ' ' : '')
                . $timeDuration;

            return $item;
        });

        return response()->json(
            [
                'budgets'  => $budgetsReminder,
                'problems' => $problemsReminder,
            ]
        );
    }

        /**
     * Get Totals and Units data for the dashboard
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardData(Request $request)
    {
        $this->validate($request, [
            'startDate' => 'required|date',
            'endDate'   => 'required|date',
        ]);

        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));

        // Currency
        $currency = Currency::find($request->currency_id);

        $utils = new DashboardDataUtils();

        return $utils->GetDashboardData($startDate, $endDate, $currency);
    }

    /**
     * Get Unit data for the dashboard
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUnitData(Request $request)
    {
        $this->validate($request, [
            'unitId'    => 'required|integer',
            'startDate' => 'required|date',
            'endDate'   => 'required|date',
        ]);

        $startDate = Carbon::parse($request->input('startDate'));
        $endDate = Carbon::parse($request->input('endDate'));

        // Unit
        $unitId = $request->unitId;
        $unit = Unit::find($unitId);

        // Currency
        $dashboardCurrency = $unit->currency_id;
        $currencySymbol = $unit->currency->currency_symbol;

        // Lodgements
        $cashLodge = 0;

        $lodgements = DB::table('lodgements as l')
            ->select(
                [
                    'l.unit_id',
                    DB::raw('(l.cash + l.coin) * er.exchange_rate as cash_lodge'),
                ]
            )
            ->leftJoin('units as u', 'l.unit_id', '=', 'u.unit_id')
            ->leftJoin('exchange_rates as er', function ($join) use ($dashboardCurrency)
            {
                $join->on('er.domestic_currency_id', 'u.currency_id')
                    ->where('er.foreign_currency_id', $dashboardCurrency)
                    ->whereRaw('er.date = l.date');
            })
            ->whereDate('l.date', '>=', $startDate)
            ->whereDate('l.date', '<=', $endDate)
            ->where('l.unit_id', $unitId)
            ->orderBy('l.date', 'asc')
            ->get();

        foreach ($lodgements as $lodgement) {
            $cashLodge += $lodgement->cash_lodge;
        }

        // Sales
        $grossSalesActual = 0;
        $salesLatestEntry = 0;

        $cashSales = DB::table('cash_sales as cs')
            ->select(
                [
                    DB::raw('(cs.z_read - cs.over_ring) * er.exchange_rate as gross_sale'),
                    'cs.sale_date',
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($dashboardCurrency)
            {
                $join->on('er.domestic_currency_id', 'cs.currency_id')
                    ->where('er.foreign_currency_id', $dashboardCurrency)
                    ->whereRaw('er.date = cs.sale_date');
            })
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('cs.unit_id', $unitId)
            ->orderBy('sale_date', 'asc')
            ->get();

        foreach ($cashSales as $cashSale) {
            $grossSalesActual += $cashSale->gross_sale;
            $salesLatestEntry = Carbon::parse($cashSale->sale_date)->timestamp;
        }

        $creditSales = DB::table('credit_sales')
            ->select(
                [
                    'sale_date',
                ]
            )
            ->whereDate('sale_date', '>=', $startDate)
            ->whereDate('sale_date', '<=', $endDate)
            ->where('unit_id', $unitId)
            ->orderBy('sale_date', 'asc')
            ->get();

        foreach ($creditSales as $creditSale) {
            $creditSaleDate = Carbon::parse($creditSale->sale_date)->timestamp;

            if ($creditSaleDate > $salesLatestEntry) {
                $salesLatestEntry = $creditSaleDate;
            }
        }

        $vendingSales = DB::table('vending_sales as vs')
            ->select(
                [
                    'unit_id',
                    DB::raw('vs.total * er.exchange_rate as total'),
                    'sale_date',
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($dashboardCurrency)
            {
                $join->on('er.domestic_currency_id', 'vs.currency_id')
                    ->where('er.foreign_currency_id', $dashboardCurrency)
                    ->whereRaw('er.date = vs.sale_date');
            })
            ->whereDate('vs.sale_date', '>=', $startDate)
            ->whereDate('vs.sale_date', '<=', $endDate)
            ->where('vs.unit_id', $unitId)
            ->orderBy('vs.sale_date', 'asc')
            ->get();

        foreach ($vendingSales as $vendingSale) {
            $grossSalesActual += $vendingSale->total;

            // Latest entry
            $vendingSaleDate = Carbon::parse($vendingSale->sale_date)->timestamp;

            if ($vendingSaleDate > $salesLatestEntry) {
                $salesLatestEntry = $vendingSaleDate;
            }
        }

        // Trading accounts
        $tradingAccount = DB::table('trading_account')
            ->select(
                [
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
                    'net_sales_month_1',
                    'net_sales_month_2',
                    'net_sales_month_3',
                    'net_sales_month_4',
                    'net_sales_month_5',
                    'net_sales_month_6',
                    'net_sales_month_7',
                    'net_sales_month_8',
                    'net_sales_month_9',
                    'net_sales_month_10',
                    'net_sales_month_11',
                    'net_sales_month_12',
                    'cost_of_sales_month_1',
                    'cost_of_sales_month_2',
                    'cost_of_sales_month_3',
                    'cost_of_sales_month_4',
                    'cost_of_sales_month_5',
                    'cost_of_sales_month_6',
                    'cost_of_sales_month_7',
                    'cost_of_sales_month_8',
                    'cost_of_sales_month_9',
                    'cost_of_sales_month_10',
                    'cost_of_sales_month_11',
                    'cost_of_sales_month_12',
                    'budget_type_id',
                ]
            )
            ->where('budget_start_date', '<=', $endDate)
            ->where('budget_end_date', '>=', $startDate)
            ->where('unit_id', $unitId)
            ->orderBy('trading_account_id', 'desc')
            ->first();

        // Purchases
        $costOfSalesActual = 0;
        $purchaseLatestEntry = null;

        $purchases = DB::table('purchases as p')
            ->select(
                [
                    DB::raw('p.goods * er.exchange_rate as goods'),
                    'p.receipt_invoice_date',
                ]
            )
            ->leftJoin('nominal_codes as nc', 'nc.net_ext_ID', '=', 'p.net_ext_ID')
            ->leftJoin('exchange_rates as er', function ($join) use ($dashboardCurrency)
            {
                $join->on('er.domestic_currency_id', 'p.currency_id')
                    ->where('er.foreign_currency_id', $dashboardCurrency)
                    ->whereRaw('er.date = p.date');
            })
            ->whereDate('p.receipt_invoice_date', '>=', $startDate)
            ->whereDate('p.receipt_invoice_date', '<=', $endDate)
            ->where('p.deleted', 0)
            ->where('p.unit_id', $unitId)
            ->where('nc.cost_of_sales', 1)
            ->orderBy('p.receipt_invoice_date', 'asc')
            ->get();

        foreach ($purchases as $purchase) {
            $costOfSalesActual += $purchase->goods;
            $purchaseLatestEntry = Carbon::parse($purchase->receipt_invoice_date)->timestamp;
        }

        // Cleaning and Disp
        $cleans = 0;

        $purchases = DB::table('purchases as p')
            ->select(
                [
                    DB::raw('p.goods * er.exchange_rate as goods'),
                ]
            )
            ->leftJoin('exchange_rates as er', function ($join) use ($dashboardCurrency)
            {
                $join->on('er.domestic_currency_id', 'p.currency_id')
                    ->where('er.foreign_currency_id', $dashboardCurrency)
                    ->whereRaw('er.date = p.date');
            })
            ->whereDate('p.receipt_invoice_date', '>=', $startDate)
            ->whereDate('p.receipt_invoice_date', '<=', $endDate)
            ->where('p.deleted', 0)
            ->where('p.unit_id', $unitId)
            ->whereIn('p.net_ext_id', [ 5, 6 ])
            ->get();

        foreach ($purchases as $purchase) {
            $cleans += $purchase->goods;
        }

        // Additional calculations
        $grossSalesBudget = 0;
        $netSalesBudget = 0;
        $costOfSalesBudget = 0;
        $budgetType = 0;

        if (!is_null($tradingAccount)) {
            $budgetType = $tradingAccount->budget_type_id;

            $budgetFrom = 1;
            $budgetTo = 12;
            $budgetDate = Carbon::parse($tradingAccount->budget_start_date);
            $startMonth = $startDate->format('Y-m');
            $endMonth = $endDate->format('Y-m');

            for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++) {
                $budgetMonth = $budgetDate->format('Y-m');

                if ($startMonth == $budgetMonth) {
                    $budgetFrom = $monthIndex;
                }

                if ($endMonth == $budgetMonth) {
                    $budgetTo = $monthIndex;
                }

                $budgetDate->addMonth();
            }

            for ($i = $budgetFrom; $i <= $budgetTo; $i++) {
                $field = 'gross_sales_month_' . $i;
                $grossSalesBudget += $tradingAccount->$field;

                $field = 'net_sales_month_' . $i;
                $netSalesBudget += $tradingAccount->$field;

                $field = 'cost_of_sales_month_' . $i;
                $costOfSalesBudget += $tradingAccount->$field;
            }
        }

        $netSalesActual = (($grossSalesActual * .9) / 1.09) + (($grossSalesActual * .1) / 1.23);

        $grossProfitGrossActual = $grossSalesActual - $costOfSalesActual;
        $grossProfitNetActual = $netSalesActual - $costOfSalesActual;

        $grossProfitGrossBudget = $grossSalesBudget - $costOfSalesBudget;
        $grossProfitNetBudget = $netSalesBudget - $costOfSalesBudget;

        $grossSalesPercent = $grossSalesBudget != 0 ? $grossSalesActual / $grossSalesBudget * 100 : 0;
        $netSalesPercent = $netSalesBudget != 0 ? $netSalesActual / $netSalesBudget * 100 : 0;
        $costOfSalesPercent = $costOfSalesBudget != 0 ? $costOfSalesActual / $costOfSalesBudget * 100 : 0;

        $grossProfitGrossPercent = $grossSalesActual != 0 ? $grossProfitGrossActual / $grossSalesActual * 100 : 0;
        $grossProfitNetPercent = $netSalesActual != 0 ? $grossProfitNetActual / $netSalesActual * 100 : 0;

        // Data
        $unit = [
            'id'                 => $unit->unit_id,
            'name'               => $unit->unit_name,
            'currencySymbol'     => $currencySymbol,
            'grossSalesBudget'   => round($grossSalesBudget),
            'grossSalesActual'   => round($grossSalesActual),
            'grossSalesPercent'  => round($grossSalesPercent),
            'netSalesBudget'     => round($netSalesBudget),
            'netSalesActual'     => round($netSalesActual),
            'netSalesPercent'    => round($netSalesPercent),
            'costOfSalesBudget'  => round($costOfSalesBudget),
            'costOfSalesActual'  => round($costOfSalesActual),
            'costOfSalesPercent' => round($costOfSalesPercent),
            'gpGrossActual'      => round($grossProfitGrossActual),
            'gpGrossBudget'      => round($grossProfitGrossBudget),
            'gpGrossPercent'     => round($grossProfitGrossPercent),
            'gpNetActual'        => round($grossProfitNetActual),
            'gpNetBudget'        => round($grossProfitNetBudget),
            'gpNetPercent'       => round($grossProfitNetPercent),
            'lePurchase'         => $purchaseLatestEntry !== 0 ? date('d-m-Y', $purchaseLatestEntry) : '',
            'leSales'            => $salesLatestEntry !== 0 ? date('d-m-Y', $salesLatestEntry) : '',
            'cleaningDisp'       => round($cleans),
            'cashLodge'          => round($cashLodge),
            'showGrossChart'     => $budgetType == 0 || $budgetType == BudgetType::BUDGET_TYPE_GP,
            'showNetChart'       => $budgetType == 0 || $budgetType == BudgetType::BUDGET_TYPE_NET,
        ];

        return response()->json($unit);
    }
}
