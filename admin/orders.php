<?php
session_start();
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Ambil semua order dan status terakhir dengan informasi user
$sql = "SELECT o.*, 
        u.name as user_name, u.email as user_email,
        (SELECT status FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_status,
        (SELECT created_at FROM order_tracking WHERE order_id = o.id ORDER BY created_at DESC LIMIT 1) as last_update
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin - Order Tracking</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .flex-container { display: flex; gap: 30px; }
        .order-list { width: 45%; }
        .order-detail { width: 55%; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 20px; min-height: 400px;}
        table { width: 100%; border-collapse: collapse; margin-top: 10px;}
        th, td { border: 1px solid #ccc; padding: 8px; font-size: 12px; }
        th { background: #eee; }
        .badge { padding: 4px 10px; border-radius: 4px; }
        .badge-pending { background: #ffc107; color: #fff; }
        .badge-success { background: #28a745; color: #fff; }
        .badge-cancel { background: #dc3545; color: #fff; }
        .badge-process { background: #17a2b8; color: #fff; }
        tr.selected { background: #e3f2fd; }
        .user-info { font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <h2>Order Tracking - Admin</h2>
    <div class="flex-container">
        <!-- Kiri: List Order -->
        <div class="order-list">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Customer</th>
                        <th>User</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="order-row" data-order-id="<?php echo $order['id']; ?>">
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($order['customer_name']); ?>
                            <div class="user-info"><?php echo htmlspecialchars($order['customer_email']); ?></div>
                        </td>
                        <td>
                            <?php if ($order['user_name']): ?>
                                <?php echo htmlspecialchars($order['user_name']); ?>
                                <div class="user-info"><?php echo htmlspecialchars($order['user_email']); ?></div>
                            <?php else: ?>
                                <em>Guest</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                                $status = $order['last_status'] ?? 'pending';
                                $badge = 'badge-pending';
                                if ($status == 'success') $badge = 'badge-success';
                                if ($status == 'cancel') $badge = 'badge-cancel';
                                if ($status == 'process') $badge = 'badge-process';
                            ?>
                            <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                        </td>
                        <td>
                            <button class="show-detail-btn" data-order-id="<?php echo $order['id']; ?>">Lihat</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Kanan: Detail Order -->
        <div class="order-detail" id="order-detail">
            <em>Pilih salah satu order untuk melihat detail dan tracking...</em>
        </div>
    </div>
    <script>
    // AJAX load detail order
    document.querySelectorAll('.show-detail-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var orderId = this.getAttribute('data-order-id');
            // Highlight row
            document.querySelectorAll('.order-row').forEach(function(row) {
                row.classList.remove('selected');
            });
            this.closest('tr').classList.add('selected');
            // Load detail
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'order_tracking.php?order_id=' + orderId + '&ajax=1');
            xhr.onload = function() {
                document.getElementById('order-detail').innerHTML = xhr.responseText;
            };
            xhr.send();
        });
    });
    </script>
</body>
</html>