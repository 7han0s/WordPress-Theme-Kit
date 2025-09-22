# WordPress Theme Kit

A feature plugin to add advanced color palette management to the WordPress Site Editor, designed to feel like a native part of WordPress.

## Vision

The primary goal is to create a plugin that serves as a "feature plugin"—a well-developed, self-contained feature that could realistically be merged into WordPress Core in the future. It is built with a core-first mentality, using modern WordPress APIs like `theme.json` and the Interactivity API.

## Features

*   **Advanced Palette Management:** Define structured `light` and `dark` color palettes directly within your `theme.json`.
*   **Site Editor Integration:** A new "Color Palettes" panel in the Global Styles interface allows you to visually manage and override your theme's color palettes without writing code.
*   **Theme Toggle Block:** A "Theme Toggle" variation for the core Button block that allows users to switch between light and dark modes on the frontend.
*   **Performance-First:** The plugin is lightweight and includes features like FOUC (Flash of Unstyled Content) prevention for a smooth user experience.

## Getting Started

### Installation

1.  Clone or download this repository.
2.  Place the `wp-theme-kit` directory into your WordPress `wp-content/plugins/` directory.
3.  Activate the "WordPress Theme Kit" plugin through the WordPress admin dashboard.

### How to Use

This plugin is **block-theme-only**. To enable its features, you must opt-in by defining your color palettes in your theme's `theme.json` file.

Here is an example of the required structure:

```json
{
  "version": 2,
  "settings": {
    "color": {
      "defaultPalette": "light",
      "palettes": {
        "light": [
          { "name": "Primary", "slug": "primary", "color": "#0050B4" },
          { "name": "Background", "slug": "background", "color": "#FFFFFF" },
          { "name": "Text", "slug": "text", "color": "#111111" }
        ],
        "dark": [
          { "name": "Primary", "slug": "primary", "color": "#66A6FF" },
          { "name": "Background", "slug": "background", "color": "#121212" },
          { "name": "Text", "slug": "text", "color": "#E0E0E0" }
        ]
      }
    }
  }
}
```

Once defined, the plugin will automatically generate the necessary CSS variables and enable the "Color Palettes" panel in the Site Editor.
