-- Database: projectweb
CREATE DATABASE IF NOT EXISTS projectweb;
USE projectweb;

-- Tabel Users untuk sistem login
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'staff', 'user') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Categories untuk kategori makanan
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel Menu untuk daftar makanan
CREATE TABLE menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT,
    image VARCHAR(255),
    status ENUM('available', 'unavailable') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Tabel Orders untuk pesanan
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20),
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel Order Items untuk detail pesanan
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    menu_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menu(id) ON DELETE CASCADE
);

-- Insert default users
INSERT INTO users (username, password, role, full_name, email) VALUES
('superadmin', MD5('superadmin'), 'super_admin', 'Super Administrator', 'admin@anekarasa.com'),
('staff', MD5('staff'), 'staff', 'Staff Warung', 'staff@anekarasa.com'),
('user1', MD5('user1'), 'user', 'Customer User', 'user1@email.com');

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Makanan Utama', 'Nasi dan lauk pauk'),
('Minuman', 'Berbagai jenis minuman segar'),
('Snack', 'Makanan ringan dan cemilan');

-- Insert sample menu
INSERT INTO menu (name, description, price, category_id, status) VALUES
('Nasi Gudeg', 'Nasi dengan gudeg khas Yogyakarta', 15000, 1, 'available'),
('Nasi Pecel', 'Nasi dengan sayuran dan bumbu pecel', 12000, 1, 'available'),
('Es Teh Manis', 'Teh manis dingin segar', 5000, 2, 'available'),
('Es Jeruk', 'Jeruk peras segar dengan es', 8000, 2, 'available'),
('Kerupuk', 'Kerupuk rambak crispy', 3000, 3, 'available'),
('Tempe Goreng', 'Tempe goreng crispy', 5000, 3, 'available');

-- Insert sample orders
INSERT INTO orders (user_id, customer_name, customer_phone, total_amount, status) VALUES
(3, 'Budi Santoso', '081234567890', 27000, 'pending'),
(3, 'Siti Aminah', '081234567891', 20000, 'confirmed');

-- Insert sample order items
INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES
(1, 1, 1, 15000),
(1, 3, 2, 5000),
(1, 5, 1, 3000),
(2, 2, 1, 12000),
(2, 4, 1, 8000);
