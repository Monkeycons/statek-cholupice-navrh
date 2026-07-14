<?php
/**
 * Header šablony.
 *
 * @package StatekCholupice
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script>document.documentElement.classList.add("js");</script>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Přejít na obsah', 'statek-cholupice' ); ?></a>
<header class="site-header<?php echo is_front_page() ? '' : ' site-header--solid'; ?>">
	<div class="nav">
		<a class="brand brand-link" href="<?php echo esc_url( statek_cholupice_anchor_url( 'uvod' ) ); ?>" aria-label="<?php esc_attr_e( 'Statek Cholupice - zpět na úvod', 'statek-cholupice' ); ?>">Statek Cholupice</a>
		<nav aria-label="<?php esc_attr_e( 'Hlavní menu', 'statek-cholupice' ); ?>">
			<?php statek_cholupice_primary_navigation(); ?>
		</nav>
		<button class="mobile-menu-toggle" id="mobile-menu-toggle" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="<?php esc_attr_e( 'Otevřít menu', 'statek-cholupice' ); ?>">
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
			<span aria-hidden="true"></span>
		</button>
	</div>
	<div class="mobile-menu" id="mobile-menu" hidden>
		<nav aria-label="<?php esc_attr_e( 'Mobilní menu', 'statek-cholupice' ); ?>">
			<?php statek_cholupice_primary_navigation( true ); ?>
		</nav>
	</div>
</header>
