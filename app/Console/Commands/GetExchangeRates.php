<?php

namespace App\Console\Commands;

use App\Currency;
use App\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GetExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rates:get';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store exchange rates to the DB';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $todayRatesCnt = ExchangeRate::where('date', Carbon::now()->format('Y-m-d'))->count();

            if ($todayRatesCnt > 0) {
                throw new \Exception('Rates for current day already exist.');
            }

            $xmlData = file_get_contents('https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');

            if (!$xmlData) {
                throw new \Exception('File not found.');
            }

            $xmlObj = new \SimpleXMLElement($xmlData);

            $ratesList = [ 'EUR' => 1 ];
            foreach ($xmlObj->Cube->Cube->Cube as $rate) {
                if (!isset($rate['currency']) || !isset($rate['rate'])) {
                    throw new \Exception('Incorrect file structure.');
                }

                $currency = strtoupper($rate['currency']);
                $rate = (float)$rate['rate'];

                $ratesList[$currency] = $rate;
            }

            $currencies = Currency::all();
            $date = Carbon::now();

            foreach ($currencies as $domesticCurrency) {
                foreach ($currencies as $foreignCurrency) {
                    if (!array_key_exists($domesticCurrency->currency_code, $ratesList)) {
                        throw new \Exception("Exchange rate for {$domesticCurrency->currency_code} not found.");
                    }

                    if (!array_key_exists($foreignCurrency->currency_code, $ratesList)) {
                        throw new \Exception("Exchange rate for {$foreignCurrency->currency_code} not found.");
                    }

                    $domesticRate = $ratesList[$domesticCurrency->currency_code];
                    $foreignRate = $ratesList[$foreignCurrency->currency_code];

                    $exchangeRate = $foreignRate / $domesticRate;

                    ExchangeRate::create([
                        'domestic_currency_id' => $domesticCurrency->currency_id,
                        'foreign_currency_id'  => $foreignCurrency->currency_id,
                        'exchange_rate'        => round($exchangeRate, 4),
                        'date'                 => $date,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Send email to the admin
            Mail::raw($e->getMessage(), function ($message)
            {
                $message->to('chris.primo@primosolutions.ie');
                $message->subject('Error parsing exchange rates file');
            });
        }
    }
}
