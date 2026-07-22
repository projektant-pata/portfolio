<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutCard extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'title',
        'text',
        'sort_order',
    ];

    protected $casts = [
        'title' => 'array',
        'text' => 'array',
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
