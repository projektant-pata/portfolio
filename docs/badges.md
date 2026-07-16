# Badge Color Palette

Used for experience badge pills: `border-color` + `color` both set from `badge.color` against dark surface (`#0F172A`).

All values chosen for legibility on dark navy — approximately Tailwind 300–400 level saturation.

---

## Assigned Badges

| Slug          | Name (EN)   | Name (CS)   | Hex       | Color name | Rationale                    |
|---------------|-------------|-------------|-----------|------------|------------------------------|
| `competetion` | Competition | Soutěž      | `#EAB308` | Gold       | Achievement / winning        |
| `work`        | Work        | Práce       | `#60A5FA` | Blue       | Professional work            |
| `certificate` | Certificate | Certifikát  | `#34D399` | Emerald    | Validated credential         |
| `java`        | Java        | Java        | `#F97316` | Orange     | Java brand adjacent          |
| `php`         | PHP         | PHP         | `#A78BFA` | Violet     | PHP brand purple             |
| `education`   | Education   | Vzdělání    | `#38BDF8` | Sky        | Learning / academia          |
| `hardware`    | Hardware    | Hardware    | `#F59E0B` | Amber      | Engineering / electronics    |
| `python`      | Python      | Python      | `#2DD4BF` | Teal       | Python modern vibe           |
| `it`          | IT          | IT          | `#818CF8` | Indigo     | General tech                 |

---

## Available Palette (for future badges)

All readable as border + text on `#0F172A`.

| Name    | Hex       | Suggested use                    |
|---------|-----------|----------------------------------|
| Crimson | `#C41E3A` | Primary accent — highlight badge |
| Rose    | `#F43F5E` | Design, UI/UX                    |
| Pink    | `#EC4899` | Creative work, branding          |
| Coral   | `#FB7185` | Communication, soft skills       |
| Scarlet | `#EF4444` | Urgent, critical                 |
| Lime    | `#84CC16` | Scripting, automation            |
| Green   | `#4ADE80` | Backend, infrastructure          |
| Cyan    | `#22D3EE` | Cloud, DevOps                    |
| Purple  | `#C084FC` | Product, strategy                |
| Fuchsia | `#E879F9` | Marketing, events                |
| Silver  | `#94A3B8` | Neutral — misc / unclassified    |

---

## Usage

```blade
{{-- In experience card --}}
<span class="exp-badge" style="--badge-color: {{ $badge->color }}">
    {{ $badge->name }}
</span>
```

```css
/* experience.css */
.exp-badge {
    color: var(--badge-color);
    border: 1px solid var(--badge-color);
    background: color-mix(in srgb, var(--badge-color) 10%, transparent);
}
```
