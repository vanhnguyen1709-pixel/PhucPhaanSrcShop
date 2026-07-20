<?php
expirePendingTopups();
$topups = getAllTopups();
$pending = array_filter($topups, fn($t) => $t['status'] === 'pending');
?>
<div class="admin-top">
    <div>
        <h2>Duyệt nạp tiền <?= count($pending) ? '<span class="nav-badge big">' . count($pending) . '</span>' : '' ?></h2>
        <div class="sub">Bank MBANK 0792822868 / Thẻ cào có SERI + MÃ THẺ</div>
    </div>
</div>
<?php if (count($pending)): ?>
<div class="notice admin-money-alert">
    <b>Có <?= count($pending) ?> yêu cầu nạp chờ duyệt.</b> Kiểm tra đúng nội dung QR hoặc SERI/MÃ THẺ trước khi cộng tiền.
</div>
<?php endif; ?>
<div class="panel table-wrap">
    <?php include __DIR__ . '/../page_parts/topups_table.php'; ?>
</div>