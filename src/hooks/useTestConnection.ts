import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';

const useTestConnection = (
    baseUrl: string,
    apiUsername: string,
    apiPassword: string
) => {
    const [ isTesting, setIsTesting ] = useState<boolean>( false );
    const [ testResult, setTestResult ] = useState<string>( '' );

    const testConnection = () => {
        setIsTesting( true );
        setTestResult( '' );
        apiFetch( {
            path: '/spire-sync/v1/test-connection',
            method: 'POST',
            data: {
                base_url: baseUrl,
                api_username: apiUsername,
                api_password: apiPassword,
            },
        } )
        .then( ( response ) => {
            setTestResult( __( 'Connection successful!', 'spire-sync' ) );
            setIsTesting( false );
        }
        )
        .catch( ( error ) => {
            setTestResult( __( 'Connection failed. Please check your settings.', 'spire-sync' ) );
            setIsTesting( false );
            console.error( error );
        }
        );
    };
    return {
        isTesting,
        setIsTesting,
        testResult,
        setTestResult,
        testConnection,
    };
};

export default useTestConnection;