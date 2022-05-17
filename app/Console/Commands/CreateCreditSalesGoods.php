<?php

namespace App\Console\Commands;

use App\CreditSaleGood;
use App\CreditSales;
use Illuminate\Console\Command;

class CreateCreditSalesGoods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credit-sales-goods:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    	// Prevent second launch
	    if (count(CreditSaleGood::all()) > 0) {
	    	return;
	    }
	    
	    $taxCodesIds = [
		    9 => 3,
		    23 => 4,
		    21 => 8
	    ];
	    
        $creditSales = CreditSales::all();
        
        foreach ($creditSales as $creditSale) {
        	$netExtList = [];
        	
        	if ($creditSale->gross_9 > 0) {
        		$netExtList[] = [
			        'amount' => $creditSale->gross_9,
			        'taxCodesId' => $taxCodesIds[9]
		        ];
	        }

	        if ($creditSale->gross_23 > 0) {
		        $netExtList[] = [
			        'amount' => $creditSale->gross_23,
			        'taxCodesId' => $taxCodesIds[23]
		        ];
	        }

	        if ($creditSale->gross_21 > 0) {
		        $netExtList[] = [
			        'amount' => $creditSale->gross_21,
			        'taxCodesId' => $taxCodesIds[21]
		        ];
	        }
	        
			foreach ($netExtList as $goodItem) {
				$creditSaleGood = new CreditSaleGood();
				$creditSaleGood->credit_sales_id = $creditSale->credit_sales_id;
				$creditSaleGood->amount = $goodItem['amount'];
				$creditSaleGood->tax_code_id = $goodItem['taxCodesId'];
				$creditSaleGood->save();
			}
        }
    }
}
