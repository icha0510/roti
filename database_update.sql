-- Database Update Script for Cart and Orders
-- Run this if you get errors about existing tables

-- Step 1: Drop existing tables (if they exist)
DROP TABLE IF EXISTS `order_tracking`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `cart`;

-- Step 2: Create new tables with proper structure
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_address` text,
  `status` enum('pending','processing','shipped','completed','cancelled') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
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

-- Step 3: Insert sample data
INSERT INTO `orders` (`order_number`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `status`, `total_amount`, `notes`) VALUES
('ORD-2024-001', 'John Doe', 'john@example.com', '+1234567890', '123 Main St, City, Country', 'pending', 45.98, 'Please deliver in the morning'),
('ORD-2024-002', 'Jane Smith', 'jane@example.com', '+0987654321', '456 Oak Ave, Town, Country', 'processing', 32.50, 'Gift wrapping requested'),
('ORD-2024-003', 'Mike Johnson', 'mike@example.com', '+1122334455', '789 Pine Rd, Village, Country', 'completed', 67.25, 'Leave at front door');

INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `quantity`, `price`) VALUES
(1, 1, 'Artisan Bread', 2, 12.99),
(1, 3, 'Croissant', 1, 20.00),
(2, 2, 'Sourdough Bread', 1, 15.50),
(2, 4, 'Baguette', 1, 17.00),
(3, 1, 'Artisan Bread', 3, 12.99),
(3, 5, 'Whole Wheat Bread', 2, 14.63);

-- Step 4: Update totals
UPDATE orders o 
SET total_amount = (
    SELECT SUM(oi.quantity * oi.price) 
    FROM order_items oi 
    WHERE oi.order_id = o.id
);

-- Update tabel orders yang sudah ada (jika ada) dengan user_id default
-- UPDATE orders SET user_id = 1 WHERE user_id IS NULL; 