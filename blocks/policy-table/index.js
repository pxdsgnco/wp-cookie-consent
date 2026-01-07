/**
 * Consent Raven - Cookie Policy Table Block
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import Edit from './edit';

/**
 * Register the block
 */
registerBlockType( 'consent-raven/policy-table', {
	title: __( 'Cookie Policy Table', 'consent-raven' ),
	description: __( 'Display a table of cookies configured in Consent Raven.', 'consent-raven' ),
	category: 'widgets',
	icon: 'shield',
	keywords: [
		__( 'cookie', 'consent-raven' ),
		__( 'policy', 'consent-raven' ),
		__( 'gdpr', 'consent-raven' ),
		__( 'consent', 'consent-raven' ),
	],
	attributes: {
		showCategory: {
			type: 'boolean',
			default: true,
		},
		showProvider: {
			type: 'boolean',
			default: true,
		},
		showExpiration: {
			type: 'boolean',
			default: true,
		},
		showHost: {
			type: 'boolean',
			default: false,
		},
		filterCategory: {
			type: 'string',
			default: '',
		},
	},
	edit: Edit,
	save: () => null, // Server-side rendering.
} );
