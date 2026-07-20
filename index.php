<?php
// index.php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$user = getCurrentUser();
$settings = getSettings();
$products = getAllProducts();
$categories = getAllCategories();

// Lấy danh sách sản phẩm theo danh mục
$category = $_GET['cat'] ?? 'all';
$search = $_GET['search'] ?? '';

if ($category !== 'all') {
    $products = getProductsByCategory($category);
}

if ($search) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE name LIKE ? OR category LIKE ? OR description LIKE ?");
    $searchTerm = "%$search%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $products = $stmt->fetchAll();
}

// Lấy gói giá cho từng sản phẩm
foreach ($products as &$p) {
    $p['packages'] = getPackagesByProductId($p['id']);
}

$downloads = getActiveDownloads();
$notifications = getNotificationsForUser($user['uid']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title><?= esc($settings['shop_name'] ?? 'Cheating Game VN') ?></title>
    <link rel="icon" href="<?= esc(logoSrc($settings)) ?>" type="image/png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="drawerOverlay" class="drawer-overlay"></div>
    <div class="layout">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar">
            <div class="brand"><img src="<?= esc(logoSrc($settings)) ?>" alt="<?= esc($settings['shop_name'] ?? 'SHOP') ?>"></div>
            <nav class="nav">
                <div class="nav-title">MENU</div>
                <button data-page="home" onclick="switchPage('home')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10.5V20h13v-9.5"/><path d="M9 20v-6h6v6"/></svg>
                    </span>Trang chủ
                </button>
                <button data-page="recharge" onclick="switchPage('recharge')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><path d="M4 7.5h14a2 2 0 0 1 2 2V19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7.5a2 2 0 0 1 2-2Z"/><path d="M16 12h6v5h-6a2.5 2.5 0 0 1 0-5Z"/><path d="M4 7.5 16.5 3v4.5"/></svg>
                    </span>Ví tiền <span class="mini">Auto</span>
                </button>
                <button data-page="orders" onclick="switchPage('orders')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><path d="M6 7h12l-1 14H7L6 7Z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
                    </span>Đơn hàng
                </button>
                <button data-page="history" onclick="switchPage('history')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v6h6"/><path d="M12 7v5l3 2"/></svg>
                    </span>Lịch sử giao dịch
                </button>
                <button data-page="account" onclick="switchPage('account')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4.5 21a7.5 7.5 0 0 1 15 0"/></svg>
                    </span>Thông tin cá nhân
                </button>
                <button data-page="support" onclick="switchPage('support')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><path d="M4 5h16v11H8l-4 4V5Z"/><path d="M8 9h8"/><path d="M8 13h5"/></svg>
                    </span>Chat AI / Hỗ trợ
                </button>
                <div class="nav-title">DỊCH VỤ</div>
                <button data-page="downloads" onclick="switchPage('downloads')">
                    <span class="ico">
                        <svg viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 20h14"/></svg>
                    </span>Tải xuống <span class="mini">Free</span>
                </button>
            </nav>
        </aside>
        
        <div class="main">
            <!-- Topbar -->
            <header class="topbar">
                <button id="hamb" class="hamb">☰</button>
                <div class="searchbox">
                    <span>⌕</span>
                    <input id="globalSearch" placeholder="Tìm kiếm sản phẩm... (Enter)" 
                           value="<?= esc($search) ?>">
                </div>
                <div class="top-spacer"></div>
                <button class="pill">VN⌄</button>
                <button id="btnNotify" class="pill">
                    TB <span id="notifyCount" class="badge green"><?= count($notifications) ?: '' ?></span>
                </button>
                <div class="pill wallet">
                    <span id="topBalance"><?= money($user['balance']) ?></span>
                </div>
                <button id="btnAvatarTop" class="pill user-pill">
                    <img id="topAvatar" class="avatar" src="<?= esc($user['avatar'] ?: 'assets/products/avatar.svg') ?>" alt="avatar">
                    <span id="topUid"><?= esc($user['uid']) ?></span>
                </button>
            </header>
            
            <main id="content" class="content">
                <!-- Home -->
                <div id="page-home">
                    <section class="home-hero">
                        <?php 
                        $featured = $products[0] ?? null;
                        $featuredMedia = '';
                        if ($featured && $featured['media']) {
                            if ($featured['media_type'] === 'video') {
                                $featuredMedia = '<video src="' . esc($featured['media']) . '" muted autoplay loop playsinline></video>';
                            } else {
                                $featuredMedia = '<img src="' . esc($featured['media']) . '" alt="' . esc($featured['name']) . '">';
                            }
                        } else {
                            $featuredMedia = '<div style="height:100%;display:grid;place-items:center;font-weight:1000">' . esc($settings['shop_name'] ?? 'SHOP') . '</div>';
                        }
                        ?>
                        <div class="hero-copy">
                            <div class="hero-chip"><?= esc($featured['category'] ?? 'Dịch vụ') ?></div>
                            <h1><?= esc($featured['name'] ?? $settings['shop_name'] ?? 'Shop bán hàng') ?></h1>
                            <p><?= esc(($featured['description'] ?? $settings['announcement'] ?? '')) ?></p>
                            <div class="hero-price">
                                <b><?= $featured ? priceRange(getPackagesByProductId($featured['id'])) : money(0) ?></b>
                            </div>
                            <?php if ($featured): ?>
                            <div class="hero-actions">
                                <button class="btn primary" onclick="openBuy('<?= $featured['id'] ?>')">Mua ngay</button>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="hero-media"><?= $featuredMedia ?></div>
                    </section>
                    
                    <div class="section-head home-clean-head">
                        <div>
                            <h2><span class="section-line"></span> Danh mục</h2>
                            <div class="sub">Ghim nhanh các nhóm sản phẩm</div>
                        </div>
                        <span class="badge"><?= count($products) ?> sản phẩm</span>
                    </div>
                    
                    <div class="quick-cats">
                        <?php 
                        $quickCats = array_slice(array_filter($categories, fn($c) => $c !== 'all'), 0, 5);
                        foreach ($quickCats as $i => $c): 
                            $count = count(getProductsByCategory($c));
                        ?>
                        <button class="quick-cat q<?= $i % 5 ?>" onclick="filterCategory('<?= esc($c) ?>')">
                            <span><?= $i + 1 ?></span>
                            <b><?= esc($c) ?></b>
                            <small><?= $count ?> mục</small>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cats product-tabs">
                        <button class="cat <?= $category === 'all' ? 'active' : '' ?>" onclick="filterCategory('all')">Tất cả</button>
                        <?php foreach ($categories as $c): ?>
                        <button class="cat <?= $category === $c ? 'active' : '' ?>" onclick="filterCategory('<?= esc($c) ?>')"><?= esc($c) ?></button>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="grid" id="productGrid">
                        <?php if (empty($products)): ?>
                        <div class="empty panel">Không có sản phẩm phù hợp</div>
                        <?php else: ?>
                        <?php foreach ($products as $p): 
                            $packages = getPackagesByProductId($p['id']);
                            $pkg = firstPkg($packages);
                        ?>
                        <div class="card">
                            <div class="media">
                                <?php if ($p['media']): ?>
                                    <?php if ($p['media_type'] === 'video'): ?>
                                    <video src="<?= esc($p['media']) ?>" muted autoplay loop playsinline></video>
                                    <?php else: ?>
                                    <img src="<?= esc($p['media']) ?>" alt="<?= esc($p['name']) ?>">
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div style="height:100%;display:grid;place-items:center;font-size:14px;color:var(--muted)"><?= esc($settings['shop_name'] ?? 'SHOP') ?></div>
                                <?php endif; ?>
                                <span class="stock">● <?= esc($p['tag'] ?? 'ACTIVE') ?></span>
                            </div>
                            <span class="tag"><?= esc($p['category']) ?></span>
                            <h3><?= esc($p['name']) ?></h3>
                            <p class="desc"><?= nl2br_esc(substr($p['description'] ?? '', 0, 60)) ?></p>
                            <div class="meta">
                                <span>Kho: <b><?= intval($p['stock']) ?></b></span>
                                <span>Đã bán: <b><?= intval($p['sold']) ?></b></span>
                            </div>
                            <div class="price-row">
                                <div class="price"><?= priceRange($packages) ?></div>
                                <button class="buy" onclick="openBuy('<?= $p['id'] ?>')">Mua ngay</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recharge -->
                <div id="page-recharge" style="display:none"></div>
                <!-- Orders -->
                <div id="page-orders" style="display:none"></div>
                <!-- History -->
                <div id="page-history" style="display:none"></div>
                <!-- Account -->
                <div id="page-account" style="display:none"></div>
                <!-- Support -->
                <div id="page-support" style="display:none"></div>
                <!-- Downloads -->
                <div id="page-downloads" style="display:none"></div>
            </main>
        </div>
    </div>
    
    <button class="float-support" onclick="switchPage('support')">✧ Hỗ trợ AI</button>
    <div id="modal" class="modal"><div id="modalBox" class="modal-card"></div></div>
    <div id="toast" class="toast"></div>
    
    <script>
        // Khởi tạo biến từ PHP
        const USER_DATA = <?= json_encode($user) ?>;
        const SETTINGS = <?= json_encode($settings) ?>;
        const NOTIFICATIONS = <?= json_encode($notifications) ?>;
        const API_BASE = '/api/';
        
        // Hàm giống JS gốc
        function money(n) { return new Intl.NumberFormat('vi-VN').format(n) + 'đ'; }
        function esc(s) { return String(s||'').replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[c])); }
        function nl2br(s) { return esc(s).replace(/\n/g,'<br>'); }
        function uid(prefix){ return prefix + '-' + Math.random().toString(36).slice(2,7).toUpperCase() + Date.now().toString(36).slice(-4).toUpperCase(); }
        
        // Toast
        function toast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2400);
        }
        
        // Modal
        function showModal(html) {
            document.getElementById('modalBox').innerHTML = html;
            document.getElementById('modal').classList.add('show');
        }
        function closeModal() {
            document.getElementById('modal').classList.remove('show');
            document.getElementById('modalBox').innerHTML = '';
        }
        
        // Switch page
        function switchPage(page) {
            const pages = ['home', 'recharge', 'orders', 'history', 'account', 'support', 'downloads'];
            pages.forEach(p => {
                const el = document.getElementById('page-' + p);
                if (el) el.style.display = p === page ? 'block' : 'none';
            });
            // Update nav
            document.querySelectorAll('.nav [data-page]').forEach(b => {
                b.classList.toggle('active', b.dataset.page === page);
            });
            // Load page content via AJAX
            loadPage(page);
        }
        
        function loadPage(page) {
            fetch('/api/pages.php?page=' + page)
                .then(res => res.json())
                .then(data => {
                    const el = document.getElementById('page-' + page);
                    if (el && data.html) {
                        el.innerHTML = data.html;
                        // Re-run scripts in the loaded content
                        el.querySelectorAll('script').forEach(oldScript => {
                            const newScript = document.createElement('script');
                            Array.from(oldScript.attributes).forEach(attr => {
                                newScript.setAttribute(attr.name, attr.value);
                            });
                            newScript.textContent = oldScript.textContent;
                            oldScript.parentNode.replaceChild(newScript, oldScript);
                        });
                    }
                })
                .catch(err => console.error('Load page error:', err));
        }
        
        function filterCategory(cat) {
            window.location.href = '/?cat=' + encodeURIComponent(cat);
        }
        
        // Open buy modal
        function openBuy(productId) {
            fetch('/api/products.php?action=get&id=' + productId)
                .then(res => res.json())
                .then(data => {
                    if (!data.success || !data.data) {
                        toast('Không tìm thấy sản phẩm');
                        return;
                    }
                    const p = data.data;
                    const packages = p.packages || [];
                    let pkgButtons = packages.map((g, i) => 
                        `<button class="cat ${i===0?'active':''}" data-pkg="${i}" onclick="selectPkg(${i})">${esc(g.name)} - ${money(g.price)}</button>`
                    ).join('');
                    
                    let mediaHtml = '';
                    if (p.media) {
                        if (p.media_type === 'video') {
                            mediaHtml = `<video src="${esc(p.media)}" muted autoplay loop playsinline style="width:100%;height:100%;object-fit:cover"></video>`;
                        } else {
                            mediaHtml = `<img src="${esc(p.media)}" style="width:100%;height:100%;object-fit:cover">`;
                        }
                    }
                    
                    showModal(`
                        <div class="modal-head">
                            <h3>${esc(p.name)}</h3>
                            <button class="x" onclick="closeModal()">×</button>
                        </div>
                        <div class="media" style="height:205px;border-radius:16px;margin-bottom:14px">${mediaHtml}</div>
                        <div class="notice">${nl2br(p.description||'')}</div>
                        <div class="field" style="margin-top:14px">
                            <label>Chọn gói giá</label>
                            <div class="cats" id="pkgSelect">${pkgButtons}</div>
                        </div>
                        <div class="panel" style="padding:13px;margin-bottom:12px">
                            <div class="sub">Số dư của bạn</div>
                            <b>${money(USER_DATA.balance)}</b>
                        </div>
                        <button class="btn primary" style="width:100%" onclick="confirmBuy('${p.id}')">Xác nhận mua</button>
                    `);
                    
                    window._selectedProduct = p;
                    window._selectedPkg = packages[0] || null;
                })
                .catch(err => toast('Lỗi tải sản phẩm'));
        }
        
        function selectPkg(i) {
            const p = window._selectedProduct;
            if (!p || !p.packages) return;
            window._selectedPkg = p.packages[i];
            document.querySelectorAll('#pkgSelect .cat').forEach((b, idx) => {
                b.classList.toggle('active', idx === i);
            });
        }
        
        function confirmBuy(productId) {
            const p = window._selectedProduct;
            const pkg = window._selectedPkg;
            if (!p || !pkg) return toast('Vui lòng chọn gói');
            if (USER_DATA.balance < pkg.price) {
                closeModal();
                switchPage('recharge');
                return toast('Số dư không đủ');
            }
            
            fetch('/api/orders.php?action=create', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    product_id: p.id,
                    package_name: pkg.name,
                    price: pkg.price
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    toast('Mua hàng thành công');
                    closeModal();
                    loadPage('orders');
                    // Update balance
                    USER_DATA.balance = data.new_balance || USER_DATA.balance;
                    document.getElementById('topBalance').textContent = money(USER_DATA.balance);
                } else {
                    toast(data.error || 'Mua hàng thất bại');
                }
            })
            .catch(err => toast('Lỗi: ' + err.message));
        }
        
        // Sidebar toggle
        document.getElementById('hamb').onclick = () => {
            document.getElementById('sidebar').classList.add('open');
            document.getElementById('drawerOverlay').classList.add('show');
        };
        document.getElementById('drawerOverlay').onclick = () => {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('drawerOverlay').classList.remove('show');
        };
        
        // Modal click outside
        document.getElementById('modal').addEventListener('click', e => {
            if (e.target.id === 'modal') closeModal();
        });
        
        // Search
        document.getElementById('globalSearch').addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                window.location.href = '/?search=' + encodeURIComponent(e.target.value.trim());
            }
        });
        
        // Notifications
        document.getElementById('btnNotify').onclick = () => {
            let html = NOTIFICATIONS.map(n => `
                <div class="notice" style="margin-bottom:10px">
                    <b>${esc(n.title)}</b><br>
                    ${nl2br(n.text)}
                    <div class="sub">${esc(n.created_at)}</div>
                </div>
            `).join('') || '<div class="empty">Chưa có thông báo</div>';
            showModal(`<div class="modal-head"><h3>Thông báo</h3><button class="x" onclick="closeModal()">×</button></div>${html}`);
        };
        
        // Avatar click
        document.getElementById('btnAvatarTop').onclick = () => switchPage('account');
        
        // Set active page
        document.querySelectorAll('.nav [data-page]').forEach(b => {
            b.classList.toggle('active', b.dataset.page === 'home');
        });
    </script>
</body>
</html>
