<?php
/**
 * Footer šablony.
 *
 * @package StatekCholupice
 */
$footer = statek_cholupice_footer_data();
?>
<footer>
	<div class="wrap footer-main">
		<div class="footer-column">
			<h3>Investor projektu</h3>
			<p>
				<?php echo esc_html( $footer['investor_name'] ); ?><br>
				<?php echo esc_html( $footer['investor_address'] ); ?><br>
				<?php echo esc_html( $footer['investor_id'] ); ?><br>
				<?php echo esc_html( $footer['investor_registry'] ); ?>
			</p>
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
			<h3><?php echo esc_html( $footer['info_heading'] ); ?></h3>
			<p><?php echo esc_html( $footer['info_text'] ); ?></p>
		</div>
		<div>
			<h3><?php echo esc_html( $footer['visuals_heading'] ); ?></h3>
			<p><?php echo esc_html( $footer['visuals_text'] ); ?></p>
		</div>
	</div>
	<div class="wrap footer-bottom">
		<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> Statek Cholupice</span>
		<div class="footer-bottom-links">
			<a href="<?php echo esc_url( statek_cholupice_privacy_url() ); ?>">Zásady zpracování osobních údajů</a>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
