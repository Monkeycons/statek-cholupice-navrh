# QA Report

Výchozí bod této revize: commit `a4cde36 Preserve FAQ answer paragraphs` na větvi `feat/wordpress-production-theme`.

Release candidate šablony a companion pluginu: `1.1.0-rc.1`.

## Implementované opravy

- Repeatery používají pouze globálně nahrazovaný token `__INDEX__` a lokální inkrementální čítač od nejvyššího existujícího indexu.
- Nová řazená položka dostane další volné číslo; server nahrazuje prázdné a neplatné hodnoty pozicí ve formuláři, omezuje rozsah a při duplicitách řadí stabilně.
- Jedna centrální mapa řídí HTML `maxlength`, českou informaci editorovi i serverové zkrácení textů. Unicode fallback nevyžaduje `mbstring`.
- Administrace umožňuje zvolit vlastní hero attachment a cíle obou CTA. Bez vlastního obrázku zůstává původní optimalizovaný fallback.
- Media modal je omezený na obrázky. Výměna obrázku načte alt nové přílohy nebo pole vyprázdní; odebrání obnoví fallback.
- Veřejné a administrační assety používají `filemtime()` s verzí balíčku jako fallbackem.
- ZIP balení kontroluje kořenovou složku, cesty, verzi, extrakci a vylučuje vývojové nebo potenciálně citlivé soubory.

## Provedené automatické kontroly

- Veřejný JavaScript: `node --check wordpress/wp-content/themes/statek-cholupice/assets/js/main.js` — **OK**.
- Administrační JavaScript: `node --check wordpress/wp-content/plugins/statek-cholupice-core/assets/admin-homepage.js` — **OK**.
- Repeater test `tools/test-homepage-admin.mjs` — **OK**: pět rychlých vložení, globální náhrada v `name`, `id`, `for`, ARIA a data atributech, nový index po mezeře a automatické pořadí pro prázdný seznam, duplicity i chybějící hodnotu.
- Zdrojový preflight `tools/test-wordpress-preflight.py` — **OK**: centrální limity, `maxlength`, Unicode helper, media modal image-only, hero attachment API, CTA napojení a bezpečný JSON save.
- JSON fixture — **OK** pro češtinu, `Výraz "brownfield"`, zpětná lomítka, HTTPS URL, víceřádkový text a HTML entitu.
- FAQ převodní funkce — **OK**: její zdroj je shodný s commitem `a4cde36`; zachována je obsluha jednoho až tří odstavců, sousedních `<p>`, jednoduchého `<br>`, uvozovek a víceřádkového textu.
- `git diff --check` — **OK**.
- Python syntaxe balicího a preflight skriptu — **OK**.
- ZIP extrakce — **OK** pro oba balíčky.
- ZIP verze — **OK**, šablona i plugin obsahují `1.1.0-rc.1`.
- ZIP bezpečnost — **OK**: jedna kořenová složka, dopředná lomítka, žádné absolutní nebo nadřazené cesty, `node_modules`, dočasné soubory, logy ani soubory klíčů.

Výsledné balíčky:

- `dist/statek-cholupice-theme.zip` — 19 463 253 B.
- `dist/statek-cholupice-core.zip` — 16 046 B.
- `dist/ZIP-MANIFEST.txt` — extrakční a verzovací kontrola **OK**.

## PHP kontrola

PHP runtime v tomto lokálním prostředí není dostupný. Aktuálně upravené PHP soubory proto nebylo možné pravdivě označit jako úspěšně lintované. Seznam všech 20 souborů pro následující runtime kontrolu je v `docs/PHP-LINT-FILES.txt`. Byl proveden zdrojový audit diffu, ale ten nenahrazuje `php -l`.

## Zbývá pro WordPress runtime test

- spustit `php -l` nad seznamem 20 PHP souborů;
- nainstalovat a aktivovat oba ZIPy na čistém WordPressu se zapnutým `WP_DEBUG`;
- uložit texty na hranici limitu a o znak delší včetně češtiny, emoji, více řádků a HTML entity;
- ověřit JSON round-trip přes skutečné `update_post_meta()`;
- v administraci rychle přidat, odebrat, seřadit, uložit a znovu načíst FAQ a zkontrolovat DOM bez duplicitních ID;
- ověřit media modal, alt text, vlastní hero attachment, odebrání attachmentu a responzivní `srcset`;
- ověřit hash, relativní a externí HTTPS CTA a fallback pro `javascript:`, `data:` a `vbscript:`;
- odeslat kontaktní formulář a potvrdit doručení přes konfiguraci cílového hostingu.
