CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(500),
    category VARCHAR(100) NOT NULL,
    stock INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipping', 'delivered', 'cancelled') DEFAULT 'pending',
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
    full_name VARCHAR(100)
);

-- Insert Demo Data
INSERT INTO products (name, price, image_url, category) VALUES 
('Fresh Tomatoes (1kg)', 5.50, 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?auto=format&fit=crop&w=400&q=80', 'vegetables'),
('Local Cucumber (1kg)', 4.00, 'https://images.unsplash.com/photo-1604977042946-1eecc6a22662?auto=format&fit=crop&w=400&q=80', 'vegetables'),
('Fresh Milk (2L)', 12.00, 'https://images.unsplash.com/photo-1563636619-e9143da7973b?auto=format&fit=crop&w=400&q=80', 'grocery'),
('Chicken Breast (500g)', 18.50, 'https://images.unsplash.com/photo-1604503468506-a8da13d82791?auto=format&fit=crop&w=400&q=80', 'meat'),
('Salmon Fillet (200g)', 25.00, 'https://images.unsplash.com/photo-1599084993091-1cb5c0721cc6?auto=format&fit=crop&w=400&q=80', 'fish'),
('Rice (5kg)', 35.00, 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=400&q=80', 'hypermarket'),
('Fresh Bananas (1kg)', 6.00, 'https://images.unsplash.com/photo-1603833665858-e61d17a86224?auto=format&fit=crop&w=400&q=80', 'vegetables'),
('Lamb Chops (500g)', 45.00, 'https://images.unsplash.com/photo-1603360946369-dc9bb6f5429a?auto=format&fit=crop&w=400&q=80', 'meat'),
('Fresh Shrimps (500g)', 30.00, 'https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?auto=format&fit=crop&w=400&q=80', 'fish'),
('Sunflower Oil (1.5L)', 18.00, 'https://images.unsplash.com/photo-1474979266404-7eaacbcd041c?auto=format&fit=crop&w=400&q=80', 'grocery'),
('Italian Spaghetti (500g)', 8.50, 'https://images.unsplash.com/photo-1595295333158-4742f28fbd85?auto=format&fit=crop&w=400&q=80', 'hypermarket'),
('Orange Juice (1L)', 10.00, 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?auto=format&fit=crop&w=400&q=80', 'grocery');
