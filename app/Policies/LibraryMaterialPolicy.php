<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\LibraryMaterial;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class LibraryMaterialPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:library_material');
    }

    public function view(AuthUser $authUser, LibraryMaterial $libraryMaterial): bool
    {
        return $authUser->can('view:library_material');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:library_material');
    }

    public function update(AuthUser $authUser, LibraryMaterial $libraryMaterial): bool
    {
        return $authUser->can('update:library_material');
    }

    public function delete(AuthUser $authUser, LibraryMaterial $libraryMaterial): bool
    {
        return $authUser->can('delete:library_material');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:library_material');
    }

    public function restore(AuthUser $authUser, LibraryMaterial $libraryMaterial): bool
    {
        return $authUser->can('restore:library_material');
    }

    public function forceDelete(AuthUser $authUser, LibraryMaterial $libraryMaterial): bool
    {
        return $authUser->can('force_delete:library_material');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:library_material');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:library_material');
    }

    public function replicate(AuthUser $authUser, LibraryMaterial $libraryMaterial): bool
    {
        return $authUser->can('replicate:library_material');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:library_material');
    }
}
