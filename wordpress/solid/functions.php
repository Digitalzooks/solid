<?php
/**
 * SOLID theme. The Solid One design ("The Editorial") for WordPress, built to work with Elementor
 * and the SOLID Core plugin (content types and Elementor widgets).
 */
defined( 'ABSPATH' ) || exit;

define( 'SOLID_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );
define( 'SOLID_DEFAULT_MODE', 'light' );
define( 'SOLID_FONTS', 'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..900;1,9..144,400..900&family=Inter:wght@400;500;600;700&family=Source+Serif+4:ital,opsz,wght@0,8..60,400..600;1,8..60,400&display=swap' );

require get_theme_file_path( 'inc/template-tags.php' );
require get_theme_file_path( 'inc/white-label.php' );

/* ───────── Setup ───────── */

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'responsive-embeds' );
		add_image_size( 'solid-hero', 1680, 1680 );
	}
);

// Wider srcset range so the 1680px hero image is offered to large screens.
add_filter( 'max_srcset_image_width', fn() => 2048 );

/* ───────── Assets ───────── */

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'solid-fonts', SOLID_FONTS, array(), null );
		$deps = wp_style_is( 'elementor-frontend', 'registered' ) ? array( 'elementor-frontend' ) : array();
		$v = fn( $f ) => SOLID_VERSION . '.' . filemtime( get_theme_file_path( $f ) );
		wp_enqueue_style( 'solid', get_theme_file_uri( 'assets/solid.css' ), $deps, $v( 'assets/solid.css' ) );
		wp_enqueue_script( 'solid', get_theme_file_uri( 'assets/app.js' ), array(), $v( 'assets/app.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );

		// The site has no block content, so the block library and global styles only add conflicting rules.
		if ( ! is_admin() ) {
			foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles' ) as $h ) {
				wp_dequeue_style( $h );
			}
		}
	},
	20
);

add_action(
	'wp_head',
	function () {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
		echo '<meta name="theme-color" content="#C80F2E">' . "\n";
		// Light / dark mode before first paint (same logic and storage key as the static site).
		echo "<script>(function(){var d=document.documentElement,s=null;try{s=localStorage.getItem('solid-theme')}catch(e){}var m=location.search.match(/[?&]theme=(dark|light)/);if(m)s=m[1];var f='" . SOLID_DEFAULT_MODE . "';d.setAttribute('data-theme',s||(f==='auto'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):f))})()</script>\n";
	},
	1
);

// Site icon fallback: the SM mark, until one is set in Settings → General.
add_action(
	'wp_head',
	function () {
		if ( has_site_icon() ) {
			return;
		}
		$b = get_theme_file_uri( 'assets/brand/' );
		echo '<link rel="icon" href="' . esc_url( $b . 'favicon.ico' ) . '" sizes="48x48"><link rel="icon" href="' . esc_url( $b . 'sm-mark-red.svg' ) . '" type="image/svg+xml"><link rel="apple-touch-icon" href="' . esc_url( $b . 'apple-touch-icon.png' ) . '">' . "\n";
	},
	5
);

// Page titles: "Business | SOLID", and "SOLID — Grow. Achieve. Thrive. | SOLID" on the home page.
add_filter( 'document_title_separator', fn() => '|' );
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( is_front_page() ) {
			return array( 'title' => get_bloginfo( 'name' ) . ' — ' . get_bloginfo( 'description' ), 'site' => get_bloginfo( 'name' ) );
		}
		return $parts;
	}
);

// The design loads its own fonts; Elementor's default Roboto files are not used.
add_filter( 'elementor/frontend/print_google_fonts', '__return_false' );

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

add_filter(
	'language_attributes',
	function ( $out ) {
		if ( is_admin() ) {
			return $out;
		}
		return $out . ' data-theme="' . SOLID_DEFAULT_MODE . '" data-search-index="' . esc_url( rest_url( 'solid/v1/search-index' ) ) . '"';
	}
);

/* ───────── Body classes (match the static site) ───────── */

add_filter(
	'body_class',
	function ( $c ) {
		if ( is_front_page() ) {
			$c[] = 'is-home';
		} elseif ( is_category() ) {
			$c[] = 'is-section';
		} elseif ( is_singular( 'post' ) ) {
			$c[] = 'is-article';
		} else {
			$c[] = 'is-page';
			if ( is_page( 'the-solid-100' ) ) {
				$c[] = 'is-100';
			}
		}
		return $c;
	}
);

/* ───────── Section URLs: /business/ rather than /category/business/ ───────── */

add_filter(
	'term_link',
	function ( $url, $term, $tax ) {
		if ( 'category' !== $tax ) {
			return $url;
		}
		if ( $term->parent ) {
			return solid_sub_link( $term );
		}
		return home_url( '/' . $term->slug . '/' );
	},
	10,
	3
);

function solid_section_rewrites() {
	$terms = get_terms( array( 'taxonomy' => 'category', 'parent' => 0, 'hide_empty' => false, 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) ) {
		return;
	}
	foreach ( $terms as $slug ) {
		$q = preg_quote( $slug, '#' );
		add_rewrite_rule( "^{$q}/page/([0-9]+)/?$", 'index.php?category_name=' . $slug . '&paged=$matches[1]', 'top' );
		add_rewrite_rule( "^{$q}/feed/(feed|rdf|rss|rss2|atom)/?$", 'index.php?category_name=' . $slug . '&feed=$matches[1]', 'top' );
		add_rewrite_rule( "^{$q}/?$", 'index.php?category_name=' . $slug, 'top' );
	}
}
add_action( 'init', 'solid_section_rewrites' );

foreach ( array( 'created_category', 'edited_category', 'delete_category' ) as $hook ) {
	add_action( $hook, fn() => update_option( 'solid_flush_rewrites', 1 ) );
}
add_action(
	'init',
	function () {
		if ( get_option( 'solid_flush_rewrites' ) ) {
			delete_option( 'solid_flush_rewrites' );
			flush_rewrite_rules( false );
		}
	},
	99
);
add_action( 'after_switch_theme', fn() => update_option( 'solid_flush_rewrites', 1 ) );

// Topic archives live on their section page, filtered by the topic chips.
add_action(
	'template_redirect',
	function () {
		if ( is_category() && get_queried_object()->parent && ! is_feed() ) {
			wp_safe_redirect( solid_sub_link( get_queried_object() ), 301 );
			exit;
		}
	}
);

// Section pages show every story in the section.
add_action(
	'pre_get_posts',
	function ( $q ) {
		if ( ! is_admin() && $q->is_main_query() && $q->is_category() ) {
			$q->set( 'posts_per_page', 60 );
		}
	}
);

/* ───────── Customizer: footer text and social links ───────── */

add_action(
	'customize_register',
	function ( $wp ) {
		$wp->add_section( 'solid', array( 'title' => 'SOLID', 'priority' => 30 ) );
		$fields = array(
			'solid_footer_text' => array( 'Footer text', 'textarea', 'The magazine for Nigerian men who want more — in business, in life and in themselves.' ),
			'solid_x'           => array( 'X profile URL', 'url', '#' ),
			'solid_instagram'   => array( 'Instagram URL', 'url', '#' ),
			'solid_youtube'     => array( 'YouTube URL', 'url', '#' ),
			'solid_linkedin'    => array( 'LinkedIn URL', 'url', '#' ),
			'solid_tagline'     => array( 'Top bar tagline', 'text', 'The magazine for men who want more' ),
		);
		foreach ( $fields as $id => $f ) {
			$wp->add_setting( $id, array( 'default' => $f[2], 'sanitize_callback' => 'url' === $f[1] ? 'esc_url_raw' : 'sanitize_textarea_field' ) );
			$wp->add_control( $id, array( 'label' => $f[0], 'section' => 'solid', 'type' => $f[1] ) );
		}
	}
);

