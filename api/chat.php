<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action === 'list' && isset($_GET['uid'])) {
        $uid = $_GET['uid'];
        $admin = getCurrentAdmin();
        $user = getCurrentUser();
        
        // Check permission
        if (!$admin && (!$user || $user['uid'] !== $uid)) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            exit;
        }
        
        $chats = getChatsByUid($uid);
        echo json_encode(['success' => true, 'data' => $chats]);
    } elseif ($action === 'uids') {
        requireAdmin();
        echo json_encode(['success' => true, 'data' => getChatUids()]);
    }
} elseif ($method === 'POST' && $action === 'send') {
    $data = json_decode(file_get_contents('php://input'), true);
    $admin = getCurrentAdmin();
    $user = getCurrentUser();
    
    $fromUid = $admin ? $admin['username'] : ($user ? $user['uid'] : null);
    $fromRole = $admin ? ($admin['role'] === 'root' ? 'Admin' : 'Seller') : 'UID';
    
    if (!$fromUid) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    $chatData = [
        'uid' => $data['uid'],
        'from_uid' => $fromUid,
        'from_role' => $fromRole,
        'text' => $data['text'] ?? '',
        'file_name' => $data['file_name'] ?? '',
        'file_type' => $data['file_type'] ?? '',
        'file_data' => $data['file_data'] ?? ''
    ];
    
    createChatMessage($chatData);
    
    // Auto response for user messages
    if (!$admin && $user) {
        createChatMessage([
            'uid' => $user['uid'],
            'from_uid' => 'AI',
            'from_role' => 'AI',
            'text' => 'Mình đã nhận yêu cầu của bạn. Admin/seller sẽ kiểm tra và trả lời ngay trong khung chat này.'
        ]);
    }
    
    echo json_encode(['success' => true]);
}