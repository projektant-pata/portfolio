# Kostra pro budoucí obsah (Content Skeleton)

Tato složka (`docs/content-skeleton/`) slouží jako **centrální mapa, specifikace a šablona pro veškeré vložitelné obsahové prvky** (entity) v portfoliu. Každý typ obsahu má svůj vlastní samostatný Markdown soubor, který detailně popisuje jeho strukturu, datový model, požadované atributy, šablonu pro rychlé vyplnění a reálnou ukázku.

---

## 🗺️ Mapa obsahových entit (Přehled souborů)

| Soubor | Entita / Databázový model | Kde se na webu zobrazuje | Popis |
| :--- | :--- | :--- | :--- |
| [`01-experiences.md`](./01-experiences.md) | `Experience` | Sekce **Zkušenosti / Cesta** (`work` & `life`) | Pracovní zkušenosti, stáže, vzdělání, certifikáty a soutěže. |
| [`02-projects.md`](./02-projects.md) | `Project` & `Link` | Sekce **Projekty** | Portfolio vytvořených webů, aplikací a open-source projektů včetně odkazů a štítků. |
| [`03-articles.md`](./03-articles.md) | `Article` | Sekce **Blog / Články** | Technické články, návody, zápisky a zamyšlení. |
| [`04-reviews.md`](./04-reviews.md) | `Review` | Sekce **Reference / Doporučení** | Citace a doporučení od klientů, kolegů a mentorů. |
| [`05-about-cards.md`](./05-about-cards.md) | `AboutCard` | Sekce **O mně** | Informační karty s osobním příběhem, zájmy a motivací. |
| [`06-badges.md`](./06-badges.md) | `Badge` | Celý web (tagy u zkušeností, projektů a článků) | Štítky technologií, nástrojů a kategorií s vlastními barvami. |
| [`07-stats.md`](./07-stats.md) | `Stat` | Sekce **Statistiky** | Počítadla a statistické hodnoty (včetně dynamických jako věk nebo léta praxe). |
| [`08-settings.md`](./08-settings.md) | `Setting` | Globální web (hlavička, patička, sociální sítě) | Nastavení, kontaktní údaje a globální texty webu. |

---

## 🌍 Dvojjazyčnost (Multi-language / i18n podpora)

Většina textových polí v portfoliu podporuje **češtinu (`cs`)** i **angličtinu (`en`)** a je v databázi uložena jako pole/JSON:

```json
{
  "cs": "Český text pro návštěvníky",
  "en": "English text for visitors"
}
```

Při přidávání nového obsahu se **vždy doporučuje vyplnit obě jazykové mutace**, aby portfolio fungovalo bezchybně v českém i anglickém rozhraní.

---

## 🛠️ Jak s touto strukturou pracovat při tvorbě obsahu?

1. **Vyberte typ obsahu**, který chcete přidat (např. novou zkušenost -> `01-experiences.md`).
2. **Podívejte se na tabulku atributů**, kde zjistíte, co je povinné a jaké datové typy se očekávají.
3. **Zkopírujte šablonu (Kostru pro novou položku)** z daného souboru.
4. **Vyplňte vlastní data** v obou jazykových verzích (`cs` / `en`).
5. Vyplněná data můžete buď:
   - Zadat přímo přes **administrační rozhraní / dashboard** na webu.
   - Vložit do příslušného **databázového seederu** ve složce `database/seeders/` pro trvalou inicializaci dat.
   - Předat jako podklad pro AI asistenta pro vygenerování migrace, seederu nebo obsahu.

---

## 💡 Základní pravidla pro propojování obsahu

- **Štítky (`badges`)** se k projektům, zkušenostem a článkům připojují pomocí **slugu** (např. `laravel`, `php`, `vue`, `tailwind`). Před použitím nového štítku se ujistěte, že je definován v entitě `Badge` ([`06-badges.md`](./06-badges.md)).
- **Odkazy (`links`)** u projektů a zkušeností se zadávají jako pole objektů s URL a alternativním textem:
  ```json
  [
    { "url": "https://github.com/...", "alt": { "cs": "GitHub", "en": "GitHub" } }
  ]
  ```
- **Pořadí zobrazení (`sort_order`)** určuje vizuální prioritu (nižší číslo = vyšší priorita / zobrazení dříve).
