import { store, getContext, actions as interactivityActions } from '@wordpress/interactivity';

store( 'theme-kit/light-dark', {
	state: {
		/**
		 * The user's preferred color scheme.
		 * Can be 'auto', 'light', or 'dark'.
		 * The initial value is derived from localStorage by the FOUC prevention script,
		 * so this default is primarily for clarity.
		 */
		userPreference: 'auto',
	},
	actions: {
		/**
		 * Toggles the color scheme preference through the sequence: auto -> light -> dark -> auto.
		 */
		toggleScheme() {
			const context = getContext();
			const preferences = [ 'auto', 'light', 'dark' ];
			const currentIndex = preferences.indexOf( context.userPreference );
			const nextIndex = ( currentIndex + 1 ) % preferences.length;
			const newPreference = preferences[ nextIndex ];

			// Call the `setScheme` action to apply the new preference.
			interactivityActions[ 'theme-kit/light-dark' ].setScheme( newPreference );
		},

		/**
		 * Sets the color scheme to a specific value, applies it, and persists it.
		 *
		 * @param {string} scheme The new color scheme ('auto', 'light', or 'dark').
		 */
		setScheme( scheme ) {
			const context = getContext();
			context.userPreference = scheme;

			// Determine the actual theme to apply based on the preference.
			const themeToApply =
				scheme === 'auto'
					? window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light'
					: scheme;

			document.documentElement.setAttribute( 'data-theme', themeToApply );

			// Persist the setting to localStorage for future page loads.
			try {
				localStorage.setItem( 'theme-preference', scheme );
				// In a future step, we will call a REST API here to save the preference
				// to user meta for logged-in users.
			} catch ( e ) {
				// localStorage can be unavailable in some browser configurations (e.g., private mode).
				console.error( 'Could not save theme preference to localStorage.', e );
			}
		},
	},
} );
