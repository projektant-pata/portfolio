# Zkušenosti a časová osa (`Experience`)

> Model: `App\Models\Experience` | Tabulka: `experiences` | Seeder: `Database\Seeders\ExperienceSeeder`

Entita **Experience** reprezentuje milníky v kariéře a osobním rozvoji. Na webu je dělena do dvou hlavních kategorií přepínaných uživatelem: **Práce (`work`)** a **Život / Vzdělání (`life`)**.

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `type` | `string` | Ne | **Ano** | Typ zkušenosti: buď `work` (práce/zakázky) nebo `life` (škola/soutěže/certifikáty). | `"work"` |
| `is_special` | `boolean` | Ne | Ne (výchozí `false`) | Příznak pro vizuální zvýraznění na časové ose. | `false` |
| `sort_order` | `integer` | Ne | **Ano** | Pořadí řazení na časové ose (nižší číslo = dříve/výše na ose). | `1` |
| `year` | `array` (JSON) | **cs, en** | **Ano** | Časové období nebo rok. Může se lišit pro cs/en (např. "současnost" vs. "present"). | `{"cs": "2023 – současnost", "en": "2023 – present"}` |
| `title` | `array` (JSON) | **cs, en** | **Ano** | Hlavní název (role, pracovní pozice, název hackathonu nebo certifikátu). | `{"cs": "Full-stack vývojář", "en": "Full-stack Developer"}` |
| `subtitle` | `array` (JSON) | **cs, en** | **Ano** | Podtitul (název firmy, školy, klienta či organizátora). | `{"cs": "PěknéWeby", "en": "PekneWeby"}` |
| `content` | `array` (JSON) | **cs, en** | Volitelné | Podrobný popis činnosti. Podporuje **Markdown** (např. `**tučné**`, odstavce oddělené `\n\n`). | `{"cs": "Tvorba webových aplikací...", "en": "Building web apps..."}` |
| `image_path` | `string` | Ne | Volitelné | Cesta k logu firmy nebo ilustračnímu obrázku v `public/` (nebo z `storage/`). | `"images/experience/logo.png"` |
| `links` | `array` (JSON) | Ne | Volitelné | Pole odkazů souvisejících se zkušeností (např. web firmy, článek, certifikát). | `[{"url": "https://example.com", "alt": "Web firmy"}]` |
| `badges` | `array` (slugy) | Ne | Volitelné | Seznam **slugů** technologií a dovedností (propojení na model `Badge`). | `["laravel", "php", "vue"]` |

---

## 📝 Šablona pro novou položku (Kostra pro zkopírování)

```yaml
type: "work" # nebo "life"
is_special: false
sort_order: 10
year:
  cs: "2024 – současnost"
  en: "2024 – present"
title:
  cs: "Název vaší pozice nebo úspěchu"
  en: "Your role or achievement title"
subtitle:
  cs: "Název společnosti, školy nebo organizace"
  en: "Company, school or organization name"
content:
  cs: |
    Popis vaší pracovní náplně nebo projektu.
    
    - Můžete používat **Markdown formátování**
    - Nebo odrážky pro hlavní odpovědnosti
  en: |
    Description of your responsibilities or project.
    
    - You can use **Markdown formatting**
    - Or bullet points for main responsibilities
image_path: null # např. "images/experience/company-logo.png"
links:
  - url: "https://www.example.com"
    alt: "Navštívit web"
badges:
  - "laravel"
  - "php"
  - "javascript"
```

---

## 🌟 Reálná ukázka z portfolia

```yaml
type: "work"
is_special: false
sort_order: 1
year:
  cs: "2023 – současnost"
  en: "2023 – present"
title:
  cs: "Týmová vedoucí pozice"
  en: "Team Leading figure"
subtitle:
  cs: "Prezz"
  en: "Prezz"
content:
  cs: |
    Vůdčí postava, plnohodnotný vývojář, tvůrce UI/UX pro **Prezz** — skupinu začínajících softwarových vývojářů ze střední školy.
  en: |
    Leading figure, Full-stack developer, UI/UX maker for **Prezz** — a group of starting software developers from highschool.
links:
  - url: "https://prezz.cz"
    alt: "Prezz"
badges:
  - "laravel"
  - "vue"
  - "tailwind"
  - "php"
  - "javascript"
```

---

## 💡 Tipy pro plnění obsahu

1. **Využijte Markdown v poli `content`**: Pokud má zkušenost více podsekcí (např. Hackathon s více úkoly), použijte tučné odstavce `**Název sekce** — Popis` pro krásnou čitelnost.
2. **Přesnost slugů**: Všechny štítky v poli `badges` se musí shodovat s existujícími hodnotami `slug` v tabulce `badges` (např. `python`, `laravel`, `competition`).
3. **Pravidlo typu**:
   - Do `work` zařazujte placenou praxi, stáže, freelance zakázky a vedení projektů.
   - Do `life` zařazujte střední či vysokou školu, certifikace, hackathony a soutěže.


work
hackathon AstroPi
2024
1st place in hackathon
null 
null
null
null
Python, Competetion, IT

hackathon Funnovation 2026
2026
1st place in Machine Award Cat.
null
null
null
null
JavaScript, Competetion, IT

hackathon Opendata KHK
3rd place in hackathon
2025
null
null
null
null
JavaScript, Competetion, IT

hackathon NKU
Taking part in postupovy? hackathon
2026
null
null
null
null
JavaScript, Competetion, IT

Tour-de-App26
1st place in central Europe competetion
2025 - 2026
null
null
null
null
Java, JavaScript, Competetion, IT

SCG volunteering
developing of an internal system
null
null
null
null
JavaScript, IT

PekneWeby internship
developing of an internal system
null
null
null
null
PHP, IT


life
RSS chess tournament
1st place in school representing chess tournament
null
null
null
null
Competetion
