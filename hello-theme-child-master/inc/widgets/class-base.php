<?php
/**
 * Base class for MiRenova widgets: shared controls and render helpers.
 *
 * Every widget renders a complete section (background, overlays, decor, content),
 * so a page is simply a stack of full-width containers with one MiRenova widget each.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;

abstract class MRN_Widget_Base extends Widget_Base {

	/** @var int render attribute counter */
	private $attr_i = 0;

	public function get_categories() {
		return [ 'mirenova' ];
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn' ];
	}

	public function get_style_depends() {
		return [ 'mrn' ];
	}

	/** Lean DOM: no extra .elementor-widget-container div. */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/** Static output can be cached by Elementor (forms/menus/year override this). */
	protected function is_dynamic_content(): bool {
		return false;
	}

	/* ================================================================== */
	/* Small control helpers                                              */
	/* ================================================================== */

	protected static function tags( $with_inline = true ) {
		$t = [
			'h1' => 'H1',
			'h2' => 'H2',
			'h3' => 'H3',
			'h4' => 'H4',
			'h5' => 'H5',
			'h6' => 'H6',
			'div' => 'div',
			'p'  => 'p',
		];
		if ( $with_inline ) {
			$t['span'] = 'span';
		}
		return $t;
	}

	protected static function link_types() {
		return [
			'url'      => __( 'Custom link', 'mirenova' ),
			'phone'    => __( 'Phone (global)', 'mirenova' ),
			'email'    => __( 'E-mail (global)', 'mirenova' ),
			'whatsapp' => __( 'WhatsApp (global)', 'mirenova' ),
			'none'     => __( 'No link', 'mirenova' ),
		];
	}

	protected static function columns( $auto = true, $max = 6 ) {
		$o = $auto ? [ 'auto' => __( 'Auto (fit items)', 'mirenova' ) ] : [];
		for ( $i = 1; $i <= $max; $i++ ) {
			$o[ (string) $i ] = (string) $i;
		}
		return $o;
	}

	/** Tokens hint shown under text controls. */
	protected static function tokens_hint() {
		return __( 'Global tokens: {phone} {email} {whatsapp} {hours} {year}', 'mirenova' );
	}

	/**
	 * WYSIWYG control (every visible text on the site is a rich-text field).
	 *
	 * @param Repeater|null $r Add to a repeater instead of the widget.
	 */
	protected function wysiwyg( $id, $label, $default = '', $extra = [], $r = null ) {
		$args = array_merge(
			[
				'label'   => $label,
				'type'    => Controls_Manager::WYSIWYG,
				'default' => $default,
				'dynamic' => [ 'active' => true ],
			],
			$extra
		);
		$r ? $r->add_control( $id, $args ) : $this->add_control( $id, $args );
	}

	protected function tag_control( $id, $label, $default, $extra = [], $r = null ) {
		$args = array_merge(
			[
				'label'   => $label,
				'type'    => Controls_Manager::SELECT,
				'options' => self::tags(),
				'default' => $default,
			],
			$extra
		);
		$r ? $r->add_control( $id, $args ) : $this->add_control( $id, $args );
	}

	protected function switcher( $id, $label, $default = 'yes', $extra = [], $r = null ) {
		$args = array_merge(
			[
				'label'        => $label,
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => $default,
			],
			$extra
		);
		$r ? $r->add_control( $id, $args ) : $this->add_control( $id, $args );
	}

	protected function note( $id, $text ) {
		$this->add_control(
			$id,
			[
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => $text,
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);
	}

	protected function heading( $id, $label ) {
		$this->add_control(
			$id,
			[
				'label'     => $label,
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			]
		);
	}

	/** Media control helper (also used for decor, so every image comes from the Media Library). */
	protected function media( $id, $label, $default = [], $extra = [], $r = null ) {
		$args = array_merge(
			[
				'label'   => $label,
				'type'    => Controls_Manager::MEDIA,
				'default' => $default ? $default : [ 'url' => '', 'id' => '' ],
				'dynamic' => [ 'active' => true ],
			],
			$extra
		);
		$r ? $r->add_control( $id, $args ) : $this->add_control( $id, $args );
	}

	protected function image_fit_controls( $prefix, $selector, $defaults = [] ) {
		$this->add_control(
			$prefix . '_fit',
			[
				'label'     => __( 'Object fit', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''        => __( 'Default (cover)', 'mirenova' ),
					'cover'   => 'Cover',
					'contain' => 'Contain',
					'fill'    => 'Fill',
					'none'    => 'None',
				],
				'default'   => isset( $defaults['fit'] ) ? $defaults['fit'] : '',
				'selectors' => [ $selector => 'object-fit: {{VALUE}};' ],
			]
		);
		$this->add_responsive_control(
			$prefix . '_pos',
			[
				'label'                => __( 'Object position', 'mirenova' ),
				'type'                 => Controls_Manager::SELECT,
				'options'              => [
					''              => __( 'Default (center)', 'mirenova' ),
					'center center' => 'Center center',
					'center top'    => 'Center top',
					'center bottom' => 'Center bottom',
					'left center'   => 'Left center',
					'left top'      => 'Left top',
					'left bottom'   => 'Left bottom',
					'right center'  => 'Right center',
					'right top'     => 'Right top',
					'right bottom'  => 'Right bottom',
					'custom'        => __( 'Custom (X / Y)', 'mirenova' ),
				],
				'default'              => isset( $defaults['pos'] ) ? $defaults['pos'] : '',
				'selectors_dictionary' => [ 'custom' => 'var(--mrn-ox,50%) var(--mrn-oy,50%)' ],
				'selectors'            => [ $selector => 'object-position: {{VALUE}};' ],
			]
		);
		foreach ( [ 'x' => 'X', 'y' => 'Y' ] as $axis => $label ) {
			$this->add_responsive_control(
				$prefix . '_pos_' . $axis,
				[
					'label'      => sprintf( __( 'Position %s', 'mirenova' ), $label ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ '%', 'px' ],
					'range'      => [ '%' => [ 'min' => 0, 'max' => 100 ] ],
					'default'    => isset( $defaults[ 'pos_' . $axis ] ) ? $defaults[ 'pos_' . $axis ] : [ 'unit' => '%', 'size' => '' ],
					'selectors'  => [ $selector => '--mrn-o' . $axis . ': {{SIZE}}{{UNIT}};' ],
					'condition'  => [ $prefix . '_pos' => 'custom' ],
				]
			);
		}
	}

	protected function image_size_control( $id, $default = 'large' ) {
		$sizes = [ 'full' => __( 'Full', 'mirenova' ) ];
		foreach ( get_intermediate_image_sizes() as $s ) {
			$sizes[ $s ] = $s;
		}
		$this->add_control(
			$id,
			[
				'label'       => __( 'Image resolution', 'mirenova' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => $sizes,
				'default'     => $default,
				'description' => __( 'Max file size served. Browsers still pick a smaller one from srcset on phones.', 'mirenova' ),
			]
		);
	}

	protected function ratio_control( $id, $label, $default ) {
		$this->add_responsive_control(
			$id,
			[
				'label'     => $label,
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'100%'   => '1:1',
					'125%'   => '4:5',
					'120%'   => '5:6',
					'133.33%' => '3:4',
					'75%'    => '4:3',
					'66.66%' => '3:2',
					'62.5%'  => '16:10',
					'56.25%' => '16:9',
					'42.85%' => '21:9',
				],
				'default'   => $default,
				'selectors' => [ '{{WRAPPER}}' => '--' . str_replace( '_', '-', $id ) . ': {{VALUE}};' ],
			]
		);
	}

	/* ================================================================== */
	/* Shared sections                                                    */
	/* ================================================================== */

	/**
	 * Eyebrow + title + text (+ lead) controls, inside an already open section.
	 *
	 * @param array $d Defaults: eyebrow, title, title_tag, text, text_lead.
	 */
	protected function heading_controls( $d, $with_text = true ) {
		$this->wysiwyg( 'eyebrow', __( 'Eyebrow', 'mirenova' ), isset( $d['eyebrow'] ) ? $d['eyebrow'] : '' );
		$this->switcher( 'eyebrow_dash', __( 'Eyebrow dash', 'mirenova' ), 'yes', [ 'condition' => [ 'eyebrow!' => '' ] ] );
		$this->tag_control( 'eyebrow_tag', __( 'Eyebrow HTML tag', 'mirenova' ), 'div', [ 'condition' => [ 'eyebrow!' => '' ] ] );
		$this->wysiwyg(
			'title',
			__( 'Title', 'mirenova' ),
			isset( $d['title'] ) ? $d['title'] : '',
			[
				'separator'   => 'before',
				'description' => __( 'Bold words get the brand highlight (Style → Title → Highlight).', 'mirenova' ),
			]
		);
		$this->tag_control( 'title_tag', __( 'Title HTML tag', 'mirenova' ), isset( $d['title_tag'] ) ? $d['title_tag'] : 'h2' );
		if ( $with_text ) {
			$this->wysiwyg(
				'text',
				__( 'Text', 'mirenova' ),
				isset( $d['text'] ) ? $d['text'] : '',
				[
					'separator'   => 'before',
					'description' => self::tokens_hint(),
				]
			);
			$this->switcher( 'text_lead', __( 'Lead style for first paragraph', 'mirenova' ), isset( $d['text_lead'] ) ? $d['text_lead'] : 'yes', [ 'condition' => [ 'text!' => '' ] ] );
		}
	}

	/**
	 * Buttons repeater (inside an already open section).
	 */
	protected function buttons_controls( $default = [], $id = 'buttons' ) {
		$r = new Repeater();
		$r->add_control(
			'text',
			[
				'label'       => __( 'Text', 'mirenova' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'Solicitar presupuesto',
				'label_block' => true,
				'dynamic'     => [ 'active' => true ],
				'description' => __( 'Tokens: {phone} {email} {whatsapp}. Example: Llamar al {phone}', 'mirenova' ),
			]
		);
		$r->add_control(
			'link_type',
			[
				'label'   => __( 'Link', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => self::link_types(),
				'default' => 'url',
			]
		);
		$r->add_control(
			'link',
			[
				'label'       => __( 'URL', 'mirenova' ),
				'type'        => Controls_Manager::URL,
				'default'     => [ 'url' => '#contacto' ],
				'dynamic'     => [ 'active' => true ],
				'placeholder' => 'https://',
				'condition'   => [ 'link_type' => 'url' ],
			]
		);
		$r->add_control(
			'style',
			[
				'label'   => __( 'Style', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'primary' => __( 'Primary (filled)', 'mirenova' ),
					'outline' => __( 'Outline', 'mirenova' ),
					'link'    => __( 'Text link', 'mirenova' ),
				],
				'default' => 'primary',
			]
		);
		$r->add_control(
			'icon',
			[
				'label'       => __( 'Icon', 'mirenova' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'default'     => [ 'value' => '', 'library' => '' ],
			]
		);
		$r->add_control(
			'icon_pos',
			[
				'label'     => __( 'Icon position', 'mirenova' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'before' => [ 'title' => __( 'Before', 'mirenova' ), 'icon' => 'eicon-h-align-left' ],
					'after'  => [ 'title' => __( 'After', 'mirenova' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default'   => 'after',
				'condition' => [ 'icon[value]!' => '' ],
			]
		);
		$this->add_control(
			$id,
			[
				'label'         => __( 'Buttons', 'mirenova' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $r->get_controls(),
				'default'       => $default,
				'title_field'   => '{{{ text }}}',
				'prevent_empty' => false,
				'separator'     => 'before',
			]
		);
		$this->add_responsive_control(
			$id . '_align',
			[
				'label'                => __( 'Buttons alignment', 'mirenova' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [
					'start'   => [ 'title' => __( 'Start', 'mirenova' ), 'icon' => 'eicon-text-align-left' ],
					'center'  => [ 'title' => __( 'Center', 'mirenova' ), 'icon' => 'eicon-text-align-center' ],
					'end'     => [ 'title' => __( 'End', 'mirenova' ), 'icon' => 'eicon-text-align-right' ],
					'stretch' => [ 'title' => __( 'Full width', 'mirenova' ), 'icon' => 'eicon-text-align-justify' ],
				],
				'selectors_dictionary' => [
					'start'   => '--mrn-btns-jc:flex-start;--mrn-btn-grow:0;',
					'center'  => '--mrn-btns-jc:center;--mrn-btn-grow:0;',
					'end'     => '--mrn-btns-jc:flex-end;--mrn-btn-grow:0;',
					'stretch' => '--mrn-btns-jc:stretch;--mrn-btn-grow:1;',
				],
				'selectors'            => [ '{{WRAPPER}} .mrn-btns--' . $id => '{{VALUE}}' ],
				'condition'            => [ $id . '!' => [] ],
			]
		);
	}

	/** Content → Section: anchor, color scheme, spacing, content width. Leaves the section open. */
	protected function section_controls_open( $d ) {
		$this->start_controls_section( 's_section', [ 'label' => __( 'Section', 'mirenova' ) ] );
		$this->add_control(
			'anchor',
			[
				'label'       => __( 'Anchor ID', 'mirenova' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => isset( $d['anchor'] ) ? $d['anchor'] : '',
				'description' => __( 'For menu links like #servicios. Letters, numbers and hyphens.', 'mirenova' ),
				'ai'          => [ 'active' => false ],
			]
		);
		$this->add_control(
			'scheme',
			[
				'label'       => __( 'Color scheme', 'mirenova' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'light' => __( 'Light background', 'mirenova' ),
					'dark'  => __( 'Dark background', 'mirenova' ),
				],
				'default'     => isset( $d['scheme'] ) ? $d['scheme'] : 'light',
				'description' => __( 'Switches text, card and accent colors for light or dark backgrounds. When you switch it, also adjust Style → Section background (color, image, overlays).', 'mirenova' ),
			]
		);
		foreach ( [ 'pt' => __( 'Padding top', 'mirenova' ), 'pb' => __( 'Padding bottom', 'mirenova' ) ] as $k => $label ) {
			$this->add_responsive_control(
				'pad_' . $k,
				[
					'label'       => $label,
					'type'        => Controls_Manager::SLIDER,
					'size_units'  => [ 'px', 'vw', 'rem', 'custom' ],
					'range'       => [ 'px' => [ 'min' => 0, 'max' => 400 ] ],
					'selectors'   => [ '{{WRAPPER}}' => '--mrn-' . $k . ': {{SIZE}}{{UNIT}};' ],
					'description' => 'pt' === $k ? __( 'Empty = design default (fluid, 80–180px).', 'mirenova' ) : '',
				]
			);
		}
		$this->add_responsive_control(
			'content_width',
			[
				'label'      => __( 'Content max width', 'mirenova' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', 'custom' ],
				'range'      => [ 'px' => [ 'min' => 600, 'max' => 1920 ] ],
				'selectors'  => [ '{{WRAPPER}}' => '--mrn-w: {{SIZE}}{{UNIT}};' ],
			]
		);
	}

	/**
	 * Style → Background: color, image (lazy <img>, object-fit/position), filters, two overlays.
	 *
	 * @param array $d image, size, fit, pos, opacity, blend, ov1 (fields_options), ov2, no_image.
	 */
	protected function background_controls( $d = [] ) {
		$this->start_controls_section(
			's_bg',
			[
				'label' => __( 'Section background', 'mirenova' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			'bg_color',
			[
				'label'     => __( 'Background color', 'mirenova' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} > .mrn, {{WRAPPER}} .mrn-sec' => 'background-color: {{VALUE}};' ],
			]
		);
		if ( empty( $d['no_image'] ) ) {
			$this->media( 'bg_image', __( 'Background image', 'mirenova' ), isset( $d['image'] ) ? $d['image'] : [], [ 'separator' => 'before' ] );
			$this->image_size_control( 'bg_image_size', 'full' );
			$this->image_fit_controls( 'bg', '{{WRAPPER}} .mrn-bg__img', $d );
			$this->add_control(
				'bg_opacity',
				[
					'label'     => __( 'Image opacity', 'mirenova' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.01 ] ],
					'default'   => isset( $d['opacity'] ) ? [ 'size' => $d['opacity'] ] : [ 'size' => '' ],
					'selectors' => [ '{{WRAPPER}} .mrn-bg__img' => 'opacity: {{SIZE}};' ],
				]
			);
			$this->add_control(
				'bg_blend',
				[
					'label'     => __( 'Blend mode', 'mirenova' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						''           => __( 'Normal', 'mirenova' ),
						'multiply'   => 'Multiply',
						'screen'     => 'Screen',
						'overlay'    => 'Overlay',
						'darken'     => 'Darken',
						'lighten'    => 'Lighten',
						'luminosity' => 'Luminosity',
					],
					'default'   => isset( $d['blend'] ) ? $d['blend'] : '',
					'selectors' => [ '{{WRAPPER}} .mrn-bg__img' => 'mix-blend-mode: {{VALUE}};' ],
				]
			);
			$this->add_group_control(
				Group_Control_Css_Filter::get_type(),
				[
					'name'     => 'bg_filters',
					'selector' => '{{WRAPPER}} .mrn-bg__img',
				]
			);
			$this->switcher( 'bg_hide_mobile', __( 'Hide image on mobile (faster)', 'mirenova' ), '' );
		}
		foreach ( [ 1, 2 ] as $n ) {
			$this->heading( 'ov' . $n . '_heading', 1 === $n ? __( 'Background overlay', 'mirenova' ) : __( 'Second overlay', 'mirenova' ) );
			$args = [
				'name'     => 'ov' . $n,
				'types'    => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .mrn-bg__ov--' . $n,
			];
			if ( ! empty( $d[ 'ov' . $n ] ) ) {
				$args['fields_options'] = $d[ 'ov' . $n ];
			}
			$this->add_group_control( Group_Control_Background::get_type(), $args );
			$this->add_control(
				'ov' . $n . '_opacity',
				[
					'label'     => __( 'Overlay opacity', 'mirenova' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.01 ] ],
					'selectors' => [ '{{WRAPPER}} .mrn-bg__ov--' . $n => 'opacity: {{SIZE}};' ],
					'condition' => [ 'ov' . $n . '_background!' => '' ],
				]
			);
			$this->add_control(
				'ov' . $n . '_blend',
				[
					'label'     => __( 'Overlay blend mode', 'mirenova' ),
					'type'      => Controls_Manager::SELECT,
					'options'   => [
						''         => __( 'Normal', 'mirenova' ),
						'multiply' => 'Multiply',
						'screen'   => 'Screen',
						'overlay'  => 'Overlay',
						'color'    => 'Color',
					],
					'selectors' => [ '{{WRAPPER}} .mrn-bg__ov--' . $n => 'mix-blend-mode: {{VALUE}};' ],
					'condition' => [ 'ov' . $n . '_background!' => '' ],
				]
			);
		}
		$this->end_controls_section();
	}

	/**
	 * Gradient overlay defaults for Group_Control_Background.
	 */
	protected static function gradient( $a, $a_stop, $b, $b_stop, $angle ) {
		return [
			'background'     => [ 'default' => 'gradient' ],
			'color'          => [ 'default' => $a ],
			'color_stop'     => [ 'default' => [ 'unit' => '%', 'size' => $a_stop ] ],
			'color_b'        => [ 'default' => $b ],
			'color_b_stop'   => [ 'default' => [ 'unit' => '%', 'size' => $b_stop ] ],
			'gradient_type'  => [ 'default' => 'linear' ],
			'gradient_angle' => [ 'default' => [ 'unit' => 'deg', 'size' => $angle ] ],
		];
	}

	/**
	 * Style → Decor: repeater of brand diamonds, glows and decor images (any image from the Media Library).
	 */
	protected function decor_controls( $defaults = [] ) {
		$this->start_controls_section(
			's_decor',
			[
				'label' => __( 'Decor', 'mirenova' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->switcher( 'decor_on', __( 'Show decor', 'mirenova' ) );

		$r     = new Repeater();
		$shape = [ 'grad', 'soft', 'line' ];
		$r->add_control(
			'type',
			[
				'label'   => __( 'Type', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'image' => __( 'Image', 'mirenova' ),
					'grad'  => __( 'Diamond — brand gradient', 'mirenova' ),
					'soft'  => __( 'Diamond — solid color', 'mirenova' ),
					'line'  => __( 'Diamond — outline', 'mirenova' ),
					'glow'  => __( 'Radial glow', 'mirenova' ),
				],
				'default' => 'soft',
			]
		);
		$this->media( 'image', __( 'Image', 'mirenova' ), [], [ 'condition' => [ 'type' => 'image' ] ], $r );
		$r->add_control(
			'color',
			[
				'label'     => __( 'Color', 'mirenova' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--c: {{VALUE}};' ],
				'condition' => [ 'type' => [ 'soft', 'line', 'glow' ] ],
			]
		);
		$r->add_control(
			'line_w',
			[
				'label'     => __( 'Line width', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 1, 'max' => 10, 'step' => 0.5 ] ],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--bw: {{SIZE}}px;' ],
				'condition' => [ 'type' => 'line' ],
			]
		);
		$r->add_responsive_control(
			'size',
			[
				'label'      => __( 'Width', 'mirenova' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'vw', 'custom' ],
				'range'      => [ 'px' => [ 'min' => 10, 'max' => 1400 ] ],
				'default'    => [ 'unit' => 'px', 'size' => 200 ],
				'selectors'  => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--w: {{SIZE}}{{UNIT}};' ],
				'condition'  => [ 'type!' => 'glow' ],
			]
		);
		$r->add_control(
			'x_side',
			[
				'label'     => __( 'Horizontal anchor', 'mirenova' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'left'  => [ 'title' => __( 'Left', 'mirenova' ), 'icon' => 'eicon-h-align-left' ],
					'right' => [ 'title' => __( 'Right', 'mirenova' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default'   => 'left',
				'toggle'    => false,
				'condition' => [ 'type!' => 'glow' ],
			]
		);
		$offset = [
			'type'       => Controls_Manager::SLIDER,
			'size_units' => [ 'px', '%', 'vw', 'custom' ],
			'range'      => [
				'px' => [ 'min' => -1000, 'max' => 1500 ],
				'%'  => [ 'min' => -100, 'max' => 100 ],
			],
			'default'    => [ 'unit' => 'px', 'size' => 0 ],
		];
		$r->add_responsive_control( 'x', $offset + [ 'label' => __( 'Offset X', 'mirenova' ), 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'left: {{SIZE}}{{UNIT}}; right: auto;' ], 'condition' => [ 'x_side' => 'left', 'type!' => 'glow' ] ] );
		$r->add_responsive_control( 'xr', $offset + [ 'label' => __( 'Offset X', 'mirenova' ), 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'right: {{SIZE}}{{UNIT}}; left: auto;' ], 'condition' => [ 'x_side' => 'right', 'type!' => 'glow' ] ] );
		$r->add_control(
			'y_side',
			[
				'label'     => __( 'Vertical anchor', 'mirenova' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [
					'top'    => [ 'title' => __( 'Top', 'mirenova' ), 'icon' => 'eicon-v-align-top' ],
					'bottom' => [ 'title' => __( 'Bottom', 'mirenova' ), 'icon' => 'eicon-v-align-bottom' ],
				],
				'default'   => 'top',
				'toggle'    => false,
				'condition' => [ 'type!' => 'glow' ],
			]
		);
		$r->add_responsive_control( 'y', $offset + [ 'label' => __( 'Offset Y', 'mirenova' ), 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'top: {{SIZE}}{{UNIT}}; bottom: auto;' ], 'condition' => [ 'y_side' => 'top', 'type!' => 'glow' ] ] );
		$r->add_responsive_control( 'yb', $offset + [ 'label' => __( 'Offset Y', 'mirenova' ), 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'bottom: {{SIZE}}{{UNIT}}; top: auto;' ], 'condition' => [ 'y_side' => 'bottom', 'type!' => 'glow' ] ] );
		foreach ( [ 'gx' => 'X', 'gy' => 'Y' ] as $k => $axis ) {
			$r->add_control(
				$k,
				[
					'label'     => sprintf( __( 'Glow center %s', 'mirenova' ), $axis ),
					'type'      => Controls_Manager::SLIDER,
					'size_units' => [ '%' ],
					'range'     => [ '%' => [ 'min' => 0, 'max' => 100 ] ],
					'default'   => [ 'unit' => '%', 'size' => 'gx' === $k ? 80 : 40 ],
					'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--' . $k . ': {{SIZE}}%;' ],
					'condition' => [ 'type' => 'glow' ],
				]
			);
		}
		$r->add_control(
			'radius',
			[
				'label'     => __( 'Corner radius', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'     => [ '%' => [ 'min' => 0, 'max' => 50 ] ],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--r: {{SIZE}}%;' ],
				'condition' => [ 'type' => $shape ],
			]
		);
		$r->add_control(
			'opacity',
			[
				'label'     => __( 'Opacity', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 1, 'step' => 0.01 ] ],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'opacity: {{SIZE}};' ],
			]
		);
		$r->add_control(
			'blur',
			[
				'label'     => __( 'Blur', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 60 ] ],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--blur: {{SIZE}}px;' ],
				'condition' => [ 'type!' => 'glow' ],
			]
		);
		$r->add_control(
			'saturate',
			[
				'label'     => __( 'Saturation', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'size_units' => [ '%' ],
				'range'     => [ '%' => [ 'min' => 0, 'max' => 200 ] ],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => '--sat: {{SIZE}}%;' ],
				'condition' => [ 'type' => 'image' ],
			]
		);
		$r->add_control(
			'blend',
			[
				'label'     => __( 'Blend mode', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''         => __( 'Normal', 'mirenova' ),
					'multiply' => 'Multiply',
					'screen'   => 'Screen',
					'overlay'  => 'Overlay',
				],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'mix-blend-mode: {{VALUE}};' ],
			]
		);
		$this->switcher( 'shadow', __( 'Brand glow shadow', 'mirenova' ), '', [ 'condition' => [ 'type' => [ 'grad', 'image' ] ] ], $r );
		$this->switcher( 'hide_d', __( 'Hide on desktop', 'mirenova' ), '', [ 'separator' => 'before' ], $r );
		$this->switcher( 'hide_t', __( 'Hide on tablet', 'mirenova' ), '', [], $r );
		$this->switcher( 'hide_m', __( 'Hide on mobile', 'mirenova' ), '', [], $r );

		$this->add_control(
			'decor',
			[
				'label'         => __( 'Decor items', 'mirenova' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $r->get_controls(),
				'default'       => $defaults,
				'prevent_empty' => false,
				'title_field'   => '{{{ ({image:"Image",grad:"Gradient diamond",soft:"Solid diamond",line:"Outline diamond",glow:"Glow"})[type] || type }}}',
				'condition'     => [ 'decor_on' => 'yes' ],
			]
		);
		$this->end_controls_section();
	}

	/** Decor item defaults helper. */
	protected static function d( $type, $w, $x, $y, $extra = [] ) {
		$item = [ 'type' => $type ];
		if ( null !== $w ) {
			$item['size'] = is_array( $w ) ? $w : [ 'unit' => 'px', 'size' => $w ];
		}
		$pos = static function ( $v ) {
			if ( is_array( $v ) ) {
				return $v;
			}
			if ( is_string( $v ) && preg_match( '/^(-?[\d.]+)(px|%|vw)$/', $v, $m ) ) {
				return [ 'unit' => $m[2], 'size' => (float) $m[1] ];
			}
			return [ 'unit' => 'custom', 'size' => (string) $v ];
		};
		if ( null !== $x ) {
			list( $side, $val ) = $x;
			$item['x_side'] = $side;
			$item[ 'left' === $side ? 'x' : 'xr' ] = $pos( $val );
		}
		if ( null !== $y ) {
			list( $side, $val ) = $y;
			$item['y_side'] = $side;
			$item[ 'top' === $side ? 'y' : 'yb' ] = $pos( $val );
		}
		return array_merge( $item, $extra );
	}

	/** Content → Empty cells: decorative filler for incomplete grid rows. */
	protected function filler_controls( $d = [] ) {
		$this->start_controls_section( 's_fill', [ 'label' => __( 'Empty cells filler', 'mirenova' ) ] );
		$this->note( 'fill_note', __( 'When the last row is not full (e.g. 3 cards in 2 columns), a brand decor cell fills the gap automatically on each device.', 'mirenova' ) );
		$this->switcher( 'fill_on', __( 'Fill empty cells', 'mirenova' ) );
		$this->media( 'fill_image', __( 'Decor image', 'mirenova' ), isset( $d['image'] ) ? $d['image'] : [], [ 'condition' => [ 'fill_on' => 'yes' ] ] );
		$this->switcher( 'fill_diamonds', __( 'Brand diamonds', 'mirenova' ), 'yes', [ 'condition' => [ 'fill_on' => 'yes' ] ] );
		$this->wysiwyg( 'fill_text', __( 'Text (optional)', 'mirenova' ), '', [ 'condition' => [ 'fill_on' => 'yes' ] ] );
		$this->add_control(
			'fill_bg',
			[
				'label'     => __( 'Background color', 'mirenova' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ '{{WRAPPER}} .mrn-fill' => 'background-color: {{VALUE}};' ],
				'condition' => [ 'fill_on' => 'yes' ],
			]
		);
		$this->end_controls_section();
	}

	/* ================================================================== */
	/* Style helpers                                                      */
	/* ================================================================== */

	/**
	 * Style section with color (+hover) and typography for one text element.
	 */
	protected function style_text( $id, $label, $selector, $opts = [] ) {
		$this->start_controls_section(
			'st_' . $id,
			[
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control(
			$id . '_color',
			[
				'label'     => __( 'Color', 'mirenova' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ $selector => 'color: {{VALUE}};' ],
			]
		);
		if ( ! empty( $opts['hover'] ) ) {
			$hover = implode( ',', array_map( static function ( $s ) { return trim( $s ) . ':hover'; }, explode( ',', $selector ) ) );
			$this->add_control(
				$id . '_color_h',
				[
					'label'     => __( 'Hover color', 'mirenova' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [ $hover => 'color: {{VALUE}};' ],
				]
			);
		}
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => $id . '_typo',
				'selector' => $selector,
			]
		);
		if ( ! empty( $opts['highlight'] ) ) {
			$this->add_control(
				'hl_mode',
				[
					'label'       => __( 'Highlight (bold words)', 'mirenova' ),
					'type'        => Controls_Manager::SELECT,
					'options'     => [
						'gradient' => __( 'Brand gradient', 'mirenova' ),
						'color'    => __( 'Solid color', 'mirenova' ),
						'plain'    => __( 'Just bold', 'mirenova' ),
					],
					'default'     => 'gradient',
					'separator'   => 'before',
				]
			);
			$this->add_control(
				'hl_a',
				[
					'label'     => __( 'Gradient start', 'mirenova' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}}' => '--mrn-hl-a: {{VALUE}};' ],
					'condition' => [ 'hl_mode' => 'gradient' ],
				]
			);
			$this->add_control(
				'hl_b',
				[
					'label'     => __( 'Gradient end', 'mirenova' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}}' => '--mrn-hl-b: {{VALUE}};' ],
					'condition' => [ 'hl_mode' => 'gradient' ],
				]
			);
			$this->add_control(
				'hl_c',
				[
					'label'     => __( 'Highlight color', 'mirenova' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => [ '{{WRAPPER}}' => '--mrn-hl-c: {{VALUE}};' ],
					'condition' => [ 'hl_mode' => 'color' ],
				]
			);
		}
		if ( ! empty( $opts['gap'] ) ) {
			$this->add_responsive_control(
				$id . '_gap',
				[
					'label'      => __( 'Space above', 'mirenova' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', 'rem' ],
					'range'      => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
					'selectors'  => [ $selector => 'margin-top: {{SIZE}}{{UNIT}};' ],
				]
			);
		}
		if ( ! empty( $opts['max'] ) ) {
			$this->add_responsive_control(
				$id . '_max',
				[
					'label'      => __( 'Max width', 'mirenova' ),
					'type'       => Controls_Manager::SLIDER,
					'size_units' => [ 'px', '%', 'ch' ],
					'range'      => [ 'px' => [ 'min' => 200, 'max' => 1300 ] ],
					'selectors'  => [ $selector => 'max-width: {{SIZE}}{{UNIT}};' ],
				]
			);
		}
		$this->end_controls_section();
	}

	/** Style → Card image: object-fit / position. */
	protected function style_card_image() {
		$this->start_controls_section( 'st_cardimg', [ 'label' => __( 'Card image', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->image_fit_controls( 'card_img', '{{WRAPPER}} .mrn-card__img img' );
		$this->end_controls_section();
	}

	/** Eyebrow / title / text style sections. */
	protected function style_heading( $scope = '' ) {
		$this->style_text( 'eyebrow', __( 'Eyebrow', 'mirenova' ), '{{WRAPPER}} ' . $scope . '.mrn-eyebrow' );
		$this->style_text( 'title', __( 'Title', 'mirenova' ), '{{WRAPPER}} ' . $scope . '.mrn-title', [ 'highlight' => true, 'gap' => true, 'max' => true ] );
		$this->style_text( 'text', __( 'Text', 'mirenova' ), '{{WRAPPER}} ' . $scope . '.mrn-text', [ 'gap' => true, 'max' => true ] );
	}

	/** Style → Buttons. */
	protected function style_buttons() {
		$this->start_controls_section(
			'st_buttons',
			[
				'label' => __( 'Buttons', 'mirenova' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'btn_typo',
				'selector' => '{{WRAPPER}} .mrn-btn',
			]
		);
		$this->add_responsive_control(
			'btn_h',
			[
				'label'      => __( 'Height', 'mirenova' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => [ 'px' => [ 'min' => 32, 'max' => 90 ] ],
				'selectors'  => [ '{{WRAPPER}}' => '--mrn-btn-h: {{SIZE}}px;' ],
			]
		);
		$this->add_responsive_control(
			'btn_px',
			[
				'label'     => __( 'Horizontal padding', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 8, 'max' => 80 ] ],
				'selectors' => [ '{{WRAPPER}}' => '--mrn-btn-px: {{SIZE}}px;' ],
			]
		);
		$this->add_control(
			'btn_radius',
			[
				'label'     => __( 'Border radius', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'selectors' => [ '{{WRAPPER}}' => '--mrn-btn-r: {{SIZE}}px;' ],
			]
		);
		foreach ( [ 'primary' => __( 'Primary', 'mirenova' ), 'outline' => __( 'Outline', 'mirenova' ) ] as $k => $label ) {
			$sel = '{{WRAPPER}} .mrn-btn--' . $k;
			$this->heading( 'btn_' . $k . '_h', $label );
			$this->add_control( 'btn_' . $k . '_color', [ 'label' => __( 'Text', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $sel => 'color: {{VALUE}};' ] ] );
			$this->add_control( 'btn_' . $k . '_bg', [ 'label' => __( 'Background', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $sel => 'background-color: {{VALUE}};' ] ] );
			$this->add_control( 'btn_' . $k . '_border', [ 'label' => __( 'Border', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $sel => 'border-color: {{VALUE}};' ] ] );
			$this->add_control( 'btn_' . $k . '_color_h', [ 'label' => __( 'Hover text', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $sel . ':hover,' . $sel . ':focus-visible' => 'color: {{VALUE}};' ] ] );
			$this->add_control( 'btn_' . $k . '_bg_h', [ 'label' => __( 'Hover background', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $sel . ':hover,' . $sel . ':focus-visible' => 'background-color: {{VALUE}};' ] ] );
			$this->add_control( 'btn_' . $k . '_border_h', [ 'label' => __( 'Hover border', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $sel . ':hover,' . $sel . ':focus-visible' => 'border-color: {{VALUE}};' ] ] );
		}
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'      => 'btn_shadow',
				'selector'  => '{{WRAPPER}} .mrn-btn--primary',
				'separator' => 'before',
			]
		);
		$this->end_controls_section();
	}

	/** Style → Card (box) helper. */
	protected function style_box( $id, $label, $selector, $opts = [] ) {
		$this->start_controls_section(
			'st_' . $id,
			[
				'label' => $label,
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_control( $id . '_bg', [ 'label' => __( 'Background', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $selector => 'background-color: {{VALUE}};' ] ] );
		$this->add_control( $id . '_border', [ 'label' => __( 'Border color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ $selector => 'border-color: {{VALUE}};' ] ] );
		$this->add_control(
			$id . '_radius',
			[
				'label'     => __( 'Border radius', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
				'selectors' => [ $selector => 'border-radius: {{SIZE}}px;' ],
			]
		);
		$this->add_responsive_control(
			$id . '_pad',
			[
				'label'      => __( 'Padding', 'mirenova' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ ( isset( $opts['pad_selector'] ) ? $opts['pad_selector'] : $selector ) => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ],
			]
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => $id . '_shadow',
				'selector' => $selector,
			]
		);
		if ( ! empty( $opts['gap'] ) ) {
			$this->add_responsive_control(
				$id . '_gap',
				[
					'label'     => __( 'Gap between items', 'mirenova' ),
					'type'      => Controls_Manager::SLIDER,
					'range'     => [ 'px' => [ 'min' => 0, 'max' => 120 ] ],
					'selectors' => [ '{{WRAPPER}}' => '--mrn-gap: {{SIZE}}px;' ],
				]
			);
		}
		$this->end_controls_section();
	}

	/* ================================================================== */
	/* Render helpers                                                     */
	/* ================================================================== */

	protected static function tag( $t, $default = 'h2' ) {
		return in_array( $t, [ 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span' ], true ) ? $t : $default;
	}

	protected static function on( $s, $key ) {
		return isset( $s[ $key ] ) && 'yes' === $s[ $key ];
	}

	protected function section_open( $s, $class, $attrs = '' ) {
		$scheme = isset( $s['scheme'] ) && 'dark' === $s['scheme'] ? 'dark' : 'light';
		$hl     = isset( $s['hl_mode'] ) && in_array( $s['hl_mode'], [ 'gradient', 'color', 'plain' ], true ) ? $s['hl_mode'] : 'gradient';
		$id     = ! empty( $s['anchor'] ) ? ' id="' . esc_attr( sanitize_title( $s['anchor'] ) ) . '"' : '';
		echo '<section class="' . esc_attr( 'mrn mrn-sec mrn--' . $scheme . ' mrn-hl--' . $hl . ' ' . $class ) . '"' . $id . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Background layer: [extra] + image + overlays + decor.
	 */
	protected function background_html( $s, $before = '' ) {
		$img = '';
		if ( ! empty( $s['bg_image']['url'] ) || ! empty( $s['bg_image']['id'] ) ) {
			$img = MRN::img(
				$s['bg_image'],
				! empty( $s['bg_image_size'] ) ? $s['bg_image_size'] : 'full',
				[
					'class'   => 'mrn-bg__img' . ( self::on( $s, 'bg_hide_mobile' ) ? ' mrn-hide-m' : '' ),
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '100vw',
				]
			);
		}
		// Overlay layers are always present: their color/gradient comes from the Elementor CSS
		// (style controls are not available at render time on the frontend in Elementor 4).
		$ov    = '<div class="mrn-bg__ov mrn-bg__ov--1"></div><div class="mrn-bg__ov mrn-bg__ov--2"></div>';
		$decor = $this->decor_html( $s );
		return '<div class="mrn-bg" aria-hidden="true">' . $before . $img . $ov . $decor . '</div>';
	}

	protected function decor_html( $s ) {
		if ( ! self::on( $s, 'decor_on' ) || empty( $s['decor'] ) || ! is_array( $s['decor'] ) ) {
			return '';
		}
		$out = '';
		foreach ( $s['decor'] as $d ) {
			$type = isset( $d['type'] ) ? $d['type'] : 'soft';
			$cls  = [ 'mrn-d', 'mrn-d--' . $type, 'elementor-repeater-item-' . ( isset( $d['_id'] ) ? $d['_id'] : '' ) ];
			if ( self::on( $d, 'shadow' ) ) {
				$cls[] = 'has-shadow';
			}
			$blur = isset( $d['blur']['size'] ) && '' !== $d['blur']['size'] && (float) $d['blur']['size'] > 0;
			$sat  = isset( $d['saturate']['size'] ) && '' !== $d['saturate']['size'] && 100.0 !== (float) $d['saturate']['size'];
			if ( $blur || $sat ) {
				$cls[] = 'has-filter';
			}
			foreach ( [ 'd', 't', 'm' ] as $dev ) {
				if ( self::on( $d, 'hide_' . $dev ) ) {
					$cls[] = 'mrn-hide-' . $dev;
				}
			}
			$inner = '';
			if ( 'image' === $type ) {
				$w     = isset( $d['size']['unit'], $d['size']['size'] ) && 'px' === $d['size']['unit'] ? (int) $d['size']['size'] . 'px' : '40vw';
				$inner = MRN::img(
					isset( $d['image'] ) ? $d['image'] : [],
					'full',
					[
						'alt'     => '',
						'loading' => 'lazy',
						'sizes'   => $w,
					]
				);
				if ( '' === $inner ) {
					continue;
				}
			}
			$out .= '<div class="' . esc_attr( implode( ' ', $cls ) ) . '">' . $inner . '</div>';
		}
		return $out ? '<div class="mrn-decor">' . $out . '</div>' : '';
	}

	protected function eyebrow_html( $s, $key = 'eyebrow' ) {
		$t = MRN::inline( isset( $s[ $key ] ) ? $s[ $key ] : '' );
		if ( '' === $t ) {
			return '';
		}
		$dash = ! isset( $s[ $key . '_dash' ] ) || 'yes' === $s[ $key . '_dash' ] ? '<span class="mrn-eyebrow__dash" aria-hidden="true"></span>' : '';
		$tag  = self::tag( isset( $s[ $key . '_tag' ] ) ? $s[ $key . '_tag' ] : 'div', 'div' );
		$tag  = 'span' === $tag ? 'div' : $tag;
		return '<' . $tag . ' class="mrn-eyebrow">' . $dash . '<span class="mrn-eyebrow__text">' . $t . '</span></' . $tag . '>';
	}

	protected function title_html( $s, $key = 'title', $class = 'mrn-title', $default_tag = 'h2' ) {
		$t = MRN::inline( isset( $s[ $key ] ) ? $s[ $key ] : '' );
		if ( '' === $t ) {
			return '';
		}
		$tag = self::tag( isset( $s[ $key . '_tag' ] ) ? $s[ $key . '_tag' ] : $default_tag, $default_tag );
		return '<' . $tag . ' class="' . esc_attr( $class ) . '">' . $t . '</' . $tag . '>';
	}

	protected function text_html( $s, $key = 'text', $class = 'mrn-text' ) {
		$t = MRN::rich( isset( $s[ $key ] ) ? $s[ $key ] : '' );
		if ( '' === $t ) {
			return '';
		}
		$lead = self::on( $s, $key . '_lead' ) ? ' mrn-text--lead' : '';
		return '<div class="' . esc_attr( $class . $lead ) . '">' . $t . '</div>';
	}

	/** Inline rich text with a wrapper tag (card titles, questions, labels). */
	protected static function inline_tag( $html, $tag, $class ) {
		$t = MRN::inline( $html );
		if ( '' === $t ) {
			return '';
		}
		$tag = self::tag( $tag, 'div' );
		return '<' . $tag . ' class="' . esc_attr( $class ) . '">' . $t . '</' . $tag . '>';
	}

	/** Icon from an ICONS control: MiRenova set → inline SVG, otherwise Elementor icon libraries. */
	protected function icon_html( $icon, $class = 'mrn-ico' ) {
		if ( ! is_array( $icon ) || empty( $icon['value'] ) ) {
			return '';
		}
		if ( isset( $icon['library'] ) && 'mrn-icons' === $icon['library'] && is_string( $icon['value'] ) ) {
			return MRN_Icons::svg( preg_replace( '/^.*mrn-i-/', '', $icon['value'] ), [ 'class' => $class ] );
		}
		ob_start();
		Icons_Manager::render_icon( $icon, [ 'aria-hidden' => 'true', 'class' => $class ] );
		return (string) ob_get_clean();
	}

	/** Unique render attribute key. */
	protected function attr_key( $prefix ) {
		return $prefix . '-' . ( ++$this->attr_i );
	}

	/**
	 * Link wrapper attributes for a link type + URL control.
	 *
	 * @return string '' when there is no link.
	 */
	protected function link_attrs( $type, $url, $class = '' ) {
		$link = MRN::link( $type ? $type : 'url', $url );
		if ( empty( $link['url'] ) ) {
			return '';
		}
		$key = $this->attr_key( 'lnk' );
		$this->add_link_attributes( $key, $link );
		if ( $class ) {
			$this->add_render_attribute( $key, 'class', $class );
		}
		return $this->get_render_attribute_string( $key );
	}

	protected function buttons_html( $s, $id = 'buttons', $class = '' ) {
		if ( empty( $s[ $id ] ) || ! is_array( $s[ $id ] ) ) {
			return '';
		}
		$out = '';
		foreach ( $s[ $id ] as $b ) {
			$type  = isset( $b['link_type'] ) ? $b['link_type'] : 'url';
			$text  = isset( $b['text'] ) ? (string) $b['text'] : '';
			if ( '' === trim( $text ) ) {
				$text = 'phone' === $type ? '{phone}' : ( 'email' === $type ? '{email}' : '' );
			}
			$label = MRN::tokens( esc_html( $text ) );
			$style = isset( $b['style'] ) && in_array( $b['style'], [ 'primary', 'outline', 'link' ], true ) ? $b['style'] : 'primary';
			$icon  = $this->icon_html( isset( $b['icon'] ) ? $b['icon'] : [], 'mrn-ico mrn-btn__icon' );
			$cls   = 'mrn-btn mrn-btn--' . $style . ' elementor-repeater-item-' . ( isset( $b['_id'] ) ? $b['_id'] : '' );
			$inner = ( $icon && isset( $b['icon_pos'] ) && 'before' === $b['icon_pos'] ? $icon : '' )
				. '<span class="mrn-btn__label">' . $label . '</span>'
				. ( $icon && ( ! isset( $b['icon_pos'] ) || 'before' !== $b['icon_pos'] ) ? $icon : '' );
			$attrs = $this->link_attrs( $type, isset( $b['link'] ) ? $b['link'] : [], $cls );
			$out  .= $attrs ? '<a ' . $attrs . '>' . $inner . '</a>' : '<span class="' . esc_attr( $cls ) . '">' . $inner . '</span>';
		}
		return '<div class="' . esc_attr( 'mrn-btns mrn-btns--' . $id . ' ' . $class ) . '">' . $out . '</div>';
	}

	/* ---------- grids ---------- */

	/**
	 * Resolve desktop / tablet / mobile column counts.
	 *
	 * @return int[] [desktop, tablet, mobile]
	 */
	protected function cols( $s, $key, $count, $auto_max = 4 ) {
		$res  = [];
		$prev = 'auto';
		foreach ( [ '', '_tablet', '_mobile' ] as $suf ) {
			$v = isset( $s[ $key . $suf ] ) && '' !== $s[ $key . $suf ] ? (string) $s[ $key . $suf ] : $prev;
			$n = 'auto' === $v ? max( 1, min( max( 1, $count ), $auto_max ) ) : max( 1, (int) $v );
			$res[] = $n;
			$prev  = $v;
		}
		return $res;
	}

	protected static function grid_vars( array $cols ) {
		return sprintf( '--cd:%d;--ct:%d;--cm:%d;', $cols[0], $cols[1], $cols[2] );
	}

	protected static function needs_fill( array $cols, $count ) {
		foreach ( $cols as $c ) {
			if ( $count % $c ) {
				return true;
			}
		}
		return false;
	}

	protected static function fill_vars( array $cols, $count ) {
		$css = '';
		foreach ( [ 'd', 't', 'm' ] as $i => $dev ) {
			$c    = $cols[ $i ];
			$r    = $count % $c;
			$span = $r ? $c - $r : 0;
			$css .= '--f' . $dev . ':' . max( 1, $span ) . ';--v' . $dev . ':' . ( $span ? 'flex' : 'none' ) . ';';
		}
		return $css;
	}

	/** Decorative cell that fills the incomplete last row. */
	protected function filler_html( $s, array $cols, $count, $class = '' ) {
		if ( ! self::on( $s, 'fill_on' ) || ! self::needs_fill( $cols, $count ) ) {
			return '';
		}
		$img  = '';
		$text = MRN::rich( isset( $s['fill_text'] ) ? $s['fill_text'] : '' );
		if ( ! empty( $s['fill_image']['url'] ) || ! empty( $s['fill_image']['id'] ) ) {
			$img = MRN::img(
				$s['fill_image'],
				'large',
				[
					'class'   => 'mrn-fill__img',
					'alt'     => '',
					'loading' => 'lazy',
					'sizes'   => '(max-width: 1024px) 60vw, 420px',
				]
			);
		}
		$dia = self::on( $s, 'fill_diamonds' )
			? '<span class="mrn-fill__d mrn-fill__d--1"></span><span class="mrn-fill__d mrn-fill__d--2"></span><span class="mrn-fill__d mrn-fill__d--3"></span>'
			: '';
		return '<div class="' . esc_attr( 'mrn-fill ' . $class . ( $text ? ' has-text' : '' ) ) . '" style="' . esc_attr( self::fill_vars( $cols, $count ) ) . '"' . ( $text ? '' : ' aria-hidden="true"' ) . '>'
			. $dia . $img . ( $text ? '<div class="mrn-fill__text mrn-text">' . $text . '</div>' : '' ) . '</div>';
	}

	/** "01", "02"… (or plain numbers). */
	protected static function number( $i, $format = '01', $start = 1 ) {
		$n = (int) $start + (int) $i;
		return '01' === $format ? str_pad( (string) $n, 2, '0', STR_PAD_LEFT ) : (string) $n;
	}
}
