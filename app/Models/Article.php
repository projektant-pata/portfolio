<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Article extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'slug',
        'header',
        'description',
        'content',
        'date',
        'published_at',
        'thumbnail_url',
        'user_id',
        'sort_order',
    ];

    protected $casts = [
        'header' => 'array',
        'description' => 'array',
        'content' => 'array',
        'date' => 'date',
        'published_at' => 'datetime',
    ];

    public function getTranslation(string $field, string $locale, string $fallback = 'en'): string
    {
        $value = $this->{$field};

        if (! is_array($value)) {
            return '';
        }

        return $value[$locale] ?? $value[$fallback] ?? '';
    }

    /**
     * Articles visible to the public: published, and not scheduled for later.
     *
     * @param  Builder<Article>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->lessThanOrEqualTo(now());
    }

    /**
     * Whole minutes at 200 words per minute, from the Markdown source.
     *
     * `str_word_count()` is deliberately avoided: it drops Czech diacritics
     * and would undercount every `cs` post.
     */
    public function readingTime(string $locale): int
    {
        $words = preg_split('/\s+/u', trim($this->getTranslation('content', $locale)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return max(1, (int) ceil(count($words) / 200));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'article_badge');
    }
}
