<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Study;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class StudyPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:study');
    }

    public function view(AuthUser $authUser, Study $study): bool
    {
        return $authUser->can('view:study');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:study');
    }

    public function update(AuthUser $authUser, Study $study): bool
    {
        return $authUser->can('update:study');
    }

    public function delete(AuthUser $authUser, Study $study): bool
    {
        return $authUser->can('delete:study');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:study');
    }

    public function restore(AuthUser $authUser, Study $study): bool
    {
        return $authUser->can('restore:study');
    }

    public function forceDelete(AuthUser $authUser, Study $study): bool
    {
        return $authUser->can('force_delete:study');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:study');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:study');
    }

    public function replicate(AuthUser $authUser, Study $study): bool
    {
        return $authUser->can('replicate:study');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:study');
    }

    /**
     * Menyetujui atau menolak konten yang menunggu approval (PRD 5.3).
     */
    public function approve(AuthUser $authUser, ?Study $study = null): bool
    {
        return $authUser->can('approve:study');
    }
}
