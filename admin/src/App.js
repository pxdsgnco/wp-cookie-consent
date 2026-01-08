/**
 * Consent Raven - Main Admin App Component
 *
 * @package Consent_Raven
 * @since 1.0.0
 */

import { useState, useEffect, useCallback } from '@wordpress/element';
import { TabPanel, Button, Spinner, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

import SettingsPanel from './components/SettingsPanel';
import AppearancePanel from './components/AppearancePanel';
import ContentPanel from './components/ContentPanel';
import CategoriesPanel from './components/CategoriesPanel';
import CookiesPanel from './components/CookiesPanel';
import ScriptsPanel from './components/ScriptsPanel';
import ToolsPanel from './components/ToolsPanel';
import ConsentLogsPanel from './components/ConsentLogsPanel';

/**
 * Main App component
 *
 * @param {Object} props Component props.
 * @param {string} props.initialTab Initial tab to display.
 * @return {JSX.Element} App component.
 */
const App = ( { initialTab = 'settings' } ) => {
	// State
	const [ settings, setSettings ] = useState( window.consentRavenAdmin?.settings || {} );
	const [ categories, setCategories ] = useState( window.consentRavenAdmin?.categories || [] );
	const [ cookies, setCookies ] = useState( window.consentRavenAdmin?.cookies || [] );
	const [ scripts, setScripts ] = useState( window.consentRavenAdmin?.scripts || [] );
	const [ isLoading, setIsLoading ] = useState( false );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ notice, setNotice ] = useState( null );
	const [ hasChanges, setHasChanges ] = useState( false );

	// Tab definitions
	const tabs = [
		{
			name: 'settings',
			title: __( 'General', 'consent-raven' ),
			className: 'cr-tab-settings',
		},
		{
			name: 'appearance',
			title: __( 'Appearance', 'consent-raven' ),
			className: 'cr-tab-appearance',
		},
		{
			name: 'content',
			title: __( 'Content', 'consent-raven' ),
			className: 'cr-tab-content',
		},
		{
			name: 'categories',
			title: __( 'Categories', 'consent-raven' ),
			className: 'cr-tab-categories',
		},
		{
			name: 'cookies',
			title: __( 'Cookies', 'consent-raven' ),
			className: 'cr-tab-cookies',
		},
		{
			name: 'scripts',
			title: __( 'Scripts', 'consent-raven' ),
			className: 'cr-tab-scripts',
		},
		{
			name: 'tools',
			title: __( 'Tools', 'consent-raven' ),
			className: 'cr-tab-tools',
		},
		{
			name: 'logs',
			title: __( 'Consent Logs', 'consent-raven' ),
			className: 'cr-tab-logs',
		},
	];

	/**
	 * Update settings
	 *
	 * @param {Object} newSettings New settings to merge.
	 */
	const updateSettings = useCallback( ( newSettings ) => {
		setSettings( ( prev ) => ( { ...prev, ...newSettings } ) );
		setHasChanges( true );
	}, [] );

	/**
	 * Update appearance settings
	 *
	 * @param {Object} newAppearance New appearance settings to merge.
	 */
	const updateAppearance = useCallback( ( newAppearance ) => {
		setSettings( ( prev ) => ( {
			...prev,
			appearance: { ...prev.appearance, ...newAppearance },
		} ) );
		setHasChanges( true );
	}, [] );

	/**
	 * Update content settings
	 *
	 * @param {Object} newContent New content settings to merge.
	 */
	const updateContent = useCallback( ( newContent ) => {
		setSettings( ( prev ) => ( {
			...prev,
			content: { ...prev.content, ...newContent },
		} ) );
		setHasChanges( true );
	}, [] );

	/**
	 * Save all settings
	 */
	const saveSettings = async () => {
		setIsSaving( true );
		setNotice( null );

		try {
			// Save main settings
			await apiFetch( {
				path: '/consent-raven/v1/settings',
				method: 'POST',
				data: settings,
			} );

			// Save categories
			await apiFetch( {
				path: '/consent-raven/v1/categories',
				method: 'POST',
				data: categories,
			} );

			// Save cookies
			await apiFetch( {
				path: '/consent-raven/v1/cookies',
				method: 'POST',
				data: cookies,
			} );

			// Save scripts
			await apiFetch( {
				path: '/consent-raven/v1/scripts',
				method: 'POST',
				data: scripts,
			} );

			setNotice( {
				status: 'success',
				message: __( 'Settings saved successfully!', 'consent-raven' ),
			} );
			setHasChanges( false );
		} catch ( error ) {
			setNotice( {
				status: 'error',
				message: error.message || __( 'Error saving settings.', 'consent-raven' ),
			} );
		} finally {
			setIsSaving( false );
		}
	};

	/**
	 * Render tab content
	 *
	 * @param {Object} tab Tab object.
	 * @return {JSX.Element} Tab content.
	 */
	const renderTabContent = ( tab ) => {
		switch ( tab.name ) {
			case 'settings':
				return (
					<SettingsPanel
						settings={ settings }
						updateSettings={ updateSettings }
						pages={ window.consentRavenAdmin?.pages || [] }
					/>
				);
			case 'appearance':
				return (
					<AppearancePanel
						appearance={ settings.appearance || {} }
						updateAppearance={ updateAppearance }
						settings={ settings }
						content={ settings.content || {} }
						categories={ categories }
					/>
				);
			case 'content':
				return (
					<ContentPanel
						content={ settings.content || {} }
						updateContent={ updateContent }
					/>
				);
			case 'categories':
				return (
					<CategoriesPanel
						categories={ categories }
						setCategories={ ( newCategories ) => {
							setCategories( newCategories );
							setHasChanges( true );
						} }
					/>
				);
			case 'cookies':
				return (
					<CookiesPanel
						cookies={ cookies }
						setCookies={ ( newCookies ) => {
							setCookies( newCookies );
							setHasChanges( true );
						} }
						categories={ categories }
					/>
				);
			case 'scripts':
				return (
					<ScriptsPanel
						scripts={ scripts }
						setScripts={ ( newScripts ) => {
							setScripts( newScripts );
							setHasChanges( true );
						} }
						categories={ categories }
					/>
				);
			case 'tools':
				return (
					<ToolsPanel
						settings={ settings }
						categories={ categories }
						cookies={ cookies }
						scripts={ scripts }
						onImport={ ( data ) => {
							if ( data.settings ) {
								setSettings( data.settings );
							}
							if ( data.categories ) {
								setCategories( data.categories );
							}
							if ( data.cookies ) {
								setCookies( data.cookies );
							}
							if ( data.scripts ) {
								setScripts( data.scripts );
							}
						} }
					/>
				);
			case 'logs':
				return <ConsentLogsPanel />;
			default:
				return null;
		}
	};

	// Find initial tab index
	const initialTabName = tabs.find( ( t ) => t.name === initialTab )?.name || 'settings';

	return (
		<div className="cr-admin-app">
			<div className="cr-admin-header">
				<h1>{ __( 'Consent Raven', 'consent-raven' ) }</h1>
				<div className="cr-admin-header__actions">
					<Button
						variant="primary"
						onClick={ saveSettings }
						disabled={ isSaving || ! hasChanges }
						isBusy={ isSaving }
					>
						{ isSaving
							? __( 'Saving...', 'consent-raven' )
							: __( 'Save Changes', 'consent-raven' )
						}
					</Button>
				</div>
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

			<TabPanel
				className="cr-admin-tabs"
				tabs={ tabs }
				initialTabName={ initialTabName }
			>
				{ ( tab ) => (
					<div className="cr-admin-panel">
						{ renderTabContent( tab ) }
					</div>
				) }
			</TabPanel>

			{ hasChanges && (
				<div className="cr-save-bar">
					<span className="cr-save-bar__message">
						{ __( 'You have unsaved changes.', 'consent-raven' ) }
					</span>
					<Button
						variant="primary"
						onClick={ saveSettings }
						disabled={ isSaving }
						isBusy={ isSaving }
					>
						{ isSaving
							? __( 'Saving...', 'consent-raven' )
							: __( 'Save Changes', 'consent-raven' )
						}
					</Button>
				</div>
			) }
		</div>
	);
};

export default App;
