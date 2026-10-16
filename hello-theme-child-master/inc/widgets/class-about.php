<?php
/**
 * MR About: text + framed photo with brand badge. Photo can be on the right or on the left.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;

class MRN_Widget_About extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-about';
	}

	public function get_title() {
		return 'MR About';
	}

	public function get_icon() {
		return 'eicon-image-box';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'about', 'image', 'text', 'split' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 's_content', [ 'label' => __( 'Content', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Sobre nosotros',
				'title'     => '<strong>MiRenova,</strong> tu empresa de reformas en Málaga',
				'text'      => '<p>MiRenova es la marca de reformas de ARVION STUDIO S.L. Nuestra empresa tiene su base en Málaga y trabaja con viviendas de la Costa del Sol, combinando equipo propio y profesionales especializados.</p><p>En las reformas, ayudamos a nuestros clientes a concretar sus expectativas: qué renovar, cómo aprovechar cada espacio y qué inversión requiere el proyecto. Nuestro compromiso es explicar las decisiones y mantener una comunicación clara durante la obra.</p>',
				'text_lead' => 'yes',
			]
		);
		$this->buttons_controls( [] );
		$this->end_controls_section();

		$this->start_controls_section( 's_image', [ 'label' => __( 'Image', 'mirenova' ) ] );
		$this->media( 'image', __( 'Photo', 'mirenova' ), MRN::media( 'foto03' ) );
		$this->image_size_control( 'image_size', 'large' );
		$this->add_control(
			'image_side',
			[
				'label'   => __( 'Photo position', 'mirenova' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'  => [ 'title' => __( 'Left (inverse)', 'mirenova' ), 'icon' => 'eicon-h-align-left' ],
					'right' => [ 'title' => __( 'Right', 'mirenova' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default' => 'right',
				'toggle'  => false,
			]
		);
		$this->add_control(
			'mobile_order',
			[
				'label'   => __( 'On mobile show first', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'text'  => __( 'Text', 'mirenova' ),
					'image' => __( 'Photo', 'mirenova' ),
				],
				'default' => 'text',
			]
		);
		$this->ratio_control( 'about_ratio', __( 'Aspect ratio', 'mirenova' ), '120%' );
		$this->image_fit_controls( 'image', '{{WRAPPER}} .mrn-about__photo img' );
		$this->switcher( 'frame', __( 'Outline frame', 'mirenova' ) );
		$this->switcher( 'badge', __( 'Diamond badge', 'mirenova' ) );
		$this->add_control(
			'badge_icon',
			[
				'label'     => __( 'Badge icon', 'mirenova' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [ 'value' => 'mrn-i mrn-i-home', 'library' => 'mrn-icons' ],
				'condition' => [ 'badge' => 'yes' ],
			]
		);
		$this->end_controls_section();

		$this->section_controls_open( [ 'anchor' => 'sobre', 'scheme' => 'light' ] );
		$this->switcher( 'space_strip', __( 'Extra top space (hero strip above)', 'mirenova' ), 'yes' );
		$this->switcher( 'space_cards', __( 'Extra bottom space (overlapping cards below)', 'mirenova' ), 'yes' );
		$this->end_controls_section();

		$this->background_controls(
			[
				'image'   => MRN::media( 'bg01' ),
				'pos'     => 'right center',
				'opacity' => 0.7,
				'ov1'     => self::gradient( '#FFFFFF', 30, 'rgba(255,255,255,.45)', 100, 90 ),
				'ov2'     => self::gradient( '#FFFFFF', 0, 'rgba(255,255,255,0)', 28, 180 ),
			]
		);
		$this->decor_controls(
			[
				self::d( 'soft', 260, [ 'left', '-130px' ], [ 'top', '40%' ], [ 'color' => '#EEF3F9', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
				self::d( 'line', 90, [ 'left', '60px' ], [ 'top', '66%' ], [ 'color' => '#CFE4FF', 'radius' => [ 'unit' => '%', 'size' => 14 ] ] ),
			]
		);
		$this->style_heading();

		$this->start_controls_section( 'st_image', [ 'label' => __( 'Photo & frame', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->add_responsive_control(
			'image_max',
			[
				'label'      => __( 'Max width', 'mirenova' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%' ],
				'range'      => [ 'px' => [ 'min' => 200, 'max' => 900 ] ],
				'selectors'  => [ '{{WRAPPER}} .mrn-about__media' => 'max-width: {{SIZE}}{{UNIT}};' ],
			]
		);
		$this->add_control( 'image_radius', [ 'label' => __( 'Radius', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ], 'selectors' => [ '{{WRAPPER}} .mrn-about__photo, {{WRAPPER}} .mrn-about__frame' => 'border-radius: {{SIZE}}px;' ] ] );
		$this->add_control( 'frame_color', [ 'label' => __( 'Frame color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-about__frame' => 'border-color: {{VALUE}};' ] ] );
		$this->add_control( 'badge_color', [ 'label' => __( 'Badge icon color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-about__badge' => 'color: {{VALUE}};' ] ] );
		$this->end_controls_section();
		$this->style_buttons();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$class = 'mrn-about'
			. ( 'left' === $s['image_side'] ? ' mrn-about--left' : '' )
			. ( 'image' === $s['mobile_order'] ? ' mrn-about--img-first' : '' )
			. ( self::on( $s, 'space_strip' ) ? ' mrn-sec--after-strip' : '' )
			. ( self::on( $s, 'space_cards' ) ? ' mrn-sec--before-overlap' : '' );
		$this->section_open( $s, $class );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap"><div class="mrn-split mrn-about__grid">';
		echo '<div class="mrn-about__text">' . $this->eyebrow_html( $s ) . $this->title_html( $s ) . $this->text_html( $s ) . $this->buttons_html( $s ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$img = MRN::img(
			$s['image'],
			$s['image_size'] ? $s['image_size'] : 'large',
			[
				'loading' => 'lazy',
				'sizes'   => '(max-width: 1080px) 82vw, 490px',
			]
		);
		if ( $img ) {
			echo '<div class="mrn-about__media">';
			if ( self::on( $s, 'frame' ) ) {
				echo '<div class="mrn-about__frame" aria-hidden="true"></div>';
			}
			echo '<div class="mrn-about__photo">' . $img . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( self::on( $s, 'badge' ) ) {
				echo '<span class="mrn-about__badge mrn-dia mrn-dia--grad" aria-hidden="true">' . $this->icon_html( $s['badge_icon'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
		}
		echo '</div></div></section>';
	}
}
