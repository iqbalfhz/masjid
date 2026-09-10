<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

/**
 * @property int $level
 * @property string|null $label
 */
class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'level', 'label'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
    }

    public function displayName(): string
    {
        return $this->label ?? str($this->name)->replace('_', ' ')->title()->value();
    }
}
