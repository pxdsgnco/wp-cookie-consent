/**
 * Consent Raven - Scripts Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState, useMemo } from '@wordpress/element';
import {
	Button,
	TextControl,
	SelectControl,
	TextareaControl,
	Modal,
	CheckboxControl,
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
	const [ searchTerm, setSearchTerm ] = useState( '' );
	const [ filterCategory, setFilterCategory ] = useState( '' );
	const [ selectedScripts, setSelectedScripts ] = useState( [] );
	const [ validationErrors, setValidationErrors ] = useState( {} );

	// Filter out essential categories (they don't need blocking).
	const nonEssentialCategories = categories.filter( ( cat ) => ! cat.essential );

	const categoryOptions = [
		{ value: '', label: __( 'All Categories', 'consent-raven' ) },
		...nonEssentialCategories.map( ( cat ) => ( {
			value: cat.id,
			label: cat.name,
		} ) ),
	];

	const categorySelectOptions = nonEssentialCategories.map( ( cat ) => ( {
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
	 * Validate script fields
	 *
	 * @param {Object} script Script to validate.
	 * @return {Object} Validation errors.
	 */
	const validateScript = ( script ) => {
		const errors = {};

		if ( ! script.category_id ) {
			errors.category_id = __( 'Please select a category.', 'consent-raven' );
		}

		if ( ! script.handle && ! script.pattern && ! script.script ) {
			errors.script = __( 'Please provide at least a handle, URL pattern, or inline script.', 'consent-raven' );
		}

		return errors;
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
		setValidationErrors( {} );
		setIsModalOpen( true );
	};

	/**
	 * Edit existing script
	 *
	 * @param {Object} script Script to edit.
	 */
	const editScript = ( script ) => {
		setEditingScript( { ...script, isNew: false } );
		setValidationErrors( {} );
		setIsModalOpen( true );
	};

	/**
	 * Save script
	 */
	const saveScript = () => {
		const errors = validateScript( editingScript );
		setValidationErrors( errors );

		if ( Object.keys( errors ).length > 0 ) {
			return;
		}

		let updatedScripts;

		if ( editingScript.isNew ) {
			updatedScripts = [
				...scripts,
				{
					id: editingScript.id,
					category_id: editingScript.category_id,
					handle: editingScript.handle.trim(),
					pattern: editingScript.pattern.trim(),
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
							handle: editingScript.handle.trim(),
							pattern: editingScript.pattern.trim(),
							method: editingScript.method,
							script: editingScript.script,
					  }
					: s
			);
		}

		setScripts( updatedScripts );
		setIsModalOpen( false );
		setEditingScript( null );
		setValidationErrors( {} );
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
		setSelectedScripts( selectedScripts.filter( ( scriptId ) => scriptId !== id ) );
	};

	/**
	 * Delete selected scripts
	 */
	const deleteSelectedScripts = () => {
		if ( selectedScripts.length === 0 ) {
			return;
		}

		if ( ! window.confirm(
			/* translators: %d: number of scripts */
			sprintf( __( 'Are you sure you want to delete %d script(s)?', 'consent-raven' ), selectedScripts.length )
		) ) {
			return;
		}

		setScripts( scripts.filter( ( s ) => ! selectedScripts.includes( s.id ) ) );
		setSelectedScripts( [] );
	};

	/**
	 * Change category for selected scripts
	 *
	 * @param {string} categoryId New category ID.
	 */
	const changeSelectedCategory = ( categoryId ) => {
		if ( selectedScripts.length === 0 || ! categoryId ) {
			return;
		}

		const updatedScripts = scripts.map( ( script ) =>
			selectedScripts.includes( script.id )
				? { ...script, category_id: categoryId }
				: script
		);

		setScripts( updatedScripts );
		setSelectedScripts( [] );
	};

	/**
	 * Toggle script selection
	 *
	 * @param {string} id Script ID.
	 */
	const toggleScriptSelection = ( id ) => {
		setSelectedScripts( ( prev ) =>
			prev.includes( id )
				? prev.filter( ( scriptId ) => scriptId !== id )
				: [ ...prev, id ]
		);
	};

	/**
	 * Toggle all scripts selection
	 */
	const toggleAllSelection = () => {
		if ( selectedScripts.length === filteredScripts.length ) {
			setSelectedScripts( [] );
		} else {
			setSelectedScripts( filteredScripts.map( ( s ) => s.id ) );
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

	/**
	 * Get script display name
	 *
	 * @param {Object} script Script object.
	 * @return {string} Display name.
	 */
	const getScriptDisplayName = ( script ) => {
		if ( script.handle ) {
			return script.handle;
		}
		if ( script.pattern ) {
			return script.pattern.length > 30 ? script.pattern.substring( 0, 30 ) + '...' : script.pattern;
		}
		return __( 'Inline Script', 'consent-raven' );
	};

	// Filter scripts by category and search term.
	const filteredScripts = useMemo( () => {
		return scripts.filter( ( script ) => {
			const matchesCategory = ! filterCategory || script.category_id === filterCategory;
			const matchesSearch = ! searchTerm ||
				script.handle?.toLowerCase().includes( searchTerm.toLowerCase() ) ||
				script.pattern?.toLowerCase().includes( searchTerm.toLowerCase() ) ||
				script.script?.toLowerCase().includes( searchTerm.toLowerCase() );

			return matchesCategory && matchesSearch;
		} );
	}, [ scripts, filterCategory, searchTerm ] );

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

			{/* Filter Bar */}
			<div className="cr-filter-bar">
				<div className="cr-search-box">
					<TextControl
						label={ __( 'Search', 'consent-raven' ) }
						hideLabelFromVision
						placeholder={ __( 'Search scripts...', 'consent-raven' ) }
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
					<Button variant="primary" onClick={ addScript }>
						{ __( 'Add Script', 'consent-raven' ) }
					</Button>
				</div>
			</div>

			{/* Bulk Actions Bar */}
			{ selectedScripts.length > 0 && (
				<div className="cr-bulk-bar">
					<span className="cr-bulk-bar__count">
						{ sprintf(
							/* translators: %d: number of selected items */
							__( '%d selected', 'consent-raven' ),
							selectedScripts.length
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
							onClick={ deleteSelectedScripts }
						>
							{ __( 'Delete Selected', 'consent-raven' ) }
						</Button>
					</div>
				</div>
			) }

			{ filteredScripts.length === 0 ? (
				<div className="cr-empty-state">
					<div className="cr-empty-state__icon">{ String.fromCodePoint( 0x1F4DC ) }</div>
					<h3 className="cr-empty-state__title">
						{ searchTerm || filterCategory
							? __( 'No Scripts Found', 'consent-raven' )
							: __( 'No Scripts Configured', 'consent-raven' )
						}
					</h3>
					<p className="cr-empty-state__description">
						{ searchTerm || filterCategory
							? __( 'Try adjusting your search or filter.', 'consent-raven' )
							: __( 'Add scripts that should be blocked until user consent is given.', 'consent-raven' )
						}
					</p>
				</div>
			) : (
				<table className="cr-data-table">
					<thead>
						<tr>
							<th className="cr-checkbox-cell">
								<CheckboxControl
									checked={ selectedScripts.length === filteredScripts.length && filteredScripts.length > 0 }
									onChange={ toggleAllSelection }
									aria-label={ __( 'Select all', 'consent-raven' ) }
								/>
							</th>
							<th>{ __( 'Script', 'consent-raven' ) }</th>
							<th>{ __( 'Category', 'consent-raven' ) }</th>
							<th>{ __( 'Method', 'consent-raven' ) }</th>
							<th>{ __( 'Actions', 'consent-raven' ) }</th>
						</tr>
					</thead>
					<tbody>
						{ filteredScripts.map( ( script ) => (
							<tr key={ script.id }>
								<td className="cr-checkbox-cell">
									<CheckboxControl
										checked={ selectedScripts.includes( script.id ) }
										onChange={ () => toggleScriptSelection( script.id ) }
										aria-label={ sprintf(
											/* translators: %s: script name */
											__( 'Select %s', 'consent-raven' ),
											getScriptDisplayName( script )
										) }
									/>
								</td>
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
											<br />
											<small style={ { color: '#666' } }>
												{ script.script.length > 50
													? script.script.substring( 0, 50 ) + '...'
													: script.script
												}
											</small>
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

			{/* Script count */}
			{ scripts.length > 0 && (
				<p className="cr-helper-text" style={ { marginTop: '16px' } }>
					{ sprintf(
						/* translators: 1: filtered count, 2: total count */
						__( 'Showing %1$d of %2$d scripts', 'consent-raven' ),
						filteredScripts.length,
						scripts.length
					) }
				</p>
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
						setValidationErrors( {} );
					} }
				>
					<div className="cr-form-group">
						<SelectControl
							label={ __( 'Category', 'consent-raven' ) }
							help={ validationErrors.category_id ? undefined : __( 'Scripts in this category will be blocked until consent is given.', 'consent-raven' ) }
							value={ editingScript.category_id }
							options={ categorySelectOptions }
							onChange={ ( category_id ) =>
								setEditingScript( { ...editingScript, category_id } )
							}
						/>
						{ validationErrors.category_id && (
							<span className="cr-field-error">{ validationErrors.category_id }</span>
						) }
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
							help={ validationErrors.script ? undefined : __( 'JavaScript code to execute when consent is given for this category.', 'consent-raven' ) }
							value={ editingScript.script }
							rows={ 6 }
							onChange={ ( script ) =>
								setEditingScript( { ...editingScript, script } )
							}
						/>
						{ validationErrors.script && (
							<span className="cr-field-error">{ validationErrors.script }</span>
						) }
					</div>

					<div className="cr-form-group" style={ { display: 'flex', gap: '8px', justifyContent: 'flex-end' } }>
						<Button
							variant="tertiary"
							onClick={ () => {
								setIsModalOpen( false );
								setEditingScript( null );
								setValidationErrors( {} );
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
