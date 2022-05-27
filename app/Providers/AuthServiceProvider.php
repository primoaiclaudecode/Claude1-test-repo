<?php

namespace App\Providers;

use App\Services\GuardService;
use App\User;
use App\UserGroup;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('unit-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::USER_GROUP_UNIT,
                    UserGroup::USER_GROUP_OPERATIONS,
                    UserGroup::USER_GROUP_HQ,
                    UserGroup::USER_GROUP_MANAGEMENT,
                    UserGroup::USER_GROUP_ADMIN,
                    UserGroup::USER_GROUP_SU,
                ]
            );
        });

        Gate::define('operations-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::USER_GROUP_OPERATIONS,
                    UserGroup::USER_GROUP_HQ,
                    UserGroup::USER_GROUP_MANAGEMENT,
                    UserGroup::USER_GROUP_ADMIN,
                    UserGroup::USER_GROUP_SU,
                ]
            );
        });

        Gate::define('hq-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::USER_GROUP_HQ,
                    UserGroup::USER_GROUP_MANAGEMENT,
                    UserGroup::USER_GROUP_ADMIN,
                    UserGroup::USER_GROUP_SU,
                ]
            );
        });

        Gate::define('management-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::USER_GROUP_MANAGEMENT,
                    UserGroup::USER_GROUP_SU,
                ]
            );
        });

        Gate::define('admin-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::USER_GROUP_ADMIN,
                    UserGroup::USER_GROUP_SU,
                ]
            );
        });

        Gate::define('su-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::USER_GROUP_SU,
                ]
            );
        });

        Gate::define('limited-access-user-group', function ($user)
        {
            return GuardService::checkGroupAccess(
                $user->user_id,
                [
                    UserGroup::LIMITED_ACCESS,
                ]
            );
        });
    }
}
