<?php
require_once __DIR__ . '/config.php';

function load_groups() {
    if (!file_exists(GROUPS_FILE)) return [];
    return json_decode(file_get_contents(GROUPS_FILE), true) ?? [];
}

function save_groups($groups) {
    if (!is_dir(dirname(GROUPS_FILE))) {
        mkdir(dirname(GROUPS_FILE), 0755, true);
    }
    file_put_contents(GROUPS_FILE, json_encode($groups, JSON_PRETTY_PRINT));
}

// Handle AJAX requests
if (isset($_GET['group_action'])) {
    header('Content-Type: application/json');

    $token = $_GET['token'] ?? $_POST['token'] ?? '';
    if ($token !== N8N_API_TOKEN) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $groups = load_groups();

    switch ($_GET['group_action']) {
        case 'get':
            echo json_encode($groups);
            break;

        case 'add_group':
            $name = trim($_POST['name'] ?? '');
            if (!$name) { echo json_encode(['success' => false, 'error' => 'Name required']); exit; }
            foreach ($groups as $g) {
                if ($g['name'] === $name) { echo json_encode(['success' => false, 'error' => 'Group already exists']); exit; }
            }
            $groups[] = ['name' => $name, 'workflows' => []];
            save_groups($groups);
            echo json_encode(['success' => true]);
            break;

        case 'delete_group':
            $name = trim($_POST['name'] ?? '');
            $groups = array_values(array_filter($groups, fn($g) => $g['name'] !== $name));
            save_groups($groups);
            echo json_encode(['success' => true]);
            break;

        case 'add_workflow':
            $group_name    = trim($_POST['group_name'] ?? '');
            $workflow_id   = trim($_POST['workflow_id'] ?? '');
            $workflow_name = trim($_POST['workflow_name'] ?? '');
            foreach ($groups as &$g) {
                $g['workflows'] = array_values(array_filter($g['workflows'] ?? [], fn($w) => $w['id'] !== $workflow_id));
            }
            unset($g);
            foreach ($groups as &$g) {
                if ($g['name'] === $group_name) {
                    $g['workflows'][] = ['id' => $workflow_id, 'name' => $workflow_name];
                    break;
                }
            }
            unset($g);
            save_groups($groups);
            echo json_encode(['success' => true]);
            break;

        case 'remove_workflow':
            $workflow_id = trim($_POST['workflow_id'] ?? '');
            foreach ($groups as &$g) {
                $g['workflows'] = array_values(array_filter($g['workflows'] ?? [], fn($w) => $w['id'] !== $workflow_id));
            }
            unset($g);
            save_groups($groups);
            echo json_encode(['success' => true]);
            break;
    }
    exit;
}
