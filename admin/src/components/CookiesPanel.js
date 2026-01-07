/**
 * Consent Raven - Cookies Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState, useMemo } from '@wordpress/element';
import {
	Button,
	TextControl,
	SelectControl,
	Modal,
	CheckboxControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Cookies Panel component
 *
 * @param {Object}   props            Component props.
 * @param {Array}    props.cookies    Current cookies.
 * @param {Function} props.setCookies Function to update cookies.
 * @param {Array}    props.categories Available categories.
 * @return {JSX.Element} Cookies panel.
 */
const CookiesPanel = ( { cookies, setCookies, categories } ) => {
	const [ editingCookie, setEditingCookie ] = useState( null );
	const [ isModalOpen, setIsModalOpen ] = useState( false );
	const [ filterCategory, setFilterCategory ] = useState( '' );
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ selectedCookies, setSelectedCookies ] = useState( [] );
	const [ validationErrors, setValidationErrors ] = useState( {} );

	const categoryOptions = [
		{ value: '', label: __( 'All Categories', 'consent-raven' ) },
		...categories.map( ( cat ) => ( {
			value: cat.id,
			label: cat.name,
		} ) ),
	];

	const categorySelectOptions = categories.map( ( cat ) => ( {
		value: cat.id,
		label: cat.name,
	} ) );

	/**
	 * Validate cookie fields
	 *
	 * @param {Object} cookie Cookie to validate.
	 * @return {Object} Validation errors.
	 */
	const validateCookie = ( cookie ) => {
		const errors = {};

		if ( ! cookie.name || cookie.name.trim() === '' ) {
			errors.name = __( 'Cookie name is required.', 'consent-raven' );
		}

		if ( ! cookie.category_id ) {
			errors.category_id = __( 'Please select a category.', 'consent-raven' );
		}

		// Check for duplicate cookie names.
		const existingIndex = cookies.findIndex(
			( c, i ) => c.name === cookie.name && i !== cookie.index
		);
		if ( existingIndex !== -1 && cookie.isNew ) {
			errors.name = __( 'A cookie with this name already exists.', 'consent-raven' );
		}

		return errors;
	};

	/**
	 * Add new cookie
	 */
	const addCookie = () => {
		setEditingCookie( {
			name: '',
			category_id: categories[ 0 ]?.id || '',
			provider: '',
			purpose: '',
			expiration: '',
			host: '',
			isNew: true,
		} );
		setValidationErrors( {} );
		setIsModalOpen( true );
	};

	/**
	 * Edit existing cookie
	 *
	 * @param {Object} cookie Cookie to edit.
	 * @param {number} index  Cookie index.
	 */
	const editCookie = ( cookie, index ) => {
		setEditingCookie( { ...cookie, index, isNew: false } );
		setValidationErrors( {} );
		setIsModalOpen( true );
	};

	/**
	 * Save cookie
	 */
	const saveCookie = () => {
		const errors = validateCookie( editingCookie );
		setValidationErrors( errors );

		if ( Object.keys( errors ).length > 0 ) {
			return;
		}

		let updatedCookies;

		if ( editingCookie.isNew ) {
			updatedCookies = [
				...cookies,
				{
					name: editingCookie.name.trim(),
					category_id: editingCookie.category_id,
					provider: editingCookie.provider,
					purpose: editingCookie.purpose,
					expiration: editingCookie.expiration,
					host: editingCookie.host,
				},
			];
		} else {
			updatedCookies = cookies.map( ( c, i ) =>
				i === editingCookie.index
					? {
							name: editingCookie.name.trim(),
							category_id: editingCookie.category_id,
							provider: editingCookie.provider,
							purpose: editingCookie.purpose,
							expiration: editingCookie.expiration,
							host: editingCookie.host,
					  }
					: c
			);
		}

		setCookies( updatedCookies );
		setIsModalOpen( false );
		setEditingCookie( null );
		setValidationErrors( {} );
	};

	/**
	 * Delete cookie
	 *
	 * @param {number} index Cookie index to delete.
	 */
	const deleteCookie = ( index ) => {
		if ( ! window.confirm( __( 'Are you sure you want to delete this cookie?', 'consent-raven' ) ) ) {
			return;
		}

		setCookies( cookies.filter( ( _, i ) => i !== index ) );
		setSelectedCookies( selectedCookies.filter( ( i ) => i !== index ) );
	};

	/**
	 * Delete selected cookies
	 */
	const deleteSelectedCookies = () => {
		if ( selectedCookies.length === 0 ) {
			return;
		}

		if ( ! window.confirm(
			/* translators: %d: number of cookies */
			sprintf( __( 'Are you sure you want to delete %d cookie(s)?', 'consent-raven' ), selectedCookies.length )
		) ) {
			return;
		}

		setCookies( cookies.filter( ( _, i ) => ! selectedCookies.includes( i ) ) );
		setSelectedCookies( [] );
	};

	/**
	 * Change category for selected cookies
	 *
	 * @param {string} categoryId New category ID.
	 */
	const changeSelectedCategory = ( categoryId ) => {
		if ( selectedCookies.length === 0 || ! categoryId ) {
			return;
		}

		const updatedCookies = cookies.map( ( cookie, index ) =>
			selectedCookies.includes( index )
				? { ...cookie, category_id: categoryId }
				: cookie
		);

		setCookies( updatedCookies );
		setSelectedCookies( [] );
	};

	/**
	 * Toggle cookie selection
	 *
	 * @param {number} index Cookie index.
	 */
	const toggleCookieSelection = ( index ) => {
		setSelectedCookies( ( prev ) =>
			prev.includes( index )
				? prev.filter( ( i ) => i !== index )
				: [ ...prev, index ]
		);
	};

	/**
	 * Toggle all cookies selection
	 */
	const toggleAllSelection = () => {
		if ( selectedCookies.length === filteredCookies.length ) {
			setSelectedCookies( [] );
		} else {
			const allIndices = filteredCookies.map( ( _, i ) => {
				// Get original index.
				return cookies.findIndex(
					( c ) => c.name === filteredCookies[ i ].name && c.category_id === filteredCookies[ i ].category_id
				);
			} );
			setSelectedCookies( allIndices );
		}
	};

	/**
	 * Get category name by ID
	 *
	 * @param {string} id Category ID.
	 * @return {string} Category name.
	 */
	const getCategoryName = ( id ) => {
		const category = categories.find( ( cat ) => cat.id === id );
		return category?.name || id;
	};

	// Filter cookies by category and search term.
	const filteredCookies = useMemo( () => {
		return cookies.filter( ( cookie ) => {
			const matchesCategory = ! filterCategory || cookie.category_id === filterCategory;
			const matchesSearch = ! searchTerm ||
				cookie.name.toLowerCase().includes( searchTerm.toLowerCase() ) ||
				cookie.provider?.toLowerCase().includes( searchTerm.toLowerCase() ) ||
				cookie.purpose?.toLowerCase().includes( searchTerm.toLowerCase() );

			return matchesCategory && matchesSearch;
		} );
	}, [ cookies, filterCategory, searchTerm ] );

	return (
		<div className="cr-cookies-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Cookie Definitions', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Define the cookies used on your website. These will appear in your cookie policy table.', 'consent-raven' ) }
				</p>
			</div>

			{/* Filter Bar */}
			<div className="cr-filter-bar">
				<div className="cr-search-box">
					<TextControl
						label={ __( 'Search', 'consent-raven' ) }
						hideLabelFromVision
						placeholder={ __( 'Search cookies...', 'consent-raven' ) }
						value={ searchTerm }
						onChange={ setSearchTerm }
					/>
				</div>
				<SelectControl
					label={ __( 'Filter by Category', 'consent-raven' ) }
					hideLabelFromVision
					value={ filterCategory }
					options={ categoryOptions }
					onChange={ setFilterCategory }
					__nextHasNoMarginBottom
				/>
				<div className="cr-filter-actions">
					<Button variant="primary" onClick={ addCookie }>
						{ __( 'Add Cookie', 'consent-raven' ) }
					</Button>
				</div>
			</div>

			{/* Bulk Actions Bar */}
			{ selectedCookies.length > 0 && (
				<div className="cr-bulk-bar">
					<span className="cr-bulk-bar__count">
						{ sprintf(
							/* translators: %d: number of selected items */
							__( '%d selected', 'consent-raven' ),
							selectedCookies.length
						) }
					</span>
					<div className="cr-bulk-bar__actions">
						<SelectControl
							label={ __( 'Move to Category', 'consent-raven' ) }
							hideLabelFromVision
							value=""
							options={ [
								{ value: '', label: __( 'Move to...', 'consent-raven' ) },
								...categorySelectOptions,
							] }
							onChange={ changeSelectedCategory }
							__nextHasNoMarginBottom
						/>
						<Button
							variant="secondary"
							isDestructive
							onClick={ deleteSelectedCookies }
						>
							{ __( 'Delete Selected', 'consent-raven' ) }
						</Button>
					</div>
				</div>
			) }

			{ filteredCookies.length === 0 ? (
				<div className="cr-empty-state">
					<div className="cr-empty-state__icon">{ String.fromCodePoint( 0x1F36A ) }</div>
					<h3 className="cr-empty-state__title">
						{ searchTerm || filterCategory
							? __( 'No Cookies Found', 'consent-raven' )
							: __( 'No Cookies', 'consent-raven' )
						}
					</h3>
					<p className="cr-empty-state__description">
						{ searchTerm || filterCategory
							? __( 'Try adjusting your search or filter.', 'consent-raven' )
							: __( 'Add cookie definitions to display in your cookie policy.', 'consent-raven' )
						}
					</p>
				</div>
			) : (
				<table className="cr-data-table">
					<thead>
						<tr>
							<th className="cr-checkbox-cell">
								<CheckboxControl
									checked={ selectedCookies.length === filteredCookies.length && filteredCookies.length > 0 }
									onChange={ toggleAllSelection }
									aria-label={ __( 'Select all', 'consent-raven' ) }
								/>
							</th>
							<th>{ __( 'Cookie Name', 'consent-raven' ) }</th>
							<th>{ __( 'Provider', 'consent-raven' ) }</th>
							<th>{ __( 'Category', 'consent-raven' ) }</th>
							<th>{ __( 'Expiration', 'consent-raven' ) }</th>
							<th>{ __( 'Actions', 'consent-raven' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ filteredCookies.map( ( cookie, filteredIndex ) => {
							// Find original index in unfiltered array.
							const originalIndex = cookies.findIndex(
								( c ) =>
									c.name === cookie.name &&
									c.category_id === cookie.category_id
							);

							return (
								<tr key={ `${ cookie.name }-${ filteredIndex }` }>
									<td className="cr-checkbox-cell">
										<CheckboxControl
											checked={ selectedCookies.includes( originalIndex ) }
											onChange={ () => toggleCookieSelection( originalIndex ) }
											aria-label={ sprintf(
												/* translators: %s: cookie name */
												__( 'Select %s', 'consent-raven' ),
												cookie.name
											) }
										/>
									</td>
									<td>
										<code>{ cookie.name }</code>
										{ cookie.purpose && (
											<>
												<br />
												<small>{ cookie.purpose }</small>
											</>
										) }
									</td>
									<td>{ cookie.provider || '—' }</td>
									<td>{ getCategoryName( cookie.category_id ) }</td>
									<td>{ cookie.expiration || '—' }</td>
									<td className="cr-data-table__actions">
										<Button
											variant="secondary"
											size="small"
											onClick={ () => editCookie( cookie, originalIndex ) }
										>
											{ __( 'Edit', 'consent-raven' ) }
										</Button>
										<Button
											variant="tertiary"
											size="small"
											isDestructive
											onClick={ () => deleteCookie( originalIndex ) }
										>
											{ __( 'Delete', 'consent-raven' ) }
										</Button>
									</td>
								</tr>
							);
						} ) }
					</tbody>
				</table>
			) }

			{/* Cookie count */}
			{ cookies.length > 0 && (
				<p className="cr-helper-text" style={ { marginTop: '16px' } }>
					{ sprintf(
						/* translators: 1: filtered count, 2: total count */
						__( 'Showing %1$d of %2$d cookies', 'consent-raven' ),
						filteredCookies.length,
						cookies.length
					) }
				</p>
			) }

			{ isModalOpen && editingCookie && (
				<Modal
					title={
						editingCookie.isNew
							? __( 'Add Cookie', 'consent-raven' )
							: __( 'Edit Cookie', 'consent-raven' )
					}
					onRequestClose={ () => {
						setIsModalOpen( false );
						setEditingCookie( null );
						setValidationErrors( {} );
					} }
				>
					<div className="cr-form-group">
						<TextControl
							label={ __( 'Cookie Name', 'consent-raven' ) }
							help={ validationErrors.name ? undefined : __( 'Use * as wildcard (e.g., _ga_*)', 'consent-raven' ) }
							value={ editingCookie.name }
							onChange={ ( name ) =>
								setEditingCookie( { ...editingCookie, name } )
							}
							className={ validationErrors.name ? 'has-error' : '' }
						/>
						{ validationErrors.name && (
							<span className="cr-field-error">{ validationErrors.name }</span>
						) }
					</div>

					<div className="cr-form-group">
						<SelectControl
							label={ __( 'Category', 'consent-raven' ) }
							value={ editingCookie.category_id }
							options={ categorySelectOptions }
							onChange={ ( category_id ) =>
								setEditingCookie( { ...editingCookie, category_id } )
							}
						/>
						{ validationErrors.category_id && (
							<span className="cr-field-error">{ validationErrors.category_id }</span>
						) }
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Provider', 'consent-raven' ) }
							help={ __( 'The service or company that sets this cookie.', 'consent-raven' ) }
							value={ editingCookie.provider }
							onChange={ ( provider ) =>
								setEditingCookie( { ...editingCookie, provider } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Purpose', 'consent-raven' ) }
							help={ __( 'A brief description of what this cookie is used for.', 'consent-raven' ) }
							value={ editingCookie.purpose }
							onChange={ ( purpose ) =>
								setEditingCookie( { ...editingCookie, purpose } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Expiration', 'consent-raven' ) }
							help={ __( 'e.g., Session, 1 year, 24 hours', 'consent-raven' ) }
							value={ editingCookie.expiration }
							onChange={ ( expiration ) =>
								setEditingCookie( { ...editingCookie, expiration } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Host / Domain', 'consent-raven' ) }
							help={ __( 'The domain where the cookie is set.', 'consent-raven' ) }
							value={ editingCookie.host }
							onChange={ ( host ) =>
								setEditingCookie( { ...editingCookie, host } )
							}
						/>
					</div>

					<div className="cr-form-group" style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end' } }>
						<Button
							variant="tertiary"
							onClick={ () => {
								setIsModalOpen( false );
								setEditingCookie( null );
								setValidationErrors( {} );
							} }
						>
							{ __( 'Cancel', 'consent-raven' ) }
						</Button>
						<Button variant="primary" onClick={ saveCookie }>
							{ __( 'Save', 'consent-raven' ) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default CookiesPanel;
