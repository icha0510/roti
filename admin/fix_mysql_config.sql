-- Fix MySQL configuration untuk menangani gambar besar
-- Jalankan sebagai root user

-- Set max_allowed_packet ke 16MB
SET GLOBAL max_allowed_packet = 16777216;

-- Set net_buffer_length ke 1MB
SET GLOBAL net_buffer_length = 1048576;

-- Set max_allowed_packet untuk session saat ini
SET SESSION max_allowed_packet = 16777216;

-- Verifikasi pengaturan
SHOW VARIABLES LIKE 'max_allowed_packet';
SHOW VARIABLES LIKE 'net_buffer_length'; 