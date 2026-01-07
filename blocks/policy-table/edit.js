/**
 * Consent Raven - Cookie Policy Table Block Editor
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, SelectControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Edit component for the Cookie Policy Table block
 *
 * @param {Object}   props               Block props.
 * @param {Object}   props.attributes    Block attributes.
 * @param {Function} props.setAttributes Function to set attributes.
 * @return {JSX.Element} Block edit component.
 */
const Edit = ( { attributes, setAttributes } ) => {
	const {
		showCategory,
		showProvider,
		showExpiration,
		showHost,
		filterCategory,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'cr-policy-table-block',
	} );

	// Get categories from localized data if available.
	const categories = window.consentRavenAdmin?.categories || [];
	const categoryOptions = [
		{ value: '', label: __( 'All Categories', 'consent-raven' ) },
		...categories.map( ( cat ) => ( {
			value: cat.id,
			label: cat.name,
		} ) ),
	];

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Display Settings', 'consent-raven' ) }>
					<ToggleControl
						label={ __( 'Show Category Column', 'consent-raven' ) }
						checked={ showCategory }
						onChange={ ( value ) =>
							setAttributes( { showCategory: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Provider Column', 'consent-raven' ) }
						checked={ showProvider }
						onChange={ ( value ) =>
							setAttributes( { showProvider: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Expiration Column', 'consent-raven' ) }
						checked={ showExpiration }
						onChange={ ( value ) =>
							setAttributes( { showExpiration: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show Host Column', 'consent-raven' ) }
						checked={ showHost }
						onChange={ ( value ) =>
							setAttributes( { showHost: value } )
						}
					/>
				</PanelBody>
				<PanelBody title={ __( 'Filter', 'consent-raven' ) }>
					<SelectControl
						label={ __( 'Filter by Category', 'consent-raven' ) }
						value={ filterCategory }
						options={ categoryOptions }
						onChange={ ( value ) =>
							setAttributes( { filterCategory: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div className="cr-policy-table-block__preview">
					<table className="cr-policy-table">
						<thead>
							<tr>
								<th>{ __( 'Cookie', 'consent-raven' ) }</th>
								{ showCategory && <th>{ __( 'Category', 'consent-raven' ) }</th> }
								{ showProvider && <th>{ __( 'Provider', 'consent-raven' ) }</th> }
								<th>{ __( 'Purpose', 'consent-raven' ) }</th>
								{ showExpiration && <th>{ __( 'Expiration', 'consent-raven' ) }</th> }
								{ showHost && <th>{ __( 'Host', 'consent-raven' ) }</th> }
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colSpan={ 3 + ( showCategory ? 1 : 0 ) + ( showProvider ? 1 : 0 ) + ( showExpiration ? 1 : 0 ) + ( showHost ? 1 : 0 ) }>
									<em>{ __( 'Cookie table will be rendered here.', 'consent-raven' ) }</em>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</>
	);
};

export default Edit;
