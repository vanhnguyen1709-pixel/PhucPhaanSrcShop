<div class="admin-top">
    <div><h2>UID người dùng</h2><div class="sub">Đổi thông tin UID, mật khẩu, avatar, số dư</div></div>
</div>
<div class="panel table-wrap">
    <?php if (count($users)): ?>
    <table class="table">
        <thead><tr><th>UID</th><th>Tên</th><th>Liên kết</th><th>Số dư</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= esc($u['uid']) ?></td>
            <td><?= esc($u['name']) ?></td>
            <td><?= esc($u['email'] ?: $u['phone'] ?: '') ?></td>
            <td><?= money($u['balance']) ?></td>
            <td><button class="btn" onclick="editUser('<?= $u['uid'] ?>')">Sửa</button></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div class="empty">Chưa có UID</div>
    <?php endif; ?>
</div>
<div id="userForm"></div>
<script>
function editUser(uid) {
    fetch('/api/users.php?action=get&uid=' + uid)
        .then(res => res.json())
        .then(data => {
            if (!data.success) return;
            const u = data.data;
            document.getElementById('userForm').innerHTML = `
            <div class="panel" style="margin-top:16px">
                <h3>Sửa UID <?= esc($u['uid']) ?></h3>
                <div class="row">
                    <div>
                        <div class="field"><label>Tên</label><input class="input" id="euName" value="${esc(u.name)}"></div>
                        <div class="field"><label>Email</label><input class="input" id="euEmail" value="${esc(u.email||'')}"></div>
                        <div class="field"><label>SĐT</label><input class="input" id="euPhone" value="${esc(u.phone||'')}"></div>
                    </div>
                    <div>
                        <div class="field"><label>Mật khẩu UID</label><input class="input" id="euPass" value="${esc(u.password)}"></div>
                        <div class="field"><label>Số dư</label><input class="input" id="euBal" type="number" value="${parseInt(u.balance)||0}"></div>
                        <button class="btn primary" onclick="saveEditUser('${u.uid}')">Lưu UID</button>
                    </div>
                </div>
            </div>`;
        });
}

function saveEditUser(uid) {
    const data = {
        uid: uid,
        name: document.getElementById('euName').value,
        email: document.getElementById('euEmail').value,
        phone: document.getElementById('euPhone').value,
        password: document.getElementById('euPass').value || '123456',
        balance: parseInt(document.getElementById('euBal').value) || 0
    };
    fetch('/api/users.php?action=admin_update', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã lưu UID');
            switchAdmin('users');
        }
    });
}
</script>