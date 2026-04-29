-- ===============================
-- ONLINE SHOP DATABASE (ADVANCED)
-- ===============================


-- ===============================
-- USERS
-- ===============================
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    password TEXT,
    user_type VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- PRODUCTS
-- ===============================
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255),
    description TEXT,
    price NUMERIC(10,2),
    stock INT DEFAULT 0,
    image TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- CATEGORIES
-- ===============================
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100)
);

-- ===============================
-- PRODUCT-CATEGORIES
-- ===============================
CREATE TABLE product_categories (
    id SERIAL PRIMARY KEY,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    category_id INT REFERENCES categories(id) ON DELETE CASCADE
);

-- ===============================
-- WISHLIST
-- ===============================
CREATE TABLE wishlist (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE
);

-- ===============================
-- CART
-- ==============================
DROP TABLE cart;

CREATE TABLE cart (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id) ON DELETE CASCADE,
    name VARCHAR(255),
    price NUMERIC(10,2),
    quantity INT DEFAULT 1,
    image TEXT
);

INSERT INTO cart (user_id, product_id, name, price, quantity, image)
VALUES (1, 1, 'test', 100, 1, 'test.jpg');

-- ===============================
-- ORDERS (IMPROVED)
-- ===============================
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),

    subtotal NUMERIC(10,2),
    shipping_fee NUMERIC(10,2) DEFAULT 0,
    total NUMERIC(10,2),

    status VARCHAR(50) DEFAULT 'pending', 
    -- pending, paid, processing, shipped, delivered, cancelled

    payment_method VARCHAR(50), -- COD, GCash, Card
    payment_status VARCHAR(50) DEFAULT 'unpaid', 
    -- unpaid, paid, failed

    address TEXT,
    phone VARCHAR(20),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- ORDER ITEMS (IMPROVED)
-- ===============================
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id) ON DELETE CASCADE,
    product_id INT REFERENCES products(id),

    product_name VARCHAR(255),
    product_price NUMERIC(10,2),

    quantity INT,
    total_price NUMERIC(10,2)
);

-- ===============================
-- PAYMENTS (IMPROVED)
-- ===============================
CREATE TABLE payments (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id),

    amount NUMERIC(10,2),
    method VARCHAR(50), -- GCash, PayPal, Card, COD

    transaction_reference VARCHAR(255), -- gcash ref number
    payment_status VARCHAR(50), -- success, pending, failed

    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- SHIPPING
-- ===============================
CREATE TABLE shipping (
    id SERIAL PRIMARY KEY,
    order_id INT REFERENCES orders(id),

    courier VARCHAR(100),
    tracking_number VARCHAR(100),

    shipping_status VARCHAR(50) DEFAULT 'pending',
    -- pending, shipped, in_transit, delivered

    shipped_at TIMESTAMP,
    delivered_at TIMESTAMP
);

-- ===============================
-- REVIEWS
-- ===============================
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),
    product_id INT REFERENCES products(id),

    rating INT CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- NOTIFICATIONS
-- ===============================
CREATE TABLE notifications (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id),

    message TEXT,
    is_read BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===============================
-- SAMPLE DATA
-- ===============================
INSERT INTO users (name, email, password, user_type)
VALUES ('Admin', 'admin@gmail.com', '123456', 'admin');

INSERT INTO categories (name) VALUES ('Fruits'), ('Vegetables'),
('Fish'), ('Meats');

INSERT INTO products (name, description, price, stock, image)
VALUES 
('Apple', '200/kg', 200.00, 100, 'apple.jpg'),
('Banana', '160/kg', 160.00, 100, 'banana.jpg'),
('Water Melon', '250/kg', 250.00, 100, 'melon.jpg'),
('Strawberry', '350/kg', 350.00, 100, 'strawberry.jpg'),
('Water Melon', '250/kg', 250.00, 100, 'melon.jpg'),


('Carrots', '150/kg', 150.00, 100, 'carrot.jpg'),
('Kalabasa', '180/kg', 180.00, 100, 'kalabasa.jpg'),
('Talong', '120/kg', 120.00, 100, 'talong.jpg'),
('Petchay', '150/kg', 150.00, 100, 'pechay.jpg'),

('Tuna', '350/kg', 350.00, 100, 'tuna.jpg'),
('Tilapia', '250/kg', 250.00, 100, 'tilapia.jpg'),

('Beef', '400/kg', 400.00, 100, 'beef.jpg'),
('Pork', '350/kg', 350.00, 100, 'pork.jpg'),
('Chicken', '250/kg', 250.00, 50, 'chicken.jpg');


INSERT INTO product_categories (product_id, category_id)
VALUES (1,1), (2,2);