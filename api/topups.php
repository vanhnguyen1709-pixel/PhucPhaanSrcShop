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
    if ($action === 'list') {
        $uid = $_GET['uid'] ?? null;
        if ($uid) {
            $topups = getTopupsByUid($uid);
        } else {
            $admin = requireAdmin();
            $topups = getAllTopups();
        }
        echo json_encode(['success' => true, 'data' => $topups]);
    } elseif ($action === 'pending') {
        $admin = requireAdmin();
        echo json_encode(['success' => true, 'data' => getPendingTopups()]);
    } elseif ($action === 'new_qr') {
        $amount = intval($_GET['amount'] ?? 50000);
        $code = generateRandomCode(12);
        $expiresAt = time() + 15 * 60;
        echo json_encode([
            'success' => true,
            'qr_content' => $code,
            'expires_at' => date('Y-m-d H:i:s', $expiresAt),
            'expires_at_ms' => $expiresAt * 1000,
            'qr_url' => vietQrUrl($amount, $code)
        ]);
    }
} elseif ($method === 'POST' && $action === 'create') {
    $user = requireUser();
    $data = json_decode(file_get_contents('php://input'), true);
    
    $data['uid'] = $user['uid'];
    $id = createTopup($data);
    
    createNotification([
        'admin_only' => 1,
        'title' => 'Có yêu cầu nạp tiền mới',
        'text' => "UID {$user['uid']} gửi yêu cầu nạp " . money($data['amount']) . " qua " . ($data['method'] === 'bank' ? 'MBANK' : 'Thẻ cào'),
        'from_uid' => 'system'
    ]);
    
    echo json_encode(['success' => true, 'id' => $id]);
} elseif ($method === 'PUT' && $action === 'approve') {
    $admin = requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    if (approveTopup($data['id'], $admin['username'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể duyệt']);
    }
} elseif ($method === 'PUT' && $action === 'reject') {
    $admin = requireAdmin();
    $data = json_decode(file_get_contents('php://input'), true);
    if (rejectTopup($data['id'], $admin['username'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Không thể từ chối']);
    }
}