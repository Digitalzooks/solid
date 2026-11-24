<?php
/**
 * Plugin Name: SOLID Core
 * Description: Content types (writers, Conversations, SOLID 100 honourees), section details, search index and the SOLID Elementor widgets for the SOLID magazine. Works with the SOLID theme.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: SOLID
 * Text Domain: solid-core
 */

defined( 'ABSPATH' ) || exit;

define( 'SOLID_CORE_DIR', __DIR__ );

require __DIR__ . '/inc/content.php';
require __DIR__ . '/inc/admin.php';
require __DIR__ . '/inc/import.php';
require __DIR__ . '/inc/forms.php';

add_action(
	'plugins_loaded',
	function () {
		if ( did_action( 'elementor/loaded' ) ) {
			require __DIR__ . '/inc/elementor.php';
		}
	}
);

register_activation_hook(
	__FILE__,
	function () {
		solid_core_register_types();
		flush_rewrite_rules( false );
	}
);
