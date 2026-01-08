/**
 * Consent Raven - Consent Logs Panel Component
 *
 * @package Consent_Raven
 * @since 1.2.0
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { Button, Spinner, Notice, SelectControl, Modal } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Consent Logs Panel component - View and export consent records
 *
 * @return {JSX.Element} Consent logs panel.
 */
const ConsentLogsPanel = () => {
	const [ logs, setLogs ] = useState( [] );
	const [ stats, setStats ] = useState( null );
	const [ isLoading, setIsLoading ] = useState( true );
	const [ isLoadingStats, setIsLoadingStats ] = useState( true );
	const [ currentPage, setCurrentPage ] = useState( 1 );
	const [ totalPages, setTotalPages ] = useState( 1 );
	const [ totalItems, setTotalItems ] = useState( 0 );
	const [ filters, setFilters ] = useState( {
		action: '',
		date_from: '',
		date_to: '',
	} );
	const [ isExporting, setIsExporting ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ confirmClear, setConfirmClear ] = useState( false );
	const [ isClearing, setIsClearing ] = useState( false );

	/**
	 * Fetch consent logs
	 */
	const fetchLogs = useCallback( async () => {
		setIsLoading( true );
		try {
			let path = `/consent-raven/v1/consent-logs?page=${ currentPage }&per_page=20`;
			if ( filters.action ) {
				path += `&action=${ filters.action }`;
			}
			if ( filters.date_from ) {
				path += `&date_from=${ filters.date_from }`;
			}
			if ( filters.date_to ) {
				path += `&date_to=${ filters.date_to }`;
			}

			const response = await apiFetch( { path } );
			setLogs( response.logs || [] );
			setTotalPages( response.total_pages || 1 );
			setTotalItems( response.total || 0 );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Error loading consent logs.', 'consent-raven' ),
			} );
		} finally {
			setIsLoading( false );
		}
	}, [ currentPage, filters ] );

	/**
	 * Fetch consent statistics
	 */
	const fetchStats = useCallback( async () => {
		setIsLoadingStats( true );
		try {
			const response = await apiFetch( {
				path: '/consent-raven/v1/consent-logs/stats',
			} );
			setStats( response.stats || null );
		} catch ( error ) {
			console.error( 'Failed to fetch stats:', error );
		} finally {
			setIsLoadingStats( false );
		}
	}, [] );

	useEffect( () => {
		fetchLogs();
	}, [ fetchLogs ] );

	useEffect( () => {
		fetchStats();
	}, [ fetchStats ] );

	/**
	 * Export logs to CSV
	 */
	const handleExport = async () => {
		setIsExporting( true );
		setNotice( null );

		try {
			let path = '/consent-raven/v1/consent-logs/export';
			const params = [];
			if ( filters.action ) {
				params.push( `action=${ filters.action }` );
			}
			if ( filters.date_from ) {
				params.push( `date_from=${ filters.date_from }` );
			}
			if ( filters.date_to ) {
				params.push( `date_to=${ filters.date_to }` );
			}
			if ( params.length > 0 ) {
				path += '?' + params.join( '&' );
			}

			const response = await apiFetch( { path } );

			// Convert to CSV string
			const csvContent = response.data
				.map( ( row ) => row.map( ( cell ) => `"${ String( cell ).replace( /"/g, '""' ) }"` ).join( ',' ) )
				.join( '\n' );

			// Create and download file
			const blob = new Blob( [ csvContent ], { type: 'text/csv;charset=utf-8;' } );
			const url = URL.createObjectURL( blob );
			const a = document.createElement( 'a' );
			a.href = url;
			a.download = response.filename || 'consent-logs.csv';
			document.body.appendChild( a );
			a.click();
			document.body.removeChild( a );
			URL.revokeObjectURL( url );

			setNotice( {
				status: 'success',
				message: __( 'Export completed successfully!', 'consent-raven' ),
			} );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Export failed.', 'consent-raven' ),
			} );
		} finally {
			setIsExporting( false );
		}
	};

	/**
	 * Clear all logs
	 */
	const handleClearLogs = async () => {
		setIsClearing( true );
		try {
			await apiFetch( {
				path: '/consent-raven/v1/consent-logs',
				method: 'DELETE',
			} );
			setLogs( [] );
			setTotalItems( 0 );
			setTotalPages( 1 );
			setCurrentPage( 1 );
			setConfirmClear( false );
			fetchStats();
			setNotice( {
				status: 'success',
				message: __( 'All consent logs have been cleared.', 'consent-raven' ),
			} );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Failed to clear logs.', 'consent-raven' ),
			} );
		} finally {
			setIsClearing( false );
		}
	};

	/**
	 * Format categories for display
	 */
	const formatCategories = ( categoriesJson ) => {
		try {
			const categories = JSON.parse( categoriesJson );
			return Object.entries( categories )
				.filter( ( [ , enabled ] ) => enabled )
				.map( ( [ name ] ) => name )
				.join( ', ' );
		} catch {
			return categoriesJson;
		}
	};

	/**
	 * Format action for display
	 */
	const formatAction = ( action ) => {
		const actions = {
			accept_all: __( 'Accepted All', 'consent-raven' ),
			reject_all: __( 'Rejected All', 'consent-raven' ),
			custom: __( 'Custom', 'consent-raven' ),
		};
		return actions[ action ] || action;
	};

	/**
	 * Get action badge class
	 */
	const getActionBadgeClass = ( action ) => {
		const classes = {
			accept_all: 'cr-badge cr-badge--success',
			reject_all: 'cr-badge cr-badge--warning',
			custom: 'cr-badge cr-badge--info',
		};
		return classes[ action ] || 'cr-badge';
	};

	/**
	 * Handle filter change
	 */
	const handleFilterChange = ( key, value ) => {
		setFilters( { ...filters, [ key ]: value } );
		setCurrentPage( 1 );
	};

	return (
		<div className="cr-consent-logs-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Consent Logs', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'View and export consent records for compliance audits. All data is anonymized with hashed IP addresses.', 'consent-raven' ) }
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

			{ /* Stats Cards */ }
			{ ! isLoadingStats && stats && (
				<div className="cr-stats-grid">
					<div className="cr-stat-card">
						<span className="cr-stat-card__value">{ stats.total }</span>
						<span className="cr-stat-card__label">{ __( 'Total Records', 'consent-raven' ) }</span>
					</div>
					<div className="cr-stat-card cr-stat-card--success">
						<span className="cr-stat-card__value">{ stats.accept_all }</span>
						<span className="cr-stat-card__label">{ __( 'Accepted All', 'consent-raven' ) }</span>
					</div>
					<div className="cr-stat-card cr-stat-card--warning">
						<span className="cr-stat-card__value">{ stats.reject_all }</span>
						<span className="cr-stat-card__label">{ __( 'Rejected All', 'consent-raven' ) }</span>
					</div>
					<div className="cr-stat-card cr-stat-card--info">
						<span className="cr-stat-card__value">{ stats.custom }</span>
						<span className="cr-stat-card__label">{ __( 'Custom', 'consent-raven' ) }</span>
					</div>
				</div>
			) }

			{ /* Filter Bar */ }
			<div className="cr-filter-bar">
				<SelectControl
					label={ __( 'Filter by Action', 'consent-raven' ) }
					hideLabelFromVision
					value={ filters.action }
					options={ [
						{ value: '', label: __( 'All Actions', 'consent-raven' ) },
						{ value: 'accept_all', label: __( 'Accepted All', 'consent-raven' ) },
						{ value: 'reject_all', label: __( 'Rejected All', 'consent-raven' ) },
						{ value: 'custom', label: __( 'Custom', 'consent-raven' ) },
					] }
					onChange={ ( value ) => handleFilterChange( 'action', value ) }
				/>
				<div className="cr-filter-actions">
					<Button
						variant="secondary"
						onClick={ handleExport }
						disabled={ isExporting || logs.length === 0 }
						isBusy={ isExporting }
					>
						{ isExporting
							? __( 'Exporting...', 'consent-raven' )
							: __( 'Export CSV', 'consent-raven' )
						}
					</Button>
					<Button
						variant="secondary"
						isDestructive
						onClick={ () => setConfirmClear( true ) }
						disabled={ logs.length === 0 }
					>
						{ __( 'Clear All Logs', 'consent-raven' ) }
					</Button>
				</div>
			</div>

			{ /* Logs Table */ }
			{ isLoading ? (
				<div className="cr-loading">
					<Spinner />
				</div>
			) : logs.length === 0 ? (
				<div className="cr-empty-state">
					<div className="cr-empty-state__icon">
						<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
							<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
							<polyline points="14 2 14 8 20 8"></polyline>
							<line x1="16" y1="13" x2="8" y2="13"></line>
							<line x1="16" y1="17" x2="8" y2="17"></line>
							<polyline points="10 9 9 9 8 9"></polyline>
						</svg>
					</div>
					<h3 className="cr-empty-state__title">
						{ __( 'No Consent Logs', 'consent-raven' ) }
					</h3>
					<p className="cr-empty-state__description">
						{ __( 'Consent records will appear here as users interact with the cookie banner.', 'consent-raven' ) }
					</p>
				</div>
			) : (
				<>
					<table className="cr-data-table">
						<thead>
							<tr>
								<th>{ __( 'ID', 'consent-raven' ) }</th>
								<th>{ __( 'IP Hash', 'consent-raven' ) }</th>
								<th>{ __( 'Action', 'consent-raven' ) }</th>
								<th>{ __( 'Categories', 'consent-raven' ) }</th>
								<th>{ __( 'Version', 'consent-raven' ) }</th>
								<th>{ __( 'Date/Time', 'consent-raven' ) }</th>
							</tr>
						</thead>
						<tbody>
							{ logs.map( ( log ) => (
								<tr key={ log.id }>
									<td>{ log.id }</td>
									<td>
										<code title={ log.ip_hash }>
											{ log.ip_hash.substring( 0, 12 ) }...
										</code>
									</td>
									<td>
										<span className={ getActionBadgeClass( log.consent_action ) }>
											{ formatAction( log.consent_action ) }
										</span>
									</td>
									<td>{ formatCategories( log.categories ) }</td>
									<td>{ log.consent_version }</td>
									<td>{ new Date( log.created_at ).toLocaleString() }</td>
								</tr>
							) ) }
						</tbody>
					</table>

					{ /* Pagination */ }
					<div className="cr-pagination">
						<span className="cr-pagination__info">
							{ __( 'Showing', 'consent-raven' ) } { logs.length } { __( 'of', 'consent-raven' ) } { totalItems } { __( 'records', 'consent-raven' ) }
						</span>
						<div className="cr-pagination__buttons">
							<Button
								variant="secondary"
								disabled={ currentPage === 1 }
								onClick={ () => setCurrentPage( currentPage - 1 ) }
							>
								{ __( 'Previous', 'consent-raven' ) }
							</Button>
							<span className="cr-pagination__current">
								{ __( 'Page', 'consent-raven' ) } { currentPage } { __( 'of', 'consent-raven' ) } { totalPages }
							</span>
							<Button
								variant="secondary"
								disabled={ currentPage === totalPages }
								onClick={ () => setCurrentPage( currentPage + 1 ) }
							>
								{ __( 'Next', 'consent-raven' ) }
							</Button>
						</div>
					</div>
				</>
			) }

			{ /* Clear Confirmation Modal */ }
			{ confirmClear && (
				<Modal
					title={ __( 'Clear All Consent Logs', 'consent-raven' ) }
					onRequestClose={ () => setConfirmClear( false ) }
				>
					<p>
						{ __( 'Are you sure you want to delete all consent logs? This action cannot be undone.', 'consent-raven' ) }
					</p>
					<div style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end', marginTop: '24px' } }>
						<Button variant="tertiary" onClick={ () => setConfirmClear( false ) }>
							{ __( 'Cancel', 'consent-raven' ) }
						</Button>
						<Button
							variant="primary"
							isDestructive
							onClick={ handleClearLogs }
							isBusy={ isClearing }
							disabled={ isClearing }
						>
							{ isClearing
								? __( 'Clearing...', 'consent-raven' )
								: __( 'Yes, Clear All', 'consent-raven' )
							}
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default ConsentLogsPanel;
