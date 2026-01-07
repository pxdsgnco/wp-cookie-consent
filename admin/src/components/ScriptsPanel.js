/**
 * Consent Raven - Scripts Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState } from '@wordpress/element';
import {
	Button,
	TextControl,
	SelectControl,
	TextareaControl,
	Modal,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Scripts Panel component
 *
 * @param {Object}   props            Component props.
 * @param {Array}    props.scripts    Current scripts.
 * @param {Function} props.setScripts Function to update scripts.
 * @param {Array}    props.categories Available categories.
 * @return {JSX.Element} Scripts panel.
 */
const ScriptsPanel = ( { scripts, setScripts, categories } ) => {
	const [ editingScript, setEditingScript ] = useState( null );
	const [ isModalOpen, setIsModalOpen ] = useState( false );

	// Filter out essential categories (they don't need blocking)
	const nonEssentialCategories = categories.filter( ( cat ) => ! cat.essential );

	const categoryOptions = nonEssentialCategories.map( ( cat ) => ( {
		value: cat.id,
		label: cat.name,
	} ) );

	const methodOptions = [
		{ value: 'type-swap', label: __( 'Type Swap (Recommended)', 'consent-raven' ) },
		{ value: 'data-attribute', label: __( 'Data Attribute', 'consent-raven' ) },
	];

	/**
	 * Generate unique ID
	 *
	 * @return {string} Unique ID.
	 */
	const generateId = () => {
		return 'script_' + Math.random().toString( 36 ).substr( 2, 9 );
	};

	/**
	 * Add new script
	 */
	const addScript = () => {
		setEditingScript( {
			id: generateId(),
			category_id: nonEssentialCategories[ 0 ]?.id || 'analytics',
			handle: '',
			pattern: '',
			method: 'type-swap',
			script: '',
			isNew: true,
		} );
		setIsModalOpen( true );
	};

	/**
	 * Edit existing script
	 *
	 * @param {Object} script Script to edit.
	 */
	const editScript = ( script ) => {
		setEditingScript( { ...script, isNew: false } );
		setIsModalOpen( true );
	};

	/**
	 * Save script
	 */
	const saveScript = () => {
		if ( ! editingScript.category_id ) {
			return;
		}

		if ( ! editingScript.handle && ! editingScript.pattern && ! editingScript.script ) {
			return;
		}

		let updatedScripts;

		if ( editingScript.isNew ) {
			updatedScripts = [
				...scripts,
				{
					id: editingScript.id,
					category_id: editingScript.category_id,
					handle: editingScript.handle,
					pattern: editingScript.pattern,
					method: editingScript.method,
					script: editingScript.script,
				},
			];
		} else {
			updatedScripts = scripts.map( ( s ) =>
				s.id === editingScript.id
					? {
							id: editingScript.id,
							category_id: editingScript.category_id,
							handle: editingScript.handle,
							pattern: editingScript.pattern,
							method: editingScript.method,
							script: editingScript.script,
					  }
					: s
			);
		}

		setScripts( updatedScripts );
		setIsModalOpen( false );
		setEditingScript( null );
	};

	/**
	 * Delete script
	 *
	 * @param {string} id Script ID to delete.
	 */
	const deleteScript = ( id ) => {
		if ( ! window.confirm( __( 'Are you sure you want to delete this script?', 'consent-raven' ) ) ) {
			return;
		}

		setScripts( scripts.filter( ( s ) => s.id !== id ) );
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

	/**
	 * Get method label
	 *
	 * @param {string} method Method value.
	 * @return {string} Method label.
	 */
	const getMethodLabel = ( method ) => {
		const option = methodOptions.find( ( opt ) => opt.value === method );
		return option?.label || method;
	};

	return (
		<div className="cr-scripts-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Script Blocking', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Configure which scripts should be blocked until consent is given.', 'consent-raven' ) }
				</p>
			</div>

			<div className="cr-form-group">
				<Button variant="primary" onClick={ addScript }>
					{ __( 'Add Script', 'consent-raven' ) }
				</Button>
			</div>

			{ scripts.length === 0 ? (
				<div className="cr-empty-state">
					<div className="cr-empty-state__icon">📜</div>
					<h3 className="cr-empty-state__title">
						{ __( 'No Scripts Configured', 'consent-raven' ) }
					</h3>
					<p className="cr-empty-state__description">
						{ __( 'Add scripts that should be blocked until user consent is given.', 'consent-raven' ) }
					</p>
				</div>
			) : (
				<table className="cr-data-table">
					<thead>
						<tr>
							<th>{ __( 'Script', 'consent-raven' ) }</th>
							<th>{ __( 'Category', 'consent-raven' ) }</th>
							<th>{ __( 'Method', 'consent-raven' ) }</th>
							<th>{ __( 'Actions', 'consent-raven' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ scripts.map( ( script ) => (
							<tr key={ script.id }>
								<td>
									{ script.handle && (
										<>
											<strong>{ __( 'Handle:', 'consent-raven' ) }</strong>{ ' ' }
											<code>{ script.handle }</code>
											<br />
										</>
									) }
									{ script.pattern && (
										<>
											<strong>{ __( 'Pattern:', 'consent-raven' ) }</strong>{ ' ' }
											<code>{ script.pattern }</code>
											<br />
										</>
									) }
									{ script.script && (
										<>
											<strong>{ __( 'Inline Script', 'consent-raven' ) }</strong>
										</>
									) }
								</td>
								<td>{ getCategoryName( script.category_id ) }</td>
								<td>{ getMethodLabel( script.method ) }</td>
								<td className="cr-data-table__actions">
									<Button
										variant="secondary"
										size="small"
										onClick={ () => editScript( script ) }
									>
										{ __( 'Edit', 'consent-raven' ) }
									</Button>
									<Button
										variant="tertiary"
										size="small"
										isDestructive
										onClick={ () => deleteScript( script.id ) }
									>
										{ __( 'Delete', 'consent-raven' ) }
									</Button>
								</td>
							</tr>
						) ) }
					</tbody>
				</table>
			) }

			{ isModalOpen && editingScript && (
				<Modal
					title={
						editingScript.isNew
							? __( 'Add Script', 'consent-raven' )
							: __( 'Edit Script', 'consent-raven' )
					}
					onRequestClose={ () => {
						setIsModalOpen( false );
						setEditingScript( null );
					} }
				>
					<div className="cr-form-group">
						<SelectControl
							label={ __( 'Category', 'consent-raven' ) }
							help={ __( 'Scripts in this category will be blocked until consent is given.', 'consent-raven' ) }
							value={ editingScript.category_id }
							options={ categoryOptions }
							onChange={ ( category_id ) =>
								setEditingScript( { ...editingScript, category_id } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<SelectControl
							label={ __( 'Blocking Method', 'consent-raven' ) }
							help={ __( 'Type Swap changes script type to text/plain. Data Attribute adds a data-cookie-category attribute.', 'consent-raven' ) }
							value={ editingScript.method }
							options={ methodOptions }
							onChange={ ( method ) =>
								setEditingScript( { ...editingScript, method } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'Script Handle', 'consent-raven' ) }
							help={ __( 'WordPress script handle (e.g., google-analytics).', 'consent-raven' ) }
							value={ editingScript.handle }
							onChange={ ( handle ) =>
								setEditingScript( { ...editingScript, handle } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextControl
							label={ __( 'URL Pattern', 'consent-raven' ) }
							help={ __( 'Regex pattern to match script URLs (e.g., googletagmanager|google-analytics).', 'consent-raven' ) }
							value={ editingScript.pattern }
							onChange={ ( pattern ) =>
								setEditingScript( { ...editingScript, pattern } )
							}
						/>
					</div>

					<div className="cr-form-group">
						<TextareaControl
							label={ __( 'Inline Script', 'consent-raven' ) }
							help={ __( 'JavaScript code to execute when consent is given for this category.', 'consent-raven' ) }
							value={ editingScript.script }
							rows={ 6 }
							onChange={ ( script ) =>
								setEditingScript( { ...editingScript, script } )
							}
						/>
					</div>

					<div className="cr-form-group" style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end' } }>
						<Button
							variant="tertiary"
							onClick={ () => {
								setIsModalOpen( false );
								setEditingScript( null );
							} }
						>
							{ __( 'Cancel', 'consent-raven' ) }
						</Button>
						<Button variant="primary" onClick={ saveScript }>
							{ __( 'Save', 'consent-raven' ) }
						</Button>
					</div>
				</Modal>
			) }
		</div>
	);
};

export default ScriptsPanel;
