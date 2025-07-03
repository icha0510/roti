<?php
require_once 'includes/functions.php';

$action = $_GET['action'] ?? 'list';
$message = '';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteNewsletterSubscriber($id)) {
        $message = 'Subscriber deleted successfully!';
    } else {
        $message = 'Error deleting subscriber.';
    }
    $action = 'list';
}

$subscribers = getAllNewsletterSubscribers();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter Subscribers - Roti'O Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 10px 20px;
        }
        .sidebar .nav-link:hover {
            background: #495057;
        }
        .sidebar .nav-link.active {
            background: #007bff;
        }
        .main-content {
            margin-left: 250px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed top-0 start-0 d-flex flex-column flex-shrink-0 p-3 text-white" style="width: 250px;">
        <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <img src="../images/logo-rotio.png" alt="Roti'O" class="me-2" style="width: 30px; height: 30px;">
            <span class="fs-4">Roti'O Admin</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link text-white">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="products.php" class="nav-link text-white">
                    <i class="fas fa-box me-2"></i>
                    Products
                </a>
            </li>
            <li>
                <a href="categories.php" class="nav-link text-white">
                    <i class="fas fa-tags me-2"></i>
                    Categories
                </a>
            </li>
            <li>
                <a href="testimonials.php" class="nav-link text-white">
                    <i class="fas fa-comments me-2"></i>
                    Testimonials
                </a>
            </li>
            <li>
                <a href="posts.php" class="nav-link text-white">
                    <i class="fas fa-newspaper me-2"></i>
                    Blog Posts
                </a>
            </li>
            <li>
                <a href="newsletter.php" class="nav-link active">
                    <i class="fas fa-envelope me-2"></i>
                    Newsletter
                </a>
            </li>
            <li>
                <a href="orders.php" class="nav-link text-white">
                    <i class="fas fa-clipboard-list me-2"></i>
                    Orders
                </a>
            </li>
            <li>
                <a href="register.php" class="nav-link text-white">
                    <i class="fas fa-user-plus me-2"></i>
                    Add Admin
                </a>
            </li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle me-2"></i>
                <strong><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                <li><a class="dropdown-item" href="../index.php" target="_blank">View Website</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Newsletter Subscribers</h1>
                <div>
                    <span class="badge bg-primary">Total: <?php echo count($subscribers); ?></span>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Subscribers List -->
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0">All Newsletter Subscribers</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Email</th>
                                    <th>Status</th>
                                    <th>Subscribed Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscribers as $subscriber): ?>
                                <tr>
                                    <td><?php echo $subscriber['id']; ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($subscriber['status'] === 'active') ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst($subscriber['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($subscriber['subscribed_at'])); ?></td>
                                    <td>
                                        <a href="?delete=<?php echo $subscriber['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 