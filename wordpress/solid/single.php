<?php
/**
 * A story.
 */
get_header();
while ( have_posts() ) :
	the_post();
	$p      = get_post();
	$sec    = solid_post_section( $p );
	$sub    = solid_post_sub( $p );
	$writer = solid_writer( $p );
	$wname  = solid_writer_name( $p );
	$wrole  = $writer ? solid_writer_role( $writer ) : '';
	$wurl   = $writer ? get_permalink( $writer ) : '';
	$wimg   = $writer ? get_post_thumbnail_id( $writer ) : 0;
	$caption = get_the_post_thumbnail_caption( $p );

	// Read next: the rest of this section, then the newest from elsewhere.
	$related = solid_latest( array( 'cat' => $sec->term_id, 'post__not_in' => array( $p->ID ), 'posts_per_page' => 3 ) );
	if ( count( $related ) < 3 ) {
		$related = array_merge( $related, solid_latest( array( 'category__not_in' => array( $sec->term_id ), 'posts_per_page' => 3 - count( $related ) ) ) );
	}
	$share = '<div class="share"><span class="sr">Share</span><a href="' . esc_url( 'https://x.com/intent/post?url=' . rawurlencode( get_permalink() ) . '&text=' . rawurlencode( get_the_title() ) ) . '" aria-label="Share on X" target="_blank" rel="noopener">' . solid_icon( 'x' ) . '</a><a href="' . esc_url( 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( get_permalink() ) ) . '" aria-label="Share on LinkedIn" target="_blank" rel="noopener">' . solid_icon( 'in' ) . '</a><a href="' . esc_url( get_permalink() ) . '" aria-label="Copy link" data-copy-link>' . solid_icon( 'link' ) . '</a><a href="#" aria-label="Save">' . solid_icon( 'mark' ) . '</a></div>';
	$men   = solid_section_by_slug( 'solid-men' );
	?>
<div class="progress" id="progress" aria-hidden="true"></div>
<article class="art">
<header class="art__head"><div class="art__headin"><p class="crumb"><a href="<?php echo esc_url( get_term_link( $sec ) ); ?>"><?php echo solid_e( $sec->name ); ?></a><span>/</span><?php echo solid_e( $sub ? $sub->name : '' ); ?></p>
<h1 class="art__t"><?php echo solid_e( get_the_title() ); ?></h1><p class="art__dek"><?php echo solid_e( solid_dek( $p ) ); ?></p>
<div class="byline"><span class="byline__av"><?php echo solid_img( $wimg, 'thumbnail', '' ); // phpcs:ignore ?></span><div class="byline__who"><?php echo $wurl ? '<a href="' . esc_url( $wurl ) . '">By ' . solid_e( $wname ) . '</a>' : '<span>By ' . solid_e( $wname ) . '</span>'; // phpcs:ignore ?><span><?php echo solid_e( $wrole ? $wrole . ' · ' : '' ) . esc_html( get_the_date( 'j M Y' ) ) . ' · ' . (int) solid_mins( $p ); ?> min read</span></div><?php echo $share; // phpcs:ignore ?></div></div></header>
<figure class="art__fig"><?php echo solid_thumb( $p, 'solid-hero', '', true ); // phpcs:ignore ?><figcaption><?php echo solid_e( $caption ? $caption : 'Photograph for SOLID' ); ?></figcaption></figure>
<div class="art__body prose"><?php the_content(); ?></div>
<footer class="art__foot"><ul class="chips"><li><a href="<?php echo esc_url( get_term_link( $sec ) ); ?>"><?php echo solid_e( $sec->name ); ?></a></li><?php if ( $sub && $sub->parent ) : ?><li><a href="<?php echo esc_url( solid_sub_link( $sub ) ); ?>"><?php echo solid_e( $sub->name ); ?></a></li><?php endif; ?><?php if ( $men && $sec->term_id !== $men->term_id ) : ?><li><a href="<?php echo esc_url( get_term_link( $men ) ); ?>"><?php echo solid_e( $men->name ); ?></a></li><?php endif; ?></ul>
<div class="authorbox"><span class="byline__av"><?php echo solid_img( $wimg, 'thumbnail', '' ); // phpcs:ignore ?></span><div><p class="kicker">Written by</p><h3><?php echo $wurl ? '<a href="' . esc_url( $wurl ) . '">' . solid_e( $wname ) . '</a>' : solid_e( $wname ); // phpcs:ignore ?></h3><p><?php echo solid_e( $writer && $writer->post_excerpt ? $writer->post_excerpt : ( $wrole ? $wrole . ' at SOLID. ' : '' ) . 'Writes about ambition, work and the business of living well.' ); ?></p></div></div></footer>
</article>
<section class="band"><div class="wrap"><?php echo solid_sec_head( 'Read next', get_term_link( $sec ), 'More ' . solid_e( $sec->name ) ); // phpcs:ignore ?><div class="grid grid-3"><?php foreach ( $related as $r ) { echo solid_card( $r, 'std', array( 'dek' => true, 'kick' => 'section' ) ); } // phpcs:ignore ?></div></div></section>
	<?php
	echo solid_newsletter(); // phpcs:ignore
endwhile;
get_footer();
