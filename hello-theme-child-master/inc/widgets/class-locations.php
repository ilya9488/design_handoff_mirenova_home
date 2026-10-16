<?php
/**
 * MR Locations: text + glass cards with location chips. Each chip can link to a page or any URL.
 *
 * @package MiRenova
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Repeater;

class MRN_Widget_Locations extends MRN_Widget_Base {

	public function get_name() {
		return 'mrn-locations';
	}

	public function get_title() {
		return 'MR Locations';
	}

	public function get_icon() {
		return 'eicon-map-pin';
	}

	public function get_keywords() {
		return [ 'mirenova', 'mrn', 'locations', 'zones', 'areas', 'cities', 'map' ];
	}

	protected function register_controls() {
		$this->start_controls_section( 's_content', [ 'label' => __( 'Content', 'mirenova' ) ] );
		$this->heading_controls(
			[
				'eyebrow'   => 'Zonas',
				'title'     => 'Reformas <strong>en Málaga</strong> y provincia',
				'text'      => '<p>Realizamos reformas de viviendas en Málaga, incluidos Centro, Soho, La Malagueta, El Limonar, Pedregalejo y El Palo. Nuestra empresa organiza las visitas desde su base en Málaga.</p><p>También ofrecemos reformas en Torremolinos, Benalmádena, Fuengirola, Mijas y Marbella, además de Alhaurín de la Torre y Rincón de la Victoria. Para otras localidades de la provincia y la Costa del Sol, valoramos volumen, desplazamiento y disponibilidad.</p>',
				'text_lead' => '',
			]
		);
		$this->buttons_controls( [] );
		$this->end_controls_section();

		$this->start_controls_section( 's_items', [ 'label' => __( 'Locations', 'mirenova' ) ] );
		$this->note( 'loc_note', __( 'A “Group title” item starts a new card. Locations after it go into that card. Link each location to a page (start typing its name) or any URL.', 'mirenova' ) );
		$r = new Repeater();
		$r->add_control(
			'type',
			[
				'label'   => __( 'Item type', 'mirenova' ),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'location' => __( 'Location', 'mirenova' ),
					'group'    => __( 'Group title (new card)', 'mirenova' ),
				],
				'default' => 'location',
			]
		);
		$r->add_control( 'name', [ 'label' => __( 'Name', 'mirenova' ), 'type' => Controls_Manager::TEXT, 'default' => 'Málaga', 'label_block' => true, 'dynamic' => [ 'active' => true ] ] );
		$r->add_control(
			'link',
			[
				'label'       => __( 'Link (page or URL)', 'mirenova' ),
				'type'        => Controls_Manager::URL,
				'autocomplete' => true,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => __( 'Type a page name or paste a URL', 'mirenova' ),
				'condition'   => [ 'type' => 'location' ],
			]
		);
		$r->add_control(
			'icon',
			[
				'label'       => __( 'Icon (optional)', 'mirenova' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
				'label_block' => false,
				'default'     => [ 'value' => '', 'library' => '' ],
				'description' => __( 'Empty = default icon below.', 'mirenova' ),
				'condition'   => [ 'type' => 'location' ],
			]
		);
		$list = [ [ 'type' => 'group', 'name' => 'Málaga' ] ];
		foreach ( [ 'Centro', 'Soho', 'La Malagueta', 'El Limonar', 'Pedregalejo', 'El Palo' ] as $n ) {
			$list[] = [ 'type' => 'location', 'name' => $n ];
		}
		$list[] = [ 'type' => 'group', 'name' => 'Costa del Sol y provincia' ];
		foreach ( [ 'Torremolinos', 'Benalmádena', 'Fuengirola', 'Mijas', 'Marbella', 'Alhaurín de la Torre', 'Rincón de la Victoria' ] as $n ) {
			$list[] = [ 'type' => 'location', 'name' => $n ];
		}
		$this->add_control(
			'items',
			[
				'label'       => __( 'Items', 'mirenova' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'default'     => $list,
				'title_field' => '{{{ "group" === type ? "▸ " + name.toUpperCase() : "— " + name }}}',
			]
		);
		$this->add_control(
			'loc_icon',
			[
				'label'   => __( 'Default location icon', 'mirenova' ),
				'type'    => Controls_Manager::ICONS,
				'default' => [ 'value' => 'mrn-i mrn-i-map-pin', 'library' => 'mrn-icons' ],
			]
		);
		$this->tag_control( 'group_tag', __( 'Group title HTML tag', 'mirenova' ), 'h3' );
		$this->end_controls_section();

		$this->start_controls_section( 's_layout', [ 'label' => __( 'Layout', 'mirenova' ) ] );
		$this->add_control(
			'cards_side',
			[
				'label'   => __( 'Cards position', 'mirenova' ),
				'type'    => Controls_Manager::CHOOSE,
				'options' => [
					'left'  => [ 'title' => __( 'Left', 'mirenova' ), 'icon' => 'eicon-h-align-left' ],
					'right' => [ 'title' => __( 'Right', 'mirenova' ), 'icon' => 'eicon-h-align-right' ],
				],
				'default' => 'right',
				'toggle'  => false,
			]
		);
		$this->end_controls_section();

		$this->section_controls_open( [ 'anchor' => 'zonas', 'scheme' => 'dark' ] );
		$this->end_controls_section();

		$this->background_controls(
			[
				'image' => MRN::media( 'bg03' ),
				'ov1'   => self::gradient( 'rgba(10,29,56,.97)', 20, 'rgba(10,29,56,.55)', 100, 90 ),
			]
		);
		$this->decor_controls(
			[
				self::d( 'grad', 460, [ 'right', '-180px' ], [ 'bottom', '-140px' ], [ 'opacity' => [ 'size' => 0.3 ], 'radius' => [ 'unit' => '%', 'size' => 10 ] ] ),
				self::d( 'line', 200, [ 'right', '22%' ], [ 'top', '-90px' ], [ 'color' => 'rgba(124,214,249,.25)', 'radius' => [ 'unit' => '%', 'size' => 12 ] ] ),
				self::d( 'image', 300, [ 'right', '-1%' ], [ 'top', '-2%' ], [ 'image' => MRN::media( 'png07' ), 'hide_t' => 'yes', 'hide_m' => 'yes' ] ),
			]
		);
		$this->style_heading( '.mrn-loc__text ' );
		$this->style_box( 'group', __( 'Group card', 'mirenova' ), '{{WRAPPER}} .mrn-loc__card' );
		$this->style_text( 'grouptitle', __( 'Group title', 'mirenova' ), '{{WRAPPER}} .mrn-loc__title' );
		$this->style_text( 'chip', __( 'Location chip', 'mirenova' ), '{{WRAPPER}} .mrn-chip', [ 'hover' => true ] );
		$this->style_buttons();
	}

	protected function render() {
		$s      = $this->get_settings_for_display();
		$groups = [];
		$cur    = -1;
		foreach ( (array) $s['items'] as $it ) {
			if ( 'group' === $it['type'] || $cur < 0 ) {
				$groups[] = [ 'title' => 'group' === $it['type'] ? $it['name'] : '', 'items' => [] ];
				$cur++;
				if ( 'group' === $it['type'] ) {
					continue;
				}
			}
			$groups[ $cur ]['items'][] = $it;
		}
		$def_icon = $this->icon_html( $s['loc_icon'] );

		$this->section_open( $s, 'mrn-loc' . ( 'left' === $s['cards_side'] ? ' mrn-loc--left' : '' ) );
		echo $this->background_html( $s ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-wrap"><div class="mrn-split mrn-loc__grid">';
		echo '<div class="mrn-loc__text">' . $this->eyebrow_html( $s ) . $this->title_html( $s ) . $this->text_html( $s ) . $this->buttons_html( $s ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div class="mrn-loc__cards">';
		foreach ( $groups as $g ) {
			echo '<div class="mrn-loc__card">';
			if ( '' !== $g['title'] ) {
				echo '<' . esc_attr( self::tag( $s['group_tag'], 'h3' ) ) . ' class="mrn-loc__title">' . esc_html( MRN::tokens( $g['title'], false ) ) . '</' . esc_attr( self::tag( $s['group_tag'], 'h3' ) ) . '>';
			}
			echo '<ul class="mrn-loc__chips">';
			foreach ( $g['items'] as $it ) {
				$icon  = $this->icon_html( isset( $it['icon'] ) ? $it['icon'] : [] );
				$icon  = $icon ? $icon : $def_icon;
				$inner = $icon . '<span>' . esc_html( $it['name'] ) . '</span>';
				$attrs = $this->link_attrs( 'url', isset( $it['link'] ) ? $it['link'] : [], 'mrn-chip elementor-repeater-item-' . $it['_id'] );
				echo '<li>' . ( $attrs ? '<a ' . $attrs . '>' . $inner . '</a>' : '<span class="mrn-chip elementor-repeater-item-' . esc_attr( $it['_id'] ) . '">' . $inner . '</span>' ) . '</li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			echo '</ul></div>';
		}
		echo '</div></div></div></section>';
	}
}
