<?php
/**
 * MiRenova → Settings: one place for phone, e-mail, WhatsApp, hours, social links
 * and the global header / footer templates.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_Settings {

	const PAGE = 'mirenova';

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'register' ] );
		// Global contacts appear inside widgets: drop Elementor caches (element cache, CSS) when they change.
		add_action( 'update_option_' . MRN::OPT, [ __CLASS__, 'flush' ] );
		add_action( 'add_option_' . MRN::OPT, [ __CLASS__, 'flush' ] );
	}

	public static function flush() {
		if ( did_action( 'elementor/loaded' ) ) {
			\Elementor\Plugin::$instance->files_manager->clear_cache();
		}
	}

	public static function menu() {
		add_menu_page(
			__( 'MiRenova', 'mirenova' ),
			__( 'MiRenova', 'mirenova' ),
			'manage_options',
			self::PAGE,
			[ __CLASS__, 'render' ],
			'dashicons-admin-home',
			58
		);
		add_submenu_page( self::PAGE, __( 'MiRenova Settings', 'mirenova' ), __( 'Settings', 'mirenova' ), 'manage_options', self::PAGE, [ __CLASS__, 'render' ] );
	}

	public static function register() {
		register_setting(
			'mrn_settings_group',
			MRN::OPT,
			[
				'type'              => 'array',
				'sanitize_callback' => [ __CLASS__, 'sanitize' ],
				'default'           => MRN::defaults(),
			]
		);
	}

	public static function sanitize( $in ) {
		$in  = is_array( $in ) ? $in : [];
		$out = (array) get_option( MRN::OPT, [] );
		foreach ( [ 'phone', 'whatsapp', 'hours', 'address' ] as $k ) {
			$out[ $k ] = isset( $in[ $k ] ) ? sanitize_text_field( wp_unslash( $in[ $k ] ) ) : '';
		}
		$out['email']           = isset( $in['email'] ) ? sanitize_email( wp_unslash( $in['email'] ) ) : '';
		$out['header_template'] = isset( $in['header_template'] ) ? absint( $in['header_template'] ) : 0;
		$out['footer_template'] = isset( $in['footer_template'] ) ? absint( $in['footer_template'] ) : 0;
		foreach ( array_keys( MRN_Icons::socials() ) as $net ) {
			$k         = 'social_' . $net;
			$out[ $k ] = isset( $in[ $k ] ) ? esc_url_raw( trim( wp_unslash( $in[ $k ] ) ) ) : '';
		}
		return $out;
	}

	private static function field( $key, $label, $type = 'text', $desc = '' ) {
		$val = MRN::opt( $key );
		printf(
			'<tr><th scope="row"><label for="mrn-%1$s">%2$s</label></th><td><input type="%3$s" class="regular-text" id="mrn-%1$s" name="%4$s[%1$s]" value="%5$s">%6$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( $type ),
			esc_attr( MRN::OPT ),
			esc_attr( $val ),
			$desc ? '<p class="description">' . wp_kses_post( $desc ) . '</p>' : ''
		);
	}

	private static function template_select( $key, $label, $desc ) {
		$val = (int) MRN::opt( $key );
		echo '<tr><th scope="row"><label for="mrn-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		echo '<select id="mrn-' . esc_attr( $key ) . '" name="' . esc_attr( MRN::OPT ) . '[' . esc_attr( $key ) . ']">';
		echo '<option value="0">' . esc_html__( '— Theme default (Hello Elementor) —', 'mirenova' ) . '</option>';
		foreach ( MRN::templates() as $id => $title ) {
			echo '<option value="' . esc_attr( $id ) . '"' . selected( $val, (int) $id, false ) . '>' . esc_html( $title ) . '</option>';
		}
		echo '</select>';
		if ( $val ) {
			$edit = admin_url( 'post.php?post=' . $val . '&action=elementor' );
			echo ' <a class="button" href="' . esc_url( $edit ) . '">' . esc_html__( 'Edit with Elementor', 'mirenova' ) . '</a>';
		}
		echo '<p class="description">' . wp_kses_post( $desc ) . '</p></td></tr>';
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'MiRenova Settings', 'mirenova' ); ?></h1>
			<p><?php esc_html_e( 'Global contact data used by every MiRenova block: buttons, header, footer, hero strip and contact rows. Change it here once — it updates everywhere.', 'mirenova' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'mrn_settings_group' ); ?>

				<h2 class="title"><?php esc_html_e( 'Contacts', 'mirenova' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					self::field( 'phone', __( 'Phone', 'mirenova' ), 'text', __( 'Shown as typed. Links use digits only (tel:). Token: <code>{phone}</code>, digits: <code>{tel}</code>.', 'mirenova' ) );
					self::field( 'email', __( 'E-mail', 'mirenova' ), 'email', __( 'Token: <code>{email}</code>.', 'mirenova' ) );
					self::field( 'whatsapp', __( 'WhatsApp number', 'mirenova' ), 'text', __( 'International format, e.g. +34 652 469 039. Used by the “WhatsApp” link type. Token: <code>{whatsapp}</code>.', 'mirenova' ) );
					self::field( 'hours', __( 'Opening hours', 'mirenova' ), 'text', __( 'Token: <code>{hours}</code>.', 'mirenova' ) );
					self::field( 'address', __( 'Address', 'mirenova' ), 'text', __( 'Optional. Token: <code>{address}</code>.', 'mirenova' ) );
					?>
				</table>
				<p class="description"><?php echo wp_kses_post( __( 'Tokens work in every MiRenova text field and button label, e.g. <code>Llamar al {phone}</code>. <code>{year}</code> prints the current year.', 'mirenova' ) ); ?></p>

				<h2 class="title"><?php esc_html_e( 'Social networks', 'mirenova' ); ?></h2>
				<p><?php esc_html_e( 'Leave a field empty to hide that network. Header and footer show only the networks filled in here.', 'mirenova' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					foreach ( MRN_Icons::socials() as $net => $label ) {
						self::field( 'social_' . $net, $label, 'url' );
					}
					?>
				</table>

				<h2 class="title"><?php esc_html_e( 'Header & Footer', 'mirenova' ); ?></h2>
				<p><?php esc_html_e( 'Pick any saved Elementor template (Templates → Saved Templates). Each page can override these in Elementor → Page Settings → “MiRenova Header & Footer” or in the page sidebar box.', 'mirenova' ); ?></p>
				<table class="form-table" role="presentation">
					<?php
					self::template_select( 'header_template', __( 'Global header', 'mirenova' ), __( 'Build it with the “MR Header” widget and save as a template.', 'mirenova' ) );
					self::template_select( 'footer_template', __( 'Global footer', 'mirenova' ), __( 'Build it with the “MR Footer” widget and save as a template.', 'mirenova' ) );
					?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

MRN_Settings::init();
