<?php
/**
 * FAQ API Service Class
 *
 * @package Website_FAQ_Maker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ API Service Class
 */
class WFM_FAQ_API {

	/**
	 * Generate FAQs using the active provider.
	 *
	 * @param string $content The content to generate FAQs from.
	 * @param int    $count   Number of FAQs to generate.
	 * @return array|WP_Error Array of FAQs or WP_Error on failure.
	 */
	public static function generate_faqs( $content, $count = 5 ) {
		$provider = get_option( 'wfm_active_provider', 'openai' );

		if ( 'openai' === $provider ) {
			return self::generate_openai( $content, $count );
		} elseif ( 'gemini' === $provider ) {
			return self::generate_gemini( $content, $count );
		}

		return new WP_Error( 'invalid_provider', __( 'Invalid API provider selected.', 'website-faq-maker' ) );
	}

	/**
	 * Generate FAQs using OpenAI.
	 *
	 * @param string $content The content.
	 * @param int    $count   Number of FAQs.
	 * @return array|WP_Error
	 */
	private static function generate_openai( $content, $count ) {
		$api_key = get_option( 'wfm_openai_api_key' );
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'OpenAI API Key is missing.', 'website-faq-maker' ) );
		}

		$url = 'https://api.openai.com/v1/chat/completions';

		$prompt = sprintf(
			'Generate %d FAQs (questions and answers) based on the following content. Return the result as a JSON array of objects, where each object has "question" and "answer" keys. Do not include any markdown formatting or extra text, just the raw JSON array. Content: %s',
			$count,
			$content
		);

		$body = array(
			'model'       => 'gpt-3.5-turbo', // Or gpt-4 if preferred/available
			'messages'    => array(
				array(
					'role'    => 'system',
					'content' => 'You are a helpful assistant that generates FAQs from text. You output valid JSON.',
				),
				array(
					'role'    => 'user',
					'content' => $prompt,
				),
			),
			'temperature' => 0.7,
		);

		$args = array(
			'body'    => wp_json_encode( $body ),
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'timeout' => 60,
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$body = wp_remote_retrieve_body( $response );
			return new WP_Error( 'api_error', 'OpenAI Error: ' . $response_code . ' - ' . $body );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['choices'][0]['message']['content'] ) ) {
			$content = $data['choices'][0]['message']['content'];
			// Attempt to parse JSON from the content
			$faqs = json_decode( $content, true );
			if ( is_array( $faqs ) ) {
				return $faqs;
			}
		}

		return new WP_Error( 'parsing_error', __( 'Failed to parse OpenAI response.', 'website-faq-maker' ) );
	}

	/**
	 * Generate FAQs using Gemini.
	 *
	 * @param string $content The content.
	 * @param int    $count   Number of FAQs.
	 * @return array|WP_Error
	 */
	private static function generate_gemini( $content, $count ) {
		$api_key = get_option( 'wfm_gemini_api_key' );
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_api_key', __( 'Gemini API Key is missing.', 'website-faq-maker' ) );
		}

		$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;

		$prompt = sprintf(
			'Generate %d FAQs (questions and answers) based on the following content. Return the result as a JSON array of objects, where each object has "question" and "answer" keys. Do not include any markdown formatting or extra text, just the raw JSON array. Content: %s',
			$count,
			$content
		);

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array(
							'text' => $prompt,
						),
					),
				),
			),
		);

		$args = array(
			'body'    => wp_json_encode( $body ),
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'timeout' => 60,
		);

		$response = wp_remote_post( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			$body = wp_remote_retrieve_body( $response );
			return new WP_Error( 'api_error', 'Gemini Error: ' . $response_code . ' - ' . $body );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( isset( $data['candidates'][0]['content']['parts'][0]['text'] ) ) {
			$content = $data['candidates'][0]['content']['parts'][0]['text'];
			// Cleanup markdown code blocks if present
			$content = str_replace( array( '```json', '```' ), '', $content );
			
			$faqs = json_decode( $content, true );
			if ( is_array( $faqs ) ) {
				return $faqs;
			}
		}

		return new WP_Error( 'parsing_error', __( 'Failed to parse Gemini response.', 'website-faq-maker' ) );
	}
}
