<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BoardMember;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BoardMemberPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:board_member');
    }

    public function view(AuthUser $authUser, BoardMember $boardMember): bool
    {
        return $authUser->can('view:board_member');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:board_member');
    }

    public function update(AuthUser $authUser, BoardMember $boardMember): bool
    {
        return $authUser->can('update:board_member');
    }

    public function delete(AuthUser $authUser, BoardMember $boardMember): bool
    {
        return $authUser->can('delete:board_member');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:board_member');
    }

    public function restore(AuthUser $authUser, BoardMember $boardMember): bool
    {
        return $authUser->can('restore:board_member');
    }

    public function forceDelete(AuthUser $authUser, BoardMember $boardMember): bool
    {
        return $authUser->can('force_delete:board_member');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:board_member');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:board_member');
    }

    public function replicate(AuthUser $authUser, BoardMember $boardMember): bool
    {
        return $authUser->can('replicate:board_member');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:board_member');
    }
}
