<?php
/**
 * Base class for the SOLID Elementor widgets. Loaded when Elementor registers widgets.
 */
defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

abstract class Solid_Widget extends \Elementor\Widget_Base {

	protected $icon = 'posts-grid';

	public function get_categories() {
		return array( 'solid' );
	}

	public function get_icon() {
		return 'eicon-' . $this->icon;
	}

	public function get_keywords() {
		return array( 'solid', 'magazine' );
	}

	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	protected function is_dynamic_content(): bool {
		return true;
	}

	/* ── Control helpers ── */

	private $section_n = 0;

	protected function start( $label = 'Content' ) {
		$this->start_controls_section( 'content_' . ( ++$this->section_n ), array( 'label' => $label ) );
	}

	protected function text( $id, $label, $default = '', $type = 'TEXT' ) {
		$this->add_control(
			$id,
			array(
				'label'       => $label,
				'type'        => constant( Controls_Manager::class . '::' . $type ),
				'default'     => $default,
				'label_block' => true,
				'dynamic'     => array( 'active' => true ),
			)
		);
	}

	protected function toggle( $id, $label, $default = '' ) {
		$this->add_control( $id, array( 'label' => $label, 'type' => Controls_Manager::SWITCHER, 'default' => $default ? 'yes' : '', 'return_value' => 'yes' ) );
	}

	protected function choice( $id, $label, $options, $default ) {
		$this->add_control( $id, array( 'label' => $label, 'type' => Controls_Manager::SELECT, 'options' => $options, 'default' => $default ) );
	}

	protected function section_select( $id, $label, $default = '', $multiple = false ) {
		$this->add_control(
			$id,
			array(
				'label'       => $label,
				'type'        => $multiple ? Controls_Manager::SELECT2 : Controls_Manager::SELECT,
				'multiple'    => $multiple,
				'options'     => self::section_options( ! $multiple ),
				'default'     => $default,
				'label_block' => true,
			)
		);
	}

	protected function band_control( $default = '' ) {
		$this->choice( 'background', 'Background', array( '' => 'None', 'band' => 'Tinted band' ), $default );
	}

	/** Story picker: latest from a section, or hand-picked stories. */
	protected function posts_controls( $p, $count, $cat = '', $offset = 0, $label = 'Stories' ) {
		$this->add_control( $p . '_heading', array( 'label' => $label, 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->choice( $p . '_source', 'Source', array( 'category' => 'Latest from a section', 'manual' => 'Choose stories' ), 'category' );
		$this->add_control( $p . '_category', array( 'label' => 'Section', 'type' => Controls_Manager::SELECT, 'options' => self::section_options( true ), 'default' => $cat, 'condition' => array( $p . '_source' => 'category' ) ) );
		$this->add_control( $p . '_offset', array( 'label' => 'Skip the newest', 'type' => Controls_Manager::NUMBER, 'min' => 0, 'default' => $offset, 'condition' => array( $p . '_source' => 'category' ) ) );
		$this->add_control( $p . '_count', array( 'label' => 'Number of stories', 'type' => Controls_Manager::NUMBER, 'min' => 1, 'max' => 24, 'default' => $count, 'condition' => array( $p . '_source' => 'category' ) ) );
		$this->add_control(
			$p . '_posts',
			array(
				'label'       => 'Stories, in order',
				'type'        => Controls_Manager::SELECT2,
				'multiple'    => true,
				'options'     => self::post_options(),
				'label_block' => true,
				'condition'   => array( $p . '_source' => 'manual' ),
			)
		);
	}

	protected function get_stories( $s, $p ) {
		if ( 'manual' === ( $s[ $p . '_source' ] ?? '' ) ) {
			$ids = array_filter( array_map( 'intval', (array) ( $s[ $p . '_posts' ] ?? array() ) ) );
			return $ids ? get_posts( array( 'post__in' => $ids, 'orderby' => 'post__in', 'posts_per_page' => count( $ids ), 'ignore_sticky_posts' => true ) ) : array();
		}
		$args = array(
			'posts_per_page'      => max( 1, (int) ( $s[ $p . '_count' ] ?? 4 ) ),
			'offset'              => max( 0, (int) ( $s[ $p . '_offset' ] ?? 0 ) ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		);
		if ( ! empty( $s[ $p . '_category' ] ) ) {
			$args['category_name'] = $s[ $p . '_category' ];
		}
		return get_posts( $args );
	}

	public static function post_options() {
		static $o = null;
		if ( null === $o ) {
			$o = array();
			foreach ( get_posts( array( 'posts_per_page' => 400, 'no_found_rows' => true ) ) as $p ) {
				$o[ $p->ID ] = html_entity_decode( get_the_title( $p ), ENT_QUOTES, 'UTF-8' );
			}
		}
		return $o;
	}

	public static function section_options( $with_all = false ) {
		$o = $with_all ? array( '' => 'All sections' ) : array();
		foreach ( solid_sections() as $t ) {
			$o[ $t->slug ] = $t->name;
		}
		return $o;
	}

	/* ── Render helpers ── */

	/** Headline text that may carry <i>, <em>, <b> or <br>. */
	protected static function rich( $s ) {
		return wp_kses( (string) $s, array( 'i' => array(), 'em' => array(), 'b' => array(), 'strong' => array(), 'br' => array() ) );
	}

	protected static function url( $link, $fallback = '' ) {
		$u = is_array( $link ) ? ( $link['url'] ?? '' ) : (string) $link;
		return '' !== $u ? $u : $fallback;
	}

	protected function link_control( $id, $label, $default = '' ) {
		$this->add_control( $id, array( 'label' => $label, 'type' => Controls_Manager::URL, 'default' => array( 'url' => $default ), 'label_block' => true, 'dynamic' => array( 'active' => true ) ) );
	}

	protected function head( $s, $link_fallback = '' ) {
		$href = ! empty( $s['link_label'] ) ? self::url( $s['link'] ?? '', $link_fallback ) : '';
		return solid_sec_head( self::rich( $s['heading'] ?? '' ), $href, esc_html( $s['link_label'] ?? '' ), self::rich( $s['kicker'] ?? '' ) );
	}

	protected function heading_controls( $heading, $label = '', $link = '', $kicker = null ) {
		$this->text( 'heading', 'Heading', $heading );
		if ( null !== $kicker ) {
			$this->text( 'kicker', 'Small line above', $kicker );
		}
		$this->text( 'link_label', 'Link text (empty hides the link)', $label );
		$this->link_control( 'link', 'Link', $link );
	}
}
