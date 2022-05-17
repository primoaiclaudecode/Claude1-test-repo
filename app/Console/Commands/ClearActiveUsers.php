<?php

namespace App\Console\Commands;

use App\ActiveUser;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ClearActiveUsers extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'active-users:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Check active users table and delete expired records.';

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
		ActiveUser::where(function ($query) {
			$query->whereNotNull('expired_at')->where('expired_at', '<', Carbon::now());
		})->orWhere(function ($query) {
			$query->whereNull('expired_at')->where('updated_at', '<', Carbon::now()->subMinutes(config('session.lifetime')));
		})->delete();
	}
}
