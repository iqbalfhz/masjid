<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Announcement;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:announcement');
    }

    public function view(AuthUser $authUser, Announcement $announcement): bool
    {
        return $authUser->can('view:announcement');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:announcement');
    }

    public function update(AuthUser $authUser, Announcement $announcement): bool
    {
        return $authUser->can('update:announcement');
    }

    public function delete(AuthUser $authUser, Announcement $announcement): bool
    {
        return $authUser->can('delete:announcement');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:announcement');
    }

    public function restore(AuthUser $authUser, Announcement $announcement): bool
    {
        return $authUser->can('restore:announcement');
    }

    public function forceDelete(AuthUser $authUser, Announcement $announcement): bool
    {
        return $authUser->can('force_delete:announcement');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:announcement');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:announcement');
    }

    public function replicate(AuthUser $authUser, Announcement $announcement): bool
    {
        return $authUser->can('replicate:announcement');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:announcement');
    }

    /**
     * Menyetujui atau menolak konten yang menunggu approval (PRD 5.3).
     */
    public function approve(AuthUser $authUser, ?Announcement $announcement = null): bool
    {
        return $authUser->can('approve:announcement');
    }
}
