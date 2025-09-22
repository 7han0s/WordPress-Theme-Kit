/**
 * WordPress dependencies
 */
import { store, getContext, select, dispatch } from '@wordpress/interactivity';

// Import the store configuration to ensure it's registered.
import './store';

describe( 'Theme Kit Interactivity Store', () => {
	it( 'should correctly toggle the color scheme preference', () => {
		const storeKey = 'theme-kit/light-dark';

		// 1. Check initial state
		let context = getContext( storeKey );
		expect( context.userPreference ).toBe( 'auto' );

		// 2. Toggle from 'auto' to 'light'
		dispatch( storeKey, 'toggleScheme' );
		context = getContext( storeKey );
		expect( context.userPreference ).toBe( 'light' );

		// 3. Toggle from 'light' to 'dark'
		dispatch( storeKey, 'toggleScheme' );
		context = getContext( storeKey );
		expect( context.userPreference ).toBe( 'dark' );

		// 4. Toggle from 'dark' back to 'auto'
		dispatch( storeKey, 'toggleScheme' );
		context = getContext( storeKey );
		expect( context.userPreference ).toBe( 'auto' );
	} );
} );
