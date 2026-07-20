<?php if ($admin['role'] !== 'root'): ?>
<div class="empty">Chỉ admin gốc mới có quyền này</div>
<?php else: ?>
<div class="admin-top">
    <div><h2>Seller con</h2><div class="sub">Admin gốc tạo tài khoản cho seller</div></div>
</div>
<div class="row">
    <div class="panel">
        <h3>Tạo seller</h3>
        <div class="field"><label>Tài khoản</label><input class="input" id="sUser"></div>
        <div class="field"><label>Mật khẩu</label><input class="input" id="sPass"></div>
        <div class="field"><label>Tên seller</label><input class="input" id="sName"></div>
        <button class="btn primary" onclick="createSeller()">Tạo seller</button>
    </div>
    <div class="panel table-wrap">
        <h3>Danh sách</h3>
        <table class="table">
            <tbody>
            <?php foreach ($admins as $a): ?>
            <tr>
                <td><?= esc($a['username']) ?></td>
                <td><?= esc($a['role']) ?></td>
                <td><?= esc($a['name']) ?></td>
                <td><span class="badge <?= $a['active'] ? 'green' : 'red' ?>"><?= $a['active'] ? 'active' : 'off' ?></span></td>
                <td><?= $a['role'] !== 'root' ? '<button class="btn" onclick="toggleSeller(\'' . $a['username'] . '\')">Bật/Tắt</button>' : '' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function createSeller() {
    const username = document.getElementById('sUser').value.trim();
    const password = document.getElementById('sPass').value;
    const name = document.getElementById('sName').value.trim();
    if (!username || !password) return toast('Nhập tài khoản/mật khẩu');
    
    fetch('/api/admin.php?action=create_seller', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({username, password, name: name || username})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã tạo seller');
            switchAdmin('sellers');
        } else {
            toast(result.error || 'Lỗi');
        }
    });
}

function toggleSeller(username) {
    fetch('/api/admin.php?action=toggle_seller', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({username})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            switchAdmin('sellers');
        }
    });
}
</script>
<?php endif; ?>