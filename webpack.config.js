const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

defaultConfig.entry = {
    dashboard: './src/admin/dashboard/index.tsx',
    'manage-syncs': './src/admin/manage-syncs/index.tsx',
    settings: './src/admin/settings/index.tsx',
    logs: './src/admin/logs/index.tsx'
};

defaultConfig.output = {
    ...defaultConfig.output,
    filename: '[name].build.js' // This will output dashboard.build.js, manage-syncs.build.js, etc.
};

// Optionally, if you want to disable cleaning in between builds:
if ( defaultConfig.plugins ) {
    // Remove the CleanWebpackPlugin if present.
    defaultConfig.plugins = defaultConfig.plugins.filter( plugin => {
        return plugin.constructor.name !== 'CleanWebpackPlugin';
    } );
}

module.exports = defaultConfig;