<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\RecordsActivity;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'avatar_path', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, RecordsActivity;

    /**
     * Password dan token sengaja tidak ikut tercatat di Log Aktivitas.
     *
     * @return list<string>
     */
    public function activityLoggedAttributes(): array
    {
        return ['name', 'email', 'phone', 'is_active'];
    }

    public function activityLogName(): string
    {
        return 'user';
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->roles()->exists();
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::url($this->avatar_path) : null;
    }

    public function isSuperadmin(): bool
    {
        return $this->hasRole(UserRole::Superadmin->value);
    }

    /**
     * Level hierarki terkuat yang dimiliki user (semakin kecil semakin tinggi).
     */
    public function roleLevel(): int
    {
        return (int) ($this->roles->min('level') ?? 99);
    }

    /**
     * Superadmin & Admin boleh mengelola user, tapi hanya untuk role yang
     * levelnya di bawah level mereka sendiri (PRD 5.2.15).
     */
    public function canManageRoleLevel(int $level): bool
    {
        if ($this->isSuperadmin()) {
            return true;
        }

        return $level > $this->roleLevel();
    }
}
