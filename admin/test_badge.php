<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Test Badge CSS</title>
    <style>
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
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
            color: #fff;
            margin-bottom: 5px;
            min-width: 80px;
            text-align: center;
        }
        .badge-pending {
            background: linear-gradient(45deg, #ffc107, #ff9800);
        }
        .badge-processing {
            background: linear-gradient(45deg, #17a2b8, #3498db);
        }
        .badge-shipped {
            background: linear-gradient(45deg, #6f42c1, #8e44ad);
        }
        .badge-delivered {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .badge-cancelled {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
        }
        .badge-success {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .badge-cancel {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
        }
        .badge-process {
            background: linear-gradient(45deg, #17a2b8, #3498db);
        }
        .user-info {
            font-size: 10px;
            color: #888;
            margin-top: 3px;
            font-style: italic;
        }
        .test-row {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧪 Test Badge CSS</h2>
        
        <div class="test-row">
            <h3>Status Baru:</h3>
            <div>
                <span class="badge badge-pending">Pending</span>
                <div class="user-info">Updated: 25/12/2024 10:30</div>
            </div>
            <div>
                <span class="badge badge-processing">Processing</span>
                <div class="user-info">Updated: 25/12/2024 11:15</div>
            </div>
            <div>
                <span class="badge badge-shipped">Shipped</span>
                <div class="user-info">Updated: 25/12/2024 14:20</div>
            </div>
            <div>
                <span class="badge badge-delivered">Delivered</span>
                <div class="user-info">Updated: 25/12/2024 16:45</div>
            </div>
            <div>
                <span class="badge badge-cancelled">Cancelled</span>
                <div class="user-info">Updated: 25/12/2024 09:10</div>
            </div>
        </div>
        
        <div class="test-row">
            <h3>Status Lama (Fallback):</h3>
            <div>
                <span class="badge badge-process">Process</span>
                <div class="user-info">Updated: 25/12/2024 10:30</div>
            </div>
            <div>
                <span class="badge badge-success">Success</span>
                <div class="user-info">Updated: 25/12/2024 11:15</div>
            </div>
            <div>
                <span class="badge badge-cancel">Cancel</span>
                <div class="user-info">Updated: 25/12/2024 14:20</div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="orders.php" style="background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 10px; font-weight: 600;">Kembali ke Orders Dashboard</a>
        </div>
    </div>
</body>
</html> 