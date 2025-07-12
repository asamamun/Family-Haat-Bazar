-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 12, 2025 at 08:52 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `logo` blob DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `logo`, `created_at`) VALUES
(1, 'ACI', 0x363835613230323332363463375f313735303733363933312e6a7067, '2025-06-23 23:48:34'),
(2, 'Akij', 0x363835613230343062383031375f313735303733363936302e6a7067, '2025-06-23 23:49:06'),
(3, 'mgi', 0x363835613230356563303865305f313735303733363939302e6a7067, '2025-06-23 23:49:20'),
(4, 'Pran', 0x363835613230373466343233315f313735303733373031322e6a7067, '2025-06-23 23:49:50'),
(5, 'Pusti', 0x363835613230383764626639625f313735303733373033312e6a7067, '2025-06-23 23:50:13'),
(6, 'Walton', '', '2025-07-01 23:41:12');

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
(6, 'kids', 'kids item', 'sdf sdf sdf d f', '683fe25a77b34_1749017178.jpg', 1, 0, '2025-06-04 06:06:18', '2025-06-04 06:06:18'),
(7, 'Cattle', 'cattle', 'asdfdsf', '684d1e18a671f_1749884440.png', 1, 0, '2025-06-14 07:00:40', '2025-06-14 07:00:40'),
(8, 'sdfgdfsgdfg', 'gfdgsdfgsfd', 'gdfsgvsdfgfdg', '684d256a6fc3a_1749886314.jpg', 1, 0, '2025-06-14 07:31:54', '2025-06-14 07:31:54'),
(9, 'panio', 'panio', 'fd sdfsdfsadfsdf sdfsda f', '685a490fdccb8_1750747407.webp', 1, 0, '2025-06-24 06:43:27', '2025-06-24 06:43:27'),
(10, 'Home and Kitchen', 'home-kitchen', 'home-kitchen', '', 1, 0, '2025-07-02 03:19:02', '2025-07-02 03:19:02');

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
  `order_number` varchar(30) DEFAULT NULL,
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

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `order_type`, `status`, `payment_status`, `payment_method`, `transaction_id`, `subtotal`, `discount_amount`, `coupon_id`, `tax_amount`, `shipping_amount`, `total_amount`, `currency`, `notes`, `billing_first_name`, `billing_last_name`, `billing_company`, `billing_address_line_1`, `billing_address_line_2`, `billing_city`, `billing_state`, `billing_postal_code`, `billing_country`, `billing_phone`, `shipping_first_name`, `shipping_last_name`, `shipping_company`, `shipping_address_line_1`, `shipping_address_line_2`, `shipping_city`, `shipping_state`, `shipping_postal_code`, `shipping_country`, `shipping_phone`, `processed_by`, `processed_at`, `created_at`, `updated_at`) VALUES
(1, 'ORD-1751351897-7559', NULL, 'online', 'pending', 'pending', 'bkash', '123123123', 361.97, 0.07, NULL, 18.10, 0.00, 380.00, 'BDT', 'test', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', NULL, NULL, '2025-07-01 06:38:17', '2025-07-01 06:38:17'),
(2, 'ORD-1751352003-3662', NULL, 'online', 'pending', 'pending', 'bkash', '123123123', 361.97, 0.07, NULL, 18.10, 0.00, 380.00, 'BDT', 'test', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', NULL, NULL, '2025-07-01 06:40:03', '2025-07-01 06:40:03'),
(3, 'ORD-1751352032-6555', NULL, 'online', 'pending', 'pending', 'bkash', '123123123', 361.97, 0.07, NULL, 18.10, 0.00, 380.00, 'BDT', 'test', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', NULL, NULL, '2025-07-01 06:40:32', '2025-07-01 06:40:32'),
(4, 'ORD-1751352109-3696', NULL, 'online', 'pending', 'pending', 'bkash', '123123123', 361.97, 0.07, NULL, 18.10, 0.00, 380.00, 'BDT', 'test', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'H', '123123123', NULL, NULL, '2025-07-01 06:41:49', '2025-07-01 06:41:49'),
(5, 'ORD-1751352202-6597', NULL, 'online', 'pending', 'pending', 'bkash', 'sadfdsf', 361.97, 0.07, NULL, 18.10, 0.00, 380.00, 'BDT', 'sdfasdf', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'bangladesh', '123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'bangladesh', '123123', NULL, NULL, '2025-07-01 06:43:22', '2025-07-01 06:43:22'),
(6, 'ORD-1751352476-7252', NULL, 'online', 'pending', 'pending', 'bkash', 'sadfdsf', 127.92, 0.07, NULL, 6.40, 0.00, 134.25, 'BDT', 'sdfasdf', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'bangladesh', '123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'bangladesh', '123123', NULL, NULL, '2025-07-01 06:47:56', '2025-07-01 06:47:56'),
(7, 'ORD-1751352519-1216', NULL, 'online', 'pending', 'pending', 'bkash', 'sadfdsf', 127.92, 0.07, NULL, 6.40, 0.00, 134.25, 'BDT', 'sdfasdf', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'bangladesh', '123123', 'A', 'B', 'C', 'D', 'E', 'F', 'G', '1216', 'bangladesh', '123123', NULL, NULL, '2025-07-01 06:48:39', '2025-07-01 06:48:39'),
(8, 'ORD-1751352687-2426', NULL, 'online', 'pending', 'pending', 'bkash', 'hhh', 127.92, 4.32, NULL, 6.40, 0.00, 130.00, 'BDT', 'hhh', 'hh', 'hh', 'hh', 'hh', 'hh', 'hh', 'hh', '1234', 'hh', '123', 'hh', 'hh', 'hh', 'hh', 'hh', 'hh', 'hh', '1234', 'hh', '123', NULL, NULL, '2025-07-01 06:51:27', '2025-07-01 06:51:27'),
(9, 'POS-1751352758-8916', 7, 'pos', 'delivered', 'paid', 'cash', NULL, 361.97, 0.00, NULL, 28.96, 0.00, 390.93, 'BDT', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', 7, '2025-07-01 06:52:38', '2025-07-01 06:52:38', '2025-07-01 06:52:38'),
(10, 'POS-1751352994-4326', 7, 'pos', 'delivered', 'paid', 'cash', NULL, 361.97, 0.00, NULL, 28.96, 0.00, 390.93, 'BDT', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', 7, '2025-07-01 06:56:34', '2025-07-01 06:56:34', '2025-07-01 06:56:34'),
(11, 'POS-1751353171-5902', 7, 'pos', 'delivered', 'paid', 'cash', NULL, 395.97, 0.00, NULL, 31.68, 0.00, 427.65, 'BDT', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', 7, '2025-07-01 06:59:31', '2025-07-01 06:59:31', '2025-07-01 06:59:31'),
(12, 'ORD-1751428701-5358', NULL, 'online', 'pending', 'pending', 'cash', NULL, 15.99, 0.00, NULL, 0.80, 0.00, 16.79, 'BDT', '', 'abir', 'khan', '', 'mirpur', '', 'dhaka', 'dhaka', '1216', 'Bangladesh', '012321232', 'abir', 'khan', '', 'mirpur', '', 'dhaka', 'dhaka', '1216', 'bangladesh', '01232123', NULL, NULL, '2025-07-01 23:58:21', '2025-07-01 23:58:21'),
(13, 'ORD-1751429333-5317', NULL, 'online', 'pending', 'pending', 'cash', NULL, 50.00, 0.00, NULL, 2.50, 0.00, 52.50, 'BDT', 'dsdgfdgh', 'a', 'b', 'c', 'as', 'ac', 'z', 'x', '12', 'ewdf', '1335458555', 'a', 'b', 'c', 'as', 'ac', 'z', 'x', '12', 'ewdf', '1335458555', NULL, NULL, '2025-07-02 00:08:53', '2025-07-02 00:08:53'),
(14, 'POS-1751429584-3593', 7, 'pos', 'delivered', 'paid', 'cash', NULL, 200.00, 0.00, NULL, 16.00, 0.00, 216.00, 'BDT', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', 7, '2025-07-02 00:13:04', '2025-07-02 00:13:04', '2025-07-02 04:13:04'),
(15, 'ORD-1751429853-9739', NULL, 'online', 'pending', 'pending', 'cash', NULL, 25.00, 0.00, NULL, 1.25, 0.00, 26.25, 'BDT', '', 'a', 'b', 'c', 'as', 'ac', 'z', 'wrgr', '12', 'Bangladesh', '01335458555', 'a', 'b', 'c', 'as', 'ac', 'z', 'wrgr', '12', 'Bangladesh', '01335458555', NULL, NULL, '2025-07-02 00:17:33', '2025-07-02 00:17:33'),
(16, 'ORD-1751430476-2891', NULL, 'online', 'pending', 'pending', 'cash', NULL, 15.99, 0.00, NULL, 0.80, 0.00, 16.79, 'BDT', 'Bring safely', 'Sobuj', 'Hasan', 'IsDB', 'kafrul', '', 'Dhaka', 'Dhaka', '1216', 'Bangladesh', '01401885646', 'Sobuj', 'Hasan', 'IsDB', 'kafrul', '', 'Dhaka', 'Dhaka', '1216', 'Bangladesh', '01401885646', NULL, NULL, '2025-07-02 00:27:56', '2025-07-02 00:27:56'),
(17, 'ORD-1751430855-4626', NULL, 'online', 'pending', 'pending', 'cash', NULL, 120.00, 0.00, NULL, 6.00, 0.00, 126.00, 'BDT', '', 'hasan', 'mahmud', 'isdb', 'mirpur', '', 'dhaka', 'dhaka', '1216', 'bangladesh', '01401775566', 'hasan', 'mahmud', 'isdb', 'mirpur', '', 'dhaka', 'dhaka', '1216', 'bangladesh', '01401775566', NULL, NULL, '2025-07-02 00:34:15', '2025-07-02 00:34:15'),
(18, 'ORD-1751433945-2357', NULL, 'online', 'pending', 'pending', 'cash', NULL, 400.00, 0.00, NULL, 20.00, 0.00, 420.00, 'BDT', 'r5ft', 'a', 'b', 'c', 'as', 'ac', 'z', 'u', '12', 'Bangladesh', '01335458555', 'a', 'b', 'c', 'as', 'ac', 'z', 'u', '12', 'Bangladesh', '01335458555', NULL, NULL, '2025-07-02 01:25:45', '2025-07-02 01:25:45'),
(19, 'POS-1751434047-2280', 7, 'pos', 'delivered', 'paid', 'cash', NULL, 18000.00, 0.00, NULL, 1440.00, 0.00, 19440.00, 'BDT', '', '', '', NULL, '', NULL, '', NULL, '', '', '', '', '', NULL, '', NULL, '', NULL, '', '', '', 7, '2025-07-02 01:27:27', '2025-07-02 01:27:27', '2025-07-02 05:27:27'),
(20, 'ORD-1751437246-8160', NULL, 'online', 'pending', 'pending', 'cash', NULL, 419.99, 0.00, NULL, 21.00, 0.00, 440.99, 'BDT', '', 'a', 'b', 'c', 'as', 'ac', 'z', 'g', '12', 'Bangladesh', '01335458555', 'a', 'b', 'c', 'as', 'ac', 'z', 'g', '12', 'Bangladesh', '01335458555', NULL, NULL, '2025-07-02 02:20:46', '2025-07-02 02:20:46'),
(21, 'ORD-1752302433-4646', NULL, 'online', 'pending', 'pending', 'cash', NULL, 129.99, 0.00, NULL, 6.50, 0.00, 136.49, 'BDT', 'fg', 'a', 'b', 'c', 'as', 'ac', 'z', 'g', '12', 'Bangladesh', '01335458555', 'a', 'b', 'c', 'as', 'ac', 'z', 'g', '12', 'Bangladesh', '01335458555', NULL, NULL, '2025-07-12 02:40:33', '2025-07-12 02:40:33');

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

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `product_sku`, `quantity`, `unit_price`, `total_price`, `created_at`) VALUES
(1, 1, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:38:17'),
(2, 1, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:38:17'),
(3, 1, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:38:17'),
(4, 2, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:40:03'),
(5, 2, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:40:03'),
(6, 2, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:40:03'),
(7, 3, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:40:32'),
(8, 3, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:40:32'),
(9, 3, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:40:32'),
(10, 4, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:41:49'),
(11, 4, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:41:49'),
(12, 4, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:41:49'),
(13, 5, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:43:22'),
(14, 5, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:43:22'),
(15, 5, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:43:22'),
(16, 6, 1, 'Cotton T-Shirt', 'TSH-001', 8, 15.99, 127.92, '2025-07-01 06:47:56'),
(17, 7, 1, 'Cotton T-Shirt', 'TSH-001', 8, 15.99, 127.92, '2025-07-01 06:48:39'),
(18, 8, 1, 'Cotton T-Shirt', 'TSH-001', 8, 15.99, 127.92, '2025-07-01 06:51:27'),
(19, 9, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:52:38'),
(20, 9, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:52:38'),
(21, 9, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:52:38'),
(22, 10, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:56:34'),
(23, 10, 2, 'Denim Jacket', 'JKT-002', 1, 45.99, 45.99, '2025-07-01 06:56:34'),
(24, 10, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:56:34'),
(25, 11, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 06:59:31'),
(26, 11, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-01 06:59:31'),
(27, 11, 6, 'Wireless Earbuds', 'EAR-001', 1, 79.99, 79.99, '2025-07-01 06:59:31'),
(28, 12, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-01 23:58:21'),
(29, 13, 31, 'Mango Juice', '135', 1, 25.00, 25.00, '2025-07-02 00:08:53'),
(30, 13, 32, 'Berry Juice', '452', 1, 25.00, 25.00, '2025-07-02 00:08:53'),
(31, 14, 28, 'detergent powder', '258', 1, 200.00, 200.00, '2025-07-02 00:13:04'),
(32, 15, 31, 'Mango Juice', '135', 1, 25.00, 25.00, '2025-07-02 00:17:33'),
(33, 16, 1, 'Cotton T-Shirt', 'TSH-001', 1, 15.99, 15.99, '2025-07-02 00:27:56'),
(34, 17, 22, 'Rice', 'sk123454', 1, 120.00, 120.00, '2025-07-02 00:34:15'),
(35, 18, 26, 'Baasmati Rice', '1254', 1, 150.00, 150.00, '2025-07-02 01:25:45'),
(36, 18, 27, 'aci salt', '456', 1, 50.00, 50.00, '2025-07-02 01:25:45'),
(37, 18, 28, 'detergent powder', '258', 1, 200.00, 200.00, '2025-07-02 01:25:45'),
(38, 19, 47, 'monitor', 'm123', 1, 18000.00, 18000.00, '2025-07-02 01:27:27'),
(39, 20, 22, 'Rice', 'sk123454', 1, 120.00, 120.00, '2025-07-02 02:20:46'),
(40, 20, 3, 'Electric Scooter', 'SCO-001', 1, 299.99, 299.99, '2025-07-02 02:20:46'),
(41, 21, 7, 'Baby Stroller', 'STR-001', 1, 129.99, 129.99, '2025-07-12 02:40:33');

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

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `order_id`, `transaction_id`, `payment_method`, `amount`, `status`, `gateway_response`, `processed_at`, `created_at`) VALUES
(1, 1, '123123123', 'bkash', 380.00, 'pending', NULL, NULL, '2025-07-01 06:38:17'),
(2, 2, '123123123', 'bkash', 380.00, 'pending', NULL, NULL, '2025-07-01 06:40:03'),
(3, 3, '123123123', 'bkash', 380.00, 'pending', NULL, NULL, '2025-07-01 06:40:32'),
(4, 4, '123123123', 'bkash', 380.00, 'pending', NULL, NULL, '2025-07-01 06:41:49'),
(5, 5, 'sadfdsf', 'bkash', 380.00, 'pending', NULL, NULL, '2025-07-01 06:43:22'),
(6, 6, 'sadfdsf', 'bkash', 134.25, 'pending', NULL, NULL, '2025-07-01 06:47:56'),
(7, 7, 'sadfdsf', 'bkash', 134.25, 'pending', NULL, NULL, '2025-07-01 06:48:39'),
(8, 8, 'hhh', 'bkash', 130.00, 'pending', NULL, NULL, '2025-07-01 06:51:27'),
(9, 9, 'POS-1751352758-8916', 'cash', 390.93, 'success', NULL, '2025-07-01 06:52:38', '2025-07-01 06:52:38'),
(10, 10, 'POS-1751352994-4326', 'cash', 390.93, 'success', NULL, '2025-07-01 06:56:34', '2025-07-01 06:56:34'),
(11, 11, 'POS-1751353171-5902', 'cash', 427.65, 'success', NULL, '2025-07-01 06:59:31', '2025-07-01 06:59:31'),
(12, 12, 'ORD-1751428701-5358', 'cash', 16.79, 'pending', NULL, NULL, '2025-07-01 23:58:21'),
(13, 13, 'ORD-1751429333-5317', 'cash', 52.50, 'pending', NULL, NULL, '2025-07-02 00:08:53'),
(14, 14, 'POS-1751429584-3593', 'cash', 216.00, 'success', NULL, '2025-07-02 00:13:04', '2025-07-02 00:13:04'),
(15, 15, 'ORD-1751429853-9739', 'cash', 26.25, 'pending', NULL, NULL, '2025-07-02 00:17:33'),
(16, 16, 'ORD-1751430476-2891', 'cash', 16.79, 'pending', NULL, NULL, '2025-07-02 00:27:56'),
(17, 17, 'ORD-1751430855-4626', 'cash', 126.00, 'pending', NULL, NULL, '2025-07-02 00:34:15'),
(18, 18, 'ORD-1751433945-2357', 'cash', 420.00, 'pending', NULL, NULL, '2025-07-02 01:25:45'),
(19, 19, 'POS-1751434047-2280', 'cash', 19440.00, 'success', NULL, '2025-07-02 01:27:27', '2025-07-02 01:27:27'),
(20, 20, 'ORD-1751437246-8160', 'cash', 440.99, 'pending', NULL, NULL, '2025-07-02 02:20:46'),
(21, 21, 'ORD-1752302433-4646', 'cash', 136.49, 'pending', NULL, NULL, '2025-07-12 02:40:33');

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `brand` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `slug`, `description`, `short_description`, `sku`, `barcode`, `selling_price`, `cost_price`, `markup_percentage`, `pricing_method`, `auto_update_price`, `stock_quantity`, `min_stock_level`, `image`, `is_hot_item`, `is_active`, `weight`, `dimensions`, `created_at`, `updated_at`, `brand`) VALUES
(1, 3, NULL, 'Cotton T-Shirt', 'cotton-t-shirt', 'Comfortable cotton T-shirt.', 'Soft and breathable cotton T-shirt.', 'TSH-001', '1234567890123', 15.99, 8.00, 99.88, 'manual', 0, 50, 5, 'tshirt1.jpg', 1, 1, 0.20, '30x20x1 cm', '2025-06-22 06:00:00', '2025-07-01 06:52:38', 2),
(2, 3, NULL, 'Denim Jacket', 'denim-jacket', 'Stylish denim jacket.', 'Classic blue denim jacket.', 'JKT-002', '1234567890124', 45.99, 30.00, 53.30, 'cost_plus', 0, 30, 5, 'denimjacket.jpg', 0, 1, 0.50, '40x30x2 cm', '2025-06-22 06:00:00', '2025-07-01 06:52:38', 1),
(3, 4, 6, 'Electric Scooter', 'electric-scooter', 'Eco-friendly electric scooter.', 'Fast and foldable scooter.', 'SCO-001', '1234567890125', 299.99, 200.00, 50.00, 'manual', 0, 20, 3, 'escooter.jpg', 1, 1, 10.00, '100x50x30 cm', '2025-06-22 06:00:00', '2025-07-01 06:52:38', 1),
(4, 4, 7, 'Toy Motorcycle', 'toy-motorcycle', 'Realistic toy motorcycle.', 'Battery-powered ride-on toy.', 'TOY-001', '1234567890126', 89.99, 50.00, 80.00, 'cost_plus', 0, 15, 2, 'toymotor.jpg', 0, 1, 2.00, '50x20x15 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(5, 5, 8, 'Smartphone XYZ', 'smartphone-xyz', 'Latest smartphone model.', 'High-performance smartphone.', 'PHN-001', '1234567890127', 699.99, 500.00, 40.00, 'market_based', 1, 25, 5, 'smartphone.jpg', 1, 1, 0.15, '15x7x0.8 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(6, 5, 8, 'Wireless Earbuds', 'wireless-earbuds', 'True wireless earbuds.', 'Crystal-clear audio earbuds.', 'EAR-001', '1234567890128', 79.99, 40.00, 99.98, 'manual', 0, 40, 5, 'earbuds.jpg', 0, 1, 0.05, '5x5x2 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(7, 6, NULL, 'Baby Stroller', 'baby-stroller', 'Lightweight baby stroller.', 'Foldable and durable stroller.', 'STR-001', '1234567890129', 129.99, 80.00, 62.49, 'cost_plus', 0, 10, 2, 'stroller.jpg', 1, 1, 5.00, '80x50x100 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(8, 6, NULL, 'Plush Teddy Bear', 'plush-teddy-bear', 'Soft plush teddy bear.', 'Cuddly toy for kids.', 'TOY-002', '1234567890130', 19.99, 10.00, 99.90, 'manual', 0, 60, 10, 'teddybear.jpg', 0, 1, 0.30, '20x15x10 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(9, 7, 12, 'Holstein Cow', 'holstein-cow', 'Healthy dairy cow.', 'High-yield milk cow.', 'COW-001', '1234567890131', 1500.00, 1200.00, 25.00, 'market_based', 0, 5, 1, 'holstein.jpg', 1, 1, 500.00, NULL, '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(10, 7, 12, 'Jersey Cow', 'jersey-cow', 'Premium jersey cow.', 'Efficient dairy cow.', 'COW-002', '1234567890132', 1400.00, 1100.00, 27.27, 'manual', 0, 3, 1, 'jersey.jpg', 0, 1, 450.00, NULL, '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(11, 3, NULL, 'Silk Saree', 'silk-saree', 'Elegant silk saree.', 'Traditional handwoven saree.', 'SAR-001', '1234567890133', 99.99, 60.00, 66.65, 'cost_plus', 0, 20, 3, 'saree.jpg', 1, 1, 0.40, '150x50x0.5 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(12, 4, 6, 'Car Cleaning Kit', 'car-cleaning-kit', 'Complete car cleaning kit.', 'All-in-one car care.', 'CLK-001', '1234567890134', 29.99, 15.00, 99.93, 'manual', 0, 35, 5, 'cleaningkit.jpg', 0, 1, 1.00, '30x20x10 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(13, 5, 8, 'LED TV 55\"', 'led-tv-55', '55-inch 4K LED TV.', 'Immersive viewing experience.', 'TV-001', '1234567890135', 499.99, 350.00, 42.85, 'market_based', 1, 15, 3, 'ledtv.jpg', 1, 1, 15.00, '120x80x10 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(14, 6, NULL, 'Wooden Puzzle', 'wooden-puzzle', 'Educational wooden puzzle.', 'Fun learning toy.', 'PUZ-001', '1234567890136', 14.99, 8.00, 87.38, 'cost_plus', 0, 50, 8, 'puzzle.jpg', 0, 1, 0.25, '20x20x1 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(15, 7, 12, 'Angus Bull', 'angus-bull', 'Strong Angus bull.', 'Premium beef cattle.', 'BUL-001', '1234567890137', 2000.00, 1600.00, 25.00, 'manual', 0, 2, 1, 'angus.jpg', 1, 1, 600.00, NULL, '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(16, 3, NULL, 'Leather Belt', 'leather-belt', 'Genuine leather belt.', 'Durable and stylish belt.', 'BLT-001', '1234567890138', 24.99, 12.00, 99.92, 'cost_plus', 0, 45, 5, 'belt.jpg', 0, 1, 0.10, '100x5x0.5 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(17, 4, 7, 'Remote Control Car', 'remote-control-car', 'Fast RC car.', 'Exciting remote control toy.', 'RCC-001', '1234567890139', 49.99, 25.00, 99.96, 'manual', 0, 25, 4, 'rccar.jpg', 1, 1, 1.50, '30x15x10 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(18, 5, 8, 'Laptop 15\"', 'laptop-15', 'High-performance 15-inch laptop.', 'Sleek and powerful laptop.', 'LAP-001', '1234567890140', 999.99, 700.00, 42.86, 'market_based', 1, 10, 2, 'laptop.jpg', 1, 1, 2.00, '35x25x2 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(19, 6, NULL, 'Diaper Bag', 'diaper-bag', 'Spacious diaper bag.', 'Multi-pocket baby bag.', 'BAG-001', '1234567890141', 39.99, 20.00, 99.95, 'cost_plus', 0, 30, 5, 'diaperbag.jpg', 0, 1, 0.80, '40x30x20 cm', '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(20, 7, 12, 'Goat', 'goat', 'Healthy dairy goat.', 'High-quality milk goat.', 'GOT-001', '1234567890142', 300.00, 200.00, 50.00, 'manual', 0, 8, 2, 'goat.jpg', 0, 1, 30.00, NULL, '2025-06-22 06:00:00', '2025-06-24 06:53:29', 1),
(21, 5, 16, 'IDB AC 2 ton', 'idb-ac-2-ton', 'df sdfsda fsdf safdd sdf f sdf sdfds ', ' sdfsd fdsaf sd', 'idbac2ton', '43543545fdgdfgfg', 45000.00, 42000.00, 0.00, 'manual', 0, 55, 5, '685b87960d02d_1750828950.png', 1, 1, 55.00, '55', '2025-06-25 05:22:30', '2025-06-25 05:22:30', 2),
(22, 10, 18, 'Rice', 'rice', 'rice', 'rice', 'sk123454', '34534545', 120.00, 110.00, 5.00, 'manual', 1, 120, 10, '6864a5a852527_1751426472.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:21:14', '2025-07-02 03:21:14', 1),
(23, 10, 18, 'Brown Rice', 'brown-rice', 'brown-rice', 'brown-rice', '435retretrt', '5465465654656', 120.00, 110.00, 5.00, 'manual', 1, 120, 10, '6864a611cd45a_1751426577.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:22:57', '2025-07-02 03:22:57', 2),
(24, 10, 18, 'Mustard oil', 'mustard-oil', 'mustard-oil', 'mustard-oil', 'sku-123', '9876543', 120.00, 110.00, 5.00, 'manual', 1, 120, 10, '6864a66ab3e69_1751426666.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:24:26', '2025-07-02 03:24:26', 1),
(26, 10, 18, 'Baasmati Rice', 'baasmati-rice', 'baasmati-rice', 'baasmati-rice', '1254', '3454545', 150.00, 120.00, 5.00, 'manual', 1, 500, 12, '6864a76580b2a_1751426917.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:28:37', '2025-07-02 03:28:37', 1),
(27, 10, 18, 'aci salt', 'aci-salt', 'aci salt jar', 'aci salt jar', '456', '654321', 50.00, 45.00, 5.00, 'manual', 1, 124, 12, '6864a7b492eed_1751426996.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:29:56', '2025-07-02 03:29:56', 1),
(28, 10, 17, 'detergent powder', 'detergent-powder', 'detergent-powder', 'detergent-powder', '258', '852', 200.00, 190.00, 5.00, 'manual', 1, 200, 20, '6864a814593ba_1751427092.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:31:32', '2025-07-02 03:31:32', 1),
(29, 10, 17, 'air freshnar', 'air-freshnar', 'air freshnar', 'air freshnar', '741', '147', 175.00, 155.00, 10.00, 'manual', 1, 20, 10, '6864a85b88a4b_1751427163.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:32:43', '2025-07-02 03:32:43', 1),
(30, 10, 17, 'air freshnar green', 'air-freshnar-green', 'air freshnar green', 'air freshnar green', '789', '987', 175.00, 155.00, 10.00, 'manual', 1, 120, 10, '6864a8d145c04_1751427281.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:34:41', '2025-07-02 03:34:41', 1),
(31, 10, 17, 'Mango Juice', 'mango-juice', 'Mango Juice', 'Mango Juice', '135', '531', 25.00, 20.00, 5.00, 'manual', 1, 50, 10, '6864a929e769d_1751427369.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:36:10', '2025-07-02 03:36:10', 4),
(32, 10, 17, 'Berry Juice', 'berry-juice', 'Berry Juice', 'Berry Juice', '452', '254', 25.00, 20.00, 5.00, 'manual', 1, 20, 10, '6864a969a0e9f_1751427433.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:37:13', '2025-07-02 03:37:13', 4),
(34, 10, 17, 'orange juice', 'orange-juice', 'orange juice', 'orange juice', 'sdafsdf', '3453', 25.00, 20.00, 5.00, 'manual', 1, 20, 10, '6864a9dbd14c2_1751427547.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:39:07', '2025-07-02 03:39:07', 4),
(35, 10, 17, 'Chanachur', 'chanachur', 'Chanachur', 'Chanachur', '9851', '1598', 35.00, 25.00, 10.00, 'manual', 1, 20, 10, '6864aae4f1ca4_1751427812.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:43:33', '2025-07-02 03:43:33', 1),
(36, 10, 17, 'Pusti Ata', 'pusti-ata', 'Pusti Ata', 'Pusti Ata', 'p123', '123', 150.00, 140.00, 5.00, 'manual', 1, 200, 10, '6864ab50d409b_1751427920.jpg', 1, 1, 2.00, '10*10', '2025-07-02 03:45:20', '2025-07-02 03:45:20', 5),
(38, 10, 17, 'Pusti Suji', 'pusti-suji', 'Pusti Suji', 'Pusti Suji', 's123', '132', 50.00, 45.00, 10.00, 'manual', 1, 200, 10, '6864aba665169_1751428006.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:46:46', '2025-07-02 03:46:46', 5),
(39, 10, 17, 'Pusti Lentil', 'pusti-lentil', 'Pusti Lentil', 'Pusti Lentil', 'l123', '3121', 110.00, 100.00, 5.00, 'manual', 1, 200, 10, '6864ac0ba36fc_1751428107.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:48:27', '2025-07-02 03:48:27', 5),
(40, 5, 19, 'Scooter', 'scooter', 'Scooter', 'Scooter', 's456', '4152', 5000.00, 4500.00, 5.00, 'manual', 1, 50, 10, '6864acc0e7a8e_1751428288.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:51:29', '2025-07-02 03:51:29', 6),
(41, 5, 19, 'Fridge', 'fridge', 'fridge', 'fridge', 'f123', 'fff123', 25000.00, 23000.00, 5.00, 'manual', 1, 20, 10, '6864ad10275f3_1751428368.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:52:48', '2025-07-02 03:52:48', 6),
(42, 5, 19, 'blender', 'blender', 'blender', 'blender', 'b123', 'b1b23', 2500.00, 2100.00, 5.00, 'manual', 1, 20, 5, '6864ad5ef198b_1751428446.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:54:07', '2025-07-02 03:54:07', 6),
(43, 5, 19, 'Laptop', 'laptop', 'laptop', 'laptop', 'lp1232', 'lp125', 35000.00, 30000.00, 10.00, 'manual', 1, 30, 10, '6864add2146e5_1751428562.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:56:02', '2025-07-02 03:56:02', 6),
(44, 5, 8, 'Mobile Phones', 'mobile-phones', 'mobile-phones', 'mobile-phones', 'mb123', 'mb123654', 2600.00, 2300.00, 5.00, 'manual', 1, 20, 10, '6864ae27107e1_1751428647.jpg', 1, 1, 1.00, '66x66', '2025-07-02 03:57:27', '2025-07-02 03:57:27', 6),
(45, 5, 19, 'Iron', 'iron', 'Iron', 'Iron', 'i369', 'i963', 1500.00, 1200.00, 5.00, 'manual', 1, 20, 10, '6864ae7301e94_1751428723.jpg', 1, 1, 1.00, '10*10', '2025-07-02 03:58:43', '2025-07-02 03:58:43', 6),
(46, 5, 19, 'TV', 'tv', 'TV', 'TV', 'tv123', 'tv123', 33000.00, 28000.00, 5.00, 'manual', 1, 50, 10, '6864b3a5e0e54_1751430053.jpg', 1, 1, 10.00, '66x66', '2025-07-02 04:20:54', '2025-07-02 04:20:54', 6),
(47, 5, 19, 'monitor', 'monitor', 'monitor', 'monitor', 'm123', 'm123', 18000.00, 16000.00, 10.00, 'manual', 1, 20, 10, '6864b43e325e9_1751430206.jpg', 1, 1, 10.00, '10*10', '2025-07-02 04:23:26', '2025-07-02 04:23:26', 6);

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
(6, 4, 'aaaa', 'aaaa', 'asfdsf sdf', '684cfbb868d76_1749875640.jpg', 1, 2, '2025-06-14 04:34:01', '2025-06-14 04:34:01'),
(7, 4, 'toys bike', 'toys-bike', 'sadf sdf sdfd sdfs f d123', '684d01c760e0a_1749877191.jpg', 1, 1, '2025-06-14 04:58:30', '2025-06-14 04:59:51'),
(8, 5, 'Mobile phone', 'mobile-phone', 'dsf dsf sdfds fds sdf ', '684d1f3cbe978_1749884732.jpg', 1, 8, '2025-06-14 06:50:04', '2025-06-14 07:05:33'),
(12, 7, 'goru1', 'goru1', 'sadfdsafdsf', '684d1eb2bff59_1749884594.jpg', 1, 0, '2025-06-14 07:03:15', '2025-06-14 07:03:15'),
(14, 9, 'amer sarbat', 'amer-sarbat', 'asf sadf sdfs afsd fdsf ', '685a492bafa50_1750747435.png', 1, 0, '2025-06-24 06:43:57', '2025-06-24 06:43:57'),
(15, 4, 'Cars', 'cars', 'dsf sadf', '685b82ee186a2_1750827758.jpg', 1, 0, '2025-06-25 05:02:38', '2025-06-25 05:02:38'),
(16, 5, 'AC', 'ac', 'dsfgdfg', '685b85aaebc84_1750828458.png', 1, 0, '2025-06-25 05:14:19', '2025-06-25 05:14:19'),
(17, 10, 'Home', 'home', 'home', NULL, 1, 0, '2025-07-02 03:19:27', '2025-07-02 03:19:27'),
(18, 10, 'Kitchen', 'kitchen', 'kitchen', NULL, 1, 0, '2025-07-02 03:19:49', '2025-07-02 03:19:49'),
(19, 5, 'Home Appliances', 'home-appliances', 'home-appliances', NULL, 1, 0, '2025-07-02 03:41:04', '2025-07-02 03:41:04');

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
(7, 'admin', 'admin', 'admin@gmail.com', NULL, '$2y$10$kyKkl7SXSsXE1IEzdbapGOS90AgPdzzIGzx/veB/lXOlZTz0gpUmC', 'admin', 1, NULL, NULL, '2025-06-04 04:59:25', '2025-06-04 04:59:35'),
(8, 'Kuddus', 'MIa', 'kuddus@gmail.com', NULL, '$2y$10$BTYIxagqON67VAbUMPD9RuFekOwKiooBxAd5duu5m8FP0iqNscPHW', 'customer', 1, NULL, NULL, '2025-06-24 04:46:57', '2025-06-24 04:46:57'),
(9, 'Rashibul ', 'Kabir', 'rashibul@gmail.com', NULL, '$2y$10$oLKlpVjGjhNTKpjMvcNQ2u/OhGUWU8klEDr2kRoMh.STW/S4QUiMS', 'customer', 1, NULL, NULL, '2025-07-02 04:07:14', '2025-07-02 04:07:14'),
(10, 'Hasan', 'Mahmud', 'hasanmahmud@gmail.com', NULL, '$2y$10$GS3o7jA/DhLsm48mvPRCW.dvnZkbDjcnf6RQKeh10S9bCx2HMmI8y', 'customer', 1, NULL, NULL, '2025-07-02 04:32:35', '2025-07-02 04:32:35');

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
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

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
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `processed_by` (`processed_by`),
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
  ADD KEY `idx_stock_low` (`stock_quantity`,`min_stock_level`),
  ADD KEY `products_ibfk_3` (`brand`);

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
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`brand`) REFERENCES `brands` (`id`);

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
