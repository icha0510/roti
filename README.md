# Bready - Bakery E-commerce Website

A complete bakery e-commerce website built with PHP, MySQL, and modern web technologies.

## Features

### 🛒 Shopping Cart System
- **Add to Cart**: Click the shopping cart icon on any product to add it to your cart
- **Cart Management**: View, update quantities, and remove items from cart
- **Session-based Cart**: Cart data persists during browser session
- **Real-time Cart Count**: Cart icon shows current number of items

### 📦 Order Management
- **Checkout Process**: Complete order form with customer details
- **Order Confirmation**: Success page with order details
- **Admin Order Tracking**: Full order management system for administrators

### 🎨 Product Management
- **Product Catalog**: Browse products by category
- **Product Details**: Detailed product pages with images and descriptions
- **Product Images**: Support for both URL images and base64 encoded images
- **Product Categories**: Organized product browsing

### 👨‍💼 Admin Dashboard
- **Product Management**: Add, edit, delete products with image upload
- **Order Management**: View and update order statuses
- **Banner Management**: Upload and manage homepage banners
- **Category Management**: Organize products into categories

### 🖼️ Image Management
- **Base64 Storage**: Images stored directly in database as base64
- **Image Upload**: Admin can upload images through web interface
- **Responsive Images**: Images display properly on all devices

## Installation

### 1. Database Setup
```sql
-- Import the main database structure
mysql -u your_username -p your_database < database.sql

-- Import cart and orders structure
mysql -u your_username -p your_database < database_cart_orders.sql
```

### 2. Configuration
1. Update database connection in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

2. Ensure your web server supports PHP sessions

### 3. File Permissions
Make sure the following directories are writable:
- `admin/uploads/` (for image uploads)

## File Structure

```
bready/
├── admin/                    # Admin dashboard
│   ├── index.php            # Admin home
│   ├── products.php         # Product management
│   ├── orders.php           # Order management
│   ├── banners.php          # Banner management
│   └── config/
│       └── database.php     # Database configuration
├── config/
│   └── database.php         # Database configuration
├── includes/
│   └── functions.php        # Helper functions
├── cart_actions.php         # Cart operations
├── cart.php                 # Shopping cart page
├── checkout.php             # Checkout process
├── order-success.php        # Order confirmation
├── index.php                # Homepage
├── product-listing.php      # Product catalog
├── product-detail.php       # Product details
└── database_cart_orders.sql # Cart and orders database
```

## Usage

### For Customers
1. **Browse Products**: Visit `product-listing.php` to see all products
2. **Add to Cart**: Click the shopping cart icon on any product
3. **View Cart**: Click the cart icon in the header to view your cart
4. **Checkout**: Proceed to checkout from the cart page
5. **Complete Order**: Fill in your details and place the order

### For Administrators
1. **Access Admin**: Go to `admin/index.php`
2. **Manage Products**: Add, edit, or delete products
3. **Track Orders**: View and update order statuses in `admin/orders.php`
4. **Upload Images**: Use the image upload feature in product management

## Cart System Details

### Cart Actions
- **Add to Cart**: `cart_actions.php?action=add&id=PRODUCT_ID&quantity=QUANTITY`
- **Remove from Cart**: `cart_actions.php?action=remove&id=PRODUCT_ID`
- **Update Quantity**: `cart_actions.php?action=update&id=PRODUCT_ID&quantity=QUANTITY`
- **Clear Cart**: `cart_actions.php?action=clear`

### Session Storage
Cart data is stored in PHP sessions:
```php
$_SESSION['cart'] = [
    'product_id' => [
        'id' => 1,
        'name' => 'Product Name',
        'price' => 19.99,
        'quantity' => 2,
        'image_data' => 'base64_encoded_image',
        'image_mime' => 'image/jpeg'
    ]
];
```

## Order Management

### Order Statuses
- **Pending**: Order received, awaiting processing
- **Processing**: Order is being prepared
- **Shipped**: Order has been shipped
- **Completed**: Order delivered successfully
- **Cancelled**: Order cancelled

### Order Flow
1. Customer places order → Status: Pending
2. Admin processes order → Status: Processing
3. Order shipped → Status: Shipped
4. Order delivered → Status: Completed

## Database Tables

### Core Tables
- `products`: Product information and images
- `categories`: Product categories
- `banners`: Homepage banners
- `testimonials`: Customer testimonials
- `posts`: Blog posts
- `awards`: Company awards

### Cart & Order Tables
- `orders`: Customer order information
- `order_items`: Individual items in each order

## Security Features

- **SQL Injection Protection**: Prepared statements used throughout
- **XSS Protection**: HTML escaping for user input
- **Session Security**: Secure session handling
- **File Upload Validation**: Image upload security checks

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Internet Explorer 11+

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- GD extension for image processing
- Session support enabled

## Troubleshooting

### Common Issues

1. **Images not displaying**: Check if GD extension is enabled
2. **Cart not working**: Ensure sessions are enabled
3. **Database connection error**: Verify database credentials in config files
4. **Upload errors**: Check directory permissions for upload folders

### Debug Mode
Enable error reporting in PHP for debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

For technical support or questions, please check:
1. Database connection settings
2. File permissions
3. PHP error logs
4. Browser console for JavaScript errors

## License

This project is for educational purposes. Feel free to modify and use for your own projects. 