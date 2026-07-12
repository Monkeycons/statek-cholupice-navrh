<?php
/**
 * Footer šablony.
 *
 * @package StatekCholupice
 */
?>
<footer>
	<div class="wrap footer-main">
		<div class="footer-column">
			<h3>Investor projektu</h3>
			<p>DSS a.s.<br>Kloboučnická 1735/26, Nusle, 140 00 Praha 4<br>IČ: 26161541, DIČ: CZ26161541<br>Zapsaná v obchodním rejstříku vedeném Městským soudem v Praze, oddíl B, vložka 6434.</p>
		</div>
		<div class="footer-column">
			<h3>Kontakt</h3>
			<p><a href="mailto:<?php echo esc_attr( statek_cholupice_contact_email() ); ?>"><?php echo esc_html( statek_cholupice_contact_email() ); ?></a></p>
		</div>
		<div class="footer-column">
			<h3>Dokumenty</h3>
			<p><a href="<?php echo esc_url( statek_cholupice_privacy_url() ); ?>">Zásady zpracování osobních údajů</a></p>
		</div>
	</div>
	<div class="wrap footer-notice">
		<div>
			<h3>Aktuálnost informací</h3>
			<p>Informace uvedené na tomto webu odpovídají stavu projektu v době jejich zveřejnění. Průběžně je aktualizujeme, mezi změnou projektu a jejím zveřejněním na webu však může vzniknout časová prodleva.</p>
		</div>
		<div>
			<h3>Vizualizace projektu</h3>
			<p>Vizualizace mají ilustrativní charakter a zachycují předpokládanou podobu projektu v době svého vzniku. V průběhu další přípravy, povolování a realizace může dojít k dílčím změnám architektonického, technického nebo materiálového řešení.</p>
		</div>
	</div>
	<div class="wrap footer-bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> Statek Cholupice</span>
		<div class="footer-bottom-links">
			<a href="<?php echo esc_url( statek_cholupice_privacy_url() ); ?>">Zásady zpracování osobních údajů</a>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
