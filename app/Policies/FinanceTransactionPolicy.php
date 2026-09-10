<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FinanceTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FinanceTransactionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:finance_transaction');
    }

    public function view(AuthUser $authUser, FinanceTransaction $financeTransaction): bool
    {
        return $authUser->can('view:finance_transaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:finance_transaction');
    }

    public function update(AuthUser $authUser, FinanceTransaction $financeTransaction): bool
    {
        return $authUser->can('update:finance_transaction');
    }

    public function delete(AuthUser $authUser, FinanceTransaction $financeTransaction): bool
    {
        return $authUser->can('delete:finance_transaction');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:finance_transaction');
    }

    public function restore(AuthUser $authUser, FinanceTransaction $financeTransaction): bool
    {
        return $authUser->can('restore:finance_transaction');
    }

    public function forceDelete(AuthUser $authUser, FinanceTransaction $financeTransaction): bool
    {
        return $authUser->can('force_delete:finance_transaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:finance_transaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:finance_transaction');
    }

    public function replicate(AuthUser $authUser, FinanceTransaction $financeTransaction): bool
    {
        return $authUser->can('replicate:finance_transaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:finance_transaction');
    }
}
