<?php
/**
 * Elementor integration: "MiRenova" category (first in the panel), widgets,
 * "MiRenova" icon library tab and the self-hosted Onest font.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN_Elementor {

	const WIDGETS = [
		'header'    => 'MRN_Widget_Header',
		'hero'      => 'MRN_Widget_Hero',
		'about'     => 'MRN_Widget_About',
		'cards'     => 'MRN_Widget_Cards',
		'services'  => 'MRN_Widget_Services',
		'steps'     => 'MRN_Widget_Steps',
		'pricing'   => 'MRN_Widget_Pricing',
		'locations' => 'MRN_Widget_Locations',
		'faq'       => 'MRN_Widget_Faq',
		'contact'   => 'MRN_Widget_Contact',
		'footer'    => 'MRN_Widget_Footer',
	];

	public static function init() {
		add_action( 'elementor/elements/categories_registered', [ __CLASS__, 'category' ], 1 );
		add_action( 'elementor/widgets/register', [ __CLASS__, 'widgets' ] );
		add_filter( 'elementor/icons_manager/additional_tabs', [ __CLASS__, 'icon_tab' ] );
		add_filter( 'elementor/fonts/additional_fonts', [ __CLASS__, 'fonts' ] );
		add_filter( 'elementor/fonts/groups', [ __CLASS__, 'font_groups' ] );
		add_action( 'elementor/editor/after_enqueue_styles', [ __CLASS__, 'editor_css' ] );
	}

	/**
	 * Register the category and move it to the top of the panel.
	 *
	 * @param \Elementor\Elements_Manager $manager
	 */
	public static function category( $manager ) {
		$manager->add_category(
			'mirenova',
			[
				'title' => 'MiRenova',
				'icon'  => 'eicon-star',
			]
		);
		try {
			$prop = new \ReflectionProperty( $manager, 'categories' );
			if ( PHP_VERSION_ID < 80100 ) {
				$prop->setAccessible( true );
			}
			$cats = (array) $prop->getValue( $manager );
			if ( isset( $cats['mirenova'] ) ) {
				$ours = [ 'mirenova' => $cats['mirenova'] ];
				unset( $cats['mirenova'] );
				$prop->setValue( $manager, $ours + $cats );
			}
		} catch ( \Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Category still works, just not first.
		}
	}

	/**
	 * @param \Elementor\Widgets_Manager $manager
	 */
	public static function widgets( $manager ) {
		require_once MRN_DIR . '/inc/widgets/class-base.php';
		foreach ( self::WIDGETS as $file => $class ) {
			require_once MRN_DIR . '/inc/widgets/class-' . $file . '.php';
			$manager->register( new $class() );
		}
	}

	/** "MiRenova" tab in the Elementor icon picker (rendered as inline SVG on the frontend). */
	public static function icon_tab( $tabs ) {
		$tabs['mrn-icons'] = [
			'name'          => 'mrn-icons',
			'label'         => 'MiRenova',
			'url'           => MRN_URI . '/assets/css/mrn-icons.css',
			'enqueue'       => [],
			'prefix'        => 'mrn-i-',
			'displayPrefix' => 'mrn-i',
			'labelIcon'     => 'mrn-i mrn-i-home',
			'ver'           => MRN_VERSION,
			'fetchJson'     => MRN_URI . '/assets/js/mrn-icons.json',
			'native'        => false,
		];
		// The CSS only feeds the editor's icon picker; the frontend renders inline SVG (no extra request).
		if ( ! is_admin() ) {
			unset( $tabs['mrn-icons']['url'] );
		}
		return $tabs;
	}

	/** Onest is self-hosted by the theme – stop Elementor from loading it from Google. */
	public static function fonts( $fonts ) {
		$fonts['Onest'] = 'mirenova';
		return $fonts;
	}

	public static function font_groups( $groups ) {
		return [ 'mirenova' => 'MiRenova (self-hosted)' ] + $groups;
	}

	public static function editor_css() {
		wp_enqueue_style( 'mrn-icons', MRN_URI . '/assets/css/mrn-icons.css', [], MRN_VERSION );
		wp_add_inline_style(
			'mrn-icons',
			'#elementor-panel-category-mirenova .elementor-panel-category-title{color:#1F7BFF}'
			. '.elementor-element .icon .eicon-mrn{display:inline-block;width:28px;height:28px}'
		);
	}
}

MRN_Elementor::init();
