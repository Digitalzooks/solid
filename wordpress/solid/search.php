<?php
/**
 * WordPress search results (?s=).
 */
get_header();
global $wp_query;
$n = (int) $wp_query->found_posts;
echo solid_phead( // phpcs:ignore
	array(
		'crumb' => 'Search',
		'title' => 'Search',
		'lede'  => $n ? sprintf( '%d %s for “%s”.', $n, 1 === $n ? 'story' : 'stories', esc_html( get_search_query() ) ) : sprintf( 'No stories match “%s”.', esc_html( get_search_query() ) ),
		'extra' => '<form class="search__field search__field--page" role="search" action="' . esc_url( home_url( '/' ) ) . '">' . solid_icon( 'search' ) . '<input type="search" name="s" data-search-input placeholder="Stories, people, topics…" aria-label="Search SOLID" value="' . esc_attr( get_search_query() ) . '"></form><ul class="search__res" data-search-results></ul>',
	)
);
?>
<section class="wrap pblock"><div class="grid grid-4"><?php while ( have_posts() ) { the_post(); echo 'post' === get_post_type() ? solid_card( get_post(), 'std', array( 'kick' => 'section' ) ) : ''; } // phpcs:ignore ?></div><?php the_posts_pagination(); ?></section>
<?php
get_footer();
