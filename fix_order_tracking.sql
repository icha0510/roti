-- Fix untuk tabel order_tracking
-- Jalankan script ini untuk memperbaiki masalah update status

-- Drop tabel order_tracking yang ada (jika ada)
DROP TABLE IF EXISTS `order_tracking`;

-- Buat ulang tabel order_tracking dengan struktur yang benar
CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample tracking data untuk testing
INSERT INTO `order_tracking` (`order_id`, `status`, `description`, `created_at`) VALUES
(1, 'pending', 'Order has been placed successfully', NOW()),
(2, 'processing', 'Order is being processed', NOW()),
(3, 'delivered', 'Order has been delivered', NOW());

-- Update status di tabel orders agar konsisten
UPDATE orders SET status = 'pending' WHERE status NOT IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled');

-- Tambahkan index untuk performa
CREATE INDEX idx_order_tracking_order_id ON order_tracking(order_id);
CREATE INDEX idx_order_tracking_status ON order_tracking(status);
CREATE INDEX idx_order_tracking_created_at ON order_tracking(created_at); 