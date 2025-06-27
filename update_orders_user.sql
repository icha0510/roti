-- Update tabel orders untuk menambahkan kolom user_id
USE bready;

-- Tambahkan kolom user_id jika belum ada
ALTER TABLE orders ADD COLUMN user_id INT NULL AFTER order_number;

-- Tambahkan foreign key jika belum ada
ALTER TABLE orders ADD CONSTRAINT fk_orders_user_id FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Update order yang sudah ada dengan user_id default (jika ada user dengan id 1)
-- UPDATE orders SET user_id = 1 WHERE user_id IS NULL AND EXISTS (SELECT 1 FROM users WHERE id = 1); 