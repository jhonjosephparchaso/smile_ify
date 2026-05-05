-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 09:10 AM
-- Server version: 10.11.14-MariaDB-0+deb12u2
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `s18100807_smileify_mock`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('General','Closed') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `title`, `description`, `type`) VALUES
(1, 'Christmas Break', 'Closed for Christmas break', 'Closed'),
(2, 'New Year Break', 'Closed for New Year', 'Closed'),
(3, 'Open for Holidays', 'We are open this holiday!', 'General'),
(4, 'Close for Holiday', 'Staff is having christmas party', 'Closed');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_services`
--

CREATE TABLE `appointment_services` (
  `appointment_services_id` int(11) NOT NULL,
  `appointment_transaction_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_services`
--

INSERT INTO `appointment_services` (`appointment_services_id`, `appointment_transaction_id`, `service_id`, `quantity`, `date_created`) VALUES
(1, 1, 2, 1, '2025-12-10 05:40:49'),
(2, 1, 3, 1, '2025-12-10 05:40:49'),
(3, 2, 3, 1, '2025-12-10 05:50:05'),
(4, 2, 4, 1, '2025-12-10 05:50:05'),
(5, 3, 2, 1, '2025-12-10 05:57:42'),
(6, 3, 6, 1, '2025-12-10 05:57:42'),
(7, 4, 2, 1, '2025-12-10 06:14:18'),
(8, 5, 2, 1, '2025-12-10 06:15:26'),
(9, 6, 3, 1, '2025-12-10 06:16:45'),
(10, 7, 3, 1, '2025-12-10 06:18:27'),
(11, 8, 2, 1, '2025-12-10 06:29:22'),
(12, 8, 4, 1, '2025-12-10 06:29:22'),
(13, 9, 6, 1, '2025-12-10 06:30:05'),
(14, 10, 5, 1, '2025-12-10 06:31:28'),
(15, 11, 3, 1, '2025-12-10 06:33:02'),
(17, 13, 3, 1, '2025-12-10 11:26:11'),
(18, 12, 5, 1, '2025-12-10 11:32:23'),
(19, 14, 4, 1, '2025-12-10 11:47:33'),
(20, 15, 2, 1, '2025-12-11 00:59:34'),
(21, 16, 2, 1, '2025-12-11 01:22:06');

-- --------------------------------------------------------

--
-- Table structure for table `appointment_transaction`
--

CREATE TABLE `appointment_transaction` (
  `appointment_transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `notes` text NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL,
  `status` enum('Booked','Completed','Cancelled') NOT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_transaction`
--

INSERT INTO `appointment_transaction` (`appointment_transaction_id`, `user_id`, `branch_id`, `dentist_id`, `appointment_date`, `appointment_time`, `notes`, `date_created`, `date_updated`, `status`, `reminder_sent`) VALUES
(1, 5, 2, 1, '2025-12-11', '09:00:00', '', '2025-12-09 21:40:49', '2025-12-10 06:22:21', 'Completed', 0),
(2, 6, 2, 1, '2025-12-11', '10:30:00', '', '2025-12-09 21:50:05', '2025-12-10 06:24:48', 'Completed', 0),
(3, 7, 2, 1, '2025-12-11', '13:00:00', '', '2025-12-09 21:57:42', '2025-12-10 06:24:53', 'Cancelled', 0),
(4, 7, 2, 1, '2025-12-22', '13:00:00', '', '2025-12-09 22:14:18', '2025-12-10 06:25:11', 'Cancelled', 0),
(5, 7, 3, 3, '2025-12-23', '13:00:00', '', '2025-12-09 22:15:26', '2025-12-10 06:40:19', 'Cancelled', 0),
(6, 8, 3, 3, '2025-12-22', '14:00:00', '', '2025-12-09 22:16:45', '2025-12-10 06:40:14', 'Completed', 0),
(7, 9, 1, 2, '2025-12-23', '09:00:00', '', '2025-12-09 22:18:27', '2025-12-10 06:27:00', 'Cancelled', 0),
(8, 10, 1, 3, '2025-12-11', '12:30:00', '', '2025-12-09 22:29:22', '2025-12-10 06:34:33', 'Completed', 0),
(9, 11, 3, 2, '2025-12-11', '09:00:00', '', '2025-12-09 22:30:05', '2025-12-10 06:39:37', 'Completed', 0),
(10, 6, 1, 3, '2025-12-19', '09:00:00', '', '2025-12-09 22:31:28', '2025-12-10 06:35:57', 'Completed', 0),
(11, 11, 2, 1, '2025-12-10', '11:30:00', 'cancel', '2025-12-09 22:33:02', '2025-12-10 11:31:30', 'Cancelled', 0),
(12, 10, 2, 1, '2025-12-11', '12:30:00', 'resched and supplies', '2025-12-09 22:44:21', '2025-12-10 11:37:10', 'Completed', 0),
(13, 14, 2, 1, '2025-12-12', '10:00:00', '', '2025-12-10 03:26:11', '2025-12-11 07:20:42', 'Completed', 0),
(14, 5, 2, 1, '2025-12-13', '11:30:00', '', '2025-12-10 03:47:33', '2025-12-11 08:51:55', 'Completed', 0),
(15, 15, 2, 1, '2025-12-15', '11:00:00', 'Mild discomfort', '2025-12-10 16:59:34', '2025-12-11 08:08:35', 'Completed', 0),
(16, 16, 2, 1, '2025-12-15', '11:30:00', 'Mild discomfort', '2025-12-10 17:22:06', NULL, 'Booked', 0);

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `branch_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `nickname` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `map_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `name`, `nickname`, `address`, `phone_number`, `status`, `date_created`, `date_updated`, `map_url`) VALUES
(1, 'Pakna-an, Mandaue City (Main Branch)', 'Mandaue', 'Jayme Street, Zone Ube, Pakna-an, Mandaue City', '9273505830', 'Active', '2025-12-10 04:11:24', '2025-12-10 04:11:24', 'https://maps.app.goo.gl/pMai2KSgVv3hj1tZA'),
(2, 'Babag 2, Lapu-Lapu City', 'Babag 2', '2nd Floor RM Arcade, Babag 2, Lapu-Lapu City', '9273505830', 'Active', '2025-12-10 04:12:48', '2025-12-10 04:12:48', 'https://maps.app.goo.gl/8okHqFg5fRn4xMyV6'),
(3, 'Pusok, Lapu-Lapu City', 'Pusok', 'Modejar Building (Room 306), City Hall Road, Pusok, Lapu-Lapu City', '9273505830', 'Active', '2025-12-10 04:13:28', '2025-12-10 04:13:28', 'https://maps.app.goo.gl/EUQVQkys2wSabHwKA'),
(4, 'ADC - Pajo', 'Pajo Branch', 'Punta Rizal Street, Pajo, Lapu-Lapu City, Cebu', '9273505830', 'Active', '2025-12-10 11:00:34', '2025-12-10 11:01:27', 'https://maps.app.goo.gl/mzhNBDsAAM44pcSY6');

-- --------------------------------------------------------

--
-- Table structure for table `branch_announcements`
--

CREATE TABLE `branch_announcements` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_announcements`
--

INSERT INTO `branch_announcements` (`id`, `announcement_id`, `branch_id`, `status`, `start_date`, `end_date`, `date_created`, `date_updated`) VALUES
(1, 1, 2, 'Inactive', '2025-12-23', '2025-12-26', '2025-12-10 05:12:57', '2025-12-11 04:34:44'),
(2, 2, 2, 'Active', '2025-12-30', '2026-01-02', '2025-12-10 05:13:53', '2025-12-10 05:13:53'),
(3, 3, 3, 'Active', '2025-12-23', '2026-01-02', '2025-12-10 05:23:21', '2025-12-10 05:23:21'),
(4, 4, 2, 'Active', '2025-12-11', '2025-12-11', '2025-12-10 11:23:15', '2025-12-10 11:23:15');

-- --------------------------------------------------------

--
-- Table structure for table `branch_promo`
--

CREATE TABLE `branch_promo` (
  `branch_promo_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `promo_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_promo`
--

INSERT INTO `branch_promo` (`branch_promo_id`, `branch_id`, `promo_id`, `status`, `start_date`, `end_date`) VALUES
(1, 4, 1, 'Active', NULL, NULL),
(2, 2, 1, 'Active', NULL, NULL),
(3, 1, 1, 'Active', NULL, NULL),
(4, 3, 1, 'Active', NULL, NULL),
(5, 4, 2, 'Active', '2025-12-12', '2026-01-12'),
(6, 2, 2, 'Active', '2025-12-12', '2026-01-12'),
(7, 1, 2, 'Active', '2025-12-12', '2026-01-12'),
(8, 3, 2, 'Active', '2025-12-12', '2026-01-12');

-- --------------------------------------------------------

--
-- Table structure for table `branch_service`
--

CREATE TABLE `branch_service` (
  `branch_services_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_service`
--

INSERT INTO `branch_service` (`branch_services_id`, `branch_id`, `service_id`, `status`, `date_created`, `date_updated`) VALUES
(1, 2, 1, 'Active', '2025-12-10 04:15:26', '2025-12-10 04:15:26'),
(2, 1, 1, 'Active', '2025-12-10 04:15:26', '2025-12-10 04:15:26'),
(3, 3, 1, 'Active', '2025-12-10 04:15:26', '2025-12-10 04:15:26'),
(4, 2, 2, 'Active', '2025-12-10 04:16:01', '2025-12-10 04:16:01'),
(5, 1, 2, 'Active', '2025-12-10 04:16:01', '2025-12-10 04:16:01'),
(6, 3, 2, 'Active', '2025-12-10 04:16:01', '2025-12-10 04:16:01'),
(7, 2, 3, 'Active', '2025-12-10 04:17:41', '2025-12-10 04:17:41'),
(8, 1, 3, 'Active', '2025-12-10 04:17:41', '2025-12-10 04:17:41'),
(9, 3, 3, 'Active', '2025-12-10 04:17:41', '2025-12-10 04:17:41'),
(10, 2, 4, 'Active', '2025-12-10 04:17:56', '2025-12-10 04:17:56'),
(11, 1, 4, 'Active', '2025-12-10 04:17:56', '2025-12-10 04:17:56'),
(12, 3, 4, 'Active', '2025-12-10 04:17:56', '2025-12-10 04:17:56'),
(13, 2, 5, 'Active', '2025-12-10 04:18:12', '2025-12-10 04:18:12'),
(14, 1, 5, 'Active', '2025-12-10 04:18:12', '2025-12-10 04:18:12'),
(15, 3, 5, 'Active', '2025-12-10 04:18:12', '2025-12-10 04:18:12'),
(16, 2, 6, 'Active', '2025-12-10 04:21:46', '2025-12-10 04:21:46'),
(17, 1, 6, 'Active', '2025-12-10 04:21:46', '2025-12-10 04:21:46'),
(18, 3, 6, 'Active', '2025-12-10 04:21:46', '2025-12-10 04:21:46'),
(19, 4, 7, 'Active', '2025-12-10 11:17:47', '2025-12-10 11:17:47'),
(20, 2, 7, 'Active', '2025-12-10 11:17:47', '2025-12-11 02:59:11'),
(21, 1, 7, 'Active', '2025-12-10 11:17:47', '2025-12-10 11:17:47');

-- --------------------------------------------------------

--
-- Table structure for table `branch_supply`
--

CREATE TABLE `branch_supply` (
  `branch_supplies_id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `supply_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 0,
  `expiration_date` date DEFAULT NULL,
  `status` enum('Available','Out of Stock') DEFAULT 'Available',
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch_supply`
--

INSERT INTO `branch_supply` (`branch_supplies_id`, `branch_id`, `supply_id`, `quantity`, `reorder_level`, `expiration_date`, `status`, `date_created`, `date_updated`) VALUES
(1, 2, 1, 475, 10, NULL, 'Available', '2025-12-10 06:55:32', '2025-12-11 08:08:35'),
(2, 2, 2, 95, 5, NULL, 'Available', '2025-12-10 06:57:17', '2025-12-10 11:37:10'),
(3, 2, 3, 49, 50, NULL, 'Available', '2025-12-10 11:30:29', '2025-12-11 08:51:55');

-- --------------------------------------------------------

--
-- Table structure for table `dental_prescription`
--

CREATE TABLE `dental_prescription` (
  `prescription_id` int(11) NOT NULL,
  `appointment_transaction_id` int(11) NOT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `drug` varchar(255) NOT NULL,
  `route` varchar(50) DEFAULT NULL,
  `frequency` varchar(50) DEFAULT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `quantity` varchar(50) NOT NULL,
  `instructions` text DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dental_prescription`
--

INSERT INTO `dental_prescription` (`prescription_id`, `appointment_transaction_id`, `admin_user_id`, `drug`, `route`, `frequency`, `dosage`, `duration`, `quantity`, `instructions`, `date_created`, `date_updated`) VALUES
(1, 1, 2, 'Amoxicillin', NULL, 'once a day', '60', '3 days', '3', 'Before Meal', '2025-12-09 22:22:02', '2025-12-10 06:22:02'),
(2, 2, 2, 'Medicol', NULL, 'every 8 hours', '100', '2 days', '5', 'Take for headache', '2025-12-09 22:24:43', '2025-12-10 06:24:43'),
(3, 12, 2, 'Amox', NULL, 'once a day', '100', '1 week', '7', 'take before lunch', '2025-12-10 03:36:18', '2025-12-10 11:36:18'),
(4, 13, 2, 'Amoxicillin', NULL, 'Every 8 hours', '500 mg', '7 days', '21', 'Take after meals. Complete the full course of medication.', '2025-12-10 22:41:03', '2025-12-11 06:41:03'),
(5, 13, 2, 'Paracetamol', NULL, 'Every 6 hours as needed', '500 mg', '5 days', '20', 'Take after meals. Do not exceed 4 tablets per day.', '2025-12-10 22:41:19', '2025-12-11 06:41:19'),
(6, 15, 2, 'Ibuprofen', NULL, 'Every 8 hours', '400 mg', '5 days', '15', 'Take after meals. Avoid if with stomach problems.', '2025-12-11 00:08:30', '2025-12-11 08:08:30');

-- --------------------------------------------------------

--
-- Table structure for table `dental_tips`
--

CREATE TABLE `dental_tips` (
  `tip_id` int(11) NOT NULL,
  `tip_text` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dental_transaction`
--

CREATE TABLE `dental_transaction` (
  `dental_transaction_id` int(11) NOT NULL,
  `appointment_transaction_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `promo_id` int(11) DEFAULT NULL,
  `promo_name` varchar(255) DEFAULT NULL,
  `promo_type` enum('percentage','fixed') DEFAULT NULL,
  `promo_value` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('Cash','Cashless') NOT NULL,
  `cashless_receipt` varchar(255) DEFAULT NULL,
  `xray_file` varchar(255) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `additional_payment` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `medcert_status` enum('None','Requested','Eligible','Issued','Expired') NOT NULL,
  `medcert_receipt` varchar(255) DEFAULT NULL,
  `fitness_status` varchar(255) DEFAULT NULL,
  `diagnosis` varchar(255) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `medcert_notes` text DEFAULT NULL,
  `medcert_requested_date` datetime DEFAULT NULL,
  `medcert_request_payment` decimal(10,2) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL,
  `prescription_downloaded` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dental_transaction`
--

INSERT INTO `dental_transaction` (`dental_transaction_id`, `appointment_transaction_id`, `dentist_id`, `admin_user_id`, `promo_id`, `promo_name`, `promo_type`, `promo_value`, `payment_method`, `cashless_receipt`, `xray_file`, `total`, `additional_payment`, `notes`, `medcert_status`, `medcert_receipt`, `fitness_status`, `diagnosis`, `remarks`, `medcert_notes`, `medcert_requested_date`, `medcert_request_payment`, `date_created`, `date_updated`, `prescription_downloaded`) VALUES
(1, 1, 1, 2, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 1150.00, 100.00, '', 'Eligible', '/images/payments/medcert_payments/1_talle.jpg', '1 week', '', '', '', '2025-12-10 11:53:56', 150.00, '2025-12-09 22:20:45', '2025-12-10 11:53:56', 0),
(2, 2, 1, 2, NULL, NULL, NULL, NULL, 'Cashless', '/images/payments/cashless_payments/2_asibal.png', NULL, 1400.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-09 22:23:06', '2025-12-10 06:24:48', 0),
(3, 8, 3, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 1550.00, 500.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-09 22:34:00', '2025-12-10 06:34:33', 0),
(4, 10, 3, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 41000.00, 1000.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-09 22:34:59', '2025-12-10 06:35:57', 0),
(5, 9, 2, 4, NULL, NULL, NULL, NULL, 'Cashless', '/images/payments/cashless_payments/5_laroa.png', NULL, 70500.00, 500.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-09 22:38:21', '2025-12-10 06:39:37', 0),
(6, 6, 3, 4, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-09 22:39:48', '2025-12-10 06:40:14', 0),
(7, 12, 1, 2, NULL, NULL, NULL, NULL, 'Cashless', '/images/payments/cashless_payments/7_brain.webp', 'images/transactions/xrays/7_brain.jpg', 46500.00, 500.00, '', 'Issued', NULL, '1 week', '', '', '', '2025-12-10 11:41:08', 150.00, '2025-12-10 03:34:28', '2025-12-10 11:41:20', 0),
(8, 13, 1, 2, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-10 19:04:45', '2025-12-11 07:20:42', 0),
(9, 15, 1, 2, 1, 'Senior Citizen Discount', 'percentage', 20.00, 'Cashless', '/images/payments/cashless_payments/9_zamora.png', 'images/transactions/xrays/9_zamora.jpg', 5600.00, 500.00, 'Recommended 6-month recall.', 'Issued', NULL, '2 Days', 'Irreversible pulpitis', 'Advise rest and follow post-treatment instructions.', NULL, '2025-12-11 08:08:35', NULL, '2025-12-11 00:07:03', '2025-12-11 08:13:41', 0),
(10, 14, 1, 2, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-11 00:31:37', '2025-12-11 08:51:55', 0);

-- --------------------------------------------------------

--
-- Table structure for table `dental_transaction_services`
--

CREATE TABLE `dental_transaction_services` (
  `id` int(11) NOT NULL,
  `dental_transaction_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) DEFAULT NULL,
  `service_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `additional_payment` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dental_transaction_services`
--

INSERT INTO `dental_transaction_services` (`id`, `dental_transaction_id`, `service_id`, `service_name`, `service_price`, `quantity`, `additional_payment`) VALUES
(1, 1, 2, 'Check Up/Consultation', 350.00, 1, 100.00),
(2, 1, 3, 'Cleaning', 700.00, 1, 0.00),
(3, 2, 3, 'Cleaning', 700.00, 1, 0.00),
(4, 2, 4, 'Tooth Filling', 700.00, 1, 0.00),
(5, 3, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(6, 3, 4, 'Tooth Filling', 700.00, 1, 500.00),
(7, 4, 5, 'Braces (Simple)', 40000.00, 1, 1000.00),
(8, 5, 6, 'Braces (Complicated)', 70000.00, 1, 500.00),
(9, 6, 3, 'Cleaning', 700.00, 1, 0.00),
(10, 7, 5, 'Braces (Simple)', 40000.00, 1, 500.00),
(11, 7, 7, 'Endodontics', 6000.00, 1, 0.00),
(17, 8, 3, 'Cleaning', 700.00, 1, 0.00),
(18, 9, 1, 'Dental Certificate', 150.00, 1, 0.00),
(19, 9, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(20, 9, 7, 'Endodontics', 6000.00, 1, 500.00),
(21, 10, 4, 'Tooth Filling', 700.00, 1, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `dental_vital`
--

CREATE TABLE `dental_vital` (
  `vitals_id` int(11) NOT NULL,
  `appointment_transaction_id` int(11) NOT NULL,
  `admin_user_id` int(11) DEFAULT NULL,
  `body_temp` decimal(4,1) DEFAULT NULL,
  `pulse_rate` int(11) DEFAULT NULL,
  `respiratory_rate` int(11) DEFAULT NULL,
  `blood_pressure` varchar(10) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `is_swelling` enum('Yes','No') NOT NULL,
  `is_bleeding` enum('Yes','No') NOT NULL,
  `is_sensitive` enum('Yes','No') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dental_vital`
--

INSERT INTO `dental_vital` (`vitals_id`, `appointment_transaction_id`, `admin_user_id`, `body_temp`, `pulse_rate`, `respiratory_rate`, `blood_pressure`, `height`, `weight`, `is_swelling`, `is_bleeding`, `is_sensitive`, `date_created`, `date_updated`) VALUES
(1, 1, 2, 36.0, 100, 100, '120/80', 170.00, 80.00, 'No', 'No', 'No', '2025-12-09 22:21:09', '2025-12-10 06:21:09'),
(2, 2, 2, 36.0, 110, 110, '124/80', 159.00, 49.00, 'No', 'Yes', 'No', '2025-12-09 22:23:35', '2025-12-10 06:23:35'),
(3, 8, 3, 36.0, 100, 120, '110/90', 180.00, 98.00, 'No', 'Yes', 'Yes', '2025-12-09 22:34:31', '2025-12-10 06:34:31'),
(4, 10, 3, 38.0, 100, 100, '120/80', 180.00, 90.00, 'No', 'No', 'No', '2025-12-09 22:35:54', '2025-12-10 06:35:54'),
(5, 9, 4, 36.0, 120, 120, '120/80', 159.00, 79.00, 'No', 'No', 'No', '2025-12-09 22:38:56', '2025-12-10 06:38:56'),
(7, 6, 4, 36.0, 100, 100, '120/80', 145.00, 42.00, 'No', 'No', 'No', '2025-12-09 22:40:11', '2025-12-10 06:40:11'),
(8, 12, 2, 36.0, 100, 100, '120/80', 165.00, 80.00, 'No', 'No', 'No', '2025-12-10 03:35:22', '2025-12-10 11:35:22'),
(9, 13, 2, 36.7, 72, 18, '120/80', 168.00, 65.00, 'No', 'No', 'No', '2025-12-10 22:27:04', '2025-12-11 06:27:04'),
(10, 15, 2, 36.5, 78, 20, '118/76', 170.00, 68.00, 'No', 'No', 'No', '2025-12-11 00:04:46', '2025-12-11 08:04:46'),
(11, 14, 2, 36.5, 78, 20, '118/76', 170.00, 65.00, 'No', 'No', 'No', '2025-12-11 00:29:24', '2025-12-11 08:29:24');

-- --------------------------------------------------------

--
-- Table structure for table `dentist`
--

CREATE TABLE `dentist` (
  `dentist_id` int(11) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `date_of_birth` varchar(255) NOT NULL,
  `date_of_birth_iv` text DEFAULT NULL,
  `date_of_birth_tag` text DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `contact_number` varchar(255) NOT NULL,
  `contact_number_iv` text DEFAULT NULL,
  `contact_number_tag` text DEFAULT NULL,
  `license_number` varchar(255) NOT NULL,
  `license_number_iv` text DEFAULT NULL,
  `license_number_tag` text DEFAULT NULL,
  `date_started` date DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `signature_image` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist`
--

INSERT INTO `dentist` (`dentist_id`, `last_name`, `middle_name`, `first_name`, `gender`, `date_of_birth`, `date_of_birth_iv`, `date_of_birth_tag`, `email`, `contact_number`, `contact_number_iv`, `contact_number_tag`, `license_number`, `license_number_iv`, `license_number_tag`, `date_started`, `status`, `signature_image`, `profile_image`, `date_created`, `date_updated`) VALUES
(1, 'Solante', '', 'Ben', 'Male', 'PWnPRBCEt/C4LQ==', 'Sa2voa2u+Uhy52hg', 'eVozv7ktnIV1QRrQsZzLRQ==', 'rixieliep@gmail.com', 'asqbn48C+G+X9g==', 'w3XKP9b5xoHDknbZ', 'Cyt4dcYCKSk+/lwoL65IcQ==', 'zfFKTiT6zg==', 'AB24AcCow+Uyhy+v', '9B9/h2xvLEhEaSmQ9vST+Q==', '2025-11-27', 'Active', '1_solante_signature.png', '1_solante_profile.jpg', '2025-12-09 20:53:47', '2025-12-10 05:07:09'),
(2, 'Arriesgado', '', 'Irish', 'Female', 'laKjO+60B7zziQ==', 'Pd6el1irKQTjCbTa', 'z4qnURrwBcv/Xk5chhmjJA==', 'rixieliep@gmail.com', 'BWBgl0fI2nZTAQ==', 'PVYEJUbQQeRsF96/', 'TqbplKFpI016YtXBeMnmBw==', 'tcET/L1v8w==', 'QcMTMBNCAaaHj/DC', 'IUpZU679FvOg9kBzqSbjgQ==', '2025-11-27', 'Active', '2_arriesgado_signature.png', NULL, '2025-12-09 20:56:38', '2025-12-10 04:56:38'),
(3, 'Guinita', '', 'Brent Louisse', 'Female', '5VArJRZyssYRvA==', 'piDYP4E8Y1jpqXHC', 'ILuUMRAayGS2iS+e6PR4Kw==', 'rixieliep@gmail.com', 'X6boB4bvW8vuZw==', 'TbYI/ag1ySIRKwD2', 'ZEvGWhAKSSJwqYtX5Jd9FQ==', 'ku0AO/6Mrg==', 'CsmEi9F6fHW5xAFX', 'ZQBixkv1v6W/CAfMWK7QTA==', '2025-11-27', 'Active', '3_guinita_signature.png', NULL, '2025-12-09 21:01:30', '2025-12-10 05:01:30'),
(4, 'Buhayan', 'Base', 'Jenmel', 'Female', 'o6BDRsI//h3ivw==', '9RytTVk6ugvjKTgc', 'yvw4yFcU13airaH8FPw24A==', 'jbuhayan@gmail.com', 'uZ5I9b3z58pkaw==', 'L22RmcvRh1WvvX5H', 'MsKZXToQ78dko8wnRkaj3g==', 'dcsaQTZdjA==', 'CQI04sen2lqojF/j', 'CBSFxtpm+wonDrFc+IafCw==', '2025-12-11', 'Active', '4_buhayan_signature.png', '4_buhayan_profile.jpg', '2025-12-10 03:15:53', '2025-12-10 11:15:53');

-- --------------------------------------------------------

--
-- Table structure for table `dentist_branch`
--

CREATE TABLE `dentist_branch` (
  `dentist_branch_id` int(11) NOT NULL,
  `dentist_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist_branch`
--

INSERT INTO `dentist_branch` (`dentist_branch_id`, `dentist_id`, `branch_id`) VALUES
(1, 1, 2),
(2, 1, 1),
(3, 1, 3),
(4, 2, 2),
(5, 2, 1),
(6, 2, 3),
(7, 3, 2),
(8, 3, 1),
(9, 3, 3),
(10, 4, 4),
(11, 4, 2);

-- --------------------------------------------------------

--
-- Table structure for table `dentist_schedule`
--

CREATE TABLE `dentist_schedule` (
  `schedule_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist_schedule`
--

INSERT INTO `dentist_schedule` (`schedule_id`, `dentist_id`, `day`, `branch_id`, `start_time`, `end_time`) VALUES
(8, 2, 'Monday', 1, '09:00:00', '16:30:00'),
(9, 2, 'Tuesday', 1, '09:00:00', '16:30:00'),
(10, 2, 'Wednesday', 1, '09:00:00', '16:30:00'),
(11, 2, 'Thursday', 3, '09:00:00', '16:30:00'),
(12, 2, 'Friday', 3, '09:00:00', '16:30:00'),
(13, 3, 'Monday', 3, '09:00:00', '16:30:00'),
(14, 3, 'Tuesday', 3, '09:00:00', '16:30:00'),
(15, 3, 'Wednesday', 3, '09:00:00', '16:30:00'),
(16, 3, 'Thursday', 1, '12:30:00', '16:30:00'),
(17, 3, 'Friday', 1, '09:00:00', '16:30:00'),
(18, 3, 'Saturday', 1, '09:00:00', '16:30:00'),
(33, 1, 'Monday', 2, '09:00:00', '16:30:00'),
(34, 1, 'Tuesday', 2, '09:00:00', '16:30:00'),
(35, 1, 'Wednesday', 2, '09:00:00', '12:00:00'),
(36, 1, 'Wednesday', 3, '14:00:00', '16:30:00'),
(37, 1, 'Thursday', 2, '09:00:00', '16:30:00'),
(38, 1, 'Friday', 2, '09:00:00', '16:30:00'),
(39, 1, 'Saturday', 2, '09:00:00', '16:30:00'),
(40, 4, 'Monday', 4, '09:00:00', '13:00:00'),
(41, 4, 'Monday', 2, '13:30:00', '16:30:00'),
(42, 4, 'Tuesday', 4, '09:00:00', '16:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `dentist_service`
--

CREATE TABLE `dentist_service` (
  `dentist_services_id` int(11) NOT NULL,
  `dentist_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dentist_service`
--

INSERT INTO `dentist_service` (`dentist_services_id`, `dentist_id`, `service_id`) VALUES
(1, 1, 6),
(2, 1, 5),
(3, 1, 2),
(4, 1, 3),
(5, 1, 1),
(6, 1, 4),
(7, 2, 6),
(8, 2, 5),
(9, 2, 2),
(10, 2, 3),
(11, 2, 1),
(12, 2, 4),
(13, 3, 6),
(14, 3, 5),
(15, 3, 2),
(16, 3, 3),
(17, 3, 1),
(18, 3, 4),
(19, 4, 6),
(20, 4, 5),
(21, 4, 2),
(22, 4, 3),
(23, 4, 1),
(24, 4, 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `is_read`, `date_created`) VALUES
(1, 2, 'Your Secretary account has been created. Branch Assignment: Babag 2, Lapu-Lapu City. Username: Juban_J', 1, '2025-12-09 20:31:20'),
(2, 3, 'Your Secretary account has been created. Branch Assignment: Pakna-an, Mandaue City (Main Branch). Username: Conde_P', 1, '2025-12-09 20:34:23'),
(3, 4, 'Your Secretary account has been created. Branch Assignment: Pusok, Lapu-Lapu City. Username: Quinto_K', 1, '2025-12-09 20:36:35'),
(4, 1, 'A new announcement titled \'Christmas Break\' was added for Babag 2, Lapu-Lapu City.', 1, '2025-12-09 21:12:57'),
(5, 1, 'A new announcement titled \'New Year Break\' was added for Babag 2, Lapu-Lapu City.', 1, '2025-12-09 21:13:53'),
(6, 2, 'Your password was changed successfully on December 10, 2025, 5:19 am. If this wasn’t you, please contact the clinic immediately.', 1, '2025-12-09 21:19:37'),
(7, 2, 'Your password was changed successfully on December 10, 2025, 5:21 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 21:21:21'),
(8, 1, 'A new announcement titled \'Open for Holidays\' was added for Pusok, Lapu-Lapu City.', 1, '2025-12-09 21:23:21'),
(9, 5, 'Welcome to Smile-ify! Your account was created.', 1, '2025-12-09 21:40:49'),
(10, 5, 'Your appointment on 2025-12-11 at 09:00 was successfully booked.', 1, '2025-12-09 21:40:49'),
(11, 5, 'Your password was changed successfully on December 10, 2025, 5:44 am. If this wasn’t you, please contact the clinic immediately.', 1, '2025-12-09 21:44:30'),
(12, 5, 'Your email was successfully updated to josephparchaso@gmail.com on December 10, 2025, 5:45 am. If this wasn’t you, please contact the clinic immediately.', 1, '2025-12-09 21:45:13'),
(13, 6, 'Welcome to Smile-ify! Your account was created.', 0, '2025-12-09 21:50:05'),
(14, 6, 'Your appointment on 2025-12-11 at 10:30 was successfully booked.', 0, '2025-12-09 21:50:05'),
(15, 6, 'Your password was changed successfully on December 10, 2025, 5:51 am. If this wasn’t you, please contact clinic immediately.', 0, '2025-12-09 21:51:34'),
(16, 7, 'Welcome to Smile-ify! Your account was created.', 1, '2025-12-09 21:57:42'),
(17, 7, 'Your appointment on 2025-12-11 at 13:00 was successfully booked.', 1, '2025-12-09 21:57:42'),
(18, 7, 'Your password was changed successfully on December 10, 2025, 5:59 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 21:59:09'),
(19, 3, 'Your password was changed successfully on December 10, 2025, 6:06 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 22:06:11'),
(20, 4, 'Your password was changed successfully on December 10, 2025, 6:06 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 22:06:57'),
(21, 7, 'Your appointment on 2025-12-22 at 13:00 was successfully booked!', 1, '2025-12-09 22:14:18'),
(22, 7, 'Your appointment on 2025-12-23 at 13:00 was successfully booked!', 1, '2025-12-09 22:15:26'),
(23, 8, 'Your appointment on 2025-12-22 at 14:00 was successfully booked!', 0, '2025-12-09 22:16:45'),
(24, 7, 'Your dependent\'s appointment on 2025-12-22 at 14:00 was successfully booked.', 1, '2025-12-09 22:16:45'),
(25, 9, 'Your appointment on 2025-12-23 at 09:00 was successfully booked!', 0, '2025-12-09 22:18:27'),
(26, 7, 'Your dependent\'s appointment on 2025-12-23 at 09:00 was successfully booked.', 1, '2025-12-09 22:18:27'),
(27, 5, 'Your appointment (December 11, 2025 at 9:00 AM) has been marked as completed. Thank you for visiting!', 1, '2025-12-09 22:22:21'),
(28, 6, 'Your appointment (December 11, 2025 at 10:30 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-09 22:24:48'),
(29, 7, 'Your appointment (December 11, 2025 at 1:00 PM) has been cancelled.', 1, '2025-12-09 22:24:53'),
(30, 7, 'Your appointment (December 22, 2025 at 1:00 PM) has been cancelled.', 1, '2025-12-09 22:25:11'),
(31, 3, 'Your password was changed successfully on December 10, 2025, 6:26 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 22:26:19'),
(32, 9, 'Your appointment (December 23, 2025 at 9:00 AM) has been cancelled.', 0, '2025-12-09 22:27:00'),
(33, 7, 'The appointment for your dependent (December 23, 2025 at 9:00 AM) has been cancelled.', 1, '2025-12-09 22:27:00'),
(34, 10, 'Welcome to Smile-ify! Your account was created.', 1, '2025-12-09 22:29:22'),
(35, 10, 'Your appointment on 2025-12-11 at 12:30 was successfully booked.', 1, '2025-12-09 22:29:22'),
(36, 11, 'Welcome to Smile-ify! Your account was created.', 1, '2025-12-09 22:30:05'),
(37, 11, 'Your appointment on 2025-12-11 at 09:00 was successfully booked.', 1, '2025-12-09 22:30:05'),
(38, 6, 'Your appointment on 2025-12-19 at 09:00 was successfully booked!', 0, '2025-12-09 22:31:28'),
(39, 11, 'Your appointment on 2025-12-10 at 11:30 was successfully booked!', 1, '2025-12-09 22:33:02'),
(40, 10, 'Your appointment (December 11, 2025 at 12:30 PM) has been marked as completed. Thank you for visiting!', 1, '2025-12-09 22:34:33'),
(41, 6, 'Your appointment (December 19, 2025 at 9:00 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-09 22:35:57'),
(42, 4, 'Your password was changed successfully on December 10, 2025, 6:37 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 22:37:32'),
(43, 11, 'Your appointment (December 11, 2025 at 9:00 AM) has been marked as completed. Thank you for visiting!', 1, '2025-12-09 22:39:37'),
(44, 8, 'Your appointment (December 22, 2025 at 2:00 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-09 22:40:14'),
(45, 7, 'The appointment for your dependent (December 22, 2025 at 2:00 PM) has been marked as completed.', 1, '2025-12-09 22:40:14'),
(46, 7, 'Your appointment (December 23, 2025 at 1:00 PM) has been cancelled.', 1, '2025-12-09 22:40:19'),
(47, 10, 'Your appointment on 2025-12-11 at 09:00 was successfully booked!', 1, '2025-12-09 22:44:21'),
(48, 10, 'Your password was changed successfully on December 10, 2025, 6:47 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 22:47:37'),
(49, 11, 'Your password was changed successfully on December 10, 2025, 6:50 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-09 22:50:17'),
(50, 1, 'Your password was changed successfully on December 10, 2025, 10:59 am. If this wasn’t you, please contact the clinic immediately.', 1, '2025-12-10 02:59:07'),
(51, 12, 'Your Secretary account has been created. Branch Assignment: ADC - Pajo. Username: Paring_C', 0, '2025-12-10 03:11:30'),
(52, 1, 'A new announcement titled \'Close for Holiday\' was added for Babag 2, Lapu-Lapu City.', 1, '2025-12-10 03:23:15'),
(53, 13, 'Welcome to Smile-ify! Your account was created.', 0, '2025-12-10 03:26:11'),
(54, 14, 'Your appointment on 2025-12-12 at 10:00 was successfully booked.', 0, '2025-12-10 03:26:11'),
(55, 13, 'Your dependent\'s appointment on 2025-12-12 at 10:00 was successfully booked.', 0, '2025-12-10 03:26:11'),
(56, 11, 'Your appointment (December 10, 2025 at 11:30 AM) has been cancelled.', 0, '2025-12-10 03:31:30'),
(57, 10, 'Your appointment has been rescheduled to December 11, 2025 at 12:30 PM.', 0, '2025-12-10 03:32:23'),
(58, 10, 'Your appointment (December 11, 2025 at 12:30 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-10 03:37:10'),
(59, 10, 'Your Dental Certificate request from your appointment on December 11, 2025 at 12:30 PM has been approved.', 0, '2025-12-10 03:41:08'),
(60, 1, 'The service Endodontics in Babag 2, Lapu-Lapu City was set to Inactive.', 1, '2025-12-10 03:43:13'),
(61, 1, 'The promo Senior Citizen Discount in Babag 2, Lapu-Lapu City was set to Inactive.', 1, '2025-12-10 03:43:47'),
(62, 5, 'Your appointment on 2025-12-13 at 11:30 was successfully booked!', 1, '2025-12-10 03:47:33'),
(63, 5, 'Your password was changed successfully on December 10, 2025, 11:50 am. If this wasn’t you, please contact the clinic immediately.', 1, '2025-12-10 03:50:45'),
(64, 2, 'Patient #5 Maxfrancis H. Talle has requested a Dental Certificate for transaction #1', 1, '2025-12-10 03:53:02'),
(65, 5, 'Your Dental Certificate request from your appointment on December 11, 2025 at 9:00 AM has been approved.', 1, '2025-12-10 03:53:56'),
(66, 5, 'Your password was changed successfully on December 11, 2025, 12:58 am. If this wasn’t you, please contact clinic immediately.', 0, '2025-12-10 16:58:02'),
(67, 15, 'Welcome to Smile-ify! Your account has been created.', 0, '2025-12-10 16:59:34'),
(68, 15, 'Your appointment on 2025-12-15 at 11:00 was successfully booked.', 0, '2025-12-10 16:59:34'),
(69, 5, 'Your password was changed successfully on December 11, 2025, 1:20 am. If this wasn’t you, please contact clinic immediately.', 0, '2025-12-10 17:20:38'),
(70, 16, 'Welcome to Smile-ify! Your account has been created.', 0, '2025-12-10 17:22:06'),
(71, 16, 'Your appointment on 2025-12-15 at 11:30 was successfully booked.', 0, '2025-12-10 17:22:06'),
(72, 1, 'Your password was changed successfully on December 11, 2025, 2:28 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-10 18:28:11'),
(73, 1, 'The service Endodontics in Babag 2, Lapu-Lapu City was set to Active.', 1, '2025-12-10 18:59:11'),
(74, 1, 'The promo Senior Citizen Discount in Babag 2, Lapu-Lapu City was set to Active.', 1, '2025-12-10 19:51:20'),
(75, 1, 'The announcement \'Christmas Break\' in Babag 2, Lapu-Lapu City was updated.', 1, '2025-12-10 20:34:44'),
(76, 14, 'Your appointment (December 12, 2025 at 10:00 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-10 23:20:42'),
(77, 13, 'The appointment for your dependent (December 12, 2025 at 10:00 AM) has been marked as completed.', 0, '2025-12-10 23:20:42'),
(78, 15, 'Your appointment (December 15, 2025 at 11:00 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-11 00:08:35');

-- --------------------------------------------------------

--
-- Table structure for table `promo`
--

CREATE TABLE `promo` (
  `promo_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo`
--

INSERT INTO `promo` (`promo_id`, `name`, `image_path`, `description`, `discount_type`, `discount_value`, `date_created`, `date_updated`) VALUES
(1, 'Senior Citizen Discount', '/images/promos/promo_1.png', 'Senior Citizen Discount for ages 60+', 'percentage', 20.00, '2025-12-10 11:19:56', '2025-12-11 03:51:20'),
(2, 'December discount', '/images/promos/promo_2.jpg', 'Happy Holidays Discount for the Month of December', 'fixed', 500.00, '2025-12-10 11:20:22', '2025-12-10 11:20:22');

-- --------------------------------------------------------

--
-- Table structure for table `qr_payment`
--

CREATE TABLE `qr_payment` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_payment`
--

INSERT INTO `qr_payment` (`id`, `file_name`, `file_path`, `uploaded_at`) VALUES
(1, 'qr_payment.webp', '/images/qr/qr_payment.webp', '2025-12-10 02:57:06');

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `service_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` double NOT NULL,
  `duration_minutes` int(11) NOT NULL DEFAULT 45,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `requires_xray` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service`
--

INSERT INTO `service` (`service_id`, `name`, `price`, `duration_minutes`, `date_created`, `date_updated`, `requires_xray`) VALUES
(1, 'Dental Certificate', 150, 0, '2025-12-10 04:15:26', '2025-12-10 11:40:27', 0),
(2, 'Check Up/Consultation', 350, 15, '2025-12-10 04:16:01', '2025-12-10 04:16:01', 0),
(3, 'Cleaning', 700, 45, '2025-12-10 04:17:41', '2025-12-10 04:17:41', 0),
(4, 'Tooth Filling', 700, 60, '2025-12-10 04:17:56', '2025-12-10 04:17:56', 0),
(5, 'Braces (Simple)', 40000, 120, '2025-12-10 04:18:12', '2025-12-10 04:18:12', 0),
(6, 'Braces (Complicated)', 70000, 150, '2025-12-10 04:21:46', '2025-12-10 04:21:46', 0),
(7, 'Endodontics', 6000, 90, '2025-12-10 11:17:47', '2025-12-11 02:59:11', 1);

-- --------------------------------------------------------

--
-- Table structure for table `service_supplies`
--

CREATE TABLE `service_supplies` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `supply_id` int(11) NOT NULL,
  `quantity_used` varchar(50) NOT NULL DEFAULT '1.00',
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_supplies`
--

INSERT INTO `service_supplies` (`id`, `service_id`, `branch_id`, `supply_id`, `quantity_used`, `date_created`, `date_updated`) VALUES
(3, 2, 2, 1, '5', '2025-12-09 22:55:32', '2025-12-10 06:55:32'),
(6, 6, 2, 2, '5', '2025-12-09 22:57:17', '2025-12-10 06:57:17'),
(7, 5, 2, 2, '5', '2025-12-09 22:57:17', '2025-12-10 06:57:17'),
(9, 4, 2, 3, '50', '2025-12-11 00:31:09', '2025-12-11 08:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `supply`
--

CREATE TABLE `supply` (
  `supply_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supply`
--

INSERT INTO `supply` (`supply_id`, `name`, `description`, `category`, `unit`) VALUES
(1, 'Mask', '', '', ''),
(2, 'Midazolam', '', '', 'mL '),
(3, 'Knife', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `guardian_id` int(11) DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `date_of_birth` varchar(255) DEFAULT NULL,
  `date_of_birth_iv` text DEFAULT NULL,
  `date_of_birth_tag` text DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `contact_number` varchar(255) DEFAULT NULL,
  `contact_number_iv` text DEFAULT NULL,
  `contact_number_tag` text DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `address_iv` text DEFAULT NULL,
  `address_tag` text DEFAULT NULL,
  `role` enum('owner','admin','patient') NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `date_started` date DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT current_timestamp(),
  `force_logout` tinyint(1) DEFAULT 0,
  `owner_flag` tinyint(1) GENERATED ALWAYS AS (case when `role` = 'owner' then 1 else NULL end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `guardian_id`, `username`, `password`, `last_name`, `middle_name`, `first_name`, `gender`, `date_of_birth`, `date_of_birth_iv`, `date_of_birth_tag`, `email`, `contact_number`, `contact_number_iv`, `contact_number_tag`, `address`, `address_iv`, `address_tag`, `role`, `branch_id`, `date_started`, `status`, `date_created`, `date_updated`, `force_logout`) VALUES
(1, NULL, 'smileify_owner', '$2y$10$TfyI44ZJWY.izArm0Js4bO2o1jqIssexDo/tqrpGFd4GPrNdsbvdm', 'Arriesgado', NULL, 'Irish', NULL, NULL, NULL, NULL, '18100807@usc.edu.ph', 'foxs8ecjtfOCrA==', 'WHNAWrt34kRNcrmX', 'mPJCz+sITGcp3uSwSxONxw==', NULL, NULL, NULL, 'owner', NULL, '2025-12-10', 'Active', '2025-12-09 19:59:03', '2025-12-10 10:59:07', 0),
(2, NULL, 'Juban_J', '$2y$10$3uIbOgHfXkxt4BWaEuVoH.WPZygHsD8xx2xMSVhM6H0PSzJC/gIe2', 'Juban', '', 'Jay Marie', 'Female', '+eYbRiHPMgxHIA==', 'WE/G3jX86X3fcM59', 'mkwpFMuuDpzDnSgGoBfSAg==', '18102727@usc.edu.ph', '6WtOzv0Pjm2dsA==', 'DH/9A2iotdm5xBKR', 'iKnX/DLJ2LM5O4xt4K1FjA==', 'iovqGJXyepFrFceM3w==', 'V1IlwXZQu1ct4C14', '28Vn5vfCoyGzYIbHtsELyA==', 'admin', 2, '2025-11-24', 'Active', '2025-12-09 20:31:20', '2025-12-10 05:19:37', 0),
(3, NULL, 'Conde_P', '$2y$10$Sy3C9GAhxcwZwRu4izdX0eQ1hJNXIyGxniaOF6SCTZX9lc3lGhiP.', 'Conde', '', 'Perlyn', 'Female', 'vg2XDPweblzr7w==', 'Pn+wrxGYt4kV4Nyw', '2vaCuZJtiln/T/vteYFlwA==', '18102727@usc.edu.ph', 'KupwBOd9bdc9OQ==', 'nuMfHZ/T3azCOXTW', 'KEkSieztwwEpOLqyDc8aqg==', 'B+dOdTumsO0t9J2RFaX6SOBw', 'NtoKfVSyFnWMHNWd', 'yystQ5hHfDOJDUfPKG11hQ==', 'admin', 1, '2025-11-25', 'Active', '2025-12-09 20:34:23', '2025-12-10 04:38:20', 0),
(4, NULL, 'Quinto_K', '$2y$10$yXrzl/0f9cqXV69xhMN9JORTgPHkGddU29pPwpj0Ge5K9qCp6NdOS', 'Quinto', '', 'Kimberly Jean', 'Female', '1M4vFZYYk3KxrA==', '0uF1eKA+KuAxuXno', 'TDhBdJyjm+Ds6DkO9FceVw==', '18102727@usc.edu.ph', 'NJgO4JbqOul1Dw==', 'lFr6Gs/N1xPmYipw', '8kjASbqnw/pg7ITS/LeJJw==', 'A0FX1LAWzfPZ8y7bSVhL0jIXt25C', '1F2kBQNli9R0Q/AW', 'iyGXZAyakjlVQULkSH9/OQ==', 'admin', 3, '2025-11-26', 'Active', '2025-12-09 20:36:35', '2025-12-10 04:38:20', 0),
(5, NULL, 'Talle_M', '$2y$10$h7fD2moXHsCMFABykqs8j.gBLcVtpBWWcn2FXd/b.1XJx/UR56g2.', 'Talle', 'Henerale', 'Maxfrancis', 'Male', 'P8y/ZlSuSAXYnw==', 'UN5qiZXyNu0qQbT6', 'MHHb3jkRqSMUdNE6BIncLQ==', 'josephparchaso@gmail.com', 'BUaUagvb3640BQ==', 'QHbKCH/gSlYN+8MO', 'XCp+amMNPfO4UU8niPTiqQ==', 'gsj9LwGAmb8XYh3BKSC6', 'yx4WTpfEX5aBzRMT', 'HQOaQC+gd4LQWPfgsMR3gA==', 'patient', 2, NULL, 'Active', '2025-12-09 21:40:49', '2025-12-10 11:50:45', 0),
(6, NULL, 'Asibal_A', '$2y$10$voU8WybsozOE/R10M9KEfuzShfa7cBthZme1dgoEqBWWrXSeTrRgm', 'Asibal', 'Llamido', 'Anne', 'Female', 'V6UyGxfEbw9fOQ==', '7Uzrozsvpt0hbWG4', 'kj6l34PaMDEY20Z4Bem6Nw==', 'josephparchaso@gmail.com', 'UJlu6YAkrie0+A==', 'pqNvS95H91kfP6Et', 'EYwN0eR/LIdoaLZNUEy0og==', NULL, NULL, NULL, 'patient', 2, NULL, 'Inactive', '2025-12-09 21:50:05', '2025-12-11 07:53:22', 0),
(7, NULL, 'Albarez_R', '$2y$10$J8JNF2SNSswKSYiAR3BH3.KA1m8ez4f/gK4oT1eSK.oypm6BrU9wG', 'Albarez', 'Remotigue', 'Rafelle Chisa Eve', 'Female', 'GtYTRn+EjYzfdQ==', 'uCr/fiPTJjJTNXgt', 'wKcIdTXjXOoUsC6Iyz2SAQ==', 'josephparchaso@gmail.com', '0TkwZpxvvuXQ2g==', 'a9IsiAetBt4anXeT', 'QPfhHTs5W346BYa+jDRT1A==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-09 21:57:42', '2025-12-10 06:15:26', 0),
(8, 7, NULL, NULL, 'Albarez', NULL, 'Alex', 'Male', 'uSQbujNDDc2f6A==', 'ihUjEwMA+O+p+E5w', 'kuEFZ4KOKGrlNpA1wUO+UQ==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 3, NULL, 'Active', '2025-12-09 22:16:45', '2025-12-10 06:16:45', 0),
(9, 7, NULL, NULL, 'Albarez', NULL, 'Solene', 'Female', 'hfsA6QcfQ1iLbw==', 'Ezu3d5cVFa+sRgM6', 'aHzQ9+qbNaHWjVb2ugEn6A==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 1, NULL, 'Active', '2025-12-09 22:18:27', '2025-12-10 06:18:27', 0),
(10, NULL, 'Brain_R', '$2y$10$iC4DL.kIpO8GWj4d1JQ5O.mzOp85T/uHSSA/D8vL/JTQy8bd5G3DW', 'Brain', '', 'Ricky', 'Male', '1W1WZBDa3v7+yQ==', 'Swz5ODrrmzVRiEMT', 'i9TEs9M4l9NySqfb9KfQew==', 'josephparchaso@gmail.com', 'SEWN0/GiUXMCMA==', 'xnUNw7xYCsZ7+Bln', 'penMLn50yO2DA6QPDitsqA==', NULL, NULL, NULL, 'patient', 1, NULL, 'Active', '2025-12-09 22:29:22', '2025-12-10 06:44:21', 0),
(11, NULL, 'Laroa_C', '$2y$10$rIDQKr7g2aRmXQbXLzphS.qFz6aHRqXpJ/lq.pWME5Ui04/6NbEKK', 'Laroa', '', 'Chico', 'Male', '/FapBrROEHP40Q==', 'zM4vpSVY+MRltYwa', 'YEleUn/HJMBt024m5fiaIA==', 'josephparchaso@gmail.com', 'ZRFisHAdxTTH7A==', 'HonGNUHor0yfkk6N', '/CfUsMEdQB9LIy/Kk1nYvQ==', NULL, NULL, NULL, 'patient', 3, NULL, 'Active', '2025-12-09 22:30:05', '2025-12-10 06:33:02', 0),
(12, NULL, 'Paring_C', '$2y$10$u5OiQdEmmarEyqB66TzfEufNYCCKwU0O/Wfu7OLpZevUuEDK4qgwu', 'Paring', 'Lener', 'Chresaian', 'Male', 'uMshm+w4rV3ZbA==', 'ciHwQ2b2K8zU6eKi', 'C3sXXB8iO6Ar5kaz5KKbTQ==', 'josephparchaso@gmail.com', 'JKM7T6e4jgb3Jg==', '4z+wIS/6sokFOGyJ', 'l95XGP350ziA+fOQr2/09Q==', 'EMYENA560zZlGV97gq7YEtFT7XXuuQ==', 'wNHCn675XLZzU8Bd', '0mqpbfShKMxywNTGpRDnpg==', 'admin', 4, '2025-12-11', 'Active', '2025-12-10 03:11:30', '2025-12-10 11:11:30', 0),
(13, NULL, 'Castillop_J', '$2y$10$LrRYrltycqRwM5gX4sDKY.WmWFSo1VLq9Z.7cuE95SNSuCjiOmjUu', 'Castillop', '', 'Jane', 'Female', 'EOLUJYP/Kry8Mg==', 'v6nGp9cYDM9o7gcI', 'aqU1hb7lMQlhx0CgDkoCPg==', '18102727@usc.edu.ph', '7SJ524zREmF52g==', 'iQTbXfQfIibddq06', 'JLRih3gIm5ktndnVug06AQ==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-10 03:26:11', '2025-12-10 11:26:11', 0),
(14, 13, NULL, NULL, 'Castillo', NULL, 'Shekinah', 'Female', 'mcpam2hkg8U2cg==', 'Ifmj3U23S1cz9vrb', 'ajVkhcNbx6TdLgk9i9r1BA==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-10 03:26:11', '2025-12-10 11:26:11', 0),
(15, NULL, 'Zamora_C', '$2y$10$iTnRkNb/KaDNwEKDBgyzue6wo.gsn7xQ.6pRJ29Khyy3eNNEjTanG', 'Zamora', '', 'Chester', 'Male', 'cXX8ikT3nRWWSA==', 'u/0ZO1kO7XWxofon', '0n5uVp9VF6wCpIF4sp005g==', 'josephparchaso@gmail.com', 'BYuUpRlMCB3rhg==', '/SgCri+YXMekbit1', 'rgMhke59qaGCkYn0uLvIfQ==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-10 16:59:34', '2025-12-11 08:09:14', 0),
(16, NULL, 'Fajardo_R', '$2y$10$JAuLnjTHbRJoEjf4V0yu9OfrOprbJ5H6Fwk60m7Zz1rCM4WmVoGK.', 'Fajardo', '', 'Rj', 'Male', 'vBywWY12p2m53Q==', '3Ixq6DXlJ66J9Tom', 'K/8s4G2E1WVOv9FHpmZvxw==', 'josephparchaso@gmail.com', '2yuVj32vJEt0aA==', 'YR0NixsG2nC5aFYU', 'ZFeG/0Xgy2vJZk1x2Sb4TQ==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-10 17:22:06', '2025-12-11 01:22:06', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`);

--
-- Indexes for table `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD PRIMARY KEY (`appointment_services_id`),
  ADD KEY `appointment_transaction_id` (`appointment_transaction_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `appointment_transaction`
--
ALTER TABLE `appointment_transaction`
  ADD PRIMARY KEY (`appointment_transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `dentist_id` (`dentist_id`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `branch_announcements`
--
ALTER TABLE `branch_announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_id` (`announcement_id`,`branch_id`),
  ADD KEY `fk_branch_announcements_branch` (`branch_id`);

--
-- Indexes for table `branch_promo`
--
ALTER TABLE `branch_promo`
  ADD PRIMARY KEY (`branch_promo_id`),
  ADD KEY `promo_id` (`promo_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `branch_service`
--
ALTER TABLE `branch_service`
  ADD PRIMARY KEY (`branch_services_id`),
  ADD KEY `fk_branch` (`branch_id`),
  ADD KEY `fk_service` (`service_id`);

--
-- Indexes for table `branch_supply`
--
ALTER TABLE `branch_supply`
  ADD PRIMARY KEY (`branch_supplies_id`),
  ADD KEY `supply_id` (`supply_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `dental_prescription`
--
ALTER TABLE `dental_prescription`
  ADD PRIMARY KEY (`prescription_id`),
  ADD KEY `appointment_transaction_id` (`appointment_transaction_id`),
  ADD KEY `fk_prescription_admin` (`admin_user_id`);

--
-- Indexes for table `dental_tips`
--
ALTER TABLE `dental_tips`
  ADD PRIMARY KEY (`tip_id`);

--
-- Indexes for table `dental_transaction`
--
ALTER TABLE `dental_transaction`
  ADD PRIMARY KEY (`dental_transaction_id`),
  ADD KEY `appointment_transaction_id` (`appointment_transaction_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `fk_dental_transaction_promo` (`promo_id`),
  ADD KEY `fk_dental_transaction_admin` (`admin_user_id`);

--
-- Indexes for table `dental_transaction_services`
--
ALTER TABLE `dental_transaction_services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dental_transaction_id` (`dental_transaction_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `dental_vital`
--
ALTER TABLE `dental_vital`
  ADD PRIMARY KEY (`vitals_id`),
  ADD KEY `appointment_transaction_id` (`appointment_transaction_id`),
  ADD KEY `fk_vital_admin` (`admin_user_id`);

--
-- Indexes for table `dentist`
--
ALTER TABLE `dentist`
  ADD PRIMARY KEY (`dentist_id`);

--
-- Indexes for table `dentist_branch`
--
ALTER TABLE `dentist_branch`
  ADD PRIMARY KEY (`dentist_branch_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `dentist_schedule`
--
ALTER TABLE `dentist_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `dentist_service`
--
ALTER TABLE `dentist_service`
  ADD PRIMARY KEY (`dentist_services_id`),
  ADD KEY `dentist_id` (`dentist_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `promo`
--
ALTER TABLE `promo`
  ADD PRIMARY KEY (`promo_id`);

--
-- Indexes for table `qr_payment`
--
ALTER TABLE `qr_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`service_id`);

--
-- Indexes for table `service_supplies`
--
ALTER TABLE `service_supplies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_service_supplies_service` (`service_id`),
  ADD KEY `fk_service_supplies_supply` (`supply_id`),
  ADD KEY `fk_service_supplies_branch` (`branch_id`);

--
-- Indexes for table `supply`
--
ALTER TABLE `supply`
  ADD PRIMARY KEY (`supply_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `index_username_unique` (`username`),
  ADD UNIQUE KEY `unique_owner` (`owner_flag`),
  ADD KEY `fk_users_branch` (`branch_id`),
  ADD KEY `fk_guardian_user` (`guardian_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `appointment_services`
--
ALTER TABLE `appointment_services`
  MODIFY `appointment_services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `appointment_transaction`
--
ALTER TABLE `appointment_transaction`
  MODIFY `appointment_transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `branch_announcements`
--
ALTER TABLE `branch_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `branch_promo`
--
ALTER TABLE `branch_promo`
  MODIFY `branch_promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `branch_service`
--
ALTER TABLE `branch_service`
  MODIFY `branch_services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `branch_supply`
--
ALTER TABLE `branch_supply`
  MODIFY `branch_supplies_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `dental_prescription`
--
ALTER TABLE `dental_prescription`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `dental_tips`
--
ALTER TABLE `dental_tips`
  MODIFY `tip_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dental_transaction`
--
ALTER TABLE `dental_transaction`
  MODIFY `dental_transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `dental_transaction_services`
--
ALTER TABLE `dental_transaction_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `dental_vital`
--
ALTER TABLE `dental_vital`
  MODIFY `vitals_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `dentist`
--
ALTER TABLE `dentist`
  MODIFY `dentist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dentist_branch`
--
ALTER TABLE `dentist_branch`
  MODIFY `dentist_branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `dentist_schedule`
--
ALTER TABLE `dentist_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `dentist_service`
--
ALTER TABLE `dentist_service`
  MODIFY `dentist_services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `promo`
--
ALTER TABLE `promo`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `qr_payment`
--
ALTER TABLE `qr_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `service_supplies`
--
ALTER TABLE `service_supplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `supply`
--
ALTER TABLE `supply`
  MODIFY `supply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_services`
--
ALTER TABLE `appointment_services`
  ADD CONSTRAINT `appointment_services_ibfk_1` FOREIGN KEY (`appointment_transaction_id`) REFERENCES `appointment_transaction` (`appointment_transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `appointment_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `appointment_transaction`
--
ALTER TABLE `appointment_transaction`
  ADD CONSTRAINT `appointment_transaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `appointment_transaction_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`),
  ADD CONSTRAINT `appointment_transaction_ibfk_4` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`dentist_id`);

--
-- Constraints for table `branch_announcements`
--
ALTER TABLE `branch_announcements`
  ADD CONSTRAINT `fk_branch_announcements_announcement` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`announcement_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_branch_announcements_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `branch_promo`
--
ALTER TABLE `branch_promo`
  ADD CONSTRAINT `branch_promo_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `promo` (`promo_id`),
  ADD CONSTRAINT `branch_promo_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `branch_service`
--
ALTER TABLE `branch_service`
  ADD CONSTRAINT `fk_branch` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_service` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_supply`
--
ALTER TABLE `branch_supply`
  ADD CONSTRAINT `branch_supply_ibfk_1` FOREIGN KEY (`supply_id`) REFERENCES `supply` (`supply_id`),
  ADD CONSTRAINT `branch_supply_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `dental_prescription`
--
ALTER TABLE `dental_prescription`
  ADD CONSTRAINT `dental_prescription_ibfk_1` FOREIGN KEY (`appointment_transaction_id`) REFERENCES `appointment_transaction` (`appointment_transaction_id`),
  ADD CONSTRAINT `fk_prescription_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `dental_transaction`
--
ALTER TABLE `dental_transaction`
  ADD CONSTRAINT `dental_transaction_ibfk_1` FOREIGN KEY (`appointment_transaction_id`) REFERENCES `appointment_transaction` (`appointment_transaction_id`),
  ADD CONSTRAINT `dental_transaction_ibfk_2` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`dentist_id`),
  ADD CONSTRAINT `fk_dental_transaction_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dental_transaction_promo` FOREIGN KEY (`promo_id`) REFERENCES `promo` (`promo_id`) ON DELETE SET NULL;

--
-- Constraints for table `dental_transaction_services`
--
ALTER TABLE `dental_transaction_services`
  ADD CONSTRAINT `dental_transaction_services_ibfk_1` FOREIGN KEY (`dental_transaction_id`) REFERENCES `dental_transaction` (`dental_transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dental_transaction_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`);

--
-- Constraints for table `dental_vital`
--
ALTER TABLE `dental_vital`
  ADD CONSTRAINT `dental_vital_ibfk_1` FOREIGN KEY (`appointment_transaction_id`) REFERENCES `appointment_transaction` (`appointment_transaction_id`),
  ADD CONSTRAINT `fk_vital_admin` FOREIGN KEY (`admin_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `dentist_branch`
--
ALTER TABLE `dentist_branch`
  ADD CONSTRAINT `dentist_branch_ibfk_1` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`dentist_id`),
  ADD CONSTRAINT `dentist_branch_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `dentist_schedule`
--
ALTER TABLE `dentist_schedule`
  ADD CONSTRAINT `dentist_schedule_ibfk_1` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`dentist_id`),
  ADD CONSTRAINT `dentist_schedule_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`branch_id`);

--
-- Constraints for table `dentist_service`
--
ALTER TABLE `dentist_service`
  ADD CONSTRAINT `dentist_service_ibfk_1` FOREIGN KEY (`dentist_id`) REFERENCES `dentist` (`dentist_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dentist_service_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_guardian_user` FOREIGN KEY (`guardian_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
