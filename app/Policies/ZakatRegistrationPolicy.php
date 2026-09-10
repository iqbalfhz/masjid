<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ZakatRegistration;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ZakatRegistrationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:zakat_registration');
    }

    public function view(AuthUser $authUser, ZakatRegistration $zakatRegistration): bool
    {
        return $authUser->can('view:zakat_registration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:zakat_registration');
    }

    public function update(AuthUser $authUser, ZakatRegistration $zakatRegistration): bool
    {
        return $authUser->can('update:zakat_registration');
    }

    public function delete(AuthUser $authUser, ZakatRegistration $zakatRegistration): bool
    {
        return $authUser->can('delete:zakat_registration');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:zakat_registration');
    }

    public function restore(AuthUser $authUser, ZakatRegistration $zakatRegistration): bool
    {
        return $authUser->can('restore:zakat_registration');
    }

    public function forceDelete(AuthUser $authUser, ZakatRegistration $zakatRegistration): bool
    {
        return $authUser->can('force_delete:zakat_registration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:zakat_registration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:zakat_registration');
    }

    public function replicate(AuthUser $authUser, ZakatRegistration $zakatRegistration): bool
    {
        return $authUser->can('replicate:zakat_registration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:zakat_registration');
    }
}
