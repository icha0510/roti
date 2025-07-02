-- Fix Order Tracking Table
-- Run this script to create or fix the order_tracking table

-- Drop table if exists and recreate
DROP TABLE IF EXISTS `order_tracking`;

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial tracking records for existing orders (if any)
INSERT INTO `order_tracking` (`order_id`, `status`, `description`, `created_at`)
SELECT 
    o.id,
    CASE 
        WHEN o.status = 'completed' THEN 'delivered'
        WHEN o.status = 'process' THEN 'processing'
        WHEN o.status = 'cancel' THEN 'cancelled'
        ELSE o.status
    END as status,
    CASE 
        WHEN o.status = 'completed' THEN 'Order has been delivered'
        WHEN o.status = 'process' THEN 'Order is being processed'
        WHEN o.status = 'cancel' THEN 'Order has been cancelled'
        WHEN o.status = 'pending' THEN 'Order has been placed successfully'
        ELSE 'Order status updated'
    END as description,
    o.created_at
FROM `orders` o
WHERE NOT EXISTS (
    SELECT 1 FROM `order_tracking` ot WHERE ot.order_id = o.id
);

-- Update orders table status enum if needed
ALTER TABLE `orders` MODIFY COLUMN `status` enum('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending';

-- Update existing status values to match new enum
UPDATE `orders` SET `status` = 'delivered' WHERE `status` = 'completed';
UPDATE `orders` SET `status` = 'processing' WHERE `status` = 'process';
UPDATE `orders` SET `status` = 'cancelled' WHERE `status` = 'cancel'; 