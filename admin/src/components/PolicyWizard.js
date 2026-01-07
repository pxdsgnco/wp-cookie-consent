/**
 * Consent Raven - Policy Page Generator Wizard
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState } from '@wordpress/element';
import {
	Button,
	Modal,
	TextControl,
	SelectControl,
	CheckboxControl,
	Notice,
	Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

/**
 * Policy Page Generator Wizard
 *
 * @param {Object}   props           Component props.
 * @param {boolean}  props.isOpen    Whether the modal is open.
 * @param {Function} props.onClose   Callback when modal closes.
 * @param {Function} props.onSuccess Callback when page is created successfully.
 * @return {JSX.Element|null} Wizard modal or null.
 */
const PolicyWizard = ( { isOpen, onClose, onSuccess } ) => {
	// State
	const [ step, setStep ] = useState( 1 );
	const [ isCreating, setIsCreating ] = useState( false );
	const [ error, setError ] = useState( null );
	const [ success, setSuccess ] = useState( null );

	// Form state
	const [ pageTitle, setPageTitle ] = useState(
		__( 'Cookie Policy', 'consent-raven' )
	);
	const [ pageTemplate, setPageTemplate ] = useState( 'comprehensive' );
	const [ includeSections, setIncludeSections ] = useState( {
		intro: true,
		whatAreCookies: true,
		howWeUse: true,
		cookieTable: true,
		manageCookies: true,
		thirdParty: true,
		updates: true,
		contact: true,
	} );
	const [ setAsPolicyPage, setSetAsPolicyPage ] = useState( true );

	// Template options
	const templateOptions = [
		{
			value: 'comprehensive',
			label: __( 'Comprehensive Policy', 'consent-raven' ),
		},
		{
			value: 'simple',
			label: __( 'Simple Policy', 'consent-raven' ),
		},
		{
			value: 'table-only',
			label: __( 'Cookie Table Only', 'consent-raven' ),
		},
	];

	// Section labels
	const sectionLabels = {
		intro: __( 'Introduction', 'consent-raven' ),
		whatAreCookies: __( 'What Are Cookies', 'consent-raven' ),
		howWeUse: __( 'How We Use Cookies', 'consent-raven' ),
		cookieTable: __( 'Cookie Details Table', 'consent-raven' ),
		manageCookies: __( 'Managing Your Cookies', 'consent-raven' ),
		thirdParty: __( 'Third-Party Cookies', 'consent-raven' ),
		updates: __( 'Policy Updates', 'consent-raven' ),
		contact: __( 'Contact Information', 'consent-raven' ),
	};

	/**
	 * Generate page content based on template and sections
	 *
	 * @return {string} Page content.
	 */
	const generatePageContent = () => {
		const sections = [];

		if ( pageTemplate === 'table-only' ) {
			sections.push( '<!-- wp:consent-raven/policy-table /-->' );
			return sections.join( '\n\n' );
		}

		if ( includeSections.intro ) {
			sections.push( `<!-- wp:paragraph -->
<p>${ __(
		'This Cookie Policy explains how we use cookies and similar technologies on our website. By continuing to use our website, you consent to the use of cookies as described in this policy.',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->` );
		}

		if ( includeSections.whatAreCookies ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __( 'What Are Cookies?', 'consent-raven' ) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __(
		'Cookies are small text files that are placed on your computer or mobile device when you visit a website. They are widely used to make websites work more efficiently, provide a better user experience, and give website owners information about how the site is being used.',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->` );
		}

		if ( includeSections.howWeUse ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __(
		'How We Use Cookies',
		'consent-raven'
	) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __( 'We use cookies for several purposes:', 'consent-raven' ) }</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>${ __( 'Essential Cookies:', 'consent-raven' ) }</strong> ${ __(
		'These cookies are necessary for the website to function properly. They enable core functionality such as security, network management, and account access.',
		'consent-raven'
	) }</li>
<li><strong>${ __( 'Analytics Cookies:', 'consent-raven' ) }</strong> ${ __(
		'These cookies help us understand how visitors interact with our website by collecting and reporting information anonymously.',
		'consent-raven'
	) }</li>
<li><strong>${ __( 'Marketing Cookies:', 'consent-raven' ) }</strong> ${ __(
		'These cookies are used to track visitors across websites and display ads that are relevant and engaging.',
		'consent-raven'
	) }</li>
</ul>
<!-- /wp:list -->` );
		}

		if ( includeSections.cookieTable ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __(
		'Cookies We Use',
		'consent-raven'
	) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __(
		'Below is a detailed list of the cookies we use on this website:',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->

<!-- wp:consent-raven/policy-table /-->` );
		}

		if ( includeSections.manageCookies ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __(
		'Managing Your Cookie Preferences',
		'consent-raven'
	) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __(
		'You can manage your cookie preferences at any time by clicking the cookie settings button on our website. Additionally, most web browsers allow you to control cookies through their settings:',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul>
<li><strong>${ __( 'Browser Settings:', 'consent-raven' ) }</strong> ${ __(
		'Most browsers allow you to view, delete, and block cookies. Note that disabling cookies may affect website functionality.',
		'consent-raven'
	) }</li>
<li><strong>${ __( 'Opt-Out Links:', 'consent-raven' ) }</strong> ${ __(
		'Some third-party services offer opt-out mechanisms. Visit their websites for more information.',
		'consent-raven'
	) }</li>
</ul>
<!-- /wp:list -->` );
		}

		if ( includeSections.thirdParty ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __(
		'Third-Party Cookies',
		'consent-raven'
	) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __(
		'Some cookies on our website are set by third-party services. We do not control these cookies and recommend reviewing the privacy policies of these third parties for more information about their use of cookies.',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->` );
		}

		if ( includeSections.updates ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __(
		'Updates to This Policy',
		'consent-raven'
	) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __(
		'We may update this Cookie Policy from time to time to reflect changes in technology, legislation, or our data practices. When we make changes, we will update the date at the top of this policy and, where appropriate, notify you through the website.',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->` );
		}

		if ( includeSections.contact ) {
			sections.push( `<!-- wp:heading -->
<h2 class="wp-block-heading">${ __( 'Contact Us', 'consent-raven' ) }</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>${ __(
		'If you have any questions about our use of cookies, please contact us.',
		'consent-raven'
	) }</p>
<!-- /wp:paragraph -->` );
		}

		return sections.join( '\n\n' );
	};

	/**
	 * Handle form submission
	 */
	const handleCreate = async () => {
		setIsCreating( true );
		setError( null );

		try {
			const content = generatePageContent();

			// Create the page
			const response = await apiFetch( {
				path: '/consent-raven/v1/create-policy-page',
				method: 'POST',
				data: {
					title: pageTitle,
					content,
					set_as_policy_page: setAsPolicyPage,
				},
			} );

			setSuccess( {
				message: __( 'Cookie policy page created successfully!', 'consent-raven' ),
				pageId: response.page_id,
				editUrl: response.edit_url,
				viewUrl: response.view_url,
			} );

			if ( onSuccess ) {
				onSuccess( response );
			}

			setStep( 3 ); // Move to success step
		} catch ( err ) {
			setError(
				err.message ||
					__( 'Failed to create cookie policy page.', 'consent-raven' )
			);
		} finally {
			setIsCreating( false );
		}
	};

	/**
	 * Handle template change
	 *
	 * @param {string} template Template value.
	 */
	const handleTemplateChange = ( template ) => {
		setPageTemplate( template );

		// Set default sections based on template
		if ( template === 'comprehensive' ) {
			setIncludeSections( {
				intro: true,
				whatAreCookies: true,
				howWeUse: true,
				cookieTable: true,
				manageCookies: true,
				thirdParty: true,
				updates: true,
				contact: true,
			} );
		} else if ( template === 'simple' ) {
			setIncludeSections( {
				intro: true,
				whatAreCookies: false,
				howWeUse: true,
				cookieTable: true,
				manageCookies: true,
				thirdParty: false,
				updates: false,
				contact: true,
			} );
		} else if ( template === 'table-only' ) {
			setIncludeSections( {
				intro: false,
				whatAreCookies: false,
				howWeUse: false,
				cookieTable: true,
				manageCookies: false,
				thirdParty: false,
				updates: false,
				contact: false,
			} );
		}
	};

	/**
	 * Reset wizard state
	 */
	const handleReset = () => {
		setStep( 1 );
		setPageTitle( __( 'Cookie Policy', 'consent-raven' ) );
		setPageTemplate( 'comprehensive' );
		setIncludeSections( {
			intro: true,
			whatAreCookies: true,
			howWeUse: true,
			cookieTable: true,
			manageCookies: true,
			thirdParty: true,
			updates: true,
			contact: true,
		} );
		setSetAsPolicyPage( true );
		setError( null );
		setSuccess( null );
	};

	/**
	 * Handle modal close
	 */
	const handleClose = () => {
		handleReset();
		onClose();
	};

	if ( ! isOpen ) {
		return null;
	}

	return (
		<Modal
			title={ __( 'Cookie Policy Page Generator', 'consent-raven' ) }
			onRequestClose={ handleClose }
			className="cr-policy-wizard"
		>
			{ error && (
				<Notice status="error" isDismissible onRemove={ () => setError( null ) }>
					{ error }
				</Notice>
			) }

			{ step === 1 && (
				<div className="cr-policy-wizard__step">
					<p className="cr-policy-wizard__intro">
						{ __(
							'Create a cookie policy page for your website. This wizard will generate a comprehensive policy page with all the necessary information about your cookie usage.',
							'consent-raven'
						) }
					</p>

					<TextControl
						label={ __( 'Page Title', 'consent-raven' ) }
						value={ pageTitle }
						onChange={ setPageTitle }
						help={ __(
							'The title for your cookie policy page.',
							'consent-raven'
						) }
					/>

					<SelectControl
						label={ __( 'Template', 'consent-raven' ) }
						value={ pageTemplate }
						options={ templateOptions }
						onChange={ handleTemplateChange }
						help={ __(
							'Choose a template for your policy page.',
							'consent-raven'
						) }
					/>

					<CheckboxControl
						label={ __( 'Set as policy page in banner settings', 'consent-raven' ) }
						checked={ setAsPolicyPage }
						onChange={ setSetAsPolicyPage }
						help={ __(
							'Automatically link this page in the cookie consent banner.',
							'consent-raven'
						) }
					/>

					<div className="cr-policy-wizard__actions">
						<Button variant="tertiary" onClick={ handleClose }>
							{ __( 'Cancel', 'consent-raven' ) }
						</Button>
						<Button
							variant="primary"
							onClick={ () => setStep( 2 ) }
							disabled={ ! pageTitle.trim() }
						>
							{ __( 'Next: Customize Sections', 'consent-raven' ) }
						</Button>
					</div>
				</div>
			) }

			{ step === 2 && (
				<div className="cr-policy-wizard__step">
					<p className="cr-policy-wizard__intro">
						{ __(
							'Select which sections to include in your cookie policy page:',
							'consent-raven'
						) }
					</p>

					<div className="cr-policy-wizard__sections">
						{ Object.entries( sectionLabels ).map( ( [ key, label ] ) => (
							<CheckboxControl
								key={ key }
								label={ label }
								checked={ includeSections[ key ] }
								onChange={ ( checked ) =>
									setIncludeSections( ( prev ) => ( {
										...prev,
										[ key ]: checked,
									} ) )
								}
								disabled={
									pageTemplate === 'table-only' && key !== 'cookieTable'
								}
							/>
						) ) }
					</div>

					<div className="cr-policy-wizard__actions">
						<Button variant="tertiary" onClick={ () => setStep( 1 ) }>
							{ __( 'Back', 'consent-raven' ) }
						</Button>
						<Button
							variant="primary"
							onClick={ handleCreate }
							disabled={ isCreating }
							isBusy={ isCreating }
						>
							{ isCreating
								? __( 'Creating...', 'consent-raven' )
								: __( 'Create Policy Page', 'consent-raven' ) }
						</Button>
					</div>
				</div>
			) }

			{ step === 3 && success && (
				<div className="cr-policy-wizard__step cr-policy-wizard__success">
					<div className="cr-policy-wizard__success-icon">
						<span role="img" aria-label="success">
							&#10004;
						</span>
					</div>
					<h3>{ success.message }</h3>
					<p>
						{ __(
							'Your cookie policy page has been created and is ready to be published.',
							'consent-raven'
						) }
					</p>

					<div className="cr-policy-wizard__success-actions">
						<Button
							variant="secondary"
							href={ success.editUrl }
							target="_blank"
						>
							{ __( 'Edit Page', 'consent-raven' ) }
						</Button>
						<Button
							variant="secondary"
							href={ success.viewUrl }
							target="_blank"
						>
							{ __( 'Preview Page', 'consent-raven' ) }
						</Button>
					</div>

					<div className="cr-policy-wizard__actions">
						<Button variant="primary" onClick={ handleClose }>
							{ __( 'Done', 'consent-raven' ) }
						</Button>
					</div>
				</div>
			) }
		</Modal>
	);
};

export default PolicyWizard;
