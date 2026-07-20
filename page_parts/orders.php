<?php
// page_parts/orders.php
$orders = getOrdersByUid($user['uid']);
?>
<div class="section-head">
    <div>
        <h2>Đơn hàng của tôi</h2>
        <div class="sub">Key/mô tả hoặc file hiển thị đúng theo admin đã gắn, không tự sinh key random.</div>
    </div>
</div>

<div class="panel table-wrap">
    <?php if (count($orders)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Gói</th>
                <th>Giá</th>
                <th>Thông tin nhận hàng</th>
                <th>Thời gian</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
        <tr>
            <td><?= esc($o['product_name']) ?></td>
            <td><?= esc($o['package_name']) ?></td>
            <td><?= money($o['price']) ?></td>
            <td>
                <?php if ($o['file_data']): ?>
                    <a class="btn primary tiny" href="<?= esc($o['file_data']) ?>" download="<?= esc($o['file_name'] ?? 'file') ?>">
                        Tải file: <?= esc($o['file_name'] ?? 'file') ?>
                    </a>
                <?php else: ?>
                    <div class="order-deliver-text"><?= nl2br_esc($o['deliver'] ?? 'Chưa có nội dung giao') ?></div>
                <?php endif; ?>
            </td>
            <td><?= esc($o['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty">Chưa có đơn hàng</div>
    <?php endif; ?>
</div>