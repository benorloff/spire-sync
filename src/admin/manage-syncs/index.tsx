import React from 'react';
import { createRoot } from '@wordpress/element';
import '../../data/store';
import ManageSyncs from './ManageSyncs';

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('spire-sync-manage-syncs-root');
  if (container) {
    const root = createRoot(container);
    root.render(<ManageSyncs />);
  }
});