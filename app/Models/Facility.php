<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Models\Concerns\RecordsActivity;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'description', 'capacity', 'image', 'is_active'])]
class Facility extends Model
{
    use HasFactory, HasSlug, RecordsActivity;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function slugSourceColumn(): string
    {
        return 'name';
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(FacilityBooking::class);
    }
}
