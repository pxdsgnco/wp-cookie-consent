/**
 * Consent Raven - Cookies Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState } from '@wordpress/element';
import {
	Button,
	TextControl,
	SelectControl,
	Modal,
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
		setIsModalOpen( true );
	};

	/**
	 * Save cookie
	 */
	const saveCookie = () => {
		if ( ! editingCookie.name || ! editingCookie.category_id ) {
			return;
		}

		let updatedCookies;

		if ( editingCookie.isNew ) {
			updatedCookies = [
				...cookies,
				{
					name: editingCookie.name,
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
							name: editingCookie.name,
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

	// Filter cookies by category
	const filteredCookies = filterCategory
		? cookies.filter( ( c ) => c.category_id === filterCategory )
		: cookies;

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

			<div className="cr-form-group" style={ { display: 'flex', gap: '16px', alignItems: 'flex-end' } }>
				<Button variant="primary" onClick={ addCookie }>
					{ __( 'Add Cookie', 'consent-raven' ) }
				</Button>
				<SelectControl
					label={ __( 'Filter by Category', 'consent-raven' ) }
					value={ filterCategory }
					options={ categoryOptions }
					onChange={ setFilterCategory }
				/>
			</div>

			{ filteredCookies.length === 0 ? (
				<div className="cr-empty-state">
					<div className="cr-empty-state__icon">🍪</div>
					<h3 className="cr-empty-state__title">
						{ __( 'No Cookies', 'consent-raven' ) }
					</h3>
					<p className="cr-empty-state__description">
						{ filterCategory
							? __( 'No cookies in this category.', 'consent-raven' )
							: __( 'Add cookie definitions to display in your cookie policy.', 'consent-raven' )
						}
					</p>
				</div>
			) : (
				<table className="cr-data-table">
					<thead>
						<tr>
							<th>{ __( 'Cookie Name', 'consent-raven' ) }</th>
							<th>{ __( 'Provider', 'consent-raven' ) }</th>
							<th>{ __( 'Category', 'consent-raven' ) }</th>
							<th>{ __( 'Expiration', 'consent-raven' ) }</th>
							<th>{ __( 'Actions', 'consent-raven' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ filteredCookies.map( ( cookie, index ) => {
							// Find original index in unfiltered array
							const originalIndex = cookies.findIndex(
								( c ) =>
									c.name === cookie.name &&
									c.category_id === cookie.category_id
							);

							return (
								<tr key={ `${ cookie.name }-${ index }` }>
									<td>
										<code>{ cookie.name }</code>
										<br />
										<small>{ cookie.purpose }</small>
									</td>
									<td>{ cookie.provider }</td>
									<td>{ getCategoryName( cookie.category_id ) }</td>
									<td>{ cookie.expiration }</td>
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
					} }
				>
					<div className="cr-form-group">
						<TextControl
							label={ __( 'Cookie Name', 'consent-raven' ) }
							help={ __( 'Use * as wildcard (e.g., _ga_*)', 'consent-raven' ) }
							value={ editingCookie.name }
							onChange={ ( name ) =>
								setEditingCookie( { ...editingCookie, name } )
							}
						/>
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
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Provider', 'consent-raven' ) }
							value={ editingCookie.provider }
							onChange={ ( provider ) =>
								setEditingCookie( { ...editingCookie, provider } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Purpose', 'consent-raven' ) }
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
