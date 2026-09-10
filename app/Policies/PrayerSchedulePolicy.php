<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PrayerSchedule;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class PrayerSchedulePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:prayer_schedule');
    }

    public function view(AuthUser $authUser, PrayerSchedule $prayerSchedule): bool
    {
        return $authUser->can('view:prayer_schedule');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:prayer_schedule');
    }

    public function update(AuthUser $authUser, PrayerSchedule $prayerSchedule): bool
    {
        return $authUser->can('update:prayer_schedule');
    }

    public function delete(AuthUser $authUser, PrayerSchedule $prayerSchedule): bool
    {
        return $authUser->can('delete:prayer_schedule');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:prayer_schedule');
    }

    public function restore(AuthUser $authUser, PrayerSchedule $prayerSchedule): bool
    {
        return $authUser->can('restore:prayer_schedule');
    }

    public function forceDelete(AuthUser $authUser, PrayerSchedule $prayerSchedule): bool
    {
        return $authUser->can('force_delete:prayer_schedule');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:prayer_schedule');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:prayer_schedule');
    }

    public function replicate(AuthUser $authUser, PrayerSchedule $prayerSchedule): bool
    {
        return $authUser->can('replicate:prayer_schedule');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:prayer_schedule');
    }
}
