<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'year',
        'slug',
        'header',
        'description',
        'img_url',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'integer',
        'header' => 'array',
        'description' => 'array',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'project_badge');
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }
}
