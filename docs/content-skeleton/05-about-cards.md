# Karty v sekci „O mně“ (`AboutCard`)

> Model: `App\Models\AboutCard` | Tabulka: `about_cards` | Seeder: `Database\Seeders\AboutCardSeeder`

Entita **AboutCard** reprezentuje jednotlivé informační karty v sekci *O mně* na hlavní stránce. Každá karta rozvíjí určitou stránku autorovy osobnosti — od profesního úvodu přes koníčky až po životní filozofii.

---

## 📋 Tabulka vlastností (Schema)

| Atribut (`field`) | Datový typ | Jazyková podpora | Povinné | Popis a pravidla | Příklad hodnoty |
| :--- | :--- | :--- | :--- | :--- | :--- |
| `title` | `array` (JSON) | **cs, en** | **Ano** | Nadpis informační karty. | `{"cs": "Co mám rád?", "en": "What do I like?"}` |
| `text` | `array` (JSON) | **cs, en** | **Ano** | Text karty. Podporuje **HTML formátování** — např. tag `<span>` pro barevné zvýraznění slov nebo `<br><br>` pro odstavce. | `{"cs": "...Mám rád <span>šachy</span>.", "en": "...I like <span>chess</span>."}` |
| `sort_order` | `integer` | Ne | Volitelné | Pořadí zobrazení karty. | `1` |

---

## 📝 Šablona pro novou kartu (Kostra pro zkopírování)

```yaml
title:
  cs: "Nadpis karty (např. Moje filozofie)"
  en: "Card Title (e.g., My Philosophy)"
text:
  cs: |
    Hlavní text vaší karty. Pro vizuální zvýraznění klíčových slov a zájmů použijte HTML tag <span>takto</span>.
    <br><br>
    Další odstavec v rámci stejné karty.
  en: |
    Main text of your card. To visually highlight key words and hobbies, use the HTML tag <span>like this</span>.
    <br><br>
    Another paragraph inside the same card.
sort_order: 10
```

---

## 🌟 Reálná ukázka z portfolia

```yaml
title:
  cs: "Co mám rád?"
  en: "What do I like?"
text:
  cs: |
    Od mládí jsem byl vášnivým šachistou. Ve 2. třídě jsem vyhrával proti středoškolákům na místním šachovém turnaji. S velkou přestávkou jsem zpět, s obnovenou vášní pro hru. Šachy mě naučily <span>kritickému myšlení</span>, <span>strategii</span> a důležité <span>trpělivosti</span> — dovednostem, které považuji za neuvěřitelně cenné na své cestě softwarového vývojáře.<br><br>Mám také opravdu moc rád <span>sumečky</span> a <span>rockovou hudbu</span> :)
  en: |
    From a young age I was a passionate chess player. I was winning in 2nd grade against highschoolers at local chess tournaments. With a great break I'm back, with a renewed passion for the game. Chess has taught me <span>critical thinking</span>, <span>strategy</span>, and the important <span>patience</span> — skills that I've found incredibly valuable in my journey as a software developer.<br><br>I also really really love <span>catfishes</span> and <span>Rock music</span> :)
sort_order: 2
```

---

## 💡 Tipy pro plnění obsahu

1. **Zvýrazňující tag `<span>`**: CSS styl portfolia automaticky propůjčuje elementům `<span>` uvnitř karet *O mně* charakteristickou akcentní barvu nebo záři. Využijte je k vypíchnutí nejdůležitějších schopností, zájmů nebo hodnot.
2. **Délka karty**: Udržujte text v rozsahu 50–120 slov na kartu, aby se karty na mřížce (CSS Grid) zobrazovaly harmonicky a s vyváženou výškou.
3. **Osobní tón**: Nebojte se do karet zapojit autentické koníčky (např. rocková hudba, sumečci, šachy), protože právě ty dodávají portfoliu lidskost a zapamatovatelnost.
