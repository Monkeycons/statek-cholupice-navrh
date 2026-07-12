# Editor Guide

## Co se spravuje v administraci

Novinky se spravují jako běžné příspěvky WordPressu. Plugin při nové instalaci vytvoří nebo najde stránku `Novinky` a nastaví ji jako blog index (`page_for_posts`), pokud už správce dříve nezvolil jinou stránku. Na homepage se zobrazí posledních 6 publikovaných příspěvků. Samostatný archiv novinek používá `home.php`, H1 „Novinky“ a standardní stránkování.

V Nastavení > Statek Cholupice lze nastavit:

- e-mail pro kontaktní formulář a viditelné kontaktní odkazy,
- URL zásad zpracování osobních údajů.

Na stránce nastavené jako homepage jsou samostatné metaboxy „Statek Cholupice“. Zobrazují se pouze u stránky nastavené jako `page_on_front`, ne u ostatních běžných stránek.

Spravovat lze:

- hero texty a CTA tlačítka,
- úvod sekce častých dotazů,
- O projektu včetně hlavního obrázku a dvojice obrázků Před / Po,
- šest částí areálu včetně pořadí, textů, obrázků a alt textů,
- sekci „Jak bude areál fungovat“ včetně seznamů „bude“ a „nebude“,
- sekce Bezpečnost, Doprava a Životní prostředí včetně rozklikávacího textu a ilustračních fotografií,
- Přínosy včetně šesti karet, pořadí a předdefinované ikony,
- Časté dotazy přes běžné repeater rozhraní bez ručního JSON,
- kontaktní blok pod FAQ,
- investorské údaje a upozornění v patičce.

## Obrázky

Obrázky se vybírají přes standardní WordPress media modal. Pokud editor obrázek nevybere, web použije schválený výchozí obrázek ze šablony. Alt text lze upravit samostatně.

## FAQ

FAQ se needituje jako JSON. Každá otázka má vlastní řádek s polem „Otázka“ a „Odpověď“. Odpověď pište jako běžný text, odstavce oddělte prázdným řádkem. Maximální počet je 12 otázek.

## Fallback

Prázdná pole nepřepisují schválený výchozí obsah šablony. Pokud editor některou hodnotu smaže a uloží, návštěvnický web se vrátí k původnímu schválenému textu nebo obrázku.
