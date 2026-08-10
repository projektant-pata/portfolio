# Reference a doporučení (`Review`)

> Model: `App\Models\Review` | Tabulka: `reviews` | Seeder: `Database\Seeders\ReviewSeeder`

Entita **Review** zobrazuje doporučení, reference a zpětnou vazbu od klientů, kolegů v týmu nebo mentorů. Zobrazují se formou interaktivního karuselu nebo karet na úvodní stránce portfolia.

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `name` | `string` | Ne | **Ano** | Jméno osoby (nebo subjektu), která referenci poskytla. | `"Petr Machovec"` |
| `position` | `array` (JSON) | **cs, en** | **Ano** | Pracovní pozice, role v projektu nebo vztah k autorovi (např. klient, kolega). | `{"cs": "Spoluzakladatel Prezz", "en": "Co-founder of Prezz"}` |
| `text` | `array` (JSON) | **cs, en** | **Ano** | Samotný text reference nebo citace. Může začínat a končit uvozovkami. | `{"cs": "\"Richard vždy dodává čistý kód...\"", "en": "\"Richard always delivers clean code...\""}` |
| `sort_order` | `integer` | Ne | Volitelné | Pořadí zobrazení v karuselu (nižší číslo = zobrazeno jako první). | `1` |

---

## 📝 Šablona pro novou referenci (Kostra pro zkopírování)

```yaml
name: "Jméno Příjmení"
position:
  cs: "Pozice a název firmy / organizace"
  en: "Role and company / organization name"
text:
  cs: '"Zde uveďte české znění doporučení od klienta či kolegy."'
  en: '"Here comes the English translation of the endorsement from a client or colleague."'
sort_order: 10
```

---

## 🌟 Reálná ukázka z portfolia

```yaml
name: "Petr Machovec"
position:
  cs: "Spoluzakladatel Prezz"
  en: "Co-founder of Prezz"
text:
  cs: '"Richard vždy dodává čistý, efektivní kód a má skvělý smysl pro uživatelsky přívětivý design. Spolehlivý a talentovaný týmový hráč!"'
  en: '"Richard always delivers clean, efficient code and has a great sense for user-friendly design. A reliable and talented team player!"'
sort_order: 1
```

---

## 💡 Tipy pro plnění obsahu

1. **Uvozovky**: Texty referencí často vypadají nejlépe, pokud je rovnou začnete i ukončíte typografickými nebo přímými uvozovkami (`"..."`).
2. **Krásný kontrast**: Pokud chcete do referencí zařadit i odlehčenou nebo hravou položku (jako např. reference od AI – *ChatGPT*), udržujte vyvážený poměr seriózních referencí od reálných kolegů/klientů k humorným.
3. **Stručnost a údernost**: Ideální délka jedné reference je 2–3 věty, aby karta zůstala vizuálně vzdušná.
