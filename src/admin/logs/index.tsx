import React from 'react';
import { createRoot } from '@wordpress/element';
import '../../data/store';
import Logs from './Logs';

document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('spire-sync-logs-root');
  if (container) {
    const root = createRoot(container);
    root.render(<Logs />);
  }
});