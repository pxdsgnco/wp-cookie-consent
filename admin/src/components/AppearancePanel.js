/**
 * Consent Raven - Appearance Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState, useMemo } from '@wordpress/element';
import { SelectControl, TextControl, ColorPicker, BaseControl, Button, Tooltip } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import BannerPreview from './BannerPreview';

/**
 * Calculate color contrast ratio (WCAG)
 *
 * @param {string} color1 First hex color.
 * @param {string} color2 Second hex color.
 * @return {number} Contrast ratio.
 */
const getContrastRatio = ( color1, color2 ) => {
	const getLuminance = ( hex ) => {
		const rgb = hex.replace( '#', '' ).match( /.{2}/g )?.map( ( x ) => {
			const c = parseInt( x, 16 ) / 255;
			return c <= 0.03928 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 );
		} ) || [ 0, 0, 0 ];
		return 0.2126 * rgb[ 0 ] + 0.7152 * rgb[ 1 ] + 0.0722 * rgb[ 2 ];
	};

	const l1 = getLuminance( color1 );
	const l2 = getLuminance( color2 );
	const lighter = Math.max( l1, l2 );
	const darker = Math.min( l1, l2 );

	return ( lighter + 0.05 ) / ( darker + 0.05 );
};

/**
 * Get contrast level label
 *
 * @param {number} ratio Contrast ratio.
 * @return {Object} Level info with label and status.
 */
const getContrastLevel = ( ratio ) => {
	if ( ratio >= 7 ) {
		return { label: 'AAA', status: 'excellent', color: '#00a32a' };
	}
	if ( ratio >= 4.5 ) {
		return { label: 'AA', status: 'good', color: '#00a32a' };
	}
	if ( ratio >= 3 ) {
		return { label: 'AA Large', status: 'fair', color: '#dba617' };
	}
	return { label: 'Fail', status: 'poor', color: '#d63638' };
};

/**
 * Appearance Panel component
 *
 * @param {Object}   props                  Component props.
 * @param {Object}   props.appearance       Current appearance settings.
 * @param {Function} props.updateAppearance Function to update appearance.
 * @param {Object}   props.settings         Full settings object.
 * @param {Object}   props.content          Content settings.
 * @param {Array}    props.categories       Cookie categories.
 * @return {JSX.Element} Appearance panel.
 */
const AppearancePanel = ( { appearance, updateAppearance, settings, content, categories } ) => {
	const [ expandedColor, setExpandedColor ] = useState( null );

	// Theme presets
	const themePresets = {
		dark: {
			label: __( 'Dark', 'consent-raven' ),
			background_color: '#1a1a1a',
			text_color: '#ffffff',
			secondary_color: '#b3b3b3',
			button_bg: '#ffffff',
			button_text: '#1a1a1a',
		},
		light: {
			label: __( 'Light', 'consent-raven' ),
			background_color: '#ffffff',
			text_color: '#1a1a1a',
			secondary_color: '#666666',
			button_bg: '#1a1a1a',
			button_text: '#ffffff',
		},
		ocean: {
			label: __( 'Ocean', 'consent-raven' ),
			background_color: '#0f3460',
			text_color: '#ffffff',
			secondary_color: '#94b8d4',
			button_bg: '#16c79a',
			button_text: '#0f3460',
		},
		forest: {
			label: __( 'Forest', 'consent-raven' ),
			background_color: '#2d4a3e',
			text_color: '#ffffff',
			secondary_color: '#a8c5b8',
			button_bg: '#8bc34a',
			button_text: '#1a2e24',
		},
		sunset: {
			label: __( 'Sunset', 'consent-raven' ),
			background_color: '#4a1942',
			text_color: '#ffffff',
			secondary_color: '#d4a5ce',
			button_bg: '#ff6b6b',
			button_text: '#ffffff',
		},
		minimal: {
			label: __( 'Minimal', 'consent-raven' ),
			background_color: '#fafafa',
			text_color: '#333333',
			secondary_color: '#888888',
			button_bg: '#333333',
			button_text: '#ffffff',
		},
		royal: {
			label: __( 'Royal', 'consent-raven' ),
			background_color: '#1e1e2e',
			text_color: '#cdd6f4',
			secondary_color: '#9399b2',
			button_bg: '#cba6f7',
			button_text: '#1e1e2e',
		},
		custom: {
			label: __( 'Custom', 'consent-raven' ),
		},
	};

	const themeOptions = Object.entries( themePresets ).map( ( [ value, preset ] ) => ( {
		value,
		label: preset.label,
	} ) );

	// Calculate contrast ratios
	const contrastInfo = useMemo( () => {
		const bgColor = appearance.background_color || '#1a1a1a';
		const textColor = appearance.text_color || '#ffffff';
		const secondaryColor = appearance.secondary_color || '#b3b3b3';
		const buttonBg = appearance.button_bg || '#ffffff';
		const buttonText = appearance.button_text || '#1a1a1a';

		return {
			textOnBg: getContrastRatio( textColor, bgColor ),
			secondaryOnBg: getContrastRatio( secondaryColor, bgColor ),
			buttonTextOnBg: getContrastRatio( buttonText, buttonBg ),
		};
	}, [ appearance ] );

	/**
	 * Handle theme change
	 *
	 * @param {string} theme Selected theme.
	 */
	const handleThemeChange = ( theme ) => {
		if ( theme !== 'custom' && themePresets[ theme ] ) {
			const { label, ...colors } = themePresets[ theme ];
			updateAppearance( {
				theme,
				...colors,
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

	/**
	 * Render color field with inline picker
	 */
	const renderColorField = ( key, label, value, helpText ) => {
		const isExpanded = expandedColor === key;

		return (
			<div className="cr-color-field">
				<div
					className="cr-color-field__header"
					onClick={ () => setExpandedColor( isExpanded ? null : key ) }
					onKeyDown={ ( e ) => {
						if ( e.key === 'Enter' || e.key === ' ' ) {
							setExpandedColor( isExpanded ? null : key );
						}
					} }
					role="button"
					tabIndex={ 0 }
					aria-expanded={ isExpanded }
				>
					<div className="cr-color-field__info">
						<span className="cr-color-field__label">{ label }</span>
						{ helpText && <span className="cr-color-field__help">{ helpText }</span> }
					</div>
					<div className="cr-color-field__preview">
						<span
							className="cr-color-swatch"
							style={ { backgroundColor: value } }
						/>
						<span className="cr-color-value">{ value }</span>
					</div>
				</div>
				{ isExpanded && (
					<div className="cr-color-field__picker">
						<ColorPicker
							color={ value }
							onChange={ ( color ) => handleColorChange( key, color ) }
							enableAlpha={ false }
						/>
					</div>
				) }
			</div>
		);
	};

	/**
	 * Render contrast indicator
	 */
	const renderContrastIndicator = ( ratio, label ) => {
		const level = getContrastLevel( ratio );

		return (
			<Tooltip text={ `${ label }: ${ ratio.toFixed( 2 ) }:1 - ${ level.status }` }>
				<span
					className="cr-contrast-badge"
					style={ { backgroundColor: level.color } }
				>
					{ level.label }
				</span>
			</Tooltip>
		);
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

			{/* Theme Presets */}
			<div className="cr-form-section">
				<h3 className="cr-form-section__title">
					{ __( 'Theme', 'consent-raven' ) }
				</h3>
				<div className="cr-theme-grid">
					{ Object.entries( themePresets ).map( ( [ key, preset ] ) => {
						if ( key === 'custom' ) {
							return null;
						}
						const isActive = appearance.theme === key;
						return (
							<button
								key={ key }
								type="button"
								className={ `cr-theme-preset ${ isActive ? 'cr-theme-preset--active' : '' }` }
								onClick={ () => handleThemeChange( key ) }
								aria-pressed={ isActive }
							>
								<div
									className="cr-theme-preset__preview"
									style={ {
										backgroundColor: preset.background_color,
										color: preset.text_color,
									} }
								>
									<div className="cr-theme-preset__banner">
										<div
											className="cr-theme-preset__button"
											style={ {
												backgroundColor: preset.button_bg,
												color: preset.button_text,
											} }
										/>
									</div>
								</div>
								<span className="cr-theme-preset__label">{ preset.label }</span>
							</button>
						);
					} ) }
				</div>
			</div>

			{/* Color Customization */}
			<div className="cr-form-section">
				<div className="cr-form-section__header">
					<h3 className="cr-form-section__title">
						{ __( 'Colors', 'consent-raven' ) }
					</h3>
					{ appearance.theme === 'custom' && (
						<span className="cr-form-section__badge">
							{ __( 'Custom', 'consent-raven' ) }
						</span>
					) }
				</div>

				<div className="cr-color-fields">
					{ renderColorField(
						'background_color',
						__( 'Background Color', 'consent-raven' ),
						appearance.background_color || '#1a1a1a'
					) }
					<div className="cr-color-field-row">
						{ renderColorField(
							'text_color',
							__( 'Text Color', 'consent-raven' ),
							appearance.text_color || '#ffffff'
						) }
						<div className="cr-contrast-info">
							{ renderContrastIndicator( contrastInfo.textOnBg, __( 'Text contrast', 'consent-raven' ) ) }
						</div>
					</div>
					<div className="cr-color-field-row">
						{ renderColorField(
							'secondary_color',
							__( 'Secondary Text', 'consent-raven' ),
							appearance.secondary_color || '#b3b3b3'
						) }
						<div className="cr-contrast-info">
							{ renderContrastIndicator( contrastInfo.secondaryOnBg, __( 'Secondary text contrast', 'consent-raven' ) ) }
						</div>
					</div>
					{ renderColorField(
						'button_bg',
						__( 'Button Background', 'consent-raven' ),
						appearance.button_bg || '#ffffff'
					) }
					<div className="cr-color-field-row">
						{ renderColorField(
							'button_text',
							__( 'Button Text', 'consent-raven' ),
							appearance.button_text || '#1a1a1a'
						) }
						<div className="cr-contrast-info">
							{ renderContrastIndicator( contrastInfo.buttonTextOnBg, __( 'Button text contrast', 'consent-raven' ) ) }
						</div>
					</div>
				</div>

				{/* Contrast Summary */}
				<div className="cr-contrast-summary">
					<span className="cr-contrast-summary__label">
						{ __( 'Accessibility:', 'consent-raven' ) }
					</span>
					{ contrastInfo.textOnBg >= 4.5 && contrastInfo.buttonTextOnBg >= 4.5 ? (
						<span className="cr-contrast-summary__status cr-contrast-summary__status--pass">
							{ __( 'WCAG AA compliant', 'consent-raven' ) }
						</span>
					) : (
						<span className="cr-contrast-summary__status cr-contrast-summary__status--fail">
							{ __( 'Improve contrast for accessibility', 'consent-raven' ) }
						</span>
					) }
				</div>
			</div>

			{/* Border Radius */}
			<div className="cr-form-section">
				<h3 className="cr-form-section__title">
					{ __( 'Border Radius', 'consent-raven' ) }
				</h3>
				<div className="cr-form-row">
					<div className="cr-form-group">
						<TextControl
							label={ __( 'Button Radius', 'consent-raven' ) }
							help={ __( 'e.g., 8px, 0.5rem', 'consent-raven' ) }
							value={ appearance.button_radius || '8px' }
							onChange={ ( button_radius ) => updateAppearance( { button_radius } ) }
						/>
					</div>
					<div className="cr-form-group">
						<TextControl
							label={ __( 'Dialog Radius', 'consent-raven' ) }
							help={ __( 'e.g., 16px, 1rem', 'consent-raven' ) }
							value={ appearance.dialog_radius || '16px' }
							onChange={ ( dialog_radius ) => updateAppearance( { dialog_radius } ) }
						/>
					</div>
				</div>
			</div>

			{/* Live Preview */}
			<BannerPreview
				settings={ settings || {} }
				appearance={ appearance }
				content={ content || {} }
				categories={ categories || [] }
			/>
		</div>
	);
};

export default AppearancePanel;
