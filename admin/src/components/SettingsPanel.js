/**
 * Consent Raven - Settings Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { ToggleControl, SelectControl, TextControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Settings Panel component
 *
 * @param {Object}   props                Component props.
 * @param {Object}   props.settings       Current settings.
 * @param {Function} props.updateSettings Function to update settings.
 * @param {Array}    props.pages          Available pages for selection.
 * @return {JSX.Element} Settings panel.
 */
const SettingsPanel = ( { settings, updateSettings, pages } ) => {
	const positionOptions = [
		{ value: 'bottom-right', label: __( 'Bottom Right (Floating)', 'consent-raven' ) },
		{ value: 'bottom-bar', label: __( 'Bottom Bar (Full Width)', 'consent-raven' ) },
		{ value: 'top-bar', label: __( 'Top Bar (Full Width)', 'consent-raven' ) },
		{ value: 'modal', label: __( 'Centered Modal', 'consent-raven' ) },
	];

	return (
		<div className="cr-settings-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'General Settings', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Configure the basic settings for your cookie consent banner.', 'consent-raven' ) }
				</p>
			</div>

			<div className="cr-form-group">
				<ToggleControl
					label={ __( 'Enable Cookie Banner', 'consent-raven' ) }
					help={ __( 'Display the cookie consent banner on your website.', 'consent-raven' ) }
					checked={ settings.enabled ?? true }
					onChange={ ( enabled ) => updateSettings( { enabled } ) }
				/>
			</div>

			<div className="cr-form-group">
				<SelectControl
					label={ __( 'Banner Position', 'consent-raven' ) }
					help={ __( 'Choose where the cookie banner appears on your website.', 'consent-raven' ) }
					value={ settings.position || 'bottom-right' }
					options={ positionOptions }
					onChange={ ( position ) => updateSettings( { position } ) }
				/>
			</div>

			<div className="cr-form-group">
				<SelectControl
					label={ __( 'Cookie Policy Page', 'consent-raven' ) }
					help={ __( 'Select the page containing your cookie policy.', 'consent-raven' ) }
					value={ settings.policy_page_id || 0 }
					options={ pages.map( ( page ) => ( {
						value: page.value,
						label: page.label,
					} ) ) }
					onChange={ ( policy_page_id ) =>
						updateSettings( { policy_page_id: parseInt( policy_page_id, 10 ) } )
					}
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Consent Version', 'consent-raven' ) }
					help={ __( 'Increment this to re-prompt users for consent when your cookie policy changes.', 'consent-raven' ) }
					value={ settings.consent_version || '1.0' }
					onChange={ ( consent_version ) => updateSettings( { consent_version } ) }
				/>
			</div>
		</div>
	);
};

export default SettingsPanel;
