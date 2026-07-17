# Google Analytics 4 a souhlas s cookies

Produkční šablona používá vlastní privacy-first integraci Google Analytics 4 (GA4) v režimu Basic Consent Mode v2. Google se před souhlasem návštěvníka vůbec nekontaktuje. Integrace se nespustí přihlášeným uživatelům WordPressu ani mimo `statekcholupice.cz` a `www.statekcholupice.cz`.

## Správa měření

- Měřicí ID se mění ve WordPressu v **Nastavení → Obecné → Google Analytics 4 – měřicí ID**.
- Samotné nasazení souborů analytiku neaktivuje.
- Při dosud neexistujícím nastavení, prázdné hodnotě nebo neplatném ID je analytika vypnutá. Aktivuje se pouze po explicitním uložení platného ID ve formátu `G-[A-Z0-9]+`.
- Doporučené ID pro tento web je `G-6WYM4Z2VWZ`; správce je smí uložit až po zveřejnění zásad ochrany osobních údajů a dokončení produkčního testu cookie lišty.
- Verze souhlasu je konstanta `STATEK_CHOLUPICE_CONSENT_VERSION` v `inc/analytics.php`. Zvýšení čísla zneplatní dřívější rozhodnutí a všem návštěvníkům znovu zobrazí volbu.
- Technická first-party cookie `statek_cookie_consent` obsahuje jen verzi, volbu `granted`/`denied` a ISO datum změny. Má platnost 180 dnů, `Path=/`, `SameSite=Lax` a na HTTPS také `Secure`.

## Pořadí produkčního spuštění

1. Vytvořit a ověřit obnovitelnou zálohu souborů a databáze.
2. Publikovat stránku zásad ochrany osobních údajů na `https://www.statekcholupice.cz/ochrana-osobnich-udaju/`.
3. Nasadit kód s analytikou ve vypnutém stavu.
4. Anonymně otestovat cookie lištu bez aktivního Google tagu.
5. V administraci explicitně uložit `G-6WYM4Z2VWZ`.
6. Znovu otestovat souhlas, odmítnutí, odvolání, cookies, Network a GA4 DebugView.

## Co se měří

Po souhlasu se odešle jeden automatický `page_view` a tyto omezené události:

| Událost | Význam | Parametry bez osobních údajů |
| --- | --- | --- |
| `section_view` | Krátká sekce byla nejméně z 50 % viditelná; u sekce vyšší než viewport byla viditelná nejméně polovina viewportu. Stav musí trvat alespoň 1 sekundu a každá sekce se měří nejvýše jednou za načtení. | `section_id`, `section_name` |
| `faq_open` | Otevření odpovědi FAQ | `faq_id`, `faq_position`, zkrácený `faq_title` |
| `before_after_interaction` | První skutečné použití slideru | `slider_id`, `slider_position`, `interaction_method` |
| `before_after_complete` | První dosažení každého krajního pohledu | `slider_id`, `slider_position`, `view` |
| `area_tab_select` | Uživatelská změna části areálu | `area_id`, `area_title`, `area_position` |
| `contact_click` | Kliknutí na e-mailový odkaz | `method=email`, `placement` |
| `generate_lead` | Jen úspěšná odpověď serveru kontaktního formuláře, pokud před odesláním nebylo vyplněné honeypot pole | `method=contact_form` |
| `select_content` | Otevření novinky | `content_type=news`, ID a název novinky, `placement` |

Jméno, e-mail, text dotazu, hodnota formulářového pole ani celá URL se do vlastních událostí neposílají. Veřejné rozhraní poskytuje formuláři jen bezparametrickou metodu `window.StatekAnalytics.recordLead()`; ostatní události vznikají výhradně z předem určených prvků šablony. Bez souhlasu všechny pokusy o měření tiše skončí.

## Odvolání souhlasu

Tlačítko **Nastavení cookies** v patičce znovu otevře stejnou volbu. Při pozdějším odmítnutí se analytický souhlas přepne na `denied`, odstraní se dostupné cookies `_ga`, `_ga_*`, `_gid`, `_gat` a `_gat_*`, uloží se technická cookie s odmítnutím a stránka se obnoví.

## Test před a po souhlasu

Testujte v anonymním okně na skutečné produkční doméně a se zavřenou administrací WordPressu.

1. Před souhlasem v DevTools → Network filtrujte `google`, `gtag` a `collect`. Nesmí existovat požadavek na `googletagmanager.com`, `google-analytics.com` ani GA collect endpoint. V Application → Cookies nesmí být `_ga` ani `_ga_*`.
2. Po **Odmítnout** obnovte stránku. Lišta zůstane skrytá, technická cookie obsahuje `denied` a Google se nekontaktuje.
3. Po **Povolit analytiku** zkontrolujte právě jeden `gtag/js`, jeden počáteční `page_view` a cookies `_ga`/`_ga_*`. V `dataLayer` musí reklamní souhlasy zůstat `denied`.
4. Přes **Nastavení cookies** souhlas odvolejte. Po automatickém obnovení nesmí být analytické cookies ani požadavky Googlu, ale technická cookie s `denied` musí zůstat.
5. Události ověřte v GA4 DebugView nebo v payloadu požadavků `collect`; kontrolujte jejich počet i absenci osobních údajů.

Na localhostu, GitHub Pages, cizí doméně a pro přihlášeného uživatele se nevykreslí ani consent UI a nenačte se `analytics.js`.

## QR adresa `/letak/`

`/letak/` i `/letak` vrací dočasné HTTP 302 na homepage s parametry:

```text
?utm_source=letak&utm_medium=qr&utm_campaign=spusteni_webu_2026&utm_content=a5_cholupice
```

Parametry se mění v poli `add_query_arg()` ve funkci `statek_cholupice_flyer_redirect()` v `inc/analytics.php`. Redirect posílá no-cache hlavičky a funguje pouze na obou produkčních hostnames.

## Ruční nastavení GA4

1. Název služby: **Statek Cholupice**.
2. Časové pásmo: Praha / Česká republika; měna: CZK.
3. Uchování dat na úrovni uživatele a události nastavit na **14 měsíců**. Google uvádí pro standardní GA4 volby 2 nebo 14 měsíců; nastavení ovlivňuje zejména explorace, ne souhrnné standardní reporty ([oficiální dokumentace](https://support.google.com/analytics/answer/7667196?hl=en)).
4. Google Signals vypnout.
5. Reklamní personalizaci vypnout a nevytvářet propojení s Google Ads.
6. V datovém streamu ponechat rozšířené měření pro page views, scrolls, outbound clicks a file downloads. Video engagement zapnout jen při skutečném podporovaném YouTube embedu.
7. Vypnout **Form interactions**, protože úspěch formuláře měří přesně `generate_lead`. Vypnout **Site search**, dokud web nemá skutečné vyhledávání. Google popisuje jednotlivé přepínače a sbírané parametry v [dokumentaci rozšířeného měření](https://support.google.com/analytics/answer/9216061?hl=en).
8. Po přijetí první události označit `generate_lead` jako klíčovou událost.
9. Po nasazení ověřit Realtime a DebugView včetně všech vlastních událostí a jejich neduplikování.
10. V nastavení webového streamu ověřit přesnou produkční URL a měřicí ID `G-6WYM4Z2VWZ`.

## Text k ručnímu vložení na stránku Ochrana osobních údajů

Obsah stránky je uložen pouze v databázi WordPressu, proto jej nelze bezpečně změnit commitem. Následující text musí správce vložit nebo přizpůsobit na existující stránce a před zveřejněním právně zkontrolovat v kontextu celých zásad:

> **Správce a kontakt**
> Správcem osobních údajů souvisejících s tímto webem je DSS a.s., IČ 26161541, se sídlem Kloboučnická 1735/26, Nusle, 140 00 Praha 4, zapsaná v obchodním rejstříku vedeném Městským soudem v Praze, oddíl B, vložka 6434. Kontakt: info@statekcholupice.cz.
>
> **Google Analytics 4**
> Službu Google Analytics 4 aktivujeme pouze tehdy, když nám k analytickým cookies udělíte souhlas. Před udělením souhlasu se analytický skript Google nenačte a web neodesílá analytické požadavky společnosti Google. Právním základem tohoto zpracování je váš souhlas, který můžete kdykoli odvolat tlačítkem „Nastavení cookies“ v patičce webu. Odvolání nemá vliv na zákonnost zpracování provedeného před jeho odvoláním.
>
> Účelem je vyhodnocovat používání webu a zlepšovat jeho obsah. Zpracováváme přitom pseudonymní údaje o návštěvě a interakcích s webem, například zobrazené stránky a sekce, použitý typ zařízení a prohlížeče, přibližné časové a technické údaje a omezené události popsané na tomto webu. Do Google Analytics neposíláme jméno, e-mail ani obsah dotazu z kontaktního formuláře. Tyto analytické údaje nepovažujeme za zcela anonymní.
>
> Po souhlasu může Google Analytics nastavit zejména first-party cookies `_ga` a `_ga_<ID>`, obvykle až na 2 roky; podle použité funkce se mohou objevit také krátkodobé `_gid` (obvykle 24 hodin) nebo `_gat`/`_gat_*` (obvykle několik minut). Google uvádí, že hlavní cookie `_ga` rozlišuje návštěvníky a běžně trvá 2 roky ([informace Googlu o cookies](https://policies.google.com/technologies/cookies)). Samostatná technická cookie `statek_cookie_consent` uchovává pouze vaši volbu, verzi souhlasu a datum změny po dobu 180 dnů.
>
> Příjemcem a poskytovatelem analytické služby je Google, zejména Google Ireland Limited a podle použitelných smluvních podmínek další společnosti skupiny Google. V souvislosti se službou může docházet k předání údajů mimo EU/EHP. Google popisuje používané právní mechanismy, včetně rámce EU–USA Data Privacy Framework a standardních smluvních doložek pro případy, kdy se na konkrétní předání příslušný rámec nepoužije ([informace Googlu o mezinárodních přenosech](https://business.safety.google/adsdatatransfers/)).
>
> Uchování dat na úrovni uživatele a události v naší službě GA4 nastavujeme na 14 měsíců. Doba uložení jednotlivých cookies se řídí jejich vlastní expirací uvedenou výše. Souhlas lze kdykoli změnit přes „Nastavení cookies“; při odvolání se dostupné analytické cookies odstraní a při dalším načtení se analytika nespustí.

Text uvádí konkrétní nastavení, které musí správce v GA4 skutečně provést. Pokud bude změněn správce, kontakt, retenční doba, smluvní vztah s Googlem nebo používané funkce, je nutné text současně aktualizovat.
