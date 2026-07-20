<?php
// includes/db.php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

// ===== ADMIN =====
function getAdminByUsername($username) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

function getAdminById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getAllAdmins() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function createAdmin($username, $password, $role, $name) {
    $pdo = getDBConnection();
    $id = uid('A');
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO admins (id, username, password, role, name, active) VALUES (?, ?, ?, ?, ?, 1)");
    return $stmt->execute([$id, $username, $hashed, $role, $name]);
}

function toggleAdmin($username) {
    $pdo = getDBConnection();
    $admin = getAdminByUsername($username);
    if (!$admin) return false;
    $newStatus = $admin['active'] ? 0 : 1;
    $stmt = $pdo->prepare("UPDATE admins SET active = ? WHERE username = ?");
    return $stmt->execute([$newStatus, $username]);
}

function deleteAdmin($username) {
    $pdo = getDBConnection();
    $admin = getAdminByUsername($username);
    if (!$admin || $admin['role'] === 'root') return false;
    $stmt = $pdo->prepare("DELETE FROM admins WHERE username = ?");
    return $stmt->execute([$username]);
}

// ===== USER =====
function getUserByUid($uid) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE uid = ?");
    $stmt->execute([$uid]);
    return $stmt->fetch();
}

function getUserByCredentials($uid, $password) {
    $user = getUserByUid($uid);
    if (!$user) return null;
    return $user['password'] === $password ? $user : null;
}

function createUser($uid, $password = '123456', $name = 'Thành viên') {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("INSERT INTO users (uid, password, name) VALUES (?, ?, ?)");
    return $stmt->execute([$uid, $password, $name]);
}

function updateUser($uid, $data) {
    $pdo = getDBConnection();
    $fields = [];
    $params = [];
    foreach ($data as $key => $value) {
        if (in_array($key, ['name', 'password', 'email', 'phone', 'balance', 'avatar'])) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
    }
    if (empty($fields)) return false;
    $params[] = $uid;
    $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE uid = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function updateUserBalance($uid, $amount) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE uid = ?");
    return $stmt->execute([$amount, $uid]);
}

function getAllUsers() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

// ===== PRODUCT =====
function getProductById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getPackagesByProductId($productId) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE product_id = ? ORDER BY price ASC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getProductWithPackages($id) {
    $product = getProductById($id);
    if (!$product) return null;
    $product['packages'] = getPackagesByProductId($id);
    return $product;
}

function getAllProducts($owner = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM products";
    $params = [];
    if ($owner) {
        $sql .= " WHERE owner = ?";
        $params[] = $owner;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductsByCategory($category, $owner = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM products WHERE category = ?";
    $params = [$category];
    if ($owner) {
        $sql .= " AND owner = ?";
        $params[] = $owner;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAllCategories($owner = null) {
    $pdo = getDBConnection();
    $sql = "SELECT DISTINCT category FROM products";
    $params = [];
    if ($owner) {
        $sql .= " WHERE owner = ?";
        $params[] = $owner;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return array_column($stmt->fetchAll(), 'category');
}

function createProduct($data, $packages) {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    try {
        $id = uid('P');
        $stmt = $pdo->prepare("INSERT INTO products (
            id, owner, name, category, tag, sold, stock, description, 
            media, media_type, delivery_type, delivery_text, 
            delivery_file_name, delivery_file_type, delivery_file_data
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $id,
            $data['owner'],
            $data['name'],
            $data['category'],
            $data['tag'] ?? 'ACTIVE',
            $data['sold'] ?? 0,
            $data['stock'] ?? 0,
            $data['description'] ?? '',
            $data['media'] ?? '',
            $data['media_type'] ?? 'image',
            $data['delivery_type'] ?? 'key',
            $data['delivery_text'] ?? '',
            $data['delivery_file_name'] ?? '',
            $data['delivery_file_type'] ?? '',
            $data['delivery_file_data'] ?? ''
        ]);
        
        foreach ($packages as $pkg) {
            $pkgId = uid('G');
            $stmt = $pdo->prepare("INSERT INTO packages (id, product_id, name, price, deliver) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pkgId, $id, $pkg['name'], $pkg['price'], $pkg['deliver'] ?? '']);
        }
        
        $pdo->commit();
        return $id;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function updateProduct($id, $data, $packages) {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    try {
        $fields = [];
        $params = [];
        $allowed = ['name', 'category', 'tag', 'stock', 'description', 'media', 'media_type', 
                   'delivery_type', 'delivery_text', 'delivery_file_name', 'delivery_file_type', 'delivery_file_data'];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = ?";
                $params[] = $value;
            }
        }
        $params[] = $id;
        if (!empty($fields)) {
            $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        $stmt = $pdo->prepare("DELETE FROM packages WHERE product_id = ?");
        $stmt->execute([$id]);
        
        foreach ($packages as $pkg) {
            $pkgId = uid('G');
            $stmt = $pdo->prepare("INSERT INTO packages (id, product_id, name, price, deliver) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$pkgId, $id, $pkg['name'], $pkg['price'], $pkg['deliver'] ?? '']);
        }
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function deleteProduct($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    return $stmt->execute([$id]);
}

function updateProductStock($id, $quantity) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE products SET stock = stock - ?, sold = sold + 1 WHERE id = ? AND stock >= ?");
    return $stmt->execute([$quantity, $id, $quantity]);
}

// ===== ORDER =====
function createOrder($data) {
    $pdo = getDBConnection();
    $id = uid('O');
    $stmt = $pdo->prepare("INSERT INTO orders (
        id, uid, product_id, product_name, package_name, price, 
        status, seller, delivery_type, deliver, file_name, file_type, file_data
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $id,
        $data['uid'],
        $data['product_id'],
        $data['product_name'],
        $data['package_name'],
        $data['price'],
        $data['status'] ?? 'done',
        $data['seller'],
        $data['delivery_type'] ?? 'key',
        $data['deliver'] ?? '',
        $data['file_name'] ?? '',
        $data['file_type'] ?? '',
        $data['file_data'] ?? ''
    ]);
}

function getOrdersByUid($uid) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE uid = ? ORDER BY created_at DESC");
    $stmt->execute([$uid]);
    return $stmt->fetchAll();
}

function getOrdersBySeller($seller, $limit = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM orders WHERE seller = ? ORDER BY created_at DESC";
    if ($limit) $sql .= " LIMIT " . intval($limit);
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$seller]);
    return $stmt->fetchAll();
}

function getAllOrders($limit = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM orders ORDER BY created_at DESC";
    if ($limit) $sql .= " LIMIT " . intval($limit);
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getOrderById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ===== TOPUP =====
function createTopup($data) {
    $pdo = getDBConnection();
    $id = uid('T');
    $stmt = $pdo->prepare("INSERT INTO topups (
        id, uid, amount, face_amount, method, note, card_telco, card_serial, card_code,
        bank_name, bank_number, qr_content, status, created_ms, expires_at_ms, expires_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $id,
        $data['uid'],
        $data['amount'],
        $data['face_amount'] ?? $data['amount'],
        $data['method'] ?? 'bank',
        $data['note'] ?? '',
        $data['card_telco'] ?? '',
        $data['card_serial'] ?? '',
        $data['card_code'] ?? '',
        $data['bank_name'] ?? 'MBANK',
        $data['bank_number'] ?? '0792822868',
        $data['qr_content'] ?? generateRandomCode(12),
        'pending',
        time() * 1000,
        (time() + 15 * 60) * 1000,
        date('Y-m-d H:i:s', time() + 15 * 60)
    ]);
}

function getTopupsByUid($uid) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM topups WHERE uid = ? ORDER BY created_at DESC");
    $stmt->execute([$uid]);
    return $stmt->fetchAll();
}

function getPendingTopups() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM topups WHERE status = 'pending' ORDER BY created_at ASC");
    return $stmt->fetchAll();
}

function getAllTopups() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM topups ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function approveTopup($id, $adminUsername) {
    $pdo = getDBConnection();
    $pdo->beginTransaction();
    try {
        $topup = getTopupById($id);
        if (!$topup || $topup['status'] !== 'pending') return false;
        
        updateUserBalance($topup['uid'], $topup['amount']);
        
        $stmt = $pdo->prepare("UPDATE topups SET status = 'approved', approved_by = ?, done_at = ? WHERE id = ?");
        $stmt->execute([$adminUsername, now(), $id]);
        
        createNotification([
            'target_uid' => $topup['uid'],
            'title' => 'Nạp tiền đã được duyệt',
            'text' => "Yêu cầu nạp " . money($topup['amount']) . " đã được duyệt. Số dư đã được cộng vào UID " . $topup['uid'],
            'from_uid' => $adminUsername
        ]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function rejectTopup($id, $adminUsername) {
    $pdo = getDBConnection();
    $topup = getTopupById($id);
    if (!$topup) return false;
    
    $stmt = $pdo->prepare("UPDATE topups SET status = 'rejected', approved_by = ?, done_at = ? WHERE id = ?");
    $stmt->execute([$adminUsername, now(), $id]);
    
    createNotification([
        'target_uid' => $topup['uid'],
        'title' => 'Yêu cầu nạp bị từ chối',
        'text' => "Yêu cầu nạp " . money($topup['amount']) . " chưa được duyệt. Vui lòng kiểm tra lại nội dung chuyển khoản/thẻ hoặc chat hỗ trợ.",
        'from_uid' => $adminUsername
    ]);
    
    return true;
}

function getTopupById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM topups WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function expirePendingTopups() {
    $pdo = getDBConnection();
    $nowMs = time() * 1000;
    $stmt = $pdo->prepare("UPDATE topups SET status = 'expired', done_at = ? WHERE status = 'pending' AND expires_at_ms < ?");
    return $stmt->execute([now(), $nowMs]);
}

// ===== CHAT =====
function createChatMessage($data) {
    $pdo = getDBConnection();
    $id = uid('C');
    $stmt = $pdo->prepare("INSERT INTO chats (id, uid, from_uid, from_role, text, file_name, file_type, file_data) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $id,
        $data['uid'],
        $data['from_uid'],
        $data['from_role'],
        $data['text'] ?? '',
        $data['file_name'] ?? '',
        $data['file_type'] ?? '',
        $data['file_data'] ?? ''
    ]);
}

function getChatsByUid($uid) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM chats WHERE uid = ? ORDER BY created_at ASC");
    $stmt->execute([$uid]);
    return $stmt->fetchAll();
}

function getChatUids() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT DISTINCT uid FROM chats ORDER BY created_at DESC");
    return array_column($stmt->fetchAll(), 'uid');
}

function getLatestChatForUid($uid) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM chats WHERE uid = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$uid]);
    return $stmt->fetch();
}

// ===== NOTIFICATION =====
function createNotification($data) {
    $pdo = getDBConnection();
    $id = uid('N');
    $stmt = $pdo->prepare("INSERT INTO notifications (id, target_uid, title, text, from_uid, admin_only) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $id,
        $data['target_uid'] ?? null,
        $data['title'],
        $data['text'],
        $data['from_uid'] ?? 'system',
        $data['admin_only'] ?? 0
    ]);
}

function getNotificationsForUser($uid) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE target_uid IS NULL OR target_uid = ? ORDER BY created_at DESC");
    $stmt->execute([$uid]);
    return $stmt->fetchAll();
}

function getAllNotifications() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

function deleteNotification($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    return $stmt->execute([$id]);
}

// ===== DOWNLOAD =====
function createDownload($data) {
    $pdo = getDBConnection();
    $id = uid('D');
    $stmt = $pdo->prepare("INSERT INTO downloads (id, owner, title, description, file_name, file_type, file_data, active) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
    return $stmt->execute([
        $id,
        $data['owner'],
        $data['title'],
        $data['description'] ?? '',
        $data['file_name'],
        $data['file_type'],
        $data['file_data']
    ]);
}

function getActiveDownloads($owner = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM downloads WHERE active = 1";
    $params = [];
    if ($owner) {
        $sql .= " AND owner = ?";
        $params[] = $owner;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getAllDownloads($owner = null) {
    $pdo = getDBConnection();
    $sql = "SELECT * FROM downloads";
    $params = [];
    if ($owner) {
        $sql .= " WHERE owner = ?";
        $params[] = $owner;
    }
    $sql .= " ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function toggleDownload($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("UPDATE downloads SET active = NOT active WHERE id = ?");
    return $stmt->execute([$id]);
}

function deleteDownload($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("DELETE FROM downloads WHERE id = ?");
    return $stmt->execute([$id]);
}

function getDownloadById($id) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM downloads WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// ===== SETTINGS =====
function getSettings() {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch();
    if (!$settings) {
        $stmt = $pdo->prepare("INSERT INTO settings (id) VALUES (1)");
        $stmt->execute();
        return getSettings();
    }
    return $settings;
}

function updateSettings($data) {
    $pdo = getDBConnection();
    $fields = [];
    $params = [];
    $allowed = ['shop_name', 'slogan', 'logo', 'announcement', 'partner', 
                'bank_name', 'bank_number', 'bank_owner', 'card_note', 'zalo', 'contact'];
    foreach ($data as $key => $value) {
        if (in_array($key, $allowed)) {
            $fields[] = "$key = ?";
            $params[] = $value;
        }
    }
    if (empty($fields)) return false;
    $sql = "UPDATE settings SET " . implode(', ', $fields) . " WHERE id = 1";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}