<?php

namespace App\Console\Commands;

use App\PhasedBudget;
use App\Unit;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\PhasedBudgetReminder as PhasedBudgetReminderEmail;

class PhasedBudgetReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phased-budget:reminder';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a message to the Operations Managers about budget expiration';

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
        $budgetDate = Carbon::now()->addMonth()->format('Y-m-d');

        $budgets = PhasedBudget::where('budget_end_date', $budgetDate)->get();

        foreach ($budgets as $budget) {
            $unit = Unit::find($budget->unit_id);

            if (!$unit) {
                continue;
            }

            $operationsManagers = $unit->ops_manager_user_id !== '' ? explode(',', $unit->ops_manager_user_id) : [];

            $emails = [];

            if (count($operationsManagers) > 0) {
                $emails = User::whereIn('user_id', $operationsManagers)->pluck('user_email');
            }

            foreach ($emails as $email) {
                Mail::to($email)->send(
                    new PhasedBudgetReminderEmail($unit->unit_name, $budgetDate)
                );
            }
        }
    }
}
