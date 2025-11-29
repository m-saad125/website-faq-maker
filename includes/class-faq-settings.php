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
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_assets' ) );
	}

	/**
	 * Enqueue Settings Assets.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_settings_assets( $hook ) {
		if ( 'faq_group_page_wfm-settings' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'wfm-settings-script', WFM_PLUGIN_URL . 'assets/js/settings-script.js', array( 'jquery' ), WFM_VERSION, true );
		wp_enqueue_style( 'wfm-settings-style', WFM_PLUGIN_URL . 'assets/css/settings-style.css', array(), WFM_VERSION );
	}

	/**
	 * Add Settings Page.
	 */
	public function add_settings_page() {
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
		// General Settings
		register_setting( 'wfm_settings_group', 'wfm_active_provider', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_accordion_color', array( 'sanitize_callback' => 'sanitize_hex_color' ) );
		register_setting( 'wfm_settings_group', 'wfm_privacy_mode', array( 'sanitize_callback' => 'absint' ) );

		// OpenAI Settings
		register_setting( 'wfm_settings_group', 'wfm_openai_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_openai_model', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// Gemini Settings
		register_setting( 'wfm_settings_group', 'wfm_gemini_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_gemini_model', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// DeepSeek Settings
		register_setting( 'wfm_settings_group', 'wfm_deepseek_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_deepseek_model', array( 'sanitize_callback' => 'sanitize_text_field' ) );

		// OpenRouter Settings
		register_setting( 'wfm_settings_group', 'wfm_openrouter_api_key', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'wfm_settings_group', 'wfm_openrouter_model', array( 'sanitize_callback' => 'sanitize_text_field' ) );
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
			<?php settings_errors(); ?>

			<h2 class="nav-tab-wrapper">
				<a href="#wfm-tab-main" class="nav-tab nav-tab-active"><?php esc_html_e( 'Main', 'website-faq-maker' ); ?></a>
				<a href="#wfm-tab-openai" class="nav-tab"><?php esc_html_e( 'OpenAI', 'website-faq-maker' ); ?></a>
				<a href="#wfm-tab-gemini" class="nav-tab"><?php esc_html_e( 'Gemini', 'website-faq-maker' ); ?></a>
				<a href="#wfm-tab-deepseek" class="nav-tab"><?php esc_html_e( 'DeepSeek', 'website-faq-maker' ); ?></a>
				<a href="#wfm-tab-openrouter" class="nav-tab"><?php esc_html_e( 'OpenRouter', 'website-faq-maker' ); ?></a>
			</h2>

			<form action="options.php" method="post">
				<?php settings_fields( 'wfm_settings_group' ); ?>

				<!-- Main Tab -->
				<div id="wfm-tab-main" class="wfm-tab-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'Active Provider', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_active_provider_field(); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Accordion Color', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_accordion_color_field(); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Privacy Mode', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_privacy_mode_field(); ?>
							</td>
						</tr>
					</table>
				</div>

				<!-- OpenAI Tab -->
				<div id="wfm-tab-openai" class="wfm-tab-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'API Key', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_api_key_field( 'wfm_openai_api_key' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Model', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_openai_model_field(); ?>
							</td>
						</tr>
					</table>
				</div>

				<!-- Gemini Tab -->
				<div id="wfm-tab-gemini" class="wfm-tab-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'API Key', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_api_key_field( 'wfm_gemini_api_key' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Model', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_gemini_model_field(); ?>
							</td>
						</tr>
					</table>
				</div>

				<!-- DeepSeek Tab -->
				<div id="wfm-tab-deepseek" class="wfm-tab-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'API Key', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_api_key_field( 'wfm_deepseek_api_key' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Model', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_deepseek_model_field(); ?>
							</td>
						</tr>
					</table>
				</div>

				<!-- OpenRouter Tab -->
				<div id="wfm-tab-openrouter" class="wfm-tab-content">
					<table class="form-table">
						<tr>
							<th scope="row"><?php esc_html_e( 'API Key', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_api_key_field( 'wfm_openrouter_api_key' ); ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Model', 'website-faq-maker' ); ?></th>
							<td>
								<?php $this->render_openrouter_model_field(); ?>
							</td>
						</tr>
					</table>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render API Key Field with Eye Icon.
	 * 
	 * @param string $option_name The option name.
	 */
	public function render_api_key_field( $option_name ) {
		$value = get_option( $option_name );
		?>
		<div class="wfm-api-key-wrapper">
			<input type="password" name="<?php echo esc_attr( $option_name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="regular-text">
			<div class="wfm-eye-icon">
				<span class="dashicons dashicons-visibility"></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Active Provider Field.
	 */
	public function render_active_provider_field() {
		$value = get_option( 'wfm_active_provider', 'openai' );
		?>
		<fieldset>
			<label><input type="radio" name="wfm_active_provider" value="openai" <?php checked( $value, 'openai' ); ?>> <?php esc_html_e( 'OpenAI', 'website-faq-maker' ); ?></label><br>
			<label><input type="radio" name="wfm_active_provider" value="gemini" <?php checked( $value, 'gemini' ); ?>> <?php esc_html_e( 'Gemini', 'website-faq-maker' ); ?></label><br>
			<label><input type="radio" name="wfm_active_provider" value="deepseek" <?php checked( $value, 'deepseek' ); ?>> <?php esc_html_e( 'DeepSeek', 'website-faq-maker' ); ?></label><br>
			<label><input type="radio" name="wfm_active_provider" value="openrouter" <?php checked( $value, 'openrouter' ); ?>> <?php esc_html_e( 'OpenRouter', 'website-faq-maker' ); ?></label>
		</fieldset>
		<?php
	}

	/**
	 * Render OpenAI Model Field.
	 */
	public function render_openai_model_field() {
		$value = get_option( 'wfm_openai_model', 'gpt-3.5-turbo' );
		?>
		<select name="wfm_openai_model">
			<option value="gpt-3.5-turbo" <?php selected( $value, 'gpt-3.5-turbo' ); ?>>GPT-3.5 Turbo</option>
			<option value="gpt-4" <?php selected( $value, 'gpt-4' ); ?>>GPT-4</option>
			<option value="gpt-4-turbo" <?php selected( $value, 'gpt-4-turbo' ); ?>>GPT-4 Turbo</option>
			<option value="gpt-4o" <?php selected( $value, 'gpt-4o' ); ?>>GPT-4o</option>
		</select>
		<?php
	}

	/**
	 * Render Gemini Model Field.
	 */
	public function render_gemini_model_field() {
		$value = get_option( 'wfm_gemini_model', 'gemini-pro' );
		?>
		<select name="wfm_gemini_model">
			<option value="gemini-pro" <?php selected( $value, 'gemini-pro' ); ?>>Gemini Pro</option>
			<option value="gemini-1.5-flash" <?php selected( $value, 'gemini-1.5-flash' ); ?>>Gemini 1.5 Flash</option>
			<option value="gemini-1.5-pro" <?php selected( $value, 'gemini-1.5-pro' ); ?>>Gemini 1.5 Pro</option>
		</select>
		<?php
	}

	/**
	 * Render DeepSeek Model Field.
	 */
	public function render_deepseek_model_field() {
		$value = get_option( 'wfm_deepseek_model', 'deepseek-chat' );
		?>
		<select name="wfm_deepseek_model">
			<option value="deepseek-chat" <?php selected( $value, 'deepseek-chat' ); ?>>DeepSeek Chat</option>
			<option value="deepseek-coder" <?php selected( $value, 'deepseek-coder' ); ?>>DeepSeek Coder</option>
		</select>
		<?php
	}

	/**
	 * Render OpenRouter Model Field.
	 */
	public function render_openrouter_model_field() {
		$value = get_option( 'wfm_openrouter_model', 'openai/gpt-3.5-turbo' );
		?>
		<input type="text" name="wfm_openrouter_model" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="e.g. openai/gpt-3.5-turbo">
		<p class="description"><?php esc_html_e( 'Enter the model ID from OpenRouter.', 'website-faq-maker' ); ?></p>
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
			<?php esc_html_e( 'Disable saving generated FAQs', 'website-faq-maker' ); ?>
		</label>
		<?php
	}
}
