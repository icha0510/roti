-- Update database untuk mendukung penyimpanan gambar dalam database
USE bready_db;

-- Update tabel products untuk menyimpan gambar dalam database
ALTER TABLE products 
ADD COLUMN image_data LONGTEXT AFTER image,
ADD COLUMN image_mime VARCHAR(100) AFTER image_data;

-- Update tabel banners untuk menyimpan gambar dalam database
ALTER TABLE banners 
ADD COLUMN image_data LONGTEXT AFTER image,
ADD COLUMN image_mime VARCHAR(100) AFTER image_data;

-- Update tabel testimonials untuk menyimpan gambar dalam database
ALTER TABLE testimonials 
ADD COLUMN image_data LONGTEXT AFTER image,
ADD COLUMN image_mime VARCHAR(100) AFTER image_data;

-- Update tabel posts untuk menyimpan gambar dalam database
ALTER TABLE posts 
ADD COLUMN image_data LONGTEXT AFTER image,
ADD COLUMN image_mime VARCHAR(100) AFTER image_data;

-- Update tabel awards untuk menyimpan gambar dalam database
ALTER TABLE awards 
ADD COLUMN image_data LONGTEXT AFTER icon,
ADD COLUMN image_mime VARCHAR(100) AFTER image_data;

-- Hapus kolom image lama (opsional, jika ingin tetap menyimpan path file)
-- ALTER TABLE products DROP COLUMN image;
-- ALTER TABLE banners DROP COLUMN image;
-- ALTER TABLE testimonials DROP COLUMN image;
-- ALTER TABLE posts DROP COLUMN image;
-- ALTER TABLE awards DROP COLUMN icon; 