<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Article date shapes. Czech is not a `translatedFormat()` away from
 * correct: the rail needs a numeric month (`bře` is nobody's word) and the
 * day carries an ordinal dot, so the two locales are spelled out here.
 */
final class ArticleDate
{
    /** @var array<int, string> Czech months in the genitive, as a date reads. */
    private const CS_MONTHS = [
        1 => 'ledna', 'února', 'března', 'dubna', 'května', 'června',
        'července', 'srpna', 'září', 'října', 'listopadu', 'prosince',
    ];

    public static function header(CarbonInterface $date, string $locale): string
    {
        if ($locale === 'cs') {
            return sprintf('%d. %s %d', $date->day, self::CS_MONTHS[$date->month], $date->year);
        }

        return $date->format('j F Y');
    }

    public static function railDay(CarbonInterface $date, string $locale): string
    {
        return $locale === 'cs' ? $date->day.'.' : (string) $date->day;
    }

    public static function railMonth(CarbonInterface $date, string $locale): string
    {
        if ($locale === 'cs') {
            return sprintf('%d. %d', $date->month, $date->year);
        }

        return $date->format('M Y');
    }

    /** Month and year for the end-of-list line ("since June 2025"). */
    public static function monthYear(CarbonInterface $date, string $locale): string
    {
        if ($locale === 'cs') {
            return self::CS_MONTHS[$date->month].' '.$date->year;
        }

        return $date->format('F Y');
    }

    public static function iso(CarbonInterface $date): string
    {
        return $date->format('Y-m-d');
    }
}
