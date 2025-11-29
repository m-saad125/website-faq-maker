<?php
/**
 * FAQ Settings Class
 *
 * @package Website_FAQ_Maker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ Settings Class
 */
class WFM_FAQ_Settings {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add Settings Page.
	 */
	public function add_settings_page() {
		// We'll add this as a submenu to the CPT menu.
		add_submenu_page(
			'edit.php?post_type=faq_group',
			__( 'Settings', 'website-faq-maker' ),
			__( 'Settings', 'website-faq-maker' ),
			'manage_options',
			'wfm-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Settings.
	 */
	public function register_settings() {
		// API Settings Section
		register_setting( 'wfm_settings_group', 'wfm_openai_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_gemini_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_active_provider', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Styling Settings Section
		register_setting( 'wfm_settings_group', 'wfm_accordion_color', array( 'sanitize_callback' => 'sanitize_hex_color' ) );
		
		// Privacy Settings Section
		register_setting( 'wfm_settings_group', 'wfm_privacy_mode', array( 'sanitize_callback' => 'absint' ) );

		// Sections
		add_settings_section(
			'wfm_api_section',
			__( 'API Settings', 'website-faq-maker' ),
			array( $this, 'render_api_section' ),
			'wfm-settings'
		);

		add_settings_section(
			'wfm_styling_section',
			__( 'Styling Settings', 'website-faq-maker' ),
			array( $this, 'render_styling_section' ),
			'wfm-settings'
		);

		add_settings_section(
			'wfm_privacy_section',
			__( 'Privacy Options', 'website-faq-maker' ),
			array( $this, 'render_privacy_section' ),
			'wfm-settings'
		);

		// Fields
		add_settings_field(
			'wfm_openai_api_key',
			__( 'OpenAI API Key', 'website-faq-maker' ),
			array( $this, 'render_openai_api_key_field' ),
			'wfm-settings',
			'wfm_api_section'
		);

		add_settings_field(
			'wfm_gemini_api_key',
			__( 'Gemini API Key', 'website-faq-maker' ),
			array( $this, 'render_gemini_api_key_field' ),
			'wfm-settings',
			'wfm_api_section'
		);

		add_settings_field(
			'wfm_active_provider',
			__( 'Active Provider', 'website-faq-maker' ),
			array( $this, 'render_active_provider_field' ),
			'wfm-settings',
			'wfm_api_section'
		);

		add_settings_field(
			'wfm_accordion_color',
			__( 'Accordion Color', 'website-faq-maker' ),
			array( $this, 'render_accordion_color_field' ),
			'wfm-settings',
			'wfm_styling_section'
		);
		
		add_settings_field(
			'wfm_privacy_mode',
			__( 'Privacy Mode', 'website-faq-maker' ),
			array( $this, 'render_privacy_mode_field' ),
			'wfm-settings',
			'wfm_privacy_section'
		);
	}

	/**
	 * Render Settings Page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'wfm_settings_group' );
				do_settings_sections( 'wfm-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render API Section.
	 */
	public function render_api_section() {
		echo '<p>' . esc_html__( 'Enter your API keys for the AI providers.', 'website-faq-maker' ) . '</p>';
	}

	/**
	 * Render Styling Section.
	 */
	public function render_styling_section() {
		echo '<p>' . esc_html__( 'Customize the look of your FAQs.', 'website-faq-maker' ) . '</p>';
	}

	/**
	 * Render Privacy Section.
	 */
	public function render_privacy_section() {
		echo '<p>' . esc_html__( 'Manage privacy options.', 'website-faq-maker' ) . '</p>';
	}

	/**
	 * Render OpenAI API Key Field.
	 */
	public function render_openai_api_key_field() {
		$value = get_option( 'wfm_openai_api_key' );
		echo '<input type="password" name="wfm_openai_api_key" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	/**
	 * Render Gemini API Key Field.
	 */
	public function render_gemini_api_key_field() {
		$value = get_option( 'wfm_gemini_api_key' );
		echo '<input type="password" name="wfm_gemini_api_key" value="' . esc_attr( $value ) . '" class="regular-text">';
	}

	/**
	 * Render Active Provider Field.
	 */
	public function render_active_provider_field() {
		$value = get_option( 'wfm_active_provider', 'openai' );
		?>
		<fieldset>
			<label>
				<input type="radio" name="wfm_active_provider" value="openai" <?php checked( $value, 'openai' ); ?>>
				<?php esc_html_e( 'OpenAI', 'website-faq-maker' ); ?>
			</label>
			<br>
			<label>
				<input type="radio" name="wfm_active_provider" value="gemini" <?php checked( $value, 'gemini' ); ?>>
				<?php esc_html_e( 'Gemini', 'website-faq-maker' ); ?>
			</label>
		</fieldset>
		<?php
	}

	/**
	 * Render Accordion Color Field.
	 */
	public function render_accordion_color_field() {
		$value = get_option( 'wfm_accordion_color', '#0073aa' );
		echo '<input type="color" name="wfm_accordion_color" value="' . esc_attr( $value ) . '">';
	}
	
	/**
	 * Render Privacy Mode Field.
	 */
	public function render_privacy_mode_field() {
		$value = get_option( 'wfm_privacy_mode' );
		?>
		<label>
			<input type="checkbox" name="wfm_privacy_mode" value="1" <?php checked( $value, 1 ); ?>>
			<?php esc_html_e( 'Disable saving generated FAQs (Privacy Mode)', 'website-faq-maker' ); ?>
		</label>
		<p class="description"><?php esc_html_e( 'If enabled, you can generate and preview FAQs but cannot save them to the database.', 'website-faq-maker' ); ?></p>
		<?php
	}
}
