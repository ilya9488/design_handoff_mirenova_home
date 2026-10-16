<?php
/**
 * The template for displaying the footer.
 *
 * MiRenova: prints the footer template chosen in MiRenova → Settings (or the page override),
 * then falls back to Elementor Pro Theme Builder and the Hello Elementor footer.
 *
 * @package HelloElementorChild
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! MRN_Header_Footer::render( 'footer' ) ) {
	if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
		if ( hello_elementor_display_header_footer() ) {
			if ( did_action( 'elementor/loaded' ) && hello_header_footer_experiment_active() ) {
				get_template_part( 'template-parts/dynamic-footer' );
			} else {
				get_template_part( 'template-parts/footer' );
			}
		}
	}
}
?>

<?php wp_footer(); ?>

</body>
</html>
