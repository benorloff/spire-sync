import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
// import { store as noticesStore } from '@wordpress/notices';
// import { useDispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';

interface SpireSyncSettings {
    spire_sync: {
        base_url: string;
        api_username: string;
        api_password: string;
    };
}

interface SpireSyncTestConnectionResponse {
    success: boolean;
    message: string;
}

const useSettings = () => {
    const [ baseUrl, setBaseUrl ] = useState<string>('');
    const [ apiUsername, setApiUsername ] = useState<string>('');
    const [ apiPassword, setApiPassword ] = useState<string>('');
    const [ isSaving, setIsSaving ] = useState<boolean>( false );
    const [ isApiConnected, setIsApiConnected ] = useState<boolean>( false );
    const [ message, setMessage ] = useState<string>( '' );

	// const { createSuccessNotice } = useDispatch( noticesStore );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/settings' } ).then( ( response ) => {
			const settings = response as SpireSyncSettings;
			setBaseUrl( settings.spire_sync.base_url || '' );
			setApiUsername( settings.spire_sync.api_username || '' );
			setApiPassword( settings.spire_sync.api_password || '' );
		} );
	}, [] );

    const handleSave = async () => {
        setIsSaving( true );
        setMessage( '' );
        const testConnection = await handleTestConnection();
        
    }

    const handleTestConnection = async () => {
        let testConnectionResponse = {
            success: false,
            message: '',
        };

        apiFetch( {
            path: '/spire/v1/test-connection',
            method: 'POST',
            data: {
                base_url: baseUrl,
                api_username: apiUsername,
                api_password: apiPassword,
            },
        } ).then( ( response ) => { 
            testConnectionResponse = response as SpireSyncTestConnectionResponse;
            
            if ( ! testConnectionResponse.success ) {
                setIsApiConnected( false );
            } 
            
            setIsApiConnected( true );
            saveSettings();
        } ).catch( ( error ) => {
            setIsApiConnected( false );
            setMessage( __( 'Connection failed.', 'spire-sync' ) );
            console.error( error );
        } );
    }

	const saveSettings = async () => {
        setIsSaving( true );
        setMessage( '' );
        // Build the data object to send.
		apiFetch( {
			path: '/wp/v2/settings',
			method: 'POST',
			data: {
                spire_sync: {
                    'base_url': baseUrl,
                    'api_username': apiUsername,
                    'api_password': apiPassword,
                },
			},
		} ).then( () => {
            setMessage( __( 'Settings saved successfully.', 'spire-sync' ) );
            setIsSaving( false );
			// createSuccessNotice(
			// 	__( 'Settings saved.', 'spire-sync' )
			// );
		} ).catch( ( error ) => {
            setMessage( __( 'Error saving settings.', 'spire-sync' ) );
            setIsSaving( false );
            console.error( error );
        } );
	};

	return {
		baseUrl,
        setBaseUrl,
        apiUsername,
        setApiUsername,
        apiPassword,
        setApiPassword,
        isSaving,
        setIsSaving,
        message,
        setMessage,
		saveSettings,
	};
};

export default useSettings;