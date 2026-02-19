<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/n8n_api.php';

function load_notified() {
    if (!file_exists(FAILURES_NOTIFIED_FILE)) return [];
    return json_decode(file_get_contents(FAILURES_NOTIFIED_FILE), true) ?? [];
}

function save_notified($data) {
    if (!is_dir(dirname(FAILURES_NOTIFIED_FILE))) {
        mkdir(dirname(FAILURES_NOTIFIED_FILE), 0755, true);
    }
    file_put_contents(FAILURES_NOTIFIED_FILE, json_encode($data));
}

function check_and_alert($executions) {
    $notified = load_notified();
    $new_failures = [];

    foreach ($executions as $e) {
        if ($e['status'] === 'success') continue;
        if ($e['status'] === 'running') continue;
        $exec_id = $e['id'];
        if (isset($notified[$exec_id])) continue;
        $new_failures[] = $e;
        $notified[$exec_id] = time();
    }

    // Prune old entries (older than 7 days)
    $cutoff = time() - (7 * 86400);
    foreach ($notified as $id => $ts) {
        if ($ts < $cutoff) unset($notified[$id]);
    }
    save_notified($notified);

    if (empty($new_failures)) return 0;

    // Build email
    $lines = [];
    foreach ($new_failures as $f) {
        $started = $f['started_at'] ? date('M j g:ia', strtotime($f['started_at'])) : 'Unknown';
        $lines[] = "Workflow: {$f['workflow_name']}\nExecution ID: {$f['id']}\nStarted: $started\nMode: {$f['mode']}\n";
    }

    $count = count($new_failures);
    $subject = "n8n Alert: $count workflow failure" . ($count > 1 ? 's' : '') . " detected";
    $body = "The following n8n workflows failed:\n\n" . implode("\n---\n", $lines);
    $body .= "\nView details: https://pushkinvoice.com/n8n/";

    mail(ALERT_TO, $subject, $body, "From: " . ALERT_FROM);

    return $count;
}
