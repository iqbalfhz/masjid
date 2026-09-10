<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Testimonial;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TestimonialPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:testimonial');
    }

    public function view(AuthUser $authUser, Testimonial $testimonial): bool
    {
        return $authUser->can('view:testimonial');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:testimonial');
    }

    public function update(AuthUser $authUser, Testimonial $testimonial): bool
    {
        return $authUser->can('update:testimonial');
    }

    public function delete(AuthUser $authUser, Testimonial $testimonial): bool
    {
        return $authUser->can('delete:testimonial');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:testimonial');
    }

    public function restore(AuthUser $authUser, Testimonial $testimonial): bool
    {
        return $authUser->can('restore:testimonial');
    }

    public function forceDelete(AuthUser $authUser, Testimonial $testimonial): bool
    {
        return $authUser->can('force_delete:testimonial');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:testimonial');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:testimonial');
    }

    public function replicate(AuthUser $authUser, Testimonial $testimonial): bool
    {
        return $authUser->can('replicate:testimonial');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:testimonial');
    }

    /**
     * Menyetujui atau menolak masukan jamaah sebelum tayang publik (PRD 5.3).
     */
    public function moderate(AuthUser $authUser, ?Testimonial $testimonial = null): bool
    {
        return $authUser->can('moderate:testimonial');
    }
}
