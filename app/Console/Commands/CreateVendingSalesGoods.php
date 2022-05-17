<?php

namespace App\Console\Commands;

use App\VendingSaleGood;
use App\VendingSales;
use Illuminate\Console\Command;

class CreateVendingSalesGoods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vending-sales-goods:create';

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
	    if (count(VendingSaleGood::all()) > 0) {
	    	return;
	    }
	    
	    $taxCodesIds = [
		    9 => 2,
		    23 => 4,
		    21 => 8
	    ];
	    
	    $netExtIds = [
		    'food' => 1,
		    'minerals' => 3,
		    'confectionary' => 26
	    ];
	    
        $vendingSales = VendingSales::all();
        
        foreach ($vendingSales as $vendingSale) {
        	$netExtList = [];
        	
        	if ($vendingSale->food > 0) {
        		$netExtList[] = [
        			'netExtId' => $netExtIds['food'],
			        'amount' => $vendingSale->food,
			        'taxCodesId' => $vendingSale->net_9 > 0 ? $taxCodesIds[9] : 0
		        ];
	        }

	        if ($vendingSale->minerals > 0) {
		        $netExtList[] = [
			        'netExtId' => $netExtIds['minerals'],
			        'amount' => $vendingSale->minerals,
			        'taxCodesId' => $vendingSale->net_21 > 0 ? $taxCodesIds[21] : $taxCodesIds[23] 
		        ];
	        }

	        if ($vendingSale->confectionary > 0) {
	        	$taxCodeId = 0;
	        	
	        	if ($vendingSale->net_21 > 0) {
			        $taxCodeId = $taxCodesIds[21];
		        }

		        if ($vendingSale->net_23 > 0) {
			        $taxCodeId = $taxCodesIds[23];
		        }
	        	
		        $netExtList[] = [
			        'netExtId' => $netExtIds['confectionary'],
			        'amount' => $vendingSale->confectionary,
			        'taxCodesId' => $taxCodeId
		        ];
	        }

			foreach ($netExtList as $goodItem) {
				if ($goodItem['amount'] == 0 || $goodItem['taxCodesId'] == 0) {
					continue;
				}

				$vendingSaleGood = new VendingSaleGood();
				$vendingSaleGood->vending_sales_id = $vendingSale->vending_sales_id;
				$vendingSaleGood->net_ext_id = $goodItem['netExtId'];
				$vendingSaleGood->amount = $goodItem['amount'];
				$vendingSaleGood->tax_code_id = $goodItem['taxCodesId'];
				$vendingSaleGood->save();
			}
        }
    }
}
