# Editor Guide

## Co se spravuje v administraci

Novinky se spravují jako běžné příspěvky WordPressu. Plugin při nové instalaci vytvoří nebo najde stránku `Novinky` a nastaví ji jako blog index (`page_for_posts`), pokud už správce dříve nezvolil jinou stránku. Na homepage se zobrazí posledních 6 publikovaných příspěvků. Samostatný archiv novinek používá `home.php`, H1 „Novinky“ a standardní stránkování.

V Nastavení > Statek Cholupice lze nastavit:

- e-mail pro kontaktní formulář a viditelné kontaktní odkazy,
- URL zásad zpracování osobních údajů.

Na stránce nastavené jako homepage je metabox „Statek Cholupice - obsah homepage“. Metabox se zobrazuje pouze u stránky nastavené jako `page_on_front`, ne u ostatních běžných stránek. Umožňuje upravit:

- texty v hero sekci,
- nadpisy a úvod sekce častých dotazů,
- položky FAQ jako JSON pole objektů `{ "question": "...", "answer": "<p>...</p>" }`,
- nadpis a text kontaktního bloku.

Prázdná pole nepřepisují schválený výchozí obsah šablony.

## Omezení editace

Detailní layoutové bloky „O projektu“, „Šest částí, jeden živý areál“, „Jak bude areál fungovat“, Bezpečnost / Doprava / Životní prostředí, Přínosy, investorské údaje a patičkové upozornění zůstávají v této release candidate verzi pevnou součástí šablony, aby se nerozbil schválený vizuální návrh. Tato verze proto není označovaná jako plně redakčně editovatelný web. Pokud má klient později spravovat celý obsah bez zásahu do kódu, doporučený další krok je rozšířit stejný nativní metaboxový systém o tyto opakující se bloky a mediální pole.
