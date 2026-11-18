<?php
/**
 * Template Name: SOLID Search
 * Instant search across every story, then the latest stories.
 */
get_header();
echo solid_phead( // phpcs:ignore
	array(
		'crumb' => 'Search',
		'title' => 'Search',
		'lede'  => 'Find stories, people and topics across SOLID.',
		'extra' => '<div class="search__field search__field--page">' . solid_icon( 'search' ) . '<input type="search" data-search-input placeholder="Stories, people, topics…" aria-label="Search SOLID" value="' . esc_attr( get_search_query() ) . '"></div><ul class="search__res" data-search-results></ul>',
	)
);
$latest = solid_latest( array( 'posts_per_page' => 8, 'offset' => 1 ) );
?>
<section class="wrap pblock"><?php echo solid_sec_head( 'Latest stories' ); // phpcs:ignore ?><div class="grid grid-4"><?php foreach ( $latest as $p ) { echo solid_card( $p, 'std', array( 'kick' => 'section' ) ); } // phpcs:ignore ?></div></section>
<?php
get_footer();
