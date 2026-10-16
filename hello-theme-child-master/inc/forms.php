<?php
/**
 * Built-in forms (no plugin needed).
 *
 * - Fields are defined in the widget (MR Hero / MR Contact → Form). The server never trusts the
 *   browser: it loads the saved widget settings (post ID + element ID) and validates against them.
 * - Every request is e-mailed (wp_mail) and stored in MiRenova → Leads, so nothing is lost when
 *   the server cannot send mail.
 * - Anti-spam without captcha and compatible with page caching: honeypot, form key,
 *   minimum fill time, rate limit per visitor.
 * - Sent with fetch() to admin-ajax.php; without JavaScript the form still posts and gets a plain answer page.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_Forms {

	const CPT    = 'mrn_lead';
	const ACTION = 'mrn_form';
	const TYPES  = [ 'text', 'email', 'tel', 'number', 'textarea', 'select', 'acceptance' ];

	public static function init() {
		add_action( 'init', [ __CLASS__, 'register' ] );
		add_action( 'wp_ajax_' . self::ACTION, [ __CLASS__, 'submit' ] );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, [ __CLASS__, 'submit' ] );
		add_filter( 'wp_privacy_personal_data_exporters', [ __CLASS__, 'privacy_exporter' ] );
		add_filter( 'wp_privacy_personal_data_erasers', [ __CLASS__, 'privacy_eraser' ] );
		if ( is_admin() ) {
			add_action( 'admin_menu', [ __CLASS__, 'menu' ], 30 );
			add_filter( 'parent_file', [ __CLASS__, 'parent_file' ] );
			add_filter( 'manage_' . self::CPT . '_posts_columns', [ __CLASS__, 'columns' ] );
			add_action( 'manage_' . self::CPT . '_posts_custom_column', [ __CLASS__, 'column' ], 10, 2 );
			add_filter( 'post_row_actions', [ __CLASS__, 'row_actions' ], 10, 2 );
			add_action( 'add_meta_boxes_' . self::CPT, [ __CLASS__, 'meta_box' ] );
			add_action( 'admin_notices', [ __CLASS__, 'notices' ] );
			add_action( 'admin_post_mrn_test_mail', [ __CLASS__, 'test_mail' ] );
		}
	}

	/* ================================================================== */
	/* Defaults                                                            */
	/* ================================================================== */

	public static function type_options() {
		return [
			'text'       => __( 'Text', 'mirenova' ),
			'tel'        => __( 'Phone', 'mirenova' ),
			'email'      => __( 'E-mail', 'mirenova' ),
			'number'     => __( 'Number', 'mirenova' ),
			'textarea'   => __( 'Textarea', 'mirenova' ),
			'select'     => __( 'Dropdown', 'mirenova' ),
			'acceptance' => __( 'Checkbox (consent)', 'mirenova' ),
		];
	}

	public static function default_messages() {
		return [
			'success'  => 'Gracias. Hemos recibido tu solicitud y te contactaremos pronto.',
			'invalid'  => 'Revisa los campos marcados e inténtalo de nuevo.',
			'required' => 'Este campo es obligatorio.',
			'email'    => 'Introduce un correo electrónico válido.',
			'tel'      => 'Introduce un teléfono válido.',
			'number'   => 'Introduce un número válido.',
			'error'    => 'No se pudo enviar la solicitud. Inténtalo de nuevo o llámanos al {phone}.',
		];
	}

	public static function message_labels() {
		return [
			'success'  => __( 'Sent successfully', 'mirenova' ),
			'invalid'  => __( 'Some fields are invalid', 'mirenova' ),
			'required' => __( 'Required field is empty', 'mirenova' ),
			'email'    => __( 'Invalid e-mail', 'mirenova' ),
			'tel'      => __( 'Invalid phone', 'mirenova' ),
			'number'   => __( 'Invalid number', 'mirenova' ),
			'error'    => __( 'Could not send', 'mirenova' ),
		];
	}

	/** Messages of a form (plain text, global tokens replaced). */
	public static function messages( array $s ) {
		$m = self::default_messages();
		foreach ( $m as $k => $v ) {
			if ( isset( $s[ 'msg_' . $k ] ) && '' !== trim( (string) $s[ 'msg_' . $k ] ) ) {
				$v = (string) $s[ 'msg_' . $k ];
			}
			$m[ $k ] = trim( wp_strip_all_tags( MRN::tokens( $v, false ) ) );
		}
		return $m;
	}

	/**
	 * Notification recipients: widget field → MiRenova → Settings → Forms → global e-mail → site admin e-mail.
	 *
	 * @return string[]
	 */
	public static function recipients( $custom = '' ) {
		$list = self::emails( $custom );
		if ( ! $list ) {
			$list = self::emails( MRN::opt( 'form_to' ) );
		}
		if ( ! $list ) {
			$list = self::emails( MRN::email() );
		}
		if ( ! $list ) {
			$list = self::emails( get_option( 'admin_email' ) );
		}
		return $list;
	}

	/** "a@b.c, d@e.f" → valid unique e-mails. */
	public static function emails( $raw ) {
		$out = [];
		foreach ( preg_split( '/[\s,;]+/', (string) $raw ) as $e ) {
			$e = sanitize_email( $e );
			if ( $e && is_email( $e ) ) {
				$out[ strtolower( $e ) ] = $e;
			}
		}
		return array_values( $out );
	}

	/* ================================================================== */
	/* Fields                                                              */
	/* ================================================================== */

	/** Field name inside f[...]: the repeater item ID (stable), or its position. */
	private static function key( $f, $i ) {
		$k = isset( $f['_id'] ) ? preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $f['_id'] ) : '';
		return '' !== $k ? $k : 'f' . $i;
	}

	private static function options( $raw ) {
		$out = [];
		foreach ( preg_split( '/\r\n|\r|\n/', (string) $raw ) as $line ) {
			$line = trim( wp_strip_all_tags( $line ) );
			if ( '' !== $line ) {
				$out[] = $line;
			}
		}
		return $out;
	}

	private static function numeric( $f, $key ) {
		return isset( $f[ $key ] ) && '' !== $f[ $key ] && is_numeric( $f[ $key ] ) ? (float) $f[ $key ] : '';
	}

	/**
	 * Normalized field list of a form widget.
	 *
	 * @return array[]
	 */
	public static function fields( array $s ) {
		$out   = [];
		$items = isset( $s['form_fields'] ) && is_array( $s['form_fields'] ) ? array_values( $s['form_fields'] ) : [];
		foreach ( $items as $i => $f ) {
			$type   = isset( $f['type'] ) && in_array( $f['type'], self::TYPES, true ) ? $f['type'] : 'text';
			$ph     = isset( $f['placeholder'] ) ? trim( wp_strip_all_tags( (string) $f['placeholder'] ) ) : '';
			$accept = 'acceptance' === $type && isset( $f['accept'] ) ? (string) $f['accept'] : '';
			$label  = isset( $f['label'] ) ? trim( wp_strip_all_tags( (string) $f['label'] ) ) : '';
			if ( '' === $label ) {
				$label = 'acceptance' === $type ? wp_trim_words( wp_strip_all_tags( $accept ), 6 ) : $ph;
			}
			if ( '' === $label ) {
				$types = self::type_options();
				$label = $types[ $type ];
			}
			$ac = isset( $f['autocomplete'] ) ? preg_replace( '/[^a-z0-9 -]/', '', (string) $f['autocomplete'] ) : '';
			if ( '' === $ac && in_array( $type, [ 'email', 'tel' ], true ) ) {
				$ac = $type;
			}
			$out[] = [
				'key'          => self::key( $f, $i ),
				'type'         => $type,
				'placeholder'  => $ph,
				'label'        => $label,
				'required'     => isset( $f['required'] ) && 'yes' === $f['required'],
				'half'         => isset( $f['width'] ) && 'half' === $f['width'],
				'options'      => 'select' === $type ? self::options( isset( $f['options'] ) ? $f['options'] : '' ) : [],
				'rows'         => isset( $f['rows'] ) && (int) $f['rows'] ? max( 2, min( 12, (int) $f['rows'] ) ) : 3,
				'min'          => self::numeric( $f, 'min' ),
				'max'          => self::numeric( $f, 'max' ),
				'autocomplete' => $ac,
				'accept'       => $accept,
			];
		}
		return $out;
	}

	/* ================================================================== */
	/* Render                                                              */
	/* ================================================================== */

	/**
	 * Form HTML for a widget.
	 *
	 * @param array  $s       Widget settings.
	 * @param string $el_id   Elementor element ID.
	 * @param int    $post_id Document (page / template) that stores the widget.
	 */
	public static function render( array $s, $el_id, $post_id ) {
		$fields = self::fields( $s );
		if ( ! $fields ) {
			return current_user_can( 'edit_posts' ) ? '<p class="mrn-form-missing">' . esc_html__( 'Add fields in Form → Fields.', 'mirenova' ) . '</p>' : '';
		}
		$uid  = 'mrn-f-' . preg_replace( '/[^A-Za-z0-9_-]/', '', (string) $el_id );
		$html = '';
		foreach ( $fields as $f ) {
			$id    = $uid . '-' . $f['key'];
			$attrs = ' id="' . esc_attr( $id ) . '" name="f[' . esc_attr( $f['key'] ) . ']"'
				. ( $f['required'] ? ' required aria-required="true"' : '' );
			$ph    = esc_attr( $f['placeholder'] );
			$ph_a  = '' !== $ph ? ' placeholder="' . $ph . '" aria-label="' . $ph . '"' : ' aria-label="' . esc_attr( $f['label'] ) . '"';
			$ac    = $f['autocomplete'] ? ' autocomplete="' . esc_attr( $f['autocomplete'] ) . '"' : '';
			switch ( $f['type'] ) {
				case 'textarea':
					$ctrl = '<textarea class="mrn-form__control"' . $attrs . $ph_a . ' rows="' . (int) $f['rows'] . '" maxlength="5000"></textarea>';
					break;
				case 'select':
					$opts = '<option value="">' . ( '' !== $ph ? $ph : '—' ) . '</option>';
					foreach ( $f['options'] as $o ) {
						$opts .= '<option value="' . esc_attr( $o ) . '">' . esc_html( $o ) . '</option>';
					}
					$ctrl = '<select class="mrn-form__control is-empty"' . $attrs . ' aria-label="' . esc_attr( '' !== $ph ? $f['placeholder'] : $f['label'] ) . '"' . $ac . '>' . $opts . '</select>';
					break;
				case 'acceptance':
					$ctrl = '<label class="mrn-form__check" for="' . esc_attr( $id ) . '"><input type="checkbox" value="1"' . $attrs . '><span>' . MRN::inline( $f['accept'] ) . '</span></label>';
					break;
				case 'number':
					$ctrl = '<input type="number" class="mrn-form__control"' . $attrs . $ph_a . ' inputmode="decimal" step="any"'
						. ( '' !== $f['min'] ? ' min="' . esc_attr( $f['min'] ) . '"' : '' )
						. ( '' !== $f['max'] ? ' max="' . esc_attr( $f['max'] ) . '"' : '' ) . '>';
					break;
				default:
					$ctrl = '<input type="' . esc_attr( $f['type'] ) . '" class="mrn-form__control"' . $attrs . $ph_a . $ac
						. ( 'tel' === $f['type'] ? ' inputmode="tel"' : '' ) . ' maxlength="200">';
			}
			$html .= '<div class="mrn-form__f mrn-form__f--' . esc_attr( $f['type'] ) . ( $f['half'] ? ' mrn-form__f--half' : '' ) . '">' . $ctrl . '</div>';
		}

		$button = isset( $s['form_button'] ) && '' !== trim( (string) $s['form_button'] ) ? (string) $s['form_button'] : __( 'Send', 'mirenova' );
		$cfg    = [
			'm'    => self::messages( $s ),
			'sent' => isset( $s['form_sent'] ) ? wp_strip_all_tags( MRN::tokens( (string) $s['form_sent'], false ) ) : '',
			'name' => self::form_name( $s ),
		];
		unset( $cfg['m']['success'] );

		return '<form class="mrn-form' . ( isset( $s['form_compact'] ) && 'yes' === $s['form_compact'] ? ' mrn-form--compact' : '' ) . '" id="' . esc_attr( $uid ) . '"'
			. ' action="' . esc_url( admin_url( 'admin-ajax.php' ) ) . '" method="post" novalidate data-mrn-form="' . esc_attr( wp_json_encode( $cfg ) ) . '">'
			. '<div class="mrn-form__grid">' . $html
			. '<div class="mrn-form__f mrn-form__submit"><button type="submit" class="mrn-btn mrn-btn--primary mrn-btn--block">'
			. '<span class="mrn-btn__label">' . MRN::tokens( esc_html( $button ) ) . '</span>'
			. MRN_Icons::svg( 'arrow-right', [ 'class' => 'mrn-ico mrn-btn__icon', 'stroke-width' => '2.2' ] )
			. '<span class="mrn-form__spin" aria-hidden="true"></span></button></div>'
			. '</div>'
			. '<div class="mrn-form__hp" aria-hidden="true"><input type="text" name="mrn_website" value="" tabindex="-1" autocomplete="off"></div>'
			. '<input type="hidden" name="action" value="' . esc_attr( self::ACTION ) . '">'
			. '<input type="hidden" name="mrn_post" value="' . (int) $post_id . '">'
			. '<input type="hidden" name="mrn_el" value="' . esc_attr( $el_id ) . '">'
			. '<input type="hidden" name="mrn_k" value="' . esc_attr( self::form_key( $post_id, $el_id ) ) . '">'
			. '<div class="mrn-form__status" role="status" aria-live="polite"></div>'
			. '</form>';
	}

	private static function form_name( array $s ) {
		$name = isset( $s['form_name'] ) ? trim( wp_strip_all_tags( (string) $s['form_name'] ) ) : '';
		return '' !== $name ? $name : __( 'Form', 'mirenova' );
	}

	/** Static per form (works with page caching), unknown to bots that post blindly. */
	private static function form_key( $post_id, $el_id ) {
		return substr( wp_hash( 'mrn-form|' . (int) $post_id . '|' . $el_id ), 0, 16 );
	}

	/* ================================================================== */
	/* Submit                                                              */
	/* ================================================================== */

	public static function submit() {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- public form; nonces break with page caching, see anti-spam checks.
		$js      = ! empty( $_POST['mrn_js'] );
		$post_id = isset( $_POST['mrn_post'] ) ? absint( wp_unslash( $_POST['mrn_post'] ) ) : 0;
		$el_id   = isset( $_POST['mrn_el'] ) ? preg_replace( '/[^A-Za-z0-9]/', '', (string) wp_unslash( $_POST['mrn_el'] ) ) : '';
		$key     = isset( $_POST['mrn_k'] ) ? (string) wp_unslash( $_POST['mrn_k'] ) : '';
		$trap    = isset( $_POST['mrn_website'] ) ? (string) wp_unslash( $_POST['mrn_website'] ) : '';
		$elapsed = isset( $_POST['mrn_t'] ) ? (int) $_POST['mrn_t'] : -1;
		$input   = isset( $_POST['f'] ) && is_array( $_POST['f'] ) ? wp_unslash( $_POST['f'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated per field below.
		// phpcs:enable

		$s    = self::widget_settings( $post_id, $el_id );
		$msgs = self::messages( $s ? $s : [] );
		if ( ! $s ) {
			self::respond( false, $msgs['error'], [], $js );
		}

		$min_time = (int) apply_filters( 'mrn_form_min_time', 2500 );
		$spam     = ! hash_equals( self::form_key( $post_id, $el_id ), $key ) || '' !== trim( $trap ) || ( $elapsed >= 0 && $elapsed < $min_time );
		if ( apply_filters( 'mrn_form_is_spam', $spam, $s, $input ) ) {
			self::respond( false, $msgs['error'], [], $js );
		}
		if ( self::rate_limited() ) {
			self::respond( false, $msgs['error'], [], $js );
		}

		$errors = [];
		$data   = [];
		foreach ( self::fields( $s ) as $f ) {
			$raw = isset( $input[ $f['key'] ] ) && is_scalar( $input[ $f['key'] ] ) ? (string) $input[ $f['key'] ] : '';
			list( $value, $error ) = self::validate( $f, $raw, $msgs );
			if ( '' !== $error ) {
				$errors[ $f['key'] ] = $error;
				continue;
			}
			$data[] = [
				'label' => $f['label'],
				'value' => $value,
				'type'  => $f['type'],
				'ac'    => $f['autocomplete'],
			];
		}
		if ( $errors ) {
			self::respond( false, $msgs['invalid'], $errors, $js );
		}

		$page = isset( $_POST['mrn_url'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_POST['mrn_url'] ) ), '' ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $page ) {
			$page = wp_get_referer();
		}
		$lead = apply_filters(
			'mrn_form_lead',
			[
				'form'    => self::form_name( $s ),
				'fields'  => $data,
				'page'    => $page ? $page : '',
				'post_id' => $post_id,
			],
			$s
		);

		$mailed = self::mail( $lead, $s );
		$stored = self::store( $lead, $mailed );
		self::hit_rate();
		do_action( 'mrn_form_submitted', $lead, $mailed, $stored, $s );

		if ( ! $mailed && ! $stored ) {
			self::respond( false, $msgs['error'], [], $js );
		}
		$redirect = isset( $s['form_redirect']['url'] ) ? esc_url_raw( MRN::tokens( (string) $s['form_redirect']['url'], false ) ) : '';
		self::respond( true, self::messages( $s )['success'], [], $js, $redirect );
	}

	/**
	 * Saved settings of the form widget (never trust the browser for fields or recipients).
	 *
	 * @return array|null
	 */
	private static function widget_settings( $post_id, $el_id ) {
		if ( ! $post_id || '' === $el_id || ! did_action( 'elementor/loaded' ) ) {
			return null;
		}
		if ( 'publish' !== get_post_status( $post_id ) && ! current_user_can( 'edit_post', $post_id ) ) {
			return null;
		}
		$doc = \Elementor\Plugin::$instance->documents->get( $post_id );
		if ( ! $doc ) {
			return null;
		}
		$el = self::find( (array) $doc->get_elements_data(), $el_id );
		$ok = (array) apply_filters( 'mrn_form_widgets', [ 'mrn-hero', 'mrn-contact' ] );
		if ( ! $el || empty( $el['widgetType'] ) || ! in_array( $el['widgetType'], $ok, true ) ) {
			return null;
		}
		$widget = \Elementor\Plugin::$instance->elements_manager->create_element_instance( $el );
		if ( ! $widget ) {
			return null;
		}
		$s = $widget->get_settings_for_display();
		return is_array( $s ) && isset( $s['form_on'] ) && 'yes' === $s['form_on'] ? $s : null;
	}

	private static function find( array $elements, $id ) {
		foreach ( $elements as $e ) {
			if ( isset( $e['id'] ) && $e['id'] === $id ) {
				return $e;
			}
			if ( ! empty( $e['elements'] ) && is_array( $e['elements'] ) ) {
				$found = self::find( $e['elements'], $id );
				if ( $found ) {
					return $found;
				}
			}
		}
		return null;
	}

	/**
	 * @return array [value, error message]
	 */
	private static function validate( array $f, $raw, array $m ) {
		$type = $f['type'];
		if ( 'textarea' === $type ) {
			$v = mb_substr( sanitize_textarea_field( $raw ), 0, 5000 );
		} elseif ( 'acceptance' === $type ) {
			$v = '' !== $raw && '0' !== $raw ? '✓' : '';
		} else {
			$v = mb_substr( sanitize_text_field( $raw ), 0, 300 );
		}
		if ( '' === $v ) {
			return [ '', $f['required'] ? $m['required'] : '' ];
		}
		switch ( $type ) {
			case 'email':
				if ( ! is_email( $v ) ) {
					return [ '', $m['email'] ];
				}
				$v = sanitize_email( $v );
				break;
			case 'tel':
				$digits = strlen( preg_replace( '/\D+/', '', $v ) );
				if ( $digits < 6 || $digits > 16 || preg_match( '#[^0-9+()\-.\s/]#', $v ) ) {
					return [ '', $m['tel'] ];
				}
				break;
			case 'number':
				$n = str_replace( ',', '.', $v );
				if ( ! is_numeric( $n ) || ( '' !== $f['min'] && (float) $n < $f['min'] ) || ( '' !== $f['max'] && (float) $n > $f['max'] ) ) {
					return [ '', $m['number'] ];
				}
				$v = (string) ( $n + 0 );
				break;
			case 'select':
				if ( ! in_array( $v, array_map( 'sanitize_text_field', $f['options'] ), true ) ) {
					return [ '', $m['required'] ];
				}
				break;
		}
		return [ $v, '' ];
	}

	private static function rate_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		return 'mrn_rl_' . substr( md5( $ip . '|' . $ua ), 0, 20 );
	}

	private static function rate_limited() {
		return (int) get_transient( self::rate_key() ) >= (int) apply_filters( 'mrn_form_rate_limit', 5 );
	}

	private static function hit_rate() {
		set_transient( self::rate_key(), (int) get_transient( self::rate_key() ) + 1, 10 * MINUTE_IN_SECONDS );
	}

	/**
	 * JSON for fetch(); a minimal answer page when JavaScript is off.
	 */
	private static function respond( $ok, $message, array $fields = [], $js = true, $redirect = '' ) {
		if ( $js ) {
			$data = [
				'message' => $message,
				'fields'  => (object) $fields,
			];
			if ( $ok && $redirect ) {
				$data['redirect'] = $redirect;
			}
			$ok ? wp_send_json_success( $data ) : wp_send_json_error( $data );
		}
		if ( $ok && $redirect ) {
			wp_redirect( $redirect ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- set by the site editor.
			exit;
		}
		$back = wp_get_referer();
		status_header( $ok ? 200 : 400 );
		nocache_headers();
		header( 'Content-Type: text/html; charset=utf-8' );
		echo '<!doctype html><html lang="' . esc_attr( get_bloginfo( 'language' ) ) . '"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex">'
			. '<title>' . esc_html( get_bloginfo( 'name' ) ) . '</title></head>'
			. '<body style="max-width:560px;margin:60px auto;padding:0 20px;font:16px/1.6 sans-serif;color:#0E2A4F">'
			. '<p>' . esc_html( $message ) . '</p>'
			. ( $fields ? '<ul><li>' . implode( '</li><li>', array_map( 'esc_html', $fields ) ) . '</li></ul>' : '' )
			. ( $back ? '<p><a href="' . esc_url( $back ) . '">← ' . esc_html( get_bloginfo( 'name' ) ) . '</a></p>' : '' )
			. '</body></html>';
		exit;
	}

	/* ================================================================== */
	/* Mail & storage                                                      */
	/* ================================================================== */

	/** First value of a kind: name / tel / email. */
	private static function pick( array $lead, $kind ) {
		foreach ( $lead['fields'] as $f ) {
			if ( '' === $f['value'] ) {
				continue;
			}
			if ( ( 'name' === $kind && 'name' === $f['ac'] ) || ( 'name' !== $kind && $kind === $f['type'] ) ) {
				return $f['value'];
			}
		}
		if ( 'name' === $kind ) {
			foreach ( $lead['fields'] as $f ) {
				if ( 'text' === $f['type'] && '' !== $f['value'] ) {
					return $f['value'];
				}
			}
		}
		return '';
	}

	private static function mail( array $lead, array $s ) {
		$to = self::recipients( isset( $s['mail_to'] ) ? $s['mail_to'] : '' );
		if ( ! $to ) {
			return false;
		}
		$name    = self::pick( $lead, 'name' );
		$subject = isset( $s['mail_subject'] ) && '' !== trim( (string) $s['mail_subject'] ) ? (string) $s['mail_subject'] : 'Nueva solicitud: {form} — {name}';
		$subject = strtr(
			$subject,
			[
				'{form}' => $lead['form'],
				'{name}' => $name,
				'{site}' => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			]
		);
		$subject = trim( preg_replace( '/[\s:—–-]+$/u', '', wp_strip_all_tags( $subject ) ) );
		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		$email   = self::pick( $lead, 'email' );
		if ( $email ) {
			$headers[] = 'Reply-To: ' . $email;
		}
		$from = static function () {
			return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
		};
		add_filter( 'wp_mail_from_name', $from );
		$ok = wp_mail( $to, $subject, self::mail_body( $lead ), apply_filters( 'mrn_form_mail_headers', $headers, $lead ) );
		remove_filter( 'wp_mail_from_name', $from );
		return (bool) $ok;
	}

	private static function value_html( array $f ) {
		$v = esc_html( $f['value'] );
		switch ( $f['type'] ) {
			case 'textarea':
				return nl2br( $v );
			case 'email':
				return '<a href="mailto:' . esc_attr( $f['value'] ) . '">' . $v . '</a>';
			case 'tel':
				return '<a href="tel:' . esc_attr( MRN::phone_digits( $f['value'] ) ) . '">' . $v . '</a>';
		}
		return $v;
	}

	private static function rows_html( array $fields, $td1, $td2 ) {
		$rows = '';
		foreach ( $fields as $f ) {
			if ( '' === $f['value'] ) {
				continue;
			}
			$rows .= '<tr><td style="' . $td1 . '">' . esc_html( $f['label'] ) . '</td><td style="' . $td2 . '">' . self::value_html( $f ) . '</td></tr>';
		}
		return $rows;
	}

	private static function mail_body( array $lead ) {
		$rows = self::rows_html( $lead['fields'], 'padding:9px 18px 9px 0;vertical-align:top;color:#5A6A82;white-space:nowrap', 'padding:9px 0;vertical-align:top;color:#0E2A4F;font-weight:600' );
		$meta = wp_date( get_option( 'date_format' ) . ' H:i' )
			. ( $lead['page'] ? ' · <a href="' . esc_url( $lead['page'] ) . '" style="color:#1F7BFF">' . esc_html( preg_replace( '#^https?://#', '', $lead['page'] ) ) . '</a>' : '' );
		return '<!doctype html><html><body style="margin:0;padding:24px;background:#F6F8FB;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:1.5">'
			. '<div style="max-width:620px;margin:0 auto;padding:28px;background:#ffffff;border-radius:4px">'
			. '<h1 style="margin:0 0 18px;font-size:20px;line-height:1.3;color:#0E2A4F">' . esc_html( $lead['form'] ) . '</h1>'
			. '<table role="presentation" cellpadding="0" cellspacing="0" style="border-collapse:collapse">' . $rows . '</table>'
			. '<p style="margin:24px 0 0;font-size:13px;color:#5A6A82">' . $meta . '<br>' . esc_html( get_bloginfo( 'name' ) ) . '</p>'
			. '</div></body></html>';
	}

	private static function store( array $lead, $mailed ) {
		if ( ! MRN::opt( 'form_store' ) ) {
			return false;
		}
		$name  = self::pick( $lead, 'name' );
		$phone = self::pick( $lead, 'tel' );
		$email = self::pick( $lead, 'email' );
		$id    = wp_insert_post(
			[
				'post_type'   => self::CPT,
				'post_status' => 'publish',
				'post_title'  => $name ? $name : ( $phone ? $phone : ( $email ? $email : __( 'Lead', 'mirenova' ) ) ),
				'meta_input'  => [
					'_mrn_form'   => $lead['form'],
					'_mrn_fields' => $lead['fields'],
					'_mrn_page'   => $lead['page'],
					'_mrn_phone'  => $phone,
					'_mrn_email'  => $email ? strtolower( $email ) : '',
					'_mrn_mail'   => $mailed ? 'sent' : 'failed',
				],
			],
			true
		);
		return $id && ! is_wp_error( $id );
	}

	/* ================================================================== */
	/* Admin: Leads                                                        */
	/* ================================================================== */

	public static function register() {
		$cap = 'manage_options';
		register_post_type(
			self::CPT,
			[
				'labels'              => [
					'name'               => __( 'Leads', 'mirenova' ),
					'singular_name'      => __( 'Lead', 'mirenova' ),
					'menu_name'          => __( 'Leads', 'mirenova' ),
					'all_items'          => __( 'Leads', 'mirenova' ),
					'edit_item'          => __( 'Lead', 'mirenova' ),
					'search_items'       => __( 'Search leads', 'mirenova' ),
					'not_found'          => __( 'No leads yet. Form submissions appear here.', 'mirenova' ),
					'not_found_in_trash' => __( 'No leads in the Trash.', 'mirenova' ),
				],
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'show_in_rest'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => [ 'title' ],
				'map_meta_cap'        => true,
				'capabilities'        => [
					'create_posts'           => 'do_not_allow',
					'edit_posts'             => $cap,
					'edit_others_posts'      => $cap,
					'edit_published_posts'   => $cap,
					'edit_private_posts'     => $cap,
					'publish_posts'          => $cap,
					'read_private_posts'     => $cap,
					'delete_posts'           => $cap,
					'delete_others_posts'    => $cap,
					'delete_published_posts' => $cap,
					'delete_private_posts'   => $cap,
				],
			]
		);
	}

	public static function menu() {
		add_submenu_page( MRN_Settings::PAGE, __( 'Leads', 'mirenova' ), __( 'Leads', 'mirenova' ), 'manage_options', 'edit.php?post_type=' . self::CPT );
	}

	public static function parent_file( $file ) {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		return $screen && self::CPT === $screen->post_type ? MRN_Settings::PAGE : $file;
	}

	public static function columns( $cols ) {
		return [
			'cb'        => isset( $cols['cb'] ) ? $cols['cb'] : '',
			'title'     => __( 'Name', 'mirenova' ),
			'mrn_phone' => __( 'Phone', 'mirenova' ),
			'mrn_email' => __( 'E-mail', 'mirenova' ),
			'mrn_form'  => __( 'Form', 'mirenova' ),
			'mrn_page'  => __( 'Page', 'mirenova' ),
			'mrn_mail'  => __( 'E-mail sent', 'mirenova' ),
			'date'      => __( 'Date', 'mirenova' ),
		];
	}

	public static function column( $col, $post_id ) {
		switch ( $col ) {
			case 'mrn_phone':
				$v = (string) get_post_meta( $post_id, '_mrn_phone', true );
				echo $v ? '<a href="tel:' . esc_attr( MRN::phone_digits( $v ) ) . '">' . esc_html( $v ) . '</a>' : '—';
				break;
			case 'mrn_email':
				$v = (string) get_post_meta( $post_id, '_mrn_email', true );
				echo $v ? '<a href="mailto:' . esc_attr( $v ) . '">' . esc_html( $v ) . '</a>' : '—';
				break;
			case 'mrn_form':
				echo esc_html( (string) get_post_meta( $post_id, '_mrn_form', true ) );
				break;
			case 'mrn_page':
				$v = (string) get_post_meta( $post_id, '_mrn_page', true );
				echo $v ? '<a href="' . esc_url( $v ) . '" target="_blank" rel="noopener">' . esc_html( wp_parse_url( $v, PHP_URL_PATH ) ? wp_parse_url( $v, PHP_URL_PATH ) : '/' ) . '</a>' : '—';
				break;
			case 'mrn_mail':
				echo 'failed' === get_post_meta( $post_id, '_mrn_mail', true ) ? '<span style="color:#b32d2e">✖ ' . esc_html__( 'Not sent', 'mirenova' ) . '</span>' : '✓';
				break;
		}
	}

	public static function row_actions( $actions, $post ) {
		if ( self::CPT === $post->post_type ) {
			unset( $actions['inline hide-if-no-js'] );
			if ( isset( $actions['edit'] ) ) {
				$actions['edit'] = '<a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html__( 'View', 'mirenova' ) . '</a>';
			}
		}
		return $actions;
	}

	public static function meta_box() {
		add_meta_box( 'mrn-lead', __( 'Request', 'mirenova' ), [ __CLASS__, 'meta_box_html' ], self::CPT, 'normal', 'high' );
	}

	public static function meta_box_html( $post ) {
		$fields = (array) get_post_meta( $post->ID, '_mrn_fields', true );
		$page   = (string) get_post_meta( $post->ID, '_mrn_page', true );
		echo '<table class="widefat striped" style="border:0"><tbody>';
		echo self::rows_html( $fields, 'width:220px;color:#50575e', 'font-weight:600' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<tr><td style="color:#50575e">' . esc_html__( 'Form', 'mirenova' ) . '</td><td>' . esc_html( (string) get_post_meta( $post->ID, '_mrn_form', true ) ) . '</td></tr>';
		if ( $page ) {
			echo '<tr><td style="color:#50575e">' . esc_html__( 'Page', 'mirenova' ) . '</td><td><a href="' . esc_url( $page ) . '" target="_blank" rel="noopener">' . esc_html( $page ) . '</a></td></tr>';
		}
		echo '<tr><td style="color:#50575e">' . esc_html__( 'Date', 'mirenova' ) . '</td><td>' . esc_html( get_the_date( get_option( 'date_format' ) . ' H:i', $post ) ) . '</td></tr>';
		echo '<tr><td style="color:#50575e">' . esc_html__( 'E-mail notification', 'mirenova' ) . '</td><td>' . ( 'failed' === get_post_meta( $post->ID, '_mrn_mail', true ) ? esc_html__( 'Not sent — check the mail settings of the server (SMTP).', 'mirenova' ) : esc_html__( 'Sent', 'mirenova' ) ) . '</td></tr>';
		echo '</tbody></table>';
	}

	/** Warn when e-mails could not be sent (leads are still saved). */
	public static function notices() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( 'toplevel_page_' . MRN_Settings::PAGE === $screen->id && isset( $_GET['mrn_mail_test'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$err = get_transient( 'mrn_mail_test_error' );
			delete_transient( 'mrn_mail_test_error' );
			if ( 'ok' === $_GET['mrn_mail_test'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( sprintf( /* translators: %s: e-mails */ __( 'Test e-mail sent to %s. Check the inbox and the spam folder.', 'mirenova' ), implode( ', ', self::recipients() ) ) ) . '</p></div>';
			} else {
				echo '<div class="notice notice-error"><p>' . esc_html__( 'The server could not send the test e-mail. Install an SMTP plugin (e.g. WP Mail SMTP or FluentSMTP) and try again.', 'mirenova' ) . ( $err ? ' <code>' . esc_html( $err ) . '</code>' : '' ) . '</p></div>';
			}
		}
		if ( 'edit-' . self::CPT !== $screen->id && 'toplevel_page_' . MRN_Settings::PAGE !== $screen->id ) {
			return;
		}
		$failed = get_posts(
			[
				'post_type'      => self::CPT,
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'meta_key'       => '_mrn_mail', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => 'failed', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'date_query'     => [ [ 'after' => '14 days ago' ] ],
			]
		);
		if ( $failed ) {
			echo '<div class="notice notice-warning"><p>' . esc_html__( 'Some form e-mails could not be sent by the server in the last 14 days (the leads are saved in MiRenova → Leads). Install an SMTP plugin (e.g. WP Mail SMTP or FluentSMTP) and use “Send test e-mail” in MiRenova → Settings.', 'mirenova' ) . '</p></div>';
		}
	}

	public static function test_mail() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'mrn_test_mail' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'mirenova' ) );
		}
		$err = '';
		$log = static function ( $e ) use ( &$err ) {
			$err = is_wp_error( $e ) ? $e->get_error_message() : '';
		};
		add_action( 'wp_mail_failed', $log );
		$ok = wp_mail(
			self::recipients(),
			get_bloginfo( 'name' ) . ': test e-mail',
			'<p>Test e-mail from MiRenova → Settings. Form notifications will arrive at this address.</p>',
			[ 'Content-Type: text/html; charset=UTF-8' ]
		);
		remove_action( 'wp_mail_failed', $log );
		if ( $err ) {
			set_transient( 'mrn_mail_test_error', $err, MINUTE_IN_SECONDS * 5 );
		}
		wp_safe_redirect( admin_url( 'admin.php?page=' . MRN_Settings::PAGE . '&mrn_mail_test=' . ( $ok ? 'ok' : 'fail' ) ) );
		exit;
	}

	/* ================================================================== */
	/* Privacy (Tools → Export / Erase Personal Data)                      */
	/* ================================================================== */

	private static function leads_by_email( $email, $page ) {
		return get_posts(
			[
				'post_type'      => self::CPT,
				'post_status'    => 'any',
				'posts_per_page' => 50,
				'paged'          => max( 1, (int) $page ),
				'meta_key'       => '_mrn_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value'     => strtolower( (string) $email ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			]
		);
	}

	public static function privacy_exporter( $list ) {
		$list['mirenova-leads'] = [
			'exporter_friendly_name' => __( 'MiRenova form requests', 'mirenova' ),
			'callback'               => static function ( $email, $page = 1 ) {
				$items = [];
				$posts = self::leads_by_email( $email, $page );
				foreach ( $posts as $p ) {
					$data = [];
					foreach ( (array) get_post_meta( $p->ID, '_mrn_fields', true ) as $f ) {
						if ( isset( $f['label'], $f['value'] ) && '' !== $f['value'] ) {
							$data[] = [ 'name' => $f['label'], 'value' => $f['value'] ];
						}
					}
					$items[] = [
						'group_id'    => 'mirenova-leads',
						'group_label' => __( 'Form requests', 'mirenova' ),
						'item_id'     => 'mrn-lead-' . $p->ID,
						'data'        => $data,
					];
				}
				return [ 'data' => $items, 'done' => count( $posts ) < 50 ];
			},
		];
		return $list;
	}

	public static function privacy_eraser( $list ) {
		$list['mirenova-leads'] = [
			'eraser_friendly_name' => __( 'MiRenova form requests', 'mirenova' ),
			'callback'             => static function ( $email ) {
				$posts = self::leads_by_email( $email, 1 );
				foreach ( $posts as $p ) {
					wp_delete_post( $p->ID, true );
				}
				return [
					'items_removed'  => count( $posts ) > 0,
					'items_retained' => false,
					'messages'       => [],
					'done'           => count( $posts ) < 50,
				];
			},
		];
		return $list;
	}
}

MRN_Forms::init();
