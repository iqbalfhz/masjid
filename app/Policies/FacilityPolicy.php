<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Facility;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacilityPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:facility');
    }

    public function view(AuthUser $authUser, Facility $facility): bool
    {
        return $authUser->can('view:facility');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:facility');
    }

    public function update(AuthUser $authUser, Facility $facility): bool
    {
        return $authUser->can('update:facility');
    }

    public function delete(AuthUser $authUser, Facility $facility): bool
    {
        return $authUser->can('delete:facility');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:facility');
    }

    public function restore(AuthUser $authUser, Facility $facility): bool
    {
        return $authUser->can('restore:facility');
    }

    public function forceDelete(AuthUser $authUser, Facility $facility): bool
    {
        return $authUser->can('force_delete:facility');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:facility');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:facility');
    }

    public function replicate(AuthUser $authUser, Facility $facility): bool
    {
        return $authUser->can('replicate:facility');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:facility');
    }
}
