<?php
// includes/auth.php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

const ADMIN_SESSION = 'cheating_game_vn_admin_session';
const USER_SESSION = 'cheating_game_vn_uid_session';

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function getCurrentAdmin() {
    startSession();
    if (!isset($_SESSION[ADMIN_SESSION])) return null;
    return getAdminByUsername($_SESSION[ADMIN_SESSION]);
}

function requireAdmin() {
    $admin = getCurrentAdmin();
    if (!$admin || !$admin['active']) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized', 'redirect' => 'admin.php']);
        exit;
    }
    return $admin;
}

function requireRoot() {
    $admin = requireAdmin();
    if ($admin['role'] !== 'root') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    return $admin;
}

function loginAdmin($username, $password) {
    $admin = getAdminByUsername($username);
    if (!$admin || !$admin['active']) return false;
    if (!password_verify($password, $admin['password'])) return false;
    startSession();
    $_SESSION[ADMIN_SESSION] = $username;
    return $admin;
}

function logoutAdmin() {
    startSession();
    unset($_SESSION[ADMIN_SESSION]);
}

function getCurrentUser() {
    startSession();
    if (!isset($_SESSION[USER_SESSION])) {
        $uid = 'UID-' . strtoupper(substr(bin2hex(random_bytes(6)), 0, 6));
        $password = '123456';
        createUser($uid, $password, 'Thành viên');
        $_SESSION[USER_SESSION] = $uid;
        return getUserByUid($uid);
    }
    return getUserByUid($_SESSION[USER_SESSION]);
}

function loginUser($uid, $password) {
    $user = getUserByCredentials($uid, $password);
    if (!$user) return false;
    startSession();
    $_SESSION[USER_SESSION] = $uid;
    return $user;
}

function logoutUser() {
    startSession();
    unset($_SESSION[USER_SESSION]);
}

function requireUser() {
    $user = getCurrentUser();
    if (!$user) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    return $user;
}

function canManageProduct($admin, $product) {
    if (!$admin || !$product) return false;
    return $admin['role'] === 'root' || $product['owner'] === $admin['username'];
}