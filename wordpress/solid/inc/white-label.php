<?php
/**
 * White label: SOLID branding on the sign-in page, the admin bar, the dashboard and the admin screens,
 * in place of the WordPress logo, links and promotional panels.
 */
defined( 'ABSPATH' ) || exit;

/** A standalone SVG of the SOLID wordmark, in the given colours. */
function solid_logo_standalone( $kind = 'full', $ink = '#FFFFFF', $red = '#C8102E' ) {
	$g = solid_logo_data()[ $kind ];
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $g['w'] . ' ' . $g['h'] . '"><path fill="' . $ink . '" fill-rule="evenodd" d="' . $g['ink'] . '"/><path fill="' . $red . '" fill-rule="evenodd" d="' . $g['red'] . '"/></svg>';
}

function solid_wl_asset( $file ) {
	return get_theme_file_uri( 'assets/' . $file );
}

function solid_wl_version( $file ) {
	return SOLID_VERSION . '.' . filemtime( get_theme_file_path( 'assets/' . $file ) );
}

/** The SM mark as favicon, until a site icon is set in Settings → General. */
function solid_wl_favicon() {
	if ( ! has_site_icon() ) {
		echo '<link rel="icon" href="' . esc_url( solid_wl_asset( 'brand/sm-mark-red.svg' ) ) . '" type="image/svg+xml">' . "\n";
	}
}
add_action( 'login_head', 'solid_wl_favicon' );
add_action( 'admin_head', 'solid_wl_favicon' );

/* ───────── Sign-in page ───────── */

add_action(
	'login_enqueue_scripts',
	function () {
		wp_enqueue_style( 'solid-fonts', SOLID_FONTS, array(), null );
		wp_enqueue_style( 'solid-login', solid_wl_asset( 'login.css' ), array( 'login' ), solid_wl_version( 'login.css' ) );
		$logo = 'data:image/svg+xml;base64,' . base64_encode( solid_logo_standalone( 'full' ) ); // phpcs:ignore
		wp_add_inline_style( 'solid-login', '.login h1 a{background-image:url(' . $logo . ')}' );
	}
);

add_filter( 'login_headerurl', fn() => home_url( '/' ) );
add_filter( 'login_headertext', fn() => get_bloginfo( 'name' ) );
add_filter( 'login_title', fn( $title ) => preg_replace( '/\s*(&lsaquo;|‹|&#8212;|—)\s*WordPress\s*$/u', '', $title ) );
add_filter( 'login_display_language_dropdown', '__return_false' );

/* ───────── Admin screens ───────── */

add_action(
	'admin_enqueue_scripts',
	function () {
		wp_enqueue_style( 'solid-admin', solid_wl_asset( 'admin.css' ), array(), solid_wl_version( 'admin.css' ) );
		$screen = get_current_screen();
		if ( $screen && 'dashboard' === $screen->id ) {
			wp_enqueue_style( 'solid-fonts', SOLID_FONTS, array(), null );
		}
	}
);

// One look for everyone: the SOLID colours replace the admin colour schemes.
remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
add_filter( 'get_user_option_admin_color', fn() => 'fresh' );

// "Dashboard ‹ SOLID" rather than "Dashboard ‹ SOLID — WordPress".
add_filter( 'admin_title', fn( $admin_title, $title ) => $title . ' ‹ ' . get_bloginfo( 'name' ), 10, 2 );

add_filter( 'admin_footer_text', fn() => '<span class="solid-foot">' . esc_html( get_bloginfo( 'name' ) ) . ' · Editorial dashboard</span>' );
add_filter( 'update_footer', fn( $text ) => current_user_can( 'update_core' ) ? $text : '', 11 );

// The help tabs point to WordPress.org documentation.
add_action(
	'current_screen',
	function ( $screen ) {
		$screen->remove_help_tabs();
	}
);

// Emails from the site carry its name, not "WordPress".
add_filter( 'wp_mail_from_name', fn( $name ) => 'WordPress' === $name ? get_bloginfo( 'name' ) : $name );

remove_action( 'wp_head', 'wp_generator' );

/* ───────── Admin bar ───────── */

add_action(
	'admin_bar_menu',
	function ( $bar ) {
		$bar->remove_node( 'wp-logo' );
		$bar->add_node(
			array(
				'id'    => 'solid-brand',
				'title' => '<img src="' . esc_url( solid_wl_asset( 'brand/sm-mark-red.svg' ) ) . '" alt="" width="20" height="20" style="width:20px;height:20px;margin:6px 0;vertical-align:top;border-radius:2px"><span class="screen-reader-text">' . esc_html( get_bloginfo( 'name' ) ) . ' dashboard</span>',
				'href'  => admin_url(),
				'meta'  => array( 'title' => get_bloginfo( 'name' ) . ' dashboard' ),
			)
		);
	},
	5
);

add_action(
	'admin_bar_menu',
	function ( $bar ) {
		$node = $bar->get_node( 'my-account' );
		if ( $node ) {
			$bar->add_node( array( 'id' => 'my-account', 'title' => str_replace( 'Howdy,', 'Hello,', $node->title ) ) );
		}
	},
	9999
);

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( is_admin_bar_showing() ) {
			wp_add_inline_style( 'admin-bar', '#wpadminbar{background:#0F0F10}' );
		}
	}
);

/* ───────── Dashboard ───────── */

remove_action( 'welcome_panel', 'wp_welcome_panel' );

add_action(
	'wp_dashboard_setup',
	function () {
		global $wp_meta_boxes;

		// Only the SOLID panels, Activity and (for administrators) Site Health. Plugins' promotional panels go.
		$keep = apply_filters( 'solid_dashboard_keep', array( 'dashboard_activity', 'dashboard_site_health' ) );
		foreach ( (array) ( $wp_meta_boxes['dashboard'] ?? array() ) as $context => $priorities ) {
			foreach ( (array) $priorities as $priority => $boxes ) {
				foreach ( (array) $boxes as $id => $box ) {
					if ( ! in_array( $id, $keep, true ) ) {
						unset( $wp_meta_boxes['dashboard'][ $context ][ $priority ][ $id ] );
					}
				}
			}
		}

		if ( current_user_can( 'edit_posts' ) ) {
			wp_add_dashboard_widget( 'solid_in_progress', 'Stories in progress', 'solid_wl_in_progress' );
			add_meta_box( 'solid_guide', 'Editorial guide', 'solid_wl_guide', 'dashboard', 'side', 'high' );
		}
	},
	999
);

/** The SOLID banner at the top of the dashboard: greeting, quick actions and counts. */
add_action(
	'admin_notices',
	function () {
		$screen = get_current_screen();
		if ( ! $screen || 'dashboard' !== $screen->id ) {
			return;
		}
		$user  = wp_get_current_user();
		$name  = $user->first_name ? $user->first_name : $user->display_name;
		$hour  = (int) current_time( 'G' );
		$greet = $hour < 12 ? 'Good morning' : ( $hour < 17 ? 'Good afternoon' : 'Good evening' );

		$posts   = wp_count_posts( 'post' );
		$writers = wp_count_posts( 'solid_writer' );
		$hon     = wp_count_posts( 'solid_honouree' );
		$conv    = wp_count_posts( 'solid_conversation' );
		$stats   = array(
			array( 'Published stories', (int) $posts->publish, admin_url( 'edit.php?post_status=publish' ) ),
			array( 'Drafts and pending', (int) $posts->draft + (int) $posts->pending, admin_url( 'edit.php?post_status=draft' ) ),
			array( 'Writers', (int) $writers->publish, admin_url( 'edit.php?post_type=solid_writer' ) ),
			array( 'Conversations', (int) $conv->publish, admin_url( 'edit.php?post_type=solid_conversation' ) ),
			array( 'SOLID 100', (int) $hon->publish, admin_url( 'edit.php?post_type=solid_honouree' ) ),
		);

		$actions = array(
			array( 'edit_posts', 'Write a story', admin_url( 'post-new.php' ), true ),
			array( 'edit_posts', 'All stories', admin_url( 'edit.php' ), false ),
			array( 'edit_posts', 'Writers', admin_url( 'edit.php?post_type=solid_writer' ), false ),
			array( 'manage_categories', 'Sections', admin_url( 'edit-tags.php?taxonomy=category' ), false ),
			array( 'upload_files', 'Media', admin_url( 'upload.php' ), false ),
			array( 'edit_pages', 'Pages', admin_url( 'edit.php?post_type=page' ), false ),
			array( 'read', 'View site', home_url( '/' ), false ),
		);

		echo '<div class="solid-hero">';
		echo '<div class="solid-hero__top"><div class="solid-hero__logo" aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . solid_logo_standalone( 'full' ) . '</div>'; // phpcs:ignore
		echo '<p class="solid-hero__date">' . esc_html( wp_date( 'l j F Y' ) ) . '</p></div>';
		echo '<h1 class="solid-hero__h">' . esc_html( $greet . ', ' . $name ) . '.</h1>';
		echo '<p class="solid-hero__dek">Welcome to the ' . esc_html( get_bloginfo( 'name' ) ) . ' editorial dashboard.</p>';
		echo '<div class="solid-hero__actions">';
		foreach ( $actions as $a ) {
			if ( current_user_can( $a[0] ) ) {
				echo '<a class="solid-btn' . ( $a[3] ? ' solid-btn--red' : '' ) . '" href="' . esc_url( $a[2] ) . '">' . esc_html( $a[1] ) . '</a>';
			}
		}
		echo '</div>';
		if ( current_user_can( 'edit_posts' ) ) {
			echo '<div class="solid-hero__stats">';
			foreach ( $stats as $s ) {
				echo '<a class="solid-stat" href="' . esc_url( $s[2] ) . '"><strong>' . esc_html( number_format_i18n( $s[1] ) ) . '</strong><span>' . esc_html( $s[0] ) . '</span></a>';
			}
			echo '</div>';
		}
		echo '</div>';
	},
	1
);

function solid_wl_in_progress() {
	$q = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => array( 'draft', 'pending', 'future' ),
			'posts_per_page' => 6,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		)
	);
	if ( ! $q ) {
		echo '<p class="solid-empty">Nothing in progress. <a href="' . esc_url( admin_url( 'post-new.php' ) ) . '">Write a story</a></p>';
		return;
	}
	$labels = array( 'draft' => 'Draft', 'pending' => 'Pending review', 'future' => 'Scheduled' );
	echo '<ul class="solid-list">';
	foreach ( $q as $p ) {
		$sec  = function_exists( 'solid_post_section' ) ? solid_post_section( $p ) : null;
		$by   = function_exists( 'solid_writer_name' ) ? solid_writer_name( $p ) : '';
		$meta = array_filter( array( $sec ? $sec->name : '', $by, 'Edited ' . human_time_diff( get_post_modified_time( 'U', true, $p ) ) . ' ago' ) );
		$link = current_user_can( 'edit_post', $p->ID ) ? get_edit_post_link( $p->ID ) : '';
		echo '<li><span class="solid-pill solid-pill--' . esc_attr( $p->post_status ) . '">' . esc_html( $labels[ $p->post_status ] ?? $p->post_status ) . '</span>';
		echo $link ? '<a class="solid-list__t" href="' . esc_url( $link ) . '">' : '<span class="solid-list__t">';
		echo esc_html( get_the_title( $p ) ? get_the_title( $p ) : '(No title)' );
		echo $link ? '</a>' : '</span>';
		echo '<span class="solid-list__m">' . esc_html( implode( ' · ', $meta ) ) . '</span></li>';
	}
	echo '</ul><p class="solid-more"><a href="' . esc_url( admin_url( 'edit.php?post_status=draft' ) ) . '">All drafts</a></p>';
}

function solid_wl_guide() {
	$tips = array(
		'Put each story in one topic, a sub-category of its section.',
		'The excerpt is the standfirst shown under the headline and on story cards.',
		'Choose the writer under Story details. Reading time is worked out for you if you leave it empty.',
		'Use a landscape featured image at least 1680 pixels wide.',
		'The home page and other pages are built in Elementor with the SOLID widgets.',
	);
	echo '<ol class="solid-guide">';
	foreach ( $tips as $t ) {
		echo '<li>' . esc_html( $t ) . '</li>';
	}
	echo '</ol>';
}
