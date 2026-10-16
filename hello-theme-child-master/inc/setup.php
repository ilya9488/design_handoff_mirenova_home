<?php
/**
 * MiRenova → Setup: one-time tools.
 *
 *  1. Generate the Home page: imports the bundled images into the Media Library, creates the
 *     Contact Form 7 forms, the header/footer menus, the header/footer Elementor templates
 *     (set as global) and the Home page built from MiRenova widgets with all content filled in.
 *  2. Apply Elementor global colors, fonts and theme style (with backup / restore).
 *
 * Remove after launch: delete this file and its require line in functions.php.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_Setup {

	const NONCE = 'mrn_setup';

	public static function init() {
		add_action( 'admin_menu', [ __CLASS__, 'menu' ], 20 );
		add_action( 'wp_ajax_mrn_setup', [ __CLASS__, 'ajax' ] );
	}

	public static function menu() {
		add_submenu_page( 'mirenova', __( 'MiRenova Setup', 'mirenova' ), __( 'Setup', 'mirenova' ), 'manage_options', 'mirenova-setup', [ __CLASS__, 'page' ] );
	}

	/* ================================================================== */
	/* Admin page                                                          */
	/* ================================================================== */

	public static function page() {
		$home   = (int) get_option( 'mrn_home_page' );
		$backup = get_option( 'mrn_kit_backup' );
		$cf7    = defined( 'WPCF7_VERSION' );
		$el     = did_action( 'elementor/loaded' );
		?>
		<div class="wrap mrn-setup">
			<h1><?php esc_html_e( 'MiRenova Setup', 'mirenova' ); ?></h1>
			<p><?php esc_html_e( 'One-time tools. When the site is ready, delete inc/setup.php and its line in functions.php.', 'mirenova' ); ?></p>

			<table class="widefat striped" style="max-width:760px">
				<tbody>
					<tr><td><?php esc_html_e( 'Elementor', 'mirenova' ); ?></td><td><?php echo $el ? '✅ ' . esc_html( ELEMENTOR_VERSION ) : '❌ ' . esc_html__( 'Install and activate Elementor', 'mirenova' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Contact Form 7', 'mirenova' ); ?></td><td><?php echo $cf7 ? '✅ ' . esc_html( WPCF7_VERSION ) : '❌ ' . esc_html__( 'Install and activate Contact Form 7', 'mirenova' ); ?></td></tr>
					<tr><td><?php esc_html_e( 'Home page', 'mirenova' ); ?></td><td>
						<?php
						if ( $home && get_post( $home ) ) {
							echo '<a href="' . esc_url( get_permalink( $home ) ) . '" target="_blank">' . esc_html( get_the_title( $home ) ) . '</a> · <a href="' . esc_url( admin_url( 'post.php?post=' . $home . '&action=elementor' ) ) . '">' . esc_html__( 'Edit with Elementor', 'mirenova' ) . '</a>';
						} else {
							esc_html_e( 'not generated yet', 'mirenova' );
						}
						?>
					</td></tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( '1. Generate the Home page', 'mirenova' ); ?></h2>
			<p><?php esc_html_e( 'Creates (or reuses): Media Library images, Contact Form 7 forms, header & footer menus, header & footer templates and a new Home page filled with the final content. Safe to run again — existing forms, menus, images and templates are reused; a new page is created each time.', 'mirenova' ); ?></p>
			<p>
				<label><input type="checkbox" id="mrn-front" checked> <?php esc_html_e( 'Set the new page as the site front page', 'mirenova' ); ?></label><br>
				<label><input type="checkbox" id="mrn-hf" checked> <?php esc_html_e( 'Use the generated header & footer as global (MiRenova → Settings)', 'mirenova' ); ?></label>
			</p>
			<p><button type="button" class="button button-primary button-hero" data-mrn-run="media,forms,menus,templates,home" <?php disabled( ! $el ); ?>><?php esc_html_e( 'Generate Home page', 'mirenova' ); ?></button></p>

			<h2><?php esc_html_e( '2. Elementor global colors & fonts', 'mirenova' ); ?></h2>
			<p><?php esc_html_e( 'Writes the MiRenova palette (system + custom colors), typography presets (Onest) and theme style (body, links, headings, buttons) into Elementor → Site Settings. MiRenova blocks read these colors, so changing a global color later restyles the whole site. Your current Site Settings are backed up first.', 'mirenova' ); ?></p>
			<p>
				<button type="button" class="button button-primary" data-mrn-run="kit" <?php disabled( ! $el ); ?>><?php esc_html_e( 'Apply global colors & fonts', 'mirenova' ); ?></button>
				<?php if ( $backup ) : ?>
					<button type="button" class="button" data-mrn-run="kit_restore"><?php esc_html_e( 'Restore previous Site Settings', 'mirenova' ); ?></button>
				<?php endif; ?>
			</p>

			<h2><?php esc_html_e( 'Log', 'mirenova' ); ?></h2>
			<pre id="mrn-log" style="max-width:760px;min-height:120px;padding:12px;background:#fff;border:1px solid #dcdcde;white-space:pre-wrap"></pre>
		</div>
		<script>
		(function () {
			var log = document.getElementById('mrn-log');
			var nonce = <?php echo wp_json_encode( wp_create_nonce( self::NONCE ) ); ?>;
			function write(t) { log.textContent += t + '\n'; }
			function call(step) {
				var body = new FormData();
				body.append('action', 'mrn_setup');
				body.append('step', step);
				body.append('_wpnonce', nonce);
				body.append('front', document.getElementById('mrn-front').checked ? '1' : '');
				body.append('hf', document.getElementById('mrn-hf').checked ? '1' : '');
				return fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: body })
					.then(function (r) { return r.json(); });
			}
			function run(steps, btn) {
				if (!steps.length) { write('✔ Done.'); btn.disabled = false; return; }
				var step = steps[0];
				call(step).then(function (res) {
					var d = res && res.data ? res.data : {};
					write((res && res.success ? '• ' : '✖ ') + (d.msg || step));
					if (!res || !res.success) { btn.disabled = false; return; }
					if (d.remaining > 0) { return run(steps, btn); }
					if (d.url) { write('  → ' + d.url); }
					run(steps.slice(1), btn);
				}).catch(function (e) { write('✖ ' + step + ': ' + e); btn.disabled = false; });
			}
			document.querySelectorAll('[data-mrn-run]').forEach(function (btn) {
				btn.addEventListener('click', function () {
					btn.disabled = true;
					log.textContent = '';
					run(btn.getAttribute('data-mrn-run').split(','), btn);
				});
			});
		})();
		</script>
		<?php
	}

	public static function ajax() {
		if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( self::NONCE, '_wpnonce', false ) ) {
			wp_send_json_error( [ 'msg' => 'Not allowed.' ], 403 );
		}
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}
		$step = isset( $_POST['step'] ) ? sanitize_key( $_POST['step'] ) : '';
		$opts = [
			'front' => ! empty( $_POST['front'] ),
			'hf'    => ! empty( $_POST['hf'] ),
		];
		try {
			switch ( $step ) {
				case 'media':
					$res = self::import_media( 3 );
					break;
				case 'forms':
					$res = self::create_forms();
					break;
				case 'menus':
					$res = self::create_menus();
					break;
				case 'templates':
					$res = self::create_templates( $opts['hf'] );
					break;
				case 'home':
					$res = self::create_home( $opts['front'] );
					break;
				case 'kit':
					$res = self::apply_kit();
					break;
				case 'kit_restore':
					$res = self::restore_kit();
					break;
				default:
					$res = new WP_Error( 'step', 'Unknown step.' );
			}
		} catch ( \Throwable $e ) {
			$res = new WP_Error( 'exception', $e->getMessage() );
		}
		if ( is_wp_error( $res ) ) {
			wp_send_json_error( [ 'msg' => $res->get_error_message() ] );
		}
		wp_send_json_success( $res );
	}

	/* ================================================================== */
	/* Steps                                                               */
	/* ================================================================== */

	/** Import bundled images (a few per request so slow hosts never time out). */
	public static function import_media( $batch = 3 ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$map  = (array) get_option( MRN::MEDIA_OPT, [] );
		$todo = [];
		foreach ( MRN::IMAGES as $key => $img ) {
			if ( empty( $map[ $key ] ) || 'attachment' !== get_post_type( (int) $map[ $key ] ) ) {
				$todo[] = $key;
			}
		}
		$done = [];
		foreach ( array_slice( $todo, 0, $batch ) as $key ) {
			list( $file, $alt ) = MRN::IMAGES[ $key ];
			$src = MRN_DIR . '/assets/img/' . $file;
			if ( ! file_exists( $src ) ) {
				continue;
			}
			$tmp = wp_tempnam( $file );
			copy( $src, $tmp );
			$title = ucfirst( str_replace( '-', ' ', preg_replace( '/^[A-Z]+-\d+-/', '', pathinfo( $file, PATHINFO_FILENAME ) ) ) );
			$id    = media_handle_sideload( [ 'name' => $file, 'tmp_name' => $tmp ], 0, $title );
			if ( is_wp_error( $id ) ) {
				@unlink( $tmp ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				return $id;
			}
			update_post_meta( $id, '_wp_attachment_image_alt', $alt );
			$map[ $key ] = $id;
			$done[]      = $file;
			update_option( MRN::MEDIA_OPT, $map, false );
		}
		$remaining = max( 0, count( $todo ) - count( $done ) );
		return [
			'msg'       => $done ? sprintf( 'Images imported: %s (%d left)', implode( ', ', $done ), $remaining ) : 'Images: all in the Media Library',
			'remaining' => $done ? $remaining : 0,
		];
	}

	public static function create_forms() {
		if ( ! class_exists( 'WPCF7_ContactForm' ) ) {
			return new WP_Error( 'cf7', 'Contact Form 7 is not active — forms skipped. Activate it and run again.' );
		}
		$forms = (array) get_option( 'mrn_forms', [] );
		$defs  = [
			'hero'    => [ 'MiRenova — Solicitud rápida (Hero)', MRN_CF7::template_hero(), false ],
			'contact' => [ 'MiRenova — Contacto', MRN_CF7::template_contact(), true ],
		];
		$made  = [];
		foreach ( $defs as $key => $d ) {
			if ( ! empty( $forms[ $key ] ) && 'wpcf7_contact_form' === get_post_type( (int) $forms[ $key ] ) ) {
				continue;
			}
			$cf    = WPCF7_ContactForm::get_template( [ 'title' => $d[0] ] );
			$props = $cf->get_properties();

			$props['form']                       = $d[1];
			$props['mail']['subject']            = 'Nueva solicitud de presupuesto — [your-name]';
			$props['mail']['body']               = MRN_CF7::mail_body( $d[2] );
			$props['mail']['recipient']          = '[_site_admin_email]';
			$props['mail']['additional_headers'] = $d[2] ? 'Reply-To: [your-email]' : '';
			$props['mail_2']['active']           = false;
			$props['messages']                   = array_merge(
				(array) $props['messages'],
				[
					'mail_sent_ok'     => 'Gracias. Hemos recibido tu solicitud y te contactaremos pronto.',
					'mail_sent_ng'     => 'No se pudo enviar el mensaje. Inténtalo de nuevo o llámanos.',
					'validation_error' => 'Revisa los campos marcados e inténtalo de nuevo.',
					'spam'             => 'No se pudo enviar el mensaje. Inténtalo de nuevo o llámanos.',
					'invalid_required' => 'Este campo es obligatorio.',
					'invalid_too_long' => 'El texto es demasiado largo.',
					'invalid_email'    => 'Introduce un correo electrónico válido.',
					'invalid_tel'      => 'Introduce un teléfono válido.',
					'invalid_number'   => 'Introduce un número válido.',
					'number_too_small' => 'El número es demasiado pequeño.',
					'number_too_large' => 'El número es demasiado grande.',
				]
			);
			$cf->set_properties( $props );
			$id = $cf->save();
			if ( $id ) {
				$forms[ $key ] = (int) $id;
				$made[]        = $d[0];
			}
		}
		update_option( 'mrn_forms', $forms, false );
		return [ 'msg' => $made ? 'Forms created: ' . implode( ', ', $made ) : 'Forms: already created' ];
	}

	public static function create_menus() {
		$menus = (array) get_option( 'mrn_menus', [] );
		$links = [
			'Servicios' => '#servicios',
			'Proceso'   => '#proceso',
			'Precios'   => '#precios',
			'Zonas'     => '#zonas',
			'Preguntas' => '#faq',
			'Contacto'  => '#contacto',
		];
		$defs  = [
			'header' => [ 'MiRenova — Header', $links ],
			'footer' => [ 'MiRenova — Footer', array_slice( $links, 0, 5, true ) ],
		];
		$made  = [];
		foreach ( $defs as $key => $d ) {
			if ( ! empty( $menus[ $key ] ) && is_nav_menu( (int) $menus[ $key ] ) ) {
				continue;
			}
			$existing = wp_get_nav_menu_object( $d[0] );
			if ( $existing ) {
				$menus[ $key ] = (int) $existing->term_id;
				continue;
			}
			$id = wp_create_nav_menu( $d[0] );
			if ( is_wp_error( $id ) ) {
				return $id;
			}
			foreach ( $d[1] as $title => $hash ) {
				wp_update_nav_menu_item(
					$id,
					0,
					[
						'menu-item-title'  => $title,
						'menu-item-url'    => home_url( '/' . $hash ),
						'menu-item-status' => 'publish',
						'menu-item-type'   => 'custom',
					]
				);
			}
			$menus[ $key ] = (int) $id;
			$made[]        = $d[0];
		}
		update_option( 'mrn_menus', $menus, false );
		// Hello Elementor fallback locations.
		$loc           = (array) get_theme_mod( 'nav_menu_locations', [] );
		$loc['menu-1'] = isset( $loc['menu-1'] ) && $loc['menu-1'] ? $loc['menu-1'] : $menus['header'];
		$loc['menu-2'] = isset( $loc['menu-2'] ) && $loc['menu-2'] ? $loc['menu-2'] : $menus['footer'];
		set_theme_mod( 'nav_menu_locations', $loc );
		return [ 'msg' => $made ? 'Menus created: ' . implode( ', ', $made ) : 'Menus: already created' ];
	}

	public static function create_templates( $set_global ) {
		$tpl  = (array) get_option( 'mrn_templates', [] );
		$made = [];
		foreach ( [ 'header' => 'MiRenova — Header', 'footer' => 'MiRenova — Footer' ] as $loc => $title ) {
			if ( empty( $tpl[ $loc ] ) || 'publish' !== get_post_status( (int) $tpl[ $loc ] ) ) {
				$doc = \Elementor\Plugin::$instance->documents->create( 'section', [ 'post_title' => $title, 'post_status' => 'publish' ] );
				if ( is_wp_error( $doc ) ) {
					return $doc;
				}
				$doc->save( [ 'elements' => [ self::container( self::widget( 'mrn-' . $loc ) ) ] ] );
				$tpl[ $loc ] = (int) $doc->get_main_id();
				$made[]      = $title;
			}
		}
		update_option( 'mrn_templates', $tpl, false );
		if ( $set_global ) {
			$o                    = (array) get_option( MRN::OPT, [] );
			$o['header_template'] = $tpl['header'];
			$o['footer_template'] = $tpl['footer'];
			update_option( MRN::OPT, array_merge( MRN::defaults(), $o ) );
		}
		return [ 'msg' => ( $made ? 'Templates created: ' . implode( ', ', $made ) : 'Templates: already created' ) . ( $set_global ? ' (set as global)' : '' ) ];
	}

	public static function create_home( $front ) {
		$widgets  = [ 'mrn-hero', 'mrn-about', 'mrn-cards', 'mrn-services', 'mrn-steps', 'mrn-pricing', 'mrn-locations', 'mrn-faq', 'mrn-contact' ];
		$elements = [];
		foreach ( $widgets as $w ) {
			$elements[] = self::container( self::widget( $w ) );
		}
		$doc = \Elementor\Plugin::$instance->documents->create(
			'wp-page',
			[
				'post_title'  => 'Inicio',
				'post_status' => 'publish',
				'post_type'   => 'page',
			]
		);
		if ( is_wp_error( $doc ) ) {
			return $doc;
		}
		$id = (int) $doc->get_main_id();
		update_post_meta( $id, '_wp_page_template', 'elementor_header_footer' );
		$doc->save( [ 'elements' => $elements ] );
		update_option( 'mrn_home_page', $id, false );
		if ( $front ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $id );
		}
		\Elementor\Plugin::$instance->files_manager->clear_cache();
		return [
			'msg' => 'Home page created (#' . $id . ')' . ( $front ? ' and set as front page' : '' ),
			'url' => admin_url( 'post.php?post=' . $id . '&action=elementor' ),
		];
	}

	/* ================================================================== */
	/* Elementor data helpers                                              */
	/* ================================================================== */

	private static function eid() {
		return substr( md5( wp_generate_uuid4() ), 0, 7 );
	}

	/**
	 * Widget element. Repeaters get explicit items with _id (needed for per-item styles);
	 * everything else uses the widget defaults (the final content).
	 */
	private static function widget( $name, $overrides = [] ) {
		$settings = [];
		$type     = \Elementor\Plugin::$instance->widgets_manager->get_widget_types( $name );
		if ( $type ) {
			foreach ( $type->get_controls() as $key => $control ) {
				if ( isset( $control['type'] ) && 'repeater' === $control['type'] && ! empty( $control['default'] ) && is_array( $control['default'] ) ) {
					$items = $control['default'];
					foreach ( $items as &$item ) {
						$item['_id'] = self::eid();
					}
					unset( $item );
					$settings[ $key ] = $items;
				}
			}
		}
		return [
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $name,
			'settings'   => array_merge( $settings, $overrides ),
			'elements'   => [],
		];
	}

	/** Full-width container without padding/gap (falls back to section/column). */
	private static function container( $widget ) {
		$zero = [
			'unit'     => 'px',
			'top'      => '0',
			'right'    => '0',
			'bottom'   => '0',
			'left'     => '0',
			'isLinked' => true,
		];
		if ( \Elementor\Plugin::$instance->experiments->is_feature_active( 'container' ) ) {
			return [
				'id'       => self::eid(),
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => [
					'content_width' => 'full',
					'padding'       => $zero,
					'flex_gap'      => [ 'unit' => 'px', 'size' => 0, 'column' => '0', 'row' => '0', 'isLinked' => true ],
				],
				'elements' => [ $widget ],
			];
		}
		return [
			'id'       => self::eid(),
			'elType'   => 'section',
			'isInner'  => false,
			'settings' => [
				'layout' => 'full_width',
				'gap'    => 'no',
			],
			'elements' => [
				[
					'id'       => self::eid(),
					'elType'   => 'column',
					'isInner'  => false,
					'settings' => [
						'_column_size' => 100,
						'padding'      => $zero,
					],
					'elements' => [ $widget ],
				],
			],
		];
	}

	/* ================================================================== */
	/* Elementor kit (global colors, fonts, theme style)                   */
	/* ================================================================== */

	public static function palette() {
		return [
			[ 'mrnnavydeep', 'MR Navy deep (dark sections)', '#0A1D38' ],
			[ 'mrnnavy', 'MR Navy (headings)', '#0E2A4F' ],
			[ 'mrnnavycard', 'MR Navy card', '#0F2A4D' ],
			[ 'mrnnavyhover', 'MR Navy hover', '#13366A' ],
			[ 'mrnblue', 'MR Blue (buttons)', '#1F7BFF' ],
			[ 'mrnbluedark', 'MR Blue dark (hover, links)', '#1667E0' ],
			[ 'mrngrada', 'MR Gradient start', '#1F6FFF' ],
			[ 'mrngradb', 'MR Gradient end', '#5EC4F8' ],
			[ 'mrncyan', 'MR Cyan (accent on dark)', '#7CD6F9' ],
			[ 'mrntint', 'MR Tint', '#E4EEFB' ],
			[ 'mrntintline', 'MR Tint line', '#CFE4FF' ],
			[ 'mrnsurface', 'MR Surface (light cards)', '#F2F5F9' ],
			[ 'mrnline', 'MR Lines', '#E3E9F1' ],
			[ 'mrntextstrong', 'MR Text strong', '#33435B' ],
			[ 'mrntext', 'MR Text', '#46566E' ],
			[ 'mrntextmuted', 'MR Text muted', '#5A6A82' ],
			[ 'mrntextondark', 'MR Text on dark', '#C3D1E4' ],
			[ 'mrnmutedondark', 'MR Muted on dark', '#AFC0D7' ],
		];
	}

	private static function typo( $id, $title, $weight, $size = null, $lh = null, $ls = null, $extra = [] ) {
		$t = [
			'_id'                    => $id,
			'title'                  => $title,
			'typography_typography'  => 'custom',
			'typography_font_family' => 'Onest',
			'typography_font_weight' => (string) $weight,
		];
		if ( $size ) {
			$t['typography_font_size'] = is_array( $size ) ? $size : [ 'unit' => 'px', 'size' => $size ];
		}
		if ( $lh ) {
			$t['typography_line_height'] = [ 'unit' => 'em', 'size' => $lh ];
		}
		if ( null !== $ls ) {
			$t['typography_letter_spacing'] = [ 'unit' => 'em', 'size' => $ls ];
		}
		return array_merge( $t, $extra );
	}

	public static function apply_kit() {
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		if ( ! $kit || ! $kit->get_main_id() ) {
			return new WP_Error( 'kit', 'Elementor kit not found.' );
		}
		$id  = $kit->get_main_id();
		$raw = get_post_meta( $id, '_elementor_page_settings', true );
		$raw = is_array( $raw ) ? $raw : [];
		if ( ! get_option( 'mrn_kit_backup' ) ) {
			update_option( 'mrn_kit_backup', [ 'settings' => $raw ], false );
		}
		$s = $raw;

		$s['system_colors'] = [
			[ '_id' => 'primary', 'title' => 'Primary', 'color' => '#1F7BFF' ],
			[ '_id' => 'secondary', 'title' => 'Secondary', 'color' => '#0E2A4F' ],
			[ '_id' => 'text', 'title' => 'Text', 'color' => '#46566E' ],
			[ '_id' => 'accent', 'title' => 'Accent', 'color' => '#7CD6F9' ],
		];
		$custom = [];
		foreach ( isset( $s['custom_colors'] ) ? (array) $s['custom_colors'] : [] as $c ) {
			if ( isset( $c['_id'] ) && 0 !== strpos( $c['_id'], 'mrn' ) ) {
				$custom[] = $c;
			}
		}
		foreach ( self::palette() as $p ) {
			$custom[] = [ '_id' => $p[0], 'title' => $p[1], 'color' => $p[2] ];
		}
		$s['custom_colors'] = $custom;

		$s['system_typography'] = [
			self::typo( 'primary', 'Primary', 800 ),
			self::typo( 'secondary', 'Secondary', 700 ),
			self::typo( 'text', 'Text', 400 ),
			self::typo( 'accent', 'Accent', 600 ),
		];
		$typos = [];
		foreach ( isset( $s['custom_typography'] ) ? (array) $s['custom_typography'] : [] as $t ) {
			if ( isset( $t['_id'] ) && 0 !== strpos( $t['_id'], 'mrn' ) ) {
				$typos[] = $t;
			}
		}
		$typos[]                = self::typo( 'mrnh1', 'MR H1 (hero)', 800, [ 'unit' => 'custom', 'size' => 'clamp(42px,5vw,74px)' ], 1.02, -0.03 );
		$typos[]                = self::typo( 'mrnh2', 'MR H2 (section title)', 800, [ 'unit' => 'custom', 'size' => 'clamp(34px,3.7vw,56px)' ], 1.06, -0.025 );
		$typos[]                = self::typo( 'mrnh3', 'MR H3 (card title)', 700, 26, 1.2, -0.015 );
		$typos[]                = self::typo( 'mrnlead', 'MR Lead', 400, 17, 1.75 );
		$typos[]                = self::typo( 'mrnbody', 'MR Body', 400, 16, 1.75 );
		$typos[]                = self::typo( 'mrneyebrow', 'MR Eyebrow', 700, 13, 1.4, 0.18, [ 'typography_text_transform' => 'uppercase' ] );
		$typos[]                = self::typo( 'mrnbutton', 'MR Button', 600, 15, 1.2, 0.02 );
		$s['custom_typography'] = $typos;

		// Theme style: body, links, headings, buttons (native Elementor widgets match the brand).
		$s['body_color']                  = '#46566E';
		$s['body_typography_typography']  = 'custom';
		$s['body_typography_font_family'] = 'Onest';
		$s['body_typography_font_size']   = [ 'unit' => 'px', 'size' => 16 ];
		$s['body_typography_line_height'] = [ 'unit' => 'em', 'size' => 1.6 ];
		$s['link_normal_color']           = '#1667E0';
		$s['link_hover_color']            = '#0E2A4F';
		foreach ( [ 1 => 800, 2 => 800, 3 => 700, 4 => 700, 5 => 700, 6 => 700 ] as $h => $w ) {
			$s[ 'h' . $h . '_color' ]                  = '#0E2A4F';
			$s[ 'h' . $h . '_typography_typography' ]  = 'custom';
			$s[ 'h' . $h . '_typography_font_family' ] = 'Onest';
			$s[ 'h' . $h . '_typography_font_weight' ] = (string) $w;
		}
		$s['button_typography_typography']        = 'custom';
		$s['button_typography_font_family']       = 'Onest';
		$s['button_typography_font_weight']       = '600';
		$s['button_typography_font_size']         = [ 'unit' => 'px', 'size' => 15 ];
		$s['button_text_color']                   = '#FFFFFF';
		$s['button_background_background']        = 'classic';
		$s['button_background_color']             = '#1F7BFF';
		$s['button_hover_text_color']             = '#FFFFFF';
		$s['button_hover_background_background']  = 'classic';
		$s['button_hover_background_color']       = '#1667E0';
		$s['button_border_radius']                = [ 'unit' => 'px', 'top' => '3', 'right' => '3', 'bottom' => '3', 'left' => '3', 'isLinked' => true ];
		$s['button_padding']                      = [ 'unit' => 'px', 'top' => '16', 'right' => '28', 'bottom' => '16', 'left' => '28', 'isLinked' => false ];

		$kit->save( [ 'settings' => $s ] );
		\Elementor\Plugin::$instance->files_manager->clear_cache();
		return [ 'msg' => 'Elementor Site Settings updated: ' . count( self::palette() ) . ' brand colors, 4 system colors, 7 typography presets, theme style. Backup saved.' ];
	}

	public static function restore_kit() {
		$backup = get_option( 'mrn_kit_backup' );
		if ( ! is_array( $backup ) || ! isset( $backup['settings'] ) ) {
			return new WP_Error( 'kit', 'No backup found.' );
		}
		$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
		$kit->save( [ 'settings' => (array) $backup['settings'] ] );
		delete_option( 'mrn_kit_backup' );
		\Elementor\Plugin::$instance->files_manager->clear_cache();
		return [ 'msg' => 'Previous Elementor Site Settings restored.' ];
	}
}

MRN_Setup::init();
