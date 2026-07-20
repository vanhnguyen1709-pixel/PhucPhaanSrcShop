<?php if ($admin['role'] !== 'root'): ?>
<div class="empty">Chỉ admin gốc mới có quyền này</div>
<?php else: ?>
<div class="admin-top">
    <div><h2>Cài đặt shop</h2><div class="sub">Tên, logo admin/shop, thông báo, bank/card</div></div>
</div>
<div class="panel">
    <div class="row">
        <div>
            <div class="field">
                <label>Logo admin/shop</label>
                <div style="display:flex;gap:12px;align-items:center">
                    <img id="logoPreview" class="preview" style="width:180px;height:82px;object-fit:contain" src="<?= esc($settings['logo'] ?? '') ?>">
                    <button class="upload-arrow" onclick="$('#logoFile').click()">↑</button>
                    <input id="logoFile" class="hidden-file" type="file" accept="image/*">
                </div>
            </div>
            <div class="field"><label>Tên shop</label><input class="input" id="setShop" value="<?= esc($settings['shop_name'] ?? '') ?>"></div>
            <div class="field"><label>Thông báo chạy ngang</label><textarea id="setAnn"><?= esc($settings['announcement'] ?? '') ?></textarea></div>
            <div class="field"><label>Đối tác</label><input class="input" id="setPartner" value="<?= esc($settings['partner'] ?? '') ?>"></div>
            <div class="field"><label>Zalo</label><input class="input" id="setZalo" value="<?= esc($settings['zalo'] ?? '') ?>"></div>
        </div>
        <div>
            <div class="field"><label>Ngân hàng</label><input class="input" id="setBank" value="<?= esc($settings['bank_name'] ?? 'MBANK') ?>"></div>
            <div class="field"><label>Số tài khoản</label><input class="input" id="setBankNo" value="<?= esc($settings['bank_number'] ?? '0792822868') ?>"></div>
            <div class="field"><label>Chủ tài khoản</label><input class="input" id="setBankOwner" value="<?= esc($settings['bank_owner'] ?? '') ?>"></div>
            <div class="field"><label>Ghi chú thẻ cào</label><textarea id="setCard"><?= esc($settings['card_note'] ?? '') ?></textarea></div>
        </div>
    </div>
    <button class="btn primary" onclick="saveSettings()">Lưu cài đặt</button>
</div>
<script>
let settingsLogo = '<?= $settings['logo'] ?? '' ?>';

document.getElementById('logoFile').onchange = async function(e) {
    const f = e.target.files[0];
    if (!f) return;
    settingsLogo = await fileToDataURL(f);
    document.getElementById('logoPreview').src = settingsLogo;
    toast('Đã chọn logo mới');
};

function saveSettings() {
    const data = {
        shop_name: document.getElementById('setShop').value,
        logo: settingsLogo,
        announcement: document.getElementById('setAnn').value,
        partner: document.getElementById('setPartner').value,
        zalo: document.getElementById('setZalo').value,
        bank_name: document.getElementById('setBank').value,
        bank_number: document.getElementById('setBankNo').value,
        bank_owner: document.getElementById('setBankOwner').value,
        card_note: document.getElementById('setCard').value
    };
    
    fetch('/api/admin.php?action=update_settings', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã lưu cài đặt');
            location.reload();
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
<?php endif; ?>