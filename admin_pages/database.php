<div class="admin-top">
    <div><h2>Database</h2><div class="sub">Quản lý dữ liệu</div></div>
</div>
<div class="panel">
    <button class="btn primary" onclick="exportDB()">Export JSON</button>
    <button class="btn" onclick="$('#importFile').click()">Import JSON</button>
    <button class="btn red" onclick="if(confirm('Reset toàn bộ demo?')){resetDB()}">Reset demo</button>
    <input id="importFile" class="hidden-file" type="file" accept="application/json">
    <div class="notice" style="margin-top:14px">Muốn nhiều khách online thật cần nối Firebase/Supabase/PHP MySQL. Bản này là source frontend hoàn chỉnh dùng MySQL để test.</div>
</div>
<script>
function exportDB() {
    window.location.href = '/api/admin.php?action=download_db';
}

document.getElementById('importFile').onchange = function(e) {
    const f = e.target.files[0];
    if (!f) return;
    const r = new FileReader();
    r.onload = () => {
        try {
            const data = JSON.parse(r.result);
            fetch('/api/admin.php?action=import_db', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    toast('Import thành công');
                    setTimeout(() => location.reload(), 500);
                }
            });
        } catch (err) {
            toast('File JSON không hợp lệ');
        }
    };
    r.readAsText(f);
};

function resetDB() {
    fetch('/api/admin.php?action=reset_db')
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                toast('Đã reset database');
                location.reload();
            }
        });
}
</script>