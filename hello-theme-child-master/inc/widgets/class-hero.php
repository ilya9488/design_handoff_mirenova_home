<?php
/**
 * MR Hero: background slideshow (LCP-friendly), H1, quote form (Contact Form 7), benefits strip.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Hero extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-hero';
	}

	public function get_title() {
		return 'MR Hero';
	}

	public function get_icon() {
		return 'eicon-slider-full-screen';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'hero', 'slider', 'form', 'banner' ];
	}

	public function get_script_depends() {
		return [ 'mrn' ];
	}

	protected function is_dynamic_content(): bool {
		return true; // Contains a form.
	}

	protected function register_controls() {
		/* ---------- Slides ---------- */
		$this->start_controls_section( 's_slides', [ 'label' => __( 'Background slides', 'mirenova' ) ] );
		$r = new Repeater();
		$this->media( 'image', __( 'Image', 'mirenova' ), [], [], $r );
		$r->add_control(
			'pos',
			[
				'label'     => __( 'Focus point', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					''              => __( 'Center', 'mirenova' ),
					'center top'    => 'Top',
					'center bottom' => 'Bottom',
					'left center'   => 'Left',
					'right center'  => 'Right',
				],
				'selectors' => [ '{{WRAPPER}} {{CURRENT_ITEM}}' => 'object-position: {{VALUE}};' ],
			]
		);
		$slides = [];
		foreach ( [ 'foto01', 'foto06', 'foto07', 'foto08', 'foto09' ] as $k ) {
			$slides[] = [ 'image' => MRN::media( $k ) ];
		}
		$this->add_control(
			'slides',
			[
				'label'       => __( 'Slides', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'default'     => $slides,
				'title_field' => '<img src="{{ image.url }}" style="height:22px;width:34px;object-fit:cover;vertical-align:middle;margin-right:6px">' . __( 'Slide', 'mirenova' ),
			]
		);
		$this->image_size_control( 'slides_size', 'full' );
		$this->add_control(
			'interval',
			[
				'label'   => __( 'Seconds per slide', 'mirenova' ),
				'type'    => Controls_Manager::NUMBER,
				'min'     => 2,
				'max'     => 30,
				'step'    => 0.5,
				'default' => 6,
			]
		);
		$this->add_control(
			'fade',
			[
				'label'     => __( 'Crossfade (seconds)', 'mirenova' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 0,
				'max'       => 5,
				'step'      => 0.1,
				'default'   => 1.2,
				'selectors' => [ '{{WRAPPER}}' => '--mrn-fade: {{VALUE}}s;' ],
			]
		);
		$this->switcher( 'kenburns', __( 'Slow zoom (Ken Burns)', 'mirenova' ) );
		$this->add_control(
			'lazy',
			[
				'label'       => __( 'Load slides 2+', 'mirenova' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [
					'idle'        => __( 'On first interaction or after a delay', 'mirenova' ),
					'interaction' => __( 'Only on first interaction', 'mirenova' ),
				],
				'default'     => 'idle',
				'description' => __( 'Slide 1 loads with high priority (best LCP). Other slides wait for scroll / touch / mouse / key so they never compete with the first paint.', 'mirenova' ),
			]
		);
		$this->add_control(
			'idle_delay',
			[
				'label'     => __( 'Delay (seconds)', 'mirenova' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 30,
				'default'   => 5,
				'condition' => [ 'lazy' => 'idle' ],
			]
		);
		$this->end_controls_section();

		/* ---------- Content ---------- */
		$this->start_controls_section( 's_content', [ 'label' => __( 'Content', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Málaga · Costa del Sol',
				'title'     => '<strong>Reformas integrales</strong> en Málaga para renovar tu hogar',
				'title_tag' => 'h1',
				'text'      => '<p>En MiRenova realizamos reformas en la provincia de Málaga para particulares que quieren actualizar su piso, casa o villa. Organizamos tu reforma integral, desde la planificación hasta la entrega, con presupuesto detallado y coordinación de los trabajos.</p><p>Adaptamos nuestras reformas a tus necesidades, al estado de la vivienda y a la calidad de los acabados que buscas. Consulta inicial, visita con mediciones y presupuesto gratuitos.</p>',
				'text_lead' => 'yes',
			]
		);
		$this->buttons_controls(
			[
				[
					'text'      => 'Llamar al {phone}',
					'link_type' => 'phone',
					'style'     => 'outline',
				],
			]
		);
		$this->end_controls_section();

		/* ---------- Form ---------- */
		$forms = (array) get_option( 'mrn_forms', [] );
		$this->start_controls_section( 's_form', [ 'label' => __( 'Form', 'mirenova' ) ] );
		$this->switcher( 'form_on', __( 'Show form card', 'mirenova' ) );
		$this->add_control(
			'form_id',
			[
				'label'       => __( 'Contact Form 7', 'mirenova' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [ '' => __( '— Select a form —', 'mirenova' ) ] + MRN::forms(),
				'default'     => isset( $forms['hero'] ) ? (string) $forms['hero'] : '',
				'condition'   => [ 'form_on' => 'yes' ],
				'description' => __( 'Forms are listed from Contact → Contact Forms.', 'mirenova' ),
			]
		);
		$this->wysiwyg( 'form_title', __( 'Card title', 'mirenova' ), 'Solicita presupuesto', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->tag_control( 'form_title_tag', __( 'Card title HTML tag', 'mirenova' ), 'h2', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->wysiwyg( 'form_sub', __( 'Card subtitle', 'mirenova' ), 'Consulta inicial, visita con mediciones y presupuesto gratuitos.', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->add_control(
			'form_sent',
			[
				'label'     => __( 'Button text after sending', 'mirenova' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => 'Solicitud enviada',
				'condition' => [ 'form_on' => 'yes' ],
			]
		);
		$this->switcher( 'form_decor', __( 'Card corner diamonds', 'mirenova' ), 'yes', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->end_controls_section();

		/* ---------- Strip ---------- */
		$this->start_controls_section( 's_strip', [ 'label' => __( 'Benefits strip', 'mirenova' ) ] );
		$this->switcher( 'strip_on', __( 'Show strip', 'mirenova' ) );
		$r = new Repeater();
		$r->add_control(
			'type',
			[
				'label'   => __( 'Cell type', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'benefit' => __( 'Benefit (icon + text)', 'mirenova' ),
					'cta'     => __( 'Call to action (dark)', 'mirenova' ),
				],
				'default' => 'benefit',
			]
		);
		$r->add_control(
			'icon',
			[
				'label'     => __( 'Icon', 'mirenova' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => [ 'value' => 'mrn-i mrn-i-check', 'library' => 'mrn-icons' ],
				'condition' => [ 'type' => 'benefit' ],
			]
		);
		$this->wysiwyg( 'title', __( 'Title', 'mirenova' ), 'Consulta inicial', [], $r );
		$this->wysiwyg( 'sub', __( 'Subtitle', 'mirenova' ), 'gratuita', [ 'description' => self::tokens_hint() ], $r );
		$r->add_control(
			'link_type',
			[
				'label'   => __( 'Link', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => self::link_types(),
				'default' => 'none',
			]
		);
		$r->add_control(
			'link',
			[
				'label'     => __( 'URL', 'mirenova' ),
				'type'      => Controls_Manager::URL,
				'dynamic'   => [ 'active' => true ],
				'condition' => [ 'link_type' => 'url' ],
			]
		);
		$this->add_control(
			'strip',
			[
				'label'         => __( 'Cells', 'mirenova' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $r->get_controls(),
				'prevent_empty' => false,
				'title_field'   => '{{{ (title || "").replace(/<[^>]*>/g, "") }}} {{{ (sub || "").replace(/<[^>]*>/g, "") }}}',
				'default'       => [
					[ 'type' => 'benefit', 'icon' => [ 'value' => 'mrn-i mrn-i-users', 'library' => 'mrn-icons' ], 'title' => 'Consulta inicial', 'sub' => 'gratuita', 'link_type' => 'none' ],
					[ 'type' => 'benefit', 'icon' => [ 'value' => 'mrn-i mrn-i-ruler', 'library' => 'mrn-icons' ], 'title' => 'Visita con mediciones', 'sub' => 'gratuita', 'link_type' => 'none' ],
					[ 'type' => 'benefit', 'icon' => [ 'value' => 'mrn-i mrn-i-file-text', 'library' => 'mrn-icons' ], 'title' => 'Presupuesto detallado', 'sub' => 'gratuito', 'link_type' => 'none' ],
					[ 'type' => 'cta', 'title' => 'Llamar', 'sub' => '{phone}', 'link_type' => 'phone' ],
				],
				'condition'     => [ 'strip_on' => 'yes' ],
			]
		);
		$this->tag_control( 'strip_title_tag', __( 'Cell title HTML tag', 'mirenova' ), 'div', [ 'condition' => [ 'strip_on' => 'yes' ] ] );
		$this->add_responsive_control(
			'strip_cols',
			[
				'label'          => __( 'Columns', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( true, 6 ),
				'frontend_available' => true,
				'default'        => 'auto',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'condition'      => [ 'strip_on' => 'yes' ],
			]
		);
		$this->switcher( 'strip_overlap', __( 'Overlap next section', 'mirenova' ), 'yes', [ 'condition' => [ 'strip_on' => 'yes' ], 'description' => __( 'The strip sits half over the next section (desktop & tablet).', 'mirenova' ) ] );
		$this->end_controls_section();

		/* ---------- Section ---------- */
		$this->section_controls_open( [ 'anchor' => 'inicio', 'scheme' => 'dark' ] );
		$this->add_responsive_control(
			'min_h',
			[
				'label'      => __( 'Min height', 'mirenova' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'vh', 'svh' ],
				'range'      => [ 'px' => [ 'min' => 0, 'max' => 1200 ] ],
				'selectors'  => [ '{{WRAPPER}} .mrn-hero__inner' => 'min-height: {{SIZE}}{{UNIT}};' ],
			]
		);
		$this->end_controls_section();

		$this->filler_controls( [] );

		/* ---------- Style ---------- */
		$this->background_controls(
			[
				'no_image' => true,
				'ov1'      => self::gradient( 'rgba(10,29,56,.94)', 20, 'rgba(10,29,56,.3)', 100, 90 ),
				'ov2'      => self::gradient( 'rgba(10,29,56,.92)', 0, 'rgba(10,29,56,0)', 45, 0 ),
			]
		);
		$this->decor_controls(
			[
				self::d( 'image', [ 'unit' => 'custom', 'size' => 'clamp(230px,42vw,640px)' ], [ 'right', 'clamp(-280px,-15vw,-110px)' ], [ 'top', 'clamp(-300px,-17vw,-120px)' ], [ 'image' => MRN::media( 'extra01' ), 'opacity' => [ 'size' => 0.8 ], 'shadow' => 'yes' ] ),
				self::d( 'line', [ 'unit' => 'custom', 'size' => 'clamp(180px,26vw,400px)' ], [ 'right', 'clamp(-150px,-6vw,-60px)' ], [ 'top', 'clamp(-190px,-9vw,-70px)' ], [ 'color' => 'rgba(124,214,249,.22)', 'line_w' => [ 'size' => 1.5 ] ] ),
			]
		);
		$this->style_heading( '.mrn-hero__content ' );
		$this->style_box( 'formcard', __( 'Form card', 'mirenova' ), '{{WRAPPER}} .mrn-formcard' );
		$this->style_text( 'formtitle', __( 'Form card title', 'mirenova' ), '{{WRAPPER}} .mrn-formcard__title' );
		$this->style_box( 'strip', __( 'Strip cells', 'mirenova' ), '{{WRAPPER}} .mrn-strip__item:not(.mrn-strip__item--cta)' );
		$this->style_text( 'striptitle', __( 'Strip title', 'mirenova' ), '{{WRAPPER}} .mrn-strip__title' );
		$this->style_box( 'stripcta', __( 'Strip CTA cell', 'mirenova' ), '{{WRAPPER}} .mrn-strip__item--cta' );
		$this->style_buttons();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$slides = [];
		foreach ( (array) $s['slides'] as $sl ) {
			if ( ! empty( $sl['image']['url'] ) || ! empty( $sl['image']['id'] ) ) {
				$slides[] = $sl;
			}
		}
		$strip   = self::on( $s, 'strip_on' ) && ! empty( $s['strip'] );
		$overlap = $strip && self::on( $s, 'strip_overlap' );
		$cfg     = [
			'i'    => (int) round( max( 2, (float) $s['interval'] ) * 1000 ),
			'lazy' => 'interaction' === $s['lazy'] ? 'interaction' : 'idle',
			'idle' => (int) round( max( 1, (float) ( isset( $s['idle_delay'] ) ? $s['idle_delay'] : 5 ) ) * 1000 ),
		];
		$class   = 'mrn-hero' . ( $strip ? '' : ' mrn-hero--nostrip' ) . ( $overlap ? ' mrn-hero--overlap' : '' ) . ( self::on( $s, 'kenburns' ) ? ' mrn-hero--kb' : '' );

		$this->section_open( $s, $class, ' data-mrn-hero="' . esc_attr( wp_json_encode( $cfg ) ) . '"' );

		$size = ! empty( $s['slides_size'] ) ? $s['slides_size'] : 'full';
		$html = '';
		foreach ( $slides as $i => $sl ) {
			$cls = 'mrn-hero__slide elementor-repeater-item-' . $sl['_id'] . ( 0 === $i ? ' is-on' : '' );
			if ( 0 === $i ) {
				$html .= MRN::img(
					$sl['image'],
					$size,
					[
						'class'         => $cls,
						'loading'       => 'eager',
						'fetchpriority' => 'high',
						'sizes'         => '100vw',
					]
				);
				continue;
			}
			$img   = MRN::img( $sl['image'], $size, [ 'class' => $cls, 'sizes' => '100vw', 'loading' => 'lazy' ] );
			$img   = preg_replace( '/\s(src|srcset|sizes)="/', ' data-$1="', $img );
			$img   = str_replace( [ ' loading="lazy"', '<img ' ], [ '', '<img src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" ' ], $img );
			$html .= $img;
		}
		echo $this->background_html( $s, $html ? '<div class="mrn-hero__slides">' . $html . '</div>' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		$form = self::on( $s, 'form_on' );
		echo '<div class="mrn-wrap mrn-hero__inner"><div class="mrn-hero__grid' . ( $form ? '' : ' mrn-hero__grid--solo' ) . '">';
		echo '<div class="mrn-hero__content">';
		echo $this->eyebrow_html( $s ) . $this->title_html( $s, 'title', 'mrn-title mrn-title--h1', 'h1' ) . $this->text_html( $s ) . $this->buttons_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
		if ( $form ) {
			echo '<div class="mrn-formcard mrn-hero__form">' . ( self::on( $s, 'form_decor' ) ? '<div class="mrn-formcard__decor" aria-hidden="true"><span></span><span></span></div>' : '' );
			echo self::inline_tag( $s['form_title'], $s['form_title_tag'], 'mrn-formcard__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$sub = MRN::inline( $s['form_sub'] );
			if ( '' !== $sub ) {
				echo '<p class="mrn-formcard__sub">' . $sub . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo MRN_CF7::render( $s['form_id'], $s['form_sent'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}
		echo '</div></div>';

		if ( $strip ) {
			$items = array_values( (array) $s['strip'] );
			$count = count( $items );
			$cols  = $this->cols( $s, 'strip_cols', $count, 6 );
			echo '<div class="mrn-wrap mrn-hero__strip"><div class="mrn-strip mrn-grid" style="' . esc_attr( self::grid_vars( $cols ) ) . '">';
			foreach ( $items as $it ) {
				$cta   = isset( $it['type'] ) && 'cta' === $it['type'];
				$cls   = 'mrn-strip__item' . ( $cta ? ' mrn-strip__item--cta' : '' ) . ' elementor-repeater-item-' . $it['_id'];
				$attrs = $this->link_attrs( isset( $it['link_type'] ) ? $it['link_type'] : 'none', isset( $it['link'] ) ? $it['link'] : [], $cls );
				$tag   = $attrs ? 'a' : 'div';
				$open  = $attrs ? '<a ' . $attrs . '>' : '<div class="' . esc_attr( $cls ) . '">';
				if ( $cta ) {
					$inner = self::inline_tag( $it['title'], 'span', 'mrn-strip__label' ) . self::inline_tag( $it['sub'], 'span', 'mrn-strip__value' );
				} else {
					$icon  = $this->icon_html( isset( $it['icon'] ) ? $it['icon'] : [] );
					$inner = ( $icon ? '<span class="mrn-strip__icon mrn-dia mrn-dia--soft">' . $icon . '</span>' : '' )
						. '<div class="mrn-strip__body">' . self::inline_tag( $it['title'], $s['strip_title_tag'], 'mrn-strip__title' ) . self::inline_tag( $it['sub'], 'div', 'mrn-strip__sub' ) . '</div>';
				}
				echo $open . $inner . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo $this->filler_html( $s, $cols, $count, 'mrn-strip__fill' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div></div>';
		}
		echo '</section>';
	}
}
