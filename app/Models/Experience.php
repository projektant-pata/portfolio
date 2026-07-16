<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'is_special',
        'title',
        'subtitle',
        'content',
        'year',
        'image_path',
        'links',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_special' => 'boolean',
        'links' => 'array',
        'title' => 'array',
        'subtitle' => 'array',
        'content' => 'array',
        'year' => 'array',
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
        return $this->belongsToMany(Badge::class, 'experience_badge');
    }
}
