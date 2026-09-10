<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FacilityBooking;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FacilityBookingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:facility_booking');
    }

    public function view(AuthUser $authUser, FacilityBooking $facilityBooking): bool
    {
        return $authUser->can('view:facility_booking');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:facility_booking');
    }

    public function update(AuthUser $authUser, FacilityBooking $facilityBooking): bool
    {
        return $authUser->can('update:facility_booking');
    }

    public function delete(AuthUser $authUser, FacilityBooking $facilityBooking): bool
    {
        return $authUser->can('delete:facility_booking');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:facility_booking');
    }

    public function restore(AuthUser $authUser, FacilityBooking $facilityBooking): bool
    {
        return $authUser->can('restore:facility_booking');
    }

    public function forceDelete(AuthUser $authUser, FacilityBooking $facilityBooking): bool
    {
        return $authUser->can('force_delete:facility_booking');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:facility_booking');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:facility_booking');
    }

    public function replicate(AuthUser $authUser, FacilityBooking $facilityBooking): bool
    {
        return $authUser->can('replicate:facility_booking');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:facility_booking');
    }

    /**
     * Menyetujui atau menolak konten yang menunggu approval (PRD 5.3).
     */
    public function approve(AuthUser $authUser, ?FacilityBooking $facilityBooking = null): bool
    {
        return $authUser->can('approve:facility_booking');
    }
}
