<?php
/**
 * MR Contact: text, contact rows (global phone / e-mail / hours) and a Contact Form 7 card.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Contact extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-contact';
	}

	public function get_title() {
		return 'MR Contact';
	}

	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'contact', 'form', 'phone', 'email', 'cf7' ];
	}

	public function get_script_depends() {
		return [ 'mrn' ];
	}

	protected function is_dynamic_content(): bool {
		return true; // Contains a form.
	}

	protected function register_controls() {
		$this->start_controls_section( 's_content', [ 'label' => __( 'Content', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Contacto',
				'title'     => '<strong>Solicita presupuesto</strong> para tu reforma en Málaga',
				'text'      => '<p>Cuéntanos dónde está tu vivienda, su superficie y qué reformas necesitas. Estudiaremos las opciones para renovar tu hogar con un presupuesto ajustado al alcance y un compromiso claro sobre los trabajos acordados.</p>',
				'text_lead' => '',
			]
		);
		$this->buttons_controls( [] );
		$this->end_controls_section();

		$this->start_controls_section( 's_rows', [ 'label' => __( 'Contact rows', 'mirenova' ) ] );
		$this->note( 'rows_note', __( 'Phone, e-mail, WhatsApp and hours come from MiRenova → Settings (one place for the whole site).', 'mirenova' ) );
		$r = new Repeater();
		$r->add_control(
			'type',
			[
				'label'   => __( 'Type', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'phone'    => __( 'Phone (global)', 'mirenova' ),
					'email'    => __( 'E-mail (global)', 'mirenova' ),
					'whatsapp' => __( 'WhatsApp (global)', 'mirenova' ),
					'text'     => __( 'Text only', 'mirenova' ),
					'url'      => __( 'Custom link', 'mirenova' ),
				],
				'default' => 'phone',
			]
		);
		$r->add_control( 'icon', [ 'label' => __( 'Icon', 'mirenova' ), 'type' => Controls_Manager::ICONS, 'default' => [ 'value' => 'mrn-i mrn-i-phone', 'library' => 'mrn-icons' ] ] );
		$this->wysiwyg( 'label', __( 'Label', 'mirenova' ), 'Teléfono', [], $r );
		$this->wysiwyg( 'value', __( 'Value', 'mirenova' ), '{phone}', [ 'description' => self::tokens_hint() ], $r );
		$r->add_control( 'link', [ 'label' => __( 'URL', 'mirenova' ), 'type' => Controls_Manager::URL, 'dynamic' => [ 'active' => true ], 'condition' => [ 'type' => 'url' ] ] );
		$this->add_control(
			'rows',
			[
				'label'         => __( 'Rows', 'mirenova' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $r->get_controls(),
				'prevent_empty' => false,
				'title_field'   => '{{{ (label || value || "").replace(/<[^>]*>/g, "") }}}',
				'default'       => [
					[ 'type' => 'phone', 'icon' => [ 'value' => 'mrn-i mrn-i-phone', 'library' => 'mrn-icons' ], 'label' => 'Teléfono', 'value' => '{phone}' ],
					[ 'type' => 'email', 'icon' => [ 'value' => 'mrn-i mrn-i-mail', 'library' => 'mrn-icons' ], 'label' => 'Correo', 'value' => '{email}' ],
					[ 'type' => 'text', 'icon' => [ 'value' => 'mrn-i mrn-i-clock', 'library' => 'mrn-icons' ], 'label' => '', 'value' => '{hours}' ],
				],
			]
		);
		$this->end_controls_section();

		$forms = (array) get_option( 'mrn_forms', [] );
		$this->start_controls_section( 's_form', [ 'label' => __( 'Form', 'mirenova' ) ] );
		$this->switcher( 'form_on', __( 'Show form card', 'mirenova' ) );
		$this->add_control(
			'form_id',
			[
				'label'     => __( 'Contact Form 7', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [ '' => __( '— Select a form —', 'mirenova' ) ] + MRN::forms(),
				'default'   => isset( $forms['contact'] ) ? (string) $forms['contact'] : '',
				'condition' => [ 'form_on' => 'yes' ],
			]
		);
		$this->wysiwyg( 'form_title', __( 'Card title (optional)', 'mirenova' ), '', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->tag_control( 'form_title_tag', __( 'Card title HTML tag', 'mirenova' ), 'h3', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->wysiwyg( 'form_sub', __( 'Card subtitle (optional)', 'mirenova' ), '', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->add_control( 'form_sent', [ 'label' => __( 'Button text after sending', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Solicitud enviada', 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->switcher( 'form_decor', __( 'Card corner diamonds', 'mirenova' ), '', [ 'condition' => [ 'form_on' => 'yes' ] ] );
		$this->add_control(
			'form_side',
			[
				'label'   => __( 'Form position', 'mirenova' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'  => [ 'title' => __( 'Left', 'mirenova' ), 'icon' => 'eicon-h-align-left' ],
					'right' => [ 'title' => __( 'Right', 'mirenova' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default' => 'right',
				'toggle'  => false,
			]
		);
		$this->end_controls_section();

		$this->section_controls_open( [ 'anchor' => 'contacto', 'scheme' => 'dark' ] );
		$this->end_controls_section();

		$this->background_controls( [ 'image' => MRN::media( 'bg04' ), 'opacity' => 0.18 ] );
		$this->decor_controls(
			[
				self::d( 'glow', null, null, null, [ 'color' => 'rgba(31,111,255,.25)', 'gx' => [ 'unit' => '%', 'size' => 85 ], 'gy' => [ 'unit' => '%', 'size' => 50 ] ] ),
				self::d( 'image', 760, [ 'right', '-300px' ], [ 'top', 'calc(12% - 110px)' ], [ 'image' => MRN::media( 'extra01' ), 'opacity' => [ 'size' => 0.12 ], 'saturate' => [ 'unit' => '%', 'size' => 60 ], 'hide_m' => 'yes' ] ),
				self::d( 'line', 260, [ 'right', '12%' ], [ 'top', '-120px' ], [ 'color' => 'rgba(124,214,249,.25)', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
				self::d( 'soft', 200, [ 'left', '-80px' ], [ 'bottom', '-80px' ], [ 'color' => '#0F2A4D', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
			]
		);
		$this->style_heading( '.mrn-contact__text ' );
		$this->style_text( 'rowlabel', __( 'Row label', 'mirenova' ), '{{WRAPPER}} .mrn-crow__label' );
		$this->style_text( 'rowvalue', __( 'Row value', 'mirenova' ), '{{WRAPPER}} .mrn-crow__value', [ 'hover' => true ] );
		$this->style_box( 'formcard', __( 'Form card', 'mirenova' ), '{{WRAPPER}} .mrn-formcard' );
		$this->style_buttons();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$this->section_open( $s, 'mrn-contact' . ( 'left' === $s['form_side'] ? ' mrn-contact--left' : '' ) );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap"><div class="mrn-split mrn-contact__grid' . ( self::on( $s, 'form_on' ) ? '' : ' is-single' ) . '">';
		echo '<div class="mrn-contact__text">' . $this->eyebrow_html( $s ) . $this->title_html( $s ) . $this->text_html( $s ) . $this->buttons_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( ! empty( $s['rows'] ) ) {
			echo '<ul class="mrn-contact__rows">';
			foreach ( $s['rows'] as $row ) {
				$label = MRN::inline( $row['label'] );
				$value = MRN::inline( $row['value'] );
				if ( '' === $value && '' === $label ) {
					continue;
				}
				$type  = in_array( $row['type'], [ 'phone', 'email', 'whatsapp', 'url' ], true ) ? $row['type'] : 'none';
				$cls   = 'mrn-crow' . ( '' === $label ? ' mrn-crow--plain' : '' ) . ' elementor-repeater-item-' . $row['_id'];
				$attrs = $this->link_attrs( $type, isset( $row['link'] ) ? $row['link'] : [], $cls );
				$icon  = $this->icon_html( $row['icon'] );
				$inner = ( $icon ? '<span class="mrn-crow__icon mrn-dia mrn-dia--navy" aria-hidden="true">' . $icon . '</span>' : '' )
					. '<span class="mrn-crow__body">' . ( '' !== $label ? '<span class="mrn-crow__label">' . $label . '</span>' : '' ) . '<span class="mrn-crow__value">' . $value . '</span></span>';
				echo '<li>' . ( $attrs ? '<a ' . $attrs . '>' . $inner . '</a>' : '<div class="' . esc_attr( $cls ) . '">' . $inner . '</div>' ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</ul>';
		}
		echo '</div>';

		if ( self::on( $s, 'form_on' ) ) {
			echo '<div class="mrn-formcard mrn-contact__form">' . ( self::on( $s, 'form_decor' ) ? '<div class="mrn-formcard__decor" aria-hidden="true"><span></span><span></span></div>' : '' );
			echo self::inline_tag( $s['form_title'], $s['form_title_tag'], 'mrn-formcard__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$sub = MRN::inline( $s['form_sub'] );
			if ( '' !== $sub ) {
				echo '<p class="mrn-formcard__sub">' . $sub . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo MRN_CF7::render( $s['form_id'], $s['form_sent'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
		}
		echo '</div></div></section>';
	}
}
