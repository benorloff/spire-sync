import React from 'react';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow, TextControl, Button, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { more } from '@wordpress/icons';

interface SettingsData {
	base_url?: string;
	api_username?: string;
	// We do not preload the password for security reasons.
}

const Settings: React.FC = () => {
	const [ baseUrl, setBaseUrl ] = useState<string>('');
	const [ apiUsername, setApiUsername ] = useState<string>('');
	const [ apiPassword, setApiPassword ] = useState<string>('');
	const [ isSaving, setIsSaving ] = useState<boolean>( false );
	const [ message, setMessage ] = useState<string>('');

	// Preload settings from a localized global variable.
	// useEffect( () => {
	// 	if ( typeof spireSyncSettings !== 'undefined' && spireSyncSettings.settings ) {
	// 		const settings: SettingsData = spireSyncSettings.settings;
	// 		setBaseUrl( settings.base_url || '' );
	// 		setApiUsername( settings.api_username || '' );
	// 	}
	// }, [] );

	const handleSave = () => {
		setIsSaving( true );
		setMessage('');
		// Build the data object to send.
		const data = {
			api_username: apiUsername,
			api_password: apiPassword,
			base_url: baseUrl,
		};

		// Use apiFetch to make a POST request to your custom REST endpoint.
		apiFetch( {
			path: '/spire_sync/v1/settings',
			method: 'POST',
			data,
		} )
			.then( ( response ) => {
				setMessage( __( 'Settings saved successfully.', 'spire-sync' ) );
				setIsSaving( false );
				setApiPassword( '' ); // Optionally clear the password field.
			} )
			.catch( ( error ) => {
				console.error( error );
				setMessage( __( 'Error saving settings.', 'spire-sync' ) );
				setIsSaving( false );
			} );
	};

	return (
		<div className="spire-sync-settings-container">
			<h1>{ __( 'Spire Sync Settings', 'spire-sync' ) }</h1>
			{ message && <Notice status="success" isDismissible={ false }>{ message }</Notice> }
			<Panel header="Settings">
				<React.Fragment key="0">
				<PanelBody title={ __( 'API Settings', 'spire-sync' ) } initialOpen={ true } icon={ more }>
					<PanelRow>
						<TextControl
							__next40pxDefaultSize
							label={ __( 'Base URL', 'spire-sync' ) }
							value={ baseUrl }
							onChange={ ( val: string | undefined ) => setBaseUrl( val || '' ) }
							help={ __( 'Enter the base URL for the Spire API (e.g., http://example.com/api/v2)', 'spire-sync' ) }
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							__next40pxDefaultSize
							label={ __( 'API Username', 'spire-sync' ) }
							value={ apiUsername }
							onChange={ ( val: string | undefined ) => setApiUsername( val || '' ) }
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							__next40pxDefaultSize
							label={ __( 'API Password', 'spire-sync' ) }
							type="password"
							value={ apiPassword }
							onChange={ ( val: string | undefined ) => setApiPassword( val || '' ) }
						/>
					</PanelRow>
				</PanelBody>
				</React.Fragment>
			</Panel>
			<Button variant='primary' onClick={ handleSave } disabled={ isSaving }>
				{ isSaving ? __( 'Saving...', 'spire-sync' ) : __( 'Save Settings', 'spire-sync' ) }
			</Button>
		</div>
	);
};

export default Settings;