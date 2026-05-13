-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 11, 2026 at 05:10 PM
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
-- Database: `enslp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounting_transactions`
--

CREATE TABLE `accounting_transactions` (
  `id` int(11) NOT NULL,
  `txn_date` date NOT NULL,
  `type` enum('Income','Expense') NOT NULL,
  `category` varchar(80) NOT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `wo_id` int(11) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `payment_method` varchar(40) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounting_transactions`
--

INSERT INTO `accounting_transactions` (`id`, `txn_date`, `type`, `category`, `reference_no`, `wo_id`, `description`, `payment_method`, `amount`, `created_at`) VALUES
(135, '2026-05-11', 'Income', 'GCash Payment', NULL, NULL, 'GCash Payment - Gold Plating (1 pcs)', NULL, 450.00, '2026-05-11 19:52:14');

-- --------------------------------------------------------

--
-- Table structure for table `adhesive_jobs`
--

CREATE TABLE `adhesive_jobs` (
  `id` int(11) NOT NULL,
  `work_order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `material` varchar(255) NOT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `date_applied` date DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `att_date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `att_date`, `time_in`, `time_out`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(35, 16, '2026-05-07', '17:18:54', '17:18:58', 'Present', 'RFID Time Out', '2026-05-07 09:18:54', '2026-05-07 09:18:58');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `email`, `password`, `created_at`, `contact_number`, `address`) VALUES
(5, 'Brother Industry', 'jaeedumpp@gmail.com', '$2y$10$cjQf3JALU.fpcIOln8LAHuBv62a5WY11ksSL63ZFA0nseZ.n7Y9/e', '2026-05-08 09:00:38', '09154782744', 'Ulango Tanauan City Batangas');

-- --------------------------------------------------------

--
-- Table structure for table `cutting_jobs`
--

CREATE TABLE `cutting_jobs` (
  `id` int(11) NOT NULL,
  `work_order_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity_cut` int(11) DEFAULT NULL,
  `operator` varchar(100) DEFAULT NULL,
  `date_cut` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `dr_no` varchar(30) NOT NULL,
  `wo_id` int(11) NOT NULL,
  `delivered_to` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `delivered_date` date NOT NULL,
  `status` enum('Pending','Out for Delivery','Delivered','Cancelled') DEFAULT 'Pending',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `delivery_qty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `employment_status` varchar(50) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `salary_type` enum('Daily','Monthly') NOT NULL DEFAULT 'Daily',
  `salary_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `contact_no` varchar(20) DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `monthly_salary` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rfid_uid` varchar(50) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `rate_per_hour` decimal(10,2) DEFAULT 100.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `full_name`, `position`, `department`, `employment_status`, `date_hired`, `salary_type`, `salary_amount`, `contact_no`, `daily_rate`, `status`, `monthly_salary`, `rfid_uid`, `photo`, `rate_per_hour`) VALUES
(16, 'Byeon Wooseok', 'Engineer', 'Engineering', 'Regular', '2026-05-07', 'Monthly', 30000.00, '09154782744', NULL, 'Active', 0.00, '0067119092', 'wooseok123.jpg', 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `etching_jobs`
--

CREATE TABLE `etching_jobs` (
  `id` int(11) NOT NULL,
  `work_order_id` varchar(50) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `design` varchar(100) DEFAULT NULL,
  `operator` varchar(100) DEFAULT NULL,
  `date_etched` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) DEFAULT 0,
  `status` varchar(50) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `expense_date` date DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspection_qc`
--

CREATE TABLE `inspection_qc` (
  `id` int(11) NOT NULL,
  `work_order_id` varchar(50) NOT NULL,
  `item_id` int(11) NOT NULL,
  `inspector` varchar(100) DEFAULT NULL,
  `status` enum('Good','No Good') NOT NULL,
  `remarks` text DEFAULT NULL,
  `date_inspected` timestamp NOT NULL DEFAULT current_timestamp(),
  `passed_qty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `item_name` varchar(150) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `unit` varchar(30) DEFAULT 'pcs',
  `quantity` int(11) NOT NULL DEFAULT 0,
  `reorder_level` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `cost` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `status` varchar(20) DEFAULT 'active',
  `value` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `item_code`, `item_name`, `category`, `unit`, `quantity`, `reorder_level`, `created_at`, `cost`, `selling_price`, `status`, `value`, `image`) VALUES
(52, NULL, 'FFC 90 CC', 'Raw Material', 'pcs', 99, 50, '2026-05-04 19:11:26', 5.00, 0.00, 'active', 495.00, 'ffc90cc.jpg'),
(53, NULL, 'Gold Plated', 'Component', 'set', 99, 40, '2026-05-04 19:11:26', 10.00, 0.00, 'active', 990.00, 'goldplated.jpg'),
(54, NULL, 'ZIF acrylics', 'Supply', 'ml', 499, 200, '2026-05-04 19:11:26', 0.50, 0.00, 'active', 249.50, 'zifacrylics.jpg'),
(55, NULL, 'Copper wires', 'Supply', 'meters', 296, 100, '2026-05-04 19:11:26', 2.00, 0.00, 'active', 592.00, 'copperwires.jpg'),
(56, NULL, 'FFC 88 CF', 'Raw Material', 'pcs', 120, 60, '2026-05-04 19:11:26', 6.00, 0.00, 'active', 720.00, 'ffc88cf.jpg'),
(57, NULL, 'Gold Plated Opti', 'Component', 'set', 120, 50, '2026-05-04 19:11:26', 9.00, 0.00, 'active', 1080.00, 'goldplatedopti.jpg'),
(58, NULL, 'OFX glue', 'Supply', 'ml', 400, 150, '2026-05-04 19:11:26', 0.60, 0.00, 'active', 240.00, 'ofxglue.jpg'),
(59, NULL, 'Optical lamination', 'Supply', 'sheet', 200, 80, '2026-05-04 19:11:26', 3.00, 0.00, 'active', 600.00, 'opticallamination.jpg'),
(60, NULL, 'FFC 28 RN', 'Raw Material', 'pcs', 200, 100, '2026-05-04 19:11:26', 4.00, 0.00, 'active', 800.00, 'ffc28rn.jpg'),
(61, NULL, 'Rolled wind', 'Component', 'set', 200, 80, '2026-05-04 19:11:26', 6.00, 0.00, 'active', 1200.00, 'rollerwind.jpg'),
(62, NULL, 'YZF400 hot melt', 'Supply', 'grams', 600, 250, '2026-05-04 19:11:26', 0.40, 0.00, 'active', 240.00, 'yzf400hotmelt.jpg'),
(63, NULL, 'Semi-complex', 'Supply', 'pack', 150, 60, '2026-05-04 19:11:26', 2.00, 0.00, 'active', 300.00, 'semicomplex.jpg'),
(64, NULL, 'FFC 12 MM', 'Raw Material', 'pcs', 147, 70, '2026-05-04 19:11:26', 3.00, 0.00, 'active', 441.00, 'ffc12mm.jpg'),
(65, NULL, 'CD ROM', 'Component', 'unit', 77, 30, '2026-05-04 19:11:26', 15.00, 0.00, 'active', 1155.00, 'cdrom.jpg'),
(66, NULL, 'Escutcheon', 'Component', 'pcs', 98, 40, '2026-05-04 19:11:26', 2.00, 0.00, 'active', 196.00, 'escutcheon.jpg'),
(67, NULL, 'PCB adhesive', 'Supply', 'ml', 299, 120, '2026-05-04 19:11:26', 0.70, 0.00, 'active', 209.30, 'pcbadhesive.jpg'),
(68, NULL, 'FFC 0.4 BD', 'Raw Material', 'pcs', 175, 90, '2026-05-04 19:11:26', 5.00, 0.00, 'active', 875.00, 'ffc04bd.jpg'),
(69, NULL, 'Nickel', 'Component', 'grams', 495, 200, '2026-05-04 19:11:26', 1.00, 0.00, 'active', 495.00, 'nickel.jpg'),
(70, NULL, 'Slim OBD', 'Component', 'pcs', 116, 60, '2026-05-04 19:11:26', 4.00, 0.00, 'active', 464.00, 'slimobd.jpg'),
(71, NULL, 'Gold acrylic', 'Supply', 'ml', 345, 140, '2026-05-04 19:11:26', 0.80, 0.00, 'active', 276.00, 'goldacrylic.jpg'),
(72, NULL, 'FFC 56 DCF', 'Raw Material', 'pcs', 159, 80, '2026-05-04 19:11:26', 6.00, 0.00, 'active', 954.00, 'ffc56dcf.jpg'),
(73, NULL, 'YAG laser', 'Component', 'process', 99, 50, '2026-05-04 19:11:26', 12.00, 0.00, 'active', 1188.00, 'yaglaser.jpg'),
(74, NULL, 'Narrow pitch', 'Component', 'pcs', 140, 60, '2026-05-04 19:11:26', 3.00, 0.00, 'active', 420.00, 'narrowpitch.jpg'),
(75, NULL, 'Hot bar soldering', 'Supply', 'operation', 100, 40, '2026-05-04 19:11:26', 5.00, 0.00, 'active', 500.00, 'hotbarsoldering.jpg'),
(76, NULL, 'FFC 128 CUC', 'Raw Material', 'pcs', 170, 80, '2026-05-04 19:11:26', 5.00, 0.00, 'active', 850.00, 'ffc128cuc.jpg'),
(77, NULL, 'Black color', 'Component', 'batch', 100, 50, '2026-05-04 19:11:26', 3.00, 0.00, 'active', 300.00, 'blackcolor.jpg'),
(78, NULL, 'BK FFC', 'Component', 'pcs', 150, 70, '2026-05-04 19:11:26', 4.00, 0.00, 'active', 600.00, 'bkffc.jpg'),
(79, NULL, 'Hot melt insulation', 'Supply', 'grams', 500, 200, '2026-05-04 19:11:26', 0.50, 0.00, 'active', 250.00, 'hotmeltinsulation.jpg'),
(80, NULL, 'FFC 56 EMI', 'Raw Material', 'pcs', 139, 70, '2026-05-04 19:11:26', 6.00, 0.00, 'active', 834.00, 'ffc56emi.jpg'),
(81, NULL, 'Silver', 'Component', 'grams', 299, 120, '2026-05-04 19:11:26', 2.00, 0.00, 'active', 598.00, 'silver.jpg'),
(82, NULL, 'HF EMI', 'Component', 'pcs', 109, 50, '2026-05-04 19:11:26', 5.00, 0.00, 'active', 545.00, 'hfemi.jpg'),
(83, NULL, 'Adhesive glue', 'Supply', 'ml', 499, 150, '2026-05-04 19:11:26', 0.60, 0.00, 'active', 299.40, 'adhesiveglue.jpg'),
(87, NULL, 'Slim-flex', 'Finished Good', 'pcs', 2500, 100, '2026-05-04 19:14:10', 70.00, 110.00, 'active', 175000.00, 'slimflex.jpg'),
(88, NULL, 'Fine pitch FFC', 'Finished Good', 'pcs', 2500, 100, '2026-05-04 19:14:10', 130.00, 200.00, 'active', 325000.00, 'finepitch.jpg'),
(89, NULL, 'Delcon flex', 'Finished Good', 'pcs', 2500, 100, '2026-05-04 19:14:10', 130.00, 200.00, 'active', 325000.00, 'delconflex.jpg'),
(90, NULL, 'Black-ffc', 'Finished Good', 'pcs', 2500, 100, '2026-05-04 19:14:10', 110.00, 170.00, 'active', 275000.00, 'blackffc.jpg'),
(91, NULL, 'Emi-bloc', 'Finished Good', 'pcs', 2500, 100, '2026-05-04 19:14:10', 110.00, 170.00, 'active', 275000.00, 'emiblock.jpg'),
(93, NULL, 'Gold Plating', 'Finished Good', 'pcs', 2501, 100, '2026-05-06 10:45:47', 300.00, 450.00, 'active', 750300.00, 'goldplating.jpg'),
(94, NULL, 'Opti-flex', 'Finished Good', 'pcs', 1000, 50, '2026-05-06 10:59:57', 90.00, 140.00, 'active', 90000.00, 'optiflex.jpg'),
(95, NULL, 'Bulk FFC', 'Finished Good', 'roll', 2500, 100, '2026-05-06 11:03:08', 500.00, 750.00, 'active', 1250000.00, 'bulkffc.jpg');

--
-- Triggers `inventory_items`
--
DELIMITER $$
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `lamination_jobs`
--

CREATE TABLE `lamination_jobs` (
  `id` int(11) NOT NULL,
  `work_order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `material` varchar(255) NOT NULL,
  `operator` varchar(255) DEFAULT NULL,
  `date_laminated` date DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `user_id`, `action`, `description`, `timestamp`) VALUES
(2, 3, 'Add User', 'Added user: jovan (Role: Production)', '2026-04-08 14:12:44'),
(3, 12, 'Delete User', 'Deleted user: jovan (ID: 11)', '2026-04-08 19:06:54');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `order_details` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(100) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `work_order_id` int(11) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `proof_of_payment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `client_name`, `contact`, `order_details`, `quantity`, `status`, `date_created`, `email`, `client_id`, `created_at`, `work_order_id`, `payment_method`, `payment_status`, `proof_of_payment`) VALUES
(48, NULL, NULL, 'Fine pitch FFC', 1, 'Confirmed', '2026-05-11 11:50:25', NULL, 5, '2026-05-11 11:50:25', NULL, 'Cash', 'Pending Payment', ''),
(49, NULL, NULL, 'Gold Plating', 1, 'Confirmed', '2026-05-11 11:51:54', NULL, 5, '2026-05-11 11:51:54', NULL, 'GCash', 'Paid', '1778500314_gcash_qr.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `packing_jobs`
--

CREATE TABLE `packing_jobs` (
  `id` int(11) NOT NULL,
  `work_order_id` varchar(50) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity_packed` int(11) NOT NULL,
  `packer` varchar(100) DEFAULT NULL,
  `date_packed` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `days_worked` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overtime_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `overtime_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `deductions` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `net_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `pay_type` varchar(20) NOT NULL DEFAULT 'REGULAR',
  `basic_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `overtime_pay` decimal(10,2) NOT NULL DEFAULT 0.00,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sss` decimal(10,2) NOT NULL DEFAULT 0.00,
  `philhealth` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pagibig` decimal(10,2) NOT NULL DEFAULT 0.00,
  `other_deductions` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_history`
--

CREATE TABLE `production_history` (
  `id` int(11) NOT NULL,
  `wo_id` int(11) DEFAULT NULL,
  `wo_no` varchar(50) DEFAULT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `client` varchar(100) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `date_started` date DEFAULT NULL,
  `date_finished` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `production_history`
--

INSERT INTO `production_history` (`id`, `wo_id`, `wo_no`, `product_name`, `client`, `qty`, `selling_price`, `status`, `remarks`, `date_started`, `date_finished`, `created_at`) VALUES
(1, 5, 'WO-2026-0001', 'FFC 20 Pin', 'Samsung', 200, 250.00, 'Completed', NULL, '2026-03-16', '2026-03-18', '2026-03-18 13:03:36'),
(2, 6, 'WO-2026-0006', 'FFC 30 Pin', 'Brother Inc.', 20, 150.00, 'Completed', NULL, '2026-03-18', '2026-03-18', '2026-03-18 13:03:36'),
(3, 7, 'WO-2026-0007', 'FFC 40 Pin', 'Mitsubishi', 200, 500.00, 'Completed', NULL, '2026-03-18', '2026-03-18', '2026-03-18 13:03:36');

-- --------------------------------------------------------

--
-- Table structure for table `product_process_materials`
--

CREATE TABLE `product_process_materials` (
  `id` int(11) NOT NULL,
  `product_name` varchar(100) DEFAULT NULL,
  `process` varchar(50) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_process_materials`
--

INSERT INTO `product_process_materials` (`id`, `product_name`, `process`, `item_id`) VALUES
(1, 'Gold plating', 'Cutting', 52),
(2, 'Gold plating', 'Etching', 53),
(3, 'Gold plating', 'Adhesive', 54),
(4, 'Gold plating', 'Lamination', 55),
(5, 'Opti-flex', 'Cutting', 56),
(6, 'Opti-flex', 'Etching', 53),
(7, 'Opti-flex', 'Adhesive', 58),
(8, 'Opti-flex', 'Lamination', 59),
(9, 'Bulk FFC', 'Cutting', 60),
(10, 'Bulk FFC', 'Etching', 61),
(11, 'Bulk FFC', 'Adhesive', 62),
(12, 'Bulk FFC', 'Lamination', 63),
(13, 'Slim-flex', 'Cutting', 64),
(14, 'Slim-flex', 'Etching', 65),
(15, 'Slim-flex', 'Adhesive', 67),
(16, 'Slim-flex', 'Lamination', 66),
(17, 'Fine pitch FFC', 'Cutting', NULL),
(18, 'Fine pitch FFC', 'Etching', 69),
(19, 'Fine pitch FFC', 'Adhesive', 71),
(20, 'Fine pitch FFC', 'Lamination', 70),
(21, 'Delcon flex', 'Cutting', 72),
(22, 'Delcon flex', 'Etching', 73),
(23, 'Delcon flex', 'Adhesive', 75),
(24, 'Delcon flex', 'Lamination', 74),
(25, 'Black-ffc', 'Cutting', 76),
(26, 'Black-ffc', 'Etching', 77),
(27, 'Black-ffc', 'Adhesive', 79),
(28, 'Black-ffc', 'Lamination', 78),
(29, 'Emi-bloc', 'Cutting', 80),
(30, 'Emi-bloc', 'Etching', 81),
(31, 'Emi-bloc', 'Adhesive', 83),
(32, 'Emi-bloc', 'Lamination', 82);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `movement_type` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `movement_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `thirteenth_month`
--

CREATE TABLE `thirteenth_month` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_basic_salary` decimal(12,2) DEFAULT 0.00,
  `thirteenth_amount` decimal(12,2) DEFAULT 0.00,
  `generated_by` int(11) DEFAULT NULL,
  `date_generated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'Admin',
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `password`, `role`, `status`) VALUES
(3, 'admin', 'System Administrator', '$2y$10$fW7TiiAWFOYO.PSwVGeA2uwZN37uo4Kr7ovWfJKglkxn.i7Ecba5G', 'Admin', 'Active'),
(5, 'meai', NULL, '$2y$10$f1w56eaLbftFJ.8UmbvpxOpn3ab46oVycvM3CNpCNeRNQZx6VH3eO', 'Production', 'active'),
(6, 'lovely', NULL, '$2y$10$tVEt7/a5Pdc7VCS6yzr8kO1EWKV17K2KIkl62WTy9rhrQiFWL3mZW', 'Accounting', 'active'),
(7, 'airish', NULL, '$2y$10$nl3kdqGaJT4dy7OtLGIEI.NoFHTE3r9ApS5t.I3m.ByZ0N4BP0ad.', 'Engineer', 'active'),
(9, 'may', NULL, '$2y$10$byKoHpqLdRX7l5DO3oPND.OYkbAGdwU02dJKTsF714kf/XG05UZ9.', 'Staff', 'active'),
(10, 'meyay', NULL, '$2y$10$S8Cz4iF53P73b.lxAGGGhOozVYwFb/k9rgO/fodJRki5PzFYEINCe', 'Production', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `work_orders`
--

CREATE TABLE `work_orders` (
  `id` int(11) NOT NULL,
  `wo_no` varchar(50) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `client_name` varchar(150) DEFAULT NULL,
  `qty` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT NULL,
  `date_started` date NOT NULL,
  `date_completed` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `selling_price` decimal(10,2) DEFAULT 0.00,
  `order_id` int(11) DEFAULT NULL,
  `current_stage` varchar(50) DEFAULT NULL,
  `client_email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_orders`
--

INSERT INTO `work_orders` (`id`, `wo_no`, `product_name`, `client_name`, `qty`, `status`, `date_started`, `date_completed`, `remarks`, `created_at`, `selling_price`, `order_id`, `current_stage`, `client_email`) VALUES
(70, 'WO-2026-0001', 'Fine pitch FFC', 'Brother Industry', 1, 'Confirmed', '2026-05-11', NULL, NULL, '2026-05-11 11:51:08', 200.00, 48, 'Cutting', NULL),
(71, 'WO-2026-0071', 'Gold Plating', 'Brother Industry', 1, 'Confirmed', '2026-05-11', NULL, NULL, '2026-05-11 11:52:14', 450.00, 49, 'Cutting', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `adhesive_jobs`
--
ALTER TABLE `adhesive_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_emp_date` (`employee_id`,`att_date`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cutting_jobs`
--
ALTER TABLE `cutting_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dr_no` (`dr_no`),
  ADD KEY `wo_id` (`wo_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rfid_uid` (`rfid_uid`);

--
-- Indexes for table `etching_jobs`
--
ALTER TABLE `etching_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inspection_qc`
--
ALTER TABLE `inspection_qc`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_name` (`item_name`);

--
-- Indexes for table `lamination_jobs`
--
ALTER TABLE `lamination_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_client` (`client_id`);

--
-- Indexes for table `packing_jobs`
--
ALTER TABLE `packing_jobs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payroll_employee` (`employee_id`);

--
-- Indexes for table `production_history`
--
ALTER TABLE `production_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_process_materials`
--
ALTER TABLE `product_process_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `thirteenth_month`
--
ALTER TABLE `thirteenth_month`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_emp_year` (`employee_id`,`year`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wo_no` (`wo_no`),
  ADD KEY `fk_order` (`order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounting_transactions`
--
ALTER TABLE `accounting_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=136;

--
-- AUTO_INCREMENT for table `adhesive_jobs`
--
ALTER TABLE `adhesive_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cutting_jobs`
--
ALTER TABLE `cutting_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `etching_jobs`
--
ALTER TABLE `etching_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspection_qc`
--
ALTER TABLE `inspection_qc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `lamination_jobs`
--
ALTER TABLE `lamination_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `packing_jobs`
--
ALTER TABLE `packing_jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `production_history`
--
ALTER TABLE `production_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_process_materials`
--
ALTER TABLE `product_process_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=189;

--
-- AUTO_INCREMENT for table `thirteenth_month`
--
ALTER TABLE `thirteenth_month`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `work_orders`
--
ALTER TABLE `work_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`wo_id`) REFERENCES `work_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `work_orders`
--
ALTER TABLE `work_orders`
  ADD CONSTRAINT `fk_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
