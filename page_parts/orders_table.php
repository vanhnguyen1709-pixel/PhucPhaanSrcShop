<?php
// page_parts/orders_table.php
$list = $orders ?? [];
$limit = 7;
$list = array_slice($list, 0, $limit);
?>
<?php if (count($list)): ?>
<table class="table">
    <thead>
        <tr>
            <th>UID</th>
            <th>Sản phẩm</th>
            <th>Gói</th>
            <th>Giá</th>
            <th>Giao hàng</th>
            <th>Seller</th>
            <th>Thời gian</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($list as $o): ?>
    <tr>
        <td><?= esc($o['uid']) ?></td>
        <td><?= esc($o['product_name']) ?></td>
        <td><?= esc($o['package_name']) ?></td>
        <td><?= money($o['price']) ?></td>
        <td>
            <?php if ($o['file_data']): ?>
                <a class="btn tiny" href="<?= esc($o['file_data']) ?>" download="<?= esc($o['file_name'] ?? 'file') ?>">
                    Tải file
                </a>
            <?php else: ?>
                <span class="code"><?= esc(substr($o['deliver'] ?? '', 0, 80)) ?></span>
            <?php endif; ?>
        </td>
        <td><?= esc($o['seller']) ?></td>
        <td><?= esc($o['created_at']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div class="empty">Chưa có đơn</div>
<?php endif; ?>