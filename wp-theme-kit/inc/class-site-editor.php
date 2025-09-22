<?php
/**
 * Site Editor Class
 *
 * @package WpThemeKit
 */

namespace ThemeKit;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles logic for integrating with the Site Editor panel.
 */
class Site_Editor {

	/**
	 * Registers the filter to add settings to the block editor.
	 */
	public function __construct() {
		add_filter( 'block_editor_settings_all', array( $this, 'add_palette_data_to_editor_settings' ) );
	}

	/**
	 * Adds the theme's color palette data to the editor settings.
	 *
	 * This makes the palette data from theme.json available to JavaScript
	 * in the block editor via `wp.data.select('core/editor').getEditorSettings()`.
	 *
	 * @param array $editor_settings The existing editor settings.
	 * @return array The modified editor settings.
	 */
	public function add_palette_data_to_editor_settings( $editor_settings ) {
		// This check is for safety, though it should always exist in the editor context.
		if ( ! class_exists( 'WP_Theme_JSON_Resolver' ) ) {
			return $editor_settings;
		}

		$theme_data      = \WP_Theme_JSON_Resolver::get_theme_data();
		$settings        = $theme_data->get_settings();
		$palettes        = $settings['color']['palettes'] ?? null;
		$default_palette = $settings['color']['defaultPalette'] ?? null;

		// We only add our custom setting if the theme has opted-in by defining palettes.
		if ( ! empty( $palettes ) ) {
			$editor_settings['themeKit'] = array(
				'palettes'       => $palettes,
				'defaultPalette' => $default_palette,
			);
		}

		return $editor_settings;
	}
}
