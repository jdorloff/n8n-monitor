<?php
// ============================================
// n8n MONITOR CONFIGURATION
// ============================================
// Copy this file to config.php and fill in your values.
// Never commit config.php to version control.

// Auth - username and password to log into the dashboard
define('AUTH_USERNAME', 'admin');
define('AUTH_PASSWORD', 'your-password-here');

// n8n API - your n8n instance URL and API key
// Get your API key from: n8n Settings → API → Create API Key
define('N8N_API_URL', 'https://your-n8n-instance.com/api/v1');
define('N8N_API_KEY', 'your-n8n-api-key-here');

// Email alerts - sender and recipient for failure notifications
define('ALERT_FROM', 'alerts@yourdomain.com');
define('ALERT_TO',   'you@yourdomain.com');

// Internal API token - used between the browser and your PHP backend
// Choose any random string - this is NOT your n8n API key
define('N8N_API_TOKEN', 'choose-a-random-string-here');

// File paths - no need to change these
define('GROUPS_FILE',            __DIR__ . '/data/groups.json');
define('FAILURES_NOTIFIED_FILE', __DIR__ . '/data/failures_notified.json');
