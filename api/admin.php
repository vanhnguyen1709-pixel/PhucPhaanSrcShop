<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$admin = requireAdmin();
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'page') {
    $page = $_GET['page'] ?? 'dashboard';
    ob_start();
    $file = __DIR__ . '/../admin_pages/' . $page . '.php';
    if (file_exists($file)) {
        include $file;
    } else {
        echo '<div class="empty">Trang admin không tồn tại</div>';
    }
    $html = ob_get_clean();
    echo json_encode(['html' => $html]);
    exit;
}

// ===== ADMIN ACTIONS =====
if ($action === 'create_seller' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRoot();
    $data = json_decode(file_get_contents('php://input'), true);
    if (createAdmin($data['username'], $data['password'], 'seller', $data['name'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Tạo seller thất bại']);
    }
    exit;
}

if ($action === 'toggle_seller' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRoot();
    $data = json_decode(file_get_contents('php://input'), true);
    if (toggleAdmin($data['username'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if ($action === 'delete_seller' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRoot();
    $data = json_decode(file_get_contents('php://input'), true);
    if (deleteAdmin($data['username'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if ($action === 'send_notification' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    createNotification([
        'target_uid' => $data['target_uid'] ?? null,
        'title' => $data['title'],
        'text' => $data['text'],
        'from_uid' => $admin['username'],
        'admin_only' => $data['admin_only'] ?? 0
    ]);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update_settings' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRoot();
    $data = json_decode(file_get_contents('php://input'), true);
    updateSettings($data);
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'download_db') {
    requireRoot();
    $pdo = getDBConnection();
    $tables = ['admins', 'users', 'products', 'packages', 'orders', 'topups', 'chats', 'notifications', 'downloads', 'settings'];
    $db = [];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT * FROM $table");
        $db[$table] = $stmt->fetchAll();
    }
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="cheating-game-vn-db.json"');
    echo json_encode($db, JSON_PRETTY_PRINT);
    exit;
}

if ($action === 'import_db' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireRoot();
    // Handle JSON import via POST body
    $data = json_decode(file_get_contents('php://input'), true);
    // Truncate and insert logic...
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'reset_db') {
    requireRoot();
    // Truncate all tables and re-seed
    $pdo = getDBConnection();
    $tables = ['orders', 'topups', 'chats', 'notifications', 'packages', 'downloads', 'products', 'users'];
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Re-seed default users
    createUser('UID-DEMO01', '123456', 'Khách demo 1');
    updateUserBalance('UID-DEMO01', 5000000);
    createUser('UID-DEMO02', '123456', 'Khách demo 2');
    updateUserBalance('UID-DEMO02', 2000000);
    
    echo json_encode(['success' => true]);
    exit;
}