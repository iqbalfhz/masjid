<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\QurbanRegistration;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class QurbanRegistrationPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:qurban_registration');
    }

    public function view(AuthUser $authUser, QurbanRegistration $qurbanRegistration): bool
    {
        return $authUser->can('view:qurban_registration');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:qurban_registration');
    }

    public function update(AuthUser $authUser, QurbanRegistration $qurbanRegistration): bool
    {
        return $authUser->can('update:qurban_registration');
    }

    public function delete(AuthUser $authUser, QurbanRegistration $qurbanRegistration): bool
    {
        return $authUser->can('delete:qurban_registration');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:qurban_registration');
    }

    public function restore(AuthUser $authUser, QurbanRegistration $qurbanRegistration): bool
    {
        return $authUser->can('restore:qurban_registration');
    }

    public function forceDelete(AuthUser $authUser, QurbanRegistration $qurbanRegistration): bool
    {
        return $authUser->can('force_delete:qurban_registration');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:qurban_registration');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:qurban_registration');
    }

    public function replicate(AuthUser $authUser, QurbanRegistration $qurbanRegistration): bool
    {
        return $authUser->can('replicate:qurban_registration');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:qurban_registration');
    }
}
