# Experience: Content Field & i18n Design

**Date:** 2026-04-02  
**Status:** Approved

---

## Summary

Add a `content` (long markdown text) field to experiences and make `title`, `subtitle`, and `content` multilingual (English + Czech) using JSON columns. The dashboard edit modal gets language tabs for managing translations.

---

## Database

### Changes to `experiences` table

| Column | Change |
|---|---|
| `title` | `string` → `json` — `{"en": "...", "cs": "..."}` |
| `subtitle` | `string nullable` → `json nullable` — `{"en": "...", "cs": "..."}` |
| `content` | New — `json nullable` — `{"en": "...", "cs": "..."}` |

### Migration strategy

No existing data worth preserving. The migration directly alters `title` and `subtitle` to `json`, and adds `content` as `json nullable`. No data copy step.

Supported locales: `en`, `cs`. Adding a future locale requires no schema change — just a new key in the JSON.

---

## Model (`App\Models\Experience`)

- `title`, `subtitle`, `content` cast to `'array'`
- `content` added to `$fillable`
- Helper method: `getTranslation(string $field, string $locale, string $fallback = 'en'): string`
  - Returns the value for `$locale`
  - Falls back to `$fallback` (English) if the locale key is missing or empty
  - Returns empty string if neither exists

---

## Dashboard (Livewire — `⚡experiences.blade.php`)

### State changes

| Before | After |
|---|---|
| `public string $title = ''` | `public array $title = ['en' => '', 'cs' => '']` |
| `public string $subtitle = ''` | `public array $subtitle = ['en' => '', 'cs' => '']` |
| *(none)* | `public array $content = ['en' => '', 'cs' => '']` |
| *(none)* | `public string $locale = 'en'` |

### Validation

- `title.en` — required, string, max 255
- `title.cs` — nullable, string, max 255
- `subtitle.en` — nullable, string, max 255
- `subtitle.cs` — nullable, string, max 255
- `content.en` — nullable, string
- `content.cs` — nullable, string

### UI

The form modal gains EN / CS tabs (Flux tab component). Each tab shows:
1. `title` — text input
2. `subtitle` — text input
3. `content` — textarea (markdown, no live preview in dashboard)

Tab switching changes `$locale` (client-side via Flux tabs, no server round-trip needed for switching).

The list table continues to show the English `title` and `subtitle` for scannability.

---

## Locales

Two locales for now: `en` (primary), `cs`. Czech fields can be left blank and filled in later — English is always the required fallback.

---

## Out of scope

- Live markdown preview in the dashboard
- Frontend locale switching (public-facing rendering)
- Any other models (articles, achievements, projects)
