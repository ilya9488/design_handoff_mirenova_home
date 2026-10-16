<?php
/**
 * Contact Form 7 integration.
 *
 * - CF7 JS/CSS load only on pages that actually render a form (better LCP/TBT).
 * - MiRenova forms (markup contains "mrn-cf7") get a real <button> with arrow icon and no autop.
 * - Form templates used by the setup tool (placeholders only, no labels).
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_CF7 {

	public static function init() {
		add_filter( 'wpcf7_load_js', '__return_false' );
		add_filter( 'wpcf7_load_css', '__return_false' );
		add_filter( 'do_shortcode_tag', [ __CLASS__, 'on_shortcode' ], 10, 2 );
		add_filter( 'wpcf7_autop_or_not', [ __CLASS__, 'autop' ] );
		add_filter( 'wpcf7_form_elements', [ __CLASS__, 'submit_button' ] );
		add_filter( 'wpcf7_form_class_attr', [ __CLASS__, 'form_class' ] );
	}

	private static function is_mrn_form() {
		if ( ! function_exists( 'wpcf7_get_current_contact_form' ) ) {
			return false;
		}
		$form = wpcf7_get_current_contact_form();
		return $form && false !== strpos( (string) $form->prop( 'form' ), 'mrn-cf7' );
	}

	/** Enqueue CF7 assets only when a form is rendered. */
	public static function on_shortcode( $output, $tag ) {
		if ( 'contact-form-7' === $tag || 'contact-form' === $tag ) {
			self::enqueue( false !== strpos( (string) $output, 'mrn-cf7' ) );
		}
		return $output;
	}

	/**
	 * @param bool $mrn_only MiRenova forms are fully styled by the theme, CF7 CSS is not needed.
	 */
	public static function enqueue( $mrn_only = true ) {
		if ( function_exists( 'wpcf7_enqueue_scripts' ) && ! wp_script_is( 'contact-form-7', 'enqueued' ) ) {
			wpcf7_enqueue_scripts();
		}
		if ( ! $mrn_only && function_exists( 'wpcf7_enqueue_styles' ) && ! wp_style_is( 'contact-form-7', 'enqueued' ) ) {
			wpcf7_enqueue_styles();
		}
	}

	public static function autop( $autop ) {
		return self::is_mrn_form() ? false : $autop;
	}

	public static function form_class( $class ) {
		return self::is_mrn_form() ? $class . ' mrn-form' : $class;
	}

	/**
	 * MiRenova forms: placeholders double as accessible names (no visible labels by design),
	 * and <input type="submit"> becomes a <button> with label + arrow icon.
	 */
	public static function submit_button( $html ) {
		if ( ! self::is_mrn_form() ) {
			return $html;
		}
		$html = preg_replace_callback(
			'#<(input|textarea)\b((?:(?!aria-label)[^>])*?)\splaceholder="([^"]*)"#i',
			static function ( $m ) {
				return '<' . $m[1] . $m[2] . ' placeholder="' . $m[3] . '" aria-label="' . $m[3] . '"';
			},
			$html
		);
		$html = preg_replace_callback(
			'#<select\b([^>]*)>\s*<option value="">([^<]*)</option>#i',
			static function ( $m ) {
				$attrs = false === stripos( $m[1], 'aria-label' ) ? $m[1] . ' aria-label="' . $m[2] . '"' : $m[1];
				return '<select' . $attrs . '><option value="">' . $m[2] . '</option>';
			},
			$html
		);
		return preg_replace_callback(
			'#<input([^>]*?)type="submit"([^>]*?)/?>#i',
			static function ( $m ) {
				$attrs = $m[1] . $m[2];
				$label = '';
				if ( preg_match( '#\svalue="([^"]*)"#i', $attrs, $v ) ) {
					$label = $v[1];
					$attrs = str_replace( $v[0], '', $attrs );
				}
				$attrs = preg_replace( '#\sclass="#i', ' class="mrn-btn mrn-btn--primary mrn-btn--block ', $attrs, 1 );
				return '<button type="submit"' . rtrim( $attrs ) . '><span class="mrn-btn__label">' . $label . '</span>'
					. MRN_Icons::svg( 'arrow-right', [ 'class' => 'mrn-ico mrn-btn__icon', 'stroke-width' => '2.2' ] ) . '</button>';
			},
			$html
		);
	}

	/**
	 * Render a form for a widget (maps the ID to the current language).
	 */
	public static function render( $form_id, $sent_label = '' ) {
		$form_id = MRN::tr_id( (int) $form_id, 'wpcf7_contact_form' );
		if ( ! $form_id || ! defined( 'WPCF7_VERSION' ) || 'wpcf7_contact_form' !== get_post_type( $form_id ) ) {
			if ( current_user_can( 'edit_posts' ) ) {
				return '<p class="mrn-form-missing">' . esc_html__( 'Select a Contact Form 7 form in the widget settings (Form → Contact Form 7).', 'mirenova' ) . '</p>';
			}
			return '';
		}
		$html = do_shortcode( '[contact-form-7 id="' . $form_id . '"]' );
		return '<div class="mrn-cf7-wrap"' . ( $sent_label ? ' data-mrn-sent="' . esc_attr( $sent_label ) . '"' : '' ) . '>' . $html . '</div>';
	}

	/* ------------------------------------------------------------------ */
	/* Templates for the setup tool                                        */
	/* ------------------------------------------------------------------ */

	public static function types() {
		return '"Reforma integral de piso o ático" "Reforma de casa o villa" "Reforma de cocina" "Reforma de baño"';
	}

	public static function template_hero() {
		return '<div class="mrn-cf7 mrn-cf7--compact">' . "\n"
			. '<div class="mrn-cf7__f">[text* your-name autocomplete:name placeholder "Nombre"]</div>' . "\n"
			. '<div class="mrn-cf7__f">[tel* your-phone autocomplete:tel placeholder "Teléfono"]</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full">[text your-location placeholder "Ubicación de la vivienda"]</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full">[select your-type first_as_label "Tipo de reforma" ' . self::types() . ']</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full mrn-cf7__submit">[submit "Solicitar presupuesto"]</div>' . "\n"
			. '</div>';
	}

	public static function template_contact() {
		return '<div class="mrn-cf7">' . "\n"
			. '<div class="mrn-cf7__f">[text* your-name autocomplete:name placeholder "Nombre"]</div>' . "\n"
			. '<div class="mrn-cf7__f">[tel* your-phone autocomplete:tel placeholder "Teléfono"]</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full">[email your-email autocomplete:email placeholder "Correo electrónico"]</div>' . "\n"
			. '<div class="mrn-cf7__f">[text your-location placeholder "Ubicación de la vivienda"]</div>' . "\n"
			. '<div class="mrn-cf7__f">[number your-area min:1 max:5000 placeholder "Superficie, m²"]</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full">[select your-type first_as_label "Tipo de reforma" ' . self::types() . ']</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full">[textarea your-message x3 placeholder "Qué reformas necesitas"]</div>' . "\n"
			. '<div class="mrn-cf7__f mrn-cf7__f--full mrn-cf7__submit">[submit "Solicitar presupuesto de reformas"]</div>' . "\n"
			. '</div>';
	}

	public static function mail_body( $contact ) {
		$rows = [
			'Nombre: [your-name]',
			'Teléfono: [your-phone]',
		];
		if ( $contact ) {
			$rows[] = 'Correo: [your-email]';
		}
		$rows[] = 'Ubicación: [your-location]';
		if ( $contact ) {
			$rows[] = 'Superficie: [your-area] m²';
		}
		$rows[] = 'Tipo de reforma: [your-type]';
		if ( $contact ) {
			$rows[] = "Mensaje:\n[your-message]";
		}
		return implode( "\n", $rows ) . "\n\n--\n[_site_title] ([_site_url])\n[_url]";
	}
}

MRN_CF7::init();
