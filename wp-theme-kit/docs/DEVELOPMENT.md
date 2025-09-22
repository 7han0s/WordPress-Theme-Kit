## WordPress Theme Kit

### I. Vision & Guiding Principles

The primary goal is to create a plugin that feels like a native part of the WordPress Site Editor. It should serve as a "feature plugin"—a well-developed, self-contained feature that could realistically be merged into WordPress Core in the future.

1.  **Core-First:** Utilize WordPress APIs (`theme.json`, Interactivity API, Block Variations) exclusively. Avoid third-party libraries.
2.  **Theme-Agnostic & User-Centric:** The logic must not assume a default theme style (light or dark). The user and theme author are in complete control.
3.  **Seamless Integration:** All user-facing controls for theme authors and site builders will live within the Global Styles interface of the Site Editor.
4.  **Performance & Accessibility:** These are non-negotiable. The plugin must be fast, lightweight, and fully accessible.

---

### II. Core Features

1.  **Advanced Palette Management via `theme.json`:**
    The plugin will introduce a new, structured way to define color palettes within `theme.json`. This makes the "color remapping" feature a declarative part of the theme development process.

2.  **Site Editor Styles Integration:**
    A new panel, "Color Palettes," will be added to the Global Styles interface. This UI will allow site builders to:
    *   Visually see the defined `light` and `dark` palettes from `theme.json`.
    *   **Override and customize these palettes directly in the editor**, effectively providing the "color remapping" feature in a fully integrated, non-destructive way.
    *   Set the theme's default palette (`light` or `dark`).

3.  **"Theme Toggle" Block Variation:**
    The plugin will *not* register a new block. Instead, it will register a **Block Variation** for the core `core/button` block. This allows a theme toggle to be added anywhere a button can, inheriting all of the button block's existing features (styling, sizing, etc.).

---

### III. Plugin Architecture & Directory Structure

Here is a modern, scalable structure for the plugin.

```
/wp-theme-kit/
├── theme-kit.php # Main plugin file: entry point, hooks.
├── -mode.php  # Main plugin file: entry point, hooks.
├── build/                         # Compiled JS/CSS assets (from wp-scripts).
├── languages/                     # .pot file for translations.
├── src/                           # Source files.
│   ├── index.js                   # Main entry for block variations & editor integration.
│   ├── view.js                    # Frontend Interactivity API logic.
│   ├── store.js                   # Interactivity API store definition.
│   ├── editor.scss                # Styles for the Site Editor UI.
│   └── style.scss                 # Shared styles for editor and frontend.
└── inc/                           # PHP include files.
    ├── class-assets-loader.php    # Handles script/style registration and enqueuing.
    ├── class-site-editor.php      # Logic for integrating with the Site Editor panel.
    ├── class-rest-api.php         # Registers REST endpoint for saving user meta.
    └── class-theme-support.php    # Handles `theme.json` parsing and outputting CSS variables.
```

---

### IV. Technical Implementation Deep Dive

#### 1. `theme.json` Schema Enhancement

Theme developers will opt-in by defining palettes in `theme.json`. This structure is intuitive and flexible.

**`theme.json` example:**
```json
{
  "version": 2,
  "settings": {
    "color": {
      "defaultPalette": "light", // or "dark"
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

The `class-theme-support.php` will parse this and generate CSS Custom Properties scoped to the active theme.

```css
/* If defaultPalette is 'light' */
:root {
  color-scheme: light;
  --wp--preset--color--primary: #0050B4;
  --wp--preset--color--background: #FFFFFF;
  /* ...etc */
}
html[data-theme="dark"] {
  color-scheme: dark;
  --wp--preset--color--primary: #66A6FF;
  --wp--preset--color--background: #121212;
  /* ...etc */
}

/* If defaultPalette is 'dark' */
:root {
  color-scheme: dark;
  --wp--preset--color--primary: #66A6FF;
  /* ...etc */
}
html[data-theme="light"] {
  color-scheme: light;
  --wp--preset--color--primary: #0050B4;
  /* ...etc */
}
```

#### 2. Site Editor (Styles Panel) Integration

`class-site-editor.php` and `src/index.js` will work together:
1.  **PHP:** Use the `block_editor_settings_all` filter to pass the `settings.color.palettes` data from the server-side `theme.json` to the editor's JavaScript environment.
2.  **JavaScript:** Create a custom React component for the "Color Palettes" panel. This component will:
    *   Read the theme data using `wp.data.select('core/editor').getEditorSettings()`.
    *   Display the light and dark palettes using standard `<ColorPalette>` components.
    *   When a color is changed, it will update the settings in-memory using `wp.data.dispatch('core/editor').updateEditorSettings()`. The Site Editor's save mechanism handles the rest.

This makes the "color remapping" a visual `theme.json` editor, which is the ultimate "core-ready" approach.

#### 3. The Block Variation

In `src/index.js`, we'll register the variation:

```javascript
import { registerBlockVariation } from '@wordpress/blocks';
import { starFilled } from '@wordpress/icons';

registerBlockVariation( 'core/button', {
    name: 'theme-toggle',
    title: 'Theme Toggle',
    icon: starFilled,
    attributes: {
        namespace: 'theme-kit/theme-toggle-button', // For Interactivity API
    },
    isActive: ( { namespace } ) => namespace === 'theme-kit/theme-toggle-button',
} );
```

#### 4. The Interactivity API (`store.js` & `view.js`)

The logic will be clean and state-driven.

**`store.js`:**
```javascript
import { store, getContext } from '@wordpress/interactivity';

store( 'theme-kit/light-dark', {
    state: {
        // Populated from the server with the user's preference
        userPreference: 'auto',
    },
    actions: {
        toggleScheme() {
            const { userPreference } = getContext();
            // Logic to cycle through auto -> light -> dark -> auto
            const newPreference = //...
            actions.setScheme( newPreference );
        },
        setScheme( scheme ) {
            const context = getContext();
            context.userPreference = scheme;
            document.documentElement.setAttribute( 'data-theme', scheme );
            // Persist the setting
            try {
                localStorage.setItem( 'theme-preference', scheme );
                // Optional: call REST API to save for logged-in users
            } catch (e) {}
        },
    },
} );
```

#### 5. Server-Side Logic & FOUC Prevention

This is critical for a professional experience.

1.  **`class-assets-loader.php`**: An inline script will be injected into the `<head>` of the site using the `wp_head` action. This script is minimal and runs before any rendering.
    ```javascript
    // Injected into <head>
    (function() {
      const pref = localStorage.getItem('theme-preference') || 'auto';
      const isDark = pref === 'dark' || (pref === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
      document.documentElement.setAttribute('data-theme', isDark ? 'dark' : 'light');
    })();
    ```
2.  **Logged-in Users (`class-rest-api.php`)**: For logged-in users, the preference will be saved to `user_meta` via a REST API endpoint. The inline script will check for a localized user setting before falling back to `localStorage`.

---

### V. Compatibility & Integration Strategy

*   **Create Block Theme Plugin:** By exclusively using `theme.json`, our plugin is inherently compatible. Any theme scaffolded with this tool can immediately use our palette structure.
*   **Standard Plugins:** The plugin has a minimal footprint. It only becomes active if the theme supports it via `theme.json`. It doesn't interfere with other plugins' functionality. It correctly uses the `wp--preset--color--{slug}` CSS variables, ensuring compatibility with core blocks.

---

### VI. Development, Debugging, and Best Practices

*   **Version Control:** A `main` branch for stable releases and feature branches for development. Tags will be used for versioning.
*   **CI/CD:** GitHub Actions will be used to lint PHP (`phpcs`), lint JS (`eslint`), and run automated tests.
*   **Testing:**
    *   **PHP:** PHPUnit for unit testing the PHP classes.
    *   **JS:** Jest for unit testing any complex JavaScript logic.
    *   **E2E:** Playwright or Cypress for end-to-end tests, ensuring the Site Editor integration and the front-end toggle work as expected.
*   **Documentation:** Comprehensive inline documentation (PHPDoc, JSDoc) and a detailed `README.md` explaining how theme authors can integrate the feature.
*   **Security:** All REST API endpoints will use nonces and capability checks. All output will be properly escaped.

---

### VII. Other Considerations

*   **Accessibility:** The toggle button will use `aria-pressed` or a similar appropriate ARIA attribute to announce its state to screen readers.
*   **Internationalization (i18n):** All strings in both PHP and JavaScript will be translatable using the standard WordPress functions (`__`, `_x`, etc.).
*   **Non-Block Themes:** This plugin is designed for the future of WordPress and is therefore **block-theme-only**. This should be clearly stated in the documentation. Creating a backward-compatible version for classic themes would be a separate, more complex project.
*   **User Experience (UX):** The toggle's default state will be `auto` to respect the user's OS preference, providing the best initial experience. The options in the Site Editor will be clear and intuitive, with descriptions explaining what each palette does.
