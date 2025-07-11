-- Menambahkan kolom payment_method ke tabel orders
ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cash' AFTER notes;

-- Update data yang sudah ada untuk memiliki payment_method default
UPDATE orders SET payment_method = 'cash' WHERE payment_method IS NULL;

-- Menambahkan index untuk optimasi query
CREATE INDEX idx_payment_method ON orders(payment_method);
CREATE INDEX idx_status_payment ON orders(status, payment_method); 