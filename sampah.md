-- Tabel untuk cart (keranjang belanja)
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk orders (pesanan)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_address` text,
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk order_items (item pesanan)
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel untuk order_tracking (tracking pesanan)
CREATE TABLE IF NOT EXISTS `order_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample order data
INSERT INTO `orders` (`customer_name`, `customer_email`, `customer_phone`, `customer_address`, `status`, `total_amount`, `notes`) VALUES
('John Doe', 'john@example.com', '+1234567890', '123 Main St, City, Country', 'pending', 45.98, 'Please deliver in the morning'),
('Jane Smith', 'jane@example.com', '+0987654321', '456 Oak Ave, Town, Country', 'processing', 32.50, 'Gift wrapping requested'),
('Mike Johnson', 'mike@example.com', '+1122334455', '789 Pine Rd, Village, Country', 'completed', 67.25, 'Leave at front door');

-- Sample order items data
INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(1, 1, 'Artisan Bread', 2, 12.99),
(1, 3, 'Croissant', 1, 20.00),
(2, 2, 'Sourdough Bread', 1, 15.50),
(2, 4, 'Baguette', 1, 17.00),
(3, 1, 'Artisan Bread', 3, 12.99),
(3, 5, 'Whole Wheat Bread', 2, 14.63);

-- Update orders total_amount based on order_items
UPDATE orders o 
SET total_amount = (
    SELECT SUM(oi.quantity * oi.price) 
    FROM order_items oi 
    WHERE oi.order_id = o.id
); 