<?php
/**
 * MR Footer: column repeater (brand / menu / contacts / text), social links,
 * bottom bar with automatic year.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Footer extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-footer';
	}

	public function get_title() {
		return 'MR Footer';
	}

	public function get_icon() {
		return 'eicon-footer';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'footer', 'copyright', 'menu', 'social' ];
	}

	protected function is_dynamic_content(): bool {
		return true; // Current year + menus.
	}

	protected function register_controls() {
		$menus = (array) get_option( 'mrn_menus', [] );

		$this->start_controls_section( 's_cols', [ 'label' => __( 'Columns', 'mirenova' ) ] );
		$r = new Repeater();
		$r->add_control(
			'type',
			[
				'label'   => __( 'Column type', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'brand'    => __( 'Logo + text', 'mirenova' ),
					'menu'     => __( 'Menu', 'mirenova' ),
					'contacts' => __( 'Contacts (global)', 'mirenova' ),
					'text'     => __( 'Text', 'mirenova' ),
				],
				'default' => 'text',
			]
		);
		$this->wysiwyg( 'title', __( 'Column title (optional)', 'mirenova' ), '', [], $r );
		$this->media( 'logo', __( 'Logo', 'mirenova' ), MRN::media( 'logo' ), [ 'condition' => [ 'type' => 'brand' ] ], $r );
		$this->wysiwyg( 'text', __( 'Text', 'mirenova' ), '', [ 'condition' => [ 'type' => [ 'brand', 'text' ] ], 'description' => self::tokens_hint() ], $r );
		$r->add_control(
			'menu',
			[
				'label'     => __( 'Menu', 'mirenova' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => [ '' => __( '— Select a menu —', 'mirenova' ) ] + MRN::menus(),
				'default'   => '',
				'condition' => [ 'type' => 'menu' ],
			]
		);
		foreach ( [ 'phone' => __( 'Phone', 'mirenova' ), 'email' => __( 'E-mail', 'mirenova' ), 'whatsapp' => 'WhatsApp', 'hours' => __( 'Hours', 'mirenova' ), 'address' => __( 'Address', 'mirenova' ) ] as $k => $label ) {
			$this->switcher( 'c_' . $k, $label, in_array( $k, [ 'phone', 'email', 'hours' ], true ) ? 'yes' : '', [ 'condition' => [ 'type' => 'contacts' ] ], $r );
		}
		$this->switcher( 'social', __( 'Social links', 'mirenova' ), '', [ 'description' => __( 'From MiRenova → Settings. Hidden when none are set.', 'mirenova' ) ], $r );

		$this->add_control(
			'cols',
			[
				'label'       => __( 'Columns', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ ({brand:"Logo + text",menu:"Menu",contacts:"Contacts",text:"Text"})[type] }}}',
				'default'     => [
					[ 'type' => 'brand', 'logo' => MRN::media( 'logo' ), 'text' => '<p>MiRenova es la marca de reformas de ARVION STUDIO S.L.</p>', 'social' => 'yes' ],
					[ 'type' => 'menu', 'menu' => isset( $menus['footer'] ) ? (string) $menus['footer'] : '' ],
					[ 'type' => 'contacts', 'c_phone' => 'yes', 'c_email' => 'yes', 'c_hours' => 'yes' ],
				],
			]
		);
		$this->tag_control( 'col_title_tag', __( 'Column title HTML tag', 'mirenova' ), 'div' );
		$this->add_responsive_control(
			'grid_cols',
			[
				'label'          => __( 'Columns per row', 'mirenova' ),
				'type'           => Controls_Manager::SELECT,
				'options'        => self::columns( true, 6 ),
				'frontend_available' => true,
				'default'        => 'auto',
				'tablet_default' => '2',
				'mobile_default' => '1',
			]
		);
		$this->end_controls_section();

		$this->start_controls_section( 's_bottom', [ 'label' => __( 'Bottom bar', 'mirenova' ) ] );
		$this->switcher( 'bottom_on', __( 'Show bottom bar', 'mirenova' ) );
		$this->wysiwyg( 'copy', __( 'Copyright', 'mirenova' ), '© {year} MiRenova · ARVION STUDIO S.L.', [ 'description' => __( '{year} = year (see the switch below).', 'mirenova' ), 'condition' => [ 'bottom_on' => 'yes' ] ] );
		$this->switcher( 'auto_year', __( 'Insert the current year automatically', 'mirenova' ), 'yes', [ 'condition' => [ 'bottom_on' => 'yes' ] ] );
		$this->add_control( 'static_year', [ 'label' => __( 'Year (when automatic is off)', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => '2026', 'condition' => [ 'bottom_on' => 'yes', 'auto_year!' => 'yes' ] ] );
		$this->wysiwyg( 'right', __( 'Right text', 'mirenova' ), 'Todos los derechos reservados', [ 'condition' => [ 'bottom_on' => 'yes' ] ] );
		$this->add_control(
			'legal_menu',
			[
				'label'       => __( 'Legal links menu (optional)', 'mirenova' ),
				'type'        => Controls_Manager::SELECT,
				'options'     => [ '' => __( '— None —', 'mirenova' ) ] + MRN::menus(),
				'default'     => '',
				'description' => __( 'E.g. Aviso legal, Privacidad, Cookies.', 'mirenova' ),
				'condition'   => [ 'bottom_on' => 'yes' ],
			]
		);
		$this->end_controls_section();

		$this->start_controls_section( 'st_footer', [ 'label' => __( 'Footer', 'mirenova' ), 'tab' => Controls_Manager::TAB_STYLE ] );
		$this->add_control( 'f_bg', [ 'label' => __( 'Background', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-footer' => 'background-color: {{VALUE}};' ] ] );
		$this->add_control( 'f_line', [ 'label' => __( 'Bottom bar line', 'mirenova' ), 'type' => Controls_Manager::COLOR, 'selectors' => [ '{{WRAPPER}} .mrn-footer__bottom' => 'border-top-color: {{VALUE}};' ] ] );
		$this->add_responsive_control( 'f_pt', [ 'label' => __( 'Padding top', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'size_units' => [ 'px', 'vw' ], 'range' => [ 'px' => [ 'min' => 0, 'max' => 240 ] ], 'selectors' => [ '{{WRAPPER}} .mrn-footer' => 'padding-top: {{SIZE}}{{UNIT}};' ] ] );
		$this->add_responsive_control( 'logo_h', [ 'label' => __( 'Logo height', 'mirenova' ), 'type' => Controls_Manager::SLIDER, 'range' => [ 'px' => [ 'min' => 16, 'max' => 120 ] ], 'selectors' => [ '{{WRAPPER}} .mrn-footer__logo img' => 'height: {{SIZE}}px;' ] ] );
		$this->end_controls_section();
		$this->style_text( 'ftitle', __( 'Column titles', 'mirenova' ), '{{WRAPPER}} .mrn-footer__title' );
		$this->style_text( 'ftext', __( 'Text', 'mirenova' ), '{{WRAPPER}} .mrn-footer__text, {{WRAPPER}} .mrn-footer__contacts' );
		$this->style_text( 'flink', __( 'Links', 'mirenova' ), '{{WRAPPER}} .mrn-footer a:not(.mrn-btn)', [ 'hover' => true ] );
		$this->style_text( 'fbottom', __( 'Bottom bar', 'mirenova' ), '{{WRAPPER}} .mrn-footer__bottom' );
	}

	protected function menu( $id, $class ) {
		return MRN::nav_menu( $id, $class, 1 );
	}

	protected function render() {
		$s     = $this->get_settings_for_display();
		$cols  = array_values( (array) $s['cols'] );
		$count = count( $cols );
		$grid  = $this->cols( $s, 'grid_cols', $count, 6 );

		echo '<footer class="mrn mrn-footer">';
		echo '<div class="mrn-wrap"><div class="mrn-footer__grid mrn-grid" style="' . esc_attr( self::grid_vars( $grid ) ) . '">';
		foreach ( $cols as $c ) {
			echo '<div class="mrn-footer__col mrn-footer__col--' . esc_attr( $c['type'] ) . ' elementor-repeater-item-' . esc_attr( $c['_id'] ) . '">';
			echo self::inline_tag( $c['title'], $s['col_title_tag'], 'mrn-footer__title' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			switch ( $c['type'] ) {
				case 'brand':
					$logo = MRN::img( $c['logo'], 'medium', [ 'loading' => 'lazy', 'sizes' => '160px', 'alt' => get_bloginfo( 'name' ) ] );
					if ( $logo ) {
						echo '<a class="mrn-footer__logo" href="' . esc_url( home_url( '/' ) ) . '">' . $logo . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					$t = MRN::rich( $c['text'] );
					echo $t ? '<div class="mrn-text mrn-footer__text">' . $t . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					break;
				case 'menu':
					$m = $this->menu( $c['menu'], 'mrn-footer__menu' );
					echo $m ? '<nav aria-label="' . esc_attr__( 'Footer menu', 'mirenova' ) . '">' . $m . '</nav>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					break;
				case 'contacts':
					echo '<ul class="mrn-footer__contacts">';
					if ( self::on( $c, 'c_phone' ) && MRN::phone() ) {
						echo '<li><a class="mrn-footer__phone" href="' . esc_url( MRN::link( 'phone' )['url'] ) . '">' . esc_html( MRN::phone() ) . '</a></li>';
					}
					if ( self::on( $c, 'c_email' ) && MRN::email() ) {
						echo '<li><a href="' . esc_url( 'mailto:' . antispambot( MRN::email(), 1 ) ) . '">' . esc_html( antispambot( MRN::email() ) ) . '</a></li>';
					}
					if ( self::on( $c, 'c_whatsapp' ) && MRN::whatsapp_url() ) {
						echo '<li><a href="' . esc_url( MRN::whatsapp_url() ) . '" target="_blank" rel="noopener">WhatsApp</a></li>';
					}
					if ( self::on( $c, 'c_hours' ) && MRN::opt( 'hours' ) ) {
						echo '<li class="mrn-footer__muted">' . esc_html( MRN::opt( 'hours' ) ) . '</li>';
					}
					if ( self::on( $c, 'c_address' ) && MRN::opt( 'address' ) ) {
						echo '<li class="mrn-footer__muted">' . esc_html( MRN::opt( 'address' ) ) . '</li>';
					}
					echo '</ul>';
					break;
				default:
					$t = MRN::rich( $c['text'] );
					echo $t ? '<div class="mrn-text mrn-footer__text">' . $t . '</div>' : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			if ( self::on( $c, 'social' ) ) {
				echo MRN::socials_html( 'mrn-footer__social' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div>';
		}
		echo '</div></div>';

		if ( self::on( $s, 'bottom_on' ) ) {
			$copy = (string) $s['copy'];
			if ( ! self::on( $s, 'auto_year' ) ) {
				$copy = str_replace( '{year}', esc_html( $s['static_year'] ), $copy );
			}
			$legal = $s['legal_menu'] ? $this->menu( $s['legal_menu'], 'mrn-footer__legal' ) : '';
			echo '<div class="mrn-footer__bottom"><div class="mrn-wrap mrn-footer__bottom-in">';
			echo '<div class="mrn-footer__copy">' . MRN::inline( $copy ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( $legal ) {
				echo '<nav aria-label="' . esc_attr__( 'Legal', 'mirenova' ) . '">' . $legal . '</nav>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			$right = MRN::inline( $s['right'] );
			if ( '' !== $right ) {
				echo '<div class="mrn-footer__right">' . $right . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</div></div>';
		}
		echo '</footer>';
	}
}
