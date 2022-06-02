<?php

namespace App\Http\Controllers;

use App\Currency;
use App\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Session;
use Yajra\Datatables\Datatables;

class ExchangeRateController extends Controller
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
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function index()
    {
        // Currencies
        $currencies = Currency::where('is_default', '!=', 1)->pluck('currency_name', 'currency_id');

        return view('exchange-rates.index', [
            'currencies' => $currencies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\View\View
     */
    public function create()
    {
        // Currencies
        $currencies = Currency::where('currency_code', '!=', 'EUR')->pluck('currency_name', 'currency_id');

        return view('exchange-rates.create', [
            'heading'     => 'Create New Exchange Rate',
            'btn_caption' => 'Create Exchange Rate',
            'date'        => Carbon::now()->format('d-m-Y'),
            'currencies'  => $currencies,
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
            'currencies'     => 'required|array',
            'exchange_rates' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Prevent double insert
            $todayRatesCnt = ExchangeRate::where('date', Carbon::now()->format('Y-m-d'))->count();

            if ($todayRatesCnt > 0) {
                throw new \Exception('Rates for current day already exist.');
            }

            // Rates list
            $eurCurrency = Currency::where('currency_code', 'EUR')->first();

            $ratesList = [
                $eurCurrency->currency_id => 1,
            ];

            foreach ($request->currencies as $index => $currencyId) {
                $exchangeRate = $request->exchange_rates[$index];

                if (!is_numeric($exchangeRate)) {
                    throw new \Exception('Exchange rate must be a number.');
                }

                $ratesList[$currencyId] = (float)$exchangeRate;
            }

            // Store exchange rates
            $currencies = Currency::all();
            $date = Carbon::now();

            foreach ($currencies as $domesticCurrency) {
                foreach ($currencies as $foreignCurrency) {
                    if (!array_key_exists($domesticCurrency->currency_id, $ratesList)) {
                        throw new \Exception("Exchange rate for {$domesticCurrency->currency_code} not found.");
                    }

                    if (!array_key_exists($foreignCurrency->currency_id, $ratesList)) {
                        throw new \Exception("Exchange rate for {$foreignCurrency->currency_code} not found.");
                    }

                    $domesticRate = $ratesList[$domesticCurrency->currency_id];
                    $foreignRate = $ratesList[$foreignCurrency->currency_id];

                    $exchangeRate = $foreignRate / $domesticRate;

                    ExchangeRate::create([
                        'domestic_currency_id' => $domesticCurrency->currency_id,
                        'foreign_currency_id'  => $foreignCurrency->currency_id,
                        'exchange_rate'        => round($exchangeRate, 4),
                        'date'                 => $date,
                    ]);
                }
            }

            DB::commit();

            Session::flash('flash_message', 'Exchange Rate has been added successfully!'); //<--FLASH MESSAGE

            return redirect('/exchange-rates');
        } catch (\Exception $e) {
            DB::rollBack();

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
        // Id is date converted to timestamp
        $date = Carbon::createFromTimestamp($id);

        $eurCurrency = Currency::where('currency_code', 'EUR')->first();

        $exchangeRates = ExchangeRate::where('date', $date->format('Y-m-d'))
            ->where('domestic_currency_id', $eurCurrency->currency_id)
            ->pluck('exchange_rate', 'foreign_currency_id');

        // Currencies
        $currencies = Currency::where('currency_code', '!=', 'EUR')->pluck('currency_name', 'currency_id');

        return view('exchange-rates.create', [
            'heading'       => 'Edit Exchange rate',
            'btn_caption'   => 'Edit Exchange rate',
            'id'            => $id,
            'date'          => $date->format('d-m-Y'),
            'currencies'    => $currencies,
            'exchangeRates' => $exchangeRates,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return \Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'currencies'     => 'required|array',
            'exchange_rates' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            // Id is date converted to timestamp
            $date = Carbon::createFromTimestamp($id);

            // Delete old data
            ExchangeRate::where('date', $date)->delete();

            // Rates list
            $eurCurrency = Currency::where('currency_code', 'EUR')->first();

            $ratesList = [
                $eurCurrency->currency_id => 1,
            ];

            foreach ($request->currencies as $index => $currencyId) {
                $exchangeRate = $request->exchange_rates[$index];

                if (!is_numeric($exchangeRate)) {
                    throw new \Exception('Exchange rate must be a number.');
                }

                $ratesList[$currencyId] = (float)$exchangeRate;
            }

            // Store exchange rates
            $currencies = Currency::all();
            $date = Carbon::now();

            foreach ($currencies as $domesticCurrency) {
                foreach ($currencies as $foreignCurrency) {
                    if (!array_key_exists($domesticCurrency->currency_id, $ratesList)) {
                        throw new \Exception("Exchange rate for {$domesticCurrency->currency_code} not found.");
                    }

                    if (!array_key_exists($foreignCurrency->currency_id, $ratesList)) {
                        throw new \Exception("Exchange rate for {$foreignCurrency->currency_code} not found.");
                    }

                    $domesticRate = $ratesList[$domesticCurrency->currency_id];
                    $foreignRate = $ratesList[$foreignCurrency->currency_id];

                    $exchangeRate = $foreignRate / $domesticRate;

                    ExchangeRate::create([
                        'domestic_currency_id' => $domesticCurrency->currency_id,
                        'foreign_currency_id'  => $foreignCurrency->currency_id,
                        'exchange_rate'        => round($exchangeRate, 4),
                        'date'                 => $date,
                    ]);
                }
            }

            DB::commit();

            Session::flash('flash_message', 'Exchange Rate has been updated successfully!'); //<--FLASH MESSAGE

            return redirect('/exchange-rates');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withErrors($e->getMessage());
        }
    }

    /**
     * Get data for the currencies table
     *
     * @return mixed
     */
    public function json()
    {
        $defaultCurrency = Currency::where('is_default', 1)->first();

        // Exchange rates for EUR
        $exchangeRates = ExchangeRate::orderBy('date', 'desc')
            ->where('domestic_currency_id', $defaultCurrency->currency_id)
            ->whereDate('date', '>=', Carbon::now()->subMonth(6))
            ->get(
                [
                    'foreign_currency_id',
                    'exchange_rate',
                    'date',
                ]
            )
            ->groupBy('date');

        // Currencies
        $currencies = Currency::where('is_default', '!=', 1)->pluck('currency_code', 'currency_id');

        $data = [];
        foreach ($exchangeRates as $date => $ratesGroup) {
            $timestamp = Carbon::parse($date)->timestamp;

            if (!isset($data[$timestamp])) {
                $data[$timestamp] = [
                    'date' => $date,
                ];
            }

            foreach ($currencies as $currencyId => $currencyCode) {
                $currencyRate = $ratesGroup->where('foreign_currency_id', $currencyId)->first();

                $data[$timestamp][$currencyCode] = $currencyRate ? $currencyRate->exchange_rate : 0;
            }
        }

        $dataTableData = collect($data);

        $dataTable = Datatables::of($dataTableData)
            ->setRowId(function ($exchangeRate)
            {
                $timestamp = Carbon::parse($exchangeRate['date'])->timestamp;

                return 'tr_' . $timestamp;
            });

        $dataTable->addColumn('action', function ($exchangeRate)
        {
            $timestamp = Carbon::parse($exchangeRate['date'])->timestamp;

            return '<button type="button" class="btn btn-danger btn-xs" onclick="window.location.href = \'exchange-rates/' . $timestamp . '/edit\'"><i class="fa fa-edit"></i></button>';
        });

        return $dataTable->make();
    }
}