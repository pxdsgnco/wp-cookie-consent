/**
 * Consent Raven - Tools Panel Component
 *
 * @package Consent_Raven
 * @since 1.1.0
 */

import { useState, useRef } from '@wordpress/element';
import { Button, Notice, Modal, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Tools Panel component - Import/Export functionality
 *
 * @param {Object}   props              Component props.
 * @param {Object}   props.settings     Current settings.
 * @param {Array}    props.categories   Current categories.
 * @param {Array}    props.cookies      Current cookies.
 * @param {Array}    props.scripts      Current scripts.
 * @param {Function} props.onImport     Callback after successful import.
 * @return {JSX.Element} Tools panel.
 */
const ToolsPanel = ( { settings, categories, cookies, scripts, onImport } ) => {
	const [ isExporting, setIsExporting ] = useState( false );
	const [ isImporting, setIsImporting ] = useState( false );
	const [ isDragging, setIsDragging ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ confirmModal, setConfirmModal ] = useState( null );
	const [ importData, setImportData ] = useState( null );
	const fileInputRef = useRef( null );

	/**
	 * Export all settings to JSON file
	 */
	const handleExport = async () => {
		setIsExporting( true );
		setNotice( null );

		try {
			const response = await apiFetch( {
				path: '/consent-raven/v1/export',
				method: 'GET',
			} );

			// Create export data with current timestamp
			const exportData = {
				plugin: 'consent-raven',
				version: window.consentRavenAdmin?.version || '1.0.0',
				exported_at: new Date().toISOString(),
				data: response,
			};

			// Create and download the file
			const blob = new Blob( [ JSON.stringify( exportData, null, 2 ) ], {
				type: 'application/json',
			} );
			const url = URL.createObjectURL( blob );
			const a = document.createElement( 'a' );
			a.href = url;
			a.download = `consent-raven-export-${ new Date().toISOString().split( 'T' )[ 0 ] }.json`;
			document.body.appendChild( a );
			a.click();
			document.body.removeChild( a );
			URL.revokeObjectURL( url );

			setNotice( {
				status: 'success',
				message: __( 'Settings exported successfully!', 'consent-raven' ),
			} );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Error exporting settings.', 'consent-raven' ),
			} );
		} finally {
			setIsExporting( false );
		}
	};

	/**
	 * Validate import data
	 *
	 * @param {Object} data Import data to validate.
	 * @return {Object} Validation result with isValid and errors.
	 */
	const validateImportData = ( data ) => {
		const errors = [];

		if ( ! data || typeof data !== 'object' ) {
			errors.push( __( 'Invalid JSON format.', 'consent-raven' ) );
			return { isValid: false, errors };
		}

		if ( data.plugin !== 'consent-raven' ) {
			errors.push( __( 'This file was not exported from Consent Raven.', 'consent-raven' ) );
		}

		if ( ! data.data ) {
			errors.push( __( 'Export file is missing data.', 'consent-raven' ) );
		}

		return {
			isValid: errors.length === 0,
			errors,
		};
	};

	/**
	 * Process file for import
	 *
	 * @param {File} file The file to process.
	 */
	const processFile = async ( file ) => {
		if ( ! file ) {
			return;
		}

		if ( file.type !== 'application/json' && ! file.name.endsWith( '.json' ) ) {
			setNotice( {
				status: 'error',
				message: __( 'Please select a JSON file.', 'consent-raven' ),
			} );
			return;
		}

		try {
			const text = await file.text();
			const data = JSON.parse( text );

			const validation = validateImportData( data );

			if ( ! validation.isValid ) {
				setNotice( {
					status: 'error',
					message: validation.errors.join( ' ' ),
				} );
				return;
			}

			// Store import data and show confirmation
			setImportData( data );
			setConfirmModal( {
				title: __( 'Confirm Import', 'consent-raven' ),
				message: __( 'This will replace all your current settings with the imported data. This action cannot be undone.', 'consent-raven' ),
				details: {
					exportedAt: data.exported_at,
					version: data.version,
					hasSettings: !! data.data.settings,
					categoriesCount: data.data.categories?.length || 0,
					cookiesCount: data.data.cookies?.length || 0,
					scriptsCount: data.data.scripts?.length || 0,
				},
			} );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: __( 'Invalid JSON file. Please check the file format.', 'consent-raven' ),
			} );
		}
	};

	/**
	 * Perform the actual import
	 */
	const performImport = async () => {
		if ( ! importData ) {
			return;
		}

		setIsImporting( true );
		setConfirmModal( null );
		setNotice( null );

		try {
			await apiFetch( {
				path: '/consent-raven/v1/import',
				method: 'POST',
				data: importData.data,
			} );

			setNotice( {
				status: 'success',
				message: __( 'Settings imported successfully! Page will refresh in 2 seconds.', 'consent-raven' ),
			} );

			// Refresh the page to load new data
			setTimeout( () => {
				window.location.reload();
			}, 2000 );

			if ( onImport ) {
				onImport( importData.data );
			}
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Error importing settings.', 'consent-raven' ),
			} );
		} finally {
			setIsImporting( false );
			setImportData( null );
		}
	};

	/**
	 * Handle file input change
	 */
	const handleFileChange = ( event ) => {
		const file = event.target.files?.[ 0 ];
		if ( file ) {
			processFile( file );
		}
		// Reset input so same file can be selected again
		event.target.value = '';
	};

	/**
	 * Handle drag events
	 */
	const handleDragEnter = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		setIsDragging( true );
	};

	const handleDragLeave = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		setIsDragging( false );
	};

	const handleDragOver = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
	};

	const handleDrop = ( e ) => {
		e.preventDefault();
		e.stopPropagation();
		setIsDragging( false );

		const file = e.dataTransfer.files?.[ 0 ];
		if ( file ) {
			processFile( file );
		}
	};

	/**
	 * Reset all settings to defaults
	 */
	const handleReset = () => {
		setConfirmModal( {
			title: __( 'Reset to Defaults', 'consent-raven' ),
			message: __( 'This will reset all settings to their default values. All your customizations will be lost. This action cannot be undone.', 'consent-raven' ),
			isReset: true,
		} );
	};

	const performReset = async () => {
		setIsImporting( true );
		setConfirmModal( null );

		try {
			await apiFetch( {
				path: '/consent-raven/v1/reset',
				method: 'POST',
			} );

			setNotice( {
				status: 'success',
				message: __( 'Settings reset to defaults! Page will refresh in 2 seconds.', 'consent-raven' ),
			} );

			setTimeout( () => {
				window.location.reload();
			}, 2000 );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Error resetting settings.', 'consent-raven' ),
			} );
		} finally {
			setIsImporting( false );
		}
	};

	/**
	 * Format date for display
	 */
	const formatDate = ( isoString ) => {
		try {
			return new Date( isoString ).toLocaleString();
		} catch {
			return isoString;
		}
	};

	return (
		<div className="cr-tools-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Tools', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Import and export your cookie consent settings, or reset to defaults.', 'consent-raven' ) }
				</p>
			</div>

			{ notice && (
				<Notice
					status={ notice.status }
					isDismissible
					onRemove={ () => setNotice( null ) }
				>
					{ notice.message }
				</Notice>
			) }

			<div className="cr-import-export">
				{/* Export Section */}
				<div className="cr-import-export__section">
					<h3 className="cr-import-export__title">
						{ __( 'Export Settings', 'consent-raven' ) }
					</h3>
					<p className="cr-import-export__description">
						{ __( 'Download all your settings as a JSON file. This includes appearance, content, categories, cookies, and scripts.', 'consent-raven' ) }
					</p>
					<div className="cr-export-summary">
						<ul className="cr-export-summary__list">
							<li>
								<span className="cr-export-summary__label">{ __( 'Categories:', 'consent-raven' ) }</span>
								<span className="cr-export-summary__value">{ categories?.length || 0 }</span>
							</li>
							<li>
								<span className="cr-export-summary__label">{ __( 'Cookies:', 'consent-raven' ) }</span>
								<span className="cr-export-summary__value">{ cookies?.length || 0 }</span>
							</li>
							<li>
								<span className="cr-export-summary__label">{ __( 'Scripts:', 'consent-raven' ) }</span>
								<span className="cr-export-summary__value">{ scripts?.length || 0 }</span>
							</li>
						</ul>
					</div>
					<Button
						variant="secondary"
						onClick={ handleExport }
						disabled={ isExporting }
						isBusy={ isExporting }
					>
						{ isExporting
							? __( 'Exporting...', 'consent-raven' )
							: __( 'Export to JSON', 'consent-raven' )
						}
					</Button>
				</div>

				{/* Import Section */}
				<div className="cr-import-export__section">
					<h3 className="cr-import-export__title">
						{ __( 'Import Settings', 'consent-raven' ) }
					</h3>
					<p className="cr-import-export__description">
						{ __( 'Upload a previously exported JSON file to restore your settings.', 'consent-raven' ) }
					</p>
					<div
						className={ `cr-file-upload ${ isDragging ? 'cr-file-upload--dragging' : '' }` }
						onClick={ () => fileInputRef.current?.click() }
						onDragEnter={ handleDragEnter }
						onDragLeave={ handleDragLeave }
						onDragOver={ handleDragOver }
						onDrop={ handleDrop }
						onKeyDown={ ( e ) => {
							if ( e.key === 'Enter' || e.key === ' ' ) {
								fileInputRef.current?.click();
							}
						} }
						role="button"
						tabIndex={ 0 }
					>
						{ isImporting ? (
							<Spinner />
						) : (
							<>
								<div className="cr-file-upload__icon">📁</div>
								<div className="cr-file-upload__text">
									{ __( 'Drop a JSON file here or click to browse', 'consent-raven' ) }
								</div>
								<div className="cr-file-upload__hint">
									{ __( 'Only .json files are accepted', 'consent-raven' ) }
								</div>
							</>
						) }
						<input
							ref={ fileInputRef }
							type="file"
							accept=".json,application/json"
							onChange={ handleFileChange }
						/>
					</div>
				</div>
			</div>

			{/* Reset Section */}
			<div className="cr-form-section" style={ { marginTop: '32px' } }>
				<h3 className="cr-form-section__title" style={ { color: '#d63638' } }>
					{ __( 'Danger Zone', 'consent-raven' ) }
				</h3>
				<div className="cr-danger-zone">
					<div className="cr-danger-zone__content">
						<h4 className="cr-danger-zone__title">
							{ __( 'Reset to Defaults', 'consent-raven' ) }
						</h4>
						<p className="cr-danger-zone__description">
							{ __( 'Reset all settings to their original defaults. This cannot be undone.', 'consent-raven' ) }
						</p>
					</div>
					<Button
						variant="secondary"
						isDestructive
						onClick={ handleReset }
					>
						{ __( 'Reset Settings', 'consent-raven' ) }
					</Button>
				</div>
			</div>

			{/* Confirmation Modal */}
			{ confirmModal && (
				<Modal
					title={ confirmModal.title }
					onRequestClose={ () => {
						setConfirmModal( null );
						setImportData( null );
					} }
				>
					<p>{ confirmModal.message }</p>

					{ confirmModal.details && (
						<div className="cr-import-details">
							<h4>{ __( 'Import Details:', 'consent-raven' ) }</h4>
							<ul>
								<li>
									<strong>{ __( 'Exported:', 'consent-raven' ) }</strong>{ ' ' }
									{ formatDate( confirmModal.details.exportedAt ) }
								</li>
								<li>
									<strong>{ __( 'Version:', 'consent-raven' ) }</strong>{ ' ' }
									{ confirmModal.details.version }
								</li>
								<li>
									<strong>{ __( 'Categories:', 'consent-raven' ) }</strong>{ ' ' }
									{ confirmModal.details.categoriesCount }
								</li>
								<li>
									<strong>{ __( 'Cookies:', 'consent-raven' ) }</strong>{ ' ' }
									{ confirmModal.details.cookiesCount }
								</li>
								<li>
									<strong>{ __( 'Scripts:', 'consent-raven' ) }</strong>{ ' ' }
									{ confirmModal.details.scriptsCount }
								</li>
							</ul>
						</div>
					) }

					<div
						className="cr-form-group"
						style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end', marginTop: '24px' } }
					>
						<Button
							variant="tertiary"
							onClick={ () => {
								setConfirmModal( null );
								setImportData( null );
							} }
						>
							{ __( 'Cancel', 'consent-raven' ) }
						</Button>
						<Button
							variant="primary"
							isDestructive={ confirmModal.isReset }
							onClick={ confirmModal.isReset ? performReset : performImport }
						>
							{ confirmModal.isReset
								? __( 'Yes, Reset Everything', 'consent-raven' )
								: __( 'Yes, Import Settings', 'consent-raven' )
							}
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default ToolsPanel;
