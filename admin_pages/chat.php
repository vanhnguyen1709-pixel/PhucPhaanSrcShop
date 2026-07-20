<?php
$chatUids = getChatUids();
$currentChatUid = $_GET['uid'] ?? ($chatUids[0] ?? '');
?>
<div class="admin-top">
    <div><h2>Chat hỗ trợ UID</h2><div class="sub">Admin/seller chat riêng với từng UID</div></div>
</div>
<div class="chat-wrap">
    <div class="chat-list">
        <div class="chat-title">UID đang chat</div>
        <?php if (count($chatUids)): ?>
            <?php foreach ($chatUids as $u): 
                $latest = getLatestChatForUid($u);
            ?>
            <div class="chat-user <?= $u === $currentChatUid ? 'active' : '' ?>" onclick="selectChat('<?= $u ?>')">
                <b><?= esc($u) ?></b>
                <div class="sub"><?= esc($latest['created_at'] ?? '') ?></div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <div class="empty">Chưa có chat</div>
        <?php endif; ?>
    </div>
    <div class="chat-box" id="chatBox">
        <?php if ($currentChatUid): ?>
        <div class="chat-title"><?= esc($currentChatUid) ?></div>
        <div class="chat-messages" id="adminChatMessages">
            <?php 
            $msgs = getChatsByUid($currentChatUid);
            foreach ($msgs as $c):
                $mine = $c['from_uid'] === $admin['username'];
            ?>
            <div class="bubble <?= $mine ? 'me' : '' ?>">
                <div class="from"><?= esc($c['from_role']) ?> • <?= esc($c['created_at']) ?></div>
                <?= nl2br_esc($c['text'] ?? '') ?>
                <?php if ($c['file_data']): ?>
                    <?php if (strpos($c['file_type'] ?? '', 'image/') === 0): ?>
                    <a class="attach" href="<?= esc($c['file_data']) ?>" target="_blank"><img src="<?= esc($c['file_data']) ?>"></a>
                    <?php elseif (strpos($c['file_type'] ?? '', 'video/') === 0): ?>
                    <a class="attach" href="<?= esc($c['file_data']) ?>" target="_blank"><video src="<?= esc($c['file_data']) ?>" controls></video></a>
                    <?php else: ?>
                    <a class="attach attach-file" href="<?= esc($c['file_data']) ?>" download="<?= esc($c['file_name']) ?>">Tải file: <?= esc($c['file_name']) ?></a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="composer">
            <button class="upload-arrow" onclick="$('#adminChatFile').click()">↑</button>
            <input id="adminChatFile" class="hidden-file" type="file" accept="image/*,video/*,.zip,.rar,.txt,.pdf">
            <input class="input" id="adminChatText" placeholder="Trả lời UID...">
            <button class="btn primary" onclick="sendAdminChat()">Gửi</button>
        </div>
        <?php else: ?>
        <div class="empty">Chọn UID để chat</div>
        <?php endif; ?>
    </div>
</div>
<script>
let pendingAdminChatFile = null;

function selectChat(uid) {
    window.location.href = '?page=chat&uid=' + encodeURIComponent(uid);
}

document.getElementById('adminChatFile').onchange = function(e) {
    pendingAdminChatFile = e.target.files[0] || null;
    if (pendingAdminChatFile) toast('Đã chọn: ' + pendingAdminChatFile.name);
};

async function sendAdminChat() {
    const text = document.getElementById('adminChatText').value.trim();
    if (!text && !pendingAdminChatFile) return;
    
    let fileData = null;
    if (pendingAdminChatFile) {
        fileData = {
            name: pendingAdminChatFile.name,
            type: pendingAdminChatFile.type || 'application/octet-stream',
            data: await fileToDataURL(pendingAdminChatFile)
        };
    }
    
    fetch('/api/chat.php?action=send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            uid: '<?= $currentChatUid ?>',
            text: text,
            file_name: fileData?.name || '',
            file_type: fileData?.type || '',
            file_data: fileData?.data || ''
        })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            pendingAdminChatFile = null;
            selectChat('<?= $currentChatUid ?>');
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

// Auto scroll
setTimeout(() => {
    const el = document.getElementById('adminChatMessages');
    if (el) el.scrollTop = el.scrollHeight;
}, 100);
</script>