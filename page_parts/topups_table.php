<?php
// page_parts/topups_table.php
$list = $topups ?? [];
?>
<?php if (count($list)): ?>
<table class="table">
    <thead>
        <tr>
            <th>UID</th>
            <th>Method</th>
            <th>Số tiền cộng</th>
            <th>Mệnh giá</th>
            <th>Bank/QR</th>
            <th>SERI</th>
            <th>MÃ THẺ</th>
            <th>Hạn QR</th>
            <th>Ghi chú</th>
            <th>Trạng thái</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($list as $t): ?>
    <tr>
        <td><?= esc($t['uid']) ?></td>
        <td><?= $t['method'] === 'bank' ? 'MBANK' : 'Thẻ cào' ?></td>
        <td><?= money($t['amount']) ?></td>
        <td><?= $t['face_amount'] ? money($t['face_amount']) : '' ?></td>
        <td>
            <?php if ($t['method'] === 'bank'): ?>
                <b>MBANK</b><br>0792822868<br>
                <span class="code"><?= esc($t['qr_content'] ?? '') ?></span>
            <?php else: ?>
                <span class="code"><?= esc($t['qr_content'] ?? '') ?></span>
            <?php endif; ?>
        </td>
        <td><?= esc($t['card_serial'] ?? '') ?></td>
        <td><?= esc($t['card_code'] ?? '') ?></td>
        <td><?= esc($t['expires_at'] ?? '') ?></td>
        <td><?= esc($t['note'] ?? '') ?></td>
        <td>
            <span class="badge <?= $t['status'] === 'approved' ? 'green' : (($t['status'] === 'rejected' || $t['status'] === 'expired') ? 'red' : '') ?>">
                <?= esc($t['status']) ?>
            </span>
        </td>
        <td>
            <?php if ($t['status'] === 'pending'): ?>
                <button class="btn green" onclick="approveTopup('<?= $t['id'] ?>')">Duyệt</button>
                <button class="btn red" onclick="rejectTopup('<?= $t['id'] ?>')">Từ chối</button>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<div class="empty">Không có dữ liệu</div>
<?php endif; ?>

<script>
function approveTopup(id) {
    if (!confirm('Duyệt yêu cầu nạp này?')) return;
    fetch('/api/topups.php?action=approve', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã duyệt nạp và gửi thông báo');
            switchAdmin('topups');
        }
    });
}

function rejectTopup(id) {
    if (!confirm('Từ chối yêu cầu nạp này?')) return;
    fetch('/api/topups.php?action=reject', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã từ chối và gửi thông báo');
            switchAdmin('topups');
        }
    });
}
</script>