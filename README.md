# n8n Monitor

A lightweight, self-hosted reporting dashboard for [n8n](https://n8n.io/) workflow automation. Built for anyone running multiple workflows who needs a clean operational view without digging into the n8n UI.

![PHP](https://img.shields.io/badge/PHP-7.4+-blue) ![n8n](https://img.shields.io/badge/n8n-v0.150+-orange)

---

## What it does

- Lists all your n8n workflows with enabled/disabled status and last run time
- Shows execution history with status (success/failed), mode, duration, and timestamp
- Groups workflows by business function — e.g. "Billing", "Outbound", "Prospect Trader"
- Filters executions and workflows by group, assignment status, and name
- Sends email alerts when a workflow fails (once per failure, no spam)
- Shows failure badge counts per group in the sidebar
- Auto-refreshes every 60 seconds
- Password protected

---

## Why

The n8n UI is built for building and debugging workflows, not for monitoring them operationally. Once you have 15+ workflows running across different business functions, you need a grouped, at-a-glance view of what is running and what is failing — without logging into n8n itself.

---

## Requirements

- PHP 7.4+ with cURL enabled
- n8n v0.150 or newer (uses the `/api/v1/executions?status=` filter)
- n8n API key with read access to executions and workflows
- A web server (Apache/Nginx) with write access to the `data/` directory
- PHP `mail()` configured on your server for email alerts

---

## Installation

1. Copy all files to a directory on your web server, e.g. `yoursite.com/n8n/`

2. Create the `data/` directory and make it writable:
```bash
mkdir data
chmod 777 data
```

3. Add a `.htaccess` file inside `data/` to block direct access:
```
Deny from all
```

4. Copy `config.example.php` to `config.php` and set your values:
```bash
cp config.example.php config.php
```
```php
define('AUTH_USERNAME', 'admin');
define('AUTH_PASSWORD', 'your-password');

define('N8N_API_URL', 'https://your-n8n-instance.com/api/v1');
define('N8N_API_KEY', 'your-n8n-api-key');

define('ALERT_FROM', 'alerts@yourdomain.com');
define('ALERT_TO',   'you@yourdomain.com');
```

5. Visit `yoursite.com/n8n/` and log in.

---

## Getting your n8n API key

In n8n go to **Settings → API → Create API Key**. The key only needs read access.

---

## Usage

### Executions tab
Shows the last 50 executions across all workflows. Filter by:
- Status: All / Success / Failed
- Assignment: All / Assigned to a group / Unassigned
- Group name
- Workflow name search

### Workflows tab
Shows all workflows with enabled/disabled status and last run time. From here you can assign workflows to groups using the inline dropdown.

### Groups
Create groups in the sidebar to organize workflows by business function. Click a group to filter both the Executions and Workflows views. Failure badges show on groups with recent failed executions.

---

## File structure

```
n8n/
├── index.php          # Main UI and login
├── api.php            # JSON endpoint for the dashboard
├── config.php         # Configuration
├── groups.php         # Group management (CRUD)
├── n8n_api.php        # n8n API client
├── alerts.php         # Failure email alerts
├── data/
│   ├── .htaccess      # Blocks direct access
│   ├── groups.json    # Group configuration (auto-created)
│   └── failures_notified.json  # Tracks sent alerts (auto-created)
└── README.md
```

---

## Compatibility

| n8n version | Status |
|-------------|--------|
| v0.150+     | ✅ Full support |
| v0.122–0.149 | ⚠️ Works but success/failed filters may not apply — all executions returned |
| Below v0.122 | ❌ API not available |

---

## Notes

- Group configuration is stored in `data/groups.json` — back this up if needed
- Email alerts fire once per unique failed execution ID, tracked in `data/failures_notified.json`. Entries older than 7 days are pruned automatically
- The internal API token in `config.php` (`N8N_API_TOKEN`) is used between the browser and your PHP backend — it is not your n8n API key and never leaves your server

---

## Support

This is a personal tool shared as-is. No support is provided and issues may not be responded to.

---

## License

MIT — free to use, modify, and distribute.
