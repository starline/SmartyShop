-- phpMyAdmin SQL Dump
-- version 4.6.6deb4+deb9u2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 11, 2024 at 07:09 AM
-- Server version: 10.1.48-MariaDB-0+deb9u2
-- PHP Version: 7.0.33-60+0~20220627.68+debian9~1.gbp3d361a

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `store`
--

-- --------------------------------------------------------

--
-- Table structure for table `s_cart`
--

CREATE TABLE `s_cart` (
  `id` int(11) NOT NULL,
  `token` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_cart_products`
--

CREATE TABLE `s_cart_products` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_content_blog`
--

CREATE TABLE `s_content_blog` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(255) NOT NULL DEFAULT '',
  `meta_description` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `annotation` text,
  `body` mediumtext,
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_content_comments`
--

CREATE TABLE `s_content_comments` (
  `id` bigint(20) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(20) NOT NULL DEFAULT '',
  `text` text,
  `entity_id` int(11) DEFAULT NULL,
  `type` enum('product','blog','category') DEFAULT NULL,
  `rating` int(1) DEFAULT NULL,
  `related_id` bigint(20) DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_content_feedbacks`
--

CREATE TABLE `s_content_feedbacks` (
  `id` bigint(20) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(20) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `message` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_content_images`
--

CREATE TABLE `s_content_images` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `entity_id` int(11) DEFAULT NULL,
  `entity_name` varchar(25) NOT NULL DEFAULT '',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_content_menu`
--

CREATE TABLE `s_content_menu` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_content_pages`
--

CREATE TABLE `s_content_pages` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(255) NOT NULL DEFAULT '',
  `meta_description` varchar(255) NOT NULL DEFAULT '',
  `h1` varchar(255) DEFAULT NULL,
  `body` text,
  `menu_id` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_finance_categories`
--

CREATE TABLE `s_finance_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) DEFAULT NULL,
  `comment` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_finance_currencies`
--

CREATE TABLE `s_finance_currencies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sign` varchar(20) NOT NULL DEFAULT '',
  `code` char(4) NOT NULL DEFAULT '',
  `rate_from` decimal(10,2) NOT NULL DEFAULT '1.00',
  `rate_to` decimal(10,2) NOT NULL DEFAULT '1.00',
  `cents` int(1) NOT NULL DEFAULT '2',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_finance_entity_related`
--

CREATE TABLE `s_finance_entity_related` (
  `payment_id` int(11) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `entity_name` varchar(11) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_finance_payments`
--

CREATE TABLE `s_finance_payments` (
  `id` int(11) NOT NULL,
  `purse_id` int(11) DEFAULT NULL,
  `finance_category_id` int(11) DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `purse_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency_rate` decimal(10,4) NOT NULL DEFAULT '1.0000',
  `comment` text,
  `manager_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `related_payment_id` int(11) DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `verified_date` datetime DEFAULT NULL,
  `verified_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_finance_purses`
--

CREATE TABLE `s_finance_purses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `currency_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_orders`
--

CREATE TABLE `s_orders` (
  `id` bigint(20) NOT NULL,
  `delivery_id` int(11) DEFAULT NULL,
  `delivery_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `delivery_note` varchar(255) NOT NULL DEFAULT '',
  `delivery_info` varchar(900) NOT NULL DEFAULT '',
  `separate_delivery` tinyint(1) NOT NULL DEFAULT '0',
  `payment_method_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `address` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `comment` text,
  `note` text,
  `url` varchar(255) NOT NULL DEFAULT '',
  `ip` varchar(255) NOT NULL DEFAULT '',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `profit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `interest_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_code` varchar(255) NOT NULL DEFAULT '',
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `manager_id` int(11) DEFAULT NULL,
  `settings` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_orders_delivery`
--

CREATE TABLE `s_orders_delivery` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `module` varchar(255) NOT NULL DEFAULT '',
  `settings` text,
  `free_from` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `separate_payment` tinyint(1) NOT NULL DEFAULT '0',
  `finance_purse_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_orders_delivery_payment`
--

CREATE TABLE `s_orders_delivery_payment` (
  `delivery_id` int(11) NOT NULL,
  `payment_method_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Связка способом оплаты и способов доставки';

-- --------------------------------------------------------

--
-- Table structure for table `s_orders_labels`
--

CREATE TABLE `s_orders_labels` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `color` varchar(6) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `in_filter` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_orders_labels_related`
--

CREATE TABLE `s_orders_labels_related` (
  `order_id` int(11) NOT NULL,
  `label_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_orders_payment_methods`
--

CREATE TABLE `s_orders_payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `public_name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `comment` varchar(255) NOT NULL DEFAULT '',
  `module` varchar(255) NOT NULL DEFAULT '',
  `settings` text,
  `currency_id` float DEFAULT NULL,
  `finance_purse_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `enabled_public` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_orders_purchases`
--

CREATE TABLE `s_orders_purchases` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `variant_name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products`
--

CREATE TABLE `s_products` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `annotation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `disable` tinyint(1) NOT NULL DEFAULT '0',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `sale` tinyint(1) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_brands`
--

CREATE TABLE `s_products_brands` (
  `id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(255) NOT NULL DEFAULT '',
  `meta_description` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `description` text,
  `image` varchar(255) NOT NULL DEFAULT '',
  `featured` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_categories`
--

CREATE TABLE `s_products_categories` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT '0',
  `url` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `meta_title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `meta_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `h1` varchar(255) NOT NULL DEFAULT '',
  `annotation` text,
  `description` text,
  `image` varchar(255) NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0',
  `visible` tinyint(1) NOT NULL DEFAULT '0',
  `main` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_categories_features`
--

CREATE TABLE `s_products_categories_features` (
  `category_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_categories_synonyms`
--

CREATE TABLE `s_products_categories_synonyms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `category_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_features`
--

CREATE TABLE `s_products_features` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `in_filter` tinyint(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_features_variants`
--

CREATE TABLE `s_products_features_variants` (
  `id` int(11) NOT NULL,
  `feature_id` int(11) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_merchants`
--

CREATE TABLE `s_products_merchants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_merchants_variants`
--

CREATE TABLE `s_products_merchants_variants` (
  `variant_id` int(11) NOT NULL,
  `merchant_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_options`
--

CREATE TABLE `s_products_options` (
  `product_id` int(11) NOT NULL,
  `feature_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_products_related`
--

CREATE TABLE `s_products_products_related` (
  `product_id` int(11) NOT NULL,
  `related_id` int(11) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_providers`
--

CREATE TABLE `s_products_providers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `no_restore_price` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_products_variants`
--

CREATE TABLE `s_products_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `sku` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(14,2) NOT NULL DEFAULT '0.00',
  `cost_price` decimal(14,2) NOT NULL DEFAULT '0.00',
  `old_price` decimal(14,2) NOT NULL DEFAULT '0.00',
  `stock` mediumint(9) DEFAULT NULL,
  `weight` decimal(8,3) NOT NULL DEFAULT '0.000',
  `provider_id` int(11) DEFAULT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `awaiting_date` date DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `awaiting` tinyint(1) NOT NULL DEFAULT '0',
  `custom` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_seo_faqs`
--

CREATE TABLE `s_seo_faqs` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `entity_name` varchar(25) NOT NULL DEFAULT '',
  `entity_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_seo_keywords`
--

CREATE TABLE `s_seo_keywords` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `entity_name` varchar(25) NOT NULL DEFAULT '',
  `entity_id` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_settings`
--

CREATE TABLE `s_settings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_users`
--

CREATE TABLE `s_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `token` varchar(255) DEFAULT NULL COMMENT 'Telegram',
  `te_chat_id` int(255) DEFAULT NULL COMMENT 'Telegram',
  `password` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `last_ip` varchar(15) NOT NULL DEFAULT '',
  `group_id` int(11) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `manager` tinyint(1) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_users_coupons`
--

CREATE TABLE `s_users_coupons` (
  `id` bigint(20) NOT NULL,
  `code` varchar(255) NOT NULL DEFAULT '',
  `expire` datetime DEFAULT NULL,
  `type` enum('absolute','percentage') NOT NULL DEFAULT 'absolute',
  `value` decimal(10,2) NOT NULL DEFAULT '0.00',
  `min_order_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `single` tinyint(1) NOT NULL DEFAULT '0',
  `usages` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_users_groups`
--

CREATE TABLE `s_users_groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_users_notify`
--

CREATE TABLE `s_users_notify` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `comment` varchar(255) NOT NULL DEFAULT '',
  `module` varchar(255) DEFAULT NULL,
  `settings` text,
  `position` int(11) NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_users_notify_types`
--

CREATE TABLE `s_users_notify_types` (
  `user_id` int(11) NOT NULL,
  `notify_id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_users_permissions`
--

CREATE TABLE `s_users_permissions` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_wh_movements`
--

CREATE TABLE `s_wh_movements` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `awaiting_date` date DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `note` text,
  `note_logist` text,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `closed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `s_wh_purchases`
--

CREATE TABLE `s_wh_purchases` (
  `id` int(11) NOT NULL,
  `movement_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `sku` varchar(255) NOT NULL DEFAULT '',
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `variant_name` varchar(255) NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `cost_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `amount` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `s_cart`
--
ALTER TABLE `s_cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_cart_products`
--
ALTER TABLE `s_cart_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_content_blog`
--
ALTER TABLE `s_content_blog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`visible`),
  ADD KEY `url` (`url`);

--
-- Indexes for table `s_content_comments`
--
ALTER TABLE `s_content_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `object_id` (`type`,`entity_id`,`approved`,`ip`) USING BTREE,
  ADD KEY `related_id` (`related_id`);

--
-- Indexes for table `s_content_feedbacks`
--
ALTER TABLE `s_content_feedbacks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_content_images`
--
ALTER TABLE `s_content_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `filename` (`filename`),
  ADD KEY `product_id` (`entity_id`,`entity_name`) USING BTREE;

--
-- Indexes for table `s_content_menu`
--
ALTER TABLE `s_content_menu`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_content_pages`
--
ALTER TABLE `s_content_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `url` (`url`,`menu_id`,`visible`) USING BTREE,
  ADD KEY `menu_id` (`menu_id`) USING BTREE;

--
-- Indexes for table `s_finance_categories`
--
ALTER TABLE `s_finance_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type` (`type`);

--
-- Indexes for table `s_finance_currencies`
--
ALTER TABLE `s_finance_currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `position` (`position`),
  ADD KEY `enabled` (`enabled`);

--
-- Indexes for table `s_finance_entity_related`
--
ALTER TABLE `s_finance_entity_related`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `entity_name` (`entity_name`,`entity_id`) USING BTREE;

--
-- Indexes for table `s_finance_payments`
--
ALTER TABLE `s_finance_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `related_payment_id` (`related_payment_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `purse_id` (`purse_id`,`type`,`finance_category_id`,`related_payment_id`) USING BTREE,
  ADD KEY `finance_category_id` (`finance_category_id`,`purse_id`,`type`,`related_payment_id`) USING BTREE,
  ADD KEY `type` (`type`,`related_payment_id`) USING BTREE;

--
-- Indexes for table `s_finance_purses`
--
ALTER TABLE `s_finance_purses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `currency_id` (`currency_id`);

--
-- Indexes for table `s_orders`
--
ALTER TABLE `s_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`url`) USING BTREE,
  ADD KEY `status` (`status`,`paid`) USING BTREE,
  ADD KEY `paid` (`paid`) USING BTREE,
  ADD KEY `closed` (`closed`) USING BTREE,
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `payment_method_id` (`payment_method_id`),
  ADD KEY `delivery_id` (`delivery_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `s_orders_delivery`
--
ALTER TABLE `s_orders_delivery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`),
  ADD KEY `finance_purse_id` (`finance_purse_id`);

--
-- Indexes for table `s_orders_delivery_payment`
--
ALTER TABLE `s_orders_delivery_payment`
  ADD PRIMARY KEY (`delivery_id`,`payment_method_id`);

--
-- Indexes for table `s_orders_labels`
--
ALTER TABLE `s_orders_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`) USING BTREE;

--
-- Indexes for table `s_orders_labels_related`
--
ALTER TABLE `s_orders_labels_related`
  ADD PRIMARY KEY (`order_id`,`label_id`);

--
-- Indexes for table `s_orders_payment_methods`
--
ALTER TABLE `s_orders_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `finance_purse_id` (`finance_purse_id`),
  ADD KEY `currency_id` (`currency_id`);

--
-- Indexes for table `s_orders_purchases`
--
ALTER TABLE `s_orders_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `s_products`
--
ALTER TABLE `s_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `url` (`url`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `visible` (`visible`),
  ADD KEY `hit` (`featured`),
  ADD KEY `sale` (`sale`),
  ADD KEY `disable` (`disable`),
  ADD KEY `category_id` (`category_id`,`visible`) USING BTREE;
ALTER TABLE `s_products` ADD FULLTEXT KEY `name` (`name`);

--
-- Indexes for table `s_products_brands`
--
ALTER TABLE `s_products_brands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`),
  ADD KEY `url` (`url`),
  ADD KEY `featured` (`featured`);

--
-- Indexes for table `s_products_categories`
--
ALTER TABLE `s_products_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `url` (`url`),
  ADD KEY `position` (`position`),
  ADD KEY `visible` (`visible`),
  ADD KEY `parent_id` (`parent_id`,`visible`) USING BTREE;

--
-- Indexes for table `s_products_categories_features`
--
ALTER TABLE `s_products_categories_features`
  ADD PRIMARY KEY (`category_id`,`feature_id`);

--
-- Indexes for table `s_products_categories_synonyms`
--
ALTER TABLE `s_products_categories_synonyms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categry_id` (`category_id`);

--
-- Indexes for table `s_products_features`
--
ALTER TABLE `s_products_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `in_filter` (`in_filter`,`id`) USING BTREE;

--
-- Indexes for table `s_products_features_variants`
--
ALTER TABLE `s_products_features_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `s_products_merchants`
--
ALTER TABLE `s_products_merchants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enabled` (`enabled`) USING BTREE;

--
-- Indexes for table `s_products_merchants_variants`
--
ALTER TABLE `s_products_merchants_variants`
  ADD PRIMARY KEY (`variant_id`) USING BTREE,
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `s_products_options`
--
ALTER TABLE `s_products_options`
  ADD PRIMARY KEY (`product_id`,`feature_id`),
  ADD KEY `value` (`value`),
  ADD KEY `feature_id` (`feature_id`);

--
-- Indexes for table `s_products_products_related`
--
ALTER TABLE `s_products_products_related`
  ADD PRIMARY KEY (`product_id`,`related_id`);

--
-- Indexes for table `s_products_providers`
--
ALTER TABLE `s_products_providers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `no_restore_price` (`no_restore_price`);

--
-- Indexes for table `s_products_variants`
--
ALTER TABLE `s_products_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `sku` (`sku`),
  ADD KEY `price` (`price`),
  ADD KEY `stock` (`stock`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `s_seo_faqs`
--
ALTER TABLE `s_seo_faqs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entity_id` (`entity_id`);

--
-- Indexes for table `s_seo_keywords`
--
ALTER TABLE `s_seo_keywords`
  ADD PRIMARY KEY (`id`),
  ADD KEY `entity_name` (`entity_name`,`entity_id`) USING BTREE;

--
-- Indexes for table `s_settings`
--
ALTER TABLE `s_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `s_users`
--
ALTER TABLE `s_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email` (`email`),
  ADD KEY `group_id` (`group_id`);
ALTER TABLE `s_users` ADD FULLTEXT KEY `name` (`name`,`phone`,`email`);

--
-- Indexes for table `s_users_coupons`
--
ALTER TABLE `s_users_coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code` (`code`);

--
-- Indexes for table `s_users_groups`
--
ALTER TABLE `s_users_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_users_notify`
--
ALTER TABLE `s_users_notify`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `s_users_notify_types`
--
ALTER TABLE `s_users_notify_types`
  ADD KEY `user_id` (`user_id`,`notify_id`);

--
-- Indexes for table `s_users_permissions`
--
ALTER TABLE `s_users_permissions`
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `s_wh_movements`
--
ALTER TABLE `s_wh_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `s_wh_purchases`
--
ALTER TABLE `s_wh_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `movement_id` (`movement_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `s_cart`
--
ALTER TABLE `s_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `s_cart_products`
--
ALTER TABLE `s_cart_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `s_content_blog`
--
ALTER TABLE `s_content_blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
--
-- AUTO_INCREMENT for table `s_content_comments`
--
ALTER TABLE `s_content_comments`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3239;
--
-- AUTO_INCREMENT for table `s_content_feedbacks`
--
ALTER TABLE `s_content_feedbacks`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `s_content_images`
--
ALTER TABLE `s_content_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3895;
--
-- AUTO_INCREMENT for table `s_content_menu`
--
ALTER TABLE `s_content_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `s_content_pages`
--
ALTER TABLE `s_content_pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
--
-- AUTO_INCREMENT for table `s_finance_categories`
--
ALTER TABLE `s_finance_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
--
-- AUTO_INCREMENT for table `s_finance_currencies`
--
ALTER TABLE `s_finance_currencies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `s_finance_payments`
--
ALTER TABLE `s_finance_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1114;
--
-- AUTO_INCREMENT for table `s_finance_purses`
--
ALTER TABLE `s_finance_purses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
--
-- AUTO_INCREMENT for table `s_orders`
--
ALTER TABLE `s_orders`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5208;
--
-- AUTO_INCREMENT for table `s_orders_delivery`
--
ALTER TABLE `s_orders_delivery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `s_orders_labels`
--
ALTER TABLE `s_orders_labels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT for table `s_orders_payment_methods`
--
ALTER TABLE `s_orders_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT for table `s_orders_purchases`
--
ALTER TABLE `s_orders_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10032;
--
-- AUTO_INCREMENT for table `s_products`
--
ALTER TABLE `s_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=524;
--
-- AUTO_INCREMENT for table `s_products_brands`
--
ALTER TABLE `s_products_brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;
--
-- AUTO_INCREMENT for table `s_products_categories`
--
ALTER TABLE `s_products_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2401;
--
-- AUTO_INCREMENT for table `s_products_categories_synonyms`
--
ALTER TABLE `s_products_categories_synonyms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;
--
-- AUTO_INCREMENT for table `s_products_features`
--
ALTER TABLE `s_products_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=394;
--
-- AUTO_INCREMENT for table `s_products_features_variants`
--
ALTER TABLE `s_products_features_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1822;
--
-- AUTO_INCREMENT for table `s_products_merchants`
--
ALTER TABLE `s_products_merchants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
--
-- AUTO_INCREMENT for table `s_products_providers`
--
ALTER TABLE `s_products_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;
--
-- AUTO_INCREMENT for table `s_products_variants`
--
ALTER TABLE `s_products_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26781;
--
-- AUTO_INCREMENT for table `s_seo_faqs`
--
ALTER TABLE `s_seo_faqs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `s_seo_keywords`
--
ALTER TABLE `s_seo_keywords`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=690;
--
-- AUTO_INCREMENT for table `s_settings`
--
ALTER TABLE `s_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;
--
-- AUTO_INCREMENT for table `s_users`
--
ALTER TABLE `s_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4191;
--
-- AUTO_INCREMENT for table `s_users_coupons`
--
ALTER TABLE `s_users_coupons`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table `s_users_groups`
--
ALTER TABLE `s_users_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `s_users_notify`
--
ALTER TABLE `s_users_notify`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `s_wh_movements`
--
ALTER TABLE `s_wh_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=227;
--
-- AUTO_INCREMENT for table `s_wh_purchases`
--
ALTER TABLE `s_wh_purchases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1869;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
