<?php
/**
 * Detail článku
 *
 * @package StatekCholupice
 */

get_header();
?>
<main id="main-content">
	<section>
		<div class="wrap">
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<article <?php post_class(); ?>>
					<p class="news-card-date"><?php echo esc_html( get_the_date() ); ?></p>
					<h1><?php the_title(); ?></h1>
					<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'large', array( 'class' => 'news-card-image' ) ); } ?>
					<?php the_content(); ?>
				</article>
			<?php endwhile; endif; ?>
		</div>
	</section>
</main>
<?php
get_footer();
