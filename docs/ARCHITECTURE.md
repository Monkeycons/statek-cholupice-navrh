# Architektura

Release candidate `1.1.0-rc.1` je rozdělený na šablonu `statek-cholupice` a companion plugin `statek-cholupice-core`.

Šablona řeší schválený vzhled, layout, front-endové interakce, navigaci, homepage, archiv novinek, metadata a responzivní vykreslení obrázků. Plugin řeší kontaktní REST endpoint, globální nastavení, idempotentní inicializaci webu a nativní administrační metaboxy bez ACF.

## Homepage data

Schválený obsah je uložen jako fallback v `inc/content.php`. Redakční data jsou post meta stránky nastavené jako `page_on_front`. Jednoduchá pole jsou samostatná metadata; opakovatelné struktury jsou JSON s bezpečným WordPress slashing postupem. Sanitizace i HTML `maxlength` používají jednu centrální mapu limitů v companion pluginu.

Repeatery používají jednotný token `__INDEX__` a lokální inkrementální čítač odvozený od nejvyššího existujícího indexu. Číselné pořadí se normalizuje na serveru a duplicity se řadí stabilně podle původní pozice.

## Obrázky

Výchozí obrázky zajišťuje `statek_cholupice_picture()`, který skládá AVIF, WebP a JPEG varianty z `assets/images/optimized`. Vlastní obsahové obrázky a hero se ukládají jako WordPress attachment ID.

`statek_cholupice_hero_picture()` použije pro vlastní hero WordPress attachment API, které doplní rozměry, `srcset` a `sizes`; obrázek zůstává v původním wrapperu a zachovává schválený crop i overlay. Pokud attachment chybí nebo není platný obrázek, helper použije původní optimalizovanou picture pipeline šablony.

## CTA a asset cache

Hero CTA cíle mohou být bezpečné hash kotvy, relativní interní cesty nebo HTTPS adresy. Neplatná schémata se vracejí na `#projekt` nebo `#prinosy`. Veřejný výstup používá `esc_url()`.

Veřejné i administrační CSS a JavaScript assety používají jako verzi `filemtime()` existujícího souboru a verzi šablony nebo pluginu pouze jako fallback.
