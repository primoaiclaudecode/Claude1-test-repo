<?php

namespace App\Http\Middleware;

use App\ActiveUser;
use Carbon\Carbon;
use Closure;

class TrackUserAction
{
	/**
	 * Track logged in users.
	 * Store logged in user to the Cash and track user actions.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @param string|null $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = null)
	{
		if (!auth()->user()) {
			return $next($request);
		}

		$userId = auth()->user()->user_id;

		$activeUser = ActiveUser::where('user_id', $userId)
			->where('session_token', $request->session()->getToken())
			->first();

		if (!$activeUser) {
			return $next($request);
		}

		$activeUser->updated_at = Carbon::now();

		if (!is_null($activeUser->expired_at)) {
			$activeUser->expired_at = Carbon::now()->addMinutes(config('session.lifetime'));
		}

		$activeUser->save();

		return $next($request);
	}
}
