import React from 'react';
import { createRoot } from '@wordpress/element';
import Settings from './Settings';

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('spire-sync-settings-root');
  if (container) {
    const root = createRoot(container);
    root.render(<Settings />);
  }
});