<?php
/**
 * Front-end assets: one small CSS file in <head>, one deferred JS file, self-hosted Onest font.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_Assets {

	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ], 15 );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'frontend_tweaks' ], 100 );
		add_action( 'wp_head', [ __CLASS__, 'preload_font' ], 1 );

		// Small, safe speed wins.
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
	}

	public static function enqueue() {
		wp_enqueue_style( 'mrn', MRN_URI . '/assets/css/mirenova.css', [], MRN_VERSION );
		wp_add_inline_style( 'mrn', self::breakpoint_css() );

		wp_register_script(
			'mrn',
			MRN_URI . '/assets/js/mirenova.js',
			[],
			MRN_VERSION,
			[
				'in_footer' => true,
				'strategy'  => 'defer',
			]
		);
	}

	/**
	 * Render-blocking diet on the frontend:
	 * - the MiRenova icon CSS is only for the Elementor icon picker (frontend uses inline SVG);
	 * - jQuery goes to the footer (nothing in MiRenova needs it; Elementor loads it in the footer anyway).
	 *   Disable with: add_filter( 'mrn_jquery_in_footer', '__return_false' );
	 */
	public static function frontend_tweaks() {
		wp_dequeue_style( 'elementor-icons-mrn-icons' );

		$preview = isset( $_GET['elementor-preview'] ) || is_customize_preview(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! $preview && did_action( 'elementor/loaded' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
			$preview = true;
		}
		if ( ! $preview && apply_filters( 'mrn_jquery_in_footer', true ) ) {
			foreach ( [ 'jquery', 'jquery-core', 'jquery-migrate' ] as $handle ) {
				wp_script_add_data( $handle, 'group', 1 );
			}
		}
	}

	/** Preload the Latin subset of Onest (the only one Spanish pages need). */
	public static function preload_font() {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( MRN_URI . '/assets/fonts/onest-latin.woff2' )
		);
	}

	/** Elementor breakpoints (tablet / mobile), falling back to Elementor defaults. */
	public static function breakpoints() {
		$tablet = 1024;
		$mobile = 767;
		if ( did_action( 'elementor/loaded' ) && isset( \Elementor\Plugin::$instance->breakpoints ) ) {
			try {
				$bps = \Elementor\Plugin::$instance->breakpoints->get_breakpoints();
				if ( isset( $bps['tablet'] ) ) {
					$tablet = (int) $bps['tablet']->get_value();
				}
				if ( isset( $bps['mobile'] ) ) {
					$mobile = (int) $bps['mobile']->get_value();
				}
			} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			}
		}
		return [ $tablet, $mobile ];
	}

	/**
	 * Column grids and "hide on device" helpers follow the Elementor breakpoints,
	 * so the responsive switcher in the editor matches what visitors see.
	 */
	public static function breakpoint_css() {
		list( $t, $m ) = self::breakpoints();
		$t1 = $t + 1;
		$m1 = $m + 1;
		return "@media (min-width:{$t1}px){.mrn-hide-d{display:none!important}}"
			. "@media (max-width:{$t}px){"
			. '.mrn .mrn-grid{grid-template-columns:repeat(var(--ct,1),minmax(0,1fr))}'
			. '.mrn .mrn-grid>.mrn-fill{grid-column:span var(--ft,1);display:var(--vt,flex)}'
			. '.mrn .mrn-step__line{display:none}'
			. '}'
			. "@media (min-width:{$m1}px) and (max-width:{$t}px){.mrn-hide-t{display:none!important}}"
			. "@media (max-width:{$m}px){"
			. '.mrn .mrn-grid{grid-template-columns:repeat(var(--cm,1),minmax(0,1fr))}'
			. '.mrn .mrn-grid>.mrn-fill{grid-column:span var(--fm,1);display:var(--vm,flex)}'
			. '.mrn-hide-m{display:none!important}'
			. '}';
	}
}

MRN_Assets::init();
