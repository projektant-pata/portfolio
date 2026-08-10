# Statistiky a počítadla (`Stat`)

> Model: `App\Models\Stat` | Tabulka: `stats` | Seeder: `Database\Seeders\StatSeeder`

Entita **Stat** reprezentuje statistické karty, počítadla a zajímavá čísla o autorově kariéře, věku, zájmech a projektech. Podporuje tři režimy fungování:
1. **Statická hodnota** — zadána ručně v poli `value` (např. `"5+"` projektů nebo `"∞"` kávy).
2. **Serverově počítaná hodnota (`source`)** — automaticky vypočítaná na backendu z konstant (např. věk `'age'` z data narození nebo léta praxe `'years_experience'`).
3. **Frontendově dynamická hodnota (`value_id`)** — element s unikátním ID pro JavaScript, který jej může po načtení aktualizovat z externí API (např. živé šachové elo `'elo'` nebo repozitáře na GitHubu `'github-repos'`).

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `value` | `array` (JSON) / `null` | **cs, en** | Volitelné | Zobrazená číselná či textová hodnota. Pokud je vyplněn `source`, má být `null`. | `{"cs": "5+", "en": "5+"}` |
| `text` | `array` (JSON) | **cs, en** | **Ano** | Popisek statistiky zobrazovaný pod hodnotou. | `{"cs": "Projektů dokončeno", "en": "Projects Completed"}` |
| `source` | `string` / `null` | Ne | Volitelné | Identifikátor pro automatický výpočet na backendu: buď `'age'`, `'years_experience'`, nebo `null`. | `"age"` |
| `value_id` | `string` / `null` | Ne | Volitelné | Unikátní DOM atribut `id` pro frontendové skripty a AJAX aktualizace. | `"elo"` |
| `sort_order` | `integer` | Ne | Volitelné | Pořadí vypsání karty v sekci statistik. | `1` |

---

## 📝 Šablona pro novou statistiku (Kostra pro zkopírování)

### 1. Statická hodnota (běžná statistika)
```yaml
value:
  cs: "10+"
  en: "10+"
text:
  cs: "Spokojených klientů"
  en: "Happy Clients"
source: null
value_id: null
sort_order: 10
```

### 2. Serverově dynamická hodnota (věk nebo roky praxe)
```yaml
value: null # Počítá se automaticky modelovou metodou displayValue()
text:
  cs: "Let věku"
  en: "Years old"
source: "age" # nebo "years_experience"
value_id: null
sort_order: 11
```

### 3. Frontendově / API řízená hodnota (s výchozím textem)
```yaml
value:
  cs: "Načítání.."
  en: "Loading.."
text:
  cs: "Nejvyšší šachové elo"
  en: "Highest chess elo"
source: null
value_id: "elo" # JS skript na webu zaměří toto ID a načte živou hodnotu z API
sort_order: 12
```

---

## 🌟 Reálné ukázky z portfolia

```yaml
# Ukázka 1: Roky zkušeností (automaticky počítané)
value: null
source: "years_experience"
text:
  cs: "Roky zkušeností"
  en: "Years of experience"
sort_order: 2

---
# Ukázka 2: GitHub repozitáře (cíl pro JS)
value:
  cs: "18"
  en: "18"
value_id: "github-repos"
text:
  cs: "GitHub repozitářů"
  en: "GitHub repositories"
sort_order: 9

---
# Ukázka 3: Vypito kávy (čistě statický vtip/zájem)
value:
  cs: "∞"
  en: "∞"
text:
  cs: "Vypitých šálků kávy"
  en: "Coffee consumed"
sort_order: 7
```

---

## 💡 Tipy pro plnění obsahu

1. **Rovnováha čísel a humoru**: Kombinujte profesionální metriky (dokončené projekty, počet repozitářů, léta praxe) s hravými položkami (vypitá káva, šachová figura `♞`, počet vyhraných hackathonů).
2. **Kdy použít `source` vs. `value_id`**:
   - `source` použijte pro veličiny odvozené z data, kde není potřeba dotazovat externí službu (věk, délka kariéry).
   - `value_id` použijte pro data z externích platforem (Chess.com, GitHub API, Wakatime), která se stahují na straně klienta.
3. **Žádná zbytečná duplikace**: Pokud nastavíte `source: "age"`, neduplikujte číslo v poli `value`, protože backend jej dynamicky přepisuje metodou `displayValue($locale)`.
