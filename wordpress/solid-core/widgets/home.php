<?php
/**
 * SOLID home page widgets: one widget per block of the Solid One home page.
 */
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/* ───────── Cover story + Latest ───────── */
class Solid_W_Cover extends Solid_Widget {
	protected $icon = 'featured-image';
	public function get_name() { return 'solid-cover'; }
	public function get_title() { return 'SOLID Cover Story'; }

	protected function register_controls() {
		$this->start( 'Cover story' );
		$this->add_control( 'post', array( 'label' => 'Cover story (empty: newest in Cover Stories)', 'type' => Controls_Manager::SELECT2, 'options' => self::post_options(), 'label_block' => true ) );
		$this->text( 'title', 'Headline (optional; use <i>…</i> for italics)', 'The <i>Long</i> Game' );
		$this->text( 'flag', 'Label', 'Cover Story' );
		$this->text( 'issue', 'Issue line', 'Issue 01 · November 2026' );
		$this->text( 'button', 'Button text', 'Read the cover story' );
		$this->text( 'caption', 'Photo caption', 'Kolade Aremu, photographed in Lagos for SOLID' );
		$this->end_controls_section();
		$this->start( 'Latest column' );
		$this->text( 'latest_heading', 'Heading', 'Latest' );
		$this->section_select( 'latest_sections', 'Newest story from each of', array( 'business', 'wealth', 'health', 'lifestyle', 'leadership', 'culture' ), true );
		$this->text( 'latest_label', 'Link text', 'All stories' );
		$this->link_control( 'latest_link', 'Link', '/search/' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$cover = ! empty( $s['post'] ) ? get_post( (int) $s['post'] ) : null;
		if ( ! $cover ) {
			$cs    = get_term_by( 'slug', 'cover-stories', 'category' );
			$cover = solid_latest( $cs ? array( 'cat' => $cs->term_id, 'posts_per_page' => 1 ) : array( 'posts_per_page' => 1 ) )[0] ?? null;
		}
		if ( ! $cover ) {
			return;
		}
		$url    = get_permalink( $cover );
		$title  = '' !== trim( (string) $s['title'] ) ? self::rich( $s['title'] ) : solid_e( get_the_title( $cover ) );
		$latest = array();
		foreach ( (array) $s['latest_sections'] as $slug ) {
			$p = solid_latest( array( 'category_name' => $slug, 'posts_per_page' => 1 ) );
			if ( $p ) {
				$latest[] = $p[0];
			}
		}
		usort( $latest, fn( $a, $b ) => strcmp( $b->post_date, $a->post_date ) );

		echo '<section class="cover wrap">' . "\n";
		echo '<div class="cover__text" data-reveal><p class="cover__flag"><b>' . solid_e( $s['flag'] ) . '</b><span>' . solid_e( $s['issue'] ) . '</span></p>' . "\n";
		echo '<h1 class="cover__t"><a href="' . esc_url( $url ) . '">' . $title . '</a></h1>' . "\n"; // phpcs:ignore
		echo '<p class="cover__dek">' . solid_e( solid_dek( $cover ) ) . '</p>' . "\n";
		echo '<p class="meta"><span>By ' . solid_e( solid_writer_name( $cover ) ) . '</span><span>' . (int) solid_mins( $cover ) . ' min read</span></p>' . "\n";
		echo '<a class="btn btn--ghost" href="' . esc_url( $url ) . '">' . solid_e( $s['button'] ) . ' ' . solid_icon( 'arrow' ) . '</a></div>' . "\n"; // phpcs:ignore
		echo '<a class="cover__img" href="' . esc_url( $url ) . '" tabindex="-1">' . solid_thumb( $cover, 'solid-hero', null, true ) . ( $s['caption'] ? '<span class="cover__cap">' . solid_e( $s['caption'] ) . '</span>' : '' ) . '</a>' . "\n"; // phpcs:ignore
		echo '<aside class="cover__side"><h2 class="rule-h">' . solid_e( $s['latest_heading'] ) . '</h2><ol class="latest">';
		foreach ( $latest as $p ) {
			$sec = solid_post_section( $p );
			echo '<li><span class="latest__m"><a class="kicker" href="' . esc_url( get_term_link( $sec ) ) . '">' . solid_e( $sec->name ) . '</a>' . solid_date( $p, 'j M' ) . '</span><h3><a href="' . esc_url( get_permalink( $p ) ) . '">' . solid_e( get_the_title( $p ) ) . '</a></h3></li>'; // phpcs:ignore
		}
		echo '</ol>' . ( $s['latest_label'] ? '<a class="more" href="' . esc_url( self::url( $s['latest_link'], home_url( '/search/' ) ) ) . '">' . solid_e( $s['latest_label'] ) . ' ' . solid_icon( 'arrow' ) . '</a>' : '' ) . '</aside>' . "\n"; // phpcs:ignore
		echo '</section>';
	}
}

/* ───────── Trending ───────── */
class Solid_W_Trending extends Solid_Widget {
	protected $icon = 'number-field';
	public function get_name() { return 'solid-trending'; }
	public function get_title() { return 'SOLID Trending'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'heading', 'Heading', 'Trending' );
		$this->text( 'text', 'Text', 'What readers are sharing this week' );
		$this->posts_controls( 'stories', 5 );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="trend"><div class="wrap trend__in"><div class="trend__h"><h2>' . solid_e( $s['heading'] ) . '</h2><p>' . solid_e( $s['text'] ) . '</p></div>' . "\n" . '<ol class="trend__l">';
		foreach ( $this->get_stories( $s, 'stories' ) as $i => $p ) {
			$sec = solid_post_section( $p );
			echo '<li><span class="trend__n">' . ( $i + 1 ) . '</span><div><a class="kicker" href="' . esc_url( get_term_link( $sec ) ) . '">' . solid_e( $sec->name ) . '</a><h3><a href="' . esc_url( get_permalink( $p ) ) . '">' . solid_e( get_the_title( $p ) ) . '</a></h3></div></li>';
		}
		echo '</ol></div></section>';
	}
}

/* ───────── Section spotlight (Business Spotlight) ───────── */
class Solid_W_Spotlight extends Solid_Widget {
	protected $icon = 'gallery-justified';
	public function get_name() { return 'solid-spotlight'; }
	public function get_title() { return 'SOLID Section Spotlight'; }

	protected function register_controls() {
		$this->start();
		$this->heading_controls( 'Business Spotlight', 'All Business', '/business/' );
		$this->posts_controls( 'stories', 5, 'business', 0, 'Stories (the first is the lead)' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$posts = $this->get_stories( $s, 'stories' );
		$lead  = array_shift( $posts );
		echo '<section class="wrap blk" data-reveal>' . $this->head( $s ) . "\n"; // phpcs:ignore
		echo '<div class="biz"><div class="biz__lead">' . ( $lead ? solid_card( $lead, 'wide' ) : '' ) . '</div><div class="biz__list">'; // phpcs:ignore
		foreach ( $posts as $p ) {
			echo solid_card( $p, 'row' ); // phpcs:ignore
		}
		echo '</div></div></section>';
	}
}

/* ───────── SOLID Men ───────── */
class Solid_W_Men extends Solid_Widget {
	protected $icon = 'person';
	public function get_name() { return 'solid-men'; }
	public function get_title() { return 'SOLID Men'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'kicker', 'Small line above', 'Portraits of ambition' );
		$this->text( 'heading', 'Heading (use <i>…</i> for italics)', 'SOLID <i>Men</i>' );
		$this->text( 'text', 'Text', 'Profiles of the men defining what it means to build, lead and last.', 'TEXTAREA' );
		$this->text( 'link_label', 'Link text', 'Meet them all' );
		$this->link_control( 'link', 'Link', '/solid-men/' );
		$this->posts_controls( 'stories', 4, 'solid-men', 1 );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="men"><div class="wrap men__in"><div class="men__head" data-reveal><p class="kicker">' . solid_e( $s['kicker'] ) . '</p><h2 class="men__t">' . self::rich( $s['heading'] ) . '</h2><p>' . solid_e( $s['text'] ) . '</p>' . ( $s['link_label'] ? '<a class="more" href="' . esc_url( self::url( $s['link'] ) ) . '">' . solid_e( $s['link_label'] ) . ' ' . solid_icon( 'arrow' ) . '</a>' : '' ) . '</div>' . "\n"; // phpcs:ignore
		echo '<div class="men__grid">';
		foreach ( $this->get_stories( $s, 'stories' ) as $p ) {
			echo solid_card( $p, 'portrait', array( 'meta' => false ) ); // phpcs:ignore
		}
		echo '</div></div></section>';
	}
}

/* ───────── The Lifestyle Edit ───────── */
class Solid_W_Edit extends Solid_Widget {
	protected $icon = 'gallery-masonry';
	public function get_name() { return 'solid-edit'; }
	public function get_title() { return 'SOLID Feature Edit'; }

	protected function register_controls() {
		$this->start();
		$this->heading_controls( 'The Lifestyle Edit', 'All Lifestyle', '/lifestyle/' );
		$this->posts_controls( 'stories', 5, 'lifestyle', 0, 'Stories (the first is the tall feature)' );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$posts = $this->get_stories( $s, 'stories' );
		$lead  = array_shift( $posts );
		echo '<section class="wrap blk" data-reveal>' . $this->head( $s ) . "\n"; // phpcs:ignore
		echo '<div class="edit">' . ( $lead ? solid_card( $lead, 'over' ) : '' ) . '<div class="edit__g">'; // phpcs:ignore
		foreach ( $posts as $p ) {
			echo solid_card( $p, 'std', array( 'meta' => false ) ); // phpcs:ignore
		}
		echo '</div></div></section>';
	}
}

/* ───────── Growth & Development (section columns) ───────── */
class Solid_W_Growth extends Solid_Widget {
	protected $icon = 'column';
	public function get_name() { return 'solid-growth'; }
	public function get_title() { return 'SOLID Section Columns'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'heading', 'Heading', 'Growth & Development' );
		$this->text( 'kicker', 'Small line above', 'Mind · Leadership · Wealth · Health' );
		$this->section_select( 'sections', 'Sections (one column each)', array( 'mind', 'leadership', 'wealth', 'health' ), true );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="growth"><div class="wrap" data-reveal>' . solid_sec_head( self::rich( $s['heading'] ), '', '', self::rich( $s['kicker'] ) ) . "\n" . '<div class="growth__g">'; // phpcs:ignore
		foreach ( (array) $s['sections'] as $slug ) {
			$sec  = solid_section_by_slug( $slug );
			$list = $sec ? solid_latest( array( 'cat' => $sec->term_id, 'posts_per_page' => 3 ) ) : array();
			if ( ! $list ) {
				continue;
			}
			echo '<div class="gcol"><a class="gcol__h" href="' . esc_url( get_term_link( $sec ) ) . '"><span>' . solid_e( $sec->name ) . '</span>' . solid_icon( 'arrow' ) . '</a>' . solid_card( $list[0], 'std' ) . '<ul class="gcol__l">'; // phpcs:ignore
			foreach ( array_slice( $list, 1 ) as $p ) {
				echo '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . solid_e( get_the_title( $p ) ) . '</a></li>';
			}
			echo '</ul></div>';
		}
		echo '</div></div></section>';
	}
}

/* ───────── SOLID Conversations (home block) ───────── */
class Solid_W_Conversations extends Solid_Widget {
	protected $icon = 'youtube';
	public function get_name() { return 'solid-conversations'; }
	public function get_title() { return 'SOLID Conversations'; }

	protected function register_controls() {
		$this->start();
		$this->heading_controls( 'SOLID Conversations', 'All episodes', '/conversations/' );
		$this->add_control( 'count', array( 'label' => 'Number of episodes', 'type' => Controls_Manager::NUMBER, 'default' => 4, 'min' => 1, 'max' => 8 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$list = solid_core_conversations( (int) $s['count'] );
		$main = array_shift( $list );
		if ( ! $main ) {
			return;
		}
		$v = solid_conv( $main );
		echo '<section class="wrap blk" data-reveal>' . $this->head( $s ) . "\n"; // phpcs:ignore
		echo '<div class="convo"><a class="convo__main" href="' . esc_url( $v['url'] ) . '">' . solid_thumb( $main, 'solid-hero', '' ) . '<span class="play">' . solid_icon( 'play' ) . '</span><span class="convo__txt"><span class="kicker">' . solid_e( $v['kind'] . ' · Ep. ' . $v['ep'] . ' · ' . $v['len'] ) . '</span><b>' . solid_e( $v['title'] ) . '</b><span>with ' . solid_e( $v['guest'] ) . '</span></span></a>' . "\n" . '<ol class="convo__l">'; // phpcs:ignore
		foreach ( $list as $p ) {
			$e = solid_conv( $p );
			echo '<li><a href="' . esc_url( $e['url'] ) . '"><span class="convo__ep">Ep. ' . solid_e( $e['ep'] ) . '</span><span class="convo__b"><span class="kicker">' . solid_e( $e['kind'] ) . '</span><b>' . solid_e( $e['title'] ) . '</b><span>with ' . solid_e( $e['guest'] ) . ' · ' . solid_e( $e['len'] ) . '</span></span><span class="convo__ic">' . solid_icon( solid_conv_is_audio( $e ) ? 'mic' : 'play' ) . '</span></a></li>'; // phpcs:ignore
		}
		echo '</ol></div></section>';
	}
}

function solid_core_conversations( $n, $offset = 0 ) {
	return get_posts(
		array(
			'post_type'      => 'solid_conversation',
			'posts_per_page' => $n,
			'offset'         => $offset,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			'no_found_rows'  => true,
		)
	);
}

function solid_core_honourees( $n = -1 ) {
	return get_posts(
		array(
			'post_type'      => 'solid_honouree',
			'posts_per_page' => $n,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'no_found_rows'  => true,
		)
	);
}

/* ───────── The SOLID 100 (home band) ───────── */
class Solid_W_Hundred extends Solid_Widget {
	protected $icon = 'person';
	public function get_name() { return 'solid-hundred'; }
	public function get_title() { return 'SOLID 100 Feature'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'kicker', 'Small line above', 'The annual list' );
		$this->text( 'heading', 'Heading (use <i>…</i> for italics)', 'The SOLID <i>100</i>' );
		$this->text( 'text', 'Text', 'One hundred Nigerian men whose work, character and influence are setting the standard for a generation.', 'TEXTAREA' );
		$this->text( 'button', 'Button text', 'Explore the list' );
		$this->link_control( 'link', 'Button link', '/the-solid-100/' );
		$this->add_control( 'count', array( 'label' => 'Number of men', 'type' => Controls_Manager::NUMBER, 'default' => 8, 'min' => 1, 'max' => 24 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="s100"><div class="wrap s100__in"><div class="s100__copy" data-reveal><p class="kicker">' . solid_e( $s['kicker'] ) . '</p><h2 class="s100__t">' . self::rich( $s['heading'] ) . '</h2><p>' . solid_e( $s['text'] ) . '</p><a class="btn" href="' . esc_url( self::url( $s['link'] ) ) . '">' . solid_e( $s['button'] ) . ' ' . solid_icon( 'arrow' ) . '</a></div>' . "\n" . '<div class="s100__faces">'; // phpcs:ignore
		foreach ( solid_core_honourees( (int) $s['count'] ) as $p ) {
			echo '<figure>' . solid_thumb( $p, 'large', '' ) . '<figcaption>' . solid_e( $p->post_title ) . '<span>' . solid_e( get_post_meta( $p->ID, 'solid_category', true ) ) . '</span></figcaption></figure>'; // phpcs:ignore
		}
		echo '</div></div></section>';
	}
}

/* ───────── Three section columns (Family · Culture · Impact) ───────── */
class Solid_W_Trio extends Solid_Widget {
	protected $icon = 'gallery-grid';
	public function get_name() { return 'solid-trio'; }
	public function get_title() { return 'SOLID Section Lists'; }

	protected function register_controls() {
		$this->start();
		$this->section_select( 'sections', 'Sections (one column each)', array( 'family', 'culture', 'impact' ), true );
		$this->add_control( 'count', array( 'label' => 'Stories per section', 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 6 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="wrap blk tri" data-reveal>';
		foreach ( (array) $s['sections'] as $slug ) {
			$sec = solid_section_by_slug( $slug );
			if ( ! $sec ) {
				continue;
			}
			echo '<div class="tri__c"><a class="gcol__h" href="' . esc_url( get_term_link( $sec ) ) . '"><span>' . solid_e( $sec->name ) . '</span>' . solid_icon( 'arrow' ) . '</a>'; // phpcs:ignore
			foreach ( solid_latest( array( 'cat' => $sec->term_id, 'posts_per_page' => (int) $s['count'] ) ) as $p ) {
				echo solid_card( $p, 'row', array( 'meta' => false ) ); // phpcs:ignore
			}
			echo '</div>';
		}
		echo '</section>';
	}
}

/* ───────── Newsletter ───────── */
class Solid_W_Newsletter extends Solid_Widget {
	protected $icon = 'mail';
	public function get_name() { return 'solid-newsletter'; }
	public function get_title() { return 'SOLID Newsletter'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'kicker', 'Newsletter name', 'The SOLID Brief' );
		$this->text( 'title', 'Heading', 'Stay ahead.' );
		$this->text( 'text', 'Text', 'The week’s sharpest stories on business, money and living well, delivered every Sunday morning.', 'TEXTAREA' );
		$this->text( 'button', 'Button text', 'Subscribe' );
		$this->text( 'note', 'Small print', 'No spam. Unsubscribe at any time.' );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo solid_newsletter( array_intersect_key( $s, array_flip( array( 'kicker', 'title', 'text', 'button', 'note' ) ) ) ); // phpcs:ignore
	}
}
