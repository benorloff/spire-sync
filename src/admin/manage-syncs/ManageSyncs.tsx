import React from 'react';
import { __ } from '@wordpress/i18n';

const ManageSyncs: React.FC = () => {
  return (
    <div>
      <h1>{ __('Manage Syncs', 'spire-sync') }</h1>
    </div>
  );
};

export default ManageSyncs;