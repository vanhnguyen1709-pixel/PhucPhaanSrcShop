<?php
// page_parts/history.php
$topups = getTopupsByUid($user['uid']);
?>
<div class="section-head">
    <div>
        <h2>Lịch sử giao dịch</h2>
        <div class="sub">Nạp tiền qua bank hoặc thẻ cào</div>
    </div>
</div>

<div class="panel table-wrap">
    <?php if (count($topups)): ?>
    <table class="table">
        <thead>
            <tr>
                <th>Phương thức</th>
                <th>Số tiền</th>
                <th>Mệnh giá</th>
                <th>Trạng thái</th>
                <th>Thời gian</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($topups as $t): ?>
        <tr>
            <td><?= $t['method'] === 'bank' ? '🏦 Bank' : '💳 Thẻ cào' ?></td>
            <td><?= money($t['amount']) ?></td>
            <td><?= $t['face_amount'] ? money($t['face_amount']) : '' ?></td>
            <td>
                <span class="badge <?= $t['status'] === 'approved' ? 'green' : ($t['status'] === 'pending' ? '' : 'red') ?>">
                    <?= esc($t['status']) ?>
                </span>
            </td>
            <td><?= esc($t['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty">Chưa có giao dịch nạp tiền</div>
    <?php endif; ?>
</div>