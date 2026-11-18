<?php
/**
 * One-off importer for the launch content (the Solid One placeholder stories, people and pages).
 * Run step by step: solid_core_import( 'terms' | 'photos' | 'people' | 'stories' | 'pages' | 'settings', $seed_path ).
 * Every step is safe to run again: existing items are updated, not duplicated.
 */
defined( 'ABSPATH' ) || exit;

function solid_core_seed( $path ) {
	static $seed = null;
	if ( null === $seed ) {
		$seed = json_decode( (string) file_get_contents( $path ), true );
	}
	return $seed;
}

function solid_core_import( $step, $path, $arg = null ) {
	$seed = solid_core_seed( $path );
	switch ( $step ) {
		case 'terms':
			return solid_core_import_terms( $seed );
		case 'photos':
			return solid_core_import_photos( $seed, (int) $arg );
		case 'people':
			return solid_core_import_people( $seed );
		case 'stories':
			return solid_core_import_stories( $seed );
		case 'pages':
			return solid_core_import_pages( $seed );
		case 'settings':
			return solid_core_import_settings();
	}
	return 'Unknown step';
}

/* ───────── Sections and topics ───────── */

function solid_core_import_terms( $seed ) {
	$out = array();
	foreach ( $seed['sections'] as $s ) {
		$t = term_exists( $s['slug'], 'category' );
		if ( ! $t ) {
			$t = wp_insert_term( $s['name'], 'category', array( 'slug' => $s['slug'], 'description' => $s['intro'] ) );
		} else {
			wp_update_term( (int) $t['term_id'], 'category', array( 'name' => $s['name'], 'description' => $s['intro'], 'parent' => 0 ) );
		}
		$pid = (int) $t['term_id'];
		update_term_meta( $pid, 'solid_line', $s['line'] );
		update_term_meta( $pid, 'solid_order', $s['order'] );
		foreach ( $s['subs'] as $i => $name ) {
			$slug = solid_slugify( $name );
			// Topic slugs are unique per section (Mentorship appears under two sections).
			$existing = get_terms( array( 'taxonomy' => 'category', 'parent' => $pid, 'hide_empty' => false, 'slug' => array( $slug, $slug . '-' . $s['slug'] ) ) );
			if ( $existing && ! is_wp_error( $existing ) ) {
				$sid = $existing[0]->term_id;
				wp_update_term( $sid, 'category', array( 'name' => $name ) );
			} else {
				$r = wp_insert_term( $name, 'category', array( 'parent' => $pid, 'slug' => term_exists( $slug, 'category' ) ? $slug . '-' . $s['slug'] : $slug ) );
				if ( is_wp_error( $r ) ) {
					$out[] = $name . ': ' . $r->get_error_message();
					continue;
				}
				$sid = $r['term_id'];
			}
			update_term_meta( $sid, 'solid_order', $i + 1 );
		}
		$out[] = $s['name'];
	}
	update_option( 'solid_flush_rewrites', 1 );
	return $out;
}

/* ───────── Photos (sideloaded into the Media Library from Unsplash) ───────── */

function solid_core_photo_keys( $seed ) {
	$keys = array();
	foreach ( array( 'stories', 'authors', 'conversations', 'honourees' ) as $g ) {
		foreach ( $seed[ $g ] as $x ) {
			$keys[ $x['img'] ] = true;
		}
	}
	return array_keys( $keys );
}

/** Imports the next $batch photos that are not in the library yet. Returns what is left. */
function solid_core_import_photos( $seed, $batch = 4 ) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$map  = get_option( 'solid_photo_map', array() );
	$alts = array( 'blueSmile' => 'Portrait of a smiling man in a blue suit' );
	$todo = array_values( array_diff( solid_core_photo_keys( $seed ), array_keys( array_filter( $map, fn( $id ) => get_post( $id ) ) ) ) );
	$done = array();
	foreach ( array_slice( $todo, 0, max( 1, $batch ) ) as $key ) {
		$url = 'https://images.unsplash.com/photo-' . $seed['photos'][ $key ] . '?fm=jpg&q=80&w=1800';
		$tmp = download_url( $url, 60 );
		if ( is_wp_error( $tmp ) ) {
			$done[] = $key . ' failed: ' . $tmp->get_error_message();
			continue;
		}
		$id = media_handle_sideload( array( 'name' => 'solid-' . strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $key ) ) . '.jpg', 'tmp_name' => $tmp ), 0, 'Photograph: Unsplash' );
		if ( is_wp_error( $id ) ) {
			@unlink( $tmp ); // phpcs:ignore
			$done[] = $key . ' failed: ' . $id->get_error_message();
			continue;
		}
		update_post_meta( $id, '_wp_attachment_image_alt', $alts[ $key ] ?? '' );
		update_post_meta( $id, 'solid_photo_key', $key );
		$map[ $key ] = $id;
		update_option( 'solid_photo_map', $map, false );
		$done[] = $key;
	}
	return array( 'imported' => $done, 'left' => count( $todo ) - count( array_filter( $done, fn( $d ) => false === strpos( $d, 'failed' ) ) ) );
}

function solid_core_photo( $key ) {
	$map = get_option( 'solid_photo_map', array() );
	return (int) ( $map[ $key ] ?? 0 );
}

/* ───────── Writers, conversations, honourees ───────── */

function solid_core_upsert( $type, $slug, $data, $meta = array(), $photo = '' ) {
	$found = get_posts( array( 'post_type' => $type, 'name' => $slug, 'post_status' => 'any', 'posts_per_page' => 1 ) );
	$data  = array_merge( array( 'post_type' => $type, 'post_name' => $slug, 'post_status' => 'publish' ), $data );
	if ( $found ) {
		$data['ID'] = $found[0]->ID;
	}
	$id = wp_insert_post( wp_slash( $data ), true );
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	foreach ( $meta as $k => $v ) {
		update_post_meta( $id, $k, $v );
	}
	if ( $photo && solid_core_photo( $photo ) ) {
		set_post_thumbnail( $id, solid_core_photo( $photo ) );
	}
	return $id;
}

function solid_core_import_people( $seed ) {
	$out = array();
	foreach ( $seed['authors'] as $i => $a ) {
		$out[] = solid_core_upsert( 'solid_writer', $a['slug'], array( 'post_title' => $a['name'], 'post_excerpt' => $a['role'] . ' at SOLID. Writes about ambition, work and the business of living well.', 'menu_order' => $i ), array( 'solid_role' => $a['role'] ), $a['img'] );
	}
	foreach ( $seed['conversations'] as $i => $c ) {
		$out[] = solid_core_upsert(
			'solid_conversation',
			solid_slugify( $c['title'] ),
			array( 'post_title' => $c['title'], 'post_excerpt' => $c['dek'], 'menu_order' => $i ),
			array( 'solid_guest' => $c['guest'], 'solid_role' => $c['role'], 'solid_kind' => $c['kind'], 'solid_length' => $c['len'], 'solid_episode' => (string) $c['ep'] ),
			$c['img']
		);
	}
	foreach ( $seed['honourees'] as $i => $h ) {
		$out[] = solid_core_upsert( 'solid_honouree', solid_slugify( $h['name'] ), array( 'post_title' => $h['name'], 'menu_order' => $i ), array( 'solid_role' => $h['role'], 'solid_category' => $h['cat'] ), $h['img'] );
	}
	return $out;
}

/* ───────── Stories ───────── */

function solid_core_story_body( $dek ) {
	$dek = esc_html( $dek );
	return <<<HTML
<p class="drop">There is a particular kind of quiet that settles over a man who knows exactly what he is building. It is not the quiet of someone with nothing to say. It is the quiet of someone who has stopped needing to announce it.</p>
<p>{$dek} That sentence could have been written a decade ago, and in some ways that is the point. The men we spoke to for this story share an unfashionable belief: that most things worth having arrive slowly, and that the work of a serious life is mostly the work of staying the course.</p>
<p>It is easy, in a country that moves as fast as this one, to mistake motion for progress. Lagos rewards the loud. But look closely at the people who are still standing after twenty years and a different pattern emerges. They are deliberate. They keep their promises small and their standards high. They are, in a word, solid.</p>
<h2>The discipline nobody sees</h2>
<p>Ask them how it happened and the answers are almost disappointingly ordinary. They woke up early. They wrote things down. They said no far more often than they said yes. They chose partners carefully and treated their word as the most valuable thing they owned.</p>
<blockquote><p>“Nobody claps for consistency. They clap for the result, years later, and call it luck.”</p></blockquote>
<p>What looks like talent from a distance is usually a long sequence of unremarkable days. The edge is not intensity. It is the refusal to stop: to keep showing up for the plan after the excitement of making the plan has worn off.</p>
<h2>What it asks of you</h2>
<p>None of this is free. Building anything that lasts costs time that could have been spent elsewhere, and the men here are honest about what they traded. The best of them have learned to count success in more than one currency: the health they kept, the families that still know them, the people who would vouch for them without being asked.</p>
<p>That may be the real lesson. Ambition is not the problem, and neither is wanting more. The question is what kind of man the wanting turns you into, and whether, when you finally arrive, there is anyone you love standing beside you.</p>
HTML;
}

function solid_core_import_stories( $seed ) {
	$writers = array();
	foreach ( get_posts( array( 'post_type' => 'solid_writer', 'posts_per_page' => -1 ) ) as $w ) {
		$writers[ $w->post_name ] = $w->ID;
	}
	// Order by date the way the static site did: newest first, one second apart so the order is stable.
	$by_date = $seed['stories'];
	usort( $by_date, fn( $a, $b ) => strcmp( $b['date'], $a['date'] ) );
	$rank = 0;
	$out  = array();
	foreach ( $seed['stories'] as $s ) {
		$sec  = get_term_by( 'slug', $s['section'], 'category' );
		$subs = get_terms( array( 'taxonomy' => 'category', 'parent' => $sec->term_id, 'hide_empty' => false, 'name' => $s['sub'] ) );
		$cat  = ( $subs && ! is_wp_error( $subs ) ) ? $subs[0]->term_id : $sec->term_id;
		// Stories sharing a day keep the static order: earlier in the list = later in the day.
		$time = gmdate( 'H:i:s', 12 * 3600 - ( $s['i'] * 60 ) );
		$id   = solid_core_upsert(
			'post',
			$s['slug'],
			array(
				'post_title'    => $s['title'],
				'post_excerpt'  => $s['dek'],
				'post_content'  => solid_core_story_body( $s['dek'] ),
				'post_date'     => $s['date'] . ' ' . $time,
				'post_date_gmt' => $s['date'] . ' ' . $time,
				'post_category' => array( $cat ),
				'post_author'   => 1,
			),
			array( 'solid_mins' => $s['mins'], 'solid_writer' => $writers[ $s['author'] ] ?? 0 ),
			$s['img']
		);
		$out[] = is_wp_error( $id ) ? $s['slug'] . ': ' . $id->get_error_message() : $id;
	}
	delete_transient( 'solid_search_index' );
	return $out;
}

/* ───────── Pages ───────── */

function solid_core_story_id( $seed, $section, $n ) {
	$list = array_values( array_filter( $seed['stories'], fn( $s ) => $s['section'] === $section ) );
	$p    = get_page_by_path( $list[ $n ]['slug'], OBJECT, 'post' );
	return $p ? (string) $p->ID : '';
}

function solid_core_w( $type, $settings = array(), $anchor = '' ) {
	if ( $anchor ) {
		$settings['_element_id'] = $anchor;
	}
	return array( 'id' => substr( md5( $type . wp_json_encode( $settings ) . wp_rand() ), 0, 7 ), 'elType' => 'widget', 'widgetType' => $type, 'settings' => $settings, 'elements' => array() );
}

function solid_core_elementor_page( $slug, $title, $widgets ) {
	$container = array(
		'id'       => substr( md5( $slug . 'c' ), 0, 7 ),
		'elType'   => 'container',
		'isInner'  => false,
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'solid-flow',
			'padding'       => array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true ),
			'flex_gap'      => array( 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0', 'isLinked' => true ),
		),
		'elements' => $widgets,
	);
	$page = get_page_by_path( $slug );
	$id   = wp_insert_post(
		array(
			'ID'           => $page ? $page->ID : 0,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_title'   => $title,
			'post_content' => '',
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return $id;
	}
	update_post_meta( $id, '_elementor_edit_mode', 'builder' );
	update_post_meta( $id, '_elementor_template_type', 'wp-page' );
	update_post_meta( $id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
	update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
	update_post_meta( $id, '_elementor_data', wp_slash( wp_json_encode( array( $container ) ) ) );
	delete_post_meta( $id, '_elementor_css' );
	delete_post_meta( $id, '_elementor_element_cache' );
	return $id;
}

function solid_core_chips( $subs ) {
	return array_map( fn( $x ) => array( 'label' => $x, 'link' => '#' . solid_slugify( $x ) ), $subs );
}

function solid_core_import_pages( $seed ) {
	$m   = fn( $sec, ...$n ) => array_map( fn( $i ) => solid_core_story_id( $seed, $sec, $i ), $n );
	$ids = array();

	$ids['home'] = solid_core_elementor_page(
		'home',
		'Home',
		array(
			solid_core_w( 'solid-cover', array( 'post' => solid_core_story_id( $seed, 'solid-men', 0 ) ) ),
			solid_core_w(
				'solid-trending',
				array(
					'stories_source' => 'manual',
					'stories_posts'  => array( solid_core_story_id( $seed, 'wealth', 1 ), solid_core_story_id( $seed, 'health', 3 ), solid_core_story_id( $seed, 'lifestyle', 1 ), solid_core_story_id( $seed, 'mind', 0 ), solid_core_story_id( $seed, 'business', 4 ) ),
				)
			),
			solid_core_w( 'solid-spotlight', array( 'stories_source' => 'manual', 'stories_posts' => $m( 'business', 1, 0, 2, 3, 5 ) ) ),
			solid_core_w( 'solid-men' ),
			solid_core_w( 'solid-edit', array( 'stories_source' => 'manual', 'stories_posts' => $m( 'lifestyle', 0, 1, 2, 7, 5 ) ) ),
			solid_core_w( 'solid-growth' ),
			solid_core_w( 'solid-conversations' ),
			solid_core_w( 'solid-hundred' ),
			solid_core_w( 'solid-trio' ),
			solid_core_w( 'solid-newsletter' ),
		)
	);

	$fr  = array( 'About the Recognition', 'Recognition Categories', 'Selection Criteria', 'Honouree Profiles', 'Previous Editions' );
	$ids['the-solid-100'] = solid_core_elementor_page(
		'the-solid-100',
		'The SOLID 100',
		array(
			solid_core_w( 'solid-page-header', array( 'crumb' => 'The SOLID 100', 'title' => 'The SOLID <em>100</em>', 'lede' => 'An annual recognition of one hundred Nigerian men whose work, character and influence are setting the standard for a generation.', 'style' => 'phead--100', 'big' => '100', 'chips' => solid_core_chips( $fr ) ) ),
			solid_core_w( 'solid-text', array( 'heading' => 'About the recognition', 'content' => '<p>The SOLID 100 is not a rich list and it is not a popularity contest. It is a considered record of men doing serious work: building companies, healing patients, teaching children, making art and serving the public with distinction.</p><p>Each year our editors, guided by an independent advisory panel, select one hundred honourees across ten categories. The list is published in full, with a profile of every man on it.</p>' ), 'about-the-recognition' ),
			solid_core_w( 'solid-numbered', array( 'heading' => 'Recognition categories', 'background' => 'band', 'items' => array_map( fn( $c ) => array( 'title' => $c ), $seed['categories100'] ) ), 'recognition-categories' ),
			solid_core_w(
				'solid-criteria',
				array(
					'heading' => 'Selection criteria',
					'items'   => array(
						array( 'title' => 'Achievement', 'text' => 'A sustained record of excellence in his field, measured in outcomes rather than attention.' ),
						array( 'title' => 'Character', 'text' => 'A reputation for integrity among the people who know his work best.' ),
						array( 'title' => 'Influence', 'text' => 'Evidence that he raises the standard for those around him and those coming after.' ),
						array( 'title' => 'Impact', 'text' => 'A contribution that reaches beyond personal success to community and country.' ),
					),
				),
				'selection-criteria'
			),
			solid_core_w( 'solid-people', array( 'heading' => 'Honouree profiles', 'link_label' => 'All SOLID Men', 'link' => array( 'url' => '/solid-men/' ), 'source' => 'solid_honouree' ), 'honouree-profiles' ),
			solid_core_w( 'solid-text', array( 'heading' => 'Previous editions', 'background' => 'band', 'content' => '<p>The inaugural SOLID 100 will be published in 2027. Past editions will be archived here, with every honouree profile available to read in full.</p>', 'button' => 'Nominate a man', 'button_link' => array( 'url' => '/about/#contact' ) ), 'previous-editions' ),
			solid_core_w( 'solid-newsletter' ),
		)
	);

	$ids['conversations'] = solid_core_elementor_page(
		'conversations',
		'SOLID Conversations',
		array(
			solid_core_w( 'solid-page-header', array( 'crumb' => 'SOLID Conversations', 'title' => 'SOLID Conversations', 'lede' => 'Long-form interviews with men worth listening to. Watch, listen or read.', 'chips' => solid_core_chips( array( 'Video Interviews', 'Podcast Episodes', 'Interview Transcripts' ) ) ) ),
			solid_core_w( 'solid-conv-list', array( 'layout' => 'lead', 'offset' => 0 ), 'video-interviews' ),
			solid_core_w( 'solid-conv-list', array( 'layout' => 'grid', 'heading' => 'Latest episodes', 'offset' => 1, 'count' => 3 ), 'podcast-episodes' ),
			solid_core_w( 'solid-post-grid', array( 'heading' => 'Interview transcripts', 'background' => 'band', 'columns' => '3', 'dek' => 'yes', 'kick' => 'sub', 'stories_source' => 'category', 'stories_category' => 'solid-men', 'stories_offset' => 1, 'stories_count' => 3 ), 'interview-transcripts' ),
			solid_core_w( 'solid-newsletter' ),
		)
	);

	$ids['about'] = solid_core_elementor_page(
		'about',
		'About SOLID',
		array(
			solid_core_w( 'solid-page-header', array( 'crumb' => 'About', 'title' => 'About SOLID', 'lede' => 'A platform for ambition. A community for success.', 'chips' => solid_core_chips( array( 'Our Story', 'Mission & Vision', 'Editorial Team', 'Editorial Policy', 'Contact' ) ) ) ),
			solid_core_w( 'solid-text', array( 'heading' => 'Our story', 'drop' => 'yes', 'content' => '<p>SOLID is Nigeria’s online magazine for men who are committed to growth: in business, in life and in themselves. We inspire, inform and celebrate men who dream bigger, work harder and achieve more.</p><p>We started SOLID because the conversation about what it means to be a successful man had become too narrow. Money matters. So do health, family, character and the mark you leave. We cover all of it, with the seriousness it deserves.</p>' ), 'our-story' ),
			solid_core_w( 'solid-statements', array( 'items' => array( array( 'label' => 'Mission', 'text' => 'To equip Nigerian men with the ideas, examples and tools to build lives of substance.' ), array( 'label' => 'Vision', 'text' => 'A generation of men known for what they build, how they lead and who they lift.' ) ) ), 'mission-and-vision' ),
			solid_core_w( 'solid-people', array( 'heading' => 'Editorial team', 'link_label' => '', 'source' => 'solid_writer' ), 'editorial-team' ),
			solid_core_w( 'solid-text', array( 'heading' => 'Editorial policy', 'content' => '<p>Our journalism is independent. Advertisers and partners have no influence over editorial coverage, and sponsored content is always clearly labelled. When we get something wrong, we correct it promptly and transparently.</p><p><a href="/editorial-policy/">Read the full editorial policy</a> · <a href="/corrections-policy/">Corrections policy</a></p>' ), 'editorial-policy' ),
			solid_core_w( 'solid-contact', array( 'heading' => 'Contact', 'items' => array( array( 'label' => 'Editorial', 'email' => 'editorial@solidng.com' ), array( 'label' => 'Partnerships', 'email' => 'partnerships@solidng.com' ), array( 'label' => 'General', 'email' => 'hello@solidng.com' ) ) ), 'contact' ),
		)
	);

	$ids['advertise'] = solid_core_elementor_page(
		'advertise',
		'Advertise with SOLID',
		array(
			solid_core_w( 'solid-page-header', array( 'crumb' => 'Advertise', 'title' => 'Advertise with SOLID', 'lede' => 'Partner. Advertise. Grow. Reach the men shaping Nigeria’s next decade.', 'chips' => solid_core_chips( array( 'Media Kit', 'Advertising Opportunities', 'Brand Partnerships', 'Sponsored Content Enquiries' ) ) ) ),
			solid_core_w( 'solid-stats', array( 'items' => array( array( 'value' => '1M+', 'label' => 'Monthly reach (est.)' ), array( 'value' => '72%', 'label' => 'Male audience' ), array( 'value' => '25–44', 'label' => 'Core age group' ), array( 'value' => 'Lagos, Abuja', 'label' => 'Top cities and beyond' ) ) ), 'media-kit' ),
			solid_core_w( 'solid-ticks', array( 'heading' => 'Why partner with SOLID', 'items' => array_map( fn( $t ) => array( 'text' => $t ), array( 'Highly engaged, aspirational audience', 'Trusted and credible editorial voice', 'Multi-platform visibility across web, social and newsletter', 'Alignment with success, ambition and excellence' ) ) ), 'brand-partnerships' ),
			solid_core_w(
				'solid-offers',
				array(
					'heading'    => 'Advertising opportunities',
					'background' => 'band',
					'items'      => array(
						array( 'title' => 'Display Ads', 'text' => 'Premium placements across the homepage, sections and articles.' ),
						array( 'title' => 'Sponsored Articles', 'text' => 'Clearly labelled stories written with our editorial standards.' ),
						array( 'title' => 'Branded Content', 'text' => 'Custom series and features produced by the SOLID studio.' ),
						array( 'title' => 'Video Placements', 'text' => 'Pre-roll and integrations across SOLID Conversations.' ),
						array( 'title' => 'Newsletter Sponsorship', 'text' => 'Own the Sunday inbox of our most loyal readers.' ),
						array( 'title' => 'Events & Experiences', 'text' => 'Partner on The SOLID 100 and our live gatherings.' ),
					),
				),
				'advertising-opportunities'
			),
			solid_core_w( 'solid-enquiry', array(), 'sponsored-content-enquiries' ),
		)
	);

	// Policy pages: plain pages using the theme's standard page template.
	$sections = array( 'Overview', 'What this covers', 'How we apply it', 'Your choices', 'Changes to this policy', 'Contact us' );
	foreach ( $seed['legal'] as $l ) {
		$body = '';
		foreach ( $sections as $h ) {
			$body .= '<h2>' . $h . '</h2><p>This is placeholder text for the ' . esc_html( strtolower( $l['name'] ) ) . '. The final wording will be supplied by SOLID and reviewed before launch. It sets out, in plain language, how the magazine operates and what readers can expect from us.</p>' . "\n";
		}
		$page             = get_page_by_path( $l['slug'] );
		$ids[ $l['slug'] ] = wp_insert_post( array( 'ID' => $page ? $page->ID : 0, 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $l['slug'], 'post_title' => $l['name'], 'post_content' => $body, 'post_excerpt' => 'Last updated 16 November 2026' ) );
	}

	$page          = get_page_by_path( 'search' );
	$ids['search'] = wp_insert_post( array( 'ID' => $page ? $page->ID : 0, 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'search', 'post_title' => 'Search', 'post_content' => '' ) );
	update_post_meta( $ids['search'], '_wp_page_template', 'page-templates/search-page.php' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $ids['home'] );
	if ( class_exists( '\Elementor\Plugin' ) ) {
		\Elementor\Plugin::$instance->files_manager->clear_cache();
	}
	return $ids;
}

/* ───────── Site settings ───────── */

function solid_core_import_settings() {
	update_option( 'blogname', 'SOLID' );
	update_option( 'blogdescription', 'Grow. Achieve. Thrive.' );
	update_option( 'permalink_structure', '/story/%postname%/' );
	update_option( 'timezone_string', 'Africa/Lagos' );
	update_option( 'date_format', 'j F Y' );
	// Elementor: no default colours or fonts, so the SOLID design is the only source of style.
	update_option( 'elementor_disable_color_schemes', 'yes' );
	update_option( 'elementor_disable_typography_schemes', 'yes' );
	$kit = (int) get_option( 'elementor_active_kit' );
	if ( $kit ) {
		$k = (array) get_post_meta( $kit, '_elementor_page_settings', true );
		$k['space_between_widgets'] = array( 'column' => '0', 'row' => '0', 'isLinked' => true, 'unit' => 'px', 'size' => 0 );
		$k['container_padding']     = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
		$k['container_width']       = array( 'unit' => 'px', 'size' => 1320 );
		$k['default_page_template'] = 'elementor_header_footer';
		update_post_meta( $kit, '_elementor_page_settings', $k );
	}
	flush_rewrite_rules( false );
	return 'ok';
}
