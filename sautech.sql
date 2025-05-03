-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 01:02 AM
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
-- Database: `sautech`
--

-- --------------------------------------------------------

--
-- Table structure for table `billing_category_prices`
--

CREATE TABLE `billing_category_prices` (
  `id` int(11) NOT NULL,
  `service_category_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_category_prices`
--

INSERT INTO `billing_category_prices` (`id`, `service_category_id`, `item_name`, `unit_price`, `vat_rate`, `created_at`, `updated_at`) VALUES
(4, 3, 'None', 15.27, 15.00, '2025-04-29 09:59:54', '2025-04-29 09:59:54'),
(5, 2, 'null', 12.72, 15.00, '2025-04-29 10:22:23', '2025-04-29 10:22:23');

-- --------------------------------------------------------

--
-- Table structure for table `billing_invoice_companies`
--

CREATE TABLE `billing_invoice_companies` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_invoice_companies`
--

INSERT INTO `billing_invoice_companies` (`id`, `company_name`, `vat_rate`, `created_at`) VALUES
(1, 'hmstechs', 0.03, '2025-04-27 11:36:27');

-- --------------------------------------------------------

--
-- Table structure for table `billing_items`
--

CREATE TABLE `billing_items` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `service_type_id` int(11) DEFAULT NULL,
  `service_type_name` varchar(255) NOT NULL,
  `service_category_id` int(11) DEFAULT NULL,
  `service_category_name` varchar(255) NOT NULL,
  `cpu` varchar(255) NOT NULL,
  `memory` varchar(255) NOT NULL,
  `hdd_sata` varchar(255) NOT NULL,
  `hdd_ssd` varchar(255) NOT NULL,
  `os` varchar(255) NOT NULL,
  `ip_address` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `qty` varchar(255) NOT NULL,
  `unit_price` varchar(255) NOT NULL,
  `vat_applied` int(11) NOT NULL DEFAULT 0,
  `vat_rate` varchar(255) NOT NULL,
  `invoice_type` varchar(255) NOT NULL,
  `invoicing_company_id` int(11) NOT NULL,
  `invoicing_company_name` varchar(255) NOT NULL,
  `frequency` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `currency` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing_service_categories`
--

CREATE TABLE `billing_service_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `has_vm_fields` tinyint(4) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_service_categories`
--

INSERT INTO `billing_service_categories` (`id`, `category_name`, `note`, `has_vm_fields`, `created_at`, `is_deleted`) VALUES
(1, 'none', 'undefined', 1, '2025-04-27 11:06:24', 1),
(2, 'Web', 'undefined', 1, '2025-04-28 03:50:01', 0),
(3, 'none', '', 0, '2025-04-28 08:40:49', 0),
(4, 'null', 'undefined', 0, '2025-04-28 13:46:58', 0);

-- --------------------------------------------------------

--
-- Table structure for table `billing_service_types`
--

CREATE TABLE `billing_service_types` (
  `id` int(11) NOT NULL,
  `service_type_name` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billing_service_types`
--

INSERT INTO `billing_service_types` (`id`, `service_type_name`, `note`, `created_at`) VALUES
(1, 'none', '', '2025-04-27 11:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `billing_suppliers`
--

CREATE TABLE `billing_suppliers` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_details` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `salesperson` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `number` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `office_number` varchar(255) DEFAULT NULL,
  `accounts_contact` varchar(255) DEFAULT NULL,
  `accounts_email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `vat_number` varchar(100) DEFAULT NULL,
  `registration_number` varchar(100) DEFAULT NULL,
  `billing_type` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `sales_person` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `billing_country` varchar(100) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_name`, `number`, `email`, `contact_person`, `office_number`, `accounts_contact`, `accounts_email`, `address`, `notes`, `vat_number`, `registration_number`, `billing_type`, `status`, `sales_person`, `created_at`, `billing_country`, `currency`) VALUES
(6, 'Sautech (Pty) Ltd', '', 'riaan@sautech.net', 'Riaan van Jaarsveld', '0117681790', 'Chandre', 'accounts@sautech.net', '34 Stokroos Avenue', '', '4240111963', '2003/44/444', 'Invoice', 'Active', 'Riaan', '2025-04-22 08:24:32', 'RSA', 'ZAR'),
(7, 'Afrihost', '', 'chantal@afrihost.com', 'Chantal Nunes', '0117681791, 0834445555', 'Katryn', 'accounts@afrihost.com', '01 Rivnonia', '', '325564765', '3444/444/00003', 'Invoice', 'Active', 'Chantal', '2025-04-22 09:42:23', 'RSA', 'ZAR'),
(8, 'Sage Stokes', '', 'nesyq@mailinator.com', 'Officia vel sit dolo', '386', 'Et id ullamco quidem', 'ferogapi@mailinator.com', 'Incidunt ipsum magn', 'Est quas fugit ver', '887', '398', 'Invoice', 'Suspended', 'In voluptatem volupt', '2025-04-24 14:18:09', 'Namibia', 'USD'),
(9, 'Mari Joyce', '', 'legidu@mailinator.com', 'Nulla nostrum ea dol', '656', 'Eveniet nisi et ape', 'zuwekaby@mailinator.com', 'Modi et eum voluptas', 'Tenetur officia sed ', '943', '205', 'Invoice', 'Lead', 'Reprehenderit ea ame', '2025-04-24 14:18:21', 'RSA', 'NAD'),
(13, 'Rameez', '03365456284', 'rameeznazar600@gmail.com', 'Rameez Nazar', '0117681790', '', '', 'Faisalabad', '', '', '', 'Invoice', 'Active', '', '2025-04-25 10:33:23', 'RSA', 'NAD');

-- --------------------------------------------------------

--
-- Table structure for table `client_365`
--

CREATE TABLE `client_365` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `product` varchar(255) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_365`
--

INSERT INTO `client_365` (`id`, `client_id`, `product`, `fullname`, `username`, `password`, `note`) VALUES
(2, 7, 'gfgf', 'rwr', 'rwrw', 'rwrw', 'rwrw');

-- --------------------------------------------------------

--
-- Table structure for table `client_custom_tabs`
--

CREATE TABLE `client_custom_tabs` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `tab_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_custom_tabs`
--

INSERT INTO `client_custom_tabs` (`id`, `client_id`, `tab_name`, `created_at`) VALUES
(1, NULL, NULL, '2025-04-24 04:07:52');

-- --------------------------------------------------------

--
-- Table structure for table `client_devices`
--

CREATE TABLE `client_devices` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `device_name` varchar(255) NOT NULL,
  `device_type` varchar(255) NOT NULL,
  `device_ip` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `enable_username` varchar(255) NOT NULL,
  `enable_password` varchar(255) NOT NULL,
  `access_port` varchar(255) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_devices`
--

INSERT INTO `client_devices` (`id`, `client_id`, `device_name`, `device_type`, `device_ip`, `location`, `username`, `password`, `enable_username`, `enable_password`, `access_port`, `note`, `created_at`, `is_deleted`) VALUES
(1, 9, 'Internet', 'j', '1', 'fsf', 'ali000', '000', '', '', '', '', '2025-04-28 02:08:13', 1),
(2, 13, 'Internet', '2650', '2', 'd', 'ali000', '000', '', '', '1', 'hi how are you', '2025-04-28 02:24:40', 0);

-- --------------------------------------------------------

--
-- Table structure for table `client_documents`
--

CREATE TABLE `client_documents` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_documents`
--

INSERT INTO `client_documents` (`id`, `client_id`, `name`, `filename`, `uploaded_at`) VALUES
(2, 1, 'SLA2', '1745173059_Sautech_ClientZone_Pro_Overview_UTF8.pdf', '2025-04-20 18:17:39'),
(5, 4, 'P02', '1745240733_471763800_10162058678076885_219087097141969261_n.jpg', '2025-04-21 13:05:33'),
(6, 4, 'rwur', '1745240750_Exodus.exe', '2025-04-21 13:05:50'),
(10, 7, 'fsfs', '1745330657_Sautech_ClientZone_Pro_Overview_UTF8.pdf', '2025-04-22 14:04:17'),
(11, 6, 'Test', '1745404298_471763800_10162058678076885_219087097141969261_n.jpg', '2025-04-23 10:31:38');

-- --------------------------------------------------------

--
-- Table structure for table `client_products`
--

CREATE TABLE `client_products` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_product_records`
--

CREATE TABLE `client_product_records` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client_services`
--

CREATE TABLE `client_services` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `service_type` varchar(100) DEFAULT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `specs` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `billing_amount` decimal(10,2) DEFAULT NULL,
  `invoice_link` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `currency` varchar(10) DEFAULT 'ZAR',
  `quantity` int(11) DEFAULT 1,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `exclude_vat` tinyint(1) DEFAULT 1,
  `ex_vat_amount` decimal(10,2) DEFAULT NULL,
  `vat_amount` decimal(10,2) DEFAULT NULL,
  `total_with_vat` decimal(10,2) DEFAULT NULL,
  `debit_order_day` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_services`
--

INSERT INTO `client_services` (`id`, `client_name`, `service_type`, `service_name`, `specs`, `status`, `billing_amount`, `invoice_link`, `notes`, `created_at`, `is_deleted`, `currency`, `quantity`, `start_date`, `end_date`, `exclude_vat`, `ex_vat_amount`, `vat_amount`, `total_with_vat`, `debit_order_day`) VALUES
(1, 'Charlaine', 'Hosting', 'Exchange 100GB', 'domain:sautech.net', 'Active', NULL, '', '8 hours onsite support', '2025-04-20 14:42:29', 1, 'ZAR', 17, '2024-04-01', '2027-04-09', 1, 99.00, 252.45, 1935.45, NULL),
(2, 'Riaan', 'Finance', 'Dell Laptop 4450', '3 Month Finance', 'Active', NULL, '', '', '2025-04-20 15:11:37', 1, 'ZAR', 1, '2025-04-30', '2025-07-30', 1, 8000.00, 1248.00, 9248.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `client_service_edits`
--

CREATE TABLE `client_service_edits` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `old_quantity` int(11) DEFAULT NULL,
  `new_quantity` int(11) DEFAULT NULL,
  `edited_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_service_edits`
--

INSERT INTO `client_service_edits` (`id`, `service_id`, `old_quantity`, `new_quantity`, `edited_at`) VALUES
(1, 1, 18, 17, '2025-04-20 14:55:49'),
(2, 1, 18, 17, '2025-04-20 14:57:00'),
(3, 1, 18, 17, '2025-04-20 14:58:56'),
(4, 1, 18, 17, '2025-04-20 15:00:03'),
(5, 1, 18, 17, '2025-04-20 15:01:24'),
(6, 1, 17, 17, '2025-04-20 19:03:52'),
(7, 1, 17, 17, '2025-04-20 19:06:57'),
(8, 1, 17, 17, '2025-04-20 19:09:06'),
(9, 1, 17, 17, '2025-04-20 19:11:11');

-- --------------------------------------------------------

--
-- Table structure for table `client_support_items`
--

CREATE TABLE `client_support_items` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `extra` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`extra`)),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_support_items`
--

INSERT INTO `client_support_items` (`id`, `client_id`, `label`, `type`, `ip_address`, `username`, `password`, `extra`, `created_at`) VALUES
(4, 1, 'jjkj', 'j', 'j', 'j', 'kj', NULL, '2025-04-20 19:01:04'),
(5, 4, 'jk', 'jjk', 'k', 'jj', 'kjk', '{\"note\": \"k\"}', '2025-04-21 13:02:26'),
(6, 4, 'u9u7', 'rwerfjk', 'kjlk', 'nk', 'nm', '{\"note\": \" n\"}', '2025-04-21 13:06:17'),
(7, 6, 'Cisco Router', '2650', '192.168.1.4', 'root', 'admin', '{\"note\": \"Dean se Office\"}', '2025-04-22 08:28:30'),
(8, 6, 'Internet', 'VOX 500MB Fibre', '44.5.7.22', 'admin', 'we dont have it', '{\"note\": \"Supplied by VOX Contract # 445599Kp\"}', '2025-04-22 09:15:15'),
(9, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-24 04:07:51');

-- --------------------------------------------------------

--
-- Table structure for table `exchange_domains`
--

CREATE TABLE `exchange_domains` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange_domains`
--

INSERT INTO `exchange_domains` (`id`, `client_id`, `domain`, `created_at`) VALUES
(1, 7, 'Sautech.net', '2025-04-22 14:27:43'),
(2, 7, 'Sautech.co.za', '2025-04-22 14:34:06'),
(4, 7, 'sautech.com.na', '2025-04-23 07:12:41'),
(6, 7, 'cheree.com', '2025-04-23 07:37:34'),
(7, 7, 'fswf.com', '2025-04-23 07:41:39'),
(8, 7, 'test2.com', '2025-04-23 07:42:41'),
(9, 7, 'test4.com', '2025-04-23 07:46:27'),
(10, 7, 'test5.com', '2025-04-23 07:52:03'),
(11, 7, 'test6.com', '2025-04-23 11:08:02'),
(12, 7, 'O365BS-ipin.co.za', '2025-04-23 11:50:18');

-- --------------------------------------------------------

--
-- Table structure for table `exchange_mailboxes`
--

CREATE TABLE `exchange_mailboxes` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `spamtitan` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exchange_mailboxes`
--

INSERT INTO `exchange_mailboxes` (`id`, `client_id`, `domain`, `email`, `password`, `full_name`, `spamtitan`, `note`, `created_at`) VALUES
(3, 7, 'Sautech.co.za', 'rvjaarsveld@sautech.net', 'www@dsccc', 'Riaan van Jaarsveld', '', 'Test', '2025-04-22 17:05:20'),
(11, 7, 'Sautech.net', 'fssf@ddas.com', 'dsjakdjasd', 'fefsf', 'Gandalf ', '', '2025-04-23 08:04:51'),
(12, 7, 'test6.com', 'fwfw@com.oedfe', 'eqeqefdsfsfsd', 'fwefwfw', 'Apollo', '', '2025-04-23 11:08:33');

-- --------------------------------------------------------

--
-- Table structure for table `hosting_assets`
--

CREATE TABLE `hosting_assets` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `asset_type` varchar(100) DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  `server_name` varchar(255) DEFAULT NULL,
  `os` varchar(100) DEFAULT NULL,
  `cpu` varchar(50) DEFAULT NULL,
  `mem` varchar(50) DEFAULT NULL,
  `ram` varchar(255) NOT NULL,
  `sata` varchar(50) DEFAULT NULL,
  `ssd` varchar(50) DEFAULT NULL,
  `private_ip` varchar(100) DEFAULT NULL,
  `public_ip` varchar(100) DEFAULT NULL,
  `ip_address` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `spla` enum('Yes','No') DEFAULT 'No',
  `login_url` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hosting_assets`
--

INSERT INTO `hosting_assets` (`id`, `client_id`, `client_name`, `location`, `asset_type`, `host`, `server_name`, `os`, `cpu`, `mem`, `ram`, `sata`, `ssd`, `private_ip`, `public_ip`, `ip_address`, `username`, `password`, `spla`, `login_url`, `note`, `created_at`) VALUES
(13, 2, 'Rehan Ali', 'null', 'null', 'null', 'null', 'null', '5', 'null', 'null', 'null', 'null', 'null', 'null', 0, '', '', 'Yes', 'null', 'null', '2025-04-26 21:43:38');

-- --------------------------------------------------------

--
-- Table structure for table `hosting_asset_types`
--

CREATE TABLE `hosting_asset_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hosting_asset_types`
--

INSERT INTO `hosting_asset_types` (`id`, `name`) VALUES
(3, ''),
(1, 'Hyper-V');

-- --------------------------------------------------------

--
-- Table structure for table `hosting_hosts`
--

CREATE TABLE `hosting_hosts` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosting_locations`
--

CREATE TABLE `hosting_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosting_oss`
--

CREATE TABLE `hosting_oss` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hosting_oss`
--

INSERT INTO `hosting_oss` (`id`, `name`) VALUES
(1, 'Windows Server 2022');

-- --------------------------------------------------------

--
-- Table structure for table `products_365`
--

CREATE TABLE `products_365` (
  `id` int(11) NOT NULL,
  `product` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products_365`
--

INSERT INTO `products_365` (`id`, `product`) VALUES
(1, 'rerww'),
(3, 'Business');

-- --------------------------------------------------------

--
-- Table structure for table `registers`
--

CREATE TABLE `registers` (
  `id` int(11) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `device_type` varchar(255) NOT NULL,
  `device_ip` varchar(255) NOT NULL,
  `device_ip_location` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registers`
--

INSERT INTO `registers` (`id`, `client_name`, `client_id`, `device_type`, `device_ip`, `device_ip_location`, `url`, `username`, `password`) VALUES
(9, 'Rehan', '2', '', '', '', '', 'rehan', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `service_names`
--

CREATE TABLE `service_names` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_types`
--

CREATE TABLE `service_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'vat_rate', '15.6');

-- --------------------------------------------------------

--
-- Table structure for table `spamtitan_servers`
--

CREATE TABLE `spamtitan_servers` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `hostname` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spamtitan_servers`
--

INSERT INTO `spamtitan_servers` (`id`, `client_id`, `hostname`, `created_at`) VALUES
(1, 7, 'Gandalf ', '2025-04-22 14:27:53'),
(2, 7, 'Apollo', '2025-04-22 14:34:15'),
(3, 7, 'Balrog', '2025-04-23 07:10:26'),
(4, 7, 'Athena', '2025-04-23 07:26:53'),
(5, 6, 'Balrog', '2025-04-23 07:29:22'),
(6, 7, 'Ironfoot', '2025-04-23 07:42:52');

-- --------------------------------------------------------

--
-- Table structure for table `spla_custom_data`
--

CREATE TABLE `spla_custom_data` (
  `id` int(11) NOT NULL,
  `spla_license_id` int(11) DEFAULT NULL,
  `field_id` int(11) DEFAULT NULL,
  `field_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spla_custom_fields`
--

CREATE TABLE `spla_custom_fields` (
  `id` int(11) NOT NULL,
  `field_name` varchar(255) DEFAULT NULL,
  `field_type` varchar(50) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `spla_licenses`
--

CREATE TABLE `spla_licenses` (
  `id` int(11) NOT NULL,
  `client` varchar(255) DEFAULT NULL,
  `vm_name` varchar(255) DEFAULT NULL,
  `vcpus` int(11) DEFAULT NULL,
  `ram` int(11) DEFAULT NULL,
  `disk` int(11) DEFAULT NULL,
  `windows_version` varchar(100) DEFAULT NULL,
  `ms_products` varchar(255) DEFAULT NULL,
  `quantity` varchar(244) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `type` enum('vm','license') DEFAULT 'vm'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spla_licenses`
--

INSERT INTO `spla_licenses` (`id`, `client`, `vm_name`, `vcpus`, `ram`, `disk`, `windows_version`, `ms_products`, `quantity`, `notes`, `created_at`, `is_deleted`, `type`) VALUES
(1, 'Sautech', 'Exchange2016', 8, 12, 1000, 'Windows Server 2022', 'Exchange Server', '', 'Hosted Exchange', '2025-04-20 13:21:23', 0, 'vm'),
(2, 'Charlaine', 'Web01', 3, 23, 22, 'Windows Server 2022', '', '', 'Test', '2025-04-20 13:35:39', 0, 'vm'),
(3, 'NessGroup', 'NessMain', 1, 1, 1, 'Remote Desktop', '', '', 'QTY 10', '2025-04-20 15:28:41', 1, 'vm'),
(4, 'NessGroup', '', 0, 0, 0, '', 'Remote Desktop', '', '', '2025-04-20 15:44:21', 1, 'license'),
(5, 'Rameez', NULL, NULL, NULL, NULL, NULL, 'Remotes', '1', '', '2025-04-26 10:48:13', 1, 'license'),
(6, 'Rameez', NULL, NULL, NULL, NULL, NULL, 'Remotes', '1', '', '2025-04-26 10:48:56', 1, 'license'),
(7, 'Rameez', NULL, NULL, NULL, NULL, NULL, 'Desktop', '1', '', '2025-04-26 19:17:42', 1, 'license'),
(8, 'Rameez', NULL, NULL, NULL, NULL, NULL, 'Remote', '1', '', '2025-04-26 19:22:21', 1, 'license'),
(9, 'Afrihost', NULL, NULL, NULL, NULL, NULL, 'Remotes', '1', '', '2025-04-26 19:41:41', 1, 'license'),
(10, 'Rameez', NULL, NULL, NULL, NULL, NULL, 'Remotes', '10', '', '2025-04-26 20:42:20', 1, 'license'),
(11, 'Sage Stokes', NULL, NULL, NULL, NULL, NULL, 'Remote', '6', '', '2025-04-26 21:15:26', 1, 'license'),
(12, 'Mari Joyce', NULL, NULL, NULL, NULL, NULL, 'Remote', '10', '', '2025-04-26 21:31:01', 1, 'license'),
(13, 'Ashish. (Hire me in upwork)', NULL, NULL, NULL, NULL, NULL, 'Remote', '10', '', '2025-04-26 21:38:01', 1, 'license'),
(14, 'Ashish. (Hire me in upwork)', NULL, NULL, NULL, NULL, NULL, 'Remote', '10', '', '2025-04-26 21:44:03', 0, 'license');

-- --------------------------------------------------------

--
-- Table structure for table `spla_licenses_edits`
--

CREATE TABLE `spla_licenses_edits` (
  `id` int(11) NOT NULL,
  `spla_id` int(11) NOT NULL,
  `field_changed` varchar(50) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `edited_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `spla_licenses_edits`
--

INSERT INTO `spla_licenses_edits` (`id`, `spla_id`, `field_changed`, `old_value`, `new_value`, `edited_at`) VALUES
(1, 4, 'quantity', '21', '22', '2025-04-20 15:48:15');

-- --------------------------------------------------------

--
-- Table structure for table `support_data`
--

CREATE TABLE `support_data` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `serial` varchar(255) DEFAULT NULL,
  `make` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `ipaddress` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_data`
--

INSERT INTO `support_data` (`id`, `client_id`, `description`, `serial`, `make`, `model`, `location`, `username`, `password`, `note`, `ipaddress`) VALUES
(1, 7, 'd', 'd', 'd', 'dd', 'd', 'd', 'd', 'd', NULL),
(2, 7, 'fsdf', 'fsf', 'fsf', 'fsf', 'fsf', 'fsf', 'fsf', 'ffsfsfsfsf', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billing_category_prices`
--
ALTER TABLE `billing_category_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`service_category_id`);

--
-- Indexes for table `billing_invoice_companies`
--
ALTER TABLE `billing_invoice_companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_items`
--
ALTER TABLE `billing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `billing_service_type_id` (`service_type_id`),
  ADD KEY `billing_service_category_id` (`service_category_id`),
  ADD KEY `invoice_company_id` (`invoicing_company_id`);

--
-- Indexes for table `billing_service_categories`
--
ALTER TABLE `billing_service_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_service_types`
--
ALTER TABLE `billing_service_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `billing_suppliers`
--
ALTER TABLE `billing_suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_365`
--
ALTER TABLE `client_365`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_custom_tabs`
--
ALTER TABLE `client_custom_tabs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_devices`
--
ALTER TABLE `client_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_is` (`client_id`);

--
-- Indexes for table `client_documents`
--
ALTER TABLE `client_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_products`
--
ALTER TABLE `client_products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_product_records`
--
ALTER TABLE `client_product_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_services`
--
ALTER TABLE `client_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_service_edits`
--
ALTER TABLE `client_service_edits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `client_support_items`
--
ALTER TABLE `client_support_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exchange_domains`
--
ALTER TABLE `exchange_domains`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exchange_mailboxes`
--
ALTER TABLE `exchange_mailboxes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hosting_assets`
--
ALTER TABLE `hosting_assets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hosting_asset_types`
--
ALTER TABLE `hosting_asset_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hosting_hosts`
--
ALTER TABLE `hosting_hosts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hosting_locations`
--
ALTER TABLE `hosting_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `hosting_oss`
--
ALTER TABLE `hosting_oss`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `products_365`
--
ALTER TABLE `products_365`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registers`
--
ALTER TABLE `registers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_names`
--
ALTER TABLE `service_names`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `service_types`
--
ALTER TABLE `service_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `spamtitan_servers`
--
ALTER TABLE `spamtitan_servers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spla_custom_data`
--
ALTER TABLE `spla_custom_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spla_license_id` (`spla_license_id`),
  ADD KEY `field_id` (`field_id`);

--
-- Indexes for table `spla_custom_fields`
--
ALTER TABLE `spla_custom_fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spla_licenses`
--
ALTER TABLE `spla_licenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `spla_licenses_edits`
--
ALTER TABLE `spla_licenses_edits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `spla_id` (`spla_id`);

--
-- Indexes for table `support_data`
--
ALTER TABLE `support_data`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billing_category_prices`
--
ALTER TABLE `billing_category_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `billing_invoice_companies`
--
ALTER TABLE `billing_invoice_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `billing_items`
--
ALTER TABLE `billing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `billing_service_categories`
--
ALTER TABLE `billing_service_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `billing_service_types`
--
ALTER TABLE `billing_service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `billing_suppliers`
--
ALTER TABLE `billing_suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `client_365`
--
ALTER TABLE `client_365`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `client_custom_tabs`
--
ALTER TABLE `client_custom_tabs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_devices`
--
ALTER TABLE `client_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `client_documents`
--
ALTER TABLE `client_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `client_products`
--
ALTER TABLE `client_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_product_records`
--
ALTER TABLE `client_product_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `client_services`
--
ALTER TABLE `client_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `client_service_edits`
--
ALTER TABLE `client_service_edits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `client_support_items`
--
ALTER TABLE `client_support_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `exchange_domains`
--
ALTER TABLE `exchange_domains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exchange_mailboxes`
--
ALTER TABLE `exchange_mailboxes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `hosting_assets`
--
ALTER TABLE `hosting_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `hosting_asset_types`
--
ALTER TABLE `hosting_asset_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `hosting_hosts`
--
ALTER TABLE `hosting_hosts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `hosting_locations`
--
ALTER TABLE `hosting_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `hosting_oss`
--
ALTER TABLE `hosting_oss`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products_365`
--
ALTER TABLE `products_365`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registers`
--
ALTER TABLE `registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `service_names`
--
ALTER TABLE `service_names`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_types`
--
ALTER TABLE `service_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `spamtitan_servers`
--
ALTER TABLE `spamtitan_servers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `spla_custom_data`
--
ALTER TABLE `spla_custom_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spla_custom_fields`
--
ALTER TABLE `spla_custom_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `spla_licenses`
--
ALTER TABLE `spla_licenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `spla_licenses_edits`
--
ALTER TABLE `spla_licenses_edits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_data`
--
ALTER TABLE `support_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billing_category_prices`
--
ALTER TABLE `billing_category_prices`
  ADD CONSTRAINT `category_id` FOREIGN KEY (`service_category_id`) REFERENCES `billing_service_categories` (`id`);

--
-- Constraints for table `billing_items`
--
ALTER TABLE `billing_items`
  ADD CONSTRAINT `billing_service_category_id` FOREIGN KEY (`service_category_id`) REFERENCES `billing_service_categories` (`id`),
  ADD CONSTRAINT `billing_service_type_id` FOREIGN KEY (`service_type_id`) REFERENCES `billing_service_types` (`id`),
  ADD CONSTRAINT `client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `supplier_id` FOREIGN KEY (`supplier_id`) REFERENCES `billing_suppliers` (`id`);

--
-- Constraints for table `client_service_edits`
--
ALTER TABLE `client_service_edits`
  ADD CONSTRAINT `client_service_edits_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `client_services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spla_custom_data`
--
ALTER TABLE `spla_custom_data`
  ADD CONSTRAINT `spla_custom_data_ibfk_1` FOREIGN KEY (`spla_license_id`) REFERENCES `spla_licenses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `spla_custom_data_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `spla_custom_fields` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `spla_licenses_edits`
--
ALTER TABLE `spla_licenses_edits`
  ADD CONSTRAINT `spla_licenses_edits_ibfk_1` FOREIGN KEY (`spla_id`) REFERENCES `spla_licenses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
