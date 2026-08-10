# Články a blogové zápisky (`Article`)

> Model: `App\Models\Article` | Tabulka: `articles`

Entita **Article** slouží k prezentaci článků, technických zápisků, návodů nebo novinek v sekci *Blog / Články*. Každý článek může být obohacen o náhledový obrázek, úvodní perex a seznam tematických štítků (`badges`).

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `slug` | `string` | Ne | **Ano** | Unikátní URL identifikátor článku (přátelská URL v malých písmenech s pomlčkami). | `"jak-jsem-vyhral-hackathon"` |
| `date` | `date` (`YYYY-MM-DD`) | Ne | **Ano** | Datum vydání nebo sepsání článku. | `"2025-05-14"` |
| `header` | `array` (JSON) | **cs, en** | **Ano** | Hlavní nadpis článku. | `{"cs": "Můj první článek", "en": "My First Article"}` |
| `description` | `array` (JSON) | **cs, en** | **Ano** | Perex / krátký souhrn článku zobrazený v seznamu článků na hlavní stránce. | `{"cs": "Krátké shrnutí pro čtenáře...", "en": "Short summary for readers..."}` |
| `content` | `array` (JSON) | **cs, en** | **Ano** | Kompletní text článku (podporuje Markdown, nadpisy, kódové bloky a odstavce). | `{"cs": "# Úvod\n\nText článku...", "en": "# Intro\n\nArticle body..."}` |
| `thumbnail_url` | `string` | Ne | Volitelné | Cesta k náhledovému obrázku v `public/` (nebo v `storage/`). | `"images/articles/my-first-article.webp"` |
| `sort_order` | `integer` | Ne | Volitelné | Pořadí vypsání (pokud není řazeno automaticky podle data). | `1` |
| `badges` | `array` (slugy) | Ne | Volitelné | Seznam **slugů** kategorií a technologií (propojení na `Badge`). | `["laravel", "php", "architecture"]` |

---

## 📝 Šablona pro nový článek (Kostra pro zkopírování)

```yaml
slug: "nazev-noveho-clanku"
date: "2026-08-01"
header:
  cs: "Nadpis vašeho nového článku"
  en: "Title of your new article"
description:
  cs: "Krátký perex pro zobrazení v seznamu článků a na úvodní stránce."
  en: "Short excerpt for listing and front page display."
content:
  cs: |
    # Hlavní téma článku
    
    Zde začíná úvodní odstavec článku. Můžete využít plné formátování v **Markdownu**.
    
    ## Podnadpis sekce
    
    1. První bod
    2. Druhý bod
    
    ```php
    // Ukázka kódu
    echo "Hello, world!";
    ```
  en: |
    # Main Article Topic
    
    Here begins the intro paragraph. You can use full **Markdown formatting**.
    
    ## Section Subheading
    
    1. First point
    2. Second point
    
    ```php
    // Code snippet
    echo "Hello, world!";
    ```
thumbnail_url: "images/articles/nazev-noveho-clanku.webp"
sort_order: 1
badges:
  - "laravel"
  - "php"
```

---

## 🌟 Reálná ukázka z portfolia

```yaml
slug: "proc-pouzivam-laravel"
date: "2025-04-10"
header:
  cs: "Proč je Laravel mou volbou #1 pro backend"
  en: "Why Laravel is my #1 choice for backend"
description:
  cs: "Zamyšlení nad elegantní syntaxí, ekosystémem a architekturou frameworku Laravel ve srovnání s ostatními nástroji."
  en: "Thoughts on elegant syntax, ecosystem, and architecture of the Laravel framework compared to other tools."
content:
  cs: |
    Když jsem poprvé začal pracovat s PHP, zkoušel jsem čistý kód i různé menší knihovny. Až **Laravel** mi ale ukázal, co to znamená skutečná vývojářská ergonomie.
    
    ### Hlavní výhody:
    - Expressivní syntaxe Eloquentu
    - Robustní migrace a seedery
    - Skvělý ekosystém (Livewire, Pint, Sail)
  en: |
    When I first started working with PHP, I tried plain code and various small libraries. But **Laravel** showed me what true developer ergonomics look like.
    
    ### Main benefits:
    - Expressive Eloquent syntax
    - Robust migrations and seeders
    - Great ecosystem (Livewire, Pint, Sail)
thumbnail_url: "images/articles/laravel-choice.webp"
badges:
  - "laravel"
  - "php"
```

---

## 💡 Tipy pro plnění obsahu

1. **SEO přátelský slug**: Slug by měl vždy odpovídat hlavní klíčové myšlence článku bez diakritiky a speciálních znaků (`a-z`, `0-9`, `-`).
2. **Kvalitní perex (`description`)**: Napište poutavý úvod, který nepřesáhne 2–3 věty. Slouží také pro meta tagy na sociálních sítích.
3. **Formátování v `content`**: Využívejte nadpisy (`##`, `###`), citace (`>`) a kódové bloky (` ```php `) pro čistou vizuální prezentaci dlouhých textů.
