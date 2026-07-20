<div class="admin-top">
    <div><h2>Đơn hàng</h2><div class="sub">Check lịch sử mua theo UID</div></div>
    <input class="input" style="width:280px" id="orderSearch" placeholder="Nhập UID để lọc" oninput="filterOrders()">
</div>
<div class="panel table-wrap" id="ordersBox">
    <?php include __DIR__ . '/../page_parts/orders_table.php'; ?>
</div>
<script>
function filterOrders() {
    const q = document.getElementById('orderSearch').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#ordersBox table tbody tr');
    rows.forEach(row => {
        const uid = row.querySelector('td:first-child')?.textContent?.toLowerCase() || '';
        row.style.display = !q || uid.includes(q) ? '' : 'none';
    });
}
</script>
