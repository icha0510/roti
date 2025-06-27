<?php
session_start();
require_once '../config/database.php';

$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == 1;

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$order_id) {
    echo '<div style="color:red;">Order tidak ditemukan.</div>';
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Ambil data order dengan informasi user
date_default_timezone_set('Asia/Jakarta');
$stmt = $db->prepare("SELECT o.*, u.name as user_name, u.email as user_email 
                      FROM orders o 
                      LEFT JOIN users u ON o.user_id = u.id 
                      WHERE o.id = :id");
$stmt->bindParam(':id', $order_id);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) {
    echo '<div style="color:red;">Order tidak ditemukan.</div>';
    exit;
}

// Proses update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    $desc = $_POST['description'] ?? '';
    $stmt = $db->prepare("INSERT INTO order_tracking (order_id, status, description, created_at) VALUES (:order_id, :status, :description, NOW())");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':description', $desc);
    $stmt->execute();
    header("Location: order_tracking.php?order_id=$order_id");
    exit;
}

// Ambil riwayat tracking
$stmt = $db->prepare("SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY created_at DESC");
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();
$trackings = $stmt->fetchAll(PDO::FETCH_ASSOC);

function status_badge($status) {
    $badge = 'badge-pending';
    if ($status == 'success') $badge = 'badge-success';
    if ($status == 'cancel') $badge = 'badge-cancel';
    if ($status == 'process') $badge = 'badge-process';
    return '<span class="badge '.$badge.'">'.ucfirst($status).'</span>';
}

if (!$isAjax) {
    echo '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><title>Tracking Order #'.htmlspecialchars($order['order_number']).'</title><link rel="stylesheet" href="../css/style.css"><style>body { font-family: Arial, sans-serif; background: #f8f9fa; } .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 30px; } h2 { margin-bottom: 10px; } .badge { padding: 4px 10px; border-radius: 4px; font-size: 13px; } .badge-pending { background: #ffc107; color: #fff; } .badge-success { background: #28a745; color: #fff; } .badge-cancel { background: #dc3545; color: #fff; } .badge-process { background: #17a2b8; color: #fff; } ul.tracking-list { list-style: none; padding: 0; } ul.tracking-list li { margin-bottom: 15px; background: #f1f1f1; border-radius: 5px; padding: 10px 15px; } .form-group { margin-bottom: 15px; } label { font-weight: 600; } textarea { width: 100%; min-height: 50px; } button, select { padding: 6px 12px; } .back-link { display: inline-block; margin-top: 20px; } .user-info { background: #f8f9fa; padding: 10px; border-radius: 5px; margin: 10px 0; }</style></head><body>';
    echo '<div class="container">';
}
?>
<h2>Tracking Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>

<div class="user-info">
    <h4>Informasi Customer:</h4>
    <p><b>Nama:</b> <?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p><b>Email:</b> <?php echo htmlspecialchars($order['customer_email']); ?></p>
    <p><b>Telepon:</b> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
    <p><b>Alamat:</b> <?php echo htmlspecialchars($order['customer_address']); ?></p>
    
    <?php if ($order['user_name']): ?>
        <h4>Informasi User Account:</h4>
        <p><b>User:</b> <?php echo htmlspecialchars($order['user_name']); ?></p>
        <p><b>Email User:</b> <?php echo htmlspecialchars($order['user_email']); ?></p>
    <?php else: ?>
        <p><em>Order dari guest user</em></p>
    <?php endif; ?>
</div>

<p><b>Produk:</b> <?php echo htmlspecialchars($order['product_name']); ?></p>
<p><b>Jumlah:</b> <?php echo htmlspecialchars($order['product_quantity']); ?></p>
<p><b>Total:</b> Rp<?php echo number_format($order['total_amount'], 2); ?></p>
<p><b>Tanggal Order:</b> <?php echo htmlspecialchars($order['order_date']); ?></p>
<p><b>Waktu Kontak:</b> <?php echo htmlspecialchars($order['contact_time']); ?></p>
<?php if ($order['notes']): ?>
    <p><b>Catatan:</b> <?php echo htmlspecialchars($order['notes']); ?></p>
<?php endif; ?>

<hr>
<h3>Riwayat Tracking</h3>
<ul class="tracking-list">
    <?php if (count($trackings) == 0): ?>
        <li><em>Belum ada riwayat tracking.</em></li>
    <?php else: foreach ($trackings as $t): ?>
        <li>
            <?php echo status_badge($t['status']); ?> - <?php echo htmlspecialchars($t['description']); ?>
            <br><small><?php echo $t['created_at']; ?></small>
        </li>
    <?php endforeach; endif; ?>
</ul>
<hr>
<h3>Update Status</h3>
<form method="post">
    <div class="form-group">
        <label>Status</label>
        <select name="status" required>
            <option value="">-- Pilih Status --</option>
            <option value="pending">Pending</option>
            <option value="process">Process</option>
            <option value="success">Success</option>
            <option value="cancel">Cancel</option>
        </select>
    </div>
    <div class="form-group">
        <label>Keterangan</label>
        <textarea name="description" placeholder="Keterangan"></textarea>
    </div>
    <button type="submit">Update Status</button>
</form>
<a href="orders.php" class="back-link">&larr; Kembali ke Daftar Order</a>
<?php
if (!$isAjax) {
    echo '</div></body></html>';
}