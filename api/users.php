<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'me') {
        $user = requireUser();
        echo json_encode(['success' => true, 'data' => $user]);
    } elseif ($action === 'list') {
        requireAdmin();
        echo json_encode(['success' => true, 'data' => getAllUsers()]);
    } elseif ($action === 'get' && isset($_GET['uid'])) {
        requireAdmin();
        $user = getUserByUid($_GET['uid']);
        echo json_encode(['success' => true, 'data' => $user]);
    }
} elseif ($method === 'POST' && $action === 'login') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user = loginUser($data['uid'], $data['password']);
    if ($user) {
        echo json_encode(['success' => true, 'data' => $user]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Sai UID hoặc mật khẩu']);
    }
} elseif ($method === 'PUT' && $action === 'update') {
    $user = requireUser();
    $data = json_decode(file_get_contents('php://input'), true);
    updateUser($user['uid'], $data);
    echo json_encode(['success' => true]);
} elseif ($method === 'PUT' && $action === 'admin_update') {
    $admin = requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    updateUser($data['uid'], $data);
    echo json_encode(['success' => true]);
}