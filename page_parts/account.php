<?php
// page_parts/account.php
?>
<div class="section-head">
    <div>
        <h2><span class="section-line"></span>Thông tin cá nhân</h2>
        <div class="sub">UID có mật khẩu riêng, có thể login trên máy khác</div>
    </div>
</div>

<div class="account-profile">
    <!-- Account Info Card -->
    <div class="account-card">
        <div class="account-avatar-section">
            <img class="account-avatar" src="<?= esc($user['avatar'] ?: 'assets/products/avatar.svg') ?>" alt="avatar">
            <div class="account-avatar-info">
                <span class="uid"><?= esc($user['uid']) ?></span>
                <span class="uid-label">UID</span>
                <div class="default-pass">Mật khẩu mặc định: 123456</div>
            </div>
            <button class="account-avatar-upload" onclick="$('#avatarFile').click()">↑</button>
            <input id="avatarFile" class="hidden-file" type="file" accept="image/*">
        </div>
        
        <div class="account-field">
            <label>Tên hiển thị</label>
            <input class="input" id="uName" value="<?= esc($user['name'] ?? 'Thành viên') ?>" placeholder="Nhập tên hiển thị">
        </div>
        <div class="account-field">
            <label>Email liên kết</label>
            <input class="input" id="uEmail" value="<?= esc($user['email'] ?? '') ?>" placeholder="Nhập email">
        </div>
        <div class="account-field">
            <label>Số điện thoại</label>
            <input class="input" id="uPhone" value="<?= esc($user['phone'] ?? '') ?>" placeholder="Nhập số điện thoại">
        </div>
        <div class="account-field">
            <label>Mật khẩu UID</label>
            <input class="input" id="uPass" value="<?= esc($user['password'] ?? '123456') ?>" placeholder="Nhập mật khẩu mới">
        </div>
        
        <button class="btn primary account-btn" onclick="saveAccount()">Lưu thông tin</button>
    </div>

    <!-- Login Card -->
    <div class="account-card">
        <div class="account-login-section">
            <h4>Login UID khác</h4>
            <div class="field">
                <label>UID</label>
                <input class="input" id="loginUid" placeholder="UID-XXXXXX">
            </div>
            <div class="field">
                <label>Mật khẩu UID</label>
                <input class="input" id="loginPass" type="password" placeholder="Nhập mật khẩu">
            </div>
            <button class="btn primary account-login-btn" onclick="loginOtherUid()">Đăng nhập UID</button>
        </div>
    </div>
</div>

<script>
document.getElementById('avatarFile').onchange = async function(e) {
    const f = e.target.files[0];
    if (!f) return;
    const data = await fileToDataURL(f);
    
    fetch('/api/users.php?action=update', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({avatar: data})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã đổi avatar');
            location.reload();
        }
    });
};

function saveAccount() {
    const data = {
        name: document.getElementById('uName').value.trim() || 'Thành viên',
        email: document.getElementById('uEmail').value.trim(),
        phone: document.getElementById('uPhone').value.trim(),
        password: document.getElementById('uPass').value.trim() || '123456'
    };
    
    fetch('/api/users.php?action=update', {
        method: 'PUT',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã lưu tài khoản');
            location.reload();
        }
    });
}

function loginOtherUid() {
    const uid = document.getElementById('loginUid').value.trim();
    const password = document.getElementById('loginPass').value;
    
    fetch('/api/users.php?action=login', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({uid, password})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã đăng nhập UID');
            location.reload();
        } else {
            toast('Sai UID hoặc mật khẩu');
        }
    });
}

function fileToDataURL(file) {
    return new Promise((resolve, reject) => {
        const r = new FileReader();
        r.onload = () => resolve(r.result);
        r.onerror = reject;
        r.readAsDataURL(file);
    });
}
</script>