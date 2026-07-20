<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$page = $_GET['page'] ?? 'home';
$user = getCurrentUser();
$settings = getSettings();

ob_start();

switch ($page) {
    case 'recharge':
        include __DIR__ . '/../page_parts/recharge.php';
        break;
    case 'orders':
        include __DIR__ . '/../page_parts/orders.php';
        break;
    case 'history':
        include __DIR__ . '/../page_parts/history.php';
        break;
    case 'account':
        include __DIR__ . '/../page_parts/account.php';
        break;
    case 'support':
        include __DIR__ . '/../page_parts/support.php';
        break;
    case 'downloads':
        include __DIR__ . '/../page_parts/downloads.php';
        break;
    default:
        echo '<div class="empty">Trang không tồn tại</div>';
}

$html = ob_get_clean();
echo json_encode(['html' => $html]);