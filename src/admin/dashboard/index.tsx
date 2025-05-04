import React from 'react';
import { createRoot } from '@wordpress/element';
import Dashboard from './Dashboard';

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('spire-sync-dashboard-root');
  if (container) {
    const root = createRoot(container);
    root.render(<Dashboard />);
  }
});