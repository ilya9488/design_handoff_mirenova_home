<?php
/**
 * MR Header: logo, WordPress menu, global phone, CTA, social links; burger menu at ≤1199px.
 *
 * Save it as an Elementor template and pick it in MiRenova → Settings (global) or per page.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

class MRN_Widget_Header extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-header';
	}

	public function get_title() {
		return 'MR Header';
	}

	public function get_icon() {
		return 'eicon-header';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'header', 'menu', 'nav', 'logo' ];
	}

	public function get_script_depends() {
		return [ 'mrn' ];
	}

	protected function is_dynamic_content(): bool {
		return true; // Current menu item differs per page.
	}

	protected function register_controls() {
		$menus = (array) get_option( 'mrn_menus', [] );

		$this->start_controls_section( 's_logo', [ 'label' => __( 'Logo', 'mirenova' ) ] );
		$this->media( 'logo', __( 'Logo', 'mirenova' ), MRN::media( 'logo' ) );
		$this->add_control( 'logo_link', [ 'label' => __( 'Logo link', 'mirenova' ), 'type' => Controls_Manager::URL, 'placeholder' => __( 'Home page', 'mirenova' ), 'dynamic' => [ 'active' => true ] ] );
		$this->add_responsive_control(
			'logo_h',
			[
				'label'     => __( 'Logo height', 'mirenova' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 'px' => [ 'min' => 16, 'max' => 120 ] ],
				'selectors' => [ '{{WRAPPER}}' => '--mrn-logo-h: {{SIZE}}px;' ],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section( 's_menu', [ 'label' => __( 'Menu', 'mirenova' ) ] );
		$this->add_control(
			'menu',
			[
				'label'       => __( 'Menu', 'mirenova' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [ '' => __( '— Select a menu —', 'mirenova' ) ] + MRN::menus(),
				'default'     => isset( $menus['header'] ) ? (string) $menus['header'] : '',
				'description' => __( 'Menus from Appearance → Menus. With WPML/Polylang the translated menu is used automatically.', 'mirenova' ),
			]
		);
		$this->add_control(
			'depth',
			[
				'label'   => __( 'Levels', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'1' => __( 'Top level only', 'mirenova' ),
					'2' => __( 'With sub-menus', 'mirenova' ),
				],
				'default' => '2',
			]
		);
		$this->add_control( 'burger_label', [ 'label' => __( 'Burger button label (screen readers)', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Menú' ] );
		$this->note( 'burger_note', __( 'The burger menu replaces the desktop menu at 1199px and below.', 'mirenova' ) );
		$this->end_controls_section();

		$this->start_controls_section( 's_actions', [ 'label' => __( 'Phone & button', 'mirenova' ) ] );
		$this->switcher( 'phone_on', __( 'Show phone', 'mirenova' ) );
		$this->add_control( 'phone_text', [ 'label' => __( 'Phone text', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '{phone}', 'description' => __( 'Number comes from MiRenova → Settings. You can add text: “Llámanos {phone}”.', 'mirenova' ), 'condition' => [ 'phone_on' => 'yes' ] ] );
		$this->switcher( 'cta_on', __( 'Show button', 'mirenova' ), 'yes', [ 'separator' => 'before' ] );
		$this->add_control( 'cta_text', [ 'label' => __( 'Button text', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Solicitar presupuesto', 'condition' => [ 'cta_on' => 'yes' ] ] );
		$this->add_control( 'cta_type', [ 'label' => __( 'Button link', 'mirenova' ), 'type' => Controls_Manager::SELECT, 'options' => self::link_types(), 'default' => 'url', 'condition' => [ 'cta_on' => 'yes' ] ] );
		$this->add_control( 'cta_link', [ 'label' => __( 'URL', 'mirenova' ), 'type' => Controls_Manager::URL, 'default' => [ 'url' => home_url( '/#contacto' ) ], 'dynamic' => [ 'active' => true ], 'condition' => [ 'cta_on' => 'yes', 'cta_type' => 'url' ] ] );
		$this->switcher( 'cta_hide_xs', __( 'Hide button in the bar on small phones', 'mirenova' ), 'yes', [ 'description' => __( 'Below 560px the button stays inside the burger menu.', 'mirenova' ), 'condition' => [ 'cta_on' => 'yes' ] ] );
		$this->end_controls_section();

		$this->start_controls_section( 's_social', [ 'label' => __( 'Social links', 'mirenova' ) ] );
		$this->note( 'social_note', __( 'Links come from MiRenova → Settings → Social networks. Empty networks are hidden automatically.', 'mirenova' ) );
		$this->switcher( 'social_bar', __( 'Show in the bar (desktop)', 'mirenova' ) );
		$this->switcher( 'social_panel', __( 'Show in the burger menu', 'mirenova' ) );
		$this->end_controls_section();

		$this->start_controls_section( 's_behavior', [ 'label' => __( 'Behavior', 'mirenova' ) ] );
		$this->switcher( 'sticky', __( 'Sticky header', 'mirenova' ) );
		$this->end_controls_section();

		/* Style */
		$this->start_controls_section( 'st_bar', [ 'label' => __( 'Bar', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->add_control( 'bar_bg', [ 'label' => __( 'Background', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-header, {{WRAPPER}} .mrn-header__panel' => 'background-color: {{VALUE}};' ] ] );
		$this->add_control( 'bar_border', [ 'label' => __( 'Bottom line', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-header' => 'border-bottom-color: {{VALUE}};' ] ] );
		$this->add_responsive_control( 'bar_h', [ 'label' => __( 'Height', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 48, 'max' => 140 ] ], 'selectors' => [ '{{WRAPPER}}' => '--mrn-header-h: {{SIZE}}px;' ] ] );
		$this->end_controls_section();
		$this->style_text( 'nav', __( 'Menu links', 'mirenova' ), '{{WRAPPER}} .mrn-menu > li > a', [ 'hover' => true ] );
		$this->style_text( 'mnav', __( 'Burger menu links', 'mirenova' ), '{{WRAPPER}} .mrn-mmenu a', [ 'hover' => true ] );
		$this->style_text( 'phone', __( 'Phone', 'mirenova' ), '{{WRAPPER}} .mrn-header__phone', [ 'hover' => true ] );
		$this->start_controls_section( 'st_burger', [ 'label' => __( 'Burger & social icons', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->add_control( 'burger_color', [ 'label' => __( 'Burger color', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-header__burger' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'burger_border', [ 'label' => __( 'Burger border', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-header__burger' => 'border-color: {{VALUE}};' ] ] );
		$this->add_control( 'social_color', [ 'label' => __( 'Social icons', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-social a' => 'color: {{VALUE}};' ] ] );
		$this->add_control( 'social_color_h', [ 'label' => __( 'Social icons hover', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-social a:hover' => 'color: {{VALUE}};' ] ] );
		$this->end_controls_section();
		$this->style_buttons();
	}

	protected function menu_html( $s, $class ) {
		return MRN::nav_menu( $s['menu'], $class, (int) $s['depth'] );
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$uid   = 'mrn-menu-' . $this->get_id();
		$home  = ! empty( $s['logo_link']['url'] ) ? $s['logo_link'] : [ 'url' => home_url( '/' ) ];
		$logo  = MRN::img( $s['logo'], 'medium', [ 'class' => 'mrn-header__logo-img', 'loading' => 'eager', 'sizes' => '160px', 'alt' => get_bloginfo( 'name' ) ] );
		$menu  = $this->menu_html( $s, 'mrn-menu' );
		$mmenu = preg_replace( '/\sid="[^"]*"/', '', $this->menu_html( $s, 'mrn-mmenu' ) );
		$phone = self::on( $s, 'phone_on' ) && MRN::phone() ? MRN::tokens( esc_html( $s['phone_text'] ) ) : '';
		$tel   = MRN::link( 'phone' );

		$cta = '';
		if ( self::on( $s, 'cta_on' ) && '' !== trim( (string) $s['cta_text'] ) ) {
			$cta = $this->link_attrs( $s['cta_type'], $s['cta_link'], 'mrn-btn mrn-btn--primary mrn-header__cta' );
		}
		$cta_label = MRN::tokens( esc_html( $s['cta_text'] ) );
		$socials   = MRN::socials_html( 'mrn-header__social' );

		echo '<header class="mrn mrn-header' . ( self::on( $s, 'sticky' ) ? ' mrn-header--sticky' : '' ) . '" data-mrn-header>';
		echo '<div class="mrn-wrap mrn-header__bar">';
		$logo_attrs = $this->link_attrs( 'url', $home, 'mrn-header__logo' );
		echo '<a ' . $logo_attrs . ' aria-label="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . $logo . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( $menu ) {
			echo '<nav class="mrn-header__nav" aria-label="' . esc_attr__( 'Main menu', 'mirenova' ) . '">' . $menu . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<div class="mrn-header__actions">';
		if ( $socials && self::on( $s, 'social_bar' ) ) {
			echo $socials; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( $phone ) {
			echo '<a class="mrn-header__phone" href="' . esc_url( $tel['url'] ) . '">' . $phone . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( $cta ) {
			echo '<a ' . $cta . ( self::on( $s, 'cta_hide_xs' ) ? ' data-hide-xs' : '' ) . '><span class="mrn-btn__label">' . $cta_label . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '<button type="button" class="mrn-header__burger" aria-expanded="false" aria-controls="' . esc_attr( $uid ) . '" aria-label="' . esc_attr( $s['burger_label'] ) . '">'
			. MRN_Icons::svg( 'menu', [ 'class' => 'mrn-ico mrn-header__i-open', 'stroke-width' => '2.2' ] )
			. MRN_Icons::svg( 'x', [ 'class' => 'mrn-ico mrn-header__i-close', 'stroke-width' => '2.2' ] ) . '</button>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</div></div>';

		echo '<div class="mrn-header__panel" id="' . esc_attr( $uid ) . '" hidden><div class="mrn-wrap mrn-header__panel-in">';
		if ( $mmenu ) {
			echo '<nav aria-label="' . esc_attr__( 'Mobile menu', 'mirenova' ) . '">' . $mmenu . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( MRN::phone() && self::on( $s, 'phone_on' ) ) {
			echo '<a class="mrn-header__mphone" href="' . esc_url( $tel['url'] ) . '">' . $phone . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( $socials && self::on( $s, 'social_panel' ) ) {
			echo $socials; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		if ( $cta ) {
			echo '<a ' . str_replace( 'mrn-header__cta', 'mrn-header__mcta mrn-btn--block', $cta ) . '><span class="mrn-btn__label">' . $cta_label . '</span></a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</div></div></header>';
	}
}
