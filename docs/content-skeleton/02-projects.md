# Projekty a reference na webu (`Project` & `Link`)

> Model: `App\Models\Project`, `App\Models\Link` | Tabulky: `projects`, `links` | Seeder: `Database\Seeders\ProjectsSeeder`

Entita **Project** představuje klíčové reference na weby, webové aplikace nebo open-source projekty v sekci *Projekty*. Ke každému projektu se mohou vázat **odkazy (`links`)** — například odkaz na živý web či repozitář na GitHubu s vlastní ikonou.

---

## 📋 Tabulka vlastností (Schema)

### 1. Model `Project`

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `slug` | `string` | Ne | **Ano** | Unikátní URL identifikátor projektu v malých písmenech s pomlčkami. | `"u-sladovny"` |
| `year` | `integer` | Ne | **Ano** | Rok vytvoření nebo vydání projektu. | `2025` |
| `header` | `array` (JSON) | **cs, en** | **Ano** | Hlavní název projektu. | `{"cs": "U Sladovny", "en": "U Sladovny"}` |
| `description` | `array` (JSON) | **cs, en** | **Ano** | Detailní popisek projektu (proč vznikl, jaké technologie byly použity). | `{"cs": "Projekt, na kterém jsem pracoval...", "en": "A project I was part of..."}` |
| `img_url` | `string` | Ne | **Ano** | Relativní cesta ke screenshotu/náhledu v `public/`. | `"images/projects/u_sladovny.png"` |
| `sort_order` | `integer` | Ne | Volitelné | Pořadí zobrazení (výchozí podle ID/pořadí zadání). | `1` |
| `badges` | `array` (slugy) | Ne | Volitelné | Seznam **slugů** technologií (propojení na tabulku `badges`). | `["laravel", "tailwind", "php"]` |
| `links` | `array` (objekty) | **cs, en** | Volitelné | Seznam připojených tlačítek s odkazy na živý web nebo repozitář. | Viz sekce pod-entita `Link` |

### 2. Pod-entita `Link` (Tlačítko u projektu)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `url` | `string` (URL) | Ne | **Ano** | Cílová adresa webu nebo repozitáře. | `"https://github.com/..."` |
| `alt` | `array` (JSON) | **cs, en** | **Ano** | Popisek zobrazený na tlačítku nebo jako alt text. | `{"cs": "Navštívit web", "en": "Visit website"}` |
| `img_url` | `string` | Ne | Volitelné | Cesta k ikonce odkazu (např. ikona webu nebo GitHubu). | `"images/projects/icons/web.webp"` |

---

## 📝 Šablona pro nový projekt (Kostra pro zkopírování)

```yaml
slug: "nazev-projektu"
year: 2026
header:
  cs: "Název vašeho projektu"
  en: "Your Project Name"
description:
  cs: "Podrobný popis projektu, jeho cílů a vaší role při vývoji."
  en: "Detailed description of the project, its goals and your role in development."
img_url: "images/projects/nazev_projektu.png"
sort_order: 1
badges:
  - "laravel"
  - "vue"
  - "tailwind"
links:
  - url: "https://www.example.com"
    alt:
      cs: "Navštívit web"
      en: "Visit website"
    img_url: "images/projects/icons/web.webp"
  - url: "https://github.com/vaše-jmeno/repozitar"
    alt:
      cs: "Zobrazit na GitHubu"
      en: "View on GitHub"
    img_url: "images/mobile/icons/github.webp"
```

---

## 🌟 Reálná ukázka z portfolia

```yaml
slug: "spse-hub"
year: 2022
header:
  cs: "SPŠE Rozcestník"
  en: "SPŠE Hub"
description:
  cs: "Tento projekt je rozcestník všech webových stránek, které jsem vytvořil při studiu na střední škole při plnění úkolů od učitele Reného 'Dusíka' Duse. Jsou to jedny z mých prvních stránek (první jsme vytvářeli už na základní škole)."
  en: "The SPSE Hub is a project created under the guidance of Mr. Nitrogen to teach how to make a website. It marks my beginnings and has a nostalgic effect on me – so I'm including it. Built using HTML5, CSS3, and JavaScript."
img_url: "images/projects/spse_wp.png"
badges:
  - "javascript"
links:
  - url: "https://hyvlri22.llmp.spse-net.cz/"
    alt:
      cs: "Navštívit web"
      en: "Visit website"
    img_url: "images/projects/icons/web.webp"
  - url: "https://github.com/projektant-pata/SPSE-WP"
    alt:
      cs: "Zobrazit na GitHubu"
      en: "View on GitHub"
    img_url: "images/mobile/icons/github.webp"
```

---

## 💡 Tipy pro plnění obsahu

1. **Ikony odkazů (`img_url` u odkazů)**:
   - Pro obecný odkaz na web použijte ikonu: `"images/projects/icons/web.webp"`
   - Pro odkaz na GitHub použijte ikonu: `"images/mobile/icons/github.webp"`
2. **Konzistentní miniatury**: Pro screenshoty v `img_url` doporučujeme formát `.png` nebo `.webp` a poměr stran 16:9, uloženy ve složce `public/images/projects/`.
3. **Přiřazování štítků**: Ke každému projektu uveďte hlavní technologie, které tvoří jeho stack (např. `laravel`, `php`, `vue`, `tailwind`, `mysql`).


HradecProjekt 
2025
bacges same as opendata khk

Lifely
2026
badges same as uhk hackathon 

Think Diffrent Academy
2025 - 2026
badges sane as scg

Home server
2026
hardware