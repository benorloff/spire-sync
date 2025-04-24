import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
// import { store as noticesStore } from '@wordpress/notices';
// import { useDispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';

interface SpireSyncSettings {
    spire_sync_settings: {
        spire_api: {
            base_url: string;
            company_name: string;
            api_username: string;
            api_password: string;
        };
    };
}

interface SpireSyncTestConnectionResponse {
    success: boolean;
    message: string;
}

const useSettings = () => {
    const [ baseUrl, setBaseUrl ] = useState<string>('');
    const [ companyName, setCompanyName ] = useState<string>('');
    const [ apiUsername, setApiUsername ] = useState<string>('');
    const [ apiPassword, setApiPassword ] = useState<string>('');
    const [ isTesting, setIsTesting ] = useState<boolean>( false );
    const [ isSaving, setIsSaving ] = useState<boolean>( false );
    const [ isValidConnection, setIsValidConnection ] = useState<boolean>( false );
    const [ message, setMessage ] = useState<string>( '' );
    const [ wcVersion, setWcVersion ] = useState<string>( '' );
    const [ syncType, setSyncType ] = useState<"create"|"update"|"create-update"|"create-update-delete">( 'update' );

	// const { createSuccessNotice } = useDispatch( noticesStore );

	useEffect( () => {
		apiFetch( { path: '/wp/v2/settings' } ).then( ( response ) => {
			const settings = response as SpireSyncSettings;
			setBaseUrl( settings.spire_sync_settings.spire_api.base_url || '' );
            setCompanyName( settings.spire_sync_settings.spire_api.company_name || '' );
			setApiUsername( settings.spire_sync_settings.spire_api.api_username || '' );
			setApiPassword( settings.spire_sync_settings.spire_api.api_password || '' );
		} );
        apiFetch( { path: '/wc/v3/system_status' } ).then( ( response ) => {
            const system_status = response as any;
            const wcVers = system_status.environment.version ?? '';
            setWcVersion( wcVers );
        })
	}, [] );

    const handleSave = async () => {
        setIsSaving( true );
        setMessage( '' );
        // const testConnection = await handleTestConnection();
        
    }

    const handleTestConnection = async () => {

        setIsTesting( true );
        setMessage( '' );

        let testConnectionResponse = {
            success: false,
            message: '',
        };

        apiFetch( {
            path: '/spire_sync/v1/test-connection',
            method: 'POST',
            data: {
                base_url: baseUrl,
                company_name: companyName,
                api_username: apiUsername,
                api_password: apiPassword,
            },
        } ).then( ( response ) => { 
            testConnectionResponse = response as SpireSyncTestConnectionResponse;
            
            if ( ! testConnectionResponse.success ) {
                setIsTesting( false );
                setIsValidConnection( false );
                setMessage( __( 'Invalid credentials. Please try again.', 'spire-sync' ) );
            } 
            
            setIsTesting( false );
            setIsValidConnection( true );
            setMessage( __( 'Connection successful!', 'spire-sync' ) );
        } ).catch( ( error ) => {
            setIsTesting( false );
            setIsValidConnection( false );
            setMessage( __( 'An error occurred while testing the connection. Please try again later.', 'spire-sync' ) );
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
                spire_sync_settings: {
                    spire_api: {
                        'base_url': baseUrl,
                        'company_name': companyName,
                        'api_username': apiUsername,
                        'api_password': apiPassword,
                    },
                },
			},
		} ).then( (response) => {
            console.log( 'Settings saved:', response );
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
        companyName,
        setCompanyName,
        apiUsername,
        setApiUsername,
        apiPassword,
        setApiPassword,
        isTesting,
        setIsTesting,
        isSaving,
        setIsSaving,
        isValidConnection,
        setIsValidConnection,
        message,
        setMessage,
        handleTestConnection,
		handleSave,
        saveSettings,
        wcVersion,
        syncType,
        setSyncType,
	};
};

export default useSettings;