<?php
/**
 * MR Process: numbered steps with connector lines; columns fit the number of steps automatically.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Steps extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-steps';
	}

	public function get_title() {
		return 'MR Process Steps';
	}

	public function get_icon() {
		return 'eicon-time-line';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'process', 'steps', 'timeline', 'numbers' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 's_head', [ 'label' => __( 'Heading', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Proceso',
				'title'     => 'Cómo organizamos <strong>tu reforma integral</strong>',
				'text'      => '<p>Nuestro proceso de reformas ordena decisiones, compras y ejecución. Los clientes conocen el alcance de la obra y los puntos que necesitan su aprobación.</p>',
				'text_lead' => '',
			]
		);
		$this->buttons_controls( [] );
		$this->end_controls_section();

		$this->start_controls_section( 's_steps', [ 'label' => __( 'Steps', 'mirenova' ) ] );
		$r = new Repeater();
		$this->wysiwyg( 'title', __( 'Title', 'mirenova' ), 'Paso', [], $r );
		$this->wysiwyg( 'text', __( 'Text', 'mirenova' ), '<p>Descripción del paso.</p>', [ 'description' => self::tokens_hint() ], $r );
		$r->add_control(
			'icon',
			[
				'label'       => __( 'Icon instead of number', 'mirenova' ),
				'type'        => Controls_Manager::ICONS,
				'default'     => [ 'value' => '', 'library' => '' ],
				'description' => __( 'Leave empty to show the automatic number.', 'mirenova' ),
			]
		);
		$this->add_control(
			'steps',
			[
				'label'       => __( 'Steps', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ (title || "").replace(/<[^>]*>/g, "") }}}',
				'default'     => [
					[
						'title' => 'Visita y planificación',
						'text'  => '<p>Recogemos tus necesidades, medimos el inmueble y preparamos un planteamiento inicial. En esta fase de las reformas estudiamos distribución, estado de las viviendas y prioridades del proyecto.</p>',
					],
					[
						'title' => 'Presupuesto y contrato',
						'text'  => '<p>El presupuesto de reformas detalla trabajos, materiales, mano de obra y partidas acordadas. Formalizamos las condiciones mediante contrato y emitimos facturas a través de nuestra empresa.</p><p>Recomendamos comparar varios presupuestos con el mismo alcance: una diferencia de precio puede responder a suministros o trabajos que una propuesta no incluye.</p>',
					],
					[
						'title' => 'Ejecución y control de obra',
						'text'  => '<p>Organizamos compras, entregas y coordinación de gremios durante las reformas. Confirmamos los plazos de obra según disponibilidad del equipo, definición técnica y suministros.</p><p>Si aparecen imprevistos, explicamos las soluciones y acordamos los cambios antes de ejecutarlos. Nuestro compromiso con la transparencia incluye actualizar el presupuesto cuando cambia el alcance de la obra.</p>',
					],
					[
						'title' => 'Revisión y entrega',
						'text'  => '<p>Al finalizar las reformas revisamos los trabajos y las instalaciones con el cliente. Registramos las observaciones que deban resolverse y formalizamos la finalización conforme al contrato.</p>',
					],
				],
			]
		);
		$this->tag_control( 'step_title_tag', __( 'Step title HTML tag', 'mirenova' ), 'h3' );
		$this->add_control(
			'num_format',
			[
				'label'   => __( 'Number format', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'01' => '01, 02, 03…',
					'1'  => '1, 2, 3…',
				],
				'default' => '01',
			]
		);
		$this->switcher( 'first_hl', __( 'Highlight first step', 'mirenova' ) );
		$this->switcher( 'last_check', __( 'Check icon on last step', 'mirenova' ) );
		$this->end_controls_section();

		$this->start_controls_section( 's_layout', [ 'label' => __( 'Layout', 'mirenova' ) ] );
		$this->add_responsive_control(
			'cols',
			[
				'label'          => __( 'Columns', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( true, 6 ),
				'frontend_available' => true,
				'default'        => 'auto',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'description'    => __( 'Auto = one column per step (up to 5).', 'mirenova' ),
			]
		);
		$this->switcher( 'lines', __( 'Connector lines (desktop)', 'mirenova' ) );
		$this->end_controls_section();

		$this->section_controls_open( [ 'anchor' => 'proceso', 'scheme' => 'dark' ] );
		$this->end_controls_section();

		$this->background_controls(
			[
				'image'   => MRN::media( 'bg02' ),
				'opacity' => 0.14,
				'ov1'     => self::gradient( 'rgba(10,29,56,.9)', 0, 'rgba(10,29,56,.35)', 100, 90 ),
			]
		);
		$this->decor_controls(
			[
				self::d( 'line', 520, [ 'right', '-230px' ], [ 'top', '6%' ], [ 'color' => 'rgba(124,214,249,.2)', 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'grad', 360, [ 'right', '-150px' ], [ 'top', '14%' ], [ 'opacity' => [ 'size' => 0.22 ], 'blur' => [ 'size' => 6 ], 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'soft', 140, [ 'right', '12%' ], [ 'bottom', '-60px' ], [ 'color' => '#1F6FFF', 'opacity' => [ 'size' => 0.35 ], 'radius' => [ 'unit' => '%', 'size' => 14 ] ] ),
				self::d( 'image', 280, [ 'right', '2%' ], [ 'top', '7%' ], [ 'image' => MRN::media( 'png03' ), 'hide_t' => 'yes', 'hide_m' => 'yes' ] ),
			]
		);
		$this->style_heading( '.mrn-head ' );
		$this->style_text( 'steptitle', __( 'Step title', 'mirenova' ), '{{WRAPPER}} .mrn-step__title' );
		$this->style_text( 'steptext', __( 'Step text', 'mirenova' ), '{{WRAPPER}} .mrn-step__text' );
		$this->start_controls_section( 'st_badge', [ 'label' => __( 'Number badges', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->add_control( 'badge_color', [ 'label' => __( 'Number color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-step__badge' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'badge_bg', [ 'label' => __( 'Badge fill', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}}' => '--mrn-step-bg: {{VALUE}};' ] ] );
		$this->add_control( 'badge_line', [ 'label' => __( 'Badge outline', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}}' => '--mrn-step-line: {{VALUE}};' ] ] );
		$this->add_control( 'connector', [ 'label' => __( 'Connector color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-step__line' => 'background: linear-gradient(90deg, {{VALUE}}, transparent);' ] ] );
		$this->end_controls_section();
		$this->style_buttons();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$steps = array_values( (array) $s['steps'] );
		$count = count( $steps );
		$cols  = $this->cols( $s, 'cols', $count, 5 );
		$tag   = self::tag( $s['step_title_tag'], 'h3' );

		$this->section_open( $s, 'mrn-steps' );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap">';
		$head = $this->eyebrow_html( $s ) . $this->title_html( $s ) . $this->text_html( $s ) . $this->buttons_html( $s );
		if ( $head ) {
			echo '<div class="mrn-head mrn-head--narrow">' . $head . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<ol class="mrn-steps__grid mrn-grid" style="' . esc_attr( self::grid_vars( $cols ) ) . '">';
		foreach ( $steps as $i => $st ) {
			$last     = $i === $count - 1;
			$row_end  = 0 === ( $i + 1 ) % $cols[0];
			$variant  = ( 0 === $i && self::on( $s, 'first_hl' ) ) ? 'grad' : ( $last && self::on( $s, 'last_check' ) ? 'done' : 'line' );
			$icon     = $this->icon_html( isset( $st['icon'] ) ? $st['icon'] : [] );
			if ( ! $icon && 'done' === $variant ) {
				$icon = MRN_Icons::svg( 'check', [ 'stroke-width' => '2.4' ] );
			}
			$badge = $icon ? $icon : '<span class="mrn-step__num">' . esc_html( self::number( $i, $s['num_format'] ) ) . '</span>';
			echo '<li class="mrn-step elementor-repeater-item-' . esc_attr( $st['_id'] ) . '">';
			if ( self::on( $s, 'lines' ) && ! $last && ! $row_end ) {
				echo '<span class="mrn-step__line" aria-hidden="true"></span>';
			}
			echo '<span class="mrn-step__badge mrn-dia mrn-dia--' . esc_attr( $variant ) . '">' . $badge . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo self::inline_tag( $st['title'], $tag, 'mrn-step__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( '' !== trim( (string) $st['text'] ) ) {
				echo '<div class="mrn-text mrn-step__text">' . MRN::rich( $st['text'] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</li>';
		}
		echo '</ol></div></section>';
	}
}
