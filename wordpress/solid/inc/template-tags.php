<?php
/**
 * SOLID rendering helpers. Shared by the theme templates and the SOLID Core Elementor widgets,
 * so every card, header and block uses the same markup as the original Solid One design.
 */
defined( 'ABSPATH' ) || exit;

/* ───────── Small utilities ───────── */

function solid_e( $s ) {
	return esc_html( (string) $s );
}

function solid_icon( $name ) {
	static $i = null;
	if ( null === $i ) {
		$s = function ( $d, $o = '' ) {
			return '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" ' . $o . '>' . $d . '</svg>';
		};
		$i = array(
			'search' => $s( '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>' ),
			'sun'    => $s( '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>', 'class="i-sun"' ),
			'moon'   => $s( '<path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/>', 'class="i-moon"' ),
			'menu'   => $s( '<path d="M3 7h18M3 12h18M3 17h18"/>' ),
			'close'  => $s( '<path d="M5 5l14 14M19 5 5 19"/>' ),
			'arrow'  => $s( '<path d="M5 12h14M13 6l6 6-6 6"/>', 'class="i-arrow"' ),
			'left'   => $s( '<path d="M19 12H5M11 6l-6 6 6 6"/>' ),
			'play'   => '<svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l11-6.5a1 1 0 0 0 0-1.72l-11-6.5A1 1 0 0 0 8 5.5Z"/></svg>',
			'mic'    => $s( '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>' ),
			'x'      => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M17.5 3h3.1l-6.8 7.8L22 21h-6.3l-4.9-6.4L5.2 21H2.1l7.3-8.3L2 3h6.4l4.4 5.9L17.5 3Zm-1.1 16.1h1.7L7.7 4.8H5.9l10.5 14.3Z"/></svg>',
			'ig'     => $s( '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".6" fill="currentColor"/>' ),
			'yt'     => $s( '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10 9.5 5 2.5-5 2.5v-5Z" fill="currentColor"/>' ),
			'in'     => '<svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5ZM3 9.75h4v11H3v-11Zm6.5 0h3.8v1.6h.06c.53-.95 1.83-1.9 3.76-1.9 4.02 0 4.88 2.5 4.88 5.9v5.4h-4v-4.8c0-1.3-.03-3-1.9-3s-2.2 1.4-2.2 2.9v4.9h-4v-11Z"/></svg>',
			'link'   => $s( '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>' ),
			'mark'   => $s( '<path d="M6 3h12v18l-6-4.5L6 21V3Z"/>' ),
		);
	}
	return $i[ $name ] ?? '';
}

/** Slug the same way the static site did, for #sub anchors. */
function solid_slugify( $s ) {
	$s = strtolower( html_entity_decode( (string) $s, ENT_QUOTES, 'UTF-8' ) );
	$s = str_replace( array( '₦', "'", '’' ), '', $s );
	$s = str_replace( '&', 'and', $s );
	$s = preg_replace( '/[^a-z0-9]+/', '-', $s );
	return trim( $s, '-' );
}

/* ───────── Logo ───────── */

function solid_logo_data() {
	static $lg = null;
	if ( null === $lg ) {
		$lg = json_decode( (string) file_get_contents( get_theme_file_path( 'assets/logo.json' ) ), true );
	}
	return $lg;
}

function solid_logo_defs() {
	$lg  = solid_logo_data();
	$sym = function ( $id, $g ) {
		return '<symbol id="' . $id . '" viewBox="0 0 ' . $g['w'] . ' ' . $g['h'] . '"><path fill="currentColor" fill-rule="evenodd" d="' . $g['ink'] . '"/><path style="fill:var(--red)" fill-rule="evenodd" d="' . $g['red'] . '"/></symbol>';
	};
	return '<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">' . $sym( 'lg-the', $lg['the'] ) . $sym( 'lg-full', $lg['full'] ) . '</svg>';
}

function solid_logo_svg( $kind = 'the', $cls = '' ) {
	$g = solid_logo_data()[ $kind ];
	return '<svg class="logo__svg' . ( $cls ? ' ' . $cls : '' ) . '" viewBox="0 0 ' . $g['w'] . ' ' . $g['h'] . '" aria-hidden="true" focusable="false"><use href="#lg-' . $kind . '"/></svg>';
}

function solid_logo( $full = false ) {
	$inner = $full ? solid_logo_svg( 'full', 'logo--full' ) . solid_logo_svg( 'the', 'logo--short' ) : solid_logo_svg( 'the' );
	return '<a class="logo" href="' . esc_url( home_url( '/' ) ) . '" aria-label="The SOLID Mag — home">' . $inner . '</a>';
}

function solid_theme_toggle() {
	return '<button class="icon-btn theme-toggle" type="button" data-theme-toggle aria-label="Switch between light and dark mode" title="Light / dark mode">' . solid_icon( 'sun' ) . solid_icon( 'moon' ) . '</button>';
}

/* ───────── Content model ───────── */

/** Top-level categories (the magazine's sections), in editorial order. */
function solid_sections() {
	static $list = null;
	if ( null === $list ) {
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'parent'     => 0,
				'hide_empty' => false,
				'exclude'    => array( (int) get_option( 'default_category' ) ),
			)
		);
		$list  = is_wp_error( $terms ) ? array() : $terms;
		usort( $list, 'solid_term_order_cmp' );
	}
	return $list;
}

function solid_term_order_cmp( $a, $b ) {
	return (int) get_term_meta( $a->term_id, 'solid_order', true ) <=> (int) get_term_meta( $b->term_id, 'solid_order', true );
}

/** Topics (child categories) of a section, in editorial order. */
function solid_subs( $section ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => $section->term_id,
			'hide_empty' => false,
		)
	);
	$terms = is_wp_error( $terms ) ? array() : $terms;
	usort( $terms, 'solid_term_order_cmp' );
	return $terms;
}

function solid_section_by_slug( $slug ) {
	$t = get_term_by( 'slug', $slug, 'category' );
	return $t ?: null;
}

function solid_section_line( $term ) {
	return (string) get_term_meta( $term->term_id, 'solid_line', true );
}

/** The section (top-level category) a post belongs to. */
function solid_post_section( $post ) {
	$sub = solid_post_sub( $post );
	if ( $sub && $sub->parent ) {
		return get_term( $sub->parent, 'category' );
	}
	if ( $sub ) {
		return $sub;
	}
	return get_term( (int) get_option( 'default_category' ), 'category' );
}

/** The topic (child category) a post belongs to, or its section if it has none. */
function solid_post_sub( $post ) {
	$cats = get_the_category( $post->ID );
	if ( ! $cats ) {
		return null;
	}
	foreach ( $cats as $c ) {
		if ( $c->parent ) {
			return $c;
		}
	}
	return $cats[0];
}

function solid_mins( $post ) {
	$m = (int) get_post_meta( $post->ID, 'solid_mins', true );
	if ( ! $m ) {
		$m = max( 1, (int) round( str_word_count( wp_strip_all_tags( $post->post_content ) ) / 220 ) );
	}
	return $m;
}

function solid_dek( $post ) {
	return has_excerpt( $post ) ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 28 );
}

/** The SOLID writer credited on a post (a "Writer" entry from SOLID Core). */
function solid_writer( $post ) {
	$id = (int) get_post_meta( $post->ID, 'solid_writer', true );
	$w  = $id ? get_post( $id ) : null;
	return ( $w && 'publish' === $w->post_status ) ? $w : null;
}

function solid_writer_name( $post ) {
	$w = solid_writer( $post );
	return $w ? $w->post_title : get_the_author_meta( 'display_name', $post->post_author );
}

function solid_writer_role( $w ) {
	return $w ? (string) get_post_meta( $w->ID, 'solid_role', true ) : '';
}

/* ───────── Links ───────── */

function solid_sub_link( $sub ) {
	if ( ! $sub->parent ) {
		return get_term_link( $sub );
	}
	return trailingslashit( get_term_link( (int) $sub->parent, 'category' ) ) . '#' . solid_slugify( $sub->name );
}

/** Link to a page by its path, e.g. solid_page_url('about', 'contact'). */
function solid_page_url( $path, $hash = '' ) {
	return home_url( '/' . trim( $path, '/' ) . '/' ) . ( $hash ? '#' . $hash : '' );
}

/* ───────── Images ───────── */

/** An image tag for an attachment. $size picks the largest file needed; srcset covers the rest. */
function solid_img( $att_id, $size = 'large', $alt = null, $eager = false ) {
	if ( ! $att_id ) {
		return '';
	}
	$attr = array( 'decoding' => 'async', 'class' => '' );
	if ( $eager ) {
		$attr['fetchpriority'] = 'high';
		$attr['loading']       = false;
	} else {
		$attr['loading'] = 'lazy';
	}
	if ( null !== $alt ) {
		$attr['alt'] = $alt;
	}
	$html = wp_get_attachment_image( $att_id, $size, false, $attr );
	return str_replace( ' class=""', '', $html );
}

function solid_thumb( $post, $size = 'large', $alt = null, $eager = false ) {
	return solid_img( get_post_thumbnail_id( $post ), $size, $alt, $eager );
}

/* ───────── Components ───────── */

function solid_date( $post, $fmt = 'j M Y' ) {
	return '<time datetime="' . esc_attr( get_the_date( 'Y-m-d', $post ) ) . '">' . esc_html( get_the_date( $fmt, $post ) ) . '</time>';
}

/**
 * A story card. Variants: std, row, lead, over, portrait, wide, mini.
 * Options: dek (bool), kick ('section' or topic), meta (false hides byline and length), n, cls, eager.
 */
function solid_card( $post, $v = 'std', $o = array() ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}
	$sec   = solid_post_section( $post );
	$sub   = solid_post_sub( $post );
	$dek   = $o['dek'] ?? in_array( $v, array( 'lead', 'wide', 'over' ), true );
	$kick  = ( isset( $o['kick'] ) && 'section' === $o['kick'] ) ? $sec->name : ( $sub ? $sub->name : $sec->name );
	$sizes = array( 'row' => 'medium', 'mini' => 'medium', 'lead' => 'solid-hero', 'wide' => 'solid-hero' );
	$meta  = $o['meta'] ?? true;
	$url   = get_permalink( $post );

	$h  = '<article class="card card--' . $v . ( ! empty( $o['cls'] ) ? ' ' . $o['cls'] : '' ) . '" data-sub="' . esc_attr( solid_slugify( $sub ? $sub->name : '' ) ) . '">' . "\n";
	$h .= ! empty( $o['n'] ) ? '<span class="card__num">' . sprintf( '%02d', (int) $o['n'] ) . '</span>' : '';
	$h .= '<div class="card__media">' . solid_thumb( $post, $sizes[ $v ] ?? 'large', '', ! empty( $o['eager'] ) ) . '</div>' . "\n";
	$h .= '<div class="card__body"><a class="kicker" href="' . esc_url( get_term_link( $sec ) ) . '">' . solid_e( $kick ) . '</a>' . "\n";
	$h .= '<h3 class="card__title"><a href="' . esc_url( $url ) . '">' . solid_e( get_the_title( $post ) ) . '</a></h3>' . "\n";
	$h .= $dek ? '<p class="card__dek">' . solid_e( solid_dek( $post ) ) . '</p>' . "\n" : '';
	$h .= '<p class="meta">' . ( $meta ? '<span>By ' . solid_e( solid_writer_name( $post ) ) . '</span>' : '' ) . solid_date( $post ) . ( $meta ? '<span>' . solid_mins( $post ) . ' min read</span>' : '' ) . '</p></div></article>';
	return $h;
}

function solid_sec_head( $title, $href = '', $label = 'View all', $kicker = '' ) {
	return '<div class="sec-head">' . ( $kicker ? '<p class="kicker">' . $kicker . '</p>' : '' ) . '<h2 class="sec-title">' . $title . '</h2>' . ( $href ? '<a class="more" href="' . esc_url( $href ) . '">' . $label . ' ' . solid_icon( 'arrow' ) . '</a>' : '' ) . '</div>';
}

/** Conversation fields from a SOLID Core "Conversation" entry. */
function solid_conv( $p ) {
	$p = get_post( $p );
	return array(
		'post'  => $p,
		'title' => get_the_title( $p ),
		'guest' => (string) get_post_meta( $p->ID, 'solid_guest', true ),
		'role'  => (string) get_post_meta( $p->ID, 'solid_role', true ),
		'kind'  => (string) get_post_meta( $p->ID, 'solid_kind', true ),
		'len'   => (string) get_post_meta( $p->ID, 'solid_length', true ),
		'ep'    => (string) get_post_meta( $p->ID, 'solid_episode', true ),
		'dek'   => $p->post_excerpt,
		'url'   => ( $u = get_post_meta( $p->ID, 'solid_url', true ) ) ? $u : solid_page_url( 'conversations' ),
	);
}

function solid_conv_is_audio( $v ) {
	return 0 === strpos( $v['kind'], 'Podcast' );
}

function solid_conv_card( $p, $cls = '', $size = 'large' ) {
	$v = solid_conv( $p );
	return '<article class="conv ' . $cls . '"><a class="conv__media" href="' . esc_url( $v['url'] ) . '">' . solid_thumb( $v['post'], $size, '' ) . '<span class="play">' . solid_icon( solid_conv_is_audio( $v ) ? 'mic' : 'play' ) . '</span><span class="conv__len">' . solid_e( $v['len'] ) . '</span></a>' . "\n"
		. '<div class="conv__body"><span class="kicker">' . solid_e( $v['kind'] ) . ' · Ep. ' . solid_e( $v['ep'] ) . '</span><h3 class="conv__title"><a href="' . esc_url( $v['url'] ) . '">' . solid_e( $v['title'] ) . '</a></h3><p class="conv__guest">with ' . solid_e( $v['guest'] ) . ', ' . solid_e( strtolower( $v['role'] ) ) . '</p><p class="conv__dek">' . solid_e( $v['dek'] ) . '</p></div></article>';
}

/** A person card: an honouree (category + role) or a writer (role, links to their page). */
function solid_person( $p ) {
	$p      = get_post( $p );
	$cat    = (string) get_post_meta( $p->ID, 'solid_category', true );
	$role   = (string) get_post_meta( $p->ID, 'solid_role', true );
	$writer = 'solid_writer' === $p->post_type;
	$name   = $writer ? '<a href="' . esc_url( get_permalink( $p ) ) . '">' . solid_e( $p->post_title ) . '</a>' : solid_e( $p->post_title );
	return '<article class="person"><div class="person__media">' . solid_thumb( $p, 'large', '' ) . '</div><div class="person__body"><span class="kicker">' . solid_e( $cat ? $cat : $role ) . '</span><h3 class="person__name">' . $name . '</h3>' . ( $cat ? '<p class="person__role">' . solid_e( $role ) . '</p>' : '' ) . '</div></article>';
}

/** The newsletter sign-up band. Marks the page so the mobile menu can link to it. */
function solid_newsletter( $o = array() ) {
	$GLOBALS['solid_has_newsletter'] = true;
	$o = wp_parse_args(
		$o,
		array(
			'kicker' => 'The SOLID Brief',
			'title'  => 'Stay ahead.',
			'text'   => 'The week’s sharpest stories on business, money and living well, delivered every Sunday morning.',
			'button' => 'Subscribe',
			'note'   => 'No spam. Unsubscribe at any time.',
		)
	);
	return '<section class="nl" id="newsletter"><div class="wrap nl__in"><div class="nl__copy"><p class="kicker">' . solid_e( $o['kicker'] ) . '</p><h2 class="nl__t">' . solid_e( $o['title'] ) . '</h2><p class="nl__d">' . solid_e( $o['text'] ) . '</p></div>' . "\n"
		. '<form class="nl__form" data-newsletter><label class="sr" for="nl-email">Email address</label><input id="nl-email" type="email" required placeholder="Your email address" autocomplete="email"><button class="btn btn--red" type="submit">' . solid_e( $o['button'] ) . '</button><small>' . solid_e( $o['note'] ) . '</small></form></div></section>';
}

function solid_social() {
	$links = array(
		'x'  => array( 'X', get_theme_mod( 'solid_x', '#' ) ),
		'ig' => array( 'Instagram', get_theme_mod( 'solid_instagram', '#' ) ),
		'yt' => array( 'YouTube', get_theme_mod( 'solid_youtube', '#' ) ),
		'in' => array( 'LinkedIn', get_theme_mod( 'solid_linkedin', '#' ) ),
	);
	$h = '<ul class="social">';
	foreach ( $links as $k => $l ) {
		$h .= '<li><a href="' . esc_url( $l[1] ) . '" aria-label="' . $l[0] . '">' . solid_icon( $k ) . '</a></li>';
	}
	return $h . '</ul>';
}

/** Page header: breadcrumb, title, lede, chips and extras. $title and $crumb are trusted HTML. */
function solid_phead( $a ) {
	$a = wp_parse_args( $a, array( 'crumb' => '', 'title' => '', 'lede' => '', 'chips' => array(), 'cls' => '', 'extra' => '' ) );
	$h = '<header class="phead ' . esc_attr( $a['cls'] ) . '"><div class="wrap phead__in"><p class="crumb"><a href="' . esc_url( home_url( '/' ) ) . '">Home</a><span>/</span>' . $a['crumb'] . '</p><h1 class="phead__t">' . $a['title'] . '</h1>';
	$h .= $a['lede'] ? '<p class="phead__l">' . $a['lede'] . '</p>' : '';
	if ( $a['chips'] ) {
		$h .= '<ul class="chips">';
		foreach ( $a['chips'] as $c ) {
			$h .= '<li><a href="' . esc_url( $c[1] ) . '">' . solid_e( $c[0] ) . '</a></li>';
		}
		$h .= '</ul>';
	}
	return $h . $a['extra'] . '</div></header>';
}

/** Posts helper: newest published stories, optionally in one section. */
function solid_latest( $args = array() ) {
	return get_posts(
		wp_parse_args(
			$args,
			array(
				'post_type'           => 'post',
				'posts_per_page'      => 8,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		)
	);
}

/* ───────── Site navigation (built from the sections and the franchise pages) ───────── */

function solid_franchises() {
	return array(
		array( 'slug' => 'the-solid-100', 'name' => 'The SOLID 100', 'subs' => array( 'About the Recognition', 'Recognition Categories', 'Selection Criteria', 'Honouree Profiles', 'Previous Editions' ) ),
		array( 'slug' => 'conversations', 'name' => 'SOLID Conversations', 'subs' => array( 'Video Interviews', 'Podcast Episodes', 'Interview Transcripts' ) ),
	);
}

function solid_company() {
	return array(
		array( 'slug' => 'about', 'name' => 'About SOLID', 'subs' => array( 'Our Story', 'Mission & Vision', 'Editorial Team', 'Editorial Policy', 'Contact' ) ),
		array( 'slug' => 'advertise', 'name' => 'Advertise with SOLID', 'subs' => array( 'Media Kit', 'Advertising Opportunities', 'Brand Partnerships', 'Sponsored Content Enquiries' ) ),
	);
}

function solid_legal() {
	return array(
		array( 'slug' => 'privacy-policy', 'name' => 'Privacy Policy' ),
		array( 'slug' => 'terms', 'name' => 'Terms & Conditions' ),
		array( 'slug' => 'corrections-policy', 'name' => 'Corrections Policy' ),
		array( 'slug' => 'editorial-policy', 'name' => 'Editorial Policy' ),
	);
}

/** Which nav item is current: a section slug or a page slug. */
function solid_current() {
	if ( is_category() ) {
		$t = get_queried_object();
		while ( $t && $t->parent ) {
			$t = get_term( $t->parent, 'category' );
		}
		return $t ? $t->slug : '';
	}
	if ( is_singular( 'post' ) ) {
		$s = solid_post_section( get_queried_object() );
		return $s ? $s->slug : '';
	}
	if ( is_page() ) {
		return get_post_field( 'post_name', get_queried_object_id() );
	}
	return '';
}

function solid_nav_items() {
	$cur = solid_current();
	$h   = '';
	foreach ( solid_sections() as $s ) {
		$h .= '<li class="nav__i' . ( $cur === $s->slug ? ' is-on' : '' ) . '"><a href="' . esc_url( get_term_link( $s ) ) . '">' . solid_e( $s->name ) . '</a>';
		$subs = solid_subs( $s );
		if ( $subs ) {
			$h .= '<ul class="nav__sub">';
			foreach ( $subs as $x ) {
				$h .= '<li><a href="' . esc_url( solid_sub_link( $x ) ) . '">' . solid_e( $x->name ) . '</a></li>';
			}
			$h .= '</ul>';
		}
		$h .= '</li>';
	}
	foreach ( array( array( 'the-solid-100', 'The SOLID 100' ), array( 'conversations', 'Conversations' ) ) as $p ) {
		$h .= '<li class="nav__i' . ( $cur === $p[0] ? ' is-on' : '' ) . '"><a href="' . esc_url( solid_page_url( $p[0] ) ) . '">' . $p[1] . '</a></li>';
	}
	return $h;
}

function solid_menu_panel() {
	$nl  = ! empty( $GLOBALS['solid_has_newsletter'] ) ? '#newsletter' : home_url( '/#newsletter' );
	$col = function ( $name, $href, $items, $first = '', $mobile_hide = array() ) {
		$h = '<div class="menu__col"><a class="menu__h" href="' . esc_url( $href ) . '">' . solid_e( $name ) . '</a><ul>' . $first;
		foreach ( $items as $i ) {
			$h .= '<li' . ( in_array( $i[0], $mobile_hide, true ) ? ' class="menu__desk"' : '' ) . '><a href="' . esc_url( $i[1] ) . '">' . solid_e( $i[0] ) . '</a></li>';
		}
		return $h . '</ul></div>';
	};
	$h = '<div class="menu" id="menu" aria-hidden="true" role="dialog" aria-label="All sections"><div class="menu__in wrap">' . "\n"
		. '<div class="menu__bar">' . solid_logo( false ) . '<div class="menu__tools">' . solid_theme_toggle() . '<button class="icon-btn" type="button" data-menu-close aria-label="Close menu">' . solid_icon( 'close' ) . '</button></div></div>' . "\n"
		. '<div class="menu__grid">';
	foreach ( solid_sections() as $s ) {
		// Phones: About SOLID leads SOLID Men, and two of its topics are left out (the lower group of menus is hidden on phones).
		$men   = 'solid-men' === $s->slug;
		$first = $men ? '<li class="menu__mob"><a href="' . esc_url( solid_page_url( 'about' ) ) . '">About SOLID</a></li>' : '';
		$h    .= $col( $s->name, get_term_link( $s ), array_map( fn( $x ) => array( $x->name, solid_sub_link( $x ) ), solid_subs( $s ) ), $first, $men ? array( 'Milestones', 'Rising Men' ) : array() );
	}
	$h .= '</div>' . "\n" . '<div class="menu__grid menu__grid--b">';
	foreach ( array_merge( solid_franchises(), solid_company() ) as $s ) {
		$h .= $col( $s['name'], solid_page_url( $s['slug'] ), array_map( fn( $x ) => array( $x, solid_page_url( $s['slug'], solid_slugify( $x ) ) ), $s['subs'] ) );
	}
	$h .= '<div class="menu__col"><span class="menu__h">More</span><ul><li><a href="' . esc_url( solid_page_url( 'search' ) ) . '">Search</a></li>';
	foreach ( solid_legal() as $l ) {
		$h .= '<li><a href="' . esc_url( solid_page_url( $l['slug'] ) ) . '">' . solid_e( $l['name'] ) . '</a></li>';
	}
	$h .= '</ul></div></div>' . "\n"
		. '<div class="menu__sub"><p class="kicker">Free newsletter</p><p class="menu__sub-t">The SOLID Brief</p><p class="menu__sub-d">The week’s sharpest stories on business, money and living well, in your inbox every Sunday morning.</p><a class="btn btn--red" href="' . esc_url( $nl ) . '">Get the newsletter</a><small>Free. No spam. Unsubscribe at any time.</small></div>' . "\n"
		. '</div></div>';
	return $h;
}

function solid_search_panel() {
	return '<div class="search" id="search" aria-hidden="true" role="dialog" aria-label="Search"><div class="search__in"><button class="icon-btn search__x" type="button" data-search-close aria-label="Close search">' . solid_icon( 'close' ) . '</button>' . "\n"
		. '<label class="kicker" for="q">Search SOLID</label><div class="search__field">' . solid_icon( 'search' ) . '<input id="q" type="search" data-search-input placeholder="Stories, people, topics…" autocomplete="off"></div><ul class="search__res" data-search-results></ul></div></div>';
}

function solid_footer() {
	$col = function ( $h, $items ) {
		$o = '<div class="foot__col"><h3>' . $h . '</h3><ul>';
		foreach ( $items as $i ) {
			$o .= '<li><a href="' . esc_url( $i[1] ) . '">' . solid_e( $i[0] ) . '</a></li>';
		}
		return $o . '</ul></div>';
	};
	$secs = array_map( fn( $s ) => array( $s->name, get_term_link( $s ) ), solid_sections() );
	$men  = solid_section_by_slug( 'solid-men' );
	$fr   = array_map( fn( $s ) => array( $s['name'], solid_page_url( $s['slug'] ) ), solid_franchises() );
	if ( $men ) {
		$fr[] = array( 'Cover Stories', get_term_link( $men ) . '#cover-stories' );
		$fr[] = array( 'Men to Watch', get_term_link( $men ) . '#men-to-watch' );
	}
	$fr[] = array( 'Search', solid_page_url( 'search' ) );

	$h  = '<footer class="foot"><div class="wrap">' . "\n";
	$h .= '<div class="foot__top"><div class="foot__brand">' . solid_logo() . '<p>' . solid_e( get_theme_mod( 'solid_footer_text', 'The magazine for Nigerian men who want more — in business, in life and in themselves.' ) ) . '</p>' . solid_social() . '</div>' . "\n";
	$h .= '<nav class="foot__cols" aria-label="Footer">' . "\n";
	$h .= $col( 'Sections', array_slice( $secs, 0, 5 ) ) . "\n" . $col( '&nbsp;', array_slice( $secs, 5 ) ) . "\n" . $col( 'Franchises', $fr ) . "\n";
	$h .= $col( 'SOLID', array( array( 'Our Story', solid_page_url( 'about' ) ), array( 'Editorial Team', solid_page_url( 'about', 'editorial-team' ) ), array( 'Advertise', solid_page_url( 'advertise' ) ), array( 'Media Kit', solid_page_url( 'advertise', 'media-kit' ) ), array( 'Contact', solid_page_url( 'about', 'contact' ) ) ) ) . "\n";
	$h .= '</nav></div>' . "\n";
	$h .= '<div class="foot__bot"><span>© ' . esc_html( wp_date( 'Y' ) ) . ' SOLID Magazine. All rights reserved.</span><ul class="foot__legal">';
	foreach ( solid_legal() as $l ) {
		$h .= '<li><a href="' . esc_url( solid_page_url( $l['slug'] ) ) . '">' . solid_e( $l['name'] ) . '</a></li>';
	}
	$h .= '</ul><span class="foot__tag">#SOLIDMen</span></div>' . "\n" . '</div></footer>';
	return $h;
}
