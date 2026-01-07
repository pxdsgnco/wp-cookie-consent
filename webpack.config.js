/**
 * Consent Raven - Webpack Configuration
 *
 * Extends the default WordPress scripts webpack config.
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );

module.exports = {
	...defaultConfig,
	entry: {
		admin: path.resolve( __dirname, 'admin/src/index.js' ),
		'block-policy-table': path.resolve( __dirname, 'blocks/policy-table/index.js' ),
	},
	output: {
		path: path.resolve( __dirname, 'assets/js' ),
		filename: '[name].js',
	},
};
