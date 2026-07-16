# Supr cupr upgrade plan

Plán na velký audit + upgrade portfolia. Tři nezávislé Fable prompty (kód, design, dokumentace) + doporučené nástroje.

## 1. Bug/code audit (Fable)

```
Projdi celý Laravel projekt (backend PHP, Livewire komponenty, routes, migrace).
Najdi: bugy, N+1 queries, chybějící validace/autorizaci, nekonzistentní error handling,
mrtvý kód, špatné typové hinty, security issues (XSS, mass assignment, SQL injection).
U každého nálezu: soubor:řádek, popis problému, návrh fixu.
Neopravuj nic sám, jen vypiš seznam seřazený podle závažnosti.
```

## 2. Design/UI konzistence (Fable)

```
Projdi všechny Blade/Livewire views a Flux UI komponenty v projektu.
Zkontroluj: jednotnost designu (barvy, spacing, typografie, border-radius),
duplicitní markup co by měl být sdílená komponenta, nekonzistentní pojmenování komponent,
responzivitu (mobile breakpointy), přístupnost (a11y - aria labels, kontrast).
Navrhni, které opakující se bloky vytáhnout do znovupoužitelných Flux/Blade komponent
a jak sjednotit design tokens (barvy, spacing) přes celou appku.
Výstup: seznam problémů + konkrétní návrh struktury komponent.
```

## 3. Dokumentace / CLAUDE.md (Fable, spustit poslední)

```
Projdi strukturu projektu (composer.json, package.json, routes, models, config)
a existující CLAUDE.md. Over, jestli CLAUDE.md odpovídá skutečnému stavu projektu
(verze balíčků, konvence, struktura složek, workflow).
Navrhni aktualizovaný CLAUDE.md: přesný foundational context, project-specific
konvence (ne obecné Laravel rady - ty už máme), popis architektury appky
(co je za moduly/domény, jak spolu souvisí), a chybějící sekce (deployment,
env setup, testing workflow).
Výstup: diff/návrh nového CLAUDE.md + seznam co se v projektu liší od popsaného stavu.
```

Pořadí: 1 a 2 nezávislé, 3 nejlíp až po nich (víc kontextu co sedí).

## Doporučené nástroje (Claude Code skills, už dostupné)

- **code-review** — po diffu z promptu 1, najde bugy/duplicity, `--fix` rovnou opraví
- **security-review** — security pass na pending changes, navazuje na prompt 1
- **verify** — ověří že změny fakt fungujou end-to-end, ne jen že testy projdou
- **run** — nastartuje appku, screenshot, vizuální ověření UI změn (prompt 2)
- **caveman:cavecrew** — delegace velkých auditů na subagenty, šetří kontext (~60 %)
