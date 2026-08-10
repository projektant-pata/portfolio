# Globální texty a nastavení webu (`Setting`)

> Model: `App\Models\Setting` | Tabulka: `settings` | Seeder: `Database\Seeders\SettingSeeder`

Entita **Setting** představuje úložiště pro globální texty, nadpisy sekcí, rotující texty v hrdinské sekci (hero banneru) a další konfigurace webu. Využívá páry `key` -> `value` s podporou pro lokalizované řetězce nebo pole. Všechna nastavení jsou navíc v aplikaci **trvale kešována** pro maximální rychlost načítání.

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `key` | `string` | Ne | **Ano** | Unikátní klíč nastavení ve formátu `snake_case`. | `"hero_suptitle"` |
| `value` | `array` (JSON) | **cs, en** | **Ano** | Lokalizovaná hodnota (může jméno/řetězec nebo pole řetězců jako např. pro role). | `{"cs": "👋 Ahoj světe!", "en": "👋 Hello world!"}` |

---

## 🔑 Seznam existujících klíčů v portfoliu

| Klíč (`key`) | Popis a použití na webu | Očekávaná struktura v `value` |
| :--- | :--- | :--- |
| `hero_suptitle` | Malý úvodní text nad hlavním nadpisem (např. pozdrav). | Řetězec pro `cs` a `en` |
| `hero_title` | Hlavní nadpis webu. Podporuje HTML tag `<span>` pro barevné zvýraznění jména/přezdívky. | Řetězec pro `cs` a `en` |
| `hero_roles` | Seznam rotujících rolí pod nadpisem na úvodní obrazovce. | Pole řetězců pro `cs` a `en` |
| `stats_title` | Nadpis nad sekcí statistik. | Řetězec pro `cs` a `en` |
| `tools_title` | Nadpis nad sekcí technologií a nástrojů. | Řetězec pro `cs` a `en` |
| `reviews_title` | Nadpis nad sekcí referencí a doporučení. | Řetězec pro `cs` a `en` |
| `about_title` | Nadpis nad sekcí O mně. | Řetězec pro `cs` a `en` |

---

## 📝 Šablona pro nové nastavení (Kostra pro zkopírování)

### 1. Běžný textový klíč
```yaml
key: "nazev_sekce_title"
value:
  cs: "Nadpis sekce v češtině"
  en: "Section Title in English"
```

### 2. Klíč se seznamem / polem (např. rotující podnadpisy)
```yaml
key: "hero_roles"
value:
  cs:
    - "Full-stack vývojář"
    - "Šachista"
    - "Laravel řemeslník"
  en:
    - "Full-stack developer"
    - "Chess player"
    - "Laravel craftsman"
```

---

## 🌟 Reálná ukázka z portfolia

```yaml
key: "hero_title"
value:
  cs: "Jsem <span>projektant-pata</span>,"
  en: "I’m <span>projektant-pata</span>,"
```

---

## 💡 Tipy pro plnění obsahu

1. **Kešování**: Model `Setting` automaticky promazává mezipaměť (Cache) po každém uložení, takže jakékoliv nové nastavení nebo úprava se okamžitě projeví v aplikaci.
2. **HTML v textech**: Stejně jako v sekci *O mně*, tak i v `hero_title` můžete použít tag `<span>`, který vyvolá stylizované akcentní zobrazení textu uvnitř tagu.
