<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PathNode extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'year',
        'title',
        'body',
        'is_now',
        'sort_order',
    ];

    protected $casts = [
        'year' => 'array',
        'title' => 'array',
        'body' => 'array',
        'is_now' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }
}
