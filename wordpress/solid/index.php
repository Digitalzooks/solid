<?php
/**
 * Fallback listing (blog index, tags, dates).
 */
get_header();
echo solid_phead( array( 'crumb' => solid_e( wp_strip_all_tags( get_the_archive_title() ) ), 'title' => is_home() ? 'Latest stories' : solid_e( wp_strip_all_tags( get_the_archive_title() ) ) ) ); // phpcs:ignore
?>
<section class="wrap pblock"><div class="grid grid-3"><?php while ( have_posts() ) { the_post(); echo solid_card( get_post(), 'std', array( 'dek' => true, 'kick' => 'section' ) ); } // phpcs:ignore ?></div><?php the_posts_pagination(); ?></section>
<?php
get_footer();
