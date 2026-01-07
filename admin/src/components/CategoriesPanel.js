/**
 * Consent Raven - Categories Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState } from '@wordpress/element';
import {
	Button,
	TextControl,
	TextareaControl,
	ToggleControl,
	Modal,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Categories Panel component
 *
 * @param {Object}   props               Component props.
 * @param {Array}    props.categories    Current categories.
 * @param {Function} props.setCategories Function to update categories.
 * @return {JSX.Element} Categories panel.
 */
const CategoriesPanel = ( { categories, setCategories } ) => {
	const [ editingCategory, setEditingCategory ] = useState( null );
	const [ isModalOpen, setIsModalOpen ] = useState( false );

	/**
	 * Generate a unique slug from name
	 *
	 * @param {string} name Category name.
	 * @return {string} Generated slug.
	 */
	const generateSlug = ( name ) => {
		return name
			.toLowerCase()
			.replace( /[^a-z0-9]+/g, '-' )
			.replace( /^-|-$/g, '' );
	};

	/**
	 * Add new category
	 */
	const addCategory = () => {
		setEditingCategory( {
			id: '',
			slug: '',
			name: '',
			description: '',
			essential: false,
			isNew: true,
		} );
		setIsModalOpen( true );
	};

	/**
	 * Edit existing category
	 *
	 * @param {Object} category Category to edit.
	 */
	const editCategory = ( category ) => {
		setEditingCategory( { ...category, isNew: false } );
		setIsModalOpen( true );
	};

	/**
	 * Save category
	 */
	const saveCategory = () => {
		if ( ! editingCategory.name || ! editingCategory.slug ) {
			return;
		}

		let updatedCategories;

		if ( editingCategory.isNew ) {
			updatedCategories = [
				...categories,
				{
					id: editingCategory.slug,
					slug: editingCategory.slug,
					name: editingCategory.name,
					description: editingCategory.description,
					essential: editingCategory.essential,
				},
			];
		} else {
			updatedCategories = categories.map( ( cat ) =>
				cat.id === editingCategory.id
					? {
							...cat,
							name: editingCategory.name,
							description: editingCategory.description,
							essential: editingCategory.essential,
					  }
					: cat
			);
		}

		setCategories( updatedCategories );
		setIsModalOpen( false );
		setEditingCategory( null );
	};

	/**
	 * Delete category
	 *
	 * @param {string} id Category ID to delete.
	 */
	const deleteCategory = ( id ) => {
		if ( ! window.confirm( __( 'Are you sure you want to delete this category?', 'consent-raven' ) ) ) {
			return;
		}

		setCategories( categories.filter( ( cat ) => cat.id !== id ) );
	};

	return (
		<div className="cr-categories-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Cookie Categories', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Manage the cookie categories that users can consent to.', 'consent-raven' ) }
				</p>
			</div>

			<div className="cr-form-group">
				<Button variant="primary" onClick={ addCategory }>
					{ __( 'Add Category', 'consent-raven' ) }
				</Button>
			</div>

			{ categories.length === 0 ? (
				<div className="cr-empty-state">
					<div className="cr-empty-state__icon">📁</div>
					<h3 className="cr-empty-state__title">
						{ __( 'No Categories', 'consent-raven' ) }
					</h3>
					<p className="cr-empty-state__description">
						{ __( 'Add your first cookie category to get started.', 'consent-raven' ) }
					</p>
				</div>
			) : (
				<table className="cr-data-table">
					<thead>
						<tr>
							<th>{ __( 'Name', 'consent-raven' ) }</th>
							<th>{ __( 'Slug', 'consent-raven' ) }</th>
							<th>{ __( 'Essential', 'consent-raven' ) }</th>
							<th>{ __( 'Actions', 'consent-raven' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ categories.map( ( category ) => (
							<tr key={ category.id }>
								<td>
									<strong>{ category.name }</strong>
									<br />
									<small>{ category.description }</small>
								</td>
								<td>
									<code>{ category.slug }</code>
								</td>
								<td>{ category.essential ? __( 'Yes', 'consent-raven' ) : __( 'No', 'consent-raven' ) }</td>
								<td className="cr-data-table__actions">
									<Button
										variant="secondary"
										size="small"
										onClick={ () => editCategory( category ) }
									>
										{ __( 'Edit', 'consent-raven' ) }
									</Button>
									{ ! category.essential && (
										<Button
											variant="tertiary"
											size="small"
											isDestructive
											onClick={ () => deleteCategory( category.id ) }
										>
											{ __( 'Delete', 'consent-raven' ) }
										</Button>
									) }
								</td>
							</tr>
						) ) }
					</tbody>
				</table>
			) }

			{ isModalOpen && editingCategory && (
				<Modal
					title={
						editingCategory.isNew
							? __( 'Add Category', 'consent-raven' )
							: __( 'Edit Category', 'consent-raven' )
					}
					onRequestClose={ () => {
						setIsModalOpen( false );
						setEditingCategory( null );
					} }
				>
					<div className="cr-form-group">
						<TextControl
							label={ __( 'Name', 'consent-raven' ) }
							value={ editingCategory.name }
							onChange={ ( name ) => {
								const slug = editingCategory.isNew
									? generateSlug( name )
									: editingCategory.slug;
								setEditingCategory( { ...editingCategory, name, slug } );
							} }
						/>
					</div>

					{ editingCategory.isNew && (
						<div className="cr-form-group">
							<TextControl
								label={ __( 'Slug', 'consent-raven' ) }
								help={ __( 'Unique identifier for this category.', 'consent-raven' ) }
								value={ editingCategory.slug }
								onChange={ ( slug ) =>
									setEditingCategory( { ...editingCategory, slug: generateSlug( slug ) } )
								}
							/>
						</div>
					) }

					<div className="cr-form-group">
						<TextareaControl
							label={ __( 'Description', 'consent-raven' ) }
							value={ editingCategory.description }
							onChange={ ( description ) =>
								setEditingCategory( { ...editingCategory, description } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<ToggleControl
							label={ __( 'Essential Category', 'consent-raven' ) }
							help={ __( 'Essential categories are always enabled and cannot be disabled by users.', 'consent-raven' ) }
							checked={ editingCategory.essential }
							onChange={ ( essential ) =>
								setEditingCategory( { ...editingCategory, essential } )
							}
						/>
					</div>

					<div className="cr-form-group" style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end' } }>
						<Button
							variant="tertiary"
							onClick={ () => {
								setIsModalOpen( false );
								setEditingCategory( null );
							} }
						>
							{ __( 'Cancel', 'consent-raven' ) }
						</Button>
						<Button variant="primary" onClick={ saveCategory }>
							{ __( 'Save', 'consent-raven' ) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default CategoriesPanel;
