import React from 'react';
import { __ } from '@wordpress/i18n';

const Settings: React.FC = () => {
  return (
    <div>
      <h1>{ __('Settings', 'spire-sync') }</h1>
    </div>
  );
};

export default Settings;