import { __ } from '@wordpress/i18n';
import { registerBlockVariation } from '@wordpress/blocks';
import { starFilled } from '@wordpress/icons';
import { registerPlugin } from '@wordpress/plugins';
import { Fill, PanelBody, ColorPalette } from '@wordpress/components';
import { withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

import './editor.scss';

/**
 * A component to display the theme's color palettes inside the Global Styles sidebar.
 *
 * @param {object} props             Component props.
 * @param {object} props.themeKitSettings The palette settings from theme.json.
 */
const PalettesDisplay = ( { themeKitSettings } ) => {
	// Don't render anything if the settings are not present in theme.json.
	if ( ! themeKitSettings || ! themeKitSettings.palettes ) {
		return null;
	}

	const { palettes, defaultPalette } = themeKitSettings;

	return (
		<PanelBody title={ __( 'Color Palettes', 'wp-theme-kit' ) } initialOpen={ true }>
			<p className="components-panel__body-description">
				{ __( 'The following palettes are defined by the theme.', 'wp-theme-kit' ) }
			</p>
			{ Object.entries( palettes ).map( ( [ name, palette ] ) => (
				<div key={ name } style={ { marginBottom: '24px' } }>
					<h2
						className="components-panel__body-title"
						style={ { textTransform: 'capitalize', fontSize: '14px', marginBottom: '8px' } }
					>
						{ name }
						{ name === defaultPalette && ` (Default)` }
					</h2>
					<ColorPalette
						colors={ palette }
						disableCustomColors={ true }
						clearable={ false }
						// This is a display-only component for now.
						// The onChange handler is omitted intentionally.
					/>
				</div>
			) ) }
		</PanelBody>
	);
};

// Use withSelect to connect the component to the editor's data store.
const ComposedPalettesDisplay = compose(
	withSelect( ( select ) => ( {
		themeKitSettings: select( 'core/editor' ).getEditorSettings().themeKit,
	} ) )
)( PalettesDisplay );

/**
 * A wrapper component that renders our panel into the Global Styles sidebar slot.
 */
const GlobalStylesSidebarExtension = () => (
	<Fill name="core/edit-site/GlobalStyles/sidebar">
		<ComposedPalettesDisplay />
	</Fill>
);

// Register a "dummy" plugin. Its sole purpose is to render the SlotFill component
// which injects our panel into the right place in the editor's chrome.
registerPlugin( 'theme-kit-global-styles-panel', {
	render: GlobalStylesSidebarExtension,
	icon: null, // This plugin is not visible in the main sidebar.
} );

/**
 * Register a 'Theme Toggle' variation for the core/button block.
 */
registerBlockVariation( 'core/button', {
	name: 'theme-toggle',
	title: __( 'Theme Toggle', 'wp-theme-kit' ),
	icon: starFilled,
	attributes: {
		namespace: 'theme-kit/theme-toggle-button',
		text: __( 'Toggle Theme', 'wp-theme-kit' ),
	},
	isActive: ( { namespace } ) => namespace === 'theme-kit/theme-toggle-button',
} );
