<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['endpoint', 'endpoint_hash', 'public_key', 'auth_token', 'content_encoding', 'enabled_prayers', 'minutes_before', 'user_agent', 'last_notified_at'])]
class PushSubscription extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enabled_prayers' => 'array',
            'last_notified_at' => 'datetime',
        ];
    }

    public function wantsPrayer(string $prayer): bool
    {
        $enabled = $this->enabled_prayers;

        return $enabled === null || in_array($prayer, $enabled, true);
    }
}
