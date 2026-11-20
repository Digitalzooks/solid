<?php
/**
 * Content model: writers, conversations, honourees, section details and the search index.
 */
defined( 'ABSPATH' ) || exit;

function solid_core_register_types() {
	register_post_type(
		'solid_writer',
		array(
			'labels'        => array( 'name' => 'Writers', 'singular_name' => 'Writer', 'add_new_item' => 'Add writer', 'edit_item' => 'Edit writer', 'featured_image' => 'Photo', 'set_featured_image' => 'Set photo' ),
			'public'        => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-id',
			'menu_position' => 6,
			'supports'      => array( 'title', 'excerpt', 'thumbnail' ),
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'author', 'with_front' => false ),
		)
	);
	register_post_type(
		'solid_conversation',
		array(
			'labels'        => array( 'name' => 'Conversations', 'singular_name' => 'Conversation', 'add_new_item' => 'Add conversation', 'edit_item' => 'Edit conversation', 'featured_image' => 'Cover image', 'set_featured_image' => 'Set cover image' ),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-microphone',
			'menu_position' => 7,
			'supports'      => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
		)
	);
	register_post_type(
		'solid_honouree',
		array(
			'labels'        => array( 'name' => 'SOLID 100', 'singular_name' => 'Man on the list', 'all_items' => 'The list', 'add_new_item' => 'Add a man to the list', 'edit_item' => 'Edit man on the list', 'featured_image' => 'Portrait', 'set_featured_image' => 'Set portrait' ),
			'public'        => false,
			'show_ui'       => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-list-view',
			'menu_position' => 8,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);

	$meta = array(
		'post'               => array( 'solid_mins' => 'integer', 'solid_writer' => 'integer' ),
		'solid_writer'       => array( 'solid_role' => 'string' ),
		'solid_conversation' => array( 'solid_guest' => 'string', 'solid_role' => 'string', 'solid_kind' => 'string', 'solid_length' => 'string', 'solid_episode' => 'string', 'solid_url' => 'string' ),
		'solid_honouree'     => array( 'solid_role' => 'string', 'solid_category' => 'string' ),
	);
	foreach ( $meta as $type => $keys ) {
		foreach ( $keys as $key => $kind ) {
			register_post_meta(
				$type,
				$key,
				array(
					'type'          => $kind,
					'single'        => true,
					'show_in_rest'  => true,
					'auth_callback' => fn() => current_user_can( 'edit_posts' ),
				)
			);
		}
	}
	register_term_meta( 'category', 'solid_line', array( 'type' => 'string', 'single' => true, 'show_in_rest' => true ) );
	register_term_meta( 'category', 'solid_order', array( 'type' => 'integer', 'single' => true, 'show_in_rest' => true ) );
}
add_action( 'init', 'solid_core_register_types' );

// The writer pages use /author/name/, so WordPress's own user archives move to /profile/.
add_action(
	'init',
	function () {
		$GLOBALS['wp_rewrite']->author_base = 'profile';
	},
	1
);

/* ───────── Search index for the instant search ───────── */

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'solid/v1',
			'/search-index',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					$out = get_transient( 'solid_search_index' );
					if ( false === $out ) {
						$out = array();
						foreach ( get_posts( array( 'post_type' => 'post', 'posts_per_page' => 500, 'no_found_rows' => true ) ) as $p ) {
							$sec   = function_exists( 'solid_post_section' ) ? solid_post_section( $p ) : null;
							$out[] = array(
								't' => html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' ),
								'd' => function_exists( 'solid_dek' ) ? solid_dek( $p ) : $p->post_excerpt,
								'k' => $sec ? $sec->name : '',
								'a' => function_exists( 'solid_writer_name' ) ? solid_writer_name( $p ) : '',
								'u' => get_permalink( $p ),
							);
						}
						set_transient( 'solid_search_index', $out, DAY_IN_SECONDS );
					}
					$res = new WP_REST_Response( $out );
					$res->header( 'Cache-Control', 'public, max-age=300' );
					return $res;
				},
			)
		);
	}
);

foreach ( array( 'save_post_post', 'deleted_post', 'edited_category', 'save_post_solid_writer' ) as $hook ) {
	add_action( $hook, fn() => delete_transient( 'solid_search_index' ) );
}
