<?php

namespace App\Console\Commands;

use App\PhasedBudgetUnitRow;
use Illuminate\Console\Command;

class ToggleRowsVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rows-visibility:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update data in the "phased_budget_unit_rows" table to store visible rows instead of hidden.';

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
    	$phasedBudgetRows = PhasedBudgetUnitRow::$rows;
    	
	    $hiddenRows = [];
	    
	    foreach (PhasedBudgetUnitRow::all() as $hiddenRow) {
	    	$hiddenRows[$hiddenRow->user_id][$hiddenRow->unit_id][] = $hiddenRow->row_index;
	    }
	    
	    foreach ($hiddenRows as $userId => $unitRows) {
	    	foreach ($unitRows as $unitId => $rowIndexes) {
	    		// Clear old rows
			    PhasedBudgetUnitRow::where('user_id', $userId)->where('unit_id', $unitId)->delete();
			    
				// Insert new rows	    
	    		foreach ($phasedBudgetRows as $rowIndex => $title) {
	    			// Skip hidden rows
	    			if (in_array($rowIndex, $rowIndexes)) {
	    				continue;
				    }
	    			
	    			PhasedBudgetUnitRow::create(
	    				[
	    					'user_id' => $userId,
						    'unit_id' => $unitId,
						    'row_index' => $rowIndex
					    ]
				    );
			    }
		    }
	    }
    }
}
