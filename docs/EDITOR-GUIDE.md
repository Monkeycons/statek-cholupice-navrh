# Editor Guide

## Co se spravuje v administraci

Novinky se spravují jako běžné příspěvky WordPressu. Na homepage se zobrazí posledních 6 publikovaných příspěvků. Samostatný archiv novinek používá `home.php`, H1 „Novinky“ a standardní stránkování.

V Nastavení > Statek Cholupice lze nastavit:

- e-mail pro kontaktní formulář a viditelné kontaktní odkazy,
- URL zásad zpracování osobních údajů.

Na stránce nastavené jako homepage je metabox „Statek Cholupice - obsah homepage“. Umožňuje upravit:

- texty v hero sekci,
- nadpisy a úvod sekce častých dotazů,
- položky FAQ jako JSON pole objektů `{ "question": "...", "answer": "<p>...</p>" }`,
- nadpis a text kontaktního bloku.

Prázdná pole nepřepisují schválený výchozí obsah šablony.

## Omezení editace

Detailní layoutové bloky „O projektu“, „Šest částí, jeden živý areál“, „Jak bude areál fungovat“, Bezpečnost / Doprava / Životní prostředí a Přínosy zůstávají v této release candidate verzi pevnou součástí šablony, aby se nerozbil schválený vizuální návrh. Pokud má být klient později spravuje celý bez zásahu do kódu, doporučený další krok je rozšířit stejný nativní metaboxový systém o tyto opakující se bloky.
