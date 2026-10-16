<?php
/**
 * MR Feature Cards: photo cards with icon badge, optional overlap into the previous section,
 * any number of columns and an automatic brand filler for incomplete rows.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Cards extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-cards';
	}

	public function get_title() {
		return 'MR Feature Cards';
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'cards', 'features', 'team', 'grid' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 's_head', [ 'label' => __( 'Heading (optional)', 'mirenova' ) ] );
		$this->heading_controls( [ 'text_lead' => '' ] );
		$this->buttons_controls( [] );
		$this->end_controls_section();

		$this->start_controls_section( 's_cards', [ 'label' => __( 'Cards', 'mirenova' ) ] );
		$r = new Repeater();
		$this->media( 'image', __( 'Image', 'mirenova' ), [], [], $r );
		$r->add_control(
			'icon',
			[
				'label'   => __( 'Badge icon', 'mirenova' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [ 'value' => 'mrn-i mrn-i-check', 'library' => 'mrn-icons' ],
			]
		);
		$this->wysiwyg( 'title', __( 'Title', 'mirenova' ), 'Título de la tarjeta', [], $r );
		$this->wysiwyg( 'text', __( 'Text', 'mirenova' ), '<p>Texto de la tarjeta.</p>', [ 'description' => self::tokens_hint() ], $r );
		$r->add_control( 'btn_text', [ 'label' => __( 'Button text', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'separator' => 'before', 'dynamic' => [ 'active' => true ], 'description' => __( 'Empty = no button.', 'mirenova' ) ] );
		$r->add_control( 'btn_type', [ 'label' => __( 'Button link', 'mirenova' ), 'type' => Controls_Manager::SELECT, 'options' => self::link_types(), 'default' => 'url', 'condition' => [ 'btn_text!' => '' ] ] );
		$r->add_control( 'btn_link', [ 'label' => __( 'URL', 'mirenova' ), 'type' => Controls_Manager::URL, 'dynamic' => [ 'active' => true ], 'condition' => [ 'btn_text!' => '', 'btn_type' => 'url' ] ] );
		$r->add_control( 'btn_style', [ 'label' => __( 'Button style', 'mirenova' ), 'type' => Controls_Manager::SELECT, 'options' => [ 'primary' => 'Primary', 'outline' => 'Outline', 'link' => 'Text link' ], 'default' => 'link', 'condition' => [ 'btn_text!' => '' ] ] );
		$this->media( 'decor', __( 'Corner decor image', 'mirenova' ), [], [ 'separator' => 'before', 'description' => __( 'Optional PNG/WebP that hangs over the card corner.', 'mirenova' ) ], $r );
		$r->add_responsive_control( 'decor_w', [ 'label' => __( 'Decor width', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 40, 'max' => 600 ] ], 'default' => [ 'unit' => 'px', 'size' => 210 ], 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}} .mrn-card__decor' => 'width: {{SIZE}}{{UNIT}};' ] ] );
		$r->add_responsive_control( 'decor_r', [ 'label' => __( 'Decor right', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => -300, 'max' => 300 ] ], 'default' => [ 'unit' => 'px', 'size' => -36 ], 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}} .mrn-card__decor' => 'right: {{SIZE}}{{UNIT}};' ] ] );
		$r->add_responsive_control( 'decor_t', [ 'label' => __( 'Decor top', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => -300, 'max' => 300 ] ], 'default' => [ 'unit' => 'px', 'size' => -84 ], 'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}} .mrn-card__decor' => 'top: {{SIZE}}{{UNIT}};' ] ] );
		$this->switcher( 'decor_hide_t', __( 'Hide decor on tablet', 'mirenova' ), 'yes', [], $r );
		$this->switcher( 'decor_hide_m', __( 'Hide decor on mobile', 'mirenova' ), 'yes', [], $r );

		$this->add_control(
			'cards',
			[
				'label'       => __( 'Cards', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ (title || "").replace(/<[^>]*>/g, "") }}}',
				'default'     => [
					[
						'image' => MRN::media( 'foto04' ),
						'icon'  => [ 'value' => 'mrn-i mrn-i-users', 'library' => 'mrn-icons' ],
						'title' => 'Equipo propio y coordinación de gremios',
						'text'  => '<p>El equipo ejecuta los trabajos principales y coordina los gremios necesarios. Para diseño, proyecto técnico, carpintería a medida y climatización contamos con profesionales colaboradores según el encargo.</p><p>Durante el proceso de reformas, nuestros profesionales supervisan la preparación de las superficies y la calidad de los acabados. Este control de obra permite revisar el resultado antes de avanzar.</p>',
					],
					[
						'image' => MRN::media( 'foto05' ),
						'icon'  => [ 'value' => 'mrn-i mrn-i-ruler', 'library' => 'mrn-icons' ],
						'title' => 'Taller propio y atención al detalle',
						'text'  => '<p>Nuestra experiencia en reformas incluye porcelánico de gran formato, encimeras y lavabos a medida. El taller permite adaptar estas piezas al espacio, cuidando encuentros, cortes y continuidad del acabado.</p>',
						'decor' => MRN::media( 'png02' ),
					],
				],
			]
		);
		$this->tag_control( 'card_title_tag', __( 'Card title HTML tag', 'mirenova' ), 'h3' );
		$this->end_controls_section();

		$this->start_controls_section( 's_layout', [ 'label' => __( 'Layout', 'mirenova' ) ] );
		$this->add_responsive_control(
			'cols',
			[
				'label'          => __( 'Columns', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( false, 4 ),
				'frontend_available' => true,
				'default'        => '2',
				'tablet_default' => '1',
				'mobile_default' => '1',
			]
		);
		$this->ratio_control( 'cards_ratio', __( 'Image ratio', 'mirenova' ), '56.25%' );
		$this->switcher( 'overlap', __( 'Overlap previous section', 'mirenova' ), 'yes', [ 'description' => __( 'Pulls the cards up over the section above (that section reserves the space).', 'mirenova' ) ] );
		$this->add_responsive_control(
			'overlap_size',
			[
				'label'      => __( 'Overlap amount', 'mirenova' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vw', 'custom' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 400 ] ],
				'selectors'  => [ '{{WRAPPER}}' => '--mrn-overlap: {{SIZE}}{{UNIT}};' ],
				'condition'  => [ 'overlap' => 'yes' ],
				'description' => __( 'Empty = design default (140–220px). Keep it equal to the extra bottom space of the section above.', 'mirenova' ),
			]
		);
		$this->end_controls_section();

		$this->filler_controls( [ 'image' => MRN::media( 'extra01' ) ] );

		$this->section_controls_open( [ 'anchor' => '', 'scheme' => 'dark' ] );
		$this->end_controls_section();

		$this->background_controls( [] );
		$this->decor_controls(
			[
				self::d( 'glow', null, null, null, [ 'color' => 'rgba(31,111,255,.2)', 'gx' => [ 'unit' => '%', 'size' => 80 ], 'gy' => [ 'unit' => '%', 'size' => 40 ] ] ),
				self::d( 'grad', 620, [ 'right', '-260px' ], [ 'top', '20%' ], [ 'opacity' => [ 'size' => 0.14 ], 'blur' => [ 'size' => 10 ] ] ),
				self::d( 'line', 320, [ 'left', '-140px' ], [ 'bottom', '-120px' ], [ 'color' => 'rgba(124,214,249,.2)', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
			]
		);
		$this->style_heading();
		$this->style_box( 'card', __( 'Card', 'mirenova' ), '{{WRAPPER}} .mrn-card', [ 'pad_selector' => '{{WRAPPER}} .mrn-card__body', 'gap' => true ] );
		$this->style_card_image();
		$this->style_text( 'cardtitle', __( 'Card title', 'mirenova' ), '{{WRAPPER}} .mrn-card__title' );
		$this->style_text( 'cardtext', __( 'Card text', 'mirenova' ), '{{WRAPPER}} .mrn-card__text' );
		$this->style_buttons();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$cards = array_values( (array) $s['cards'] );
		$count = count( $cards );
		$cols  = $this->cols( $s, 'cols', $count, 4 );
		$this->section_open( $s, 'mrn-cards' . ( self::on( $s, 'overlap' ) ? ' mrn-cards--overlap' : '' ) );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap">';
		$head = $this->eyebrow_html( $s ) . $this->title_html( $s ) . $this->text_html( $s ) . $this->buttons_html( $s );
		if ( $head ) {
			echo '<div class="mrn-head">' . $head . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<div class="mrn-cards__grid mrn-grid" style="' . esc_attr( self::grid_vars( $cols ) ) . '">';
		foreach ( $cards as $c ) {
			echo $this->card_html( $s, $c ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo $this->filler_html( $s, $cols, $count, 'mrn-card mrn-card--fill' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div></section>';
	}

	protected function card_html( $s, $c ) {
		$img   = MRN::img( $c['image'], 'large', [ 'loading' => 'lazy', 'sizes' => '(max-width: 1024px) 92vw, 620px' ] );
		$icon  = $this->icon_html( $c['icon'] );
		$decor = '';
		if ( ! empty( $c['decor']['url'] ) || ! empty( $c['decor']['id'] ) ) {
			$hide  = ( self::on( $c, 'decor_hide_t' ) ? ' mrn-hide-t' : '' ) . ( self::on( $c, 'decor_hide_m' ) ? ' mrn-hide-m' : '' );
			$decor = '<div class="mrn-card__decor' . $hide . '" aria-hidden="true">' . MRN::img( $c['decor'], 'medium_large', [ 'alt' => '', 'loading' => 'lazy', 'sizes' => '220px' ] ) . '</div>';
		}
		$btn = '';
		if ( ! empty( $c['btn_text'] ) ) {
			$btn = $this->buttons_html(
				[
					'cbtn' => [
						[
							'_id'       => $c['_id'] . 'b',
							'text'      => $c['btn_text'],
							'link_type' => $c['btn_type'],
							'link'      => isset( $c['btn_link'] ) ? $c['btn_link'] : [],
							'style'     => $c['btn_style'],
						],
					],
				],
				'cbtn'
			);
		}
		return '<article class="mrn-card elementor-repeater-item-' . esc_attr( $c['_id'] ) . '">'
			. '<div class="mrn-card__media">' . ( $img ? '<div class="mrn-card__img">' . $img . '</div>' : '' )
			. ( $icon ? '<span class="mrn-card__badge mrn-dia mrn-dia--grad" aria-hidden="true">' . $icon . '</span>' : '' ) . $decor . '</div>'
			. '<div class="mrn-card__body">' . self::inline_tag( $c['title'], $s['card_title_tag'], 'mrn-card__title' )
			. ( '' !== trim( (string) $c['text'] ) ? '<div class="mrn-text mrn-card__text">' . MRN::rich( $c['text'] ) . '</div>' : '' ) . $btn . '</div>'
			. '</article>';
	}
}
