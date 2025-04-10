// src/admin/dashboard/Dashboard.tsx
import React from 'react';
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';

const Dashboard: React.FC = () => {
  return (
    <div>
      <h1>{ __('Spire Sync Dashboard', 'spire-sync') }</h1>
      <Button variant='primary'>
        { __('Run Sync', 'spire-sync') }
      </Button>
      {/* ...other UI elements... */}
    </div>
  );
};

export default Dashboard;