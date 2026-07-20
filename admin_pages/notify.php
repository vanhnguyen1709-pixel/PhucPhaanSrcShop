<div class="admin-top">
    <div><h2>Gửi thông báo</h2><div class="sub">Hiện ở nút thông báo bên shop</div></div>
</div>
<div class="row">
    <div class="panel">
        <div class="field"><label>Tiêu đề</label><input class="input" id="nTitle"></div>
        <div class="field"><label>Nội dung</label><textarea id="nText"></textarea></div>
        <button class="btn primary" onclick="sendNotify()">Gửi thông báo</button>
    </div>
    <div class="panel">
        <h3>Lịch sử</h3>
        <?php foreach ($notifications as $n): ?>
        <div class="notice" style="margin-bottom:10px">
            <b><?= esc($n['title']) ?></b><br>
            <?= nl2br_esc($n['text']) ?>
            <div class="sub"><?= esc($n['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($notifications)): ?>
        <div class="empty">Chưa có</div>
        <?php endif; ?>
    </div>
</div>
<script>
function sendNotify() {
    const title = document.getElementById('nTitle').value.trim();
    const text = document.getElementById('nText').value.trim();
    if (!title || !text) return toast('Nhập đủ tiêu đề và nội dung');
    
    fetch('/api/admin.php?action=send_notification', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({title, text})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã gửi thông báo');
            switchAdmin('notify');
        }
    });
}
</script>