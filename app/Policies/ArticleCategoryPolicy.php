<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ArticleCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ArticleCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:article_category');
    }

    public function view(AuthUser $authUser, ArticleCategory $articleCategory): bool
    {
        return $authUser->can('view:article_category');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:article_category');
    }

    public function update(AuthUser $authUser, ArticleCategory $articleCategory): bool
    {
        return $authUser->can('update:article_category');
    }

    public function delete(AuthUser $authUser, ArticleCategory $articleCategory): bool
    {
        return $authUser->can('delete:article_category');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:article_category');
    }

    public function restore(AuthUser $authUser, ArticleCategory $articleCategory): bool
    {
        return $authUser->can('restore:article_category');
    }

    public function forceDelete(AuthUser $authUser, ArticleCategory $articleCategory): bool
    {
        return $authUser->can('force_delete:article_category');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:article_category');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:article_category');
    }

    public function replicate(AuthUser $authUser, ArticleCategory $articleCategory): bool
    {
        return $authUser->can('replicate:article_category');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:article_category');
    }
}
