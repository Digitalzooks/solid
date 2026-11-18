<?php
get_header();
echo solid_phead( // phpcs:ignore
	array(
		'crumb' => 'Not found',
		'title' => 'Page not found',
		'lede'  => 'The page you are looking for has moved or never existed.',
		'extra' => '<p style="margin-top:24px"><a class="btn btn--red" href="' . esc_url( home_url( '/' ) ) . '">Back to the homepage ' . solid_icon( 'arrow' ) . '</a></p>',
	)
);
get_footer();
