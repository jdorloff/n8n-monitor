<?php
require_once __DIR__ . '/config.php';

function n8n_request($endpoint) {
    $ch = curl_init(N8N_API_URL . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-N8N-API-KEY: ' . N8N_API_KEY]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $body = curl_exec($ch);
    curl_close($ch);
    return json_decode($body, true);
}

function get_workflows() {
    $result = n8n_request('/workflows?limit=100');
    $workflows = [];
    foreach ($result['data'] ?? [] as $w) {
        $workflows[$w['id']] = [
            'name'   => $w['name'],
            'active' => $w['active'] ?? false,
        ];
    }
    return $workflows;
}

function get_merged_executions($limit = 100) {
    $workflows = get_workflows();

    $success_data = n8n_request('/executions?status=success&limit=' . $limit);
    $error_data   = n8n_request('/executions?status=error&limit=' . $limit);

    $all = array_merge($success_data['data'] ?? [], $error_data['data'] ?? []);
    usort($all, fn($a, $b) => strcmp($b['startedAt'] ?? '', $a['startedAt'] ?? ''));
    $all = array_slice($all, 0, $limit);

    $merged = [];
    foreach ($all as $e) {
        $wid      = $e['workflowId'] ?? '';
        $started  = $e['startedAt'] ?? null;
        $stopped  = $e['stoppedAt'] ?? null;
        $duration = ($started && $stopped) ? round(strtotime($stopped) - strtotime($started)) : null;
        $status   = $e['finished'] ? 'success' : 'failed';

        $merged[] = [
            'id'            => $e['id'],
            'workflow_id'   => $wid,
            'workflow_name' => $workflows[$wid]['name'] ?? 'Unknown',
            'status'        => $status,
            'mode'          => $e['mode'] ?? '',
            'started_at'    => $started,
            'stopped_at'    => $stopped,
            'duration_sec'  => $duration,
        ];
    }
    return $merged;
}

// Returns flat list of workflows with active status for the workflows tab
function get_workflows_list() {
    $result = n8n_request('/workflows?limit=100');
    $list = [];
    foreach ($result['data'] ?? [] as $w) {
        $list[] = [
            'id'     => $w['id'],
            'name'   => $w['name'],
            'active' => $w['active'] ?? false,
        ];
    }
    usort($list, fn($a, $b) => strcmp($a['name'], $b['name']));
    return $list;
}
