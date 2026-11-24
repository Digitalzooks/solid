<?php
/**
 * Fluent Forms support for SOLID.
 *
 * The newsletter and advertising enquiry forms are Fluent Forms, connected to FluentCRM in each
 * form's Settings → Configure Integrations. This file:
 * - shows the chosen Fluent Form inside the SOLID newsletter band and enquiry section,
 * - adds the {solid.source} and {solid.section} smartcodes for hidden fields, so FluentCRM can tag
 *   where a reader signed up ("Signed up: Story", "Section: Wealth"),
 * - keeps people who only sent an enquiry off the newsletter (status "Transactional").
 *
 * Which forms to use: Appearance → Customise → Newsletter and enquiry forms.
 */
defined( 'ABSPATH' ) || exit;

function solid_ff_active() {
	return defined( 'FLUENTFORM' ) || function_exists( 'wpFluentForm' );
}

/** Published Fluent Forms as [id => title]. */
function solid_ff_options( $blank = '' ) {
	$out = $blank ? array( '' => $blank ) : array();
	if ( ! solid_ff_active() ) {
		return $out;
	}
	global $wpdb;
	$rows = $wpdb->get_results( "SELECT id, title FROM {$wpdb->prefix}fluentform_forms WHERE status = 'published' ORDER BY title" ); // phpcs:ignore
	foreach ( (array) $rows as $r ) {
		$out[ (string) $r->id ] = $r->title;
	}
	return $out;
}

/** The site-wide form for a role ('newsletter' or 'enquiry'). */
function solid_ff_default( $role ) {
	$d = array( 'newsletter' => 2, 'enquiry' => 1 );
	return (int) get_option( 'solid_ff_' . $role, $d[ $role ] ?? 0 );
}

/** Rendered Fluent Form, or '' when Fluent Forms is off or the form is missing. */
function solid_ff_render( $role, $id = 0 ) {
	if ( ! solid_ff_active() ) {
		return '';
	}
	$id = (int) $id ? (int) $id : solid_ff_default( $role );
	if ( ! $id || ! isset( solid_ff_options()[ (string) $id ] ) ) {
		return '';
	}
	return do_shortcode( '[fluentform id="' . $id . '"]' );
}

/* ───────── Smartcodes for hidden fields ───────── */

/** The top-level section a category belongs to. */
function solid_ff_top_section( $term ) {
	if ( ! $term ) {
		return '';
	}
	while ( $term->parent ) {
		$p = get_term( $term->parent, 'category' );
		if ( ! $p || is_wp_error( $p ) ) {
			break;
		}
		$term = $p;
	}
	return 'uncategorized' === $term->slug ? '' : html_entity_decode( $term->name );
}

function solid_ff_context() {
	$src = 'Other page';
	$sec = '';
	if ( is_front_page() ) {
		$src = 'Homepage';
	} elseif ( is_singular( 'post' ) ) {
		$src = 'Story';
		$c   = get_the_category( get_queried_object_id() );
		$sec = $c ? solid_ff_top_section( $c[0] ) : '';
	} elseif ( is_category() ) {
		$src = 'Section';
		$sec = solid_ff_top_section( get_queried_object() );
	} elseif ( is_singular( 'solid_writer' ) || is_author() ) {
		$src = 'Writer page';
	}
	return array( 'source' => $src, 'section' => $sec );
}

add_filter(
	'fluentform/editor_shortcode_callback_group_solid',
	function ( $value, $form, $keys ) {
		$ctx = solid_ff_context();
		$key = is_array( $keys ) ? reset( $keys ) : '';
		return isset( $ctx[ $key ] ) ? $ctx[ $key ] : '';
	},
	10,
	3
);

/* ───────── Enquiries are not newsletter sign-ups ───────── */

add_action(
	'fluent_crm/contact_added_by_fluentform',
	function ( $contact, $entry, $form ) {
		if ( (int) $form->id === solid_ff_default( 'enquiry' ) && 'subscribed' === $contact->status ) {
			$contact->status = 'transactional';
			$contact->save();
		}
	},
	10,
	3
);

/* ───────── Customiser ───────── */

add_action(
	'customize_register',
	function ( $wp ) {
		if ( ! solid_ff_active() ) {
			return;
		}
		$wp->add_section(
			'solid_forms',
			array(
				'title'       => 'Newsletter and enquiry forms',
				'priority'    => 160,
				'description' => 'Fluent Forms used across the site. The newsletter form appears on story, section and writer pages and in SOLID Newsletter widgets. Lists, tags and emails are set in each form’s Settings → Configure Integrations → FluentCRM.',
			)
		);
		foreach ( array( 'newsletter' => 'Newsletter form', 'enquiry' => 'Advertising enquiry form' ) as $role => $label ) {
			$wp->add_setting( 'solid_ff_' . $role, array( 'type' => 'option', 'default' => solid_ff_default( $role ), 'sanitize_callback' => 'absint' ) );
			$wp->add_control( 'solid_ff_' . $role, array( 'label' => $label, 'section' => 'solid_forms', 'type' => 'select', 'choices' => solid_ff_options() ) );
		}
	}
);
