<?php

namespace App\Http\Controllers\Traits;

use App\Status;
use App\Unit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

trait UserUnits
{
    /**
     * Get units list according to the user level
     *
     * @param boolean $onlyActive
     *
     * @return Collection
     */
    public function getUserUnits($onlyActive = true)
    {
        $user = auth()->user();

        $query = Unit::orderBy('unit_name')
            ->when($onlyActive, function ($query)
            {
                return $query->where('status_id', '!=', Status::STATUS_UNIT_INACTIVE);
            });

        // HQ+ level
        if (Gate::allows('hq-user-group')) {
            return $query->get();
        }

        $unitMembersIds = !empty($user->unit_member)? explode(",", $user->unit_member):[];
        $opsGroupMember = !empty($user->ops_group_member)? explode(",", $user->ops_group_member):[];

        // Operations level
        if (Gate::allows('operations-user-group')) {
            return $query->where(function ($query) use ($user, $unitMembersIds, $opsGroupMember)
            {
                $query->where('ops_manager_user_id', $user->user_id);
                if (!empty($unitMembersIds)) {
                    $query->orWhereIn('unit_id', $unitMembersIds);
                }
                if (!empty($opsGroupMember)) {
                    $query->orWhereRaw(\Helpers::multiAttrsWhere('operations_group', $opsGroupMember));
                }
            })->get();
        }

        // Units level

        return $query->whereIn('unit_id', $unitMembersIds)->get();
    }
}
