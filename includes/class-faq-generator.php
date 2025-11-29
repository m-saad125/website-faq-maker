<?php
/**
 * FAQ Generator Class
 *
 * @package Website_FAQ_Maker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ Generator Class
 */
class WFM_FAQ_Generator {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_generator_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_wfm_generate_faqs', array( $this, 'ajax_generate_faqs' ) );
		add_action( 'wp_ajax_wfm_save_generated_faqs', array( $this, 'ajax_save_generated_faqs' ) );
	}

	/**
	 * Add Generator Page.
	 */
	public function add_generator_page() {
		add_submenu_page(
			'edit.php?post_type=faq_group',
			__( 'Generator', 'website-faq-maker' ),
			__( 'Generator', 'website-faq-maker' ),
			'manage_options',
			'wfm-generator',
			array( $this, 'render_generator_page' )
		);
	}

	/**
	 * Enqueue Scripts.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'faq_group_page_wfm-generator' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'wfm-admin-script', WFM_PLUGIN_URL . 'assets/js/admin-script.js', array( 'jquery' ), WFM_VERSION, true );
		wp_localize_script(
			'wfm-admin-script',
			'wfm_vars',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'wfm_generator_nonce' ),
			)
		);

		wp_enqueue_style( 'wfm-admin-style', WFM_PLUGIN_URL . 'assets/css/admin-style.css', array(), WFM_VERSION );
	}

	/**
	 * Render Generator Page.
	 */
	public function render_generator_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'FAQ Generator', 'website-faq-maker' ); ?></h1>
			
			<div class="wfm-generator-container">
				<div class="wfm-input-section">
					<h2><?php esc_html_e( '1. Select Source', 'website-faq-maker' ); ?></h2>
					
					<h3><?php esc_html_e( 'Pages', 'website-faq-maker' ); ?></h3>
					<div class="wfm-page-list" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin-bottom: 15px;">
						<?php
						$pages = get_pages();
						if ( $pages ) {
							foreach ( $pages as $page ) {
								echo '<label style="display:block;"><input type="checkbox" name="wfm_pages[]" value="' . esc_attr( $page->ID ) . '"> ' . esc_html( $page->post_title ) . '</label>';
							}
						} else {
							echo '<p>' . esc_html__( 'No pages found.', 'website-faq-maker' ) . '</p>';
						}
						?>
					</div>

					<h3><?php esc_html_e( 'Custom Content', 'website-faq-maker' ); ?></h3>
					<textarea id="wfm-custom-content" rows="5" style="width: 100%;" placeholder="<?php esc_attr_e( 'Enter custom text here...', 'website-faq-maker' ); ?>"></textarea>
				</div>

				<div class="wfm-settings-section">
					<h2><?php esc_html_e( '2. Configuration', 'website-faq-maker' ); ?></h2>
					<p>
						<label><?php esc_html_e( 'Number of FAQs:', 'website-faq-maker' ); ?></label>
						<input type="number" id="wfm-faq-count" value="5" min="1" max="50">
					</p>
					<p>
						<button type="button" id="wfm-generate-btn" class="button button-primary button-large"><?php esc_html_e( 'Generate FAQs', 'website-faq-maker' ); ?></button>
						<span class="spinner"></span>
					</p>
				</div>

				<div id="wfm-results-section" style="display: none; margin-top: 30px;">
					<h2><?php esc_html_e( '3. Preview & Save', 'website-faq-maker' ); ?></h2>
					<div id="wfm-generated-faqs"></div>
					
					<div class="wfm-save-section" style="margin-top: 20px;">
						<p>
							<label><?php esc_html_e( 'Group Title:', 'website-faq-maker' ); ?></label>
							<input type="text" id="wfm-group-title" placeholder="<?php esc_attr_e( 'e.g. My New FAQ Group', 'website-faq-maker' ); ?>" style="width: 100%; max-width: 400px;">
						</p>
						<?php if ( ! get_option( 'wfm_privacy_mode' ) ) : ?>
							<button type="button" id="wfm-save-btn" class="button button-primary"><?php esc_html_e( 'Save FAQ Group', 'website-faq-maker' ); ?></button>
						<?php else : ?>
							<p class="description"><?php esc_html_e( 'Saving is disabled in Privacy Mode.', 'website-faq-maker' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * AJAX Handler: Generate FAQs.
	 */
	public function ajax_generate_faqs() {
		check_ajax_referer( 'wfm_generator_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'website-faq-maker' ) );
		}

		$page_ids = isset( $_POST['page_ids'] ) ? array_map( 'intval', $_POST['page_ids'] ) : array();
		$custom_content = isset( $_POST['custom_content'] ) ? sanitize_textarea_field( $_POST['custom_content'] ) : '';
		$count = isset( $_POST['count'] ) ? intval( $_POST['count'] ) : 5;

		if ( empty( $page_ids ) && empty( $custom_content ) ) {
			wp_send_json_error( __( 'Please select at least one page or enter custom content.', 'website-faq-maker' ) );
		}

		// Build content string
		$content_to_process = '';
		
		if ( ! empty( $page_ids ) ) {
			foreach ( $page_ids as $page_id ) {
				$page = get_post( $page_id );
				if ( $page ) {
					$content_to_process .= "Page Title: " . $page->post_title . "\n";
					$content_to_process .= "Content: " . wp_strip_all_tags( $page->post_content ) . "\n\n";
				}
			}
		}

		if ( ! empty( $custom_content ) ) {
			$content_to_process .= "Custom Content: " . $custom_content . "\n";
		}

		// Truncate if too long (basic protection)
		if ( strlen( $content_to_process ) > 10000 ) {
			$content_to_process = substr( $content_to_process, 0, 10000 );
		}

		require_once WFM_PLUGIN_DIR . 'includes/class-faq-api.php';
		$result = WFM_FAQ_API::generate_faqs( $content_to_process, $count );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		}

		wp_send_json_success( $result );
	}

	/**
	 * AJAX Handler: Save Generated FAQs.
	 */
	public function ajax_save_generated_faqs() {
		check_ajax_referer( 'wfm_generator_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'website-faq-maker' ) );
		}

		if ( get_option( 'wfm_privacy_mode' ) ) {
			wp_send_json_error( __( 'Saving is disabled in Privacy Mode.', 'website-faq-maker' ) );
		}

		$title = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : __( 'New FAQ Group', 'website-faq-maker' );
		$faqs = isset( $_POST['faqs'] ) ? $_POST['faqs'] : array();

		if ( empty( $faqs ) ) {
			wp_send_json_error( __( 'No FAQs to save.', 'website-faq-maker' ) );
		}

		// Create Post
		$post_id = wp_insert_post( array(
			'post_title'  => $title,
			'post_type'   => 'faq_group',
			'post_status' => 'publish', // Or draft? Let's go with publish for now so it's usable immediately
		) );

		if ( is_wp_error( $post_id ) ) {
			wp_send_json_error( $post_id->get_error_message() );
		}

		// Sanitize and Save Meta
		$sanitized_faqs = array();
		foreach ( $faqs as $faq ) {
			if ( isset( $faq['question'] ) && isset( $faq['answer'] ) ) {
				$sanitized_faqs[] = array(
					'question' => sanitize_text_field( $faq['question'] ),
					'answer'   => sanitize_textarea_field( $faq['answer'] ),
				);
			}
		}

		update_post_meta( $post_id, '_wfm_faqs', $sanitized_faqs );

		wp_send_json_success( array( 'post_id' => $post_id, 'edit_url' => get_edit_post_link( $post_id, 'raw' ) ) );
	}
}
