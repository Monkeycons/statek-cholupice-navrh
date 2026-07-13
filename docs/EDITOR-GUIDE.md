# Editor Guide

Tento návod platí pro release candidate `1.1.0-rc.1`. Obsah úvodní stránky se upravuje pouze na stránce nastavené ve WordPressu jako statická homepage (`page_on_front`). Metaboxy se na jiných stránkách nezobrazují.

## Hero a CTA

- Texty hero sekce mají vlastní pole a zobrazený maximální počet znaků.
- Hero obrázek vyberte přes tlačítko **Vybrat obrázek**. Doporučený rozměr je alespoň 1920 × 1080 px v poměru 16:9.
- Hero je dekorativní, proto se u něj alt text nezadává.
- Tlačítko **Odebrat vybraný obrázek** obnoví schválený optimalizovaný obrázek šablony.
- Cíle CTA mohou být kotvy, například `#projekt`, relativní interní cesty nebo absolutní HTTPS adresy.
- Výchozí cíle jsou `#projekt` a `#prinosy`. Neplatná nebo nebezpečná adresa se nahradí odpovídající výchozí kotvou.

## Obrázky a alt text

Všechna obrazová pole používají standardní WordPress knihovnu médií omezenou pouze na obrázky. Po výběru se uloží attachment ID a zobrazí náhled. U obsahových fotografií zkontrolujte alt text; při výměně se načte alt nové přílohy nebo se pole vyprázdní. Alt se nikdy nevytváří z názvu souboru. Každý vlastní obrázek lze odebrat a vrátit se tak k výchozí fotografii šablony.

## Časté dotazy

- Novou otázku přidejte tlačítkem **Přidat otázku**.
- Pole **Pořadí** je číslo od 1 do 12. Nová otázka dostane automaticky další volné číslo.
- Při stejném čísle zůstane zachováno pořadí položek ve formuláři; žádná otázka se neztratí.
- Odpověď pište jako běžný text. Samostatné odstavce oddělte prázdným řádkem; jednoduché zalomení zůstane zalomením uvnitř odstavce.
- Vyplněná otázka se před odebráním ještě potvrdí. Maximální počet je 12 položek.

## Další editovatelný obsah

Spravovat lze O projektu, části areálu, provoz, Bezpečnost, Dopravu, Životní prostředí, Přínosy, úvod FAQ, kontaktní blok a patičku. Novinky se spravují jako běžné příspěvky WordPressu. Nastavení kontaktního e-mailu a odkazu na ochranu osobních údajů je v **Nastavení > Statek Cholupice**.

## Délkové limity

Každé pole zobrazuje svůj limit a stejnou hodnotu vynucuje server. Hlavní limity jsou: hero kicker 50 znaků, H1 90, hero text 320, CTA popisek 40, nadpis sekce 140, nadpis karty 90, motto 240, krátký perex a text přínosu 500, FAQ otázka 180 a odpověď 3000 znaků. Delší odstavce mají samostatný limit 1800 znaků.

Prázdné obsahové pole obnoví schválený fallback šablony. Výjimkou jsou CTA adresy, které se při neplatné hodnotě bezpečně vrátí na výchozí kotvu.
