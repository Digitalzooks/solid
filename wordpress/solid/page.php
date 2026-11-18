<?php
/**
 * A standard page (policies and the like): page header, then the page text.
 * Pages built with Elementor use the "Elementor Full Width" template instead.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$lede = has_excerpt() ? get_the_excerpt() : 'Last updated ' . get_the_modified_date( 'j F Y' );
	echo solid_phead( array( 'crumb' => solid_e( get_the_title() ), 'title' => solid_e( get_the_title() ), 'lede' => solid_e( $lede ) ) ); // phpcs:ignore
	?>
<section class="pblock"><div class="art__body prose"><?php the_content(); ?></div></section>
	<?php
endwhile;
get_footer();
