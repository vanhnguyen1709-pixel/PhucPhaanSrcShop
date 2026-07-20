<?php
$list = $admin['role'] === 'root' ? $products : array_filter($products, fn($p) => $p['owner'] === $admin['username']);
?>
<div class="admin-top compact-admin-head">
    <div>
        <h2>Add file / sản phẩm</h2>
        <div class="sub">Gọn hơn: chia rõ 2 kiểu giao hàng: <b>File/Sản phẩm</b> hoặc <b>Key/Mô tả</b></div>
    </div>
    <button class="btn" onclick="openProductForm()">Làm mới form</button>
</div>
<div class="product-manage-layout">
    <div id="productForm"></div>
    <div class="panel table-wrap compact-list">
        <h3>Danh sách đang bán</h3>
        <?php if (count($list)): ?>
        <table class="table">
            <thead><tr><th>Media</th><th>Tên</th><th>Loại giao</th><th>Giá</th><th>Kho</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($list as $p): 
                $packages = getPackagesByProductId($p['id']);
                $deliveryType = $p['delivery_type'] ?? ($p['delivery_file_data'] ? 'file' : 'key');
            ?>
            <tr>
                <td>
                    <?php if ($p['media']): ?>
                        <?php if ($p['media_type'] === 'video'): ?>
                        <video class="preview" src="<?= esc($p['media']) ?>" muted></video>
                        <?php else: ?>
                        <img class="preview" src="<?= esc($p['media']) ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><b><?= esc($p['name']) ?></b><div class="sub"><?= esc($p['category']) ?> • Seller: <?= esc($p['owner']) ?></div></td>
                <td><?= $deliveryType === 'file' ? '<span class="badge green">FILE / SẢN PHẨM</span>' : '<span class="badge">KEY / MÔ TẢ</span>' ?></td>
                <td><?php foreach ($packages as $g): ?><span class="pkg"><?= esc($g['name']) ?>: <?= money($g['price']) ?></span><?php endforeach; ?></td>
                <td><?= intval($p['stock']) ?></td>
                <td class="actions-cell">
                    <button class="btn" onclick="openProductForm('<?= $p['id'] ?>')">Sửa</button>
                    <button class="btn red" onclick="delProduct('<?= $p['id'] ?>')">Xóa</button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <div class="empty">Chưa có sản phẩm</div>
        <?php endif; ?>
    </div>
</div>
<script>
let editProductId = null;
let productMedia = '';
let productMediaType = 'image';
let productDeliveryFileData = '';
let productDeliveryFileName = '';
let productDeliveryFileType = '';

function openProductForm(id = '') {
    // Load product data via AJAX
    if (id) {
        fetch('/api/products.php?action=get&id=' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderProductForm(data.data);
                }
            });
    } else {
        renderProductForm(null);
    }
}

function renderProductForm(p) {
    const isEdit = !!p;
    editProductId = isEdit ? p.id : null;
    const dType = p?.delivery_type || (p?.delivery_file_data ? 'file' : 'key');
    const packages = p?.packages || [{name: '1 ngày', price: 100000, deliver: ''}];
    const pkgLines = packages.map(g => g.name + '|' + g.price).join('\n');
    
    document.getElementById('productForm').innerHTML = `
    <div class="panel compact-form">
        <div class="form-title-row">
            <div>
                <h3>${isEdit ? 'Sửa' : 'Thêm'} file / sản phẩm</h3>
                <div class="sub">Điền ngắn gọn, chọn đúng kiểu giao hàng bên dưới.</div>
            </div>
            ${isEdit ? '<button class="btn" onclick="openProductForm()">+ Thêm mới</button>' : ''}
        </div>
        <div class="field"><label>Tên sản phẩm</label><input class="input" id="pName" value="${esc(p?.name||'')}" placeholder="VD: Bypass giả lập Free Fire"></div>
        <div class="form-2">
            <div class="field"><label>Danh mục</label><input class="input" id="pCat" value="${esc(p?.category||'FreeFire')}" placeholder="FreeFire, Tool, Acc..."></div>
            <div class="field"><label>Kho</label><input class="input" id="pStock" type="number" value="${parseInt(p?.stock||0)}"></div>
        </div>
        <div class="form-2">
            <div class="field"><label>Tag</label><input class="input" id="pTag" value="${esc(p?.tag||'ACTIVE')}" placeholder="ACTIVE"></div>
            <div class="field"><label>Ảnh/video hiển thị</label>
                <div class="file-inline">
                    <button class="upload-arrow" onclick="$('#pMediaFile').click()">↑</button>
                    <input id="pMediaFile" class="hidden-file" type="file" accept="image/*,video/*">
                    <input class="input" id="pMediaLink" placeholder="Link ảnh/video" value="${esc(p?.media||'')}">
                </div>
            </div>
        </div>
        <div id="mediaPreview" class="mini-preview">${p?.media ? (p.media_type === 'video' ? `<video class="preview" style="width:150px;height:82px" src="${esc(p.media)}" controls></video>` : `<img class="preview" style="width:150px;height:82px" src="${esc(p.media)}">`) : ''}</div>
        <div class="field"><label>Mô tả hiển thị ngoài shop</label><textarea id="pDesc" placeholder="Mô tả ngắn cho khách xem trước khi mua">${esc(p?.description||'')}</textarea></div>
        <div class="field"><label>Gói giá - mỗi dòng: Tên gói | Giá</label><textarea id="pPackages" class="small-textarea" placeholder="1 ngày | 15000\n7 ngày | 80000">${esc(pkgLines)}</textarea></div>
        <div class="delivery-box">
            <label class="section-label">Kiểu giao hàng sau khi mua</label>
            <div class="delivery-tabs">
                <label class="delivery-tab ${dType==='file'?'active':''}"><input type="radio" name="pDeliveryType" value="file" ${dType==='file'?'checked':''} onchange="toggleProductDelivery()"><b>File / Sản phẩm</b><small>Upload file, tool, tài liệu</small></label>
                <label class="delivery-tab ${dType==='key'?'active':''}"><input type="radio" name="pDeliveryType" value="key" ${dType!=='file'?'checked':''} onchange="toggleProductDelivery()"><b>Key / Mô tả</b><small>Nhập thủ công, không auto random</small></label>
            </div>
            <div id="deliveryFileBox" class="delivery-panel" style="display:${dType==='file'?'block':'none'}">
                <div class="file-inline">
                    <button class="upload-arrow" onclick="$('#pDeliveryFile').click()">↑</button>
                    <input id="pDeliveryFile" class="hidden-file" type="file">
                    <div id="pDeliveryFileName" class="notice file-name-box">${p?.delivery_file_name || 'Chưa chọn file giao hàng'}</div>
                </div>
                <div class="field no-margin"><label>Nội dung giao kèm file</label><textarea id="pDeliveryTextFile" class="small-textarea" placeholder="VD: Tải file, giải nén, xem hướng dẫn trong file...">${esc(dType==='file' ? (p?.delivery_text||'') : '')}</textarea></div>
            </div>
            <div id="deliveryKeyBox" class="delivery-panel" style="display:${dType==='file'?'none':'block'}">
                <div class="field no-margin"><label>Key hoặc mô tả giao cho khách</label><textarea id="pDeliveryTextKey" placeholder="Dán key / tài khoản / mô tả giao hàng tại đây.">${esc(dType==='file' ? '' : (p?.delivery_text||''))}</textarea></div>
            </div>
        </div>
        <button class="btn primary save-wide" onclick="saveProduct()">Lưu file / sản phẩm</button>
    </div>`;
    
    // Bind file uploads
    document.getElementById('pMediaFile').onchange = async function(e) {
        const f = e.target.files[0];
        if (!f) return;
        const data = await fileToDataURL(f);
        productMedia = data;
        productMediaType = f.type.startsWith('video/') ? 'video' : 'image';
        document.getElementById('mediaPreview').innerHTML = productMediaType === 'video' ?
            `<video class="preview" style="width:150px;height:82px" src="${data}" controls></video>` :
            `<img class="preview" style="width:150px;height:82px" src="${data}">`;
    };
    
    document.getElementById('pDeliveryFile').onchange = async function(e) {
        const f = e.target.files[0];
        if (!f) return;
        productDeliveryFileData = await fileToDataURL(f);
        productDeliveryFileName = f.name;
        productDeliveryFileType = f.type || 'application/octet-stream';
        document.getElementById('pDeliveryFileName').textContent = f.name;
    };
}

function toggleProductDelivery() {
    const type = document.querySelector('input[name="pDeliveryType"]:checked')?.value || 'key';
    document.querySelectorAll('.delivery-tab').forEach(l => l.classList.toggle('active', l.querySelector('input').value === type));
    const fb = document.getElementById('deliveryFileBox'), kb = document.getElementById('deliveryKeyBox');
    if (fb) fb.style.display = type === 'file' ? 'block' : 'none';
    if (kb) kb.style.display = type === 'file' ? 'none' : 'block';
}

function readPackages() {
    return document.getElementById('pPackages').value.split('\n').map(x => x.trim()).filter(Boolean).map(line => {
        const parts = line.split('|').map(s => s.trim());
        return { name: parts[0] || 'Gói', price: parseInt(parts[1]) || 0, deliver: parts.slice(2).join('|') || '' };
    }).filter(g => g.price > 0);
}

function saveProduct() {
    const link = document.getElementById('pMediaLink').value.trim();
    if (link) {
        productMedia = link;
        productMediaType = /\.mp4|\.webm|\.mov|video/i.test(link) ? 'video' : 'image';
    }
    const packages = readPackages();
    if (!packages.length) return toast('Nhập ít nhất 1 gói giá');
    
    const deliveryType = document.querySelector('input[name="pDeliveryType"]:checked')?.value || 'key';
    const deliveryText = (deliveryType === 'file' ? document.getElementById('pDeliveryTextFile').value : document.getElementById('pDeliveryTextKey').value).trim();
    if (deliveryType === 'file' && !productDeliveryFileData && !deliveryText) return toast('Chọn file hoặc nhập nội dung giao hàng');
    if (deliveryType === 'key' && !deliveryText) return toast('Nhập key hoặc mô tả giao hàng');
    
    const data = {
        name: document.getElementById('pName').value.trim(),
        category: document.getElementById('pCat').value.trim() || 'Sản phẩm',
        tag: document.getElementById('pTag').value.trim() || 'ACTIVE',
        stock: parseInt(document.getElementById('pStock').value) || 0,
        description: document.getElementById('pDesc').value.trim(),
        media: productMedia,
        media_type: productMediaType,
        delivery_type: deliveryType,
        delivery_text: deliveryText,
        delivery_file_name: deliveryType === 'file' ? productDeliveryFileName : '',
        delivery_file_type: deliveryType === 'file' ? productDeliveryFileType : '',
        delivery_file_data: deliveryType === 'file' ? productDeliveryFileData : ''
    };
    if (!data.name) return toast('Nhập tên sản phẩm');
    
    const url = editProductId ? '/api/products.php?action=update&id=' + editProductId : '/api/products.php?action=create';
    const method = editProductId ? 'PUT' : 'POST';
    
    fetch(url, {
        method: method,
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({...data, packages: packages})
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã lưu file / sản phẩm');
            switchAdmin('products');
        } else {
            toast(result.error || 'Lỗi khi lưu');
        }
    })
    .catch(err => toast('Lỗi: ' + err.message));
}

function delProduct(id) {
    if (!confirm('Xóa sản phẩm này?')) return;
    fetch('/api/products.php?action=delete&id=' + id, {method: 'DELETE'})
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                toast('Đã xóa sản phẩm');
                switchAdmin('products');
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

// Auto load form
openProductForm();
</script>