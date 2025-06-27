<?php
require_once 'includes/functions.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        // Add new banner
        $image_result = uploadImageToDatabase($_FILES['image']);
        
        if (isset($image_result['error'])) {
            $message = showAlert($image_result['error'], 'danger');
        } else {
            $data = [
                'title' => validateInput($_POST['title']),
                'subtitle' => validateInput($_POST['subtitle']),
                'image_data' => $image_result['data'],
                'image_mime' => $image_result['mime_type'],
                'link' => validateInput($_POST['link']),
                'badge_text' => validateInput($_POST['badge_text']),
                'badge_type' => validateInput($_POST['badge_type']),
                'sort_order' => (int)$_POST['sort_order'],
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];
            
            if (addBanner($data)) {
                $message = showAlert('Banner added successfully!', 'success');
            } else {
                $message = showAlert('Error adding banner!', 'danger');
            }
        }
    }
}

// Get banners
$banners = getAllBanners();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Banners - Admin Dashboard</title>
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
        .banner-image {
            max-width: 200px;
            max-height: 100px;
            object-fit: cover;
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
            <i class="fas fa-bread-slice me-2"></i>
            <span class="fs-4">Bready Admin</span>
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
                <a href="banners.php" class="nav-link active">
                    <i class="fas fa-images me-2"></i>
                    Banners
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
                <a href="awards.php" class="nav-link text-white">
                    <i class="fas fa-trophy me-2"></i>
                    Awards
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Manage Banners</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                    <i class="fas fa-plus me-2"></i>Add New Banner
                </button>
            </div>

            <?php echo $message; ?>

            <!-- Banners Table -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Image</th>
                                    <th>Title</th>
                                    <th>Subtitle</th>
                                    <th>Badge</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($banners as $banner): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($banner['image_data'])): ?>
                                            <?php echo displayImage($banner['image_data'], $banner['image_mime'], 'banner-image img-thumbnail', $banner['title']); ?>
                                        <?php else: ?>
                                            <img src="<?php echo $banner['image']; ?>" class="banner-image img-thumbnail" alt="<?php echo $banner['title']; ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $banner['title']; ?></td>
                                    <td><?php echo $banner['subtitle']; ?></td>
                                    <td>
                                        <?php if ($banner['badge_text']): ?>
                                            <span class="badge bg-<?php echo $banner['badge_type'] == 'sale' ? 'danger' : ($banner['badge_type'] == 'new' ? 'success' : 'primary'); ?>">
                                                <?php echo $banner['badge_text']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $banner['sort_order']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $banner['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $banner['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?edit=<?php echo $banner['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?delete=<?php echo $banner['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

    <!-- Add Banner Modal -->
    <div class="modal fade" id="addBannerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Banner</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Subtitle</label>
                                    <input type="text" class="form-control" name="subtitle">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Banner Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <small class="text-muted">Recommended size: 1920x600px. Max size: 5MB</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Link URL</label>
                                    <input type="url" class="form-control" name="link" placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" name="sort_order" value="0" min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Badge Text</label>
                                    <input type="text" class="form-control" name="badge_text" placeholder="50% OFF">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Badge Type</label>
                                    <select class="form-select" name="badge_type">
                                        <option value="sale">Sale</option>
                                        <option value="new">New</option>
                                        <option value="featured">Featured</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Banner</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 