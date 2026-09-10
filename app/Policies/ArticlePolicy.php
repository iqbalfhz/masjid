<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Article;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ArticlePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:article');
    }

    public function view(AuthUser $authUser, Article $article): bool
    {
        return $authUser->can('view:article');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:article');
    }

    public function update(AuthUser $authUser, Article $article): bool
    {
        return $authUser->can('update:article');
    }

    public function delete(AuthUser $authUser, Article $article): bool
    {
        return $authUser->can('delete:article');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:article');
    }

    public function restore(AuthUser $authUser, Article $article): bool
    {
        return $authUser->can('restore:article');
    }

    public function forceDelete(AuthUser $authUser, Article $article): bool
    {
        return $authUser->can('force_delete:article');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:article');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:article');
    }

    public function replicate(AuthUser $authUser, Article $article): bool
    {
        return $authUser->can('replicate:article');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:article');
    }

    /**
     * Menyetujui atau menolak konten yang menunggu approval (PRD 5.3).
     */
    public function approve(AuthUser $authUser, ?Article $article = null): bool
    {
        return $authUser->can('approve:article');
    }
}
