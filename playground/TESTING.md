# Testování ve WordPress Playgroundu

Playground vytvoří dočasný WordPress v prohlížeči, nainstaluje release candidate šablony a companion pluginu a přihlásí testujícího jako správce. Změny nejsou trvalé a po opuštění nebo obnovení dočasné instance mohou zmizet.

## Veřejná část webu

1. **Načíst domovskou stránku.** Očekávaný výsledek: homepage se zobrazí bez PHP chyby, warningu nebo prázdné obrazovky.
2. **Ověřit desktopové menu.** Očekávaný výsledek: všechny položky jsou viditelné, klikatelné a vedou na správné sekce.
3. **Ověřit mobilní menu.** Očekávaný výsledek: menu lze otevřít, zavřít a každá položka funguje.
4. **Ověřit sticky header.** Očekávaný výsledek: při posunu zůstává čitelný, nepřekrývá obsah a funguje návrat na začátek.
5. **Ověřit administrační lištu.** Očekávaný výsledek: přihlášený správce vidí lištu a ta nerozbíjí rozložení webu.
6. **Nastavit porovnání Před / Po na 0, 25, 50, 75 a 100 %.** Očekávaný výsledek: jezdec, obrázky i dynamické štítky odpovídají každé poloze.
7. **Ovládat Před / Po klávesnicí.** Očekávaný výsledek: fungují šipky vlevo a vpravo i klávesy Home a End.
8. **Projít všech šest částí areálu.** Očekávaný výsledek: každá položka zobrazí správný obrázek, název a text bez rozbití layoutu.
9. **Projít Bezpečnost, Dopravu a Životní prostředí.** Očekávaný výsledek: fotografie, úvodní texty a rozbalování fungují ve všech třech sekcích.
10. **Otevřít všechny položky FAQ.** Očekávaný výsledek: každá odpověď se otevře a zavře, odstavce zůstanou oddělené a řádky neposkakují.

## Administrace domovské stránky

11. **Otevřít editaci homepage.** Očekávaný výsledek: stránka „Domovská stránka“ obsahuje všechny administrační panely projektu.
12. **Upravit hero nadpis a uložit.** Očekávaný výsledek: změna se po obnovení administrace i webu zachová.
13. **Upravit hero obrázek.** Očekávaný výsledek: lze vybrat obrázek z médií, uložit jej a následně jej také odebrat.
14. **Upravit obě CTA URL.** Očekávaný výsledek: funguje kotva, relativní cesta i bezpečná externí HTTPS adresa.
15. **Uložit text `Výraz "brownfield"`.** Očekávaný výsledek: uvozovky zůstanou po uložení i novém načtení beze změny.
16. **Uložit text s českými znaky.** Očekávaný výsledek: diakritika se neztratí ani nepoškodí.
17. **Uložit víceřádkový text.** Očekávaný výsledek: zalomení řádků zůstane zachováno.
18. **Uložit FAQ se dvěma samostatnými odstavci.** Očekávaný výsledek: po opětovném otevření editoru i na webu zůstanou dva odstavce.
19. **Přidat rychle pět FAQ za sebou.** Očekávaný výsledek: vznikne pět samostatných položek bez přepsaných polí.
20. **Zkontrolovat unikátní položky a pořadí.** Očekávaný výsledek: ovládací prvky mají unikátní vazby a pořadí je automaticky doplněné.
21. **Změnit pořadí FAQ a uložit.** Očekávaný výsledek: veřejná stránka respektuje nové pořadí a duplicity jsou řazené stabilně.
22. **Odstranit jednu FAQ.** Očekávaný výsledek: odstraní se pouze vybraná položka a ostatní data zůstanou zachována.
23. **Vyměnit obrázek přes media modal.** Očekávaný výsledek: dialog nabízí pouze obrázky a vybraný soubor nahradí původní.
24. **Zkontrolovat alt text.** Očekávaný výsledek: nový obrázek převezme vlastní alt text; bez zadaného altu se nepoužije název souboru.

## Novinky a systémové stránky

25. **Otevřít stránku Novinky.** Očekávaný výsledek: zobrazí se archiv příspěvků ve schváleném designu.
26. **Ověřit čtyři testovací příspěvky.** Očekávaný výsledek: existují přesně označené novinky 1 až 4.
27. **Otevřít detail článku.** Očekávaný výsledek: titulek, datum a obsah článku se zobrazí bez chyby.
28. **Ověřit archiv a stránkování.** Očekávaný výsledek: při dvou článcích na stránku lze přejít na další i předchozí stránku.
29. **Otevřít neexistující adresu.** Očekávaný výsledek: zobrazí se vlastní stránka 404 a navigace zůstane funkční.

## Kontaktní formulář a diagnostika

30. **Odeslat kontaktní formulář jako přihlášený správce.** Očekávaný výsledek: formulář projde aplikačním zpracováním a zobrazí srozumitelný stav.
31. **Odeslat formulář s prázdnými povinnými poli.** Očekávaný výsledek: validace upozorní na chybějící údaje a formulář neodešle.
32. **Zadat neplatný e-mail.** Očekávaný výsledek: formulář požádá o platnou e-mailovou adresu.
33. **Posoudit doručení e-mailu.** Očekávaný výsledek: test potvrzuje pouze zpracování formuláře; Playground neprokazuje skutečné doručení přes cílový hosting.
34. **Zkontrolovat konzoli prohlížeče.** Očekávaný výsledek: nejsou zde neočekávané JavaScriptové chyby ani neúspěšné požadavky související s webem.
35. **Zkontrolovat PHP warningy na stránce.** Očekávaný výsledek: při zapnutém `WP_DEBUG` a `WP_DEBUG_DISPLAY` se nezobrazí warning, notice ani fatal error.

## Poznámka k ukončení testu

Playground je dočasné prostředí. Po testování není potřeba nic publikovat ani převádět do produkce. Skutečné doručení kontaktních e-mailů se musí později ověřit na cílovém hostingu.
