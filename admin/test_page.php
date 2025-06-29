<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Test AJAX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test AJAX Update Status</h1>
        
        <h3>Test 1: Koneksi Database</h3>
        <button class="btn" onclick="testConnection()">Test Koneksi Database</button>
        
        <h3>Test 2: Update Status (Test Data)</h3>
        <button class="btn" onclick="testUpdateStatus()">Test Update Status</button>
        
        <h3>Test 3: Update Status (Real Data)</h3>
        <select id="orderSelect">
            <option value="">-- Pilih Order --</option>
        </select>
        <select id="statusSelect">
            <option value="">-- Pilih Status --</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="shipped">Shipped</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <button class="btn" onclick="testRealUpdate()">Test Update Real</button>
        
        <div id="result" class="result" style="display: none;"></div>
        
        <h3>Links</h3>
        <p><a href="orders.php">📋 Orders (Original)</a></p>
        <p><a href="orders_new.php">📋 Orders (New Version)</a></p>
        <p><a href="fix_order_tracking.php">🔧 Fix Order Tracking</a></p>
    </div>
    
    <script>
    // Load orders untuk dropdown
    window.onload = function() {
        loadOrders();
    };
    
    function loadOrders() {
        fetch('test_ajax.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Database connected, total orders:', data.data.total_orders);
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
        });
    }
    
    function testConnection() {
        showResult('Testing database connection...', '');
        
        fetch('test_ajax.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult('✅ Database connection successful!', data, 'success');
            } else {
                showResult('❌ Database connection failed!', data, 'error');
            }
        })
        .catch(error => {
            showResult('❌ AJAX request failed!', error, 'error');
        });
    }
    
    function testUpdateStatus() {
        showResult('Testing update status with test data...', '');
        
        var formData = new FormData();
        formData.append('order_id', '1');
        formData.append('status', 'processing');
        formData.append('description', 'Test update from test page');
        
        fetch('debug_update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult('✅ Update status successful!', data, 'success');
            } else {
                showResult('❌ Update status failed!', data, 'error');
            }
        })
        .catch(error => {
            showResult('❌ AJAX request failed!', error, 'error');
        });
    }
    
    function testRealUpdate() {
        var orderId = document.getElementById('orderSelect').value;
        var status = document.getElementById('statusSelect').value;
        
        if (!orderId || !status) {
            showResult('❌ Please select order and status!', '', 'error');
            return;
        }
        
        showResult('Testing real update...', '');
        
        var formData = new FormData();
        formData.append('order_id', orderId);
        formData.append('status', status);
        formData.append('description', 'Test update from test page - ' + new Date().toLocaleString());
        
        fetch('debug_update.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showResult('✅ Real update successful!', data, 'success');
            } else {
                showResult('❌ Real update failed!', data, 'error');
            }
        })
        .catch(error => {
            showResult('❌ AJAX request failed!', error, 'error');
        });
    }
    
    function showResult(message, data, type) {
        var resultDiv = document.getElementById('result');
        resultDiv.style.display = 'block';
        resultDiv.className = 'result ' + (type || '');
        
        var content = '<h4>' + message + '</h4>';
        if (data) {
            content += '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        }
        
        resultDiv.innerHTML = content;
    }
    </script>
</body>
</html> 