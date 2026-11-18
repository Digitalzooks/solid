<?php
/**
 * Editing screens: story details, writer, conversation and honouree fields, and section details.
 */
defined( 'ABSPATH' ) || exit;

function solid_core_fields() {
	$kinds = array( 'Video Interview' => 'Video Interview', 'Podcast Episode' => 'Podcast Episode', 'Interview Transcript' => 'Interview Transcript' );
	return array(
		'post'               => array(
			'title'  => 'Story details',
			'fields' => array(
				'solid_writer' => array( 'Writer', 'writer' ),
				'solid_mins'   => array( 'Reading time (minutes). Leave empty to calculate it.', 'number' ),
			),
		),
		'solid_writer'       => array(
			'title'  => 'Writer details',
			'fields' => array( 'solid_role' => array( 'Role, e.g. Business Editor', 'text' ) ),
		),
		'solid_conversation' => array(
			'title'  => 'Conversation details',
			'fields' => array(
				'solid_guest'   => array( 'Guest', 'text' ),
				'solid_role'    => array( 'Guest role, e.g. Founder, logistics', 'text' ),
				'solid_kind'    => array( 'Format', $kinds ),
				'solid_length'  => array( 'Length, e.g. 48 min or 18 min read', 'text' ),
				'solid_episode' => array( 'Episode number', 'number' ),
				'solid_url'     => array( 'Link (video, podcast or transcript). Leave empty to link to the Conversations page.', 'url' ),
			),
		),
		'solid_honouree'     => array(
			'title'  => 'Honouree details',
			'fields' => array(
				'solid_role'     => array( 'Role, e.g. Founder & CEO', 'text' ),
				'solid_category' => array( 'Recognition category', 'text' ),
			),
		),
	);
}

add_action(
	'add_meta_boxes',
	function () {
		foreach ( solid_core_fields() as $type => $box ) {
			add_meta_box( 'solid_details', $box['title'], 'solid_core_render_box', $type, 'post' === $type ? 'side' : 'normal', 'high', $box['fields'] );
		}
	}
);

function solid_core_render_box( $post, $box ) {
	wp_nonce_field( 'solid_details', 'solid_details_nonce' );
	foreach ( $box['args'] as $key => $f ) {
		$val = get_post_meta( $post->ID, $key, true );
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $f[0] ) . '</strong></label><br>';
		if ( 'writer' === $f[1] ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" style="width:100%"><option value="">— None —</option>';
			foreach ( get_posts( array( 'post_type' => 'solid_writer', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) as $w ) {
				echo '<option value="' . (int) $w->ID . '"' . selected( (int) $val, $w->ID, false ) . '>' . esc_html( $w->post_title ) . '</option>';
			}
			echo '</select>';
		} elseif ( is_array( $f[1] ) ) {
			echo '<select id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" style="width:100%">';
			foreach ( $f[1] as $v => $l ) {
				echo '<option value="' . esc_attr( $v ) . '"' . selected( $val, $v, false ) . '>' . esc_html( $l ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $f[1] ) . '" value="' . esc_attr( $val ) . '" style="width:100%">';
		}
		echo '</p>';
	}
	if ( 'post' === $post->post_type ) {
		echo '<p class="description">Put each story in one topic (a sub-category of its section). The excerpt is used as the standfirst.</p>';
	}
}

add_action(
	'save_post',
	function ( $post_id, $post ) {
		if ( ! isset( $_POST['solid_details_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['solid_details_nonce'] ), 'solid_details' ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$fields = solid_core_fields()[ $post->post_type ]['fields'] ?? array();
		foreach ( $fields as $key => $f ) {
			$raw = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore
			$val = 'url' === $f[1] ? esc_url_raw( $raw ) : sanitize_text_field( $raw );
			if ( '' === $val ) {
				delete_post_meta( $post_id, $key );
			} else {
				update_post_meta( $post_id, $key, in_array( $f[1], array( 'number', 'writer' ), true ) ? (int) $val : $val );
			}
		}
	},
	10,
	2
);

/* ───────── Section (category) details ───────── */

add_action(
	'category_add_form_fields',
	function () {
		echo '<div class="form-field"><label for="solid_line">Strapline</label><input name="solid_line" id="solid_line" type="text"><p>Short line shown before the description, e.g. “Build. Scale. Lead.”</p></div>';
		echo '<div class="form-field"><label for="solid_order">Order</label><input name="solid_order" id="solid_order" type="number" value="0"><p>Position in the navigation and menus (sections and topics).</p></div>';
	}
);
add_action(
	'category_edit_form_fields',
	function ( $term ) {
		echo '<tr class="form-field"><th><label for="solid_line">Strapline</label></th><td><input name="solid_line" id="solid_line" type="text" value="' . esc_attr( get_term_meta( $term->term_id, 'solid_line', true ) ) . '"><p class="description">Short line shown before the description, e.g. “Build. Scale. Lead.”</p></td></tr>';
		echo '<tr class="form-field"><th><label for="solid_order">Order</label></th><td><input name="solid_order" id="solid_order" type="number" value="' . esc_attr( (int) get_term_meta( $term->term_id, 'solid_order', true ) ) . '"><p class="description">Position in the navigation and menus.</p></td></tr>';
	}
);
foreach ( array( 'created_category', 'edited_category' ) as $hook ) {
	add_action(
		$hook,
		function ( $term_id ) {
			if ( ! current_user_can( 'manage_categories' ) || ! isset( $_POST['solid_line'] ) ) { // phpcs:ignore
				return;
			}
			update_term_meta( $term_id, 'solid_line', sanitize_text_field( wp_unslash( $_POST['solid_line'] ) ) ); // phpcs:ignore
			update_term_meta( $term_id, 'solid_order', (int) ( $_POST['solid_order'] ?? 0 ) ); // phpcs:ignore
		}
	);
}

/* Admin list columns */
add_filter( 'manage_solid_conversation_posts_columns', fn( $c ) => array_slice( $c, 0, 2 ) + array( 'solid_kind' => 'Format', 'solid_episode' => 'Ep.' ) + $c );
add_filter( 'manage_solid_honouree_posts_columns', fn( $c ) => array_slice( $c, 0, 2 ) + array( 'solid_category' => 'Category' ) + $c );
add_filter( 'manage_solid_writer_posts_columns', fn( $c ) => array_slice( $c, 0, 2 ) + array( 'solid_role' => 'Role' ) + $c );
foreach ( array( 'solid_conversation', 'solid_honouree', 'solid_writer' ) as $t ) {
	add_action(
		"manage_{$t}_posts_custom_column",
		function ( $col, $id ) {
			if ( 0 === strpos( $col, 'solid_' ) ) {
				echo esc_html( get_post_meta( $id, $col, true ) );
			}
		},
		10,
		2
	);
}
