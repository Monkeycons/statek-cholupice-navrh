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
## Dodatek 1.1.0-rc.2

Aktualni kombinace balicku: sablona `1.1.0-rc.2`, companion plugin `1.1.0-rc.1`.

Opravena byla pouze dvojice vizualnich regresi nalezenych v runtime testu Playgroundu:

- Tematicke obrazky v sekcich Bezpecnost, Doprava a Zivotni prostredi pouzivaly pri desktopovem dvousloupci prilis obecny `sizes` atribut a pri otevrenem detailu se levy obrazovy panel mohl roztahovat na vysku textu. To vedlo hlavne u Zivotniho prostredi k optickemu zmekceni obrazu. Oprava nastavuje presnejsi `sizes` pro skutecnou sirku leveho panelu a drzi tematickou ilustraci v pomeru 16:9 misto natahovani na vysku otevreneho textu.
- Pred opravou se pro desktopovy panel mohla pri beznem DPR nacitat 960px varianta; po oprave je pro 1440px a 1920px desktop vybirana 1280px nebo 1600/1672px varianta podle formatu a DPI. U Zivotniho prostredi jsou dostupne varianty 960, 1280 a 1600 px.
- Karty Novinek mely zbytecne vysokou textovou cast a obraz pusobil prilis portretne. Oprava vynucuje 16:9 na klikacim obrazovem wrapperu, pridava spravne `sizes` pro nahledove obrazky, zmensuje vnitrni odsazeni, omezuje nadpis na 2 radky a perex na 3 radky. Celkovy obsah karty zustava dostupny pres detail clanku.

Kontrola rozmeru podle CSS po oprave:

- Tematicky obraz desktop 1440 px: rendered width priblizne 640 px, DPR 1 vybere minimalne 1280px zdroj pri dostupnosti; DPR 2 vybere nejvetsi dostupnou 1600/1672px variantu.
- Tematicky obraz desktop 1920 px: rendered width zustava omezeny kontejnerem priblizne 640 px; nejvetsi dostupna varianta zabranuje upscalingu.
- Novinky desktop 1440 px: 3 karty vedle sebe, karta priblizne 379 px, obraz priblizne 379 x 213 px, pomer 16:9.
- Novinky tablet 1024 px: 2 karty vedle sebe, obraz zustava 16:9, bez horizontalniho overflow.
- Novinky mobil 390 px: horizontalni scroll/snap zustava zachovan, karta ma sirku priblizne 82vw a obraz zustava 16:9.

Neprovedene testy:

- Plnohodnotny vizualni screenshot v zivem Playgroundu a PHP lint nebyly v tomto lokalnim prostredi znovu spusteny, protoze zde neni dostupny systemovy PHP runtime a network/browser runtime muze vyzadovat rucni pristup. ZIP struktura, verze a staticke kontroly byly znovu overeny lokalne.
