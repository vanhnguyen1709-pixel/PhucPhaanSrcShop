<?php
// admin_pages/downloads.php
$list = $admin['role'] === 'root' ? $downloads : array_filter($downloads, fn($d) => $d['owner'] === $admin['username']);
$downloadFileData = '';
$downloadFileName = '';
$downloadFileType = '';
?>

<div class="admin-top">
    <div>
        <h2>Upload cho Tải xuống</h2>
        <div class="sub">Admin/seller upload file miễn phí, launcher hoặc hướng dẫn hiển thị ở shop</div>
    </div>
</div>

<div class="row">
    <div class="panel">
        <h3>Thêm file tải xuống</h3>
        <div class="field">
            <label>Tên file / tiêu đề</label>
            <input class="input" id="dTitle" placeholder="VD: Launcher, Hướng dẫn, File fix lag">
        </div>
        <div class="field">
            <label>Mô tả</label>
            <textarea id="dDesc" placeholder="Mô tả ngắn cho khách"></textarea>
        </div>
        <div class="field">
            <label>Upload file bằng nút ↑</label>
            <div style="display:flex;gap:10px;align-items:center">
                <button class="upload-arrow" onclick="$('#dFile').click()">↑</button>
                <input id="dFile" class="hidden-file" type="file">
                <div id="dFileName" class="notice" style="flex:1;margin:0">Chưa chọn file</div>
            </div>
        </div>
        <button class="btn primary" onclick="saveDownloadItem()">Lưu file tải xuống</button>
    </div>

    <div class="panel table-wrap">
        <h3>Danh sách file</h3>
        <?php if (count($list)): ?>
        <table class="table">
            <thead><tr><th>Tên</th><th>File</th><th>Seller</th><th>Trạng thái</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($list as $d): ?>
            <tr>
                <td>
                    <b><?= esc($d['title']) ?></b>
                    <div class="sub"><?= esc($d['description'] ?? '') ?></div>
                </td>
                <td><?= esc($d['file_name'] ?? '') ?></td>
                <td><?= esc($d['owner'] ?? 'admin') ?></td>
                <td><span class="badge <?= ($d['active'] ?? 1) ? 'green' : 'red' ?>"><?= ($d['active'] ?? 1) ? 'hiện' : 'ẩn' ?></span></td>
                <td>
                    <button class="btn" onclick="toggleDownload('<?= $d['id'] ?>')">Ẩn/Hiện</button>
                    <button class="btn red" onclick="deleteDownload('<?= $d['id'] ?>')">Xóa</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty">Chưa có file tải xuống</div>
        <?php endif; ?>
    </div>
</div>

<script>
let downloadFileData = '';
let downloadFileName = '';
let downloadFileType = '';

document.getElementById('dFile').onchange = async function(e) {
    const f = e.target.files[0];
    if (!f) return;
    downloadFileData = await fileToDataURL(f);
    downloadFileName = f.name;
    downloadFileType = f.type || 'application/octet-stream';
    document.getElementById('dFileName').textContent = f.name;
    toast('Đã chọn file: ' + f.name);
};

function saveDownloadItem() {
    const title = document.getElementById('dTitle').value.trim();
    if (!title) return toast('Nhập tiêu đề file');
    if (!downloadFileData) return toast('Chọn file bằng nút ↑');
    
    const data = {
        title: title,
        description: document.getElementById('dDesc').value.trim(),
        file_name: downloadFileName,
        file_type: downloadFileType,
        file_data: downloadFileData
    };
    
    fetch('/api/admin.php?action=create_download', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã thêm file tải xuống');
            switchAdmin('downloads');
        } else {
            toast(result.error || 'Lỗi khi lưu');
        }
    })
    .catch(err => toast('Lỗi: ' + err.message));
}

function toggleDownload(id) {
    fetch('/api/admin.php?action=toggle_download&id=' + id, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã cập nhật trạng thái');
            switchAdmin('downloads');
        }
    });
}

function deleteDownload(id) {
    if (!confirm('Xóa file tải xuống này?')) return;
    fetch('/api/admin.php?action=delete_download&id=' + id, {
        method: 'POST'
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã xóa file');
            switchAdmin('downloads');
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