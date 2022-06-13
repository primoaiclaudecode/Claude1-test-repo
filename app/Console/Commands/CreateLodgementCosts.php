<?php

namespace App\Console\Commands;

use App\LodgementCost;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateLodgementCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lodgement-costs:create';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fill lodgement_costs table from lodgements.';

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
        if (count(LodgementCost::all()) > 0) {
            return;
        }

        $lodgements = DB::table('lodgements as l')
            ->select(
                [
                    'l.lodgement_id',
                    'l.cash',
                    'l.coin',
                    'u.default_currency',
                ]
            )
            ->leftJoin('units AS u', 'l.unit_id', '=', 'u.unit_id')
            ->get();

        foreach ($lodgements as $lodgement) {
            $lodgementCost = new LodgementCost();

            $lodgementCost->lodgement_id = $lodgement->lodgement_id;
            $lodgementCost->currency_id = $lodgement->default_currency;
            $lodgementCost->cash = $lodgement->cash;
            $lodgementCost->coin = $lodgement->coin;

            $lodgementCost->save();
        }
    }
}
