<?php
/**
 * MR Services: numbered service cards. First row of large cards + grid, numbers are automatic.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Services extends MRN_Widget_Cards {

	public function get_name() {
		return 'mrn-services';
	}

	public function get_title() {
		return 'MR Services';
	}

	public function get_icon() {
		return 'eicon-posts-grid';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'services', 'cards', 'numbers', 'grid' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 's_head', [ 'label' => __( 'Heading', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Servicios',
				'title'     => '<strong>Servicios de reformas</strong> para viviendas',
				'text'      => '<p>Nuestras reformas en Málaga abarcan viviendas completas, cocinas y baños. Cada servicio se define en el presupuesto, con los trabajos y suministros acordados para tu reforma integral.</p>',
				'text_lead' => '',
			]
		);
		$this->add_control(
			'head_layout',
			[
				'label'   => __( 'Heading layout', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'split'   => __( 'Title left, text right', 'mirenova' ),
					'stacked' => __( 'Stacked', 'mirenova' ),
				],
				'default' => 'split',
			]
		);
		$this->buttons_controls( [] );
		$this->end_controls_section();

		$this->start_controls_section( 's_items', [ 'label' => __( 'Services', 'mirenova' ) ] );
		$r = new Repeater();
		$this->media( 'image', __( 'Image', 'mirenova' ), [], [], $r );
		$this->wysiwyg( 'title', __( 'Title', 'mirenova' ), 'Servicio', [], $r );
		$this->wysiwyg( 'text', __( 'Text', 'mirenova' ), '<p>Descripción del servicio.</p>', [ 'description' => self::tokens_hint() ], $r );
		$r->add_control( 'btn_text', [ 'label' => __( 'Button text', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'separator' => 'before', 'dynamic' => [ 'active' => true ], 'description' => __( 'Empty = no button.', 'mirenova' ) ] );
		$r->add_control( 'btn_type', [ 'label' => __( 'Button link', 'mirenova' ), 'type' => Controls_Manager::SELECT, 'options' => self::link_types(), 'default' => 'url', 'condition' => [ 'btn_text!' => '' ] ] );
		$r->add_control( 'btn_link', [ 'label' => __( 'URL', 'mirenova' ), 'type' => Controls_Manager::URL, 'dynamic' => [ 'active' => true ], 'condition' => [ 'btn_text!' => '', 'btn_type' => 'url' ] ] );
		$r->add_control( 'btn_style', [ 'label' => __( 'Button style', 'mirenova' ), 'type' => Controls_Manager::SELECT, 'options' => [ 'primary' => 'Primary', 'outline' => 'Outline', 'link' => 'Text link' ], 'default' => 'link', 'condition' => [ 'btn_text!' => '' ] ] );
		$this->add_control(
			'items',
			[
				'label'       => __( 'Services', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ (title || "").replace(/<[^>]*>/g, "") }}}',
				'default'     => [
					[
						'image' => MRN::media( 'foto06' ),
						'title' => 'Reformas integrales de pisos y áticos',
						'text'  => '<p>Las reformas de pisos e inmuebles permiten renovar instalaciones, distribución y acabados en un mismo proyecto. Estudiamos cómo utilizas cada espacio para mejorar almacenamiento, iluminación y circulación.</p><p>En viviendas recién compradas, planificamos la reforma integral antes de la entrada a vivir, ajustando el alcance a las necesidades de sus propietarios.</p>',
					],
					[
						'image' => MRN::media( 'foto07' ),
						'title' => 'Reformas de casas y villas',
						'text'  => '<p>Coordinamos reformas de viviendas unifamiliares, desde el interior hasta las zonas exteriores incluidas en el encargo. Terrazas y áreas de BBQ se valoran por separado dentro del proyecto de tu casa.</p>',
					],
					[
						'image' => MRN::media( 'foto08' ),
						'title' => 'Reformas de cocinas',
						'text'  => '<p>Nuestro servicio de reformas de cocinas combina distribución, instalaciones y montaje. Trabajamos con mobiliario de fabricantes colaboradores o soluciones prefabricadas, incorporando encimeras adaptadas al uso diario de la cocina.</p><p>En este proceso, elegimos con los clientes materiales y equipamiento que aprovechen el espacio disponible y respondan al uso previsto de la cocina.</p>',
					],
					[
						'image' => MRN::media( 'foto09' ),
						'title' => 'Reformas de baños',
						'text'  => '<p>Las reformas de baños incluyen, según presupuesto, fontanería, impermeabilización, revestimientos y montaje. Prestamos atención al detalle en pendientes, juntas y encuentros para conseguir baños funcionales y facilitar su mantenimiento.</p>',
					],
					[
						'image' => MRN::media( 'foto10' ),
						'title' => 'Albañilería, instalaciones y carpintería',
						'text'  => '<p>Una reforma integral puede comprender demoliciones, retirada de escombros, albañilería, fontanería, electricidad y carpintería. La redistribución se estudia según las condiciones técnicas del inmueble.</p><p>Renovamos las instalaciones previstas en el proyecto conforme a los requisitos aplicables. Coordinamos las reformas con profesionales de distintos oficios para preparar cada fase antes de cerrar paredes o colocar revestimientos.</p>',
					],
				],
			]
		);
		$this->tag_control( 'card_title_tag', __( 'Card title HTML tag', 'mirenova' ), 'h3' );
		$this->switcher( 'numbers', __( 'Automatic numbers', 'mirenova' ) );
		$this->add_control(
			'num_format',
			[
				'label'     => __( 'Number format', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [
					'01' => '01, 02, 03…',
					'1'  => '1, 2, 3…',
				],
				'default'   => '01',
				'condition' => [ 'numbers' => 'yes' ],
			]
		);
		$this->add_control( 'num_start', [ 'label' => __( 'Start from', 'mirenova' ), 'type' => Controls_Manager::NUMBER, 'default' => 1, 'min' => 0, 'condition' => [ 'numbers' => 'yes' ] ] );
		$this->end_controls_section();

		$this->start_controls_section( 's_layout', [ 'label' => __( 'Layout', 'mirenova' ) ] );
		$this->add_control(
			'featured_count',
			[
				'label'       => __( 'Large cards in the first row', 'mirenova' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'max'         => 6,
				'default'     => 2,
				'description' => __( '0 = all cards in one grid.', 'mirenova' ),
			]
		);
		$this->add_responsive_control(
			'featured_cols',
			[
				'label'          => __( 'Columns — large cards', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( true, 4 ),
				'frontend_available' => true,
				'default'        => 'auto',
				'tablet_default' => '1',
				'mobile_default' => '1',
				'condition'      => [ 'featured_count!' => 0 ],
			]
		);
		$this->add_responsive_control(
			'cols',
			[
				'label'          => __( 'Columns — other cards', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( false, 6 ),
				'frontend_available' => true,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
			]
		);
		$this->ratio_control( 'lg_ratio', __( 'Image ratio — large', 'mirenova' ), '62.5%' );
		$this->ratio_control( 'sm_ratio', __( 'Image ratio — other', 'mirenova' ), '75%' );
		$this->switcher( 'lift', __( 'Lift cards on hover', 'mirenova' ) );
		$this->end_controls_section();

		$this->filler_controls( [ 'image' => MRN::media( 'png02' ) ] );

		$this->section_controls_open( [ 'anchor' => 'servicios', 'scheme' => 'light' ] );
		$this->end_controls_section();

		$this->background_controls( [] );
		$this->decor_controls(
			[
				self::d( 'grad', 460, [ 'left', '-250px' ], [ 'top', '30%' ], [ 'shadow' => 'yes', 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'soft', 220, [ 'left', '-90px' ], [ 'top', '14%' ], [ 'color' => '#E4EEFB', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
				self::d( 'line', 120, [ 'left', '120px' ], [ 'top', '63%' ], [ 'color' => '#A9D8F7', 'radius' => [ 'unit' => '%', 'size' => 14 ] ] ),
				self::d( 'soft', 360, [ 'right', '-140px' ], [ 'bottom', '-120px' ], [ 'color' => '#F2F5F9', 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'image', 260, [ 'left', '0px' ], [ 'bottom', '4%' ], [ 'image' => MRN::media( 'png04' ), 'hide_t' => 'yes', 'hide_m' => 'yes' ] ),
			]
		);
		$this->style_heading( '.mrn-head ' );
		$this->style_box( 'card', __( 'Card', 'mirenova' ), '{{WRAPPER}} .mrn-card', [ 'pad_selector' => '{{WRAPPER}} .mrn-card__body', 'gap' => true ] );
		$this->style_card_image();
		$this->style_text( 'cardtitle', __( 'Card title', 'mirenova' ), '{{WRAPPER}} .mrn-card__title' );
		$this->style_text( 'cardtext', __( 'Card text', 'mirenova' ), '{{WRAPPER}} .mrn-card__text' );
		$this->style_text( 'num', __( 'Number badge', 'mirenova' ), '{{WRAPPER}} .mrn-card__num' );
		$this->style_buttons();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_values( (array) $s['items'] );
		$n     = count( $items );
		$fc    = max( 0, min( (int) $s['featured_count'], $n ) );

		$this->section_open( $s, 'mrn-services' . ( self::on( $s, 'lift' ) ? ' mrn-lift' : '' ) );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap">';

		$left  = $this->eyebrow_html( $s ) . $this->title_html( $s );
		$right = $this->text_html( $s ) . $this->buttons_html( $s );
		if ( $left || $right ) {
			$split = 'split' === $s['head_layout'];
			echo '<div class="mrn-head' . ( $split ? ' mrn-head--split' : '' ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $split ? '<div class="mrn-head__main">' . $left . '</div><div class="mrn-head__aside">' . $right . '</div>' : $left . $right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}

		$groups = [];
		if ( $fc ) {
			$groups[] = [ array_slice( $items, 0, $fc ), 'featured_cols', 'mrn-services__grid--lg', 0 ];
		}
		if ( $n > $fc ) {
			$groups[] = [ array_slice( $items, $fc ), 'cols', 'mrn-services__grid--sm', $fc ];
		}
		foreach ( $groups as $g ) {
			list( $list, $key, $class, $offset ) = $g;
			$count = count( $list );
			$cols  = $this->cols( $s, $key, $count, 4 );
			echo '<div class="mrn-services__grid mrn-grid ' . esc_attr( $class ) . '" style="' . esc_attr( self::grid_vars( $cols ) ) . '">';
			foreach ( $list as $i => $c ) {
				echo $this->service_html( $s, $c, $offset + $i ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo $this->filler_html( $s, $cols, $count, 'mrn-card mrn-card--fill' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}
		echo '</div></section>';
	}

	protected function service_html( $s, $c, $index ) {
		$img = MRN::img( $c['image'], 'large', [ 'loading' => 'lazy', 'sizes' => '(max-width: 767px) 92vw, (max-width: 1024px) 50vw, 620px' ] );
		$num = self::on( $s, 'numbers' ) ? '<span class="mrn-card__badge mrn-dia mrn-dia--grad"><span class="mrn-card__num">' . esc_html( self::number( $index, $s['num_format'], $s['num_start'] ) ) . '</span></span>' : '';
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
		return '<article class="mrn-card mrn-card--service elementor-repeater-item-' . esc_attr( $c['_id'] ) . '">'
			. '<div class="mrn-card__media">' . ( $img ? '<div class="mrn-card__img">' . $img . '</div>' : '' ) . $num . '</div>'
			. '<div class="mrn-card__body">' . self::inline_tag( $c['title'], $s['card_title_tag'], 'mrn-card__title' )
			. ( '' !== trim( (string) $c['text'] ) ? '<div class="mrn-text mrn-card__text">' . MRN::rich( $c['text'] ) . '</div>' : '' ) . $btn . '</div>'
			. '</article>';
	}
}
