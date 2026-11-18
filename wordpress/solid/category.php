<?php
/**
 * A section (top-level category): lead story, every other story with topic filters, and more from SOLID.
 */
get_header();
$sec   = get_queried_object();
$subs  = solid_subs( $sec );
$posts = $GLOBALS['wp_query']->posts;
$lead  = array_shift( $posts );
$line  = solid_section_line( $sec );

$filter = '<ul class="chips" data-filter-bar aria-label="Filter ' . esc_attr( $sec->name ) . ' by topic"><li><a href="#" data-filter="" aria-current="true">All</a></li>';
foreach ( $subs as $x ) {
	$k       = solid_slugify( $x->name );
	$filter .= '<li><a href="#' . esc_attr( $k ) . '" data-filter="' . esc_attr( $k ) . '">' . solid_e( $x->name ) . '</a></li>';
}
$filter .= '</ul>';

echo solid_phead( // phpcs:ignore
	array(
		'crumb' => solid_e( $sec->name ),
		'title' => solid_e( $sec->name ),
		'lede'  => solid_e( trim( $line . ' ' . $sec->description ) ),
		'extra' => $filter,
	)
);
$more = solid_latest( array( 'category__not_in' => array( $sec->term_id ), 'posts_per_page' => 4 ) );
?>
<div data-filter-scope><p class="wrap filter-note" data-filter-note aria-live="polite"></p>
<?php if ( $lead ) : ?>
<section class="wrap pblock" data-filter-group><?php echo solid_card( $lead, 'lead', array( 'eager' => true ) ); // phpcs:ignore ?></section>
<?php endif; ?>
<section class="wrap pblock" data-filter-group><div class="grid grid-3"><?php foreach ( $posts as $p ) { echo solid_card( $p, 'std', array( 'dek' => true ) ); } // phpcs:ignore ?></div></section>
<section class="wrap pblock filter-empty" data-filter-empty hidden><p>There are no <b data-filter-name></b> stories yet.</p><a class="btn btn--ghost" href="#" data-filter="">See all <?php echo solid_e( $sec->name ); ?> <?php echo solid_icon( 'arrow' ); // phpcs:ignore ?></a></section></div>
<section class="band"><div class="wrap"><?php echo solid_sec_head( 'More from SOLID', home_url( '/' ), 'Home' ); // phpcs:ignore ?><div class="grid grid-4"><?php foreach ( $more as $p ) { echo solid_card( $p, 'std', array( 'kick' => 'section' ) ); } // phpcs:ignore ?></div></div></section>
<?php
echo solid_newsletter(); // phpcs:ignore
get_footer();
