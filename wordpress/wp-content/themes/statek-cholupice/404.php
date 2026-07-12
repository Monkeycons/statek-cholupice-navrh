<?php
/**
 * 404
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
	<section>
		<div class="wrap">
			<h1>Stránka nebyla nalezena</h1>
			<p>Omlouváme se, požadovaná stránka neexistuje.</p>
			<p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>">Zpět na úvod</a></p>
		</div>
	</section>
</main>
<?php
get_footer();
