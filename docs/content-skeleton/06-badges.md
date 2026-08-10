# Štítky technologií a kategorií (`Badge`)

> Model: `App\Models\Badge` | Tabulka: `badges` | Seeder: `Database\Seeders\BadgesSeeder`

Entita **Badge** slouží k systematickému označování a kategorizaci obsahu na celém webu. Štítky lze přiřazovat ke **zkušenostem (`Experience`)**, **projektům (`Project`)** a **článkům (`Article`)**. Každý štítek je identifikován unikátním slugem a má svou vlastní barvu (HEX kód), která určuje barvu jeho ohraničení nebo pozadí na webu.

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `slug` | `string` | Ne | **Ano** | Unikátní identifikátor v malých písmenech bez mezer (slouží pro propojování v ostatních entitách). | `"laravel"` |
| `name` | `array` (JSON) | **cs, en** | **Ano** | Zobrazený název štítku. U technologií bývá stejný v cs/en, u kategorií se překládá. | `{"cs": "Soutěž", "en": "Competition"}` |
| `color` | `string` (HEX) | Ne | Volitelné | Barevný HEX kód (včetně mřížky `#`), ideálně sladěný se značkou technologie. | `"#FF2D20"` |

---

## 📝 Šablona pro nový štítek (Kostra pro zkopírování)

```yaml
slug: "nazev-technologie"
name:
  cs: "Zobrazený název"
  en: "Display Name"
color: "#3B82F6" # HEX kód barvy
```

### Alternativní zkrácený zápis pro Seeder (řádkové pole)
```php
['slug', 'Název English', 'Název Česky', '#HEXCOLOR']
```

---

## 🌟 Reálná ukázka z portfolia

### 1. Technologický štítek (`Laravel`)
```yaml
slug: "laravel"
name:
  cs: "Laravel"
  en: "Laravel"
color: "#FF2D20" # Charakteristická červená Laravelu
```

### 2. Kategorický štítek (`Soutěž / Competition`)
```yaml
slug: "competition"
name:
  cs: "Soutěž"
  en: "Competition"
color: "#EAB308" # Zlatá / žlutá barva
```

---

## 💡 Tipy pro plnění obsahu

1. **Barevná harmonie**: Při přidávání nové technologie vyhledejte její oficiální brandovou barvu (např. `#FF2D20` pro Laravel, `#41B883` pro Vue, `#38BDF8` pro Tailwind CSS).
2. **Konzistentní slugy**: Slugech vždy používejte malá písmena s pomlčkami místo mezer (např. `spring-boot`, `tailwind-css`, `react-native`).
3. **Mnoho-k-mnoha (Many-to-Many)**: Jeden štítek můžete přiřadit k libovolnému počtu projektů nebo zkušeností najednou — změna názvu nebo barvy se pak automaticky promítne na celém webu.
