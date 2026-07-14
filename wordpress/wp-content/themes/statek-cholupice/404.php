<?php
/**
 * 404
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
	<section class="not-found-section">
		<div class="wrap">
			<div class="not-found-card">
				<p class="not-found-kicker">404</p>
				<h1>Stránka nebyla nalezena</h1>
				<p>Požadovaná stránka možná změnila adresu nebo už není dostupná. Můžete se vrátit na úvod, podívat se na novinky nebo nám napsat.</p>
				<div class="not-found-actions">
					<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Zpět na homepage</a>
					<a class="read-more" href="<?php echo esc_url( get_post_type_archive_link( 'post' ) ?: home_url( '/novinky/' ) ); ?>">Novinky</a>
				</div>
				<p class="not-found-contact">Kontakt: <a href="mailto:<?php echo esc_attr( statek_cholupice_contact_email() ); ?>"><?php echo esc_html( statek_cholupice_contact_email() ); ?></a></p>
			</div>
		</div>
	</section>
</main>
<?php
get_footer();
