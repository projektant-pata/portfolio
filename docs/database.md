# Database Schema

## Overview

All primary keys are UUIDs unless noted. Authentication is admin-only (single user). Badges are shared across articles, achievements, projects, and experiences via many-to-many pivot tables.

```
users
  └── articles (user_id FK)
        └── article_badge (pivot) ─── badges
achievements
  └── achievement_badge (pivot) ──── badges
projects
  ├── project_badge (pivot) ──────── badges
  └── links
experiences (integer PK)
  └── experience_badge (pivot) ───── badges
```

---

## Tables

### `users`

| Column | Type | Notes |
|---|---|---|
| id | uuid | primary key |
| name | string | |
| email | string | unique |
| password | string | hashed |
| remember_token | string | nullable |
| profile_picture_url | string | nullable |
| created_at / updated_at | timestamps | |

Login is admin-only. Registration should be disabled in `config/fortify.php` by removing `Features::registration()`.

> Model note: `HasUuids` trait is used on the User model.

---

### `badges`

Shared tag/label used across articles, achievements, and projects for filtering.

| Column | Type | Notes |
|---|---|---|
| id | uuid | primary key |
| slug | string | unique |
| name | string | display label |
| color | string | nullable — hex or CSS class |
| created_at / updated_at | timestamps | |

---

### `articles`

Blog-style posts with markdown content.

| Column | Type | Notes |
|---|---|---|
| id | uuid | primary key |
| slug | string | unique — used in URLs |
| header | string | article title |
| description | string | short excerpt |
| content | longText | markdown body |
| date | date | visible publish date |
| thumbnail_url | string | nullable |
| user_id | uuid | FK → users.id (cascade delete) |
| created_at / updated_at | timestamps | |

**Relationships:** belongs to `users`, belongs to many `badges` via `article_badge`.

---

### `article_badge` *(pivot)*

| Column | Type | Notes |
|---|---|---|
| article_id | uuid | FK → articles.id (cascade delete) |
| badge_id | uuid | FK → badges.id (cascade delete) |
| — | composite PK | (article_id, badge_id) |

---

### `achievements`

Portfolio achievements with markdown content.

| Column | Type | Notes |
|---|---|---|
| id | uuid | primary key |
| slug | string | unique |
| header | string | achievement title |
| description | string | short summary |
| content | longText | markdown body |
| date | date | when the achievement occurred |
| pic_url | string | nullable |
| created_at / updated_at | timestamps | |

**Relationships:** belongs to many `badges` via `achievement_badge`.

---

### `achievement_badge` *(pivot)*

| Column | Type | Notes |
|---|---|---|
| achievement_id | uuid | FK → achievements.id (cascade delete) |
| badge_id | uuid | FK → badges.id (cascade delete) |
| — | composite PK | (achievement_id, badge_id) |

---

### `projects`

Portfolio projects.

| Column | Type | Notes |
|---|---|---|
| id | uuid | primary key |
| year | unsignedSmallInteger | e.g. 2024 |
| slug | string | unique |
| header | string | project title |
| description | text | project description |
| img_url | string | nullable |
| created_at / updated_at | timestamps | |

**Relationships:** has many `links`, belongs to many `badges` via `project_badge`.

---

### `project_badge` *(pivot)*

| Column | Type | Notes |
|---|---|---|
| project_id | uuid | FK → projects.id (cascade delete) |
| badge_id | uuid | FK → badges.id (cascade delete) |
| — | composite PK | (project_id, badge_id) |

---

### `links`

External links attached to a project (e.g. GitHub, live demo).

| Column | Type | Notes |
|---|---|---|
| id | uuid | primary key |
| project_id | uuid | FK → projects.id (cascade delete) |
| alt | string | label / tooltip text |
| img_url | string | nullable — icon for the link |
| url | string | target URL |
| created_at / updated_at | timestamps | |

**Relationships:** belongs to `projects`.

---

### `experiences`

Work history and education entries displayed on the portfolio.

| Column | Type | Notes |
|---|---|---|
| id | bigInteger | auto-increment primary key |
| type | enum | `work` or `life` |
| is_special | boolean | default false — highlights the entry |
| title | json | locale-keyed object — `{"en": "...", "cs": "..."}` |
| subtitle | json | nullable — locale-keyed |
| content | json | nullable — locale-keyed markdown body |
| year | string | nullable — e.g. `2022 – present` |
| image_path | string | nullable — stored in `storage/experiences/` |
| links | json | nullable — array of `{url}` objects |
| sort_order | unsignedSmallInteger | display order, default 0 |
| created_at / updated_at | timestamps | |

**Relationships:** belongs to many `badges` via `experience_badge`.

---

### `experience_badge` *(pivot)*

| Column | Type | Notes |
|---|---|---|
| experience_id | bigInteger | FK → experiences.id (cascade delete) |
| badge_id | uuid | FK → badges.id (cascade delete) |
| — | composite PK | (experience_id, badge_id) |
