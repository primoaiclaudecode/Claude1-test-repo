<?php

namespace App\Console\Commands;

use App\Mail\CarReminderUnit;
use App\Mail\CarReminderUser;
use App\User;
use App\UserGroup;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CarReminder extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'car:reminder';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Find not closed car problems and send reminders';

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
		// Get data
		$carProblems = DB::table('problems')
			->select(
				[
					'problems.id',
					'problems.problem_date as problemDate',
					'users.username as userName',
					'users.user_email as userEmail',
					'unit_users.user_email as managerEmail',
					'units.unit_name as unitName',
					'problem_types.title as problemName',
				]
			)
			->leftJoin('users', 'users.user_id', '=', 'problems.user_id')
			->leftJoin('users as unit_users', 'unit_users.user_id', '=', 'users.ops_mgr')
			->leftJoin('problem_types', 'problem_types.id', '=', 'problems.problem_type')
			->leftJoin('units', 'units.unit_id', '=', 'problems.unit_id')
			->whereNull('problems.closed_date')
			->where('problems.problem_date', '<=', DB::raw('DATE_SUB(NOW(), INTERVAL 30 DAY)'))
			->get();

		// Add duration
		$reportData = $carProblems->map(function ($item) {
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

		// Send emails to users
		foreach ($reportData->groupBy('userEmail') as $email => $reportItems) {
			if ($email == '') {
				continue;
			}

			$titleIds = $reportItems->pluck('id')->unique()->toArray();

			Mail::to($email)->send(
				new CarReminderUser(
					'SAM Alert!!! CAR#(' . implode(', ', $titleIds). ')',
					$reportItems
				)
			);

			// Debug info
			$this->info('==============================================');
			$this->info("Email to user - {$email}");
			$this->info('==============================================');
			
			foreach ($reportItems as $reportItem) {
				$this->info('');
				$this->info("CAR#:                              {$reportItem->id}");
				$this->info("Date report opened::               {$reportItem->problemDate}");
				$this->info("How long has report been opened:   {$reportItem->problemDuration}");
				$this->info("User:                              {$reportItem->userName}");
				$this->info("Unit:                              {$reportItem->unitName}");
				$this->info("Problem type:                      {$reportItem->problemName}");
				$this->info('');
			}
		}

		// Send emails to unite
		foreach ($reportData->groupBy('managerEmail') as $email => $reportItems) {
			if ($email == '') {
				continue;
			}

			$titleUsers = $reportItems->pluck('userName')->unique()->toArray();

			Mail::to($email)->send(
				new CarReminderUnit(
					'SAM Alert!!! Problem report open > 30 days for (' . implode(', ', $titleUsers). ')',
					$reportItems->groupBy('userName')
				)
			);

			// Debug info
			$this->info('==============================================');
			$this->info("Email to manager - {$email}");
			$this->info('==============================================');
			
			foreach ($reportItems->groupBy('userName') as $userName => $userItems) {
				foreach ($userItems as $reportItem) {
					$this->info('');
					$this->info("Problem Report summary for {$userName}");
					$this->info('');
					$this->info("CAR#:                              {$reportItem->id}");
					$this->info("Date report opened::               {$reportItem->problemDate}");
					$this->info("How long has report been opened:   {$reportItem->problemDuration}");
					$this->info("User:                              {$reportItem->userName}");
					$this->info("Unit:                              {$reportItem->unitName}");
					$this->info("Problem type:                      {$reportItem->problemName}");
					$this->info('');
				}
			}
		}
	}
}
