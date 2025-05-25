-- Ecommerce POS Database Schema

-- Categories Table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subcategories Table
CREATE TABLE subcategories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Products Table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    subcategory_id INT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    short_description VARCHAR(500),
    sku VARCHAR(100) UNIQUE NOT NULL,
    barcode VARCHAR(100) UNIQUE,
    selling_price DECIMAL(10,2) NOT NULL,
    cost_price DECIMAL(10,2), -- Current weighted average cost
    markup_percentage DECIMAL(5,2) DEFAULT 0, -- For automatic pricing
    pricing_method ENUM('manual', 'cost_plus', 'market_based') DEFAULT 'manual',
    auto_update_price BOOLEAN DEFAULT FALSE, -- Auto-update selling price when cost changes
    stock_quantity INT DEFAULT 0,
    min_stock_level INT DEFAULT 5,
    image VARCHAR(255),
    is_hot_item BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    weight DECIMAL(8,2),
    dimensions VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (subcategory_id) REFERENCES subcategories(id),
    INDEX idx_sku (sku),
    INDEX idx_barcode (barcode),
    INDEX idx_hot_items (is_hot_item),
    INDEX idx_stock (stock_quantity),
    INDEX idx_pricing (pricing_method, auto_update_price)
);

-- Purchase Orders Table (for tracking stock purchases)
CREATE TABLE purchase_orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    po_number VARCHAR(50) UNIQUE NOT NULL,
    supplier_name VARCHAR(200),
    supplier_contact VARCHAR(100),
    status ENUM('pending', 'received', 'partial', 'cancelled') DEFAULT 'pending',
    total_amount DECIMAL(12,2),
    notes TEXT,
    ordered_date DATE,
    received_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_po_number (po_number),
    INDEX idx_created_by (created_by)
);

-- Purchase Order Items Table
CREATE TABLE purchase_order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    purchase_order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_ordered INT NOT NULL,
    quantity_received INT DEFAULT 0,
    cost_price DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(12,2) NOT NULL,
    expiry_date DATE NULL,
    batch_number VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Stock Batches Table (for FIFO/LIFO/Weighted Average costing)
CREATE TABLE stock_batches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    batch_number VARCHAR(100),
    purchase_order_item_id INT,
    cost_price DECIMAL(10,2) NOT NULL,
    quantity_available INT NOT NULL,
    quantity_sold INT DEFAULT 0,
    expiry_date DATE NULL,
    received_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (purchase_order_item_id) REFERENCES purchase_order_items(id),
    INDEX idx_product_batch (product_id, is_active),
    INDEX idx_fifo_order (product_id, received_date, id)
);

-- Stock Movements Table (Enhanced for tracking inventory changes with costing)
CREATE TABLE stock_movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    batch_id INT,
    movement_type ENUM('IN', 'OUT', 'ADJUSTMENT') NOT NULL,
    quantity INT NOT NULL,
    cost_price DECIMAL(10,2),
    total_cost DECIMAL(12,2),
    reference_type ENUM('PURCHASE', 'SALE', 'ADJUSTMENT', 'RETURN', 'TRANSFER') NOT NULL,
    reference_id INT,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_product_movement (product_id, created_at),
    INDEX idx_batch_id (batch_id),
    INDEX idx_created_by (created_by)
);

-- Product Pricing History Table
CREATE TABLE product_pricing_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    old_selling_price DECIMAL(10,2),
    new_selling_price DECIMAL(10,2) NOT NULL,
    old_cost_price DECIMAL(10,2),
    new_cost_price DECIMAL(10,2),
    reason ENUM('cost_change', 'manual_update', 'promotion', 'markup_change') NOT NULL,
    margin_percentage DECIMAL(5,2),
    changed_by INT,
    effective_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_product_pricing (product_id, effective_date),
    INDEX idx_changed_by (changed_by)
);

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier', 'customer') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Customer Addresses Table
CREATE TABLE customer_addresses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('billing', 'shipping') DEFAULT 'shipping',
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    company VARCHAR(100),
    address_line_1 VARCHAR(200) NOT NULL,
    address_line_2 VARCHAR(200),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'Bangladesh',
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Coupons Table
CREATE TABLE coupons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    type ENUM('fixed', 'percentage') NOT NULL,
    value DECIMAL(10,2) NOT NULL,
    minimum_amount DECIMAL(10,2) DEFAULT 0,
    maximum_discount DECIMAL(10,2),
    usage_limit INT,
    used_count INT DEFAULT 0,
    valid_from TIMESTAMP NOT NULL,
    valid_until TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_validity (valid_from, valid_until)
);

-- Orders Table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT,
    order_type ENUM('online', 'pos') NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method ENUM('bkash', 'nogod', 'cash') NOT NULL,
    transaction_id VARCHAR(100),
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    coupon_id INT,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'BDT',
    notes TEXT,
    
    -- Billing Address
    billing_first_name VARCHAR(50),
    billing_last_name VARCHAR(50),
    billing_company VARCHAR(100),
    billing_address_line_1 VARCHAR(200),
    billing_address_line_2 VARCHAR(200),
    billing_city VARCHAR(100),
    billing_state VARCHAR(100),
    billing_postal_code VARCHAR(20),
    billing_country VARCHAR(100),
    billing_phone VARCHAR(20),
    
    -- Shipping Address
    shipping_first_name VARCHAR(50),
    shipping_last_name VARCHAR(50),
    shipping_company VARCHAR(100),
    shipping_address_line_1 VARCHAR(200),
    shipping_address_line_2 VARCHAR(200),
    shipping_city VARCHAR(100),
    shipping_state VARCHAR(100),
    shipping_postal_code VARCHAR(20),
    shipping_country VARCHAR(100),
    shipping_phone VARCHAR(20),
    
    processed_by INT, -- For POS orders (cashier)
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    FOREIGN KEY (processed_by) REFERENCES users(id),
    INDEX idx_order_number (order_number),
    INDEX idx_user_orders (user_id),
    INDEX idx_order_date (created_at),
    INDEX idx_order_type (order_type),
    INDEX idx_payment_status (payment_status)
);

-- Order Items Table
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL, -- Store at time of order
    product_sku VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_order_items (order_id)
);

-- Shopping Cart Table (for online users)
CREATE TABLE cart_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Coupon Usage Table
CREATE TABLE coupon_usage (
    id INT PRIMARY KEY AUTO_INCREMENT,
    coupon_id INT NOT NULL,
    order_id INT NOT NULL,
    user_id INT,
    discount_amount DECIMAL(10,2) NOT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_coupon_order (coupon_id, order_id)
);

-- Payment Transactions Table
CREATE TABLE payment_transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    transaction_id VARCHAR(100) NOT NULL,
    payment_method ENUM('bkash', 'nogod', 'cash') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    gateway_response TEXT,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_transaction_id (transaction_id),
    INDEX idx_payment_method (payment_method)
);

-- Settings Table (for app configuration)
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    key_name VARCHAR(100) UNIQUE NOT NULL,
    value TEXT,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Reports Cache Table (for performance)
CREATE TABLE report_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_type VARCHAR(50) NOT NULL,
    report_key VARCHAR(100) NOT NULL,
    data JSON,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NOT NULL,
    UNIQUE KEY unique_report (report_type, report_key)
);

-- Insert default settings
INSERT INTO settings (key_name, value, description) VALUES
('store_name', 'My Store', 'Store name'),
('currency', 'BDT', 'Default currency'),
('tax_rate', '0.00', 'Default tax rate percentage'),
('low_stock_threshold', '5', 'Default low stock threshold'),
('bkash_merchant_number', '', 'bKash merchant number'),
('nogod_merchant_id', '', 'Nogod merchant ID'),
('inventory_method', 'FIFO', 'Inventory costing method: FIFO, LIFO, or WEIGHTED_AVERAGE'),
('auto_price_update', '0', 'Automatically update selling prices when cost changes (0=No, 1=Yes)'),
('default_markup_percentage', '20.00', 'Default markup percentage for cost-plus pricing'),
('price_update_threshold', '5.00', 'Minimum cost change percentage to trigger price update');

-- Stored Procedures for Inventory Management

DELIMITER $

-- Calculate Weighted Average Cost
CREATE PROCEDURE CalculateWeightedAverageCost(IN product_id_param INT)
BEGIN
    DECLARE total_cost DECIMAL(15,2) DEFAULT 0;
    DECLARE total_quantity INT DEFAULT 0;
    DECLARE new_avg_cost DECIMAL(10,2) DEFAULT 0;
    
    -- Calculate weighted average from active batches
    SELECT 
        COALESCE(SUM(cost_price * quantity_available), 0),
        COALESCE(SUM(quantity_available), 0)
    INTO total_cost, total_quantity
    FROM stock_batches 
    WHERE product_id = product_id_param 
    AND is_active = TRUE 
    AND quantity_available > 0;
    
    -- Calculate new average cost
    IF total_quantity > 0 THEN
        SET new_avg_cost = total_cost / total_quantity;
        
        -- Update product cost price
        UPDATE products 
        SET cost_price = new_avg_cost,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = product_id_param;
        
        -- Check if auto price update is enabled for this product
        CALL UpdateSellingPriceIfEnabled(product_id_param, new_avg_cost);
    END IF;
END$

-- Update Selling Price Based on Cost Changes
CREATE PROCEDURE UpdateSellingPriceIfEnabled(
    IN product_id_param INT, 
    IN new_cost_price DECIMAL(10,2)
)
BEGIN
    DECLARE current_selling_price DECIMAL(10,2);
    DECLARE current_cost_price DECIMAL(10,2);
    DECLARE markup_percent DECIMAL(5,2);
    DECLARE new_selling_price DECIMAL(10,2);
    DECLARE cost_change_percent DECIMAL(5,2);
    DECLARE price_threshold DECIMAL(5,2);
    DECLARE auto_update_enabled BOOLEAN DEFAULT FALSE;
    DECLARE pricing_method_val VARCHAR(20);
    
    -- Get product pricing info
    SELECT 
        selling_price, 
        cost_price, 
        markup_percentage, 
        auto_update_price,
        pricing_method
    INTO 
        current_selling_price, 
        current_cost_price, 
        markup_percent, 
        auto_update_enabled,
        pricing_method_val
    FROM products 
    WHERE id = product_id_param;
    
    -- Get price update threshold from settings
    SELECT CAST(value AS DECIMAL(5,2)) INTO price_threshold
    FROM settings 
    WHERE key_name = 'price_update_threshold';
    
    -- Calculate cost change percentage
    IF current_cost_price > 0 THEN
        SET cost_change_percent = ABS((new_cost_price - current_cost_price) / current_cost_price * 100);
    ELSE
        SET cost_change_percent = 100; -- Force update if no previous cost
    END IF;
    
    -- Update selling price if conditions are met
    IF auto_update_enabled = TRUE 
       AND pricing_method_val = 'cost_plus'
       AND cost_change_percent >= price_threshold 
       AND markup_percent > 0 THEN
        
        SET new_selling_price = new_cost_price * (1 + markup_percent / 100);
        
        -- Insert pricing history record
        INSERT INTO product_pricing_history (
            product_id, 
            old_selling_price, 
            new_selling_price, 
            old_cost_price, 
            new_cost_price, 
            reason, 
            margin_percentage
        ) VALUES (
            product_id_param, 
            current_selling_price, 
            new_selling_price, 
            current_cost_price, 
            new_cost_price, 
            'cost_change', 
            markup_percent
        );
        
        -- Update product selling price
        UPDATE products 
        SET selling_price = new_selling_price,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = product_id_param;
    END IF;
END$

-- Process Stock Receipt (FIFO Method)
CREATE PROCEDURE ProcessStockReceipt(
    IN product_id_param INT,
    IN quantity_param INT,
    IN cost_price_param DECIMAL(10,2),
    IN batch_number_param VARCHAR(100),
    IN purchase_order_item_id_param INT,
    IN user_id_param INT
)
BEGIN
    DECLARE batch_id_val INT;
    
    -- Create new stock batch
    INSERT INTO stock_batches (
        product_id, 
        batch_number, 
        purchase_order_item_id,
        cost_price, 
        quantity_available, 
        received_date
    ) VALUES (
        product_id_param, 
        batch_number_param,
        purchase_order_item_id_param,
        cost_price_param, 
        quantity_param, 
        CURDATE()
    );
    
    SET batch_id_val = LAST_INSERT_ID();
    
    -- Record stock movement
    INSERT INTO stock_movements (
        product_id, 
        batch_id,
        movement_type, 
        quantity, 
        cost_price,
        total_cost,
        reference_type, 
        reference_id,
        created_by
    ) VALUES (
        product_id_param, 
        batch_id_val,
        'IN', 
        quantity_param, 
        cost_price_param,
        quantity_param * cost_price_param,
        'PURCHASE', 
        purchase_order_item_id_param,
        user_id_param
    );
    
    -- Update product stock quantity
    UPDATE products 
    SET stock_quantity = stock_quantity + quantity_param,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = product_id_param;
    
    -- Recalculate weighted average cost
    CALL CalculateWeightedAverageCost(product_id_param);
END$

-- Process Stock Sale (FIFO Method)
CREATE PROCEDURE ProcessStockSale(
    IN product_id_param INT,
    IN quantity_param INT,
    IN order_id_param INT,
    IN user_id_param INT
)
BEGIN
    DECLARE remaining_qty INT DEFAULT quantity_param;
    DECLARE batch_qty INT;
    DECLARE batch_id_val INT;
    DECLARE batch_cost DECIMAL(10,2);
    DECLARE done INT DEFAULT FALSE;
    
    -- Cursor for FIFO stock batches
    DECLARE batch_cursor CURSOR FOR
        SELECT id, quantity_available, cost_price
        FROM stock_batches
        WHERE product_id = product_id_param 
        AND quantity_available > 0
        AND is_active = TRUE
        ORDER BY received_date ASC, id ASC;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN batch_cursor;
    
    batch_loop: LOOP
        FETCH batch_cursor INTO batch_id_val, batch_qty, batch_cost;
        
        IF done OR remaining_qty <= 0 THEN
            LEAVE batch_loop;
        END IF;
        
        IF batch_qty >= remaining_qty THEN
            -- This batch can fulfill remaining quantity
            UPDATE stock_batches 
            SET quantity_available = quantity_available - remaining_qty,
                quantity_sold = quantity_sold + remaining_qty
            WHERE id = batch_id_val;
            
            -- Record stock movement
            INSERT INTO stock_movements (
                product_id, 
                batch_id,
                movement_type, 
                quantity, 
                cost_price,
                total_cost,
                reference_type, 
                reference_id,
                created_by
            ) VALUES (
                product_id_param, 
                batch_id_val,
                'OUT', 
                remaining_qty, 
                batch_cost,
                remaining_qty * batch_cost,
                'SALE', 
                order_id_param,
                user_id_param
            );
            
            SET remaining_qty = 0;
        ELSE
            -- Use entire batch and continue
            UPDATE stock_batches 
            SET quantity_available = 0,
                quantity_sold = quantity_sold + batch_qty,
                is_active = FALSE
            WHERE id = batch_id_val;
            
            -- Record stock movement
            INSERT INTO stock_movements (
                product_id, 
                batch_id,
                movement_type, 
                quantity, 
                cost_price,
                total_cost,
                reference_type, 
                reference_id,
                created_by
            ) VALUES (
                product_id_param, 
                batch_id_val,
                'OUT', 
                batch_qty, 
                batch_cost,
                batch_qty * batch_cost,
                'SALE', 
                order_id_param,
                user_id_param
            );
            
            SET remaining_qty = remaining_qty - batch_qty;
        END IF;
    END LOOP;
    
    CLOSE batch_cursor;
    
    -- Update product stock quantity
    UPDATE products 
    SET stock_quantity = stock_quantity - (quantity_param - remaining_qty),
        updated_at = CURRENT_TIMESTAMP
    WHERE id = product_id_param;
    
    -- Recalculate weighted average cost
    CALL CalculateWeightedAverageCost(product_id_param);
END$

DELIMITER ;
CREATE INDEX idx_products_category ON products(category_id, is_active);
CREATE INDEX idx_products_subcategory ON products(subcategory_id, is_active);
CREATE INDEX idx_orders_date_type ON orders(created_at, order_type);
CREATE INDEX idx_orders_payment ON orders(payment_method, payment_status);
CREATE INDEX idx_stock_low ON products(stock_quantity, min_stock_level);

-- Views for common queries
CREATE VIEW low_stock_products AS
SELECT 
    p.id,
    p.name,
    p.sku,
    p.stock_quantity,
    p.min_stock_level,
    c.name as category_name,
    sc.name as subcategory_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN subcategories sc ON p.subcategory_id = sc.id
WHERE p.stock_quantity <= p.min_stock_level
AND p.is_active = TRUE;

CREATE VIEW daily_sales_summary AS
SELECT 
    DATE(created_at) as sale_date,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_sales,
    AVG(total_amount) as average_order_value,
    order_type
FROM orders 
WHERE payment_status = 'paid'
GROUP BY DATE(created_at), order_type;

CREATE VIEW monthly_sales_summary AS
SELECT 
    YEAR(created_at) as sale_year,
    MONTH(created_at) as sale_month,
    COUNT(*) as total_orders,
    SUM(total_amount) as total_sales,
    AVG(total_amount) as average_order_value,
    order_type
FROM orders 
WHERE payment_status = 'paid'
GROUP BY YEAR(created_at), MONTH(created_at), order_type;