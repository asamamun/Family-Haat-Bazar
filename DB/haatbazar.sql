-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 01:42 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `haatbazar`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CalculateWeightedAverageCost` (IN `product_id_param` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessStockReceipt` (IN `product_id_param` INT, IN `quantity_param` INT, IN `cost_price_param` DECIMAL(10,2), IN `batch_number_param` VARCHAR(100), IN `purchase_order_item_id_param` INT, IN `user_id_param` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessStockSale` (IN `product_id_param` INT, IN `quantity_param` INT, IN `order_id_param` INT, IN `user_id_param` INT)   BEGIN
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
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateSellingPriceIfEnabled` (IN `product_id_param` INT, IN `new_cost_price` DECIMAL(10,2))   BEGIN
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
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(3, 'Garments', 'garments', 'asdf asdf asdf sdafd', '683fd68263935_1749014146.jpg', 1, 0, '2025-06-04 05:15:46', '2025-06-04 05:15:46'),
(4, 'Automobiles', 'automobiles', 'sadfsdfd fd', '683fd7a09ccce_1749014432.jpg', 1, 0, '2025-06-04 05:20:32', '2025-06-04 05:20:32'),
(5, 'Electronics', 'electroniocs', 'd fasdfsd sdfds fdsf df', '683fe16c12acf_1749016940.jpg', 1, 0, '2025-06-04 06:02:20', '2025-06-04 06:02:20'),
(6, 'kids', 'kids item', 'sdf sdf sdf d f', '683fe25a77b34_1749017178.jpg', 1, 0, '2025-06-04 06:06:18', '2025-06-04 06:06:18');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('fixed','percentage') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `valid_from` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valid_until` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usage`
--

CREATE TABLE `coupon_usage` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('billing','shipping') DEFAULT 'shipping',
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `company` varchar(100) DEFAULT NULL,
  `address_line_1` varchar(200) NOT NULL,
  `address_line_2` varchar(200) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'Bangladesh',
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `daily_sales_summary`
-- (See below for the actual view)
--
CREATE TABLE `daily_sales_summary` (
`sale_date` date
,`total_orders` bigint(21)
,`total_sales` decimal(32,2)
,`average_order_value` decimal(14,6)
,`order_type` enum('online','pos')
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `low_stock_products`
-- (See below for the actual view)
--
CREATE TABLE `low_stock_products` (
`id` int(11)
,`name` varchar(200)
,`sku` varchar(100)
,`stock_quantity` int(11)
,`min_stock_level` int(11)
,`category_name` varchar(100)
,`subcategory_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_sales_summary`
-- (See below for the actual view)
--
CREATE TABLE `monthly_sales_summary` (
`sale_year` int(4)
,`sale_month` int(2)
,`total_orders` bigint(21)
,`total_sales` decimal(32,2)
,`average_order_value` decimal(14,6)
,`order_type` enum('online','pos')
);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_type` enum('online','pos') NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_method` enum('bkash','nogod','cash') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_id` int(11) DEFAULT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'BDT',
  `notes` text DEFAULT NULL,
  `billing_first_name` varchar(50) DEFAULT NULL,
  `billing_last_name` varchar(50) DEFAULT NULL,
  `billing_company` varchar(100) DEFAULT NULL,
  `billing_address_line_1` varchar(200) DEFAULT NULL,
  `billing_address_line_2` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_postal_code` varchar(20) DEFAULT NULL,
  `billing_country` varchar(100) DEFAULT NULL,
  `billing_phone` varchar(20) DEFAULT NULL,
  `shipping_first_name` varchar(50) DEFAULT NULL,
  `shipping_last_name` varchar(50) DEFAULT NULL,
  `shipping_company` varchar(100) DEFAULT NULL,
  `shipping_address_line_1` varchar(200) DEFAULT NULL,
  `shipping_address_line_2` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_postal_code` varchar(20) DEFAULT NULL,
  `shipping_country` varchar(100) DEFAULT NULL,
  `shipping_phone` varchar(20) DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_sku` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `payment_method` enum('bkash','nogod','cash') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed','cancelled') DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `sku` varchar(100) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `markup_percentage` decimal(5,2) DEFAULT 0.00,
  `pricing_method` enum('manual','cost_plus','market_based') DEFAULT 'manual',
  `auto_update_price` tinyint(1) DEFAULT 0,
  `stock_quantity` int(11) DEFAULT 0,
  `min_stock_level` int(11) DEFAULT 5,
  `image` varchar(255) DEFAULT NULL,
  `is_hot_item` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_pricing_history`
--

CREATE TABLE `product_pricing_history` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `old_selling_price` decimal(10,2) DEFAULT NULL,
  `new_selling_price` decimal(10,2) NOT NULL,
  `old_cost_price` decimal(10,2) DEFAULT NULL,
  `new_cost_price` decimal(10,2) DEFAULT NULL,
  `reason` enum('cost_change','manual_update','promotion','markup_change') NOT NULL,
  `margin_percentage` decimal(5,2) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `effective_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_name` varchar(200) DEFAULT NULL,
  `supplier_contact` varchar(100) DEFAULT NULL,
  `status` enum('pending','received','partial','cancelled') DEFAULT 'pending',
  `total_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `ordered_date` date DEFAULT NULL,
  `received_date` date DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `purchase_order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_ordered` int(11) NOT NULL,
  `quantity_received` int(11) DEFAULT 0,
  `cost_price` decimal(10,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_cache`
--

CREATE TABLE `report_cache` (
  `id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `report_key` varchar(100) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key_name`, `value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'store_name', 'My Store', 'Store name', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(2, 'currency', 'BDT', 'Default currency', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(3, 'tax_rate', '0.00', 'Default tax rate percentage', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(4, 'low_stock_threshold', '5', 'Default low stock threshold', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(5, 'bkash_merchant_number', '', 'bKash merchant number', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(6, 'nogod_merchant_id', '', 'Nogod merchant ID', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(7, 'inventory_method', 'FIFO', 'Inventory costing method: FIFO, LIFO, or WEIGHTED_AVERAGE', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(8, 'auto_price_update', '0', 'Automatically update selling prices when cost changes (0=No, 1=Yes)', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(9, 'default_markup_percentage', '20.00', 'Default markup percentage for cost-plus pricing', '2025-05-25 06:24:17', '2025-05-25 06:24:17'),
(10, 'price_update_threshold', '5.00', 'Minimum cost change percentage to trigger price update', '2025-05-25 06:24:17', '2025-05-25 06:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `stock_batches`
--

CREATE TABLE `stock_batches` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_number` varchar(100) DEFAULT NULL,
  `purchase_order_item_id` int(11) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `quantity_available` int(11) NOT NULL,
  `quantity_sold` int(11) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `received_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `batch_id` int(11) DEFAULT NULL,
  `movement_type` enum('IN','OUT','ADJUSTMENT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(12,2) DEFAULT NULL,
  `reference_type` enum('PURCHASE','SALE','ADJUSTMENT','RETURN','TRANSFER') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `description`, `image`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 4, 'Bike', 'bike', 'some description', '683fea9013d87_1749019280.jpg', 1, 2, '2025-06-04 06:41:20', '2025-06-04 06:41:20'),
(2, 5, 'iPhone', 'iphone', 'sdf sdkfjhds fksdfh dff df ', '683feaca6c372_1749019338.png', 1, 3, '2025-06-04 06:42:18', '2025-06-04 06:42:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','cashier','customer') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `is_active`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'abu', 'mamun', 'abu@gmail.com', '1234567891', '$2y$10$8ftv4lKvYyTwtTyVDy.yn.PvZEa2hgtSkkqi6z20p8.mDAbULmZz2', 'admin', 1, NULL, NULL, '2025-05-29 06:31:31', '2025-05-29 06:31:31'),
(3, 'test', 'test', 'test@gmail.com', '1234234324532545', '$2y$10$DIeJxdW5gPryY1XKMWJXo.L9wBQs1ZczqJi0jGtQEzicDR21qxdzm', 'customer', 1, NULL, NULL, '2025-05-29 06:35:58', '2025-05-29 06:35:58'),
(4, 'test2', 'test2', 'test2@gmail.com', '9834759485745', '$2y$10$j//ku.FH91gJYlE40WPD../7TMM0VQ/VdvhRqbmjx2I3or6Y4sUcC', 'customer', 1, NULL, NULL, '2025-05-29 06:38:33', '2025-05-29 06:38:33'),
(5, 'Ishaq Ahmed', 'Shojib', 'ishaqhossain98@gmail.com', '01783629582', '$2y$10$08ERC8FLVEdmzQrClgxXau0MK0fopyqqNGuUUqk.38RzpKTGREndi', 'customer', 1, NULL, NULL, '2025-05-29 06:45:22', '2025-05-29 06:45:22'),
(7, 'admin', 'admin', 'admin@gmail.com', NULL, '$2y$10$kyKkl7SXSsXE1IEzdbapGOS90AgPdzzIGzx/veB/lXOlZTz0gpUmC', 'admin', 1, NULL, NULL, '2025-06-04 04:59:25', '2025-06-04 04:59:35');

-- --------------------------------------------------------

--
-- Structure for view `daily_sales_summary`
--
DROP TABLE IF EXISTS `daily_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_sales_summary`  AS SELECT cast(`orders`.`created_at` as date) AS `sale_date`, count(0) AS `total_orders`, sum(`orders`.`total_amount`) AS `total_sales`, avg(`orders`.`total_amount`) AS `average_order_value`, `orders`.`order_type` AS `order_type` FROM `orders` WHERE `orders`.`payment_status` = 'paid' GROUP BY cast(`orders`.`created_at` as date), `orders`.`order_type` ;

-- --------------------------------------------------------

--
-- Structure for view `low_stock_products`
--
DROP TABLE IF EXISTS `low_stock_products`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `low_stock_products`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, `p`.`sku` AS `sku`, `p`.`stock_quantity` AS `stock_quantity`, `p`.`min_stock_level` AS `min_stock_level`, `c`.`name` AS `category_name`, `sc`.`name` AS `subcategory_name` FROM ((`products` `p` left join `categories` `c` on(`p`.`category_id` = `c`.`id`)) left join `subcategories` `sc` on(`p`.`subcategory_id` = `sc`.`id`)) WHERE `p`.`stock_quantity` <= `p`.`min_stock_level` AND `p`.`is_active` = 1 ;

-- --------------------------------------------------------

--
-- Structure for view `monthly_sales_summary`
--
DROP TABLE IF EXISTS `monthly_sales_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_sales_summary`  AS SELECT year(`orders`.`created_at`) AS `sale_year`, month(`orders`.`created_at`) AS `sale_month`, count(0) AS `total_orders`, sum(`orders`.`total_amount`) AS `total_sales`, avg(`orders`.`total_amount`) AS `average_order_value`, `orders`.`order_type` AS `order_type` FROM `orders` WHERE `orders`.`payment_status` = 'paid' GROUP BY year(`orders`.`created_at`), month(`orders`.`created_at`), `orders`.`order_type` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_validity` (`valid_from`,`valid_until`);

--
-- Indexes for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_coupon_order` (`coupon_id`,`order_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_user_orders` (`user_id`),
  ADD KEY `idx_order_date` (`created_at`),
  ADD KEY `idx_order_type` (`order_type`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_orders_date_type` (`created_at`,`order_type`),
  ADD KEY `idx_orders_payment` (`payment_method`,`payment_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_order_items` (`order_id`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `idx_transaction_id` (`transaction_id`),
  ADD KEY `idx_payment_method` (`payment_method`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_hot_items` (`is_hot_item`),
  ADD KEY `idx_stock` (`stock_quantity`),
  ADD KEY `idx_pricing` (`pricing_method`,`auto_update_price`),
  ADD KEY `idx_products_category` (`category_id`,`is_active`),
  ADD KEY `idx_products_subcategory` (`subcategory_id`,`is_active`),
  ADD KEY `idx_stock_low` (`stock_quantity`,`min_stock_level`);

--
-- Indexes for table `product_pricing_history`
--
ALTER TABLE `product_pricing_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_pricing` (`product_id`,`effective_date`),
  ADD KEY `idx_changed_by` (`changed_by`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `idx_po_number` (`po_number`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_id` (`purchase_order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `report_cache`
--
ALTER TABLE `report_cache`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_report` (`report_type`,`report_key`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `stock_batches`
--
ALTER TABLE `stock_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_item_id` (`purchase_order_item_id`),
  ADD KEY `idx_product_batch` (`product_id`,`is_active`),
  ADD KEY `idx_fifo_order` (`product_id`,`received_date`,`id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_movement` (`product_id`,`created_at`),
  ADD KEY `idx_batch_id` (`batch_id`),
  ADD KEY `idx_created_by` (`created_by`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_pricing_history`
--
ALTER TABLE `product_pricing_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_cache`
--
ALTER TABLE `report_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `stock_batches`
--
ALTER TABLE `stock_batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_usage`
--
ALTER TABLE `coupon_usage`
  ADD CONSTRAINT `coupon_usage_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `coupon_usage_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `coupon_usage_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD CONSTRAINT `customer_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`);

--
-- Constraints for table `product_pricing_history`
--
ALTER TABLE `product_pricing_history`
  ADD CONSTRAINT `product_pricing_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `purchase_order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `stock_batches`
--
ALTER TABLE `stock_batches`
  ADD CONSTRAINT `stock_batches_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `stock_batches_ibfk_2` FOREIGN KEY (`purchase_order_item_id`) REFERENCES `purchase_order_items` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
