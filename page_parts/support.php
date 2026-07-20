<?php
// page_parts/support.php
$chats = getChatsByUid($user['uid']);
?>
<div class="section-head">
    <div>
        <h2>Chat AI / Hỗ trợ UID</h2>
        <div class="sub">Gửi text, file, ảnh hoặc video. Admin/seller sẽ trả lời trong UID này.</div>
    </div>
</div>

<div class="chat-box" style="height:calc(100vh - 190px)">
    <div class="chat-title">UID: <?= esc($user['uid']) ?></div>
    <div class="chat-messages" id="chatMessages">
        <?php if (count($chats)): ?>
            <?php foreach ($chats as $c): 
                $mine = $c['from_uid'] === $user['uid'];
            ?>
            <div class="bubble <?= $mine ? 'me' : '' ?>">
                <div class="from"><?= esc($c['from_role']) ?> • <?= esc($c['created_at']) ?></div>
                <?= nl2br_esc($c['text'] ?? '') ?>
                <?php if ($c['file_data']): ?>
                    <?php if (strpos($c['file_type'] ?? '', 'image/') === 0): ?>
                        <a class="attach" href="<?= esc($c['file_data']) ?>" target="_blank">
                            <img src="<?= esc($c['file_data']) ?>">
                        </a>
                    <?php elseif (strpos($c['file_type'] ?? '', 'video/') === 0): ?>
                        <a class="attach" href="<?= esc($c['file_data']) ?>" target="_blank">
                            <video src="<?= esc($c['file_data']) ?>" controls></video>
                        </a>
                    <?php else: ?>
                        <a class="attach attach-file" href="<?= esc($c['file_data']) ?>" download="<?= esc($c['file_name']) ?>">
                            Tải file: <?= esc($c['file_name']) ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty">Chưa có tin nhắn</div>
        <?php endif; ?>
    </div>
    
    <div class="composer">
        <button class="upload-arrow" onclick="$('#chatFile').click()">↑</button>
        <input id="chatFile" class="hidden-file" type="file" accept="image/*,video/*,.zip,.rar,.txt,.pdf">
        <input class="input" id="chatText" placeholder="Nhập nội dung hỗ trợ...">
        <button class="btn primary" onclick="sendChat()">Gửi</button>
    </div>
</div>

<script>
let pendingChatFile = null;

document.getElementById('chatFile').onchange = function(e) {
    pendingChatFile = e.target.files[0] || null;
    if (pendingChatFile) toast('Đã chọn: ' + pendingChatFile.name);
};

async function sendChat() {
    const text = document.getElementById('chatText').value.trim();
    if (!text && !pendingChatFile) return;
    
    let fileData = null;
    if (pendingChatFile) {
        fileData = {
            name: pendingChatFile.name,
            type: pendingChatFile.type || 'application/octet-stream',
            data: await fileToDataURL(pendingChatFile)
        };
    }
    
    fetch('/api/chat.php?action=send', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            uid: '<?= $user['uid'] ?>',
            text: text,
            file_name: fileData?.name || '',
            file_type: fileData?.type || '',
            file_data: fileData?.data || ''
        })
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            pendingChatFile = null;
            loadPage('support');
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

// Auto scroll to bottom
setTimeout(() => {
    const el = document.getElementById('chatMessages');
    if (el) el.scrollTop = el.scrollHeight;
}, 100);
</script>