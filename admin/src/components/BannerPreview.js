/**
 * Consent Raven - Banner Preview Component
 *
 * @package Consent_Raven
 * @since 1.1.0
 */

import { useState } from '@wordpress/element';
import { SelectControl, Button, Icon } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { desktop, mobile, tablet } from '@wordpress/icons';

/**
 * Banner Preview component
 *
 * @param {Object} props            Component props.
 * @param {Object} props.settings   Current settings.
 * @param {Object} props.appearance Current appearance settings.
 * @param {Object} props.content    Current content settings.
 * @param {Array}  props.categories Cookie categories.
 * @return {JSX.Element} Banner preview.
 */
const BannerPreview = ( { settings, appearance, content, categories } ) => {
	const [ previewPosition, setPreviewPosition ] = useState( settings.position || 'bottom-right' );
	const [ previewDevice, setPreviewDevice ] = useState( 'desktop' );
	const [ showPreferences, setShowPreferences ] = useState( false );

	const positionOptions = [
		{ value: 'bottom-right', label: __( 'Bottom Right (Floating)', 'consent-raven' ) },
		{ value: 'bottom-bar', label: __( 'Bottom Bar (Full Width)', 'consent-raven' ) },
		{ value: 'top-bar', label: __( 'Top Bar (Full Width)', 'consent-raven' ) },
		{ value: 'modal', label: __( 'Centered Modal', 'consent-raven' ) },
	];

	// CSS custom properties based on appearance settings
	const cssVars = {
		'--cr-bg-color': appearance.background_color || '#1a1a1a',
		'--cr-text-color': appearance.text_color || '#ffffff',
		'--cr-secondary-color': appearance.secondary_color || '#b3b3b3',
		'--cr-button-bg': appearance.button_bg || '#ffffff',
		'--cr-button-text': appearance.button_text || '#1a1a1a',
		'--cr-button-radius': appearance.button_radius || '8px',
		'--cr-dialog-radius': appearance.dialog_radius || '16px',
	};

	// Device preview dimensions
	const deviceDimensions = {
		desktop: { width: '100%', maxWidth: '800px', height: '500px' },
		tablet: { width: '768px', height: '500px' },
		mobile: { width: '375px', height: '667px' },
	};

	/**
	 * Get position-specific styles for the preview banner
	 */
	const getBannerStyles = () => {
		const baseStyles = {
			position: 'absolute',
			fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif',
			fontSize: '14px',
			lineHeight: '1.5',
			zIndex: 10,
		};

		switch ( previewPosition ) {
			case 'bottom-right':
				return {
					...baseStyles,
					bottom: '20px',
					right: '20px',
					maxWidth: previewDevice === 'mobile' ? '100%' : '360px',
					...(previewDevice === 'mobile' && { bottom: 0, right: 0, left: 0 }),
				};
			case 'bottom-bar':
				return {
					...baseStyles,
					bottom: 0,
					left: 0,
					right: 0,
				};
			case 'top-bar':
				return {
					...baseStyles,
					top: 0,
					left: 0,
					right: 0,
				};
			case 'modal':
				return {
					...baseStyles,
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					display: 'flex',
					alignItems: 'center',
					justifyContent: 'center',
					padding: '20px',
				};
			default:
				return baseStyles;
		}
	};

	/**
	 * Get dialog styles based on position
	 */
	const getDialogStyles = () => {
		const baseStyles = {
			backgroundColor: cssVars['--cr-bg-color'],
			color: cssVars['--cr-text-color'],
			borderRadius: previewPosition === 'bottom-bar' || previewPosition === 'top-bar' ? 0 : cssVars['--cr-dialog-radius'],
			boxShadow: '0 4px 24px rgba(0, 0, 0, 0.15)',
			overflow: 'hidden',
		};

		if ( previewPosition === 'modal' ) {
			return {
				...baseStyles,
				maxWidth: '400px',
				width: '100%',
			};
		}

		if ( previewDevice === 'mobile' && previewPosition === 'bottom-right' ) {
			return {
				...baseStyles,
				borderRadius: `${ cssVars['--cr-dialog-radius'] } ${ cssVars['--cr-dialog-radius'] } 0 0`,
			};
		}

		return baseStyles;
	};

	/**
	 * Render the main banner preview
	 */
	const renderBanner = () => {
		const isBar = previewPosition === 'bottom-bar' || previewPosition === 'top-bar';

		return (
			<div style={ getBannerStyles() }>
				{ previewPosition === 'modal' && (
					<div style={{
						position: 'absolute',
						top: 0,
						left: 0,
						right: 0,
						bottom: 0,
						backgroundColor: 'rgba(0, 0, 0, 0.5)',
						zIndex: -1,
					}} />
				) }
				<div style={ getDialogStyles() }>
					<div style={{
						padding: '24px',
						...(isBar && {
							maxWidth: '1200px',
							margin: '0 auto',
							display: 'flex',
							flexWrap: 'wrap',
							alignItems: 'center',
							gap: '20px',
						}),
					}}>
						<div style={{ flex: isBar ? 1 : 'auto' }}>
							<h2 style={{
								margin: isBar ? 0 : '0 0 12px 0',
								fontSize: '18px',
								fontWeight: 600,
								color: cssVars['--cr-text-color'],
							}}>
								{ content.title || __( 'Cookie settings', 'consent-raven' ) }
							</h2>
							<p style={{
								margin: isBar ? 0 : '0 0 20px 0',
								color: cssVars['--cr-secondary-color'],
								fontSize: '14px',
								lineHeight: '1.6',
							}}>
								{ content.description || __( 'We use cookies to improve your experience.', 'consent-raven' ) }
							</p>
						</div>
						<div style={{
							display: 'flex',
							flexDirection: isBar ? 'row' : 'column',
							gap: '12px',
							...(isBar && { marginLeft: 'auto', alignItems: 'center' }),
						}}>
							<button
								type="button"
								onClick={ () => setShowPreferences( true ) }
								style={{
									display: 'inline-flex',
									alignItems: 'center',
									justifyContent: 'center',
									padding: '12px 20px',
									borderRadius: cssVars['--cr-button-radius'],
									fontSize: '14px',
									fontWeight: 500,
									cursor: 'pointer',
									border: `1px solid ${ cssVars['--cr-secondary-color'] }`,
									backgroundColor: 'transparent',
									color: cssVars['--cr-text-color'],
									width: isBar ? 'auto' : '100%',
								}}
							>
								{ content.customize_button || __( 'Customize', 'consent-raven' ) }
							</button>
							<div style={{ display: 'flex', gap: '12px' }}>
								<button
									type="button"
									style={{
										display: 'inline-flex',
										alignItems: 'center',
										justifyContent: 'center',
										padding: '12px 20px',
										borderRadius: cssVars['--cr-button-radius'],
										fontSize: '14px',
										fontWeight: 500,
										cursor: 'pointer',
										border: `1px solid ${ cssVars['--cr-text-color'] }`,
										backgroundColor: 'transparent',
										color: cssVars['--cr-text-color'],
										flex: 1,
									}}
								>
									{ content.reject_button || __( 'Reject All', 'consent-raven' ) }
								</button>
								<button
									type="button"
									style={{
										display: 'inline-flex',
										alignItems: 'center',
										justifyContent: 'center',
										padding: '12px 20px',
										borderRadius: cssVars['--cr-button-radius'],
										fontSize: '14px',
										fontWeight: 500,
										cursor: 'pointer',
										border: 'none',
										backgroundColor: cssVars['--cr-button-bg'],
										color: cssVars['--cr-button-text'],
										flex: 1,
									}}
								>
									{ content.accept_button || __( 'Accept All', 'consent-raven' ) }
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		);
	};

	/**
	 * Render the preferences modal preview
	 */
	const renderPreferencesModal = () => {
		if ( ! showPreferences ) {
			return null;
		}

		return (
			<div style={{
				position: 'absolute',
				top: 0,
				left: 0,
				right: 0,
				bottom: 0,
				display: 'flex',
				alignItems: 'center',
				justifyContent: 'center',
				padding: '20px',
				zIndex: 20,
			}}>
				<div style={{
					position: 'absolute',
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					backgroundColor: 'rgba(0, 0, 0, 0.5)',
					zIndex: -1,
				}} />
				<div style={{
					backgroundColor: cssVars['--cr-bg-color'],
					color: cssVars['--cr-text-color'],
					borderRadius: cssVars['--cr-dialog-radius'],
					boxShadow: '0 4px 24px rgba(0, 0, 0, 0.15)',
					maxWidth: '450px',
					width: '100%',
					maxHeight: '80%',
					display: 'flex',
					flexDirection: 'column',
					overflow: 'hidden',
				}}>
					<div style={{
						display: 'flex',
						alignItems: 'center',
						justifyContent: 'space-between',
						padding: '20px 24px',
						borderBottom: '1px solid rgba(255, 255, 255, 0.1)',
					}}>
						<h2 style={{ margin: 0, fontSize: '18px', fontWeight: 600 }}>
							{ content.customize_button || __( 'Customize', 'consent-raven' ) }
						</h2>
						<button
							type="button"
							onClick={ () => setShowPreferences( false ) }
							style={{
								background: 'none',
								border: 'none',
								color: cssVars['--cr-text-color'],
								fontSize: '24px',
								cursor: 'pointer',
								padding: 0,
								width: '32px',
								height: '32px',
								display: 'flex',
								alignItems: 'center',
								justifyContent: 'center',
								borderRadius: '50%',
							}}
						>
							&times;
						</button>
					</div>
					<div style={{ flex: 1, overflow: 'auto' }}>
						{ (categories || []).map( ( category ) => (
							<div
								key={ category.id || category.slug }
								style={{
									padding: '20px 24px',
									borderBottom: '1px solid rgba(255, 255, 255, 0.1)',
								}}
							>
								<div style={{
									display: 'flex',
									alignItems: 'flex-start',
									justifyContent: 'space-between',
									gap: '16px',
								}}>
									<div style={{ flex: 1 }}>
										<h3 style={{
											margin: '0 0 4px 0',
											fontSize: '15px',
											fontWeight: 600,
										}}>
											{ category.name }
										</h3>
										<p style={{
											margin: 0,
											fontSize: '13px',
											color: cssVars['--cr-secondary-color'],
											lineHeight: '1.5',
										}}>
											{ category.description }
										</p>
									</div>
									<div>
										{ category.essential ? (
											<span style={{
												fontSize: '12px',
												color: cssVars['--cr-secondary-color'],
												padding: '4px 8px',
												backgroundColor: 'rgba(255, 255, 255, 0.1)',
												borderRadius: '4px',
												whiteSpace: 'nowrap',
											}}>
												{ __( 'Always on', 'consent-raven' ) }
											</span>
										) : (
											<div style={{
												position: 'relative',
												display: 'inline-block',
												width: '48px',
												height: '26px',
											}}>
												<div style={{
													position: 'absolute',
													top: 0,
													left: 0,
													right: 0,
													bottom: 0,
													backgroundColor: 'rgba(255, 255, 255, 0.2)',
													borderRadius: '26px',
												}} />
												<div style={{
													position: 'absolute',
													height: '20px',
													width: '20px',
													left: '3px',
													bottom: '3px',
													backgroundColor: cssVars['--cr-text-color'],
													borderRadius: '50%',
												}} />
											</div>
										) }
									</div>
								</div>
							</div>
						) ) }
					</div>
					<div style={{
						padding: '20px 24px',
						borderTop: '1px solid rgba(255, 255, 255, 0.1)',
					}}>
						<button
							type="button"
							onClick={ () => setShowPreferences( false ) }
							style={{
								display: 'inline-flex',
								alignItems: 'center',
								justifyContent: 'center',
								padding: '12px 20px',
								borderRadius: cssVars['--cr-button-radius'],
								fontSize: '14px',
								fontWeight: 500,
								cursor: 'pointer',
								border: 'none',
								backgroundColor: cssVars['--cr-button-bg'],
								color: cssVars['--cr-button-text'],
								width: '100%',
							}}
						>
							{ content.save_button || __( 'Save Preferences', 'consent-raven' ) }
						</button>
					</div>
				</div>
			</div>
		);
	};

	return (
		<div className="cr-preview-section">
			<div className="cr-preview-section__header">
				<h3 className="cr-preview-section__title">
					{ __( 'Live Preview', 'consent-raven' ) }
				</h3>
				<div className="cr-preview-controls">
					<div className="cr-preview-controls__position">
						<SelectControl
							label={ __( 'Position', 'consent-raven' ) }
							hideLabelFromVision
							value={ previewPosition }
							options={ positionOptions }
							onChange={ setPreviewPosition }
							__nextHasNoMarginBottom
						/>
					</div>
					<div className="cr-preview-controls__device">
						<Button
							icon={ desktop }
							label={ __( 'Desktop', 'consent-raven' ) }
							onClick={ () => setPreviewDevice( 'desktop' ) }
							isPressed={ previewDevice === 'desktop' }
							className="cr-device-button"
						/>
						<Button
							icon={ tablet }
							label={ __( 'Tablet', 'consent-raven' ) }
							onClick={ () => setPreviewDevice( 'tablet' ) }
							isPressed={ previewDevice === 'tablet' }
							className="cr-device-button"
						/>
						<Button
							icon={ mobile }
							label={ __( 'Mobile', 'consent-raven' ) }
							onClick={ () => setPreviewDevice( 'mobile' ) }
							isPressed={ previewDevice === 'mobile' }
							className="cr-device-button"
						/>
					</div>
				</div>
			</div>

			<div
				className="cr-preview-container"
				style={{
					...deviceDimensions[ previewDevice ],
					margin: '0 auto',
					position: 'relative',
					backgroundColor: '#f5f5f5',
					borderRadius: '8px',
					overflow: 'hidden',
					transition: 'all 0.3s ease',
				}}
			>
				{/* Simulated website background */}
				<div style={{
					position: 'absolute',
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					background: 'linear-gradient(180deg, #e8e8e8 0%, #f5f5f5 100%)',
					display: 'flex',
					flexDirection: 'column',
				}}>
					{/* Fake header */}
					<div style={{
						height: '60px',
						backgroundColor: '#ffffff',
						borderBottom: '1px solid #ddd',
						display: 'flex',
						alignItems: 'center',
						padding: '0 20px',
					}}>
						<div style={{
							width: '120px',
							height: '30px',
							backgroundColor: '#ddd',
							borderRadius: '4px',
						}} />
						<div style={{ marginLeft: 'auto', display: 'flex', gap: '16px' }}>
							{ [ 1, 2, 3 ].map( ( i ) => (
								<div
									key={ i }
									style={{
										width: '60px',
										height: '12px',
										backgroundColor: '#ddd',
										borderRadius: '2px',
									}}
								/>
							) ) }
						</div>
					</div>
					{/* Fake content */}
					<div style={{ flex: 1, padding: '20px' }}>
						<div style={{
							width: '60%',
							height: '24px',
							backgroundColor: '#ddd',
							borderRadius: '4px',
							marginBottom: '16px',
						}} />
						{ [ 1, 2, 3 ].map( ( i ) => (
							<div
								key={ i }
								style={{
									width: `${ 90 - i * 10 }%`,
									height: '12px',
									backgroundColor: '#e0e0e0',
									borderRadius: '2px',
									marginBottom: '8px',
								}}
							/>
						) ) }
					</div>
				</div>

				{/* Banner preview */}
				{ renderBanner() }

				{/* Preferences modal preview */}
				{ renderPreferencesModal() }
			</div>

			<p className="cr-preview-hint">
				{ __( 'Click "Customize" to preview the preferences modal.', 'consent-raven' ) }
			</p>
		</div>
	);
};

export default BannerPreview;
