/**
 * Consent Raven - Appearance Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { SelectControl, TextControl, ColorPicker, BaseControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Appearance Panel component
 *
 * @param {Object}   props                  Component props.
 * @param {Object}   props.appearance       Current appearance settings.
 * @param {Function} props.updateAppearance Function to update appearance.
 * @return {JSX.Element} Appearance panel.
 */
const AppearancePanel = ( { appearance, updateAppearance } ) => {
	const themeOptions = [
		{ value: 'dark', label: __( 'Dark', 'consent-raven' ) },
		{ value: 'light', label: __( 'Light', 'consent-raven' ) },
		{ value: 'custom', label: __( 'Custom', 'consent-raven' ) },
	];

	const themePresets = {
		dark: {
			background_color: '#1a1a1a',
			text_color: '#ffffff',
			secondary_color: '#b3b3b3',
			button_bg: '#ffffff',
			button_text: '#1a1a1a',
		},
		light: {
			background_color: '#ffffff',
			text_color: '#1a1a1a',
			secondary_color: '#666666',
			button_bg: '#1a1a1a',
			button_text: '#ffffff',
		},
	};

	/**
	 * Handle theme change
	 *
	 * @param {string} theme Selected theme.
	 */
	const handleThemeChange = ( theme ) => {
		if ( theme !== 'custom' && themePresets[ theme ] ) {
			updateAppearance( {
				theme,
				...themePresets[ theme ],
			} );
		} else {
			updateAppearance( { theme } );
		}
	};

	/**
	 * Handle color change
	 *
	 * @param {string} key   Color key.
	 * @param {string} color Color value.
	 */
	const handleColorChange = ( key, color ) => {
		updateAppearance( {
			[ key ]: color,
			theme: 'custom',
		} );
	};

	return (
		<div className="cr-appearance-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Appearance Settings', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Customize the look and feel of your cookie consent banner.', 'consent-raven' ) }
				</p>
			</div>

			<div className="cr-form-group">
				<SelectControl
					label={ __( 'Theme', 'consent-raven' ) }
					help={ __( 'Choose a preset theme or customize colors manually.', 'consent-raven' ) }
					value={ appearance.theme || 'dark' }
					options={ themeOptions }
					onChange={ handleThemeChange }
				/>
			</div>

			<div className="cr-color-grid">
				<div className="cr-color-item">
					<BaseControl
						label={ __( 'Background Color', 'consent-raven' ) }
						id="cr-bg-color"
					>
						<ColorPicker
							color={ appearance.background_color || '#1a1a1a' }
							onChange={ ( color ) => handleColorChange( 'background_color', color ) }
							enableAlpha={ false }
						/>
					</BaseControl>
				</div>

				<div className="cr-color-item">
					<BaseControl
						label={ __( 'Text Color', 'consent-raven' ) }
						id="cr-text-color"
					>
						<ColorPicker
							color={ appearance.text_color || '#ffffff' }
							onChange={ ( color ) => handleColorChange( 'text_color', color ) }
							enableAlpha={ false }
						/>
					</BaseControl>
				</div>

				<div className="cr-color-item">
					<BaseControl
						label={ __( 'Secondary Text Color', 'consent-raven' ) }
						id="cr-secondary-color"
					>
						<ColorPicker
							color={ appearance.secondary_color || '#b3b3b3' }
							onChange={ ( color ) => handleColorChange( 'secondary_color', color ) }
							enableAlpha={ false }
						/>
					</BaseControl>
				</div>

				<div className="cr-color-item">
					<BaseControl
						label={ __( 'Button Background', 'consent-raven' ) }
						id="cr-button-bg"
					>
						<ColorPicker
							color={ appearance.button_bg || '#ffffff' }
							onChange={ ( color ) => handleColorChange( 'button_bg', color ) }
							enableAlpha={ false }
						/>
					</BaseControl>
				</div>

				<div className="cr-color-item">
					<BaseControl
						label={ __( 'Button Text Color', 'consent-raven' ) }
						id="cr-button-text"
					>
						<ColorPicker
							color={ appearance.button_text || '#1a1a1a' }
							onChange={ ( color ) => handleColorChange( 'button_text', color ) }
							enableAlpha={ false }
						/>
					</BaseControl>
				</div>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Button Border Radius', 'consent-raven' ) }
					help={ __( 'CSS value for button corner rounding (e.g., 8px, 0.5rem).', 'consent-raven' ) }
					value={ appearance.button_radius || '8px' }
					onChange={ ( button_radius ) => updateAppearance( { button_radius } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Dialog Border Radius', 'consent-raven' ) }
					help={ __( 'CSS value for dialog corner rounding (e.g., 16px, 1rem).', 'consent-raven' ) }
					value={ appearance.dialog_radius || '16px' }
					onChange={ ( dialog_radius ) => updateAppearance( { dialog_radius } ) }
				/>
			</div>
		</div>
	);
};

export default AppearancePanel;
