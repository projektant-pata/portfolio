<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'name',
        'color',
    ];

    protected $casts = [
        'name' => 'array',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function experiences(): BelongsToMany
    {
        return $this->belongsToMany(Experience::class, 'experience_badge');
    }
}
