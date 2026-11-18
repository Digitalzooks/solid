<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip" href="#main">Skip to content</a>
<?php echo solid_logo_defs(); // phpcs:ignore ?>
<div class="top"><div class="wrap top__in"><span><?php echo esc_html( wp_date( 'l, j F Y' ) ); ?></span><span class="top__mid"><?php echo esc_html( get_theme_mod( 'solid_tagline', 'The magazine for men who want more' ) ); ?></span><span class="top__r"><a href="<?php echo esc_url( solid_page_url( 'the-solid-100' ) ); ?>">The SOLID 100</a><a href="<?php echo esc_url( solid_page_url( 'about' ) ); ?>">About</a><a href="<?php echo esc_url( solid_page_url( 'advertise' ) ); ?>">Advertise</a></span></div></div>
<header class="mast"><div class="wrap mast__in">
<div class="mast__l"><button class="mast__menu" type="button" data-menu-open><?php echo solid_icon( 'menu' ); // phpcs:ignore ?><span>Sections</span></button><button class="icon-btn" type="button" data-search-open aria-label="Search"><?php echo solid_icon( 'search' ); // phpcs:ignore ?></button></div>
<?php echo solid_logo( true ); // phpcs:ignore ?>

<div class="mast__r"><?php echo solid_theme_toggle(); // phpcs:ignore ?><a class="btn btn--red" href="#newsletter">Subscribe</a></div>
</div></header>
<nav class="nav" aria-label="Sections"><div class="wrap nav__in"><a class="nav__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-hidden="true" tabindex="-1"><?php echo solid_logo_svg(); // phpcs:ignore ?></a><ul class="nav__l"><?php echo solid_nav_items(); // phpcs:ignore ?></ul></div></nav>
<main id="main">
