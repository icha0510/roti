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
    <title>Admin - Order Management Dashboard</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: 700;
        }
        .header-actions {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: white;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            background: linear-gradient(45deg, #28a745, #20c997);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }
        .flex-container {
            display: flex;
            gap: 50px;
        }
        .order-list {
            width: 45%;
        }
        .order-detail {
            width: 55%;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 20px;
            min-height: 400px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }
        th, td {
            border: 1px solid #e9ecef;
            padding: 12px;
            font-size: 13px;
        }
        th {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            text-align: left;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        tr:hover {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            transform: scale(1.01);
        }
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .badge-pending {
            background: linear-gradient(45deg, #ffc107, #ff9800);
            color: #fff;
        }
        .badge-processing {
            background: linear-gradient(45deg, #17a2b8, #3498db);
            color: #fff;
        }
        .badge-shipped {
            background: linear-gradient(45deg, #6f42c1, #8e44ad);
            color: #fff;
        }
        .badge-delivered {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: #fff;
        }
        .badge-cancelled {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
            color: #fff;
        }
        tr.selected {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
            border-left: 4px solid #667eea;
        }
        .user-info {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        .action-buttons {
            display: flex;
            gap: 5px;
        }
        .btn-small {
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 6px;
        }
        .btn-view {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .btn-update {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
        }
        .order-count {
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            border-left: 4px solid #667eea;
        }
        .order-count h3 {
            margin: 0;
            color: #667eea;
            font-size: 1.2rem;
        }
        .order-count p {
            margin: 5px 0 0 0;
            color: #666;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            animation: modalSlideIn 0.3s ease-out;
            position: relative;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .modal-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 20px 25px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
        }
        
        .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
        }
        
        .close:hover {
            background: rgba(255,255,255,0.3);
            transform: rotate(90deg);
        }
        
        #modalBody {
            padding: 25px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #fff;
        }
        
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .btn-cancel {
            background: #6c757d;
            color: white;
        }
        
        .btn-cancel:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(45deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: linear-gradient(45deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }
        
        /* Responsive Modal */
        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
                max-width: none;
            }
            
            .modal-header {
                padding: 15px 20px;
            }
            
            #modalBody {
                padding: 20px;
            }
            
            .modal-footer {
                flex-direction: column;
            }
            
            .modal-footer .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Order Management Dashboard</h2>
        
        <div class="header-actions">
            <a href="fix_order_tracking.php" class="btn btn-success">🔧 Update Status (Fixed)</a>
            <a href="run_fix_sql.php" class="btn btn-primary">🛠️ Perbaiki Database</a>
        </div>
        
        <div class="order-count">
            <h3>📊 Total Orders: <?php echo count($orders); ?></h3>
            <p>Klik tombol "Update Status (Fixed)" untuk mengupdate status order dengan interface yang diperbaiki</p>
        </div>
        
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
                            <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
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
                                    $status = $order['last_status'];
                                    if (!$status) {
                                        $status = $order['status'] ?? 'pending';
                                    }
                                    
                                    // Mapping status yang benar sesuai enum database
                                    $badge = 'badge-pending';
                                    if ($status == 'processing') $badge = 'badge-processing';
                                    if ($status == 'shipped') $badge = 'badge-shipped';
                                    if ($status == 'delivered') $badge = 'badge-delivered';
                                    if ($status == 'cancelled') $badge = 'badge-cancelled';
                                    
                                    // Fallback untuk status lama
                                    if ($status == 'process') $badge = 'badge-processing';
                                    if ($status == 'success') $badge = 'badge-delivered';
                                    if ($status == 'cancel') $badge = 'badge-cancelled';
                                ?>
                                <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                                <?php if ($order['last_update']): ?>
                                    <div class="user-info">Updated: <?php echo date('d/m/Y H:i', strtotime($order['last_update'])); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-small btn-view show-detail-btn" data-order-id="<?php echo $order['id']; ?>">👁️ Lihat</button>
                                    <button class="btn btn-small btn-update" onclick="openUpdateModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['order_number']); ?>', '<?php echo $status; ?>')">🔄 Update</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Kanan: Detail Order -->
            <div class="order-detail" id="order-detail">
                <div style="text-align: center; padding: 50px; color: #666;">
                    <h3 style="font-size: 2rem; margin-bottom: 20px; background: linear-gradient(45deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">📋 Detail Order</h3>
                    <p style="font-size: 16px; margin-bottom: 15px;">Pilih salah satu order untuk melihat detail dan tracking...</p>
                    <p style="font-size: 14px; color: #888;"><small>Atau gunakan tombol "Update Status (Fixed)" untuk mengupdate status order</small></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Update Status -->
    <div id="updateModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">🔄 Update Status Order</h3>
                <span class="close">&times;</span>
            </div>
            <div id="modalBody">
                <div id="alertMessage"></div>
                <form id="updateStatusForm">
                    <input type="hidden" id="orderId" name="order_id">
                    <div class="form-group">
                        <label>Order Number:</label>
                        <div id="orderNumber" style="padding: 15px; background: rgba(248, 249, 250, 0.8); border-radius: 10px; font-weight: bold; border: 1px solid rgba(233, 236, 239, 0.5);"></div>
                    </div>
                    <div class="form-group">
                        <label>Status:</label>
                        <select name="status" id="statusSelect" required>
                            <option value="">-- Pilih Status --</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-cancel" onclick="closeModal()">Batal</button>
                        <button type="submit" class="btn btn-success">Update Status</button>
                    </div>
                </form>
            </div>
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
            xhr.onerror = function() {
                document.getElementById('order-detail').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;"><h3>❌ Error</h3><p>Gagal memuat detail order. Gunakan tombol "Update Status (Fixed)" untuk mengupdate status.</p></div>';
            };
            xhr.send();
        });
    });
    
    // Modal functions
    function openUpdateModal(orderId, orderNumber, currentStatus) {
        document.getElementById('orderId').value = orderId;
        document.getElementById('orderNumber').textContent = orderNumber;
        document.getElementById('statusSelect').value = currentStatus;
        document.getElementById('alertMessage').innerHTML = '';
        document.getElementById('updateModal').style.display = 'block';
        // Simpan orderId ke global untuk update badge
        window._currentUpdateOrderId = orderId;
    }
    
    function closeModal() {
        document.getElementById('updateModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('updateModal');
        if (event.target == modal) {
            closeModal();
        }
    }
    
    // Close modal when clicking X
    document.querySelector('.close').onclick = function() {
        closeModal();
    }
    
    // Handle form submission
    document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);
        var submitBtn = this.querySelector('button[type="submit"]');
        var originalText = submitBtn.textContent;
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        fetch('debug_update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('alertMessage').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                // Update badge status di tabel
                var orderId = window._currentUpdateOrderId;
                var row = document.querySelector('tr.order-row[data-order-id="' + orderId + '"]');
                if (row) {
                    var statusCell = row.querySelector('td:nth-child(4)');
                    var status = document.getElementById('statusSelect').value;
                    var badgeClass = 'badge-pending';
                    if (status == 'processing') badgeClass = 'badge-processing';
                    if (status == 'shipped') badgeClass = 'badge-shipped';
                    if (status == 'delivered') badgeClass = 'badge-delivered';
                    if (status == 'cancelled') badgeClass = 'badge-cancelled';
                    statusCell.innerHTML = '<span class="badge ' + badgeClass + '">' + status.charAt(0).toUpperCase() + status.slice(1) + '</span>';
                }
                setTimeout(function() {
                    closeModal();
                }, 800);
            } else {
                document.getElementById('alertMessage').innerHTML = '<div class="alert alert-error">' + data.message + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('alertMessage').innerHTML = '<div class="alert alert-error">Error: ' + error.message + '</div>';
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
            submitBtn.classList.remove('loading');
        });
    });
    
    // Auto refresh setiap 30 detik
    setInterval(function() {
        location.reload();
    }, 30000);
    </script>
</body>
</html> 