<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Link extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'alt',
        'img_url',
        'url',
    ];

    protected $casts = [
        'alt' => 'array',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
