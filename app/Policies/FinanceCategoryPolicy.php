<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FinanceCategory;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FinanceCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:finance_category');
    }

    public function view(AuthUser $authUser, FinanceCategory $financeCategory): bool
    {
        return $authUser->can('view:finance_category');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:finance_category');
    }

    public function update(AuthUser $authUser, FinanceCategory $financeCategory): bool
    {
        return $authUser->can('update:finance_category');
    }

    public function delete(AuthUser $authUser, FinanceCategory $financeCategory): bool
    {
        return $authUser->can('delete:finance_category');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:finance_category');
    }

    public function restore(AuthUser $authUser, FinanceCategory $financeCategory): bool
    {
        return $authUser->can('restore:finance_category');
    }

    public function forceDelete(AuthUser $authUser, FinanceCategory $financeCategory): bool
    {
        return $authUser->can('force_delete:finance_category');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:finance_category');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:finance_category');
    }

    public function replicate(AuthUser $authUser, FinanceCategory $financeCategory): bool
    {
        return $authUser->can('replicate:finance_category');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:finance_category');
    }
}
