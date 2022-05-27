<?php

namespace App\Console\Commands;

use App\CashSales;
use App\Lodgement;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CreateLodgements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lodgements:create';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill lodgements table from the cash sales.';

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
        $cashSales = CashSales::whereNull('lodgement_id')
            ->where(function ($query)
            {
                $query->where('lodge_cash', '!=', 0)
                    ->orWhere('lodge_coin', '!=', 0);
            })
            ->get(
                [
                    'unit_id',
                    'lodge_date',
                    'lodge_cash',
                    'lodge_coin',
                    'lodge_number',
                    'g4s_bag',
                    'supervisor_id',
                ]
            );

        foreach ($cashSales as $cashSale) {
            // Save Lodgement
            $lodgement = new Lodgement();
            $lodgement->unit_id = $cashSale->unit_id;
            $lodgement->date = Carbon::parse($cashSale->lodge_date)->format('Y-m-d');
            $lodgement->cash = $cashSale->lodge_cash;
            $lodgement->coin = $cashSale->lodge_coin;
            $lodgement->slip_number = $cashSale->lodge_number;
            $lodgement->bag_number = $cashSale->g4s_bag;
            $lodgement->created_by = $cashSale->supervisor_id;
            $lodgement->save();

            // Update Cash Sale ID
            $cashSale->lodgement_id = $lodgement->lodgement_id;
            $cashSale->save();
        }
    }
}
