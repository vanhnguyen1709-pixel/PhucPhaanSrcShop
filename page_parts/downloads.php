<?php
// page_parts/downloads.php
$downloads = getActiveDownloads();
?>
<div class="section-head">
    <div>
        <h2>Tải xuống</h2>
        <div class="sub">File free, hướng dẫn và launcher do admin/seller upload</div>
    </div>
    <span class="badge green"><?= count($downloads) ?> file</span>
</div>

<div class="download-grid">
    <?php if (count($downloads)): ?>
        <?php foreach ($downloads as $d): ?>
        <div class="panel download-card">
            <div class="download-icon">⇩</div>
            <h3><?= esc($d['title']) ?></h3>
            <p class="sub"><?= nl2br_esc($d['description'] ?? '') ?></p>
            <div class="sub"><?= esc($d['file_name'] ?? 'file') ?> • <?= esc($d['created_at'] ?? '') ?></div>
            <?php if ($d['file_data']): ?>
                <a class="btn primary" download="<?= esc($d['file_name'] ?? 'download') ?>" href="<?= esc($d['file_data']) ?>">
                    Tải xuống
                </a>
            <?php else: ?>
                <button class="btn" disabled>Chưa có file</button>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="panel empty">Chưa có file tải xuống</div>
    <?php endif; ?>
</div>