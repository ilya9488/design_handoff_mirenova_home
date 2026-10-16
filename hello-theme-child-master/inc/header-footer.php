<?php
/**
 * Header & footer from Elementor templates.
 *
 * Priority: page override (Elementor Page Settings / sidebar box) → global (MiRenova → Settings)
 * → Elementor Pro Theme Builder location → Hello Elementor default.
 * Template IDs are mapped to the current language (WPML / Polylang).
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_Header_Footer {

	const META_KEY = '_elementor_page_settings';

	/** @var array<string,int> resolved per request */
	private static $resolved = [];

	public static function init() {
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue' ], 20 );
		add_action( 'elementor/documents/register_controls', [ __CLASS__, 'document_controls' ] );
		add_action( 'add_meta_boxes', [ __CLASS__, 'add_meta_box' ] );
		add_action( 'save_post', [ __CLASS__, 'save_meta_box' ], 10, 2 );

		// Elementor "Full Width" template has no <main> landmark – add one (a11y + SEO).
		add_action( 'elementor/page_templates/header-footer/before_content', [ __CLASS__, 'main_open' ] );
		add_action( 'elementor/page_templates/header-footer/after_content', [ __CLASS__, 'main_close' ] );
	}

	public static function main_open() {
		echo '<main id="content" class="site-main mrn-main">';
	}

	public static function main_close() {
		echo '</main>';
	}

	/**
	 * Template ID for a location: >0 template, 0 = theme default, -1 = disabled.
	 *
	 * @param string $location header|footer
	 */
	public static function resolve( $location ) {
		if ( isset( self::$resolved[ $location ] ) ) {
			return self::$resolved[ $location ];
		}
		$id = 0;

		if ( is_singular( 'elementor_library' ) ) {
			// Editing/previewing a template itself: no site header/footer around it.
			return self::$resolved[ $location ] = -1;
		}

		$page_id = 0;
		if ( is_singular() ) {
			$page_id = get_queried_object_id();
		} elseif ( is_home() && ! is_front_page() ) {
			$page_id = (int) get_option( 'page_for_posts' );
		}

		$override = '';
		if ( $page_id ) {
			$settings = get_post_meta( $page_id, self::META_KEY, true );
			if ( is_array( $settings ) && ! empty( $settings[ 'mrn_' . $location ] ) ) {
				$override = (string) $settings[ 'mrn_' . $location ];
			}
		}

		if ( 'none' === $override ) {
			$id = -1;
		} elseif ( '' !== $override && 'global' !== $override ) {
			$id = (int) $override;
		} else {
			$id = (int) MRN::opt( $location . '_template' );
		}

		if ( $id > 0 ) {
			$id = MRN::tr_id( $id, 'elementor_library' );
			if ( 'publish' !== get_post_status( $id ) ) {
				$id = 0;
			}
		}
		return self::$resolved[ $location ] = $id;
	}

	/** Load template CSS in <head> (no FOUC). */
	public static function enqueue() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return;
		}
		$need = false;
		foreach ( [ 'header', 'footer' ] as $loc ) {
			$id = self::resolve( $loc );
			if ( $id > 0 ) {
				$need = true;
				if ( class_exists( '\Elementor\Core\Files\CSS\Post' ) ) {
					\Elementor\Core\Files\CSS\Post::create( $id )->enqueue();
				}
			}
		}
		if ( $need ) {
			\Elementor\Plugin::$instance->frontend->enqueue_styles();
		}
	}

	/**
	 * Print a location. Returns false when nothing was printed (theme fallback should run).
	 *
	 * @param string $location header|footer
	 */
	public static function render( $location ) {
		$id = self::resolve( $location );
		if ( -1 === $id ) {
			return true;
		}
		if ( $id <= 0 || ! did_action( 'elementor/loaded' ) ) {
			return false;
		}
		$html = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $id );
		if ( '' === trim( (string) $html ) ) {
			return false;
		}
		if ( 'header' === $location ) {
			$sticky = false !== strpos( $html, 'mrn-header--sticky' ) ? ' mrn-site-header--sticky' : '';
			echo '<div class="mrn-site-header' . esc_attr( $sticky ) . '">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<div class="mrn-site-footer">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		return true;
	}

	/* ------------------------------------------------------------------ */
	/* Per-page override: Elementor Page Settings                          */
	/* ------------------------------------------------------------------ */

	private static function choices() {
		return [
			'global' => __( 'Global (MiRenova → Settings)', 'mirenova' ),
			'none'   => __( 'Disabled on this page', 'mirenova' ),
		] + MRN::templates();
	}

	/**
	 * @param \Elementor\Core\Base\Document $document
	 */
	public static function document_controls( $document ) {
		if ( ! $document instanceof \Elementor\Core\DocumentTypes\PageBase ) {
			return;
		}
		if ( 'elementor_library' === get_post_type( $document->get_main_id() ) ) {
			return;
		}
		$document->start_controls_section(
			'mrn_hf_section',
			[
				'label' => __( 'MiRenova Header & Footer', 'mirenova' ),
				'tab'   => \Elementor\Controls_Manager::TAB_SETTINGS,
			]
		);
		$document->add_control(
			'mrn_header',
			[
				'label'   => __( 'Header', 'mirenova' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => self::choices(),
				'default' => 'global',
			]
		);
		$document->add_control(
			'mrn_footer',
			[
				'label'   => __( 'Footer', 'mirenova' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => self::choices(),
				'default' => 'global',
			]
		);
		$document->add_control(
			'mrn_hf_note',
			[
				'type'            => \Elementor\Controls_Manager::RAW_HTML,
				'raw'             => __( 'A page template overrides the global one only for this page. Save and reload the editor to see the change.', 'mirenova' ),
				'content_classes' => 'elementor-descriptor',
			]
		);
		$document->end_controls_section();
	}

	/* ------------------------------------------------------------------ */
	/* Per-page override: classic / block editor sidebar box               */
	/* ------------------------------------------------------------------ */

	public static function add_meta_box() {
		foreach ( get_post_types( [ 'public' => true ] ) as $pt ) {
			if ( in_array( $pt, [ 'attachment', 'elementor_library' ], true ) ) {
				continue;
			}
			add_meta_box( 'mrn-hf', __( 'MiRenova Header & Footer', 'mirenova' ), [ __CLASS__, 'meta_box' ], $pt, 'side', 'low' );
		}
	}

	public static function meta_box( $post ) {
		$settings = get_post_meta( $post->ID, self::META_KEY, true );
		$settings = is_array( $settings ) ? $settings : [];
		wp_nonce_field( 'mrn_hf_save', 'mrn_hf_nonce' );
		foreach ( [ 'header' => __( 'Header', 'mirenova' ), 'footer' => __( 'Footer', 'mirenova' ) ] as $loc => $label ) {
			$val = isset( $settings[ 'mrn_' . $loc ] ) ? (string) $settings[ 'mrn_' . $loc ] : 'global';
			echo '<p><label for="mrn-hf-' . esc_attr( $loc ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
			echo '<select style="width:100%" id="mrn-hf-' . esc_attr( $loc ) . '" name="mrn_hf[' . esc_attr( $loc ) . ']">';
			foreach ( self::choices() as $k => $t ) {
				echo '<option value="' . esc_attr( $k ) . '"' . selected( $val, (string) $k, false ) . '>' . esc_html( $t ) . '</option>';
			}
			echo '</select></p>';
		}
	}

	public static function save_meta_box( $post_id, $post ) {
		if ( ! isset( $_POST['mrn_hf_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['mrn_hf_nonce'] ), 'mrn_hf_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in       = isset( $_POST['mrn_hf'] ) && is_array( $_POST['mrn_hf'] ) ? wp_unslash( $_POST['mrn_hf'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$settings = get_post_meta( $post_id, self::META_KEY, true );
		$settings = is_array( $settings ) ? $settings : [];
		foreach ( [ 'header', 'footer' ] as $loc ) {
			$v = isset( $in[ $loc ] ) ? sanitize_key( $in[ $loc ] ) : 'global';
			if ( 'global' === $v ) {
				unset( $settings[ 'mrn_' . $loc ] );
			} else {
				$settings[ 'mrn_' . $loc ] = $v;
			}
		}
		if ( $settings ) {
			update_post_meta( $post_id, self::META_KEY, $settings );
		} else {
			delete_post_meta( $post_id, self::META_KEY );
		}
	}
}

MRN_Header_Footer::init();
