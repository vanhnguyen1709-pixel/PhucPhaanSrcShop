<?php
// page_parts/recharge.php
$amounts = [50000, 100000, 200000, 500000, 1000000, 2000000];
$telcos = ['VIETTEL', 'MOBIFONE', 'VINAPHONE', 'VIETNAMOBILE', 'ZING', 'GARENA'];
$qrCode = generateRandomCode(12);
$expiresAt = time() + 15 * 60;
?>
<div class="recharge-header">
    <h2>💎 Ví tiền</h2>
    <div class="balance-display">
        <span class="label">Số dư</span>
        <span class="amount"><?= money($user['balance']) ?></span>
        <span class="min-label">Min 50.000đ</span>
    </div>
</div>

<div class="recharge-tabs">
    <button class="active" data-method="bank" onclick="choosePay('bank')">
        <span class="tab-icon">🏦</span>
        <div class="tab-text">
            <b>Nạp ngân hàng</b>
            <small>VietQR MB • 15 phút</small>
        </div>
    </button>
    <button data-method="card" onclick="choosePay('card')">
        <span class="tab-icon">💳</span>
        <div class="tab-text">
            <b>Nạp thẻ cào</b>
            <small>Viettel, Mobi, Vina...</small>
        </div>
    </button>
</div>

<!-- Bank Recharge -->
<div id="bankRecharge" class="recharge-layout">
    <div class="recharge-panel">
        <div class="panel-title">
            <span class="icon">▣</span> Thông tin nạp
        </div>
        
        <div class="bank-selector active">
            <span class="bank-name">✦ MBANK</span>
            <span class="bank-detail">0792822868</span>
            <span class="bank-note">Chuyển khoản đúng nội dung để duyệt nhanh</span>
        </div>

        <div class="amount-section">
            <div class="amount-label">Chọn số tiền</div>
            <div class="amount-grid-clean">
                <?php foreach ($amounts as $a): ?>
                <button class="<?= $a === 50000 ? 'active' : '' ?>" data-amount="<?= $a ?>" onclick="chooseAmount(<?= $a ?>)"><?= money($a) ?></button>
                <?php endforeach; ?>
            </div>
            <input class="custom-amount-input" id="customAmount" type="number" min="50000" step="1000" placeholder="Hoặc nhập số tiền khác...">
            <input class="note-input" id="topupNote" placeholder="Ghi chú thêm (có thể bỏ trống)">
        </div>

        <div class="recharge-actions-clean">
            <button class="btn" onclick="regenQr()">🔄 Tạo QR mới</button>
            <button id="btnSubmitTopup" class="btn primary" onclick="submitTopup()">Gửi yêu cầu nạp</button>
        </div>
    </div>

    <div class="payment-preview" id="payPreview"></div>
</div>

<!-- Card Recharge -->
<div id="cardRecharge" class="card-recharge-layout" style="display:none">
    <div class="card-recharge-panel">
        <div class="section-title">Chọn loại thẻ</div>
        <div class="telco-grid-clean">
            <?php foreach ($telcos as $i => $t): ?>
            <button class="<?= $i === 0 ? 'active' : '' ?>" data-telco="<?= $t ?>" onclick="chooseTelco('<?= $t ?>')"><?= ucfirst(strtolower($t)) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="section-title">Mệnh giá</div>
        <div class="amount-grid-clean">
            <?php foreach ($amounts as $a): ?>
            <button class="<?= $a === 50000 ? 'active' : '' ?>" data-amount="<?= $a ?>" onclick="chooseAmount(<?= $a ?>)"><?= money($a) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="card-discount-display">
            <div class="discount-rate">
                <span>Chiết khấu</span>
                <b>15%</b>
            </div>
            <div class="receive-amount">
                <span>Thực nhận</span>
                <b id="cardReceive">42.500đ</b>
            </div>
        </div>

        <div class="card-form-grid-clean">
            <div class="field">
                <label>Số seri</label>
                <input class="input" id="cardSerial" placeholder="Nhập số seri thẻ">
            </div>
            <div class="field">
                <label>Mã thẻ</label>
                <input class="input" id="cardCode" placeholder="Nhập mã thẻ">
            </div>
        </div>

        <div class="card-request-code-display">
            <span class="label">Mã yêu cầu</span>
            <span class="code" id="cardRequestCode"><?= esc($qrCode) ?></span>
            <span class="timer-text">Còn lại <strong id="qrTimerCard">15:00</strong></span>
        </div>

        <button id="btnSubmitCard" class="btn primary card-submit-btn" onclick="submitTopup()">✈ Gửi thẻ</button>
    </div>
</div>

<script>
let pickedTopupAmount = 50000;
let pickedPayMethod = 'bank';
let currentQrContent = '<?= $qrCode ?>';
let qrExpireAt = <?= $expiresAt * 1000 ?>;
let qrTimer = null;
let selectedTelco = 'VIETTEL';

function choosePay(method) {
    pickedPayMethod = method;
    document.querySelectorAll('.recharge-tabs [data-method]').forEach(b => {
        b.classList.toggle('active', b.dataset.method === method);
    });
    document.getElementById('bankRecharge').style.display = method === 'bank' ? 'grid' : 'none';
    document.getElementById('cardRecharge').style.display = method === 'card' ? 'block' : 'none';
    regenQr();
}

function chooseAmount(amount) {
    pickedTopupAmount = amount;
    document.querySelectorAll('[data-amount]').forEach(b => {
        b.classList.toggle('active', parseInt(b.dataset.amount) === amount);
    });
    document.getElementById('customAmount').value = '';
    regenQr();
    updatePayInfo();
}

function chooseTelco(telco) {
    selectedTelco = telco;
    document.querySelectorAll('.telco-grid-clean button').forEach(b => {
        b.classList.toggle('active', b.dataset.telco === telco);
    });
}

function currentTopupAmount() {
    const custom = parseInt(document.getElementById('customAmount')?.value) || 0;
    return custom >= 50000 ? custom : pickedTopupAmount;
}

function currentCreditAmount() {
    return pickedPayMethod === 'card' ? Math.floor(currentTopupAmount() * 0.85) : currentTopupAmount();
}

function regenQr() {
    const amount = currentTopupAmount();
    if (amount < 50000) return toast('Min nạp 50.000đ');
    
    fetch('/api/topups.php?action=new_qr&amount=' + amount)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                currentQrContent = data.qr_content;
                qrExpireAt = data.expires_at_ms;
                updatePayInfo();
                startQrTimer();
                toast('Đã tạo mã mới, hiệu lực 15 phút');
            }
        });
}

function updatePayInfo() {
    const amount = currentTopupAmount();
    const credit = currentCreditAmount();
    const expired = Date.now() >= qrExpireAt;
    
    document.getElementById('cardReceive').textContent = money(credit);
    document.getElementById('cardRequestCode').textContent = currentQrContent;
    
    const box = document.getElementById('payPreview');
    if (!box) return;
    
    box.innerHTML = `
    <div class="preview-header">
        <h3>▤ Thông tin chuyển khoản</h3>
        <span class="status-badge ${expired ? 'offline' : 'online'}">${expired ? 'HẾT HẠN' : 'ONLINE'}</span>
    </div>
    <div class="preview-qr-section">
        <img class="qr-image" src="${vietQrUrl(amount, currentQrContent)}" alt="VietQR MB">
        <div class="qr-info">
            <div class="sub center">Quét mã QR bằng app ngân hàng</div>
            <div class="timer">Còn lại <b id="qrTimer">15:00</b></div>
        </div>
    </div>
    <div class="bank-info-list">
        <div class="bank-info-item">
            <span class="label">Ngân hàng</span>
            <span class="value">MBANK</span>
            <button class="copy-btn" onclick="copyText('MBANK')">⧉</button>
        </div>
        <div class="bank-info-item">
            <span class="label">Số TK</span>
            <span class="value">0792822868</span>
            <button class="copy-btn" onclick="copyText('0792822868')">⧉</button>
        </div>
        <div class="bank-info-item">
            <span class="label">Chủ TK</span>
            <span class="value"><?= esc($settings['bank_owner'] ?? 'CHEATING GAME VN') ?></span>
            <button class="copy-btn" onclick="copyText('<?= esc($settings['bank_owner'] ?? 'CHEATING GAME VN') ?>')">⧉</button>
        </div>
        <div class="bank-info-item">
            <span class="label">Số tiền</span>
            <span class="value">${money(amount)}</span>
            <button class="copy-btn" onclick="copyText('${amount}')">⧉</button>
        </div>
        <div class="bank-info-item highlight">
            <span class="label">Nội dung</span>
            <span class="value">${currentQrContent}</span>
            <button class="copy-btn" onclick="copyText('${currentQrContent}')">⧉</button>
        </div>
    </div>
    <div class="preview-note">
        <b>Lưu ý:</b> Chuyển khoản đúng nội dung để admin duyệt nhanh. Mỗi mã QR chỉ hiệu lực 15 phút.
    </div>`;
}

function startQrTimer() {
    clearInterval(qrTimer);
    const tick = () => {
        const left = qrExpireAt - Date.now();
        document.querySelectorAll('#qrTimer, #qrTimerCard').forEach(el => {
            if (el) {
                el.textContent = formatCountdown(left);
                el.classList.toggle('danger', left <= 60000);
            }
        });
        document.querySelectorAll('#btnSubmitTopup, #btnSubmitCard').forEach(btn => {
            if (btn) {
                btn.disabled = left <= 0;
                btn.textContent = left <= 0 ? 'Mã đã hết hạn - tạo mã mới' : 
                    (btn.id === 'btnSubmitCard' ? '✈ Gửi thẻ' : 'Gửi yêu cầu nạp');
            }
        });
        if (left <= 0) updatePayInfo();
    };
    tick();
    qrTimer = setInterval(tick, 1000);
}

function formatCountdown(ms) {
    ms = Math.max(0, ms);
    const m = Math.floor(ms / 60000);
    const s = Math.floor((ms % 60000) / 1000);
    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
}

function copyText(text) {
    navigator.clipboard?.writeText(text).then(() => toast('Đã copy')).catch(() => toast('Không copy được'));
}

function submitTopup() {
    const amount = currentTopupAmount();
    if (amount < 50000) return toast('Số tiền tối thiểu 50.000đ');
    if (Date.now() >= qrExpireAt) return toast('QR/Mã yêu cầu đã hết hạn, hãy tạo mã mới');
    
    let data = {
        amount: currentCreditAmount(),
        face_amount: amount,
        method: pickedPayMethod,
        qr_content: currentQrContent
    };
    
    if (pickedPayMethod === 'card') {
        const serial = document.getElementById('cardSerial').value.trim();
        const code = document.getElementById('cardCode').value.trim();
        if (!serial || !code) return toast('Nhập đủ SERI và MÃ THẺ');
        data.card_telco = selectedTelco;
        data.card_serial = serial;
        data.card_code = code;
        data.note = `Nhà mạng: ${selectedTelco} | SERI: ${serial} | MÃ THẺ: ${code}`;
    } else {
        data.note = document.getElementById('topupNote').value.trim();
    }
    
    fetch('/api/topups.php?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(data)
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            toast('Đã gửi yêu cầu nạp, chờ admin duyệt');
            switchPage('history');
        } else {
            toast(result.error || 'Lỗi');
        }
    });
}

// Init
updatePayInfo();
startQrTimer();

document.getElementById('customAmount').addEventListener('input', function() {
    const val = parseInt(this.value) || 0;
    if (val >= 50000) {
        document.querySelectorAll('[data-amount]').forEach(b => b.classList.remove('active'));
        regenQr();
        updatePayInfo();
    }
});
</script>