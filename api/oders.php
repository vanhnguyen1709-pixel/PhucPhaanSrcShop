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
    if ($action === 'list') {
        $uid = $_GET['uid'] ?? null;
        if ($uid) {
            $orders = getOrdersByUid($uid);
        } else {
            $admin = requireAdmin();
            $seller = $admin['role'] === 'root' ? null : $admin['username'];
            $orders = $seller ? getOrdersBySeller($seller) : getAllOrders();
        }
        echo json_encode(['success' => true, 'data' => $orders]);
    }
} elseif ($method === 'POST' && $action === 'create') {
    $user = requireUser();
    $data = json_decode(file_get_contents('php://input'), true);
    
    $product = getProductWithPackages($data['product_id']);
    if (!$product) {
        echo json_encode(['success' => false, 'error' => 'Sản phẩm không tồn tại']);
        exit;
    }
    
    // Find package
    $pkg = null;
    foreach ($product['packages'] as $p) {
        if ($p['name'] === $data['package_name'] && $p['price'] == $data['price']) {
            $pkg = $p;
            break;
        }
    }
    if (!$pkg) {
        echo json_encode(['success' => false, 'error' => 'Gói giá không hợp lệ']);
        exit;
    }
    
    // Check stock
    if ($product['stock'] <= 0) {
        echo json_encode(['success' => false, 'error' => 'Sản phẩm hết kho']);
        exit;
    }
    
    // Check balance
    $user = getUserByUid($user['uid']);
    if ($user['balance'] < $pkg['price']) {
        echo json_encode(['success' => false, 'error' => 'Số dư không đủ']);
        exit;
    }
    
    // Process order
    $deliveryType = $product['delivery_type'] ?? 'key';
    $deliver = $product['delivery_text'] ?? $pkg['deliver'] ?? '';
    
    $orderData = [
        'uid' => $user['uid'],
        'product_id' => $product['id'],
        'product_name' => $product['name'],
        'package_name' => $pkg['name'],
        'price' => $pkg['price'],
        'seller' => $product['owner'],
        'delivery_type' => $deliveryType,
        'deliver' => $deliver,
        'file_name' => $product['delivery_file_name'] ?? '',
        'file_type' => $product['delivery_file_type'] ?? '',
        'file_data' => $product['delivery_file_data'] ?? ''
    ];
    
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    try {
        createOrder($orderData);
        updateUserBalance($user['uid'], -$pkg['price']);
        updateProductStock($product['id'], 1);
        $pdo->commit();
        
        $updatedUser = getUserByUid($user['uid']);
        echo json_encode(['success' => true, 'new_balance' => $updatedUser['balance']]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}