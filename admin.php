<?php
// admin.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$admin = getCurrentAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (loginAdmin($username, $password)) {
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Sai tài khoản hoặc mật khẩu';
    }
}

if (isset($_GET['logout'])) {
    logoutAdmin();
    header('Location: admin.php');
    exit;
}

$settings = getSettings();
$products = getAllProducts();
$orders = getAllOrders();
$topups = getAllTopups();
$users = getAllUsers();
$admins = getAllAdmins();
$downloads = getAllDownloads();
$notifications = getAllNotifications();
$chatUids = getChatUids();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= esc($settings['shop_name'] ?? 'Cheating Game VN') ?></title>
    <link rel="icon" href="<?= esc(logoSrc($settings)) ?>" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php if (!$admin): ?>
    <!-- Login -->
    <div class="login-screen">
        <div class="login-card">
            <div class="brand"><img src="<?= esc(logoSrc($settings)) ?>"></div>
            <h2>Đăng nhập Admin</h2>
            <p class="sub">Admin gốc hoặc seller con</p>
            <?php if (isset($error)): ?>
            <div class="notice" style="margin-bottom:12px;border-color:#ff6b6b"><?= esc($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="field">
                    <label>Tài khoản</label>
                    <input class="input" name="username" value="admin" required>
                </div>
                <div class="field">
                    <label>Mật khẩu</label>
                    <input class="input" name="password" type="password" value="admin123" required>
                </div>
                <button class="btn primary" style="width:100%" name="login" value="1">Đăng nhập</button>
            </form>
            <div class="notice" style="margin-top:14px;font-size:12px">
                Admin: <b>admin / admin123</b><br>
                Seller demo: <b>seller / seller123</b>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Admin Layout -->
    <div class="admin-layout">
        <aside class="admin-side">
            <div class="admin-logo"><img src="<?= esc(logoSrc($settings)) ?>"></div>
            <div class="admin-nav">
                <button data-page="dashboard" onclick="switchAdmin('dashboard')">Tổng quan</button>
                <button data-page="products" onclick="switchAdmin('products')">Add file / sản phẩm</button>
                <button data-page="downloads" onclick="switchAdmin('downloads')">Upload tải xuống</button>
                <button data-page="topups" onclick="switchAdmin('topups')">Duyệt nạp tiền <?php 
                    $pendingCount = count(array_filter($topups, fn($t) => $t['status'] === 'pending'));
                    if ($pendingCount) echo '<span class="nav-badge">' . $pendingCount . '</span>';
                ?></button>
                <button data-page="orders" onclick="switchAdmin('orders')">Đơn hàng</button>
                <button data-page="users" onclick="switchAdmin('users')">UID người dùng</button>
                <button data-page="chat" onclick="switchAdmin('chat')">Chat hỗ trợ UID</button>
                <button data-page="notify" onclick="switchAdmin('notify')">Thông báo</button>
                <?php if ($admin['role'] === 'root'): ?>
                <button data-page="sellers" onclick="switchAdmin('sellers')">Seller con</button>
                <button data-page="settings" onclick="switchAdmin('settings')">Cài đặt shop</button>
                <?php endif; ?>
                <button data-page="database" onclick="switchAdmin('database')">Database</button>
                <button onclick="location.href='?logout=1'">Đăng xuất</button>
            </div>
        </aside>
        <main class="admin-main">
            <div id="adminContent">
                <!-- Content loaded via AJAX -->
            </div>
        </main>
    </div>
    <?php endif; ?>
    
    <div id="toast" class="toast"></div>
    
    <script>
        const ADMIN = <?= json_encode($admin) ?>;
        const ADMIN_API = '/api/admin.php';
        
        function toast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2400);
        }
        
        function switchAdmin(page) {
            document.querySelectorAll('.admin-nav [data-page]').forEach(b => {
                b.classList.toggle('active', b.dataset.page === page);
            });
            fetch('/api/admin.php?action=page&page=' + page)
                .then(res => res.json())
                .then(data => {
                    if (data.html) {
                        document.getElementById('adminContent').innerHTML = data.html;
                    }
                })
                .catch(err => console.error(err));
        }
        
        // Load dashboard by default
        <?php if ($admin): ?>
        switchAdmin('dashboard');
        <?php endif; ?>
    </script>
</body>
</html>
