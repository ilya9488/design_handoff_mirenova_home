<?php
/**
 * The template for displaying the header.
 *
 * MiRenova: prints the header template chosen in MiRenova → Settings (or the page override),
 * then falls back to Elementor Pro Theme Builder and the Hello Elementor header.
 *
 * @package HelloElementorChild
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$viewport_content = apply_filters( 'hello_elementor_viewport_content', 'width=device-width, initial-scale=1' );
$enable_skip_link = apply_filters( 'hello_elementor_enable_skip_link', true );
$skip_link_url    = apply_filters( 'hello_elementor_skip_link_url', '#content' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="<?php echo esc_attr( $viewport_content ); ?>">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<?php if ( $enable_skip_link ) { ?>
<a class="skip-link screen-reader-text" href="<?php echo esc_url( $skip_link_url ); ?>"><?php echo esc_html__( 'Skip to content', 'hello-elementor' ); ?></a>
<?php } ?>

<?php
if ( ! MRN_Header_Footer::render( 'header' ) ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'header' ) ) {
		if ( hello_elementor_display_header_footer() ) {
			if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
				get_template_part( 'template-parts/dynamic-header' );
			} else {
				get_template_part( 'template-parts/header' );
			}
		}
	}
}
