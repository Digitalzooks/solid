<?php
/**
 * A writer's page: profile and their stories.
 */
get_header();
$w     = get_queried_object();
$role  = solid_writer_role( $w );
$first = strtok( $w->post_title, ' ' );
$own   = solid_latest(
	array(
		'posts_per_page' => 24,
		'meta_key'       => 'solid_writer', // phpcs:ignore
		'meta_value'     => $w->ID, // phpcs:ignore
	)
);
echo solid_phead( // phpcs:ignore
	array(
		'cls'   => 'phead--author',
		'crumb' => 'Authors',
		'title' => solid_e( $w->post_title ),
		'lede'  => solid_e( $w->post_excerpt ? $w->post_excerpt : $role . ' at SOLID. Writes about ambition, work and the business of living well.' ),
		'extra' => '<span class="phead__av">' . solid_thumb( $w, 'medium', '' ) . '</span>',
	)
);
?>
<section class="wrap pblock"><?php echo solid_sec_head( 'Stories by ' . solid_e( $first ) ); // phpcs:ignore ?><div class="grid grid-3"><?php foreach ( $own as $p ) { echo solid_card( $p, 'std', array( 'dek' => true, 'kick' => 'section' ) ); } // phpcs:ignore ?></div></section>
<?php
echo solid_newsletter(); // phpcs:ignore
get_footer();
