<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GalleryAlbum;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class GalleryAlbumPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:gallery_album');
    }

    public function view(AuthUser $authUser, GalleryAlbum $galleryAlbum): bool
    {
        return $authUser->can('view:gallery_album');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:gallery_album');
    }

    public function update(AuthUser $authUser, GalleryAlbum $galleryAlbum): bool
    {
        return $authUser->can('update:gallery_album');
    }

    public function delete(AuthUser $authUser, GalleryAlbum $galleryAlbum): bool
    {
        return $authUser->can('delete:gallery_album');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:gallery_album');
    }

    public function restore(AuthUser $authUser, GalleryAlbum $galleryAlbum): bool
    {
        return $authUser->can('restore:gallery_album');
    }

    public function forceDelete(AuthUser $authUser, GalleryAlbum $galleryAlbum): bool
    {
        return $authUser->can('force_delete:gallery_album');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:gallery_album');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:gallery_album');
    }

    public function replicate(AuthUser $authUser, GalleryAlbum $galleryAlbum): bool
    {
        return $authUser->can('replicate:gallery_album');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:gallery_album');
    }
}
