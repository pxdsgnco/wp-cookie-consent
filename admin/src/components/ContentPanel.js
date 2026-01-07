/**
 * Consent Raven - Content Panel Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { TextControl, TextareaControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Content Panel component
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.content       Current content settings.
 * @param {Function} props.updateContent Function to update content.
 * @return {JSX.Element} Content panel.
 */
const ContentPanel = ( { content, updateContent } ) => {
	return (
		<div className="cr-content-panel">
			<div className="cr-admin-panel__header">
				<h2 className="cr-admin-panel__title">
					{ __( 'Content Settings', 'consent-raven' ) }
				</h2>
				<p className="cr-admin-panel__description">
					{ __( 'Customize all text displayed in the cookie consent banner.', 'consent-raven' ) }
				</p>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Banner Title', 'consent-raven' ) }
					value={ content.title || '' }
					onChange={ ( title ) => updateContent( { title } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextareaControl
					label={ __( 'Banner Description', 'consent-raven' ) }
					help={ __( 'The main text explaining your cookie usage. Include the policy link text that will be linked to your Cookie Policy page.', 'consent-raven' ) }
					value={ content.description || '' }
					rows={ 4 }
					onChange={ ( description ) => updateContent( { description } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Policy Link Text', 'consent-raven' ) }
					help={ __( 'The text in your description that should link to the Cookie Policy page.', 'consent-raven' ) }
					value={ content.policy_link_text || '' }
					onChange={ ( policy_link_text ) => updateContent( { policy_link_text } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Accept Button Text', 'consent-raven' ) }
					value={ content.accept_button || '' }
					onChange={ ( accept_button ) => updateContent( { accept_button } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Reject Button Text', 'consent-raven' ) }
					value={ content.reject_button || '' }
					onChange={ ( reject_button ) => updateContent( { reject_button } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Customize Button Text', 'consent-raven' ) }
					value={ content.customize_button || '' }
					onChange={ ( customize_button ) => updateContent( { customize_button } ) }
				/>
			</div>

			<div className="cr-form-group">
				<TextControl
					label={ __( 'Save Preferences Button Text', 'consent-raven' ) }
					value={ content.save_button || '' }
					onChange={ ( save_button ) => updateContent( { save_button } ) }
				/>
			</div>
		</div>
	);
};

export default ContentPanel;
