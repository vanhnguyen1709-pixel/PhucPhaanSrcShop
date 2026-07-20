<div class="admin-top">
    <div>
        <h2>Admin Cheating Game VN</h2>
        <div class="sub">Xin chào <?= esc($admin['name']) ?> • Quyền: <?= esc($admin['role']) ?></div>
    </div>
    <div class="tools">
        <button class="btn" onclick="location.href='index.php'">Mở shop</button>
    </div>
</div>
<?php 
$pendingCount = count(array_filter($topups, fn($t) => $t['status'] === 'pending'));
if ($pendingCount): ?>
<div class="notice admin-money-alert">
    <b>Thông báo duyệt tiền:</b> Có <?= $pendingCount ?> yêu cầu nạp đang chờ duyệt. 
    <button class="btn green" onclick="switchAdmin('topups')">Duyệt ngay</button>
</div>
<?php endif; ?>
<div class="stats">
    <div class="stat"><b><?= count($products) ?></b><div class="sub">Sản phẩm</div></div>
    <div class="stat"><b><?= count($orders) ?></b><div class="sub">Đơn hàng</div></div>
    <div class="stat"><b><?= $pendingCount ?></b><div class="sub">Nạp chờ duyệt</div></div>
    <div class="stat"><b><?= count($users) ?></b><div class="sub">UID</div></div>
</div>
<div class="row">
    <div class="panel">
        <h3>Đơn gần đây</h3>
        <?php include __DIR__ . '/../page_parts/orders_table.php'; ?>
    </div>
    <div class="panel">
        <h3>Nạp chờ duyệt</h3>
        <?php include __DIR__ . '/../page_parts/topups_table.php'; ?>
    </div>
</div>