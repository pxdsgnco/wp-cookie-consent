/**
 * Consent Raven - Admin React App Entry Point
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { createRoot, render } from '@wordpress/element';
import App from './App';

// Get the root element
const rootElement = document.getElementById( 'consent-raven-admin' );

if ( rootElement ) {
	// Get initial tab from data attribute
	const initialTab = rootElement.getAttribute( 'data-tab' ) || 'settings';

	// Use createRoot for React 18+ (WordPress 6.2+), fallback to render
	if ( createRoot ) {
		const root = createRoot( rootElement );
		root.render( <App initialTab={ initialTab } /> );
	} else {
		render( <App initialTab={ initialTab } />, rootElement );
	}
}
