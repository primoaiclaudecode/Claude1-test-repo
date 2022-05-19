<?php

namespace App\Console\Commands;

use App\Currency;
use App\ExchangeRate;
use Carbon\Carbon;
use Illuminate\Console\Command;

class HistoryExchangeRates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rates:history';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Store exchange rates history to the DB';

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
    	// Clear prev data
	    ExchangeRate::query()->truncate();

	    // Insert new data
	    $startDate = Carbon::parse('2013-01-02');
	    $endDate = Carbon::now();
	    
	    // Read rates
        $historyStr = file_get_contents('https://api.exchangeratesapi.io/history?start_at=' . $startDate->format('Y-m-d'). '&end_at=' . $endDate->format('Y-m-d'));
        
        $history = json_decode($historyStr);
        
        // Prepare rates
        $historyRatesList = [];
        foreach ($history->rates as $date => $rate) {
	        $historyRatesList[$date] = $rate;
        }

        // List contains rates not for all dates
        $prevRate = null;
        while($startDate <= $endDate) {
        	$key = $startDate->format('Y-m-d');

			// Copy rate from previous date        	
			if (!isset($historyRatesList[$key])) {
				$this->info($key);

				$historyRatesList[$key] = $prevRate;
			}

	        $prevRate = $historyRatesList[$key];

        	$startDate->addDay();
        }
        
        ksort($historyRatesList);

        // Insert rates
	    $currencies = Currency::all();
        foreach ($historyRatesList as $date => $historyRates) {
        	// Prepare rates list
	        $ratesList = ['EUR' => 1];
	        foreach ($historyRates as $currency => $rate) {
		        $ratesList[$currency] = (float)$rate;
	        }

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
				        'foreign_currency_id' => $foreignCurrency->currency_id,
				        'exchange_rate' => round($exchangeRate, 4),
				        'date' => $date
			        ]);
		        }
	        }
        }
    }
}
