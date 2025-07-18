-- Create email_logs table to track email sending activity
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('sent','failed') NOT NULL,
  `message` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_number` (`order_number`),
  KEY `email` (`email`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add index for better performance
ALTER TABLE `email_logs` ADD INDEX `order_email_idx` (`order_number`, `email`);
ALTER TABLE `email_logs` ADD INDEX `status_date_idx` (`status`, `created_at`);