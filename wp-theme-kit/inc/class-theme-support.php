<?php
/**
 * Theme Support Class
 *
 * @package WpThemeKit
 */

namespace ThemeKit;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles theme.json parsing and CSS variable generation.
 */
class Theme_Support {

	/**
	 * The theme's JSON data.
	 *
	 * @var \WP_Theme_JSON
	 */
	private $theme_data;

	/**
	 * Registers hooks to load theme data and print styles.
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'load_theme_data' ) );
		add_action( 'wp_head', array( $this, 'print_palette_styles' ), 1 );
	}

	/**
	 * Loads and parses the theme.json data from the active theme.
	 */
	public function load_theme_data() {
		if ( ! class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			return;
		}
		$this->theme_data = \WP_Theme_JSON_Resolver::get_theme_data();
	}

	/**
	 * Generates and prints the dynamic CSS for color palettes.
	 */
	public function print_palette_styles() {
		$settings = $this->theme_data ? $this->theme_data->get_settings() : null;
		$palettes = $settings['color']['palettes'] ?? null;

		if ( empty( $palettes ) || ! is_array( $palettes ) ) {
			return;
		}

		$default_slug = $settings['color']['defaultPalette'] ?? 'light';
		$other_slugs  = array_diff( array_keys( $palettes ), array( $default_slug ) );
		$css          = '';

		// Generate CSS for the default palette.
		if ( isset( $palettes[ $default_slug ] ) ) {
			$default_css = $this->generate_palette_css( $palettes[ $default_slug ] );
			if ( ! empty( $default_css ) ) {
				$css .= sprintf( ':root { color-scheme: %s; %s }', esc_attr( $default_slug ), $default_css );
			}
		}

		// Generate CSS for other palettes.
		foreach ( $other_slugs as $slug ) {
			if ( isset( $palettes[ $slug ] ) ) {
				$palette_css = $this->generate_palette_css( $palettes[ $slug ] );
				if ( ! empty( $palette_css ) ) {
					$css .= sprintf( 'html[data-theme="%s"] { color-scheme: %s; %s }', esc_attr( $slug ), esc_attr( $slug ), $palette_css );
				}
			}
		}

		if ( ! empty( $css ) ) {
			printf( '<style id="wp-theme-kit-palette-styles">%s</style>', $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Generates a string of CSS variables from a palette array.
	 *
	 * @param array $palette The color palette array.
	 * @return string The generated CSS string.
	 */
	private function generate_palette_css( $palette ) {
		$css = '';
		if ( ! is_array( $palette ) ) {
			return $css;
		}

		foreach ( $palette as $color ) {
			if ( isset( $color['slug'] ) && isset( $color['color'] ) ) {
				$slug       = sanitize_key( $color['slug'] );
				$color_val  = esc_attr( $color['color'] );
				$is_valid_color = preg_match( '/^#([a-fA-F0-9]{3}){1,2}$|var\(--wp--preset--color--[a-zA-Z0-9-]+?\)|rgb\s*\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*\)|rgba\s*\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*[\d\.]+\s*\)/', $color_val );

				if ( $is_valid_color ) {
					$css .= sprintf( '--wp--preset--color--%s: %s;', $slug, $color_val );
				}
			}
		}
		return $css;
	}
}
