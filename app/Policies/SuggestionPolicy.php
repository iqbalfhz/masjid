<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Suggestion;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SuggestionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:suggestion');
    }

    public function view(AuthUser $authUser, Suggestion $suggestion): bool
    {
        return $authUser->can('view:suggestion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:suggestion');
    }

    public function update(AuthUser $authUser, Suggestion $suggestion): bool
    {
        return $authUser->can('update:suggestion');
    }

    public function delete(AuthUser $authUser, Suggestion $suggestion): bool
    {
        return $authUser->can('delete:suggestion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:suggestion');
    }

    public function restore(AuthUser $authUser, Suggestion $suggestion): bool
    {
        return $authUser->can('restore:suggestion');
    }

    public function forceDelete(AuthUser $authUser, Suggestion $suggestion): bool
    {
        return $authUser->can('force_delete:suggestion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:suggestion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:suggestion');
    }

    public function replicate(AuthUser $authUser, Suggestion $suggestion): bool
    {
        return $authUser->can('replicate:suggestion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:suggestion');
    }
}
