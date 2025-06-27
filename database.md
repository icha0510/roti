-- Database untuk website Bready Bakery
CREATE DATABASE IF NOT EXISTS bready_db;
USE bready_db;

-- Tabel untuk kategori produk
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk produk
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    sale_price DECIMAL(10,2) NULL,
    category_id INT,
    image VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_new BOOLEAN DEFAULT FALSE,
    is_sale BOOLEAN DEFAULT FALSE,
    rating DECIMAL(3,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Tabel untuk banner/slider
CREATE TABLE banners (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200),
    subtitle VARCHAR(200),
    image VARCHAR(255) NOT NULL,
    link VARCHAR(255),
    badge_text VARCHAR(50),
    badge_type ENUM('sale', 'new', 'featured') DEFAULT 'sale',
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk testimonial
CREATE TABLE testimonials (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    company VARCHAR(100),
    content TEXT NOT NULL,
    rating INT DEFAULT 5,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk blog/posts
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    content TEXT,
    excerpt TEXT,
    image VARCHAR(255),
    author VARCHAR(100) DEFAULT 'Admin',
    status ENUM('published', 'draft') DEFAULT 'published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk awards/penghargaan
CREATE TABLE awards (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    subtitle VARCHAR(200),
    description TEXT,
    year_start VARCHAR(10),
    year_end VARCHAR(10),
    icon VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert data kategori
INSERT INTO categories (name, slug, description) VALUES
('Bakery', 'bakery', 'Produk roti dan kue'),
('Sweet', 'sweet', 'Produk manis dan dessert'),
('Bio', 'bio', 'Produk organik dan sehat');

-- Insert data produk
INSERT INTO products (name, slug, description, price, sale_price, category_id, image, stock, is_featured, is_new, is_sale, rating) VALUES
('Red Sugar Flower', 'red-sugar-flower', 'Roti dengan hiasan bunga gula merah yang cantik', 5.00, NULL, 1, 'images/products/1.png', 50, TRUE, FALSE, FALSE, 4.5),
('Chocolate Cake', 'chocolate-cake', 'Kue cokelat lembut dengan topping cokelat', 5.00, NULL, 2, 'images/products/2.png', 30, TRUE, TRUE, FALSE, 4.8),
('Vanilla Cupcake', 'vanilla-cupcake', 'Cupcake vanilla dengan frosting manis', 5.00, NULL, 2, 'images/products/3.png', 40, TRUE, TRUE, FALSE, 4.3),
('Whole Grain Bread', 'whole-grain-bread', 'Roti gandum utuh yang sehat', 8.50, 5.00, 3, 'images/products/4.png', 25, TRUE, FALSE, TRUE, 4.6),
('Croissant', 'croissant', 'Croissant klasik dengan tekstur berlapis', 5.00, NULL, 1, 'images/products/5.png', 35, TRUE, FALSE, FALSE, 4.7),
('Donut Glazed', 'donut-glazed', 'Donut dengan glazing manis', 5.00, NULL, 2, 'images/products/6.png', 45, TRUE, FALSE, FALSE, 4.4);

-- Insert data banner
INSERT INTO banners (title, subtitle, image, link, badge_text, badge_type, sort_order) VALUES
('Special Offer', '50% Off', 'images/banner/slider-5.png', 'order-form.html', '50%', 'sale', 1),
('New Product', 'Fresh Baked', 'images/banner/slider-6.png', 'order-form.html', 'New', 'new', 2);

-- Insert data testimonial
INSERT INTO testimonials (name, position, company, content, rating, image) VALUES
('Logan May', 'CEO & Founder', 'Invision', 'Dessert pudding dessert jelly beans cupcake sweet caramels gingerbread. Fruitcake biscuit cheesecake. Cookie topping sweet muffin pudding tart bear claw sugar plum croissant.', 5, 'images/user/1.jpg'),
('Sarah Johnson', 'Marketing Director', 'TechCorp', 'Dessert pudding dessert jelly beans cupcake sweet caramels gingerbread. Fruitcake biscuit cheesecake. Cookie topping sweet muffin pudding tart bear claw sugar plum croissant.', 5, 'images/user/2.jpg'),
('Mike Chen', 'Product Manager', 'StartupXYZ', 'Dessert pudding dessert jelly beans cupcake sweet caramels gingerbread. Fruitcake biscuit cheesecake. Cookie topping sweet muffin pudding tart bear claw sugar plum croissant.', 5, 'images/user/3.jpg'),
('Emily Davis', 'Creative Director', 'DesignStudio', 'Dessert pudding dessert jelly beans cupcake sweet caramels gingerbread. Fruitcake biscuit cheesecake. Cookie topping sweet muffin pudding tart bear claw sugar plum croissant.', 5, 'images/user/4.jpg');

-- Insert data posts
INSERT INTO posts (title, slug, content, excerpt, image, author, status) VALUES
('Buttery Toast', 'buttery-toast', 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition.', 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further…', 'images/posts/home-1.jpg', 'Alena Studio', 'published'),
('Pumpkin Buns with Salted', 'pumpkin-buns-with-salted', 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition.', 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further…', 'images/posts/home-2.jpg', 'Alena Studio', 'published'),
('Tartine Style Bread', 'tartine-style-bread', 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further the overall value proposition.', 'Leverage agile frameworks to provide a robust synopsis for high level overviews. Iterative approaches to corporate strategy foster collaborative thinking to further…', 'images/posts/home-3.jpg', 'Alena Studio', 'published');

-- Insert data awards
INSERT INTO awards (title, subtitle, description, year_start, year_end, icon) VALUES
('BAKERY OF THE YEAR', '2011 - 2012', 'Tart bear claw cake tiramisu chocolate bar gummies dragée lemon drops brownie. Jujubes chocolate cake sesame snaps', '2011', '2012', 'images/icons/award-1.png'),
('CUPCAKES SHOP OF THE YEAR', '2012 - 2015', 'Tart bear claw cake tiramisu chocolate bar gummies dragée lemon drops brownie. Jujubes chocolate cake sesame snaps', '2012', '2015', 'images/icons/award-2.png'),
('BAKERY OF THE MONTH', '2017 - 2018', 'Tart bear claw cake tiramisu chocolate bar gummies dragée lemon drops brownie. Jujubes chocolate cake sesame snaps', '2017', '2018', 'images/icons/award-2.png');