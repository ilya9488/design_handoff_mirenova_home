<?php
/**
 * Theme functions and definitions.
 *
 * MiRenova child theme for Hello Elementor: custom Elementor widgets (category "MiRenova"),
 * global contacts, header/footer templates, Contact Form 7 integration.
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );
define( 'MRN_VERSION', '1.0.0' );
define( 'MRN_DIR', get_stylesheet_directory() );
define( 'MRN_URI', get_stylesheet_directory_uri() );

require_once MRN_DIR . '/inc/icons.php';
require_once MRN_DIR . '/inc/helpers.php';
require_once MRN_DIR . '/inc/settings.php';
require_once MRN_DIR . '/inc/assets.php';
require_once MRN_DIR . '/inc/header-footer.php';
require_once MRN_DIR . '/inc/cf7.php';
require_once MRN_DIR . '/inc/elementor.php';

/*
 * One-time setup tools (MiRenova → Setup): generate the Home page, CF7 forms, menus,
 * header/footer templates and Elementor global colors & fonts.
 * When the site is ready, delete this line and the file inc/setup.php.
 */
if ( is_admin() && file_exists( MRN_DIR . '/inc/setup.php' ) ) {
	require_once MRN_DIR . '/inc/setup.php';
}

/**
 * Load child theme stylesheet only when it contains custom CSS (saves a request otherwise).
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {
	$file = get_stylesheet_directory() . '/style.css';
	if ( file_exists( $file ) && filesize( $file ) > 1200 ) {
		wp_enqueue_style(
			'hello-elementor-child-style',
			get_stylesheet_directory_uri() . '/style.css',
			[
				'hello-elementor-theme-style',
			],
			HELLO_ELEMENTOR_CHILD_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

/**
 * Customizer: button of the fallback Hello header (used only when no header template is selected).
 * The phone number is global: MiRenova → Settings.
 */
function mirenova_customize_header( $wp_customize ) {

	$wp_customize->add_section(
		'mirenova_header_contact',
		[
			'title'    => __( 'Header Contact', 'hello-elementor' ),
			'priority' => 35,
		]
	);

	$wp_customize->add_setting(
		'mirenova_header_button_text',
		[
			'default'           => 'Solicitar presupuesto',
			'sanitize_callback' => 'sanitize_text_field',
		]
	);

	$wp_customize->add_control(
		'mirenova_header_button_text',
		[
			'label'   => __( 'Button text', 'hello-elementor' ),
			'section' => 'mirenova_header_contact',
			'type'    => 'text',
		]
	);

	$wp_customize->add_setting(
		'mirenova_header_button_url',
		[
			'default'           => '#contact',
			'sanitize_callback' => 'esc_url_raw',
		]
	);

	$wp_customize->add_control(
		'mirenova_header_button_url',
		[
			'label'   => __( 'Button URL', 'hello-elementor' ),
			'section' => 'mirenova_header_contact',
			'type'    => 'url',
		]
	);
}

add_action(
	'customize_register',
	'mirenova_customize_header'
);
