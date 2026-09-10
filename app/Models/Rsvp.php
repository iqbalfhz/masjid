<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['rsvpable_id', 'rsvpable_type', 'name', 'phone', 'person_count', 'note'])]
class Rsvp extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'person_count' => 'integer',
        ];
    }

    public function rsvpable(): MorphTo
    {
        return $this->morphTo();
    }
}
