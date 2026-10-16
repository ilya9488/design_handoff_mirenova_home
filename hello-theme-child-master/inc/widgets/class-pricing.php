<?php
/**
 * MR Pricing: price tiers, calculator, examples table (calculated automatically) and info banner.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Repeater;

class MRN_Widget_Pricing extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-pricing';
	}

	public function get_title() {
		return 'MR Pricing';
	}

	public function get_icon() {
		return 'eicon-price-table';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'pricing', 'price', 'calculator', 'table' ];
	}

	public function get_script_depends() {
		return [ 'mrn' ];
	}

	protected function register_controls() {
		/* Heading */
		$this->start_controls_section( 's_head', [ 'label' => __( 'Heading', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Precios',
				'title'     => 'Precio de una reforma integral <strong>en Málaga</strong>',
				'text'      => '<p>En MiRenova, el precio orientativo de una reforma integral es de 800–1.000 €/m² en gama básica, 1.000–1.300 €/m² en confort y 1.300–2.000 €/m² en premium, sin IVA.</p><p>Para nuestras reformas en Málaga, el presupuesto definitivo depende del estado del inmueble, la calidad de los materiales y la complejidad del proyecto. En viviendas, el cálculo se aplica a la superficie interior que se reforma.</p>',
				'text_lead' => 'yes',
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

		/* Tiers */
		$this->start_controls_section( 's_tiers', [ 'label' => __( 'Price tiers', 'mirenova' ) ] );
		$r = new Repeater();
		$r->add_control( 'name', [ 'label' => __( 'Short name', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Gama', 'description' => __( 'Used in the calculator and the table.', 'mirenova' ) ] );
		$this->wysiwyg( 'label', __( 'Card label', 'mirenova' ), 'Gama', [], $r );
		$r->add_control( 'from', [ 'label' => __( 'Price from', 'mirenova' ), 'type' => Controls_Manager::NUMBER, 'default' => 1000, 'min' => 0 ] );
		$r->add_control( 'to', [ 'label' => __( 'Price to', 'mirenova' ), 'type' => Controls_Manager::NUMBER, 'default' => 1300, 'min' => 0 ] );
		$r->add_control( 'price_text', [ 'label' => __( 'Custom price text', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '', 'description' => __( 'Optional. Empty = “from–to” formatted automatically.', 'mirenova' ) ] );
		$r->add_control( 'unit', [ 'label' => __( 'Unit', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '€/m²' ] );
		$this->wysiwyg( 'note', __( 'Note', 'mirenova' ), 'sin IVA', [], $r );
		$this->switcher( 'hl', __( 'Highlighted (dark card)', 'mirenova' ), '', [ 'separator' => 'before' ], $r );
		$this->switcher( 'calc_default', __( 'Selected in calculator', 'mirenova' ), '', [], $r );
		$this->switcher( 'in_table', __( 'Column in examples table', 'mirenova' ), 'yes', [], $r );
		$this->add_control(
			'tiers',
			[
				'label'       => __( 'Tiers', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ name }}} · {{{ from }}}–{{{ to }}}',
				'default'     => [
					[ 'name' => 'Básica', 'label' => 'Gama básica', 'from' => 800, 'to' => 1000, 'unit' => '€/m²', 'note' => 'sin IVA', 'hl' => '', 'calc_default' => '', 'in_table' => '' ],
					[ 'name' => 'Confort', 'label' => 'Confort', 'from' => 1000, 'to' => 1300, 'unit' => '€/m²', 'note' => 'sin IVA', 'hl' => 'yes', 'calc_default' => 'yes', 'in_table' => 'yes' ],
					[ 'name' => 'Premium', 'label' => 'Premium', 'from' => 1300, 'to' => 2000, 'unit' => '€/m²', 'note' => 'sin IVA', 'hl' => '', 'calc_default' => '', 'in_table' => 'yes' ],
				],
			]
		);
		$this->tag_control( 'tier_tag', __( 'Card label HTML tag', 'mirenova' ), 'div' );
		$this->add_responsive_control(
			'tier_cols',
			[
				'label'          => __( 'Columns', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( true, 4 ),
				'frontend_available' => true,
				'default'        => 'auto',
				'tablet_default' => 'auto',
				'mobile_default' => '1',
			]
		);
		$this->add_control( 'sep', [ 'label' => __( 'Thousands separator', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '.' ] );
		$this->end_controls_section();

		/* Calculator */
		$this->start_controls_section( 's_calc', [ 'label' => __( 'Calculator', 'mirenova' ) ] );
		$this->switcher( 'calc_on', __( 'Show calculator', 'mirenova' ) );
		$this->wysiwyg( 'calc_title', __( 'Title', 'mirenova' ), 'Calcula tu reforma', [ 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->tag_control( 'calc_title_tag', __( 'Title HTML tag', 'mirenova' ), 'h3', [ 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->add_control( 'area_label', [ 'label' => __( 'Area label', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Superficie interior', 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->add_control( 'area_unit', [ 'label' => __( 'Area unit', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'm²', 'condition' => [ 'calc_on' => 'yes' ] ] );
		foreach ( [ 'min' => [ 'Min', 30 ], 'max' => [ 'Max', 250 ], 'step' => [ 'Step', 5 ], 'val' => [ 'Default value', 80 ] ] as $k => $v ) {
			$this->add_control( 'area_' . $k, [ 'label' => $v[0], 'type' => Controls_Manager::NUMBER, 'default' => $v[1], 'min' => 1, 'condition' => [ 'calc_on' => 'yes' ] ] );
		}
		$this->add_control( 'tier_label', [ 'label' => __( 'Tier selector label', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Gama', 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->add_control( 'result_label', [ 'label' => __( 'Result label', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Estimación orientativa', 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->add_control( 'currency', [ 'label' => __( 'Currency', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '€', 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->add_control( 'result_note', [ 'label' => __( 'Result note', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'sin IVA', 'condition' => [ 'calc_on' => 'yes' ] ] );
		$this->end_controls_section();

		/* Table */
		$this->start_controls_section( 's_table', [ 'label' => __( 'Examples table', 'mirenova' ) ] );
		$this->switcher( 'table_on', __( 'Show table', 'mirenova' ) );
		$this->wysiwyg( 'table_title', __( 'Title', 'mirenova' ), 'Ejemplos de presupuesto para un piso', [ 'condition' => [ 'table_on' => 'yes' ] ] );
		$this->tag_control( 'table_title_tag', __( 'Title HTML tag', 'mirenova' ), 'h3', [ 'condition' => [ 'table_on' => 'yes' ] ] );
		$this->add_control( 'col_first', [ 'label' => __( 'First column header', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Superficie', 'condition' => [ 'table_on' => 'yes' ] ] );
		$this->add_control( 'col_label', [ 'label' => __( 'Tier column header', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '{name}: {range} {unit}', 'description' => __( 'Tokens: {name} {range} {unit}', 'mirenova' ), 'condition' => [ 'table_on' => 'yes' ] ] );
		$this->add_control( 'row_label', [ 'label' => __( 'Row label', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Piso de {area} m²', 'description' => __( 'Token: {area}', 'mirenova' ), 'condition' => [ 'table_on' => 'yes' ] ] );
		$r = new Repeater();
		$r->add_control( 'area', [ 'label' => __( 'Area (m²)', 'mirenova' ), 'type' => Controls_Manager::NUMBER, 'default' => 80, 'min' => 1 ] );
		$this->add_control(
			'rows',
			[
				'label'       => __( 'Rows', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ area }}} m²',
				'default'     => [ [ 'area' => 60 ], [ 'area' => 70 ], [ 'area' => 100 ] ],
				'condition'   => [ 'table_on' => 'yes' ],
			]
		);
		$this->wysiwyg( 'table_note', __( 'Note under the table', 'mirenova' ), '<p>Son ejemplos calculados, no ofertas cerradas. Todos los importes excluyen IVA; la configuración de cada reforma integral se concreta tras la visita.</p>', [ 'condition' => [ 'table_on' => 'yes' ] ] );
		$this->end_controls_section();

		/* Banner */
		$this->start_controls_section( 's_banner', [ 'label' => __( 'Info banner', 'mirenova' ) ] );
		$this->switcher( 'banner_on', __( 'Show banner', 'mirenova' ) );
		$this->wysiwyg( 'banner_title', __( 'Title', 'mirenova' ), 'Qué influye en el precio de las reformas', [ 'condition' => [ 'banner_on' => 'yes' ] ] );
		$this->tag_control( 'banner_title_tag', __( 'Title HTML tag', 'mirenova' ), 'h3', [ 'condition' => [ 'banner_on' => 'yes' ] ] );
		$this->wysiwyg( 'banner_text', __( 'Text', 'mirenova' ), '<p>En las reformas influyen demoliciones, instalaciones, acceso, materiales y equipamiento. La cocina y los baños requieren valorar tanto superficies como conexiones y montaje; su precio no depende únicamente de los metros cuadrados.</p><p>El presupuesto debe contemplar posibles imprevistos. La colocación de baldosas se calcula según superficie, formato, preparación del soporte y dificultad, sin una tarifa máxima universal.</p>', [ 'condition' => [ 'banner_on' => 'yes' ] ] );
		$this->buttons_controls( [], 'banner_buttons' );
		$this->media( 'banner_image', __( 'Background photo', 'mirenova' ), MRN::media( 'foto04' ), [ 'separator' => 'before', 'condition' => [ 'banner_on' => 'yes' ] ] );
		$this->image_fit_controls( 'banner_img', '{{WRAPPER}} .mrn-banner__img', [ 'pos' => 'custom', 'pos_x' => [ 'unit' => '%', 'size' => 50 ], 'pos_y' => [ 'unit' => '%', 'size' => 40 ] ] );
		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'           => 'banner_ov',
				'label'          => __( 'Overlay', 'mirenova' ),
				'types'          => [ 'classic', 'gradient' ],
				'selector'       => '{{WRAPPER}} .mrn-banner__ov',
				'fields_options' => self::gradient( 'rgba(10,29,56,.62)', 0, 'rgba(10,29,56,.93)', 100, 90 ),
			]
		);
		$this->switcher( 'banner_diamond', __( 'Outline diamond', 'mirenova' ) );
		$this->end_controls_section();

		/* Section */
		$this->section_controls_open( [ 'anchor' => 'precios', 'scheme' => 'light' ] );
		$this->switcher( 'band_on', __( 'Lower background band', 'mirenova' ) );
		$this->add_control( 'band_color', [ 'label' => __( 'Band color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-pricing__band' => 'background-color: {{VALUE}};' ], 'condition' => [ 'band_on' => 'yes' ] ] );
		$this->add_control( 'band_top', [ 'label' => __( 'Band starts at', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'size_units' => [ '%' ], 'range' => [ '%' => [ 'min' => 0, 'max' => 100 ] ], 'selectors' => [ '{{WRAPPER}} .mrn-pricing__band' => 'top: {{SIZE}}%;' ], 'condition' => [ 'band_on' => 'yes' ] ] );
		$this->end_controls_section();

		/* Style */
		$this->background_controls( [] );
		$this->decor_controls(
			[
				self::d( 'soft', 400, [ 'left', '-200px' ], [ 'top', '10%' ], [ 'color' => '#E4EEFB', 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'grad', 220, [ 'left', '-110px' ], [ 'top', '23%' ], [ 'shadow' => 'yes', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
				self::d( 'line', 380, [ 'right', '-160px' ], [ 'top', '60%' ], [ 'color' => '#CFE4FF', 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'image', 250, [ 'left', '1%' ], [ 'top', '56%' ], [ 'image' => MRN::media( 'png06' ), 'hide_t' => 'yes', 'hide_m' => 'yes' ] ),
			]
		);
		$this->style_heading( '.mrn-head ' );
		$this->style_box( 'tier', __( 'Tier card', 'mirenova' ), '{{WRAPPER}} .mrn-tier:not(.mrn-tier--hl)' );
		$this->style_box( 'tierhl', __( 'Tier card — highlighted', 'mirenova' ), '{{WRAPPER}} .mrn-tier--hl' );
		$this->style_text( 'price', __( 'Price', 'mirenova' ), '{{WRAPPER}} .mrn-tier__price' );
		$this->style_box( 'panel', __( 'Calculator & table cards', 'mirenova' ), '{{WRAPPER}} .mrn-panel' );
		$this->style_box( 'result', __( 'Calculator result', 'mirenova' ), '{{WRAPPER}} .mrn-calc__result' );
		$this->style_text( 'banner_t', __( 'Banner title', 'mirenova' ), '{{WRAPPER}} .mrn-banner__title' );
		$this->style_box( 'banner', __( 'Banner', 'mirenova' ), '{{WRAPPER}} .mrn-banner' );
		$this->style_buttons();
	}

	/** "800–1.000" */
	protected static function range( $t, $sep ) {
		if ( ! empty( $t['price_text'] ) ) {
			return (string) $t['price_text'];
		}
		return MRN::num( $t['from'], $sep ) . '–' . MRN::num( $t['to'], $sep );
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$tiers = array_values( (array) $s['tiers'] );
		$sep   = (string) $s['sep'];
		$band  = self::on( $s, 'band_on' ) ? '<div class="mrn-pricing__band"></div>' : '';

		$this->section_open( $s, 'mrn-pricing' );
		echo $this->background_html( $s, $band ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap">';

		$left  = $this->eyebrow_html( $s ) . $this->title_html( $s );
		$right = $this->text_html( $s ) . $this->buttons_html( $s );
		if ( $left || $right ) {
			$split = 'split' === $s['head_layout'];
			echo '<div class="mrn-head' . ( $split ? ' mrn-head--split' : '' ) . '">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $split ? '<div class="mrn-head__main">' . $left . '</div><div class="mrn-head__aside">' . $right . '</div>' : $left . $right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}

		/* Tiers */
		if ( $tiers ) {
			$cols = $this->cols( $s, 'tier_cols', count( $tiers ), 4 );
			echo '<div class="mrn-tiers mrn-grid" style="' . esc_attr( self::grid_vars( $cols ) ) . '">';
			foreach ( $tiers as $t ) {
				$hl = self::on( $t, 'hl' );
				echo '<div class="mrn-tier' . ( $hl ? ' mrn-tier--hl' : '' ) . ' elementor-repeater-item-' . esc_attr( $t['_id'] ) . '">';
				if ( $hl ) {
					echo '<span class="mrn-tier__d1" aria-hidden="true"></span><span class="mrn-tier__d2" aria-hidden="true"></span>';
				}
				echo self::inline_tag( $t['label'], $s['tier_tag'], 'mrn-tier__label' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '<div class="mrn-tier__row"><span class="mrn-tier__price">' . esc_html( self::range( $t, $sep ) ) . '</span><span class="mrn-tier__unit">' . esc_html( $t['unit'] ) . '</span></div>';
				$note = MRN::inline( $t['note'] );
				if ( '' !== $note ) {
					echo '<div class="mrn-tier__note">' . $note . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo '</div>';
			}
			echo '</div>';
		}

		$calc  = self::on( $s, 'calc_on' ) && $tiers;
		$table = self::on( $s, 'table_on' );
		if ( $calc || $table ) {
			echo '<div class="mrn-pricing__panels' . ( $calc && $table ? '' : ' is-single' ) . '">';
			if ( $calc ) {
				$this->render_calc( $s, $tiers, $sep );
			}
			if ( $table ) {
				$this->render_table( $s, $tiers, $sep );
			}
			echo '</div>';
		}

		if ( self::on( $s, 'banner_on' ) ) {
			$this->render_banner( $s );
		}
		echo '</div></section>';
	}

	protected function render_calc( $s, $tiers, $sep ) {
		$min  = max( 1, (int) $s['area_min'] );
		$max  = max( $min + 1, (int) $s['area_max'] );
		$step = max( 1, (int) $s['area_step'] );
		$val  = min( $max, max( $min, (int) $s['area_val'] ) );
		$unit = (string) $s['area_unit'];
		$cur  = (string) $s['currency'];
		$act  = 0;
		$data = [];
		foreach ( $tiers as $i => $t ) {
			if ( self::on( $t, 'calc_default' ) ) {
				$act = $i;
			}
			$data[] = [ (float) $t['from'], (float) $t['to'], self::range( $t, $sep ) . ' ' . $t['unit'] ];
		}
		$cfg = [
			'min'  => $min,
			'max'  => $max,
			'step' => $step,
			'v'    => $val,
			't'    => $act,
			'sep'  => $sep,
			'cur'  => $cur,
			'unit' => $unit,
			'tiers' => $data,
		];
		$p     = ( $val - $min ) / ( $max - $min );
		$uid   = 'mrn-calc-' . $this->get_id();
		$a     = $tiers[ $act ];
		$out   = MRN::num( $val * (float) $a['from'], $sep ) . ' – ' . MRN::num( $val * (float) $a['to'], $sep ) . ' ' . $cur;

		echo '<div class="mrn-panel mrn-calc" data-mrn-calc="' . esc_attr( wp_json_encode( $cfg ) ) . '" style="--p:' . esc_attr( round( $p, 4 ) ) . '">';
		echo self::inline_tag( $s['calc_title'], $s['calc_title_tag'], 'mrn-panel__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-calc__row"><span class="mrn-calc__label" id="' . esc_attr( $uid ) . '-l">' . esc_html( $s['area_label'] ) . '</span>';
		echo '<div class="mrn-calc__stepper">';
		echo '<button type="button" class="mrn-calc__step" data-d="-1" aria-label="' . esc_attr( '− ' . $step . ' ' . $unit ) . '">' . MRN_Icons::svg( 'minus', [ 'stroke-width' => '2.6' ] ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<output class="mrn-calc__value" aria-live="polite"><span data-v>' . esc_html( $val ) . '</span> ' . esc_html( $unit ) . '</output>';
		echo '<button type="button" class="mrn-calc__step" data-d="1" aria-label="' . esc_attr( '+ ' . $step . ' ' . $unit ) . '">' . MRN_Icons::svg( 'plus', [ 'stroke-width' => '2.6' ] ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div>';
		echo '<div class="mrn-range"><div class="mrn-range__track"><div class="mrn-range__fill"></div></div><div class="mrn-range__thumb" aria-hidden="true"></div>';
		echo '<input type="range" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" step="' . esc_attr( $step ) . '" value="' . esc_attr( $val ) . '" aria-labelledby="' . esc_attr( $uid ) . '-l"></div>';
		echo '<div class="mrn-range__labels"><span>' . esc_html( $min . ' ' . $unit ) . '</span><span>' . esc_html( $max . ' ' . $unit ) . '</span></div>';
		echo '<div class="mrn-calc__label mrn-calc__label--tier" id="' . esc_attr( $uid ) . '-t">' . esc_html( $s['tier_label'] ) . '</div>';
		echo '<div class="mrn-calc__tiers" role="group" aria-labelledby="' . esc_attr( $uid ) . '-t" style="--n:' . esc_attr( min( 4, count( $tiers ) ) ) . '">';
		foreach ( $tiers as $i => $t ) {
			$on = $i === $act;
			echo '<button type="button" class="mrn-calc__tier' . ( $on ? ' is-on' : '' ) . '" data-t="' . esc_attr( $i ) . '" aria-pressed="' . ( $on ? 'true' : 'false' ) . '">'
				. '<span class="mrn-calc__tname"><i aria-hidden="true"></i>' . esc_html( $t['name'] ) . '</span>'
				. '<span class="mrn-calc__trate">' . esc_html( self::range( $t, $sep ) . ' ' . $t['unit'] ) . '</span></button>';
		}
		echo '</div>';
		echo '<div class="mrn-calc__result"><span class="mrn-calc__rd" aria-hidden="true"></span>';
		echo '<div class="mrn-calc__rlabel">' . esc_html( $s['result_label'] ) . ' · <span data-rate>' . esc_html( self::range( $a, $sep ) . ' ' . $a['unit'] ) . '</span></div>';
		echo '<div class="mrn-calc__rvalue" data-out aria-live="polite">' . esc_html( $out ) . '</div>';
		if ( '' !== $s['result_note'] ) {
			echo '<div class="mrn-calc__rnote">' . esc_html( $s['result_note'] ) . '</div>';
		}
		echo '</div></div>';
	}

	protected function render_table( $s, $tiers, $sep ) {
		$cols = [];
		foreach ( $tiers as $t ) {
			if ( self::on( $t, 'in_table' ) ) {
				$cols[] = $t;
			}
		}
		$title = MRN::plain( $s['table_title'] );
		echo '<div class="mrn-panel mrn-table">';
		echo self::inline_tag( $s['table_title'], $s['table_title_tag'], 'mrn-panel__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-table__scroll" role="region" tabindex="0" aria-label="' . esc_attr( $title ) . '"><table><thead><tr><th scope="col">' . esc_html( $s['col_first'] ) . '</th>';
		foreach ( $cols as $t ) {
			$h = strtr( (string) $s['col_label'], [ '{name}' => $t['name'], '{range}' => self::range( $t, $sep ), '{unit}' => $t['unit'] ] );
			echo '<th scope="col">' . esc_html( $h ) . '</th>';
		}
		echo '</tr></thead><tbody>';
		foreach ( (array) $s['rows'] as $row ) {
			$area = (float) $row['area'];
			echo '<tr><th scope="row">' . esc_html( str_replace( '{area}', MRN::num( $area, $sep ), (string) $s['row_label'] ) ) . '</th>';
			foreach ( $cols as $t ) {
				echo '<td>' . esc_html( MRN::num( $area * (float) $t['from'], $sep ) . '–' . MRN::num( $area * (float) $t['to'], $sep ) . ' ' . $s['currency'] ) . '</td>';
			}
			echo '</tr>';
		}
		echo '</tbody></table></div>';
		$note = MRN::rich( $s['table_note'] );
		if ( '' !== $note ) {
			echo '<div class="mrn-text mrn-table__note">' . $note . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div>';
	}

	protected function render_banner( $s ) {
		$img = MRN::img( $s['banner_image'], 'large', [ 'class' => 'mrn-banner__img', 'alt' => '', 'loading' => 'lazy', 'sizes' => '(max-width: 1300px) 100vw, 1220px' ] );
		echo '<div class="mrn-banner">';
		echo '<div class="mrn-banner__bg" aria-hidden="true">' . $img . '<div class="mrn-banner__ov"></div>' . ( self::on( $s, 'banner_diamond' ) ? '<span class="mrn-banner__d"></span>' : '' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::inline_tag( $s['banner_title'], $s['banner_title_tag'], 'mrn-banner__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$text = MRN::rich( $s['banner_text'] );
		echo '<div class="mrn-banner__body">' . ( $text ? '<div class="mrn-text">' . $text . '</div>' : '' ) . $this->buttons_html( $s, 'banner_buttons' ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div>';
	}
}
