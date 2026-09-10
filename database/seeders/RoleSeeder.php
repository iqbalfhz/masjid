<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Role;
use App\Support\PermissionMatrix;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (PermissionMatrix::allPermissions() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (UserRole::cases() as $case) {
            $role = Role::query()->updateOrCreate(
                ['name' => $case->value, 'guard_name' => 'web'],
                ['level' => $case->level(), 'label' => $case->getLabel()],
            );

            $role->syncPermissions(
                $case === UserRole::Superadmin
                    ? PermissionMatrix::allPermissions()
                    : PermissionMatrix::permissionsFor($case->value),
            );
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
