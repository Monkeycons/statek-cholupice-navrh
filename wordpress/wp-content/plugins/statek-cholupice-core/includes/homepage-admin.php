<?php
/**
 * Nativní editace vybraných textů homepage bez ACF.
 *
 * @package StatekCholupiceCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function statek_cholupice_core_home_fields(): array {
	return array(
		'statek_home_hero_kicker'      => array( 'label' => 'Hero - malý nadpis', 'type' => 'text' ),
		'statek_home_hero_title'       => array( 'label' => 'Hero - hlavní nadpis', 'type' => 'textarea' ),
		'statek_home_hero_text'        => array( 'label' => 'Hero - podnadpis', 'type' => 'textarea' ),
		'statek_home_hero_primary'     => array( 'label' => 'Hero - první tlačítko', 'type' => 'text' ),
		'statek_home_hero_secondary'   => array( 'label' => 'Hero - druhé tlačítko', 'type' => 'text' ),
		'statek_home_faq_heading'      => array( 'label' => 'FAQ - nadpis', 'type' => 'text' ),
		'statek_home_faq_motto'        => array( 'label' => 'FAQ - podnadpis', 'type' => 'textarea' ),
		'statek_home_faq_intro_1'      => array( 'label' => 'FAQ - úvodní odstavec', 'type' => 'textarea' ),
		'statek_home_faq_intro_2'      => array( 'label' => 'FAQ - odstavec s kontaktem', 'type' => 'html' ),
		'statek_home_faq_items'        => array( 'label' => 'FAQ položky JSON', 'type' => 'json' ),
		'statek_home_contact_heading'  => array( 'label' => 'Kontakt - nadpis', 'type' => 'text' ),
		'statek_home_contact_motto'    => array( 'label' => 'Kontakt - podnadpis', 'type' => 'textarea' ),
		'statek_home_contact_text'     => array( 'label' => 'Kontakt - text', 'type' => 'html' ),
	);
}

function statek_cholupice_core_register_home_meta(): void {
	foreach ( statek_cholupice_core_home_fields() as $key => $field ) {
		register_post_meta(
			'page',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'sanitize_callback' => 'statek_cholupice_core_sanitize_home_meta',
				'auth_callback'     => static fn( $allowed = null, $meta_key = null, $post_id = null ) => current_user_can( 'edit_pages' ),
				'show_in_rest'      => false,
			)
		);
	}
}
add_action( 'init', 'statek_cholupice_core_register_home_meta' );

function statek_cholupice_core_sanitize_home_meta( string $value, string $key = '' ): string {
	$field = statek_cholupice_core_home_fields()[ $key ] ?? null;
	if ( $field && 'json' === $field['type'] ) {
		$decoded = json_decode( wp_unslash( $value ), true );
		if ( ! is_array( $decoded ) ) {
			return '';
		}
		return wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}
	if ( $field && 'html' === $field['type'] ) {
		return wp_kses_post( $value );
	}
	return sanitize_textarea_field( $value );
}

function statek_cholupice_core_home_metabox( string $post_type, ?WP_Post $post = null ): void {
	if ( 'page' !== $post_type || ! $post || (int) $post->ID !== (int) get_option( 'page_on_front' ) ) {
		return;
	}

	add_meta_box(
		'statek-cholupice-home-content',
		'Statek Cholupice - obsah homepage',
		'statek_cholupice_core_render_home_metabox',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'statek_cholupice_core_home_metabox', 10, 2 );

function statek_cholupice_core_render_home_metabox( WP_Post $post ): void {
	wp_nonce_field( 'statek_cholupice_home_save', 'statek_cholupice_home_nonce' );
	echo '<p>Vyplněná pole přepíšou výchozí texty schválené šablony. Prázdné pole ponechá původní fallback.</p>';
	echo '<table class="form-table" role="presentation"><tbody>';
	foreach ( statek_cholupice_core_home_fields() as $key => $field ) {
		$value = (string) get_post_meta( $post->ID, $key, true );
		echo '<tr><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] ) . '</label></th><td>';
		if ( 'text' === $field['type'] ) {
			echo '<input class="large-text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="text" value="' . esc_attr( $value ) . '">';
		} else {
			echo '<textarea class="large-text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="' . ( 'json' === $field['type'] ? '10' : '3' ) . '">' . esc_textarea( $value ) . '</textarea>';
			if ( 'json' === $field['type'] ) {
				echo '<p class="description">Formát: pole objektů s klíči "question" a "answer". Maximum 12 položek.</p>';
			}
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}

function statek_cholupice_core_save_home_metabox( int $post_id ): void {
	if ( ! isset( $_POST['statek_cholupice_home_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['statek_cholupice_home_nonce'] ) ), 'statek_cholupice_home_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( (int) $post_id !== (int) get_option( 'page_on_front' ) ) {
		return;
	}

	foreach ( statek_cholupice_core_home_fields() as $key => $field ) {
		if ( ! array_key_exists( $key, $_POST ) ) {
			continue;
		}
		$value = wp_unslash( $_POST[ $key ] );
		if ( 'json' === $field['type'] ) {
			$decoded = json_decode( (string) $value, true );
			if ( ! is_array( $decoded ) ) {
				delete_post_meta( $post_id, $key );
				continue;
			}
			$clean = array();
			foreach ( array_slice( $decoded, 0, 12 ) as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				$question = sanitize_text_field( $item['question'] ?? '' );
				$answer   = wp_kses_post( $item['answer'] ?? '' );
				if ( '' !== $question && '' !== $answer ) {
					$clean[] = array(
						'question' => $question,
						'answer'   => $answer,
					);
				}
			}
			if ( $clean ) {
				update_post_meta( $post_id, $key, wp_json_encode( $clean, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) );
			} else {
				delete_post_meta( $post_id, $key );
			}
			continue;
		}

		$value = 'html' === $field['type'] ? wp_kses_post( (string) $value ) : sanitize_textarea_field( (string) $value );
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_page', 'statek_cholupice_core_save_home_metabox' );
