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
    <title>Admin - Order Tracking (New Version)</title>
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
            gap: 30px; 
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
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        .close:hover {
            color: #dc3545;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .modal-footer {
            text-align: right;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #e9ecef;
        }
        .btn-cancel {
            background: #6c757d;
            margin-right: 10px;
        }
        .btn-cancel:hover {
            background: #5a6268;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 15px;
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
        .loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #667eea;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .status-cell {
            position: relative;
        }
        .status-cell.updating {
            opacity: 0.7;
        }
        .status-cell.updating::after {
            content: "Updating...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(102, 126, 234, 0.9);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📋 Order Tracking - Admin (New Version)</h2>
        
        <div class="header-actions">
            <a href="orders.php" class="btn btn-primary">← Kembali ke Orders</a>
            <a href="fix_order_tracking.php" class="btn btn-success">🔧 Update Status (Fixed)</a>
            <a href="test_page.php" class="btn btn-primary">🧪 Test AJAX</a>
        </div>
        
        <div class="order-count">
            <h3>Total Orders: <?php echo count($orders); ?></h3>
            <p>Versi baru dengan AJAX yang lebih reliable dan multiple endpoint fallback</p>
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
                            <td class="status-cell" id="status-<?php echo $order['id']; ?>">
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
                    <h3>📋 Detail Order</h3>
                    <p>Pilih salah satu order untuk melihat detail dan tracking...</p>
                    <p><small>Atau gunakan tombol "Update Status (Fixed)" untuk mengupdate status order</small></p>
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
                        <div id="orderNumber" style="padding: 10px; background: #f8f9fa; border-radius: 5px; font-weight: bold;"></div>
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
                document.getElementById('order-detail').innerHTML = '<div style="text-align: center; padding: 50px; color: #dc3545;"><h3>❌ Error</h3><p>Gagal memuat detail order.</p></div>';
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
    
    // Update status badge
    function updateStatusBadge(orderId, newStatus) {
        var statusCell = document.getElementById('status-' + orderId);
        if (statusCell) {
            var badge = statusCell.querySelector('.badge');
            if (badge) {
                // Remove old badge classes
                badge.className = 'badge';
                
                // Add new badge class
                var badgeClass = 'badge-pending';
                if (newStatus == 'processing') badgeClass = 'badge-processing';
                if (newStatus == 'shipped') badgeClass = 'badge-shipped';
                if (newStatus == 'delivered') badgeClass = 'badge-delivered';
                if (newStatus == 'cancelled') badgeClass = 'badge-cancelled';
                
                badge.classList.add(badgeClass);
                badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                
                // Update timestamp
                var userInfo = statusCell.querySelector('.user-info');
                if (userInfo) {
                    userInfo.textContent = 'Updated: ' + new Date().toLocaleString('id-ID');
                } else {
                    var newUserInfo = document.createElement('div');
                    newUserInfo.className = 'user-info';
                    newUserInfo.textContent = 'Updated: ' + new Date().toLocaleString('id-ID');
                    statusCell.appendChild(newUserInfo);
                }
            }
        }
    }
    
    // Handle form submission with improved error handling
    document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        var submitBtn = this.querySelector('button[type="submit"]');
        var originalText = submitBtn.textContent;
        var orderId = document.getElementById('orderId').value;
        
        // Show loading
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        submitBtn.classList.add('loading');
        
        // Show updating status in table
        var statusCell = document.getElementById('status-' + orderId);
        if (statusCell) {
            statusCell.classList.add('updating');
        }
        
        // Try multiple endpoints
        var endpoints = ['update_status_ajax.php', 'debug_update.php'];
        var currentEndpoint = 0;
        
        function tryUpdate() {
            if (currentEndpoint >= endpoints.length) {
                // All endpoints failed
                document.getElementById('alertMessage').innerHTML = '<div class="alert alert-error">Semua endpoint gagal. Silakan gunakan tombol "Update Status (Fixed)".</div>';
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                if (statusCell) statusCell.classList.remove('updating');
                return;
            }
            
            var endpoint = endpoints[currentEndpoint];
            console.log('Trying endpoint:', endpoint);
            
            fetch(endpoint, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response from', endpoint, ':', response);
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                console.log('Data from', endpoint, ':', data);
                if (data.success) {
                    document.getElementById('alertMessage').innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                    
                    // Update status badge immediately
                    var newStatus = document.getElementById('statusSelect').value;
                    updateStatusBadge(orderId, newStatus);
                    
                    // Close modal after 2 seconds
                    setTimeout(function() {
                        closeModal();
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Unknown error');
                }
            })
            .catch(error => {
                console.log('Error with', endpoint, ':', error);
                currentEndpoint++;
                if (currentEndpoint < endpoints.length) {
                    // Try next endpoint
                    setTimeout(tryUpdate, 500);
                } else {
                    // All endpoints failed
                    document.getElementById('alertMessage').innerHTML = '<div class="alert alert-error">Gagal update status: ' + error.message + '</div>';
                }
            })
            .finally(() => {
                // Reset button and status cell
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('loading');
                if (statusCell) statusCell.classList.remove('updating');
            });
        }
        
        tryUpdate();
    });
    
    // Auto refresh setiap 60 detik
    setInterval(function() {
        console.log('Auto refreshing page...');
        location.reload();
    }, 60000);
    </script>
</body>
</html> 