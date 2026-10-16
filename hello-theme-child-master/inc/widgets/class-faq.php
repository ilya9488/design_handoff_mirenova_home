<?php
/**
 * MR FAQ: intro column + accessible accordion (button + region), optional FAQPage schema.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Faq extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-faq';
	}

	public function get_title() {
		return 'MR FAQ';
	}

	public function get_icon() {
		return 'eicon-accordion';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'faq', 'questions', 'accordion', 'preguntas' ];
	}

	public function get_script_depends() {
		return [ 'mrn' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 's_intro', [ 'label' => __( 'Intro', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Preguntas',
				'title'     => 'Preguntas sobre <strong>nuestras reformas</strong>',
				'text'      => '',
				'text_lead' => '',
			]
		);
		$this->buttons_controls( [] );
		$this->media( 'intro_image', __( 'Decor image under the title', 'mirenova' ), MRN::media( 'png05' ), [ 'separator' => 'before' ] );
		$this->switcher( 'intro_image_desktop', __( 'Decor image on desktop only', 'mirenova' ) );
		$this->switcher( 'sticky', __( 'Sticky intro (desktop)', 'mirenova' ) );
		$this->end_controls_section();

		$this->start_controls_section( 's_items', [ 'label' => __( 'Questions', 'mirenova' ) ] );
		$r = new Repeater();
		$this->wysiwyg( 'q', __( 'Question', 'mirenova' ), '¿Pregunta?', [], $r );
		$this->wysiwyg( 'a', __( 'Answer', 'mirenova' ), '<p>Respuesta.</p>', [ 'description' => self::tokens_hint() ], $r );
		$qa = [
			[ '¿Qué incluye el servicio llave en mano?', '<p>Una reforma integral llave en mano reúne planificación, compras, coordinación y trabajos contratados. En nuestras reformas en Málaga, diseño, mobiliario y equipamiento se incluyen según el presupuesto aprobado.</p>' ],
			[ '¿Quién tramita los permisos de obra?', '<p>El trámite municipal depende de la intervención; puede corresponder una licencia o declaración responsable. Puedes gestionarlo directamente o contratar nuestro servicio de acompañamiento administrativo y jurídico, con coste separado.</p>' ],
			[ '¿Qué garantías tienen las reformas?', '<p>Nuestra empresa de reformas dispone de seguro de responsabilidad civil. Ofrecemos tres años de garantía sobre nuestros trabajos de fontanería, electricidad e impermeabilización, con alcance y procedimiento recogidos en contrato, sin limitar los derechos legales del cliente.</p><p>Las garantías de los equipos se distinguen de las correspondientes a los trabajos. El servicio posterior a la entrega permite comunicar incidencias y valorar su resolución con nuestros profesionales.</p>' ],
			[ '¿Cuánto tarda una reforma integral en Málaga?', '<p>La duración de las reformas depende del alcance, los suministros y las condiciones de obra. Acordamos los plazos para cada proyecto, sin prometer una duración idéntica para todas las viviendas.</p>' ],
			[ '¿Ofrecéis reformas para clientes internacionales?', '<p>Ofrecemos consultas y presupuestos en español, inglés y ruso; los contratos se redactan en español. Si resides fuera, podemos acordar un sistema de comunicación y seguimiento antes de contratar las reformas.</p>' ],
			[ '¿Aceptáis pequeñas reparaciones o reformas parciales?', '<p>Nuestro servicio se centra en reformas completas de viviendas, cocinas y baños. No ofrecemos pequeñas reparaciones domésticas sueltas. Definimos la calidad y el alcance de la obra para que el resultado corresponda a lo acordado.</p>' ],
			[ '¿Cómo elegir una empresa de reformas?', '<p>Comprueba identidad fiscal, seguro, experiencia y trabajos relacionados con tu reforma integral. Revisa presupuesto, exclusiones, pagos y garantías. La profesionalidad también se aprecia en cómo se explican las condiciones y se responden las dudas de los clientes.</p>' ],
		];
		$default = [];
		foreach ( $qa as $x ) {
			$default[] = [ 'q' => $x[0], 'a' => $x[1] ];
		}
		$this->add_control(
			'items',
			[
				'label'       => __( 'Questions', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'default'     => $default,
				'title_field' => '{{{ (q || "").replace(/<[^>]*>/g, "") }}}',
			]
		);
		$this->tag_control( 'q_tag', __( 'Question HTML tag', 'mirenova' ), 'h3' );
		$this->switcher( 'open_first', __( 'First question open', 'mirenova' ) );
		$this->switcher( 'one_open', __( 'Only one open at a time', 'mirenova' ) );
		$this->switcher( 'schema', __( 'FAQPage schema (SEO)', 'mirenova' ), 'yes', [ 'description' => __( 'Adds JSON-LD structured data. Use it on one FAQ per page.', 'mirenova' ) ] );
		$this->end_controls_section();

		$this->start_controls_section( 's_layout', [ 'label' => __( 'Layout', 'mirenova' ) ] );
		$this->add_control(
			'intro_pos',
			[
				'label'   => __( 'Intro position', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'left' => __( 'Left column', 'mirenova' ),
					'top'  => __( 'Above the questions', 'mirenova' ),
				],
				'default' => 'left',
			]
		);
		$this->add_control(
			'acc_cols',
			[
				'label'     => __( 'Question columns (desktop)', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [ '1' => '1', '2' => '2' ],
				'default'   => '1',
				'condition' => [ 'intro_pos' => 'top' ],
			]
		);
		$this->end_controls_section();

		$this->section_controls_open( [ 'anchor' => 'faq', 'scheme' => 'light' ] );
		$this->end_controls_section();

		$this->background_controls(
			[
				'image'   => MRN::media( 'extra02' ),
				'pos'     => 'right top',
				'opacity' => 0.3,
				'blend'   => 'multiply',
			]
		);
		$this->decor_controls(
			[
				self::d( 'soft', 480, [ 'left', '-240px' ], [ 'top', '44%' ], [ 'color' => '#F2F5F9', 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'grad', 170, [ 'left', '-80px' ], [ 'top', '70%' ], [ 'shadow' => 'yes', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
				self::d( 'line', 300, [ 'right', '-120px' ], [ 'top', '-100px' ], [ 'color' => '#CFE4FF', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
			]
		);
		$this->style_heading( '.mrn-faq__intro ' );
		$this->style_text( 'q', __( 'Question', 'mirenova' ), '{{WRAPPER}} .mrn-faq__btn', [ 'hover' => true ] );
		$this->style_text( 'a', __( 'Answer', 'mirenova' ), '{{WRAPPER}} .mrn-faq__a .mrn-text' );
		$this->start_controls_section( 'st_faq', [ 'label' => __( 'Accordion', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->add_control( 'faq_line', [ 'label' => __( 'Divider color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-faq__list, {{WRAPPER}} .mrn-faq__item' => 'border-color: {{VALUE}};' ] ] );
		$this->add_control( 'faq_icon', [ 'label' => __( 'Icon color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-faq__icon' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'faq_icon_bg', [ 'label' => __( 'Icon diamond', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-faq__icon' => '--mrn-dia-bg: {{VALUE}};' ] ] );
		$this->end_controls_section();
		$this->style_buttons();
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = array_values( (array) $s['items'] );
		$top   = 'top' === $s['intro_pos'];
		$uid   = 'mrn-faq-' . $this->get_id();
		$tag   = self::tag( $s['q_tag'], 'h3' );

		$class = 'mrn-faq' . ( $top ? ' mrn-faq--top' : '' ) . ( ! $top && self::on( $s, 'sticky' ) ? ' mrn-faq--sticky' : '' ) . ( $top && '2' === $s['acc_cols'] ? ' mrn-faq--2col' : '' );
		$this->section_open( $s, $class );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap"><div class="mrn-faq__grid">';

		echo '<div class="mrn-faq__intro">';
		echo $this->eyebrow_html( $s ) . $this->title_html( $s ) . $this->text_html( $s ) . $this->buttons_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! $top && ( ! empty( $s['intro_image']['url'] ) || ! empty( $s['intro_image']['id'] ) ) ) {
			$hide = self::on( $s, 'intro_image_desktop' ) ? ' mrn-hide-t mrn-hide-m' : '';
			echo '<div class="mrn-faq__img' . esc_attr( $hide ) . '" aria-hidden="true">' . MRN::img( $s['intro_image'], 'medium_large', [ 'alt' => '', 'loading' => 'lazy', 'sizes' => '300px' ] ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';

		echo '<div class="mrn-faq__list" data-mrn-faq="' . ( self::on( $s, 'one_open' ) ? 'one' : 'many' ) . '">';
		foreach ( $items as $i => $it ) {
			$open = 0 === $i && self::on( $s, 'open_first' );
			$id   = $uid . '-' . $i;
			echo '<div class="mrn-faq__item elementor-repeater-item-' . esc_attr( $it['_id'] ) . ( $open ? ' is-open' : '' ) . '">';
			echo '<' . esc_attr( $tag ) . ' class="mrn-faq__q"><button type="button" class="mrn-faq__btn" id="' . esc_attr( $id ) . '-q" aria-expanded="' . ( $open ? 'true' : 'false' ) . '" aria-controls="' . esc_attr( $id ) . '-a">';
			echo '<span class="mrn-faq__qt">' . MRN::inline( $it['q'] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="mrn-faq__icon mrn-dia mrn-dia--soft" aria-hidden="true">' . MRN_Icons::svg( 'plus', [ 'class' => 'mrn-ico mrn-faq__plus', 'stroke-width' => '2.6' ] ) . MRN_Icons::svg( 'minus', [ 'class' => 'mrn-ico mrn-faq__minus', 'stroke-width' => '2.6' ] ) . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</button></' . esc_attr( $tag ) . '>';
			echo '<div class="mrn-faq__a" id="' . esc_attr( $id ) . '-a" role="region" aria-labelledby="' . esc_attr( $id ) . '-q"' . ( $open ? '' : ' hidden' ) . '><div class="mrn-text">' . MRN::rich( $it['a'] ) . '</div></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}
		echo '</div></div></div>';

		if ( self::on( $s, 'schema' ) && $items ) {
			$main = [];
			foreach ( $items as $it ) {
				$q = MRN::plain( $it['q'] );
				$a = MRN::plain( $it['a'] );
				if ( '' !== $q && '' !== $a ) {
					$main[] = [
						'@type'          => 'Question',
						'name'           => $q,
						'acceptedAnswer' => [ '@type' => 'Answer', 'text' => $a ],
					];
				}
			}
			if ( $main ) {
				echo '<script type="application/ld+json">' . wp_json_encode( [ '@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $main ], JSON_UNESCAPED_UNICODE ). '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		echo '</section>';
	}
}
