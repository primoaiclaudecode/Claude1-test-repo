<?php

namespace App\Providers;

use App\Services\GuardService;
use App\UserMenuLink;
use App\UserProfileSetting;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		view()->composer('*', function ($view) {
			$loggedUser = auth()->user();

			try {
				$menuLinkTitles = \Helpers::menuLinkTitles();
				$userMenuLinks = UserMenuLink::where('user_id', $loggedUser->user_id)->orderBy('position')->pluck('link');

				$favouritesMenu = [];

				foreach ($menuLinkTitles as $link => $title) {
					if ($userMenuLinks->contains($link)) {
						$favouritesMenu[] = [
							'link' => $link,
							'title' => $title
						];
					}
				}
			} catch(\Exception $e) {
				$favouritesMenu = [];
			}

			try {
				$userProfileSettings = UserProfileSetting::where('user_id', $loggedUser->user_id)->firstOrFail();

				$showSidebar = $userProfileSettings->show_sidebar == 1;
			} catch (\Exception $e) {
				$showSidebar = true;
			}

			$view->with('favouritesMenu', $favouritesMenu);
			$view->with('showSidebar', $showSidebar);
			$view->with('loggedUserName', $loggedUser ? $loggedUser->username : '');
			$view->with('loggedUserGroup', $loggedUser ? GuardService::getUserGroupName($loggedUser->user_id) : '');
		});
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
}
