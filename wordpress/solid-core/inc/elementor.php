<?php
/**
 * SOLID Elementor widgets: registration and the shared base class.
 * Every widget prints the same markup as the Solid One design, using the SOLID theme's helpers.
 */
defined( 'ABSPATH' ) || exit;

add_action(
	'elementor/elements/categories_registered',
	function ( $m ) {
		$m->add_category( 'solid', array( 'title' => 'SOLID Magazine', 'icon' => 'eicon-posts-grid' ) );
	}
);

add_action(
	'elementor/widgets/register',
	function ( $widgets ) {
		if ( ! function_exists( 'solid_card' ) ) {
			return; // The SOLID theme provides the markup helpers.
		}
		require_once SOLID_CORE_DIR . '/widgets/base.php';
		require_once SOLID_CORE_DIR . '/widgets/home.php';
		require_once SOLID_CORE_DIR . '/widgets/pages.php';
		foreach ( get_declared_classes() as $class ) {
			if ( is_subclass_of( $class, 'Solid_Widget' ) && ! ( new ReflectionClass( $class ) )->isAbstract() ) {
				$widgets->register( new $class() );
			}
		}
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! function_exists( 'solid_card' ) && current_user_can( 'switch_themes' ) ) {
			echo '<div class="notice notice-warning"><p><strong>SOLID Core:</strong> the SOLID Elementor widgets need the SOLID theme to be active.</p></div>';
		}
	}
);
