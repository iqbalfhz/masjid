<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MosqueSetting;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

/**
 * Pengaturan Umum tidak punya Filament Resource (dikelola lewat halaman
 * khusus), jadi policy-nya ditulis manual dengan nama permission yang sama
 * seperti resource lain.
 */
class MosqueSettingPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:mosque_setting');
    }

    public function view(AuthUser $authUser, MosqueSetting $mosqueSetting): bool
    {
        return $authUser->can('view:mosque_setting');
    }

    public function create(AuthUser $authUser): bool
    {
        return false;
    }

    public function update(AuthUser $authUser, MosqueSetting $mosqueSetting): bool
    {
        return $authUser->can('update:mosque_setting');
    }

    public function delete(AuthUser $authUser, MosqueSetting $mosqueSetting): bool
    {
        return false;
    }
}
