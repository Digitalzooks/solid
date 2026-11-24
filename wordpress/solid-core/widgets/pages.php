<?php
/**
 * SOLID page widgets: the building blocks of The SOLID 100, Conversations, About and Advertise pages.
 */
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

/* ───────── Page header ───────── */
class Solid_W_Page_Header extends Solid_Widget {
	protected $icon = 'header';
	public function get_name() { return 'solid-page-header'; }
	public function get_title() { return 'SOLID Page Header'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'crumb', 'Breadcrumb (empty: page title)', '' );
		$this->text( 'title', 'Title (empty: page title; use <em>…</em> for the red accent)', '' );
		$this->text( 'lede', 'Introduction', '', 'TEXTAREA' );
		$this->choice( 'style', 'Style', array( '' => 'Standard', 'phead--100' => 'SOLID 100 (large number behind)' ), '' );
		$this->text( 'big', 'Large background number', '100' );
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => 'Label', 'type' => Controls_Manager::TEXT, 'default' => 'Section' ) );
		$r->add_control( 'link', array( 'label' => 'Link (e.g. #our-story)', 'type' => Controls_Manager::TEXT, 'default' => '#' ) );
		$this->add_control( 'chips', array( 'label' => 'Quick links', 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ label }}}', 'default' => array() ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$title = '' !== trim( (string) $s['title'] ) ? self::rich( $s['title'] ) : solid_e( get_the_title() );
		$chips = array();
		foreach ( (array) $s['chips'] as $c ) {
			$chips[] = array( $c['label'], $c['link'] );
		}
		echo solid_phead( // phpcs:ignore
			array(
				'cls'   => $s['style'],
				'crumb' => solid_e( '' !== trim( (string) $s['crumb'] ) ? $s['crumb'] : get_the_title() ),
				'title' => $title,
				'lede'  => solid_e( $s['lede'] ),
				'chips' => $chips,
				'extra' => 'phead--100' === $s['style'] ? '<span class="phead__big" aria-hidden="true">' . solid_e( $s['big'] ) . '</span>' : '',
			)
		);
	}
}

/* ───────── Heading + text ───────── */
class Solid_W_Text extends Solid_Widget {
	protected $icon = 'text';
	public function get_name() { return 'solid-text'; }
	public function get_title() { return 'SOLID Heading and Text'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'heading', 'Heading', 'Our story' );
		$this->add_control( 'content', array( 'label' => 'Text', 'type' => Controls_Manager::WYSIWYG, 'default' => '<p>Text goes here.</p>' ) );
		$this->toggle( 'drop', 'Drop capital on the first paragraph' );
		$this->band_control();
		$this->text( 'button', 'Button text (optional)', '' );
		$this->link_control( 'button_link', 'Button link' );
		$this->choice( 'button_style', 'Button style', array( 'btn--red' => 'Red', 'btn--ghost' => 'Outline' ), 'btn--red' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$body = wp_kses_post( $s['content'] );
		if ( 'yes' === $s['drop'] ) {
			$body = preg_replace( '/<p(\s|>)/', '<p class="drop"$1', $body, 1 );
		}
		if ( $s['button'] ) {
			$body .= '<p><a class="btn ' . esc_attr( $s['button_style'] ) . '" href="' . esc_url( self::url( $s['button_link'] ) ) . '">' . solid_e( $s['button'] ) . ' ' . solid_icon( 'arrow' ) . '</a></p>';
		}
		$head = solid_sec_head( self::rich( $s['heading'] ) );
		if ( 'band' === $s['background'] ) {
			echo '<section class="band"><div class="wrap split">' . $head . '<div class="prose">' . $body . '</div></div></section>'; // phpcs:ignore
		} else {
			echo '<section class="wrap pblock split"><div>' . $head . '</div><div class="prose">' . $body . '</div></section>'; // phpcs:ignore
		}
	}
}

/** Shared: a heading, then a repeater of items, in a plain or tinted band. */
abstract class Solid_List_Widget extends Solid_Widget {
	abstract protected function items_html( $items );
	protected function item_fields( Repeater $r ) {
		$r->add_control( 'title', array( 'label' => 'Title', 'type' => Controls_Manager::TEXT, 'default' => 'Title', 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => 'Text', 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
	}
	protected function list_controls( $heading, $defaults, $band = '' ) {
		$this->start();
		$this->text( 'heading', 'Heading', $heading );
		$this->band_control( $band );
		$r = new Repeater();
		$this->item_fields( $r );
		$this->add_control( 'items', array( 'label' => 'Items', 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ title }}}', 'default' => $defaults ) );
		$this->end_controls_section();
	}
	protected function render() {
		$s     = $this->get_settings_for_display();
		$inner = solid_sec_head( self::rich( $s['heading'] ) ) . $this->items_html( (array) $s['items'] );
		echo 'band' === $s['background'] ? '<section class="band"><div class="wrap">' . $inner . '</div></section>' : '<section class="wrap pblock">' . $inner . '</section>'; // phpcs:ignore
	}
}

/* ───────── Numbered list (Recognition categories) ───────── */
class Solid_W_Numbered extends Solid_List_Widget {
	protected $icon = 'bullet-list';
	public function get_name() { return 'solid-numbered'; }
	public function get_title() { return 'SOLID Numbered List'; }
	protected function item_fields( Repeater $r ) {
		$r->add_control( 'title', array( 'label' => 'Item', 'type' => Controls_Manager::TEXT, 'default' => 'Item', 'label_block' => true ) );
	}
	protected function register_controls() {
		$this->list_controls( 'Recognition categories', array( array( 'title' => 'Business & Enterprise' ), array( 'title' => 'Technology' ) ), 'band' );
	}
	protected function items_html( $items ) {
		$h = '<ol class="cats">';
		foreach ( $items as $i => $it ) {
			$h .= '<li><span>' . sprintf( '%02d', $i + 1 ) . '</span>' . solid_e( $it['title'] ) . '</li>';
		}
		return $h . '</ol>';
	}
}

/* ───────── Criteria (numbered cards) ───────── */
class Solid_W_Criteria extends Solid_List_Widget {
	protected $icon = 'number-field';
	public function get_name() { return 'solid-criteria'; }
	public function get_title() { return 'SOLID Criteria'; }
	protected function register_controls() {
		$this->list_controls( 'Selection criteria', array( array( 'title' => 'Achievement', 'text' => 'A sustained record of excellence in his field.' ) ) );
	}
	protected function items_html( $items ) {
		$h = '<div class="grid grid-4 crit">';
		foreach ( $items as $i => $it ) {
			$h .= '<div class="crit__i"><span class="crit__n">' . ( $i + 1 ) . '</span><h3>' . solid_e( $it['title'] ) . '</h3><p>' . solid_e( $it['text'] ) . '</p></div>';
		}
		return $h . '</div>';
	}
}

/* ───────── Opportunities (numbered cards in three columns) ───────── */
class Solid_W_Offers extends Solid_List_Widget {
	protected $icon = 'info-box';
	public function get_name() { return 'solid-offers'; }
	public function get_title() { return 'SOLID Numbered Cards'; }
	protected function register_controls() {
		$this->list_controls( 'Advertising opportunities', array( array( 'title' => 'Display Ads', 'text' => 'Premium placements across the homepage, sections and articles.' ) ), 'band' );
	}
	protected function items_html( $items ) {
		$h = '<div class="grid grid-3 opps">';
		foreach ( $items as $i => $it ) {
			$h .= '<div class="opp"><span class="opp__n">' . sprintf( '%02d', $i + 1 ) . '</span><h3>' . solid_e( $it['title'] ) . '</h3><p>' . solid_e( $it['text'] ) . '</p></div>';
		}
		return $h . '</div>';
	}
}

/* ───────── People: honourees or writers ───────── */
class Solid_W_People extends Solid_Widget {
	protected $icon = 'person';
	public function get_name() { return 'solid-people'; }
	public function get_title() { return 'SOLID People'; }

	protected function register_controls() {
		$this->start();
		$this->heading_controls( 'Honouree profiles', 'All SOLID Men', '/solid-men/' );
		$this->choice( 'source', 'People', array( 'solid_honouree' => 'SOLID 100 honourees', 'solid_writer' => 'Writers (editorial team)' ), 'solid_honouree' );
		$this->add_control( 'count', array( 'label' => 'How many (0 = all)', 'type' => Controls_Manager::NUMBER, 'default' => 0, 'min' => 0 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$n      = (int) $s['count'] ? (int) $s['count'] : -1;
		$people = 'solid_writer' === $s['source']
			? get_posts( array( 'post_type' => 'solid_writer', 'posts_per_page' => $n, 'orderby' => array( 'menu_order' => 'ASC', 'date' => 'ASC' ), 'no_found_rows' => true ) )
			: solid_core_honourees( $n );
		echo '<section class="wrap pblock">' . $this->head( $s ) . '<div class="grid grid-4">'; // phpcs:ignore
		foreach ( $people as $p ) {
			echo solid_person( $p ); // phpcs:ignore
		}
		echo '</div></section>';
	}
}

/* ───────── Two statements (Mission & Vision) ───────── */
class Solid_W_Statements extends Solid_Widget {
	protected $icon = 'blockquote';
	public function get_name() { return 'solid-statements'; }
	public function get_title() { return 'SOLID Statements'; }

	protected function register_controls() {
		$this->start();
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => 'Label', 'type' => Controls_Manager::TEXT, 'default' => 'Mission' ) );
		$r->add_control( 'text', array( 'label' => 'Statement', 'type' => Controls_Manager::TEXTAREA, 'default' => '' ) );
		$this->add_control( 'items', array( 'label' => 'Statements', 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ label }}}', 'default' => array( array( 'label' => 'Mission', 'text' => '' ), array( 'label' => 'Vision', 'text' => '' ) ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="band"><div class="wrap grid grid-2 mv">';
		foreach ( (array) $s['items'] as $it ) {
			echo '<div><p class="kicker">' . solid_e( $it['label'] ) . '</p><h3>' . solid_e( $it['text'] ) . '</h3></div>';
		}
		echo '</div></section>';
	}
}

/* ───────── Contact addresses ───────── */
class Solid_W_Contact extends Solid_Widget {
	protected $icon = 'envelope';
	public function get_name() { return 'solid-contact'; }
	public function get_title() { return 'SOLID Contact'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'heading', 'Heading', 'Contact' );
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => 'Label', 'type' => Controls_Manager::TEXT, 'default' => 'General' ) );
		$r->add_control( 'email', array( 'label' => 'Email', 'type' => Controls_Manager::TEXT, 'default' => 'hello@solidng.com' ) );
		$this->add_control( 'items', array( 'label' => 'Addresses', 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ label }}}', 'default' => array( array( 'label' => 'General', 'email' => 'hello@solidng.com' ) ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="band"><div class="wrap split">' . solid_sec_head( self::rich( $s['heading'] ) ) . '<div class="contact">'; // phpcs:ignore
		foreach ( (array) $s['items'] as $it ) {
			$e = sanitize_email( $it['email'] );
			echo '<div><p class="kicker">' . solid_e( $it['label'] ) . '</p><a href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a></div>';
		}
		echo '</div></div></section>';
	}
}

/* ───────── Stats (Media kit) ───────── */
class Solid_W_Stats extends Solid_Widget {
	protected $icon = 'counter';
	public function get_name() { return 'solid-stats'; }
	public function get_title() { return 'SOLID Stats'; }

	protected function register_controls() {
		$this->start();
		$r = new Repeater();
		$r->add_control( 'value', array( 'label' => 'Figure', 'type' => Controls_Manager::TEXT, 'default' => '1M+' ) );
		$r->add_control( 'label', array( 'label' => 'Label', 'type' => Controls_Manager::TEXT, 'default' => 'Monthly reach' ) );
		$this->add_control( 'items', array( 'label' => 'Figures', 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ value }}}', 'default' => array( array( 'value' => '1M+', 'label' => 'Monthly reach (est.)' ) ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="wrap pblock"><div class="stats">';
		foreach ( (array) $s['items'] as $it ) {
			echo '<div class="stat"><b>' . solid_e( $it['value'] ) . '</b><span>' . solid_e( $it['label'] ) . '</span></div>';
		}
		echo '</div></section>';
	}
}

/* ───────── Heading + tick list ───────── */
class Solid_W_Ticks extends Solid_Widget {
	protected $icon = 'check-circle';
	public function get_name() { return 'solid-ticks'; }
	public function get_title() { return 'SOLID Tick List'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'heading', 'Heading', 'Why partner with SOLID' );
		$r = new Repeater();
		$r->add_control( 'text', array( 'label' => 'Point', 'type' => Controls_Manager::TEXT, 'default' => 'Point', 'label_block' => true ) );
		$this->add_control( 'items', array( 'label' => 'Points', 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ text }}}', 'default' => array( array( 'text' => 'Highly engaged, aspirational audience' ) ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		echo '<section class="wrap pblock split"><div>' . solid_sec_head( self::rich( $s['heading'] ) ) . '</div><ul class="ticks">'; // phpcs:ignore
		foreach ( (array) $s['items'] as $it ) {
			echo '<li>' . solid_e( $it['text'] ) . '</li>';
		}
		echo '</ul></section>';
	}
}

/* ───────── Enquiry form ───────── */
class Solid_W_Enquiry extends Solid_Widget {
	protected $icon = 'form-horizontal';
	public function get_name() { return 'solid-enquiry'; }
	public function get_title() { return 'SOLID Enquiry Form'; }

	protected function register_controls() {
		$this->start();
		$this->text( 'heading', 'Heading', 'Partner with SOLID today' );
		$this->text( 'text', 'Text', 'Let’s create impact together. Tell us about your brand and our partnerships team will be in touch.', 'TEXTAREA' );
		$this->text( 'email', 'Email address', 'partnerships@solidng.com' );
		$this->text( 'options', 'Interest options (one per line)', "Display advertising\nSponsored content\nBrand partnership\nNewsletter sponsorship\nEvents", 'TEXTAREA' );
		$this->text( 'button', 'Button text', 'Request the media kit' );
		$this->choice( 'form', 'Fluent Form', solid_ff_options( 'Site default (Customise → Newsletter and enquiry forms)' ), '' );
		$this->end_controls_section();
	}

	protected function render() {
		$s    = $this->get_settings_for_display();
		$e    = sanitize_email( $s['email'] );
		$opts = '';
		foreach ( array_filter( array_map( 'trim', explode( "\n", (string) $s['options'] ) ) ) as $o ) {
			$opts .= '<option>' . solid_e( $o ) . '</option>';
		}
		echo '<section class="wrap pblock split"><div>' . solid_sec_head( self::rich( $s['heading'] ) ) . '<p class="phead__l">' . solid_e( $s['text'] ) . '</p><p><a class="btn btn--ghost" href="mailto:' . esc_attr( $e ) . '">' . esc_html( $e ) . '</a></p></div>' . "\n"; // phpcs:ignore
		$ff = solid_ff_render( 'enquiry', $s['form'] ?? 0 );
		if ( $ff ) {
			echo '<div class="ff-solid ff-solid--enq">' . $ff . '</div></section>'; // phpcs:ignore
			return;
		}
		echo '<form class="form" data-newsletter><label>Name<input type="text" required></label><label>Company<input type="text"></label><label>Work email<input type="email" required></label><label>Interested in<select>' . $opts . '</select></label><label class="form__full">Message<textarea rows="4"></textarea></label><button class="btn btn--red" type="submit">' . solid_e( $s['button'] ) . '</button></form></section>'; // phpcs:ignore
	}
}

/* ───────── Conversations list (lead episode or grid) ───────── */
class Solid_W_Conv_List extends Solid_Widget {
	protected $icon = 'video-playlist';
	public function get_name() { return 'solid-conv-list'; }
	public function get_title() { return 'SOLID Episodes'; }

	protected function register_controls() {
		$this->start();
		$this->choice( 'layout', 'Layout', array( 'lead' => 'One large episode', 'grid' => 'Grid of episodes' ), 'grid' );
		$this->text( 'heading', 'Heading (grid)', 'Latest episodes' );
		$this->add_control( 'offset', array( 'label' => 'Skip the newest', 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 0 ) );
		$this->add_control( 'count', array( 'label' => 'Number of episodes', 'type' => Controls_Manager::NUMBER, 'default' => 3, 'min' => 1, 'max' => 24 ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'lead' === $s['layout'] ) {
			$p = solid_core_conversations( 1, (int) $s['offset'] );
			echo '<section class="wrap pblock">' . ( $p ? solid_conv_card( $p[0], 'conv--lead', 'solid-hero' ) : '' ) . '</section>'; // phpcs:ignore
			return;
		}
		echo '<section class="wrap pblock">' . ( $s['heading'] ? solid_sec_head( self::rich( $s['heading'] ) ) : '' ) . '<div class="grid grid-3">'; // phpcs:ignore
		foreach ( solid_core_conversations( (int) $s['count'], (int) $s['offset'] ) as $p ) {
			echo solid_conv_card( $p ); // phpcs:ignore
		}
		echo '</div></section>';
	}
}

/* ───────── Story grid ───────── */
class Solid_W_Post_Grid extends Solid_Widget {
	protected $icon = 'posts-grid';
	public function get_name() { return 'solid-post-grid'; }
	public function get_title() { return 'SOLID Story Grid'; }

	protected function register_controls() {
		$this->start();
		$this->heading_controls( 'Latest stories', '', '' );
		$this->band_control();
		$this->choice( 'columns', 'Columns', array( '2' => '2', '3' => '3', '4' => '4' ), '3' );
		$this->toggle( 'dek', 'Show standfirst', 'yes' );
		$this->choice( 'kick', 'Label above title', array( 'sub' => 'Topic', 'section' => 'Section' ), 'sub' );
		$this->posts_controls( 'stories', 3, '', 0 );
		$this->end_controls_section();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$cards = '';
		foreach ( $this->get_stories( $s, 'stories' ) as $p ) {
			$cards .= solid_card( $p, 'std', array( 'dek' => 'yes' === $s['dek'], 'kick' => $s['kick'] ) );
		}
		$inner = $this->head( $s ) . '<div class="grid grid-' . (int) $s['columns'] . '">' . $cards . '</div>';
		echo 'band' === $s['background'] ? '<section class="band"><div class="wrap">' . $inner . '</div></section>' : '<section class="wrap pblock">' . $inner . '</section>'; // phpcs:ignore
	}
}
