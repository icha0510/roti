<?php
require_once 'includes/functions.php';

$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            // Add new product
            $image_result = uploadImageToDatabase($_FILES['image']);
            
            if (isset($image_result['error'])) {
                $message = showAlert($image_result['error'], 'danger');
            } else {
                $data = [
                    'name' => validateInput($_POST['name']),
                    'slug' => createSlug($_POST['name']),
                    'description' => validateInput($_POST['description']),
                    'price' => (float)$_POST['price'],
                    'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                    'category_id' => (int)$_POST['category_id'],
                    'image_data' => $image_result['data'],
                    'image_mime' => $image_result['mime_type'],
                    'stock' => (int)$_POST['stock'],
                    'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                    'is_new' => isset($_POST['is_new']) ? 1 : 0,
                    'is_sale' => isset($_POST['is_sale']) ? 1 : 0,
                    'rating' => (float)$_POST['rating']
                ];
                
                if (addProduct($data)) {
                    $message = showAlert('Product added successfully!', 'success');
                } else {
                    $message = showAlert('Error adding product!', 'danger');
                }
            }
        } elseif ($_POST['action'] == 'edit') {
            // Update product
            $id = (int)$_POST['id'];
            $data = [
                'name' => validateInput($_POST['name']),
                'slug' => createSlug($_POST['name']),
                'description' => validateInput($_POST['description']),
                'price' => (float)$_POST['price'],
                'sale_price' => !empty($_POST['sale_price']) ? (float)$_POST['sale_price'] : null,
                'category_id' => (int)$_POST['category_id'],
                'stock' => (int)$_POST['stock'],
                'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
                'is_new' => isset($_POST['is_new']) ? 1 : 0,
                'is_sale' => isset($_POST['is_sale']) ? 1 : 0,
                'rating' => (float)$_POST['rating']
            ];
            
            // Handle image upload if new image is selected
            if (!empty($_FILES['image']['name'])) {
                $image_result = uploadImageToDatabase($_FILES['image']);
                if (isset($image_result['error'])) {
                    $message = showAlert($image_result['error'], 'danger');
                } else {
                    $data['image_data'] = $image_result['data'];
                    $data['image_mime'] = $image_result['mime_type'];
                }
            }
            
            if (updateProduct($id, $data)) {
                $message = showAlert('Product updated successfully!', 'success');
            } else {
                $message = showAlert('Error updating product!', 'danger');
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if (deleteProduct($id)) {
        $message = showAlert('Product deleted successfully!', 'success');
    } else {
        $message = showAlert('Error deleting product!', 'danger');
    }
}

// Get data
$products = getAllProducts();
$categories = getAllCategories();
$edit_product = null;

// Get product for editing
if (isset($_GET['edit'])) {
    $edit_product = getProductById((int)$_GET['edit']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../images/logo-rotio.png" rel="icon">
    <title>Manage Products - Admin Dashboard</title>
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
            background: #F2E205;
        }
        .main-content {
            margin-left: 250px;
        }
        .product-image {
            max-width: 100px;
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
                <a href="products.php" class="nav-link active">
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
                <a href="newsletter.php" class="nav-link text-white">
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
                <h1 class="h3 mb-0">Manage Products</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </button>
            </div>

            <?php echo $message; ?>

            <!-- Products Table -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($product['image_data'])): ?>
                                            <?php echo displayImage($product['image_data'], $product['image_mime'], 'product-image img-thumbnail', $product['name']); ?>
                                        <?php else: ?>
                                            <img src="<?php echo $product['image']; ?>" class="product-image img-thumbnail" alt="<?php echo $product['name']; ?>">
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name']; ?></td>
                                    <td>
                                        <?php if ($product['is_sale'] && $product['sale_price']): ?>
                                            <del>Rp<?php echo number_format($product['price'], 3); ?></del>
                                            <span class="text-danger">Rp<?php echo number_format($product['sale_price'], 3); ?></span>
                                        <?php else: ?>
                                            Rp<?php echo number_format($product['price'], 3); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $product['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $product['stock']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['is_featured']): ?>
                                            <span class="badge bg-primary">Featured</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_new']): ?>
                                            <span class="badge bg-success">New</span>
                                        <?php endif; ?>
                                        <?php if ($product['is_sale']): ?>
                                            <span class="badge bg-danger">Sale</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editProduct(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
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

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" class="form-control" name="price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sale Price (Optional)</label>
                                    <input type="number" class="form-control" name="sale_price" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" class="form-control" name="stock" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                            <small class="text-muted">Max size: 5MB. Supported: JPG, PNG, GIF, WebP</small>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <input type="number" class="form-control" name="rating" step="0.1" min="0" max="5" value="0">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label class="form-label">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_featured" value="1">
                                        <label class="form-check-label">Featured Product</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_new" value="1">
                                        <label class="form-check-label">New Product</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_sale" value="1">
                                        <label class="form-check-label">On Sale</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" class="form-control" name="name" id="edit_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category_id" id="edit_category_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" class="form-control" name="price" id="edit_price" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Sale Price (Optional)</label>
                                    <input type="number" class="form-control" name="sale_price" id="edit_sale_price" step="0.01">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Stock</label>
                                    <input type="number" class="form-control" name="stock" id="edit_stock" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Product Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image. Max size: 5MB</small>
                            <div id="current_image" class="mt-2"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Rating</label>
                                    <input type="number" class="form-control" name="rating" id="edit_rating" step="0.1" min="0" max="5">
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="mb-3">
                                    <label class="form-label">Options</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_featured" id="edit_is_featured" value="1">
                                        <label class="form-check-label">Featured Product</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_new" id="edit_is_new" value="1">
                                        <label class="form-check-label">New Product</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_sale" id="edit_is_sale" value="1">
                                        <label class="form-check-label">On Sale</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(id) {
            // Show loading state
            const button = event.target.closest('button');
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            button.disabled = true;
            
            // Fetch product data and populate modal
            fetch('get_product.php?id=' + id)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error('Network response was not ok: ' + response.status + ' - ' + text);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_description').value = data.description;
                    document.getElementById('edit_price').value = data.price;
                    document.getElementById('edit_sale_price').value = data.sale_price || '';
                    document.getElementById('edit_category_id').value = data.category_id;
                    document.getElementById('edit_stock').value = data.stock;
                    document.getElementById('edit_rating').value = data.rating;
                    document.getElementById('edit_is_featured').checked = data.is_featured == 1;
                    document.getElementById('edit_is_new').checked = data.is_new == 1;
                    document.getElementById('edit_is_sale').checked = data.is_sale == 1;
                    
                    // Show current image if exists
                    const currentImageDiv = document.getElementById('current_image');
                    if (data.image_data) {
                        currentImageDiv.innerHTML = `<img src="data:${data.image_mime};base64,${data.image_data}" class="img-thumbnail" style="max-width: 100px;">`;
                    } else if (data.image) {
                        currentImageDiv.innerHTML = `<img src="${data.image}" class="img-thumbnail" style="max-width: 100px;">`;
                    } else {
                        currentImageDiv.innerHTML = '<p class="text-muted">No image</p>';
                    }
                    
                    new bootstrap.Modal(document.getElementById('editProductModal')).show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading product data: ' + error.message);
                })
                .finally(() => {
                    // Restore button state
                    button.innerHTML = originalContent;
                    button.disabled = false;
                });
        }
    </script>
</body>
</html> 