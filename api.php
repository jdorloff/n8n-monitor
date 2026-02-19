<?php
ini_set('display_errors', 0);
error_reporting(0);
ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/n8n_api.php';
require_once __DIR__ . '/groups.php';
require_once __DIR__ . '/alerts.php';

ob_clean();
header('Content-Type: application/json');

$token = $_GET['token'] ?? '';
if ($token !== N8N_API_TOKEN) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'executions';

try {
    if ($action === 'executions') {
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        $executions = get_merged_executions($limit);
        $groups = load_groups();

        foreach ($executions as &$e) {
            $e['group'] = null;
            foreach ($groups as $g) {
                foreach ($g['workflows'] ?? [] as $w) {
                    if ($w['id'] === $e['workflow_id']) {
                        $e['group'] = $g['name'];
                        break 2;
                    }
                }
            }
        }
        unset($e);

        try { $new_failures = check_and_alert($executions); } catch (Exception $ex) { $new_failures = 0; }

        $cutoff = time() - 86400;
        $failure_count = 0;
        foreach ($executions as $e) {
            if ($e['status'] !== 'success' && $e['status'] !== 'running') {
                if ($e['started_at'] && strtotime($e['started_at']) > $cutoff) {
                    $failure_count++;
                }
            }
        }

        echo json_encode([
            'executions'    => $executions,
            'groups'        => $groups,
            'failure_count' => $failure_count,
            'new_failures'  => $new_failures,
        ]);

    } elseif ($action === 'workflows') {
        echo json_encode(get_workflows_list());

    } else {
        echo json_encode(['error' => 'Unknown action']);
    }
} catch (Exception $ex) {
    echo json_encode(['error' => $ex->getMessage()]);
}
