<?php
/**
 * FAQ Shortcode Class
 *
 * @package Website_FAQ_Maker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ Shortcode Class
 */
class WFM_FAQ_Shortcode {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'faq_group', array( $this, 'render_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
	}

	/**
	 * Register Scripts.
	 */
	public function register_scripts() {
		wp_register_style( 'wfm-faq-style', WFM_PLUGIN_URL . 'assets/css/faq-style.css', array(), WFM_VERSION );
		wp_register_script( 'wfm-faq-script', WFM_PLUGIN_URL . 'assets/js/faq-script.js', array( 'jquery' ), WFM_VERSION, true );
	}

	/**
	 * Render Shortcode.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Shortcode output.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'faq_group'
		);

		$post_id = intval( $atts['id'] );
		if ( ! $post_id || 'faq_group' !== get_post_type( $post_id ) ) {
			return '';
		}

		$faqs = get_post_meta( $post_id, '_wfm_faqs', true );
		if ( empty( $faqs ) || ! is_array( $faqs ) ) {
			return '';
		}

		// Enqueue scripts and styles only when shortcode is used.
		wp_enqueue_style( 'wfm-faq-style' );
		wp_enqueue_script( 'wfm-faq-script' );

		// Get custom color
		$color = get_option( 'wfm_accordion_color', '#0073aa' );
		$custom_css = "
			.wfm-accordion-item button[aria-expanded='true'] {
				border-left-color: {$color};
				color: {$color};
			}
			.wfm-accordion-item button:hover {
				color: {$color};
			}
		";
		wp_add_inline_style( 'wfm-faq-style', $custom_css );

		ob_start();
		?>
		<div class="wfm-faq-accordion">
			<?php foreach ( $faqs as $index => $faq ) : ?>
				<div class="wfm-accordion-item">
					<button id="wfm-accordion-btn-<?php echo esc_attr( $post_id . '-' . $index ); ?>" aria-expanded="false" aria-controls="wfm-accordion-content-<?php echo esc_attr( $post_id . '-' . $index ); ?>">
						<span class="wfm-accordion-title"><?php echo esc_html( $faq['question'] ); ?></span>
						<span class="wfm-accordion-icon" aria-hidden="true"></span>
					</button>
					<div id="wfm-accordion-content-<?php echo esc_attr( $post_id . '-' . $index ); ?>" class="wfm-accordion-content" hidden>
						<div class="wfm-accordion-inner">
							<?php echo wp_kses_post( wpautop( $faq['answer'] ) ); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
