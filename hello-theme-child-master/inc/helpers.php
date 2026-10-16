<?php
/**
 * Shared helpers: global contacts (single source of truth), tokens, WPML mapping,
 * images, rich text and lists for Elementor selects.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

final class MRN {

	const OPT       = 'mrn_settings';
	const MEDIA_OPT = 'mrn_media';

	/**
	 * Bundled images: key => [file, alt]. Imported into the Media Library by the setup tool.
	 */
	const IMAGES = [
		'logo'    => [ 'logo-mr.webp', 'MiRenova' ],
		'foto01'  => [ 'FOTO-01-hero-malaga-living-room.webp', 'Salón mediterráneo luminoso con vistas a Málaga' ],
		'foto02'  => [ 'FOTO-02-mediterranean-staircase.webp', 'Escalera mediterránea en una vivienda reformada' ],
		'foto03'  => [ 'FOTO-03-team-on-site.webp', 'Dos profesionales revisando planos en una reforma' ],
		'foto04'  => [ 'FOTO-04-porcelain-tile-quality-check.webp', 'Control de calidad de porcelánico de gran formato' ],
		'foto05'  => [ 'FOTO-05-porcelain-workshop.webp', 'Taller propio de porcelánico y encimeras a medida' ],
		'foto06'  => [ 'FOTO-06-malaga-penthouse.webp', 'Ático reformado en Málaga' ],
		'foto07'  => [ 'FOTO-07-costa-del-sol-villa.webp', 'Villa reformada en la Costa del Sol' ],
		'foto08'  => [ 'FOTO-08-porcelain-kitchen.webp', 'Cocina reformada con encimera de porcelánico' ],
		'foto09'  => [ 'FOTO-09-spa-bathroom.webp', 'Baño reformado con acabados tipo spa' ],
		'foto10'  => [ 'FOTO-10-renovation-engineering-carpentry.webp', 'Reforma integral: instalaciones, albañilería y carpintería' ],
		'bg00'    => [ 'BG-00-bright-hero-background.webp', '' ],
		'bg01'    => [ 'BG-01-architectural-light-shadow.webp', '' ],
		'bg02'    => [ 'BG-02-architectural-blueprint.webp', '' ],
		'bg03'    => [ 'BG-03-malaga-aerial-coast.webp', '' ],
		'bg04'    => [ 'BG-04-contact-light-interior.webp', '' ],
		'extra01' => [ 'EXTRA-01-blue-frosted-glass-diamond.webp', '' ],
		'extra02' => [ 'EXTRA-02-olive-window-shadow.webp', '' ],
		'png01'   => [ 'PNG-01-olive-branch-vase.webp', '' ],
		'png02'   => [ 'PNG-02-tile-stone-samples.webp', '' ],
		'png03'   => [ 'PNG-03-architect-tools.webp', '' ],
		'png04'   => [ 'PNG-04-interior-material-moodboard.webp', '' ],
		'png05'   => [ 'PNG-05-keys-mirenova-keychain.webp', '' ],
		'png06'   => [ 'PNG-06-rolled-blueprints-tape.webp', '' ],
		'png07'   => [ 'PNG-07-palm-leaves-corner.webp', '' ],
	];

	/* ------------------------------------------------------------------ */
	/* Global settings (MiRenova → Settings)                               */
	/* ------------------------------------------------------------------ */

	public static function defaults() {
		$d = [
			'phone'           => '+34 652 469 039',
			'email'           => 'grupo.arvion@gmail.com',
			'whatsapp'        => '',
			'hours'           => 'Lunes a viernes, de 09:00 a 18:00, hora de Málaga.',
			'address'         => '',
			'header_template' => 0,
			'footer_template' => 0,
		];
		foreach ( array_keys( MRN_Icons::socials() ) as $net ) {
			$d[ 'social_' . $net ] = '';
		}
		return $d;
	}

	/**
	 * Read one global setting. WPML/Polylang translate the text keys (see wpml-config.xml → admin-texts).
	 */
	public static function opt( $key ) {
		$o = wp_parse_args( (array) get_option( self::OPT, [] ), self::defaults() );
		return isset( $o[ $key ] ) ? $o[ $key ] : '';
	}

	public static function phone() {
		return trim( (string) self::opt( 'phone' ) );
	}

	/** Phone digits for tel: links (keeps a leading +). */
	public static function phone_digits( $phone = null ) {
		$phone = null === $phone ? self::phone() : $phone;
		return preg_replace( '/(?!^\+)[^\d]/', '', trim( (string) $phone ) );
	}

	public static function email() {
		return sanitize_email( (string) self::opt( 'email' ) );
	}

	public static function whatsapp_url() {
		$n = preg_replace( '/\D/', '', (string) self::opt( 'whatsapp' ) );
		return $n ? 'https://wa.me/' . $n : '';
	}

	/**
	 * Link for a "link type" select used by buttons, contact rows and strip cells.
	 *
	 * @param string $type  url|phone|email|whatsapp|none
	 * @param array  $url   Elementor URL control value (for type=url).
	 * @return array Elementor-style URL array (url may be empty).
	 */
	public static function link( $type, $url = [] ) {
		$url = is_array( $url ) ? $url : [];
		switch ( $type ) {
			case 'phone':
				$d = self::phone_digits();
				return [ 'url' => $d ? 'tel:' . $d : '' ];
			case 'email':
				$e = self::email();
				return [ 'url' => $e ? 'mailto:' . $e : '' ];
			case 'whatsapp':
				return [ 'url' => self::whatsapp_url(), 'is_external' => 'on' ];
			case 'none':
				return [ 'url' => '' ];
		}
		$url['url'] = isset( $url['url'] ) ? self::tokens( (string) $url['url'], false ) : '';
		return $url;
	}

	/**
	 * Replace global tokens: {phone} {tel} {email} {whatsapp} {hours} {address} {year} {site}.
	 *
	 * @param string $s      Text.
	 * @param bool   $escape Escape replacement values for HTML output.
	 */
	public static function tokens( $s, $escape = true ) {
		$s = (string) $s;
		if ( false === strpos( $s, '{' ) ) {
			return $s;
		}
		$map = [
			'{phone}'    => self::phone(),
			'{tel}'      => self::phone_digits(),
			'{email}'    => self::email(),
			'{whatsapp}' => (string) self::opt( 'whatsapp' ),
			'{hours}'    => (string) self::opt( 'hours' ),
			'{address}'  => (string) self::opt( 'address' ),
			'{year}'     => wp_date( 'Y' ),
			'{site}'     => get_bloginfo( 'name' ),
		];
		if ( $escape ) {
			$map = array_map( 'esc_html', $map );
		}
		return strtr( $s, $map );
	}

	/* ------------------------------------------------------------------ */
	/* Text                                                                */
	/* ------------------------------------------------------------------ */

	/**
	 * WYSIWYG value → inline HTML for headings, labels, questions (no <p>, keeps <strong>, <a>, <br>…).
	 */
	public static function inline( $html ) {
		$html = self::tokens( (string) $html );
		$html = preg_replace( '#</p>\s*<p[^>]*>#i', '<br>', $html );
		$html = preg_replace( '#</?(p|div|h[1-6])(\s[^>]*)?>#i', '', $html );
		$html = trim( $html );
		$html = preg_replace( "/\r?\n/", '<br>', $html );
		return $html;
	}

	/**
	 * WYSIWYG value → block HTML (paragraphs, lists, links, shortcodes).
	 */
	public static function rich( $html ) {
		$html = trim( self::tokens( (string) $html ) );
		if ( '' === $html ) {
			return '';
		}
		$html = wpautop( $html );
		$html = shortcode_unautop( $html );
		return do_shortcode( $html );
	}

	/** Plain text version (repeater titles, aria labels, JSON-LD). */
	public static function plain( $html ) {
		return trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( self::tokens( (string) $html, false ) ) ) );
	}

	/** Spanish-style number: 80000 → 80.000 */
	public static function num( $n, $sep = '.' ) {
		return number_format( (float) $n, 0, ',', $sep );
	}

	/* ------------------------------------------------------------------ */
	/* WPML / Polylang                                                     */
	/* ------------------------------------------------------------------ */

	/** Map an object ID to the current language (WPML & Polylang both support this filter). */
	public static function tr_id( $id, $type ) {
		$id = (int) $id;
		if ( $id <= 0 ) {
			return $id;
		}
		return (int) apply_filters( 'wpml_object_id', $id, $type, true );
	}

	/* ------------------------------------------------------------------ */
	/* Media                                                               */
	/* ------------------------------------------------------------------ */

	/** Elementor MEDIA default for a bundled image: Media Library item if imported, theme file otherwise. */
	public static function media( $key ) {
		if ( ! isset( self::IMAGES[ $key ] ) ) {
			return [ 'url' => '', 'id' => '' ];
		}
		$map = (array) get_option( self::MEDIA_OPT, [] );
		$id  = isset( $map[ $key ] ) ? (int) $map[ $key ] : 0;
		if ( $id && 'attachment' === get_post_type( $id ) ) {
			return [ 'url' => (string) wp_get_attachment_url( $id ), 'id' => $id ];
		}
		return [ 'url' => MRN_URI . '/assets/img/' . self::IMAGES[ $key ][0], 'id' => '' ];
	}

	/**
	 * <img> for an Elementor MEDIA value with srcset, width/height (no CLS) and loading hints.
	 *
	 * @param array  $media Elementor MEDIA value.
	 * @param string $size  Registered image size.
	 * @param array  $attrs Extra attributes (class, loading, fetchpriority, sizes, alt…).
	 */
	public static function img( $media, $size = 'large', $attrs = [] ) {
		$media = is_array( $media ) ? $media : [];
		$id    = isset( $media['id'] ) ? (int) $media['id'] : 0;
		$attrs = array_merge( [ 'decoding' => 'async' ], $attrs );

		if ( $id && wp_attachment_is_image( $id ) ) {
			if ( isset( $attrs['alt'] ) && '' === $attrs['alt'] ) {
				$attrs['alt'] = '';
			}
			return wp_get_attachment_image( $id, $size ? $size : 'full', false, $attrs );
		}

		$url = isset( $media['url'] ) ? (string) $media['url'] : '';
		if ( '' === $url ) {
			return '';
		}
		$dims = self::local_dims( $url );
		if ( $dims ) {
			$attrs = array_merge( [ 'width' => $dims[0], 'height' => $dims[1] ], $attrs );
		}
		if ( ! isset( $attrs['alt'] ) ) {
			$attrs['alt'] = self::bundled_alt( $url );
		}
		$attrs = array_merge( [ 'src' => $url ], $attrs );
		unset( $attrs['sizes'] );
		$out = '<img';
		foreach ( $attrs as $k => $v ) {
			if ( null === $v || false === $v ) {
				continue;
			}
			$out .= ' ' . $k . '="' . ( 'src' === $k ? esc_url( $v ) : esc_attr( $v ) ) . '"';
		}
		return $out . '>';
	}

	/** URL of an Elementor MEDIA value for a given size. */
	public static function img_url( $media, $size = 'full' ) {
		$id = isset( $media['id'] ) ? (int) $media['id'] : 0;
		if ( $id ) {
			$src = wp_get_attachment_image_src( $id, $size );
			if ( $src ) {
				return $src[0];
			}
		}
		return isset( $media['url'] ) ? (string) $media['url'] : '';
	}

	private static function bundled_alt( $url ) {
		$file = basename( wp_parse_url( $url, PHP_URL_PATH ) );
		foreach ( self::IMAGES as $img ) {
			if ( $img[0] === $file ) {
				return $img[1];
			}
		}
		return '';
	}

	/** Width/height of a theme-bundled image (cached). */
	private static function local_dims( $url ) {
		if ( 0 !== strpos( $url, MRN_URI ) ) {
			return null;
		}
		$cache = get_transient( 'mrn_img_dims' );
		$cache = is_array( $cache ) ? $cache : [];
		if ( isset( $cache[ $url ] ) ) {
			return $cache[ $url ];
		}
		$path = MRN_DIR . substr( $url, strlen( MRN_URI ) );
		$size = file_exists( $path ) ? wp_getimagesize( $path ) : false;
		$cache[ $url ] = $size ? [ (int) $size[0], (int) $size[1] ] : null;
		set_transient( 'mrn_img_dims', $cache, WEEK_IN_SECONDS );
		return $cache[ $url ];
	}

	/* ------------------------------------------------------------------ */
	/* Lists for Elementor selects                                         */
	/* ------------------------------------------------------------------ */

	/** Contact Form 7 forms: id => title. */
	public static function forms() {
		static $list = null;
		if ( null !== $list ) {
			return $list;
		}
		$list = [];
		if ( ! defined( 'WPCF7_VERSION' ) ) {
			return $list;
		}
		$posts = get_posts(
			[
				'post_type'        => 'wpcf7_contact_form',
				'numberposts'      => 200,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'suppress_filters' => false,
			]
		);
		foreach ( $posts as $p ) {
			$list[ (string) $p->ID ] = $p->post_title . ' (#' . $p->ID . ')';
		}
		return $list;
	}

	/** Navigation menus: id => name. */
	public static function menus() {
		$list = [];
		foreach ( wp_get_nav_menus() as $m ) {
			$list[ (string) $m->term_id ] = $m->name;
		}
		return $list;
	}

	/** Elementor saved templates usable as header/footer: id => title. */
	public static function templates() {
		static $list = null;
		if ( null !== $list ) {
			return $list;
		}
		$list  = [];
		$posts = get_posts(
			[
				'post_type'        => 'elementor_library',
				'numberposts'      => 300,
				'orderby'          => 'title',
				'order'            => 'ASC',
				'post_status'      => 'publish',
				'suppress_filters' => false,
			]
		);
		foreach ( $posts as $p ) {
			$type = get_post_meta( $p->ID, '_elementor_template_type', true );
			if ( in_array( $type, [ 'kit', 'popup', 'loop-item', 'e-floating-buttons' ], true ) ) {
				continue;
			}
			$list[ (string) $p->ID ] = $p->post_title . ( $type ? ' — ' . $type : '' );
		}
		return $list;
	}

	/**
	 * wp_nav_menu() for MiRenova widgets. Anchor links (/#servicios) are not marked as
	 * "current" (WordPress would highlight all of them on the home page and add aria-current).
	 */
	public static function nav_menu( $menu_id, $class, $depth = 1 ) {
		$menu_id = self::tr_id( (int) $menu_id, 'nav_menu' );
		if ( ! $menu_id || ! is_nav_menu( $menu_id ) ) {
			return '';
		}
		$classes = static function ( $c, $item ) {
			if ( false !== strpos( (string) $item->url, '#' ) ) {
				$c = array_diff( (array) $c, [ 'current-menu-item', 'current_page_item', 'current-menu-ancestor', 'current-menu-parent', 'current_page_parent', 'current_page_ancestor' ] );
			}
			return $c;
		};
		$attrs   = static function ( $a, $item ) {
			if ( false !== strpos( (string) $item->url, '#' ) ) {
				unset( $a['aria-current'] );
			}
			return $a;
		};
		add_filter( 'nav_menu_css_class', $classes, 99, 2 );
		add_filter( 'nav_menu_link_attributes', $attrs, 99, 2 );
		$html = wp_nav_menu(
			[
				'menu'        => $menu_id,
				'container'   => false,
				'menu_class'  => $class,
				'depth'       => (int) $depth,
				'fallback_cb' => false,
				'echo'        => false,
			]
		);
		remove_filter( 'nav_menu_css_class', $classes, 99 );
		remove_filter( 'nav_menu_link_attributes', $attrs, 99 );
		return (string) $html;
	}

	/** Configured social links: [ [key, label, url], … ] (empty ones skipped). */
	public static function socials() {
		$out = [];
		foreach ( MRN_Icons::socials() as $key => $label ) {
			$url = trim( (string) self::opt( 'social_' . $key ) );
			if ( '' !== $url ) {
				$out[] = [ $key, $label, $url ];
			}
		}
		return $out;
	}

	/** <ul> of social icon links. */
	public static function socials_html( $class = '' ) {
		$items = self::socials();
		if ( ! $items ) {
			return '';
		}
		$html = '<ul class="mrn-social ' . esc_attr( $class ) . '">';
		foreach ( $items as $s ) {
			$html .= '<li><a href="' . esc_url( $s[2] ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( $s[1] ) . '">'
				. MRN_Icons::svg( $s[0] ) . '</a></li>';
		}
		return $html . '</ul>';
	}
}
