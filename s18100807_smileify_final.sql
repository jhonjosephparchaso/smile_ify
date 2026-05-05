-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 16, 2025 at 07:13 AM
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
-- Database: `s18100807_smileify_final`
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
(2, 'Reopening', 'Regular clinic operations will resume on December 27', 'General'),
(3, 'Open for Holidays', 'We are open this holiday season', 'General'),
(4, 'Staff Christmas Party', 'Staff Celebrating Christmas Party', 'Closed'),
(5, 'Closed for Christmas', '', 'Closed');

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
(1, 1, 2, 1, '2025-12-12 05:24:30'),
(2, 1, 3, 1, '2025-12-12 05:24:30'),
(3, 2, 2, 1, '2025-12-12 05:36:20'),
(4, 2, 4, 1, '2025-12-12 05:36:20'),
(5, 2, 7, 1, '2025-12-12 05:36:20'),
(6, 3, 4, 1, '2025-12-12 05:37:15'),
(7, 3, 6, 1, '2025-12-12 05:37:15'),
(8, 4, 5, 1, '2025-12-12 05:37:39'),
(9, 5, 3, 1, '2025-12-12 05:38:07'),
(10, 6, 4, 1, '2025-12-12 05:38:20'),
(11, 7, 2, 1, '2025-12-12 05:39:00'),
(12, 7, 5, 1, '2025-12-12 05:39:00'),
(13, 8, 3, 1, '2025-12-12 05:57:41'),
(14, 9, 4, 1, '2025-12-12 05:58:01'),
(15, 9, 7, 1, '2025-12-12 05:58:01'),
(16, 10, 5, 1, '2025-12-12 05:58:18'),
(17, 11, 2, 1, '2025-12-12 05:58:33'),
(18, 12, 3, 1, '2025-12-12 06:05:39'),
(19, 12, 6, 1, '2025-12-12 06:05:39'),
(20, 13, 6, 1, '2025-12-12 06:06:13'),
(21, 14, 2, 1, '2025-12-12 06:06:30'),
(22, 15, 3, 1, '2025-12-12 09:34:48'),
(23, 16, 4, 1, '2025-12-12 09:35:05'),
(24, 17, 5, 1, '2025-12-12 09:35:28'),
(25, 18, 5, 1, '2025-12-12 09:35:45'),
(26, 19, 3, 1, '2025-12-12 09:36:00'),
(27, 20, 2, 1, '2025-12-12 09:36:15'),
(28, 20, 4, 1, '2025-12-12 09:36:15'),
(29, 21, 2, 1, '2025-12-12 15:08:00'),
(30, 22, 2, 1, '2025-12-12 15:09:47'),
(32, 24, 3, 1, '2025-12-12 15:13:49'),
(33, 25, 2, 1, '2025-12-12 15:33:52'),
(34, 26, 2, 1, '2025-12-12 15:34:29'),
(35, 26, 4, 1, '2025-12-12 15:34:29'),
(36, 27, 4, 1, '2025-12-12 15:35:18'),
(37, 28, 6, 1, '2025-12-12 15:52:00'),
(38, 29, 5, 1, '2025-12-12 15:57:25'),
(39, 30, 2, 1, '2025-12-12 16:01:10'),
(40, 30, 4, 1, '2025-12-12 16:01:10'),
(41, 31, 2, 1, '2025-12-12 16:05:01'),
(42, 31, 4, 1, '2025-12-12 16:05:01'),
(43, 31, 7, 1, '2025-12-12 16:05:01'),
(46, 33, 2, 1, '2025-12-12 17:17:38'),
(47, 33, 3, 1, '2025-12-12 17:17:38'),
(48, 32, 2, 1, '2025-12-12 17:22:42'),
(49, 32, 3, 1, '2025-12-12 17:22:42'),
(50, 34, 2, 1, '2025-12-12 17:27:24'),
(51, 35, 4, 1, '2025-12-14 01:59:39'),
(52, 36, 7, 1, '2025-12-14 03:04:27'),
(53, 37, 6, 1, '2025-12-14 03:38:54'),
(54, 38, 3, 1, '2025-12-14 03:48:15'),
(55, 39, 3, 1, '2025-12-14 04:04:25'),
(56, 40, 3, 1, '2025-12-14 19:35:13'),
(57, 41, 2, 1, '2025-12-15 00:26:50'),
(58, 23, 2, 1, '2025-12-15 01:03:02'),
(59, 42, 3, 1, '2025-12-15 02:00:13'),
(60, 42, 6, 1, '2025-12-15 02:00:13'),
(61, 43, 2, 1, '2025-12-15 05:10:14'),
(62, 43, 4, 1, '2025-12-15 05:10:14'),
(63, 43, 6, 1, '2025-12-15 05:10:14'),
(64, 44, 6, 1, '2025-12-15 05:49:53'),
(65, 45, 3, 1, '2025-12-15 07:27:04'),
(66, 46, 1, 1, '2025-12-15 08:05:19'),
(67, 47, 8, 1, '2025-12-15 08:43:12'),
(68, 48, 4, 1, '2025-12-15 17:55:00'),
(69, 49, 3, 1, '2025-12-15 18:15:23'),
(70, 49, 4, 1, '2025-12-15 18:15:23'),
(71, 49, 5, 1, '2025-12-15 18:15:23'),
(72, 49, 8, 1, '2025-12-15 18:15:23'),
(73, 50, 1, 1, '2025-12-15 23:17:16'),
(74, 50, 2, 1, '2025-12-15 23:17:16'),
(75, 51, 3, 1, '2025-12-16 04:40:21');

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
  `status` enum('Booked','Pending Reschedule','Completed','Cancelled') NOT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointment_transaction`
--

INSERT INTO `appointment_transaction` (`appointment_transaction_id`, `user_id`, `branch_id`, `dentist_id`, `appointment_date`, `appointment_time`, `notes`, `date_created`, `date_updated`, `status`, `reminder_sent`) VALUES
(1, 6, 2, 1, '2025-11-12', '09:00:00', 'First time visit', '2025-11-11 21:24:30', '2025-11-11 05:53:13', 'Completed', 0),
(2, 8, 2, 3, '2025-11-03', '09:00:00', '', '2025-11-02 21:36:20', '2025-12-12 05:49:10', 'Completed', 0),
(3, 6, 2, 4, '2025-11-06', '10:30:00', '', '2025-11-05 21:37:15', '2025-12-12 05:43:44', 'Cancelled', 0),
(4, 6, 2, 1, '2025-11-12', '14:30:00', '', '2025-11-11 21:37:39', '2025-12-12 05:45:02', 'Completed', 0),
(5, 6, 2, 1, '2025-11-13', '09:00:00', '', '2025-11-12 21:38:07', '2025-12-12 05:50:17', 'Completed', 0),
(6, 6, 2, 1, '2025-11-24', '10:00:00', '', '2025-11-23 21:38:20', '2025-12-12 05:52:26', 'Completed', 0),
(7, 6, 2, 3, '2025-11-24', '09:00:00', '', '2025-11-23 21:39:00', '2025-12-12 05:51:26', 'Completed', 0),
(8, 8, 3, 3, '2025-11-17', '09:00:00', '', '2025-11-16 21:57:41', '2025-12-12 05:59:15', 'Cancelled', 0),
(9, 8, 3, 5, '2025-11-19', '10:00:00', '', '2025-11-18 21:58:01', '2025-12-12 06:00:26', 'Completed', 0),
(10, 8, 3, 2, '2025-11-24', '13:00:00', '', '2025-11-23 21:58:18', '2025-12-12 06:00:53', 'Completed', 0),
(11, 8, 3, 3, '2025-11-28', '15:30:00', '', '2025-11-27 21:58:33', '2025-12-12 06:01:32', 'Completed', 0),
(12, 7, 1, 7, '2025-11-07', '09:00:00', '', '2025-11-06 22:05:39', '2025-12-12 06:07:40', 'Completed', 0),
(13, 7, 1, 7, '2025-11-11', '12:30:00', '', '2025-11-10 22:06:13', '2025-12-12 06:08:31', 'Completed', 0),
(14, 7, 1, 7, '2025-11-25', '15:30:00', '', '2025-11-24 22:06:30', '2025-12-12 06:09:09', 'Completed', 0),
(15, 7, 2, 4, '2025-12-03', '10:00:00', '', '2025-12-12 01:34:48', '2025-12-03 09:40:20', 'Completed', 0),
(16, 7, 2, 4, '2025-12-05', '11:00:00', '', '2025-12-12 01:35:05', '2025-12-05 09:40:45', 'Completed', 0),
(17, 8, 3, 3, '2025-12-05', '10:00:00', '', '2025-12-05 01:35:28', '2025-12-12 09:42:53', 'Completed', 0),
(18, 8, 3, 2, '2025-12-01', '13:00:00', '', '2025-12-01 01:35:45', '2025-12-12 09:43:26', 'Completed', 0),
(19, 6, 1, 7, '2025-12-02', '10:30:00', '', '2025-12-02 01:36:00', '2025-12-12 09:36:53', 'Completed', 0),
(20, 6, 1, 7, '2025-12-04', '13:00:00', '', '2025-12-04 01:36:15', '2025-12-12 09:37:25', 'Completed', 0),
(21, 8, 2, 1, '2025-12-13', '16:00:00', '', '2025-12-12 07:08:00', '2025-12-12 17:22:20', 'Cancelled', 1),
(22, 9, 2, 4, '2025-12-15', '11:00:00', '', '2025-12-12 07:09:47', NULL, 'Cancelled', 0),
(23, 10, 4, 8, '2025-11-04', '14:00:00', '', '2025-12-12 07:11:11', '2025-12-15 04:23:42', 'Cancelled', 0),
(24, 9, 2, 1, '2025-12-13', '15:00:00', '', '2025-12-12 07:13:49', '2025-12-12 15:51:53', 'Completed', 0),
(25, 6, 2, 1, '2025-12-12', '16:00:00', '', '2025-12-12 07:33:52', '2025-12-12 15:48:37', 'Cancelled', 0),
(26, 6, 2, 3, '2025-12-15', '10:00:00', '', '2025-12-12 07:34:29', NULL, 'Cancelled', 0),
(27, 6, 1, 2, '2025-11-21', '10:30:00', '', '2025-12-12 07:35:18', NULL, 'Cancelled', 0),
(28, 11, 1, 7, '2025-12-13', '09:00:00', '', '2025-12-12 07:52:00', NULL, 'Cancelled', 0),
(29, 11, 2, 1, '2025-12-12', '12:00:00', '', '2025-12-12 07:57:25', '2025-12-12 15:59:07', 'Completed', 0),
(30, 11, 1, 7, '2025-12-13', '12:00:00', '', '2025-12-12 08:01:10', NULL, 'Cancelled', 0),
(31, 11, 3, 4, '2025-12-11', '13:30:00', '', '2025-12-12 08:05:01', '2025-12-12 16:08:55', 'Completed', 0),
(32, 12, 2, 4, '2025-11-26', '09:00:00', '', '2025-12-12 09:00:26', '2025-12-12 17:22:42', 'Cancelled', 0),
(33, 15, 2, 1, '2025-12-15', '12:00:00', '', '2025-12-12 09:17:38', '2025-12-12 17:26:13', 'Completed', 0),
(34, 10, 2, 4, '2025-11-20', '11:00:00', '', '2025-12-12 09:27:24', NULL, 'Cancelled', 0),
(35, 17, 2, 3, '2025-11-18', '09:00:00', '', '2025-12-13 17:59:39', NULL, 'Cancelled', 0),
(36, 19, 2, 4, '2025-12-15', '09:00:00', '', '2025-12-13 19:04:27', NULL, 'Cancelled', 0),
(37, 20, 2, 4, '2025-10-16', '09:00:00', '', '2025-12-13 19:38:54', NULL, 'Cancelled', 0),
(38, 22, 2, 4, '2025-11-05', '09:00:00', '', '2025-12-13 19:48:15', NULL, 'Cancelled', 0),
(39, 23, 2, 2, '2025-10-15', '09:00:00', '', '2025-12-13 20:04:25', NULL, 'Cancelled', 0),
(40, 24, 1, 7, '2025-11-27', '09:00:00', '', '2025-12-14 11:35:13', NULL, 'Cancelled', 0),
(41, 7, 1, 2, '2025-11-29', '12:00:00', '', '2025-12-14 16:26:50', '2025-12-15 07:12:52', 'Cancelled', 0),
(42, 12, 1, 2, '2025-11-14', '09:00:00', '', '2025-12-14 18:00:13', NULL, 'Cancelled', 0),
(43, 6, 1, 7, '2025-11-29', '09:00:00', '', '2025-12-14 21:10:14', NULL, 'Cancelled', 0),
(44, 12, 1, 2, '2025-11-29', '09:00:00', '', '2025-12-14 21:49:53', NULL, 'Cancelled', 0),
(45, 10, 1, 7, '2025-12-06', '09:00:00', '', '2025-12-14 23:27:04', '2025-12-15 07:29:20', 'Cancelled', 0),
(46, 7, 1, 2, '2025-11-29', '09:30:00', '', '2025-12-15 00:05:19', '2025-12-15 09:51:22', 'Cancelled', 0),
(47, 12, 4, 8, '2025-11-10', '14:00:00', '', '2025-12-15 00:43:12', NULL, 'Cancelled', 0),
(48, 10, 1, 7, '2025-11-15', '13:30:00', '', '2025-12-15 09:55:00', '2025-12-15 18:31:49', 'Cancelled', 0),
(49, 7, 1, 7, '2025-12-13', '09:00:00', '', '2025-12-15 10:15:23', NULL, 'Cancelled', 0),
(50, 26, 4, 2, '2026-01-01', '09:00:00', '', '2025-12-15 15:17:16', '2025-12-15 23:52:07', 'Pending Reschedule', 0),
(51, 7, 2, 7, '2026-01-22', '13:00:00', '', '2025-12-15 20:40:21', '2025-12-16 07:06:10', 'Booked', 0);

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
  `dental_chairs` int(11) NOT NULL DEFAULT 1,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `map_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`branch_id`, `name`, `nickname`, `address`, `phone_number`, `dental_chairs`, `status`, `date_created`, `date_updated`, `map_url`) VALUES
(1, 'Pakna-an, Mandaue City (Main Branch)', 'Mandaue', 'Jayme Street, Zone Ube, Pakna-an, Mandaue City', '9273505830', 2, 'Inactive', '2025-12-12 03:51:30', '2025-12-16 06:20:45', 'https://maps.app.goo.gl/pMai2KSgVv3hj1tZA'),
(2, 'Babag 2, Lapu-Lapu City', 'Babag 2', '2nd Floor RM Arcade, Babag 2, Lapu-Lapu City', '9273505830', 1, 'Active', '2025-12-12 03:52:04', '2025-12-16 07:05:50', 'https://maps.app.goo.gl/8okHqFg5fRn4xMyV6'),
(3, 'Pusok, Lapu-Lapu City', 'Pusok', 'Modejar Building (Room 306), City Hall Road, Pusok, Lapu-Lapu City', '9273505830', 1, 'Inactive', '2025-12-12 03:52:47', '2025-12-16 06:39:36', 'https://maps.app.goo.gl/EUQVQkys2wSabHwKA'),
(4, 'Pajo, Lapu- Lapu City', 'Pajo', 'Punta Rizal Street, Pajo, Lapu-Lapu City, Cebu', '9273505830', 1, 'Inactive', '2025-12-12 17:02:40', '2025-12-16 07:05:54', 'https://maps.app.goo.gl/mzhNBDsAAM44pcSY6');

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
(1, 1, 2, 'Active', '2026-01-01', '2026-01-01', '2025-12-12 04:31:25', '2025-12-15 23:28:35'),
(2, 2, 2, 'Active', NULL, NULL, '2025-12-12 04:33:40', '2025-12-12 04:34:23'),
(3, 3, 3, 'Active', '2025-12-23', '2026-01-03', '2025-12-12 04:40:51', '2025-12-12 04:40:51'),
(4, 4, 2, 'Active', '2025-12-23', '2025-12-23', '2025-12-12 17:15:10', '2025-12-12 17:15:10'),
(5, 5, 1, 'Active', '2026-01-01', '2026-01-02', '2025-12-15 04:47:00', '2025-12-15 23:41:35');

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
(1, 2, 1, 'Active', NULL, NULL),
(2, 1, 1, 'Active', NULL, NULL),
(3, 3, 1, 'Active', NULL, NULL),
(4, 2, 2, 'Active', '2025-12-08', '2025-12-28'),
(5, 1, 2, 'Active', '2025-12-08', '2025-12-28'),
(6, 3, 2, 'Active', '2025-12-08', '2025-12-28'),
(7, 2, 3, 'Active', '2025-12-28', '2026-01-28'),
(8, 4, 3, 'Active', '2025-12-28', '2026-01-28'),
(9, 1, 3, 'Active', '2025-12-28', '2026-01-28'),
(10, 3, 3, 'Active', '2025-12-28', '2026-01-28');

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
(10, 2, 4, 'Active', '2025-12-12 04:06:40', '2025-12-12 04:06:40'),
(11, 1, 4, 'Active', '2025-12-12 04:06:40', '2025-12-15 18:05:28'),
(12, 3, 4, 'Active', '2025-12-12 04:06:40', '2025-12-12 04:06:40'),
(13, 2, 5, 'Active', '2025-12-12 04:06:56', '2025-12-12 04:06:56'),
(14, 1, 5, 'Active', '2025-12-12 04:06:56', '2025-12-12 04:06:56'),
(15, 3, 5, 'Active', '2025-12-12 04:06:56', '2025-12-12 04:06:56'),
(16, 2, 6, 'Active', '2025-12-12 04:07:14', '2025-12-12 04:07:14'),
(17, 1, 6, 'Active', '2025-12-12 04:07:14', '2025-12-12 04:07:14'),
(18, 3, 6, 'Active', '2025-12-12 04:07:14', '2025-12-12 04:07:14'),
(19, 2, 7, 'Active', '2025-12-12 04:07:31', '2025-12-12 04:07:31'),
(20, 1, 7, 'Active', '2025-12-12 04:07:31', '2025-12-12 04:07:31'),
(21, 3, 7, 'Active', '2025-12-12 04:07:31', '2025-12-12 04:07:31'),
(22, 2, 8, 'Active', '2025-12-12 17:12:25', '2025-12-12 17:12:25'),
(23, 4, 8, 'Active', '2025-12-12 17:12:25', '2025-12-12 17:12:25'),
(24, 1, 8, 'Active', '2025-12-12 17:12:25', '2025-12-12 17:12:25'),
(25, 3, 8, 'Active', '2025-12-12 17:12:25', '2025-12-12 17:12:25'),
(26, 1, 1, 'Active', '2025-12-15 23:50:36', '2025-12-15 23:50:36'),
(27, 2, 1, 'Active', '2025-12-15 23:50:36', '2025-12-15 23:50:36'),
(28, 3, 1, 'Active', '2025-12-15 23:50:36', '2025-12-15 23:50:36'),
(29, 4, 1, 'Active', '2025-12-15 23:50:36', '2025-12-15 23:50:36'),
(30, 1, 2, 'Active', '2025-12-15 23:50:44', '2025-12-15 23:50:44'),
(31, 2, 2, 'Active', '2025-12-15 23:50:44', '2025-12-15 23:50:44'),
(32, 3, 2, 'Active', '2025-12-15 23:50:44', '2025-12-15 23:50:44'),
(33, 4, 2, 'Active', '2025-12-15 23:50:44', '2025-12-15 23:50:44'),
(34, 1, 3, 'Active', '2025-12-16 06:40:52', '2025-12-16 06:40:52'),
(35, 2, 3, 'Active', '2025-12-16 06:40:52', '2025-12-16 06:40:52'),
(36, 3, 3, 'Active', '2025-12-16 06:40:52', '2025-12-16 06:40:52'),
(37, 4, 3, 'Active', '2025-12-16 06:40:52', '2025-12-16 06:40:52');

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
(1, 2, 1, 10, 20, NULL, 'Available', '2025-12-12 04:21:34', '2025-12-12 16:14:49'),
(2, 2, 2, 97, 50, '2028-12-31', 'Available', '2025-12-12 04:23:30', '2025-12-12 17:26:13'),
(3, 2, 3, 135, 50, '2027-12-31', 'Available', '2025-12-12 04:25:22', '2025-12-12 17:26:13'),
(4, 2, 4, 28, 10, '2026-12-31', 'Available', '2025-12-12 04:27:17', '2025-12-12 17:26:13'),
(5, 2, 5, 20, 5, NULL, 'Available', '2025-12-12 17:20:27', '2025-12-12 17:20:27');

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
(1, 2, 3, 'Amoxicillin', NULL, 'Every 8 hours', '500 mg', '7 days', '21', 'Take after meals. Complete the full course of medication.', '2025-12-11 21:49:06', '2025-12-12 05:49:06'),
(2, 5, 3, 'Ibuprofen', NULL, 'Every 8 hours', '400 mg', '5 days', '15', 'Take after meals. Avoid if with stomach problems.', '2025-12-11 21:49:55', '2025-12-12 05:49:55'),
(3, 7, 3, 'Mefenamic Acid', NULL, 'Every 8 hours', '500 mg', '5 days', '20', 'Take after meals. Do not exceed prescribed dose.', '2025-12-11 21:51:08', '2025-12-12 05:51:08'),
(4, 7, 3, 'Ibuprofen', NULL, 'Every 8 hours', '400 mg', '5 days', '15', 'Take after meals. Avoid if with stomach problems.', '2025-12-11 21:51:24', '2025-12-12 05:51:24'),
(5, 9, 5, 'Mefenamic Acid', NULL, 'Every 8 hours', '500 mg', '5 days', '15', 'Take after meals. Do not exceed prescribed dose.', '2025-12-11 22:00:24', '2025-12-12 06:00:24'),
(6, 24, 3, 'Amoxicillin', NULL, 'Every 8 hours', '500 mg', '7 days', '21', 'Take after meals. Complete the full course of medication.', '2025-12-12 07:51:30', '2025-12-12 15:51:30'),
(7, 24, 3, 'Paracetamol', NULL, 'Every 6 hours', '500 mg', '5 days', '20', 'Take after meals. Do not exceed 4 tablets per day.', '2025-12-12 07:51:46', '2025-12-12 15:51:46'),
(8, 33, 3, 'Amoxicillin', NULL, 'Every 8 hours', '500 mg', '7 days', '21', 'Take after meals. Complete the full course of medication.', '2025-12-12 09:25:37', '2025-12-12 17:25:37'),
(9, 33, 3, 'Ibuprofen', NULL, 'Every 8 hours', '400 mg', '5 days', '15', 'Take after meals. Avoid if with stomach problems.', '2025-12-12 09:25:54', '2025-12-12 17:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `dental_tips`
--

CREATE TABLE `dental_tips` (
  `tip_id` int(11) NOT NULL,
  `tip_text` varchar(255) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dental_tips`
--

INSERT INTO `dental_tips` (`tip_id`, `tip_text`, `date_created`) VALUES
(1, '🦷 Brush your teeth for at least two minutes twice a day!', '2025-12-12 02:00:43'),
(2, '✨ Don’t forget to floss daily — your gums will thank you!', '2025-12-12 02:00:43'),
(3, '🍬 Limit sugary snacks to keep cavities away!', '2025-12-12 02:00:43'),
(4, '📅 Visit your dentist every 6 months for a healthy smile!', '2025-12-12 02:00:43'),
(5, '💧 Drink plenty of water — it helps wash away bacteria!', '2025-12-12 02:00:43'),
(6, '🪥 Replace your toothbrush every 3 months for best results!', '2025-12-12 02:00:43'),
(7, '🛡️ Use fluoride toothpaste to strengthen your enamel!', '2025-12-12 02:00:43'),
(8, '🥤 Avoid acidic drinks like soda to protect your teeth!', '2025-12-12 02:00:43'),
(9, '👅 Gently brush your tongue to reduce bad breath!', '2025-12-12 02:00:43'),
(10, '😬 Wear a mouthguard when playing contact sports!', '2025-12-12 02:00:43'),
(11, '🌿 Chew sugar-free gum to boost saliva and clean your mouth!', '2025-12-12 02:00:43'),
(12, '🥛 Drink milk or eat cheese for stronger teeth (calcium boost!)', '2025-12-12 02:00:43'),
(13, '🚫 Avoid smoking — it stains teeth and harms gums!', '2025-12-12 02:00:43'),
(14, '😊 Smile often — it’s good for your confidence and health!', '2025-12-12 02:00:43'),
(15, '🌙 Don’t skip brushing before bed — plaque builds up overnight!', '2025-12-12 02:00:43');

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
(1, 4, 1, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 40000.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-11 21:44:08', '2025-11-12 05:45:02', 0),
(2, 2, 3, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, 'images/transactions/xrays/2_sanjose.png', 8050.00, 1000.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-02 21:48:29', '2025-12-12 05:49:10', 0),
(3, 5, 1, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 1200.00, 500.00, '', 'Expired', NULL, '2 Days', 'Irreversible pulpitis', 'Advise rest and follow post-treatment instructions.', NULL, '2025-12-12 05:50:17', NULL, '2025-11-12 21:49:25', '2025-12-12 15:12:21', 0),
(4, 7, 3, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 40350.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-23 21:50:34', '2025-12-12 05:51:26', 0),
(5, 6, 1, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 950.00, 250.00, '', 'Expired', NULL, '2 Days', 'Irreversible pulpitis', 'Advise rest and follow post-treatment instructions.', NULL, '2025-12-12 05:52:26', NULL, '2025-11-23 21:52:08', '2025-12-12 15:12:21', 0),
(6, 1, 1, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 2100.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-11 21:52:51', '2025-12-12 05:53:13', 0),
(7, 9, 5, 5, NULL, NULL, NULL, NULL, 'Cash', NULL, 'images/transactions/xrays/7_sanjose.jpg', 6700.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-18 21:59:32', '2025-12-12 06:00:26', 0),
(8, 10, 2, 5, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 40500.00, 500.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-23 22:00:35', '2025-12-12 06:00:53', 0),
(9, 11, 3, 5, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 1300.00, 250.00, '', 'Expired', NULL, '2 Days', 'Irreversible pulpitis', 'Advise rest and follow post-treatment instructions.', NULL, '2025-12-12 06:01:32', NULL, '2025-11-27 22:01:14', '2025-12-12 15:12:21', 0),
(10, 12, 7, 4, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 70700.00, 0.00, '', 'Expired', NULL, '2 days', '', '', '', '2025-12-12 17:32:27', 150.00, '2025-11-06 22:07:15', '2025-12-12 18:08:24', 0),
(11, 13, 7, 4, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 70000.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-10 22:08:12', '2025-12-12 06:08:31', 0),
(12, 14, 7, 4, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 350.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-11-24 22:08:51', '2025-12-12 06:09:09', 0),
(13, 19, 7, 4, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-02 01:36:29', '2025-12-12 09:36:53', 0),
(14, 20, 7, 4, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 1250.00, 200.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-04 01:37:07', '2025-12-12 09:37:25', 0),
(15, 15, 4, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'Expired', NULL, '2 days rest', '', '', '', '2025-12-12 17:31:17', 150.00, '2025-12-03 01:39:57', '2025-12-12 17:31:21', 0),
(16, 16, 4, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'Expired', NULL, '2 Days', '', '', '', '2025-12-12 17:29:57', 150.00, '2025-12-05 01:40:30', '2025-12-12 17:29:59', 0),
(17, 17, 3, 5, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 40000.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-05 01:42:31', '2025-12-12 09:42:53', 0),
(18, 18, 2, 5, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 40000.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-01 01:43:05', '2025-12-12 09:43:26', 0),
(19, 24, 1, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 700.00, 0.00, '', 'Requested', '/images/payments/medcert_payments/19_sanjose.png', '', '', '', NULL, '2025-12-12 17:28:46', NULL, '2025-12-12 07:50:42', '2025-12-12 17:28:46', 1),
(20, 29, 1, 3, NULL, NULL, NULL, NULL, 'Cash', NULL, NULL, 40000.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-12 07:58:30', '2025-12-12 15:59:07', 0),
(21, 31, 4, 5, NULL, NULL, NULL, NULL, 'Cash', NULL, 'images/transactions/xrays/21_alcoriza.jpg', 7050.00, 0.00, '', 'None', NULL, '', '', '', NULL, NULL, NULL, '2025-12-12 08:08:30', '2025-12-12 16:08:55', 0),
(22, 33, 1, 3, 2, 'December Discount', 'fixed', 500.00, 'Cashless', '/images/payments/cashless_payments/22_castillo.png', 'images/transactions/xrays/22_castillo.jpg', 6800.00, 250.00, '', 'Eligible', NULL, '2 Days', 'Irreversible pulpitis', 'Advise rest and follow post-treatment instructions.', NULL, '2025-12-12 17:26:13', NULL, '2025-12-12 09:24:42', '2025-12-12 17:26:13', 0);

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
(1, 1, 5, 'Braces (Simple)', 40000.00, 1, 0.00),
(2, 2, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(3, 2, 4, 'Tooth Filling', 700.00, 1, 0.00),
(4, 2, 7, 'Endodontics', 6000.00, 1, 1000.00),
(6, 3, 1, 'Dental Certificate', 0.00, 1, 0.00),
(7, 3, 3, 'Cleaning', 700.00, 1, 500.00),
(8, 4, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(9, 4, 5, 'Braces (Simple)', 40000.00, 1, 0.00),
(10, 5, 1, 'Dental Certificate', 0.00, 1, 0.00),
(11, 5, 4, 'Tooth Filling', 700.00, 1, 250.00),
(12, 6, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(13, 6, 3, 'Cleaning', 700.00, 1, 0.00),
(14, 7, 4, 'Tooth Filling', 700.00, 1, 0.00),
(15, 7, 7, 'Endodontics', 6000.00, 1, 0.00),
(16, 8, 5, 'Braces (Simple)', 40000.00, 1, 500.00),
(17, 9, 1, 'Dental Certificate', 0.00, 1, 0.00),
(18, 9, 2, 'Check Up/Consultation', 350.00, 1, 250.00),
(19, 9, 3, 'Cleaning', 700.00, 1, 0.00),
(20, 10, 3, 'Cleaning', 700.00, 1, 0.00),
(21, 10, 6, 'Braces (Complicated)', 70000.00, 1, 0.00),
(22, 11, 6, 'Braces (Complicated)', 70000.00, 1, 0.00),
(23, 12, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(24, 13, 3, 'Cleaning', 700.00, 1, 0.00),
(25, 14, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(26, 14, 4, 'Tooth Filling', 700.00, 1, 200.00),
(27, 15, 3, 'Cleaning', 700.00, 1, 0.00),
(28, 16, 4, 'Tooth Filling', 700.00, 1, 0.00),
(29, 17, 5, 'Braces (Simple)', 40000.00, 1, 0.00),
(30, 18, 5, 'Braces (Simple)', 40000.00, 1, 0.00),
(31, 19, 3, 'Cleaning', 700.00, 1, 0.00),
(32, 20, 5, 'Braces (Simple)', 40000.00, 1, 0.00),
(33, 21, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(34, 21, 4, 'Tooth Filling', 700.00, 1, 0.00),
(35, 21, 7, 'Endodontics', 6000.00, 1, 0.00),
(36, 22, 1, 'Dental Certificate', 0.00, 1, 0.00),
(37, 22, 2, 'Check Up/Consultation', 350.00, 1, 0.00),
(38, 22, 3, 'Cleaning', 700.00, 1, 250.00),
(39, 22, 7, 'Endodontics', 6000.00, 1, 0.00);

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
(1, 4, 3, 36.7, 72, 20, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 21:45:00', '2025-12-12 05:45:00'),
(2, 2, 3, 36.5, 78, 20, '118/76', 170.00, 65.00, 'No', 'No', 'No', '2025-12-11 21:48:49', '2025-12-12 05:48:49'),
(3, 5, 3, 36.7, 72, 18, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 21:49:41', '2025-12-12 05:49:41'),
(4, 7, 3, 36.7, 72, 18, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 21:50:50', '2025-12-12 05:50:50'),
(5, 6, 3, 36.5, 72, 20, '118/76', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 21:52:23', '2025-12-12 05:52:23'),
(6, 1, 3, 36.7, 72, 18, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 21:53:10', '2025-12-12 05:53:10'),
(7, 9, 5, 36.7, 72, 18, '120/80', 170.00, 65.00, 'No', 'No', 'No', '2025-12-11 22:00:04', '2025-12-12 06:00:04'),
(8, 10, 5, 36.7, 72, 18, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 22:00:51', '2025-12-12 06:00:51'),
(9, 11, 5, 36.5, 72, 20, '120/80', 168.00, 65.00, 'No', 'No', 'No', '2025-12-11 22:01:29', '2025-12-12 06:01:29'),
(10, 12, 4, 36.7, 72, 18, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-11 22:07:37', '2025-12-12 06:07:37'),
(11, 13, 4, 36.5, 72, 18, '120/80', 168.00, 65.00, 'No', 'No', 'No', '2025-12-11 22:08:28', '2025-12-12 06:08:28'),
(12, 14, 4, 36.5, 72, 18, '118/76', 170.00, 65.00, 'No', 'No', 'No', '2025-12-11 22:09:06', '2025-12-12 06:09:06'),
(13, 19, 4, 36.7, 72, 18, '120/80', 168.00, 65.00, 'No', 'No', 'No', '2025-12-12 01:36:50', '2025-12-12 09:36:50'),
(14, 20, 4, 36.5, 78, 18, '118/76', 170.00, 65.00, 'No', 'No', 'No', '2025-12-12 01:37:23', '2025-12-12 09:37:23'),
(15, 15, 3, 36.5, 72, 18, '118/76', 170.00, 60.00, 'No', 'No', 'No', '2025-12-12 01:40:16', '2025-12-12 09:40:16'),
(16, 16, 3, 36.7, 72, 18, '118/76', 170.00, 65.00, 'No', 'No', 'No', '2025-12-12 01:40:44', '2025-12-12 09:40:44'),
(17, 17, 5, 36.7, 72, 20, '120/80', 170.00, 60.00, 'No', 'No', 'No', '2025-12-12 01:42:52', '2025-12-12 09:42:52'),
(18, 18, 5, 36.7, 72, 20, '118/76', 168.00, 60.00, 'No', 'No', 'No', '2025-12-12 01:43:22', '2025-12-12 09:43:22'),
(19, 24, 3, 36.7, 72, 18, '120/80', 170.00, 65.00, 'No', 'No', 'No', '2025-12-12 07:51:12', '2025-12-12 15:51:12'),
(20, 29, 3, 36.7, 78, 20, '120/80', 168.00, 65.00, 'No', 'No', 'No', '2025-12-12 07:58:58', '2025-12-12 15:58:58'),
(21, 31, 5, 36.5, 72, 18, '118/76', 168.00, 65.00, 'No', 'No', 'No', '2025-12-12 08:08:52', '2025-12-12 16:08:52'),
(22, 33, 3, 36.7, 72, 18, '120/80', 168.00, 60.00, 'No', 'No', 'No', '2025-12-12 09:25:11', '2025-12-12 17:25:11');

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
(1, 'Solante', '', 'Ben', 'Male', '44e2vDBKI/gkkA==', 'iBk4vMDdVp4tLpp1', 'OvWXRtixxRTyelzlAoQ0YQ==', 'bensolante@gmail.com', 'l93szQlBXjtEMQ==', 'scwaNhsLHp17xdR7', 'VTdlZBRD3gocjmpUwqQ9rQ==', 'Dqd/MRIx4Q==', 'FbKOUUURzSuA2f3C', 'ICWi95mHO2gVk58sVuL/3g==', '2025-10-01', 'Inactive', '1_solante_signature.png', '1_solante_profile.jpg', '2025-10-01 20:51:14', '2025-12-16 07:04:55'),
(2, 'Hernandez', '', 'Jaime', 'Female', 'DTdMwkc/467PDQ==', 'cwSNBDMC+/engTdH', 'Ed8DQydJmhrl5reidmDMYQ==', 'jaimeh.89@yahoo.com', 'cfbaO+6hSW8Aew==', 'eJDpzxt5pUuWr+3U', 'ec/FrVy2bT0asAaxjY0K7w==', 'hTbJ7bnFSQ==', 'tGZg4bbqTgkSopkB', '9vHRWaOZ90dL2avOyFXIYg==', '2025-10-01', 'Active', '2_hernandez_signature.png', '2_hernandez_profile.jpg', '2025-10-01 20:55:26', '2025-12-16 01:27:44'),
(3, 'Perez', '', 'Vince', 'Male', 'FgbWe4bwMY5JMQ==', 'xKRZpj7gBBC3pGN/', 'BOqFK4m/OIFJ9uDV4+6aVQ==', 'vinceperez.outlook@gmail.com', 'hLpiYmRgI4uFGg==', '43UxAHE0Vp8bJnWd', 'a0dDGjFCcvoiRDvMNx8j3Q==', 'ybo3mRAUqw==', 'qVgYib8YJRB7UQSy', '9h6ZiXHYVwqinqHCT7Q12w==', '2025-10-01', 'Active', '3_perez_signature.png', '3_perez_profile.jpg', '2025-10-01 21:01:57', '2025-12-16 01:27:36'),
(4, 'Dominggo', '', 'Cedric', 'Male', 'cR4PLSHHZdw2hw==', 'FyJ/mfU6E3omOVOv', 'jtj20Nx8Pcak6nO5d5UQ+w==', 'cedric.dominggo@yahoo.com', 'a75Fxh40BeE5dA==', 'C3yql2PMu8aoulQl', 'BODHYSoOPnojbzLK3HCeCA==', 'LdfaMI8z9w==', 'TEX50VmV7ICHfvx5', '5AIT58L+k8wtYVDNqGQxDQ==', '2025-10-01', 'Active', '4_dominggo_signature.png', '4_dominggo_profile.webp', '2025-10-01 21:07:07', '2025-12-16 02:41:54'),
(5, 'Alonzo', '', 'Love', 'Female', 'r8U4RZHtIC7qVw==', 'SnxmB96qqRNT2y3M', 'm6T+KoWY1h3Od4kxZbaBjA==', 'lovealonzo@gmail.com', 'q0PaWoGKJ26SKw==', '0s6lZGiDPgnYRVK5', 'y13Zd6yFpJUCvjW/YkLopQ==', 'LAcKqYd3Mg==', 'vWUUODvs4FPxZnC6', 'E7IrqGTA6cA31FkEMDfYDA==', '2025-10-01', 'Inactive', '5_alonzo_signature.png', '5_alonzo_profile.jpg', '2025-10-01 21:11:37', '2025-12-16 06:18:21'),
(6, 'Asuncion', '', 'Ryan', 'Male', 'nNq8vX8mmLjrIQ==', '3gRCPVjg04yfugv+', '1bgTokbjhANVjanUYm0lcw==', 'ryan.asuncion@outlook.com', 'l//RUL/K+agxjA==', 'ysiIy2skW9T6BhsB', 'rm9VC/Lr1SxRWigSvNM2NQ==', '5tDXKbbhOg==', 'QyC8Wypt4y1dRRDJ', 'YQOzDsg+ZmYhXJpKwiFrLQ==', '2025-10-01', 'Active', '6_asuncion_signature.png', '6_asuncion_profile.jpg', '2025-10-01 21:15:00', '2025-12-16 03:03:58'),
(7, 'Arriesgado', '', 'Irish', 'Female', 'N9S1n7F60eD5Xg==', 'uzdqB62n/Uum18EF', 'k5hpVx6bfV6iUAVZqPfdzw==', 'irish.arriesgado@yahoo.com', '1Vldq47z6hzyqQ==', 'xUgGoJM6PnRUj6Yx', 'WKS8cVUYhFuYPer6MQuwQw==', 'nI3hMQkddQ==', 'YxN57vbbs37RIkl4', 'r7JibGsODYS2Ga1gEbcNWA==', '2025-10-01', 'Active', NULL, NULL, '2025-10-01 21:16:19', '2025-12-16 04:04:39'),
(8, 'Buhayan', '', 'Jenmel', 'Female', 'tQAQ0ca1TL6pTw==', '2LokXyP919yLJ6lQ', 'yDyhGQE2PZEfgjQ8abzK5g==', 'jbuhayan@gmail.com', 'Kjiyk5UHlMoMQA==', 'XRpt2bR05ti2H5FS', 'YPO5RusAGrvw3G9boasyNg==', 'W9e4lwYEeg==', 'PQcfyOxksD0Qsvni', 'E72KunqYVuO3YDO9VBRYTg==', '2025-12-13', 'Active', '8_buhayan_signature.png', '8_buhayan_profile.jpg', '2025-12-12 09:11:34', '2025-12-16 06:53:25'),
(9, 'Parchaso', '', 'Honorato', 'Male', 'mJE+BXfDepnuAA==', '93Wn4Kujioz6HKdY', 'yOfcq/i+gNd7LcJ9C6KLIg==', 'theartp1@gmail.com', 'jOLGjm1sYv+LeA==', 'rwC87ZAkECHVw7XM', 'ymmsCgocAewkMrefpYpnOw==', 'nUhL', 'n/hIKnFgXMZfTwOv', 'fCQbQJGJ6Axqn55+GrUV3A==', '2025-12-17', 'Inactive', NULL, NULL, '2025-12-15 20:18:21', '2025-12-16 06:01:47'),
(10, 'Morales', '', 'Chan', 'Male', 'yN8IYjlDN+jj/g==', 'ruN7xv0xMLGe4qB3', '+9xt2rHm//9MTiUho5mlNg==', 'theartp1@gmail.com', 'XGA9k57x4y/KDA==', '/0to3U48Q5uqFmqp', 'YYkmmbuBqP0lF6L65wdWYQ==', 'SBm77Rro', '0LDExVHR69n98QZO', '+RItu2XArTVT3dtaRmGSaw==', '2025-12-17', 'Inactive', NULL, NULL, '2025-12-15 20:30:13', '2025-12-16 04:31:13'),
(11, 'Summers', '', 'Daze', 'Female', 'gDODuY+Kab9vKA==', 'aHOpKo/OGbhbZ9d9', 'cMctB//i2H5fTiJ5luqATw==', 'ewewa@gmail.com', 'nJtUcvWFYoGX5w==', 'x9u2lVqL0LMiEcD+', 'FIXe2uJ2dOkGIvDaArKcyQ==', '2dY1vPg=', 'QSHfka8gklcG71oZ', '8RcRN4Hz1XhckXZiRRyHpQ==', '2025-12-17', 'Inactive', NULL, NULL, '2025-12-15 20:33:12', '2025-12-16 05:39:31');

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
(27, 1, 1),
(28, 1, 2),
(29, 1, 3),
(30, 1, 4),
(31, 2, 1),
(32, 2, 2),
(33, 2, 3),
(34, 2, 4),
(38, 3, 1),
(39, 3, 2),
(40, 3, 3),
(41, 3, 4),
(42, 4, 1),
(43, 4, 2),
(44, 4, 3),
(45, 4, 4),
(46, 5, 1),
(47, 5, 2),
(48, 5, 3),
(49, 5, 4),
(50, 6, 1),
(51, 6, 2),
(52, 6, 3),
(53, 6, 4),
(54, 7, 1),
(55, 7, 2),
(56, 7, 3),
(57, 7, 4),
(58, 8, 1),
(59, 8, 2),
(60, 8, 3),
(61, 8, 4),
(62, 9, 2),
(63, 9, 4),
(64, 9, 1),
(65, 9, 3),
(66, 10, 2),
(67, 10, 4),
(68, 10, 1),
(69, 10, 3),
(70, 11, 2),
(71, 11, 4),
(72, 11, 1),
(73, 11, 3);

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
(377, 4, 'Monday', 1, '09:00:00', '16:00:00'),
(395, 6, 'Monday', 1, '09:00:00', '16:00:00'),
(415, 7, 'Monday', 3, '12:00:00', '16:00:00'),
(416, 7, 'Saturday', 2, '09:00:00', '12:00:00'),
(417, 7, 'Thursday', 2, '12:00:00', '16:00:00'),
(418, 10, 'Monday', 4, '09:00:00', '16:00:00'),
(419, 10, 'Tuesday', 4, '09:00:00', '16:00:00'),
(441, 11, 'Saturday', 2, '12:00:00', '16:00:00'),
(455, 5, 'Friday', 3, '09:00:00', '12:00:00'),
(456, 5, 'Monday', 3, '09:00:00', '12:00:00'),
(457, 5, 'Tuesday', 3, '09:00:00', '16:00:00'),
(458, 5, 'Wednesday', 3, '09:30:00', '16:00:00'),
(469, 8, 'Friday', 2, '09:00:00', '16:00:00'),
(470, 8, 'Saturday', 4, '09:00:00', '16:00:00'),
(471, 8, 'Thursday', 1, '09:00:00', '16:00:00'),
(472, 1, 'Friday', 4, '09:00:00', '16:00:00'),
(473, 1, 'Monday', 2, '09:00:00', '16:00:00'),
(474, 1, 'Thursday', 2, '09:00:00', '12:00:00'),
(475, 1, 'Tuesday', 2, '09:00:00', '16:00:00'),
(476, 1, 'Wednesday', 2, '09:00:00', '16:00:00');

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
(71, 8, 1),
(72, 8, 2),
(73, 8, 3),
(74, 8, 4),
(75, 8, 5),
(76, 8, 6),
(77, 8, 7),
(78, 8, 8),
(79, 1, 1),
(80, 1, 2),
(81, 1, 3),
(82, 1, 4),
(83, 1, 5),
(84, 1, 6),
(85, 1, 7),
(86, 1, 8),
(87, 2, 1),
(88, 2, 2),
(89, 2, 3),
(90, 2, 4),
(91, 2, 5),
(92, 2, 6),
(93, 2, 7),
(94, 2, 8),
(95, 3, 1),
(96, 3, 2),
(97, 3, 3),
(98, 3, 4),
(99, 3, 5),
(100, 3, 6),
(101, 3, 7),
(102, 3, 8),
(103, 4, 1),
(104, 4, 2),
(105, 4, 3),
(106, 4, 4),
(107, 4, 5),
(108, 4, 6),
(109, 4, 7),
(110, 4, 8),
(111, 5, 1),
(112, 5, 2),
(113, 5, 3),
(114, 5, 4),
(115, 5, 5),
(116, 5, 6),
(117, 5, 7),
(118, 5, 8),
(119, 6, 1),
(120, 6, 2),
(121, 6, 3),
(122, 6, 4),
(123, 6, 5),
(124, 6, 6),
(125, 6, 7),
(126, 6, 8),
(127, 7, 1),
(128, 7, 2),
(129, 7, 3),
(130, 7, 4),
(131, 7, 5),
(132, 7, 6),
(133, 7, 7),
(134, 7, 8),
(135, 9, 6),
(136, 9, 5),
(137, 9, 2),
(138, 9, 3),
(139, 9, 1),
(140, 9, 7),
(141, 9, 8),
(142, 9, 4),
(143, 10, 6),
(144, 10, 5),
(145, 10, 2),
(146, 10, 3),
(147, 10, 1),
(148, 10, 7),
(149, 10, 8),
(150, 10, 4),
(151, 11, 6),
(152, 11, 5),
(153, 11, 2),
(154, 11, 3),
(155, 11, 1),
(156, 11, 7),
(157, 11, 8),
(158, 11, 4);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `appointment_transaction_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `is_read`, `date_created`, `appointment_transaction_id`) VALUES
(1, 3, 'Your Secretary account has been created. Branch Assignment: Babag 2, Lapu-Lapu City. Username: Juban_J', 1, '2025-12-11 19:58:43', NULL),
(2, 4, 'Your Secretary account has been created. Branch Assignment: Pakna-an, Mandaue City (Main Branch). Username: Conde_P', 1, '2025-12-11 20:01:40', NULL),
(3, 5, 'Your Secretary account has been created. Branch Assignment: Pusok, Lapu-Lapu City. Username: Quinto_K', 1, '2025-12-11 20:03:07', NULL),
(4, 3, 'Your password was changed successfully on December 12, 2025, 4:14 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-11 20:14:12', NULL),
(5, 4, 'Your password was changed successfully on December 12, 2025, 4:14 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-11 20:14:52', NULL),
(6, 5, 'Your password was changed successfully on December 12, 2025, 4:15 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-11 20:15:16', NULL),
(7, 1, 'A new announcement titled \'Christmas Break\' was added for Babag 2, Lapu-Lapu City.', 1, '2025-12-11 20:31:25', NULL),
(8, 1, 'A new announcement titled \'Reopening\' was added for Babag 2, Lapu-Lapu City.', 1, '2025-12-11 20:33:40', NULL),
(9, 1, 'The announcement \'Christmas Break\' in Babag 2, Lapu-Lapu City was updated.', 1, '2025-12-11 20:33:52', NULL),
(10, 1, 'The announcement \'Reopening\' in Babag 2, Lapu-Lapu City was updated.', 1, '2025-12-11 20:34:23', NULL),
(11, 1, 'A new announcement titled \'Open for Holidays\' was added for Pusok, Lapu-Lapu City.', 1, '2025-12-11 20:40:51', NULL),
(12, 6, 'Welcome to Smile-ify! Your account has been created.', 1, '2025-12-11 21:24:30', NULL),
(13, 6, 'Your appointment on 2025-12-12 at 09:00 was successfully booked.', 1, '2025-12-11 21:24:30', NULL),
(14, 6, 'Your password was changed successfully on December 12, 2025, 5:25 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-11 21:25:56', NULL),
(15, 7, 'Welcome to Smile-ify! Your account was created.', 1, '2025-12-11 21:36:20', NULL),
(16, 8, 'Your appointment on 2025-12-18 at 09:00 was successfully booked.', 0, '2025-12-11 21:36:20', NULL),
(17, 7, 'Your dependent\'s appointment on 2025-12-18 at 09:00 was successfully booked.', 1, '2025-12-11 21:36:20', NULL),
(18, 6, 'Your appointment on 2025-12-12 at 10:30 was successfully booked!', 1, '2025-12-11 21:37:15', NULL),
(19, 6, 'Your appointment on 2025-12-12 at 14:30 was successfully booked!', 1, '2025-12-11 21:37:39', NULL),
(20, 6, 'Your appointment on 2025-12-13 at 09:00 was successfully booked!', 1, '2025-12-11 21:38:07', NULL),
(21, 6, 'Your appointment on 2025-12-13 at 10:00 was successfully booked!', 1, '2025-12-11 21:38:20', NULL),
(22, 6, 'Your appointment on 2025-12-15 at 09:00 was successfully booked!', 1, '2025-12-11 21:39:00', NULL),
(23, 6, 'Your appointment (November 6, 2025 at 10:30 AM) has been cancelled.', 1, '2025-12-11 21:43:44', NULL),
(24, 8, 'Your appointment on 2025-12-12 at 09:00 was successfully booked!', 0, '2025-12-11 21:57:41', NULL),
(25, 7, 'Your dependent\'s appointment on 2025-12-12 at 09:00 has been booked.', 1, '2025-12-11 21:57:41', NULL),
(26, 8, 'Your appointment on 2025-12-12 at 10:00 was successfully booked!', 0, '2025-12-11 21:58:01', NULL),
(27, 7, 'Your dependent\'s appointment on 2025-12-12 at 10:00 has been booked.', 1, '2025-12-11 21:58:01', NULL),
(28, 8, 'Your appointment on 2025-12-12 at 13:00 was successfully booked!', 0, '2025-12-11 21:58:18', NULL),
(29, 7, 'Your dependent\'s appointment on 2025-12-12 at 13:00 has been booked.', 1, '2025-12-11 21:58:18', NULL),
(30, 8, 'Your appointment on 2025-12-12 at 15:30 was successfully booked!', 0, '2025-12-11 21:58:33', NULL),
(31, 7, 'Your dependent\'s appointment on 2025-12-12 at 15:30 has been booked.', 1, '2025-12-11 21:58:33', NULL),
(32, 8, 'Your appointment (December 12, 2025 at 9:00 AM) has been cancelled.', 0, '2025-12-11 21:59:15', NULL),
(33, 7, 'The appointment for your dependent (December 12, 2025 at 9:00 AM) has been cancelled.', 1, '2025-12-11 21:59:15', NULL),
(34, 8, 'Your appointment (December 12, 2025 at 10:00 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-11 22:00:26', NULL),
(35, 7, 'The appointment for your dependent (December 12, 2025 at 10:00 AM) has been marked as completed.', 1, '2025-12-11 22:00:26', NULL),
(36, 8, 'Your appointment (December 12, 2025 at 1:00 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-11 22:00:53', NULL),
(37, 7, 'The appointment for your dependent (December 12, 2025 at 1:00 PM) has been marked as completed.', 1, '2025-12-11 22:00:53', NULL),
(38, 8, 'Your appointment (December 12, 2025 at 3:30 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-11 22:01:32', NULL),
(39, 7, 'The appointment for your dependent (December 12, 2025 at 3:30 PM) has been marked as completed.', 1, '2025-12-11 22:01:32', NULL),
(40, 7, 'Your appointment on 2025-12-12 at 09:00 was successfully booked!', 1, '2025-12-11 22:05:39', NULL),
(41, 7, 'Your appointment on 2025-12-12 at 12:30 was successfully booked!', 1, '2025-12-11 22:06:13', NULL),
(42, 7, 'Your appointment on 2025-12-12 at 15:30 was successfully booked!', 1, '2025-12-11 22:06:30', NULL),
(43, 7, 'Your appointment (December 12, 2025 at 9:00 AM) has been marked as completed. Thank you for visiting!', 1, '2025-12-11 22:07:40', NULL),
(44, 7, 'Your appointment (December 12, 2025 at 12:30 PM) has been marked as completed. Thank you for visiting!', 1, '2025-12-11 22:08:31', NULL),
(45, 7, 'Your appointment (December 12, 2025 at 3:30 PM) has been marked as completed. Thank you for visiting!', 1, '2025-12-11 22:09:09', NULL),
(46, 7, 'Your appointment on 2025-12-12 at 10:00 was successfully booked!', 1, '2025-12-12 01:34:48', NULL),
(47, 7, 'Your appointment on 2025-12-12 at 11:00 was successfully booked!', 1, '2025-12-12 01:35:05', NULL),
(48, 8, 'Your appointment on 2025-12-12 at 10:00 was successfully booked!', 0, '2025-12-12 01:35:28', NULL),
(49, 7, 'Your dependent\'s appointment on 2025-12-12 at 10:00 has been booked.', 1, '2025-12-12 01:35:28', NULL),
(50, 8, 'Your appointment on 2025-12-12 at 13:00 was successfully booked!', 0, '2025-12-12 01:35:45', NULL),
(51, 7, 'Your dependent\'s appointment on 2025-12-12 at 13:00 has been booked.', 1, '2025-12-12 01:35:45', NULL),
(52, 6, 'Your appointment on 2025-12-12 at 10:30 was successfully booked!', 0, '2025-12-12 01:36:00', NULL),
(53, 6, 'Your appointment on 2025-12-12 at 13:00 was successfully booked!', 0, '2025-12-12 01:36:15', NULL),
(54, 6, 'Your appointment (December 12, 2025 at 10:30 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-12 01:36:53', NULL),
(55, 6, 'Your appointment (December 12, 2025 at 1:00 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-12 01:37:25', NULL),
(56, 8, 'Your appointment (December 12, 2025 at 10:00 AM) has been marked as completed. Thank you for visiting!', 0, '2025-12-12 01:42:53', NULL),
(57, 7, 'The appointment for your dependent (December 12, 2025 at 10:00 AM) has been marked as completed.', 1, '2025-12-12 01:42:53', NULL),
(58, 8, 'Your appointment (December 12, 2025 at 1:00 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-12 01:43:26', NULL),
(59, 7, 'The appointment for your dependent (December 12, 2025 at 1:00 PM) has been marked as completed.', 1, '2025-12-12 01:43:26', NULL),
(60, 7, 'Your password was changed successfully on December 12, 2025, 3:06 pm. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-12 07:06:32', NULL),
(61, 8, 'Your appointment on 2025-12-13 at 16:00 was successfully booked!', 0, '2025-12-12 07:08:00', NULL),
(62, 7, 'Your dependent\'s appointment on 2025-12-13 at 16:00 has been booked.', 1, '2025-12-12 07:08:00', NULL),
(63, 9, 'Your appointment on 2025-12-15 at 11:00 was successfully booked!', 0, '2025-12-12 07:09:47', NULL),
(64, 7, 'Your dependent\'s appointment on 2025-12-15 at 11:00 was successfully booked.', 1, '2025-12-12 07:09:47', NULL),
(65, 10, 'Your appointment on 2025-12-18 at 12:00 was successfully booked!', 0, '2025-12-12 07:11:11', NULL),
(66, 7, 'Your dependent\'s appointment on 2025-12-18 at 12:00 was successfully booked.', 1, '2025-12-12 07:11:11', NULL),
(67, 9, 'Your appointment on 2025-12-13 at 15:00 was successfully booked!', 0, '2025-12-12 07:13:49', NULL),
(68, 7, 'Your dependent\'s appointment on 2025-12-13 at 15:00 was successfully booked.', 1, '2025-12-12 07:13:49', NULL),
(69, 6, 'Your appointment on 2025-12-12 at 16:00 was successfully booked!', 0, '2025-12-12 07:33:52', NULL),
(70, 6, 'Your appointment on 2025-12-15 at 10:00 was successfully booked!', 0, '2025-12-12 07:34:29', NULL),
(71, 6, 'Your appointment on 2025-12-25 at 10:30 was successfully booked!', 0, '2025-12-12 07:35:18', NULL),
(72, 6, 'Your appointment (December 12, 2025 at 4:00 PM) has been cancelled.', 0, '2025-12-12 07:48:37', NULL),
(73, 11, 'Welcome to Smile-ify! Your account has been created.', 0, '2025-12-12 07:52:00', NULL),
(74, 11, 'Your appointment on 2025-12-13 at 09:00 was successfully booked.', 0, '2025-12-12 07:52:00', NULL),
(75, 11, 'Your appointment on 2025-12-13 at 12:00 was successfully booked!', 0, '2025-12-12 07:57:25', NULL),
(76, 11, 'Your appointment on 2025-12-13 at 12:00 was successfully booked!', 0, '2025-12-12 08:01:10', NULL),
(77, 11, 'Your appointment on 2025-12-13 at 13:30 was successfully booked!', 0, '2025-12-12 08:05:01', NULL),
(78, 11, 'Your appointment (December 13, 2025 at 1:30 PM) has been marked as completed. Thank you for visiting!', 0, '2025-12-12 08:08:55', NULL),
(79, 6, 'Your password was changed successfully on December 12, 2025, 4:58 pm. If this wasn’t you, please contact clinic immediately.', 0, '2025-12-12 08:58:49', NULL),
(80, 12, 'Welcome to Smile-ify! Your account has been created.', 0, '2025-12-12 09:00:26', NULL),
(81, 12, 'Your appointment on 2025-12-15 at 09:00 was successfully booked.', 0, '2025-12-12 09:00:26', NULL),
(82, 13, 'Your Secretary account has been created. Branch Assignment: Pajo, Lapu- Lapu City. Username: Paring_C', 1, '2025-12-12 09:09:08', NULL),
(83, 1, 'A new announcement titled \'Staff Christmas Party\' was added for Babag 2, Lapu-Lapu City.', 1, '2025-12-12 09:15:10', NULL),
(84, 14, 'Welcome to Smile-ify! Your account was created.', 0, '2025-12-12 09:17:38', NULL),
(85, 15, 'Your appointment on 2025-12-15 at 12:00 was successfully booked.', 0, '2025-12-12 09:17:38', NULL),
(86, 14, 'Your dependent\'s appointment on 2025-12-15 at 12:00 was successfully booked.', 0, '2025-12-12 09:17:38', NULL),
(87, 1, 'The service Dental Certificate in Babag 2, Lapu-Lapu City was set to Inactive.', 1, '2025-12-12 09:19:14', NULL),
(88, 1, 'The service Dental Certificate in Babag 2, Lapu-Lapu City was set to Active.', 1, '2025-12-12 09:20:01', NULL),
(89, 8, 'Your appointment (December 13, 2025 at 4:00 PM) has been cancelled.', 0, '2025-12-12 09:22:20', NULL),
(90, 7, 'The appointment for your dependent (December 13, 2025 at 4:00 PM) has been cancelled.', 1, '2025-12-12 09:22:20', NULL),
(91, 12, 'Your appointment has been rescheduled to December 16, 2025 at 9:00 AM.', 0, '2025-12-12 09:22:42', NULL),
(92, 10, 'Your appointment on 2025-12-16 at 11:00 was successfully booked!', 0, '2025-12-12 09:27:24', NULL),
(93, 7, 'Your dependent\'s appointment on 2025-12-16 at 11:00 was successfully booked.', 1, '2025-12-12 09:27:24', NULL),
(94, 3, 'Patient #9 Jerome San Jose has requested a Dental Certificate for transaction #19', 1, '2025-12-12 09:28:46', NULL),
(95, 7, 'Your Dental Certificate request from your appointment on December 5, 2025 at 11:00 AM has been approved.', 1, '2025-12-12 09:29:57', NULL),
(96, 7, 'Your Dental Certificate request from your appointment on December 3, 2025 at 10:00 AM has been approved.', 1, '2025-12-12 09:31:17', NULL),
(97, 7, 'Your Dental Certificate request from your appointment on November 7, 2025 at 9:00 AM has been approved.', 1, '2025-12-12 09:32:27', NULL),
(98, 17, 'Your appointment on 2025-12-16 at 09:00 was successfully booked.', 0, '2025-12-13 17:59:39', NULL),
(99, 16, 'Your dependent\'s appointment on 2025-12-16 at 09:00 was successfully booked.', 1, '2025-12-13 17:59:39', NULL),
(100, 16, 'Welcome to Smile-ify! Your account has been created.', 1, '2025-12-13 17:59:39', NULL),
(101, 19, 'Your appointment on 2025-12-15 at 09:00 was successfully booked.', 0, '2025-12-13 19:04:27', NULL),
(102, 18, 'Your dependent\'s appointment on 2025-12-15 at 09:00 was successfully booked.', 1, '2025-12-13 19:04:27', NULL),
(103, 18, 'Welcome to Smile-ify! Your account has been created.', 1, '2025-12-13 19:04:27', NULL),
(104, 20, 'Your appointment on 2025-12-17 at 09:00 was successfully booked!', 0, '2025-12-13 19:38:54', NULL),
(105, 18, 'Your dependent\'s appointment on 2025-12-17 at 09:00 was successfully booked.', 1, '2025-12-13 19:38:54', NULL),
(106, 21, 'Welcome to Smile-ify! Your account was created.', 0, '2025-12-13 19:48:15', NULL),
(107, 22, 'Your appointment on 2025-12-19 at 09:00 was successfully booked.', 0, '2025-12-13 19:48:15', NULL),
(108, 21, 'Your dependent\'s appointment on 2025-12-19 at 09:00 was successfully booked.', 0, '2025-12-13 19:48:15', NULL),
(109, 23, 'Your appointment on 2025-12-17 at 09:00 was successfully booked!', 0, '2025-12-13 20:04:25', NULL),
(110, 18, 'Your dependent\'s appointment on 2025-12-17 at 09:00 has been booked.', 1, '2025-12-13 20:04:25', NULL),
(111, 24, 'Welcome to Smile-ify! Your account has been created.', 0, '2025-12-14 11:35:13', NULL),
(112, 24, 'Your appointment on 2025-12-17 at 09:00 was successfully booked.', 0, '2025-12-14 11:35:13', NULL),
(113, 7, 'Your appointment on 2025-12-22 at 09:00 was successfully booked!', 1, '2025-12-14 16:26:50', NULL),
(114, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 16:28:45', NULL),
(115, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 16:33:24', NULL),
(116, 10, 'Your appointment has been rescheduled to December 17, 2025 at 2:00 PM.', 0, '2025-12-14 17:03:02', NULL),
(117, 7, 'The appointment for your dependent has been rescheduled to December 17, 2025 at 2:00 PM.', 1, '2025-12-14 17:03:02', NULL),
(118, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 17:03:47', NULL),
(119, 12, 'Your appointment on 2025-12-22 at 09:00 was successfully booked!', 0, '2025-12-14 18:00:13', NULL),
(120, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 18:17:33', NULL),
(121, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 18:18:53', NULL),
(122, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 18:25:03', NULL),
(123, 7, 'Your dental appointment requires action because the branch has been deactivated. \r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 18:28:31', NULL),
(124, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 18:33:19', 23),
(125, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 18:37:20', 23),
(126, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 19:10:33', 23),
(127, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 19:12:12', 41),
(128, 13, 'Appointment #23 has been cancelled by Patient #10.', 1, '2025-12-14 20:02:05', NULL),
(129, 5, 'Appointment #41 has been cancelled by Patient #7.', 1, '2025-12-14 20:02:33', NULL),
(130, 13, 'Appointment #23 has been cancelled by Patient #10.', 1, '2025-12-14 20:09:17', NULL),
(131, 7, 'The appointment #23 for your dependent has been cancelled.', 1, '2025-12-14 20:09:17', NULL),
(132, 13, 'Appointment #23 has been cancelled by Patient #10.', 1, '2025-12-14 20:10:46', NULL),
(133, 7, 'The appointment for your dependent has been cancelled.', 1, '2025-12-14 20:10:46', NULL),
(134, 5, 'Appointment #41 has been cancelled by Patient #7.', 1, '2025-12-14 20:11:11', NULL),
(135, 7, 'Your appointment has been cancelled.', 1, '2025-12-14 20:11:11', NULL),
(136, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 20:23:23', 23),
(137, 13, 'Appointment #23 has been cancelled by Patient #10.', 1, '2025-12-14 20:23:42', NULL),
(138, 7, 'The appointment for your dependent has been cancelled.', 1, '2025-12-14 20:23:42', NULL),
(139, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 20:33:19', 41),
(140, 1, 'A new announcement titled \'Closed for Christmas\' was added for Pakna-an, Mandaue City (Main Branch).', 1, '2025-12-14 20:47:00', NULL),
(141, 6, 'Your appointment on 2025-12-27 at 09:00 was successfully booked!', 0, '2025-12-14 21:10:14', NULL),
(142, 12, 'Your appointment on 2025-12-27 at 09:00 was successfully booked!', 0, '2025-12-14 21:49:53', NULL),
(143, 4, 'Appointment #41 has been rescheduled and confirmed by the patient.', 1, '2025-12-14 23:12:52', NULL),
(144, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-14 23:12:52', NULL),
(145, 10, 'Your appointment on 2025-12-24 at 12:30 was successfully booked!', 0, '2025-12-14 23:27:04', NULL),
(146, 7, 'Your dependent\'s appointment on 2025-12-24 at 12:30 has been booked.', 1, '2025-12-14 23:27:04', NULL),
(147, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-14 23:27:53', 45),
(148, 4, 'Appointment #45 has been cancelled by Patient #10.', 1, '2025-12-14 23:29:20', NULL),
(149, 7, 'The appointment for your dependent has been cancelled.', 1, '2025-12-14 23:29:20', NULL),
(150, 7, 'Your appointment on 2025-12-22 at 12:30 was successfully booked!', 1, '2025-12-15 00:05:19', NULL),
(151, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 00:06:04', 46),
(152, 12, 'Your appointment on 2025-12-24 at 14:00 was successfully booked!', 0, '2025-12-15 00:43:12', NULL),
(153, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 00:43:48', NULL),
(154, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 00:46:33', 46),
(155, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 00:47:25', NULL),
(156, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 00:48:46', 46),
(157, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:34:28', NULL),
(158, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 01:38:12', 46),
(159, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:38:19', NULL),
(160, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:38:27', NULL),
(161, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 01:39:15', 46),
(162, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:39:28', NULL),
(163, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 01:41:49', 46),
(164, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:42:00', NULL),
(165, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 01:45:23', 46),
(166, 13, 'Appointment #46 has been rescheduled and confirmed by the patient.', 1, '2025-12-15 01:49:08', NULL),
(167, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:49:08', NULL),
(168, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 01:50:26', 46),
(169, 12, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 0, '2025-12-15 01:50:26', 47),
(170, 4, 'Appointment #46 has been rescheduled and confirmed by the patient.', 1, '2025-12-15 01:51:22', NULL),
(171, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 01:51:22', NULL),
(172, 10, 'Your appointment on 2026-01-05 at 09:00 was successfully booked!', 0, '2025-12-15 09:55:00', NULL),
(173, 7, 'Your dependent\'s appointment on 2026-01-05 at 09:00 was successfully booked.', 1, '2025-12-15 09:55:00', NULL),
(174, 7, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 09:55:32', 48),
(175, 4, 'Appointment #48 has been rescheduled and confirmed by the patient.', 0, '2025-12-15 10:07:04', NULL),
(176, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 10:07:04', NULL),
(177, 7, 'Your appointment on 2026-01-07 at 09:00 was successfully booked!', 1, '2025-12-15 10:15:23', NULL),
(178, 4, 'Appointment #48 has been rescheduled and confirmed by the patient.', 0, '2025-12-15 10:31:49', NULL),
(179, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 10:31:49', NULL),
(180, 26, 'Your appointment on 2025-12-31 at 10:30 was successfully booked.', 0, '2025-12-15 15:17:16', NULL),
(181, 25, 'Your dependent\'s appointment on 2025-12-31 at 10:30 was successfully booked.', 0, '2025-12-15 15:17:16', NULL),
(182, 25, 'Welcome to Smile-ify! Your account has been created.', 0, '2025-12-15 15:17:16', NULL),
(183, 25, 'Your dental appointment requires action because the branch has been deactivated.\r\n                                            Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 15:23:26', 50),
(184, 1, 'The announcement \'Christmas Break\' in Babag 2, Lapu-Lapu City was updated.', 1, '2025-12-15 15:28:35', NULL),
(185, 1, 'The announcement \'Closed for Christmas\' in Pakna-an, Mandaue City (Main Branch) was updated.', 1, '2025-12-15 15:30:13', NULL),
(186, 1, 'The announcement \'Closed for Christmas\' in Pakna-an, Mandaue City (Main Branch) was updated.', 1, '2025-12-15 15:41:35', NULL),
(187, 13, 'Appointment #50 has been rescheduled and confirmed by the patient.', 1, '2025-12-15 15:52:07', NULL),
(188, 25, 'Your appointment has been successfully rescheduled and confirmed.', 0, '2025-12-15 15:52:07', NULL),
(189, 7, 'Your appointment on 2026-01-07 at 09:30 was successfully booked!', 1, '2025-12-15 20:40:21', NULL),
(190, 7, 'Your appointment with Dr. Love Alonzo requires action.\r\n                                    The dentist is no longer available. Please reschedule or cancel.', 1, '2025-12-15 22:05:56', 51),
(191, 7, 'Your dental appointment requires action.\r\n                                    Dr. Love Alonzo is no longer available. Please reschedule or cancel.', 1, '2025-12-15 22:18:21', 51),
(192, 4, 'Appointment #51 has been rescheduled and confirmed by the patient.', 0, '2025-12-15 22:18:53', NULL),
(193, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 22:18:53', NULL),
(194, 7, 'Your dental appointment requires action.\r\n                                            The branch has been deactivated. Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 22:20:45', 51),
(195, 5, 'Appointment #51 has been rescheduled and confirmed by the patient.', 1, '2025-12-15 22:21:01', NULL),
(196, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 22:21:01', NULL),
(197, 7, 'Your dental appointment requires action.\r\n                                            The branch has been deactivated. Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 22:22:12', 51),
(198, 13, 'Your password was changed successfully on December 16, 2025, 6:27 am. If this wasn’t you, please contact clinic immediately.', 1, '2025-12-15 22:27:59', NULL),
(199, 7, 'Your dental appointment requires action.\r\n                                            The branch has been deactivated. Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 22:39:36', 51),
(200, 13, 'Appointment #51 has been rescheduled and confirmed by the patient.', 0, '2025-12-15 23:05:27', NULL),
(201, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 23:05:27', NULL),
(202, 25, 'Your dental appointment requires action.\r\n                                            The branch has been deactivated. Please confirm a new schedule or cancel the appointment.', 0, '2025-12-15 23:05:54', 50),
(203, 7, 'Your dental appointment requires action.\r\n                                            The branch has been deactivated. Please confirm a new schedule or cancel the appointment.', 1, '2025-12-15 23:05:54', 51),
(204, 3, 'Appointment #51 has been rescheduled and confirmed by the patient.', 0, '2025-12-15 23:06:10', NULL),
(205, 7, 'Your appointment has been successfully rescheduled and confirmed.', 1, '2025-12-15 23:06:10', NULL);

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
(1, 'Senior Citizen Discount', '/images/promos/promo_1.png', 'Senior Citizen Discount for ages 60+', 'percentage', 20.00, '2025-12-12 04:08:36', '2025-12-12 04:08:36'),
(2, 'December Discount', '/images/promos/promo_2.jpg', 'Happy Holidays Discount for the Month of December', 'fixed', 500.00, '2025-12-12 04:09:35', '2025-12-12 04:09:35'),
(3, 'New Year, New Smile', '/images/promos/promo_3.jpg', 'New Year Discount', 'fixed', 250.00, '2025-12-12 17:13:06', '2025-12-12 17:13:06');

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
(1, 'qr_payment.jpg', '/images/qr/qr_payment.jpg', '2025-12-12 09:01:51');

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
(1, 'Dental Certificate', 150, 0, '2025-12-12 04:05:54', '2025-12-15 23:50:36', 0),
(2, 'Check Up/Consultation', 350, 15, '2025-12-12 04:06:15', '2025-12-15 23:50:44', 0),
(3, 'Cleaning', 700, 45, '2025-12-12 04:06:26', '2025-12-16 06:40:52', 0),
(4, 'Tooth Filling', 700, 60, '2025-12-12 04:06:40', '2025-12-12 04:06:40', 0),
(5, 'Braces (Simple)', 40000, 120, '2025-12-12 04:06:56', '2025-12-12 04:06:56', 0),
(6, 'Braces (Complicated)', 70000, 150, '2025-12-12 04:07:14', '2025-12-12 04:07:14', 0),
(7, 'Endodontics', 6000, 90, '2025-12-12 04:07:31', '2025-12-12 04:07:31', 1),
(8, 'Tooth Extraction', 1000, 30, '2025-12-12 17:12:25', '2025-12-12 17:12:25', 0);

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
(7, 6, 2, 2, '1', '2025-12-11 20:23:30', '2025-12-12 04:23:30'),
(8, 5, 2, 2, '1', '2025-12-11 20:23:30', '2025-12-12 04:23:30'),
(9, 2, 2, 2, '1', '2025-12-11 20:23:30', '2025-12-12 04:23:30'),
(10, 3, 2, 2, '1', '2025-12-11 20:23:30', '2025-12-12 04:23:30'),
(11, 7, 2, 2, '1', '2025-12-11 20:23:30', '2025-12-12 04:23:30'),
(12, 4, 2, 2, '1', '2025-12-11 20:23:30', '2025-12-12 04:23:30'),
(13, 6, 2, 3, '5', '2025-12-11 20:25:22', '2025-12-12 04:25:22'),
(14, 5, 2, 3, '5', '2025-12-11 20:25:22', '2025-12-12 04:25:22'),
(15, 2, 2, 3, '5', '2025-12-11 20:25:22', '2025-12-12 04:25:22'),
(16, 3, 2, 3, '5', '2025-12-11 20:25:22', '2025-12-12 04:25:22'),
(17, 7, 2, 3, '5', '2025-12-11 20:25:22', '2025-12-12 04:25:22'),
(18, 4, 2, 3, '5', '2025-12-11 20:25:22', '2025-12-12 04:25:22'),
(19, 6, 2, 4, '2', '2025-12-11 20:27:17', '2025-12-12 04:27:17'),
(20, 5, 2, 4, '2', '2025-12-11 20:27:17', '2025-12-12 04:27:17'),
(21, 7, 2, 4, '2', '2025-12-11 20:27:17', '2025-12-12 04:27:17'),
(22, 4, 2, 4, '2', '2025-12-11 20:27:17', '2025-12-12 04:27:17'),
(23, 8, 2, 5, '5', '2025-12-12 09:20:27', '2025-12-12 17:20:27');

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
(1, 'Cotton Rolls', 'Soft absorbent rolls used to control saliva during dental procedures', 'Consumable', 'Pack'),
(2, 'Dental Gloves (Latex)', 'Disposable gloves used in all dental procedures', 'PPE / Consumable', 'Pcs'),
(3, 'Face Masks (Surgical)', 'Protective surgical masks used by dentists during procedures', 'PPE', 'Pcs'),
(4, 'Dental Anesthetic Cartridge', 'Lidocaine 2% cartridges used for local anesthesia', 'Medication', 'Cartridges'),
(5, 'Syringes', 'Disposable Syringe', 'Aspirating Syringes', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `guardian_id` int(11) DEFAULT NULL,
  `relationship` enum('Parent','Sibling','Child') DEFAULT NULL,
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

INSERT INTO `users` (`user_id`, `guardian_id`, `relationship`, `username`, `password`, `last_name`, `middle_name`, `first_name`, `gender`, `date_of_birth`, `date_of_birth_iv`, `date_of_birth_tag`, `email`, `contact_number`, `contact_number_iv`, `contact_number_tag`, `address`, `address_iv`, `address_tag`, `role`, `branch_id`, `date_started`, `status`, `date_created`, `date_updated`, `force_logout`) VALUES
(1, NULL, NULL, 'smileify_owner', '$2y$10$AHZHH4vWMBSfNETHCXOaxOl9uyOanZAeJlnw4KcxEizcAhCAEcu02', 'Arriesgado', NULL, 'Irish', NULL, NULL, NULL, NULL, '18100807@usc.edu.ph', 'j1mAVY/e3bCidg==', 'oeQQKTFN748Iygha', 'Km6zvGNrOBSqWU0CieRBFg==', NULL, NULL, NULL, 'owner', NULL, '2025-12-12', 'Active', '2025-12-11 19:27:53', '2025-12-12 03:27:52', 0),
(3, NULL, NULL, 'Juban_J', '$2y$10$U.6V3yVM5v8rf6Sn0/QPLunGji1UOORgcjCXr6bwdXU5nAuYhTMie', 'Juban', '', 'Jay Marie', 'Female', 'bZZCDHSyi2BX4g==', 'UvjBzA2NWmPwDWG7', 'f7Ed55tjH9gKy2fMqePd7Q==', 'theartp1@gmail.com', 'P0jyB1tiY/auxw==', 's2FExkc1iQWj8BfD', '5siNn/xMwQzNvsFmOJy7QQ==', '48HBq1qs5KC5OK30xPvVKdB6pjH0SS0=', '6EKs6/wbWPdV1arG', 'eKnHcl9Cw4rg1XTw9rFkow==', 'admin', 2, '2025-10-01', 'Active', '2025-10-01 19:58:43', '2025-12-12 17:14:13', 0),
(4, NULL, NULL, 'Conde_P', '$2y$10$EFjTzAKLB9HdpOp/3A.nFOydugocjhNnk2qhQDzfzhuW83CRkoxjO', 'Conde', '', 'Perlyn', 'Female', '2CjN0TBddVPa7w==', 'WwsueJ5vIY71fuaf', 'kc41yrz0DdSRefgBOVGF5g==', 'theartp1@gmail.com', 'UtGhyaAOdShjAA==', 'f8mJLWhWda2Imnaa', 'Emf28QdfIPh4p84DJf/H0A==', 'S2zjGlk85dGloWGr6LpnP7wI', 'SPKKUz1A/uN7eHrd', 'D8DfLSJjwqpAnhD9K/2ERw==', 'admin', 1, '2025-10-01', 'Active', '2025-10-01 20:01:40', '2025-12-12 05:58:46', 0),
(5, NULL, NULL, 'Quinto_K', '$2y$10$qkkgH/tvxxP4lzf6Kng/pec7KDqXDL3GX5ii6arpZR08xOlpBw1/2', 'Quinto', '', 'Kimberly Jean', 'Female', 'FbMJTEyPh5o9bg==', 'jp3PTnhR9mnrf47E', '5EJABDN+6f6eZylGpfeFlw==', 'theartp1@gmail.com', 'aCMJ+LkB3ko/sg==', 'lIkukFiOalUyEJ10', 'GQ6CoCq+NlGNkgtXKIDFqA==', 'YTMW2O0w60QjkmC2nKsCq4yFKmNC2EfD7oVB', '7obuKyOXp9iOo/XF', 'KddceZ5ZWpPgdIEszQzKzQ==', 'admin', 3, '2025-10-01', 'Active', '2025-10-01 20:03:07', '2025-12-12 04:03:07', 0),
(6, NULL, NULL, 'Cuizon_D', '$2y$10$Lx0oHcax1h927C27lvMJ8eunWNpi8UeoxKqIEcazq4e22sou3FhbG', 'Cuizon', '', 'Dennis', 'Female', 'h3lA9/5GNvQaAQ==', 'HgWGr0sRm6RIPDwB', 'O3ittEcLVb+Gx08YrfNP1A==', 'josephparchaso@gmail.com', 'wnoC1yUDvTCW5A==', 'BFmqb0TsF/ImRXUo', 'KRpUdyZ1HwBD9QN8Uzmp8w==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-11 21:24:30', '2025-12-15 05:10:14', 0),
(7, NULL, NULL, 'San Jose_Y', '$2y$10$dA3Pe6jsXsT1miA7Kx3sZe217rolsZb1cf9W4FQu7ER4pRxGB1DDO', 'San Jose', '', 'Yel Marie', 'Female', 'RRALLUQN47udfw==', 'vWjc1BAySp36Ezyt', 'KdPPT7xtjvDQWm1J6y57Fg==', 'josephparchaso@gmail.com', '5imdTDCsz0hSEA==', 'XdnEOqzTm5IsWQSl', '/BctI9w/BjpUJykB7akJyA==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-11 21:36:20', '2025-12-16 04:40:21', 0),
(8, 7, NULL, NULL, NULL, 'San Jose', NULL, 'Solene', 'Female', 'rTxKFdQL8IR4FA==', 'LuKXLLxBuiXUPBA3', '1t4NX/+E+PUXLBg2VcAFQQ==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-11 21:36:20', '2025-12-12 15:08:00', 0),
(9, 7, NULL, NULL, NULL, 'San Jose', NULL, 'Jerome', 'Male', '/Uqra9T3myXR/g==', 'jiifd9vbbogNzfVv', '1GBC4Hbp4VpfGW9WeT0agg==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-12 07:09:47', '2025-12-12 15:13:49', 0),
(10, 7, NULL, NULL, NULL, 'San Jose', NULL, 'Chloe', 'Female', '9QKPCzewWzO6zg==', 'qDFIGmZKJDQ6joDl', 'A34UqOIYxudlS8tmCBoZPA==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-12 07:11:11', '2025-12-15 17:55:00', 0),
(11, NULL, NULL, 'Alcoriza_R', '$2y$10$DB.dRccfaXfEV0U5bvN1Eebu3yDdBfAdPn5V1SsOFF4FpWn1tA/XK', 'Alcoriza', 'Rio', 'Ryan June', 'Male', 'of08GpWPmDB0hQ==', 'bErK757Jntgskyh3', 'DJ+2ww2bR1tWHmFNULSpqA==', '18105953@usc.edu.ph', 'g261hmfQEEcrFg==', '/CUbGyaUisVFWh/X', 'SgWx4VCPQKTb+GKQQYrB3w==', NULL, NULL, NULL, 'patient', 1, NULL, 'Active', '2025-12-12 07:52:00', '2025-12-12 16:05:01', 0),
(12, NULL, NULL, 'Saban_R', '$2y$10$frQtlcT6iVRrGSKkSXruauX/zM0rJ4MVY/ynLh10rJsNpElO8ydy.', 'Saban', '', 'Ralph', 'Male', 'qRi2Obn2S1aXGg==', 'Ud9+ku93Ou0b5qHz', 'htb2kO5UAQVc2bN05I/6Jg==', 'josephparchaso@gmail.com', 'Fn7BK3y/b1/9Dw==', 'jRtQzAY30PJgkYd2', 'B+JUlOD18Qj/1yhc2n3Fyg==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-12 09:00:26', '2025-12-15 08:43:12', 0),
(13, NULL, NULL, 'Paring_C', '$2y$10$aLxo7g/BemSdZgG5Zz5xtOKX7e5oe/uD/AYOVrTy7f0VqXGfapzK6', 'Paring', 'Lener', 'Chresaian', 'Male', 'QUHw2SvmZy9rsw==', 'mGlCCVCWSDq+nwh5', 'yPyS59MS5+84f7T5Rc7Kiw==', 'theartp1@gmail.com', 'W1mfvCfrYblBwA==', 'JOhLWtS7dxRQBRKH', 'G2g/UaVSu7oIk81WdSDJAg==', 'u0fscWs4v/XJ+kjYhrSj1WuqAewL5A==', 'l+8HqTt0JiWGazsv', 'V3IO0kdqYor6JxwtxXsohg==', 'admin', 4, '2025-12-13', 'Active', '2025-12-12 09:09:08', '2025-12-12 17:09:08', 0),
(14, NULL, NULL, 'Castillo_J', '$2y$10$YahKv7tD9kb/O29KyTbf3Ow2MUWip6knNiHA/8HZ2lMXYuvDu1Y6u', 'Castillo', '', 'Jane', 'Female', '900okWIxQThkTQ==', 'j2O5r7KxZ5nUtUus', 'OgOQCeJDOQIeVGDfLMqRWw==', 'josephparchaso@gmail.com', 'hTz1aE+yZKNEIw==', 'Uxp1ZzPCuTIdbI4D', '3OvdjmKZow75/1tGsUyvKg==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-12 09:17:38', '2025-12-12 17:17:38', 0),
(15, 14, NULL, NULL, NULL, 'Castillo', NULL, 'Shekinah', 'Female', 'wUVhALuk03fovg==', 'gq40ENYCpLFqp4hK', 'lbJJBW/1O8n9m7hERVseHQ==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-12 09:17:38', '2025-12-12 17:17:38', 0),
(16, NULL, NULL, 'Parchaso_H', '$2y$10$n0kUhEwJ.wPFjF7kc8GN3.t/SjIsrGv5RMrWFHIyBMp2fjfc5Nt4u', 'Parchaso', '', 'Honorato', 'Male', '3WjVF8gG88s04Q==', 'idXj3EmE0GC8P3/t', 'KYdoJwzI+iD25YiwWbPyUA==', 'josephparchaso@gmail.com', 'x9QrZiH2uI4zog==', 'CQvB8BKm7gB7zJoT', 'N/y2r66+Nlx83QHRaq0ppQ==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 17:59:39', '2025-12-14 01:59:39', 0),
(17, 16, NULL, NULL, NULL, 'Parchaso', NULL, 'Harlene', 'Female', '1NuKY7QNpixqAQ==', 'ysN0U7JNBj4qghFH', '+JI7XHEjXcCtnfFVUAVTJA==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 17:59:39', '2025-12-14 01:59:39', 0),
(18, NULL, NULL, 'Potot_R', '$2y$10$QIUCwKtTNICYKuHUzao8mOk04UdLzD4GZZe4IfDix7CSwr97binRS', 'Potot', '', 'Richard', 'Male', '3ssIx6A7yulJSQ==', 'LKuB0WcHWTDjo0ke', 'ZdTAqacEt5joR6UNxktQfA==', 'josephparchaso@gmail.com', 'mAr/oqK5wakfeg==', 'bMEm7RpFnNBOsXk9', 'H2Z2j4QC4Th0uNSGNy9mYA==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 19:04:27', '2025-12-14 03:04:27', 0),
(19, 18, 'Child', NULL, NULL, 'Potot', NULL, 'Thamara', 'Male', '2DwDA7voCN5HRw==', 'twinjKuz2XDAn48X', 'BlzvniQMIvcbZL8LdoUuhA==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 19:04:27', '2025-12-14 03:04:27', 0),
(20, 18, 'Child', NULL, NULL, 'Potot', NULL, 'Anna', 'Female', 's2uewT5p/9q+dQ==', 'FO9Dh72x+Tr3xE6C', 'El5cy4tYreeWJd6qH/rtSg==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 19:38:54', '2025-12-14 03:38:54', 0),
(21, NULL, NULL, 'Lipardo_D', '$2y$10$VDoiEhiRZyNGIMZbtah8Uee2a6o1s7e.jMJv5K9L2K6uwBwLnOreq', 'Lipardo', '', 'Daddy', 'Male', '/mHz8MA2pHWWkg==', '8kOJ1LM6DjubAfJJ', 'w/ZCKcRq9pHC4LOtF2w/Ug==', 'josephparchaso@gmail.com', 'Ndm8TO0ugqGlrg==', 'NRIfPeSsT8k3QDsV', 'PZiJRdwpOKweAauVUp0D+Q==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 19:48:15', '2025-12-14 03:48:15', 0),
(22, 21, 'Child', NULL, NULL, 'Lipardo', NULL, 'JC', 'Male', 'Eu2I3g/EX5g8Xg==', 'evxyu7KGvq9hGSjy', '8x20hzmb3XFCQxeSgr5zcQ==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 19:48:15', '2025-12-14 03:48:15', 0),
(23, 18, 'Parent', NULL, NULL, 'Potot', NULL, 'Fely', 'Female', 'QNk07kgV7i/S0Q==', 'AxODythu7xy7bAGo', 'hoPlifzhVoGzmI8S0ucePw==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-13 20:04:25', '2025-12-14 04:04:25', 0),
(24, NULL, NULL, 'Tagalog_J', '$2y$10$2xbwioxJo0STwiPEG/C5Yu35KnSGHbjWf.75yj9sKfFLKKPbTqetW', 'Tagalog', 'Enero', 'Janz', 'Male', 'RaVY+3DTSUlIdA==', 'CNMNA4b43Be0eZ78', '5odHe3b+5pqtemmxnNTAkw==', 'wuwayinlin031@gmail.com', 'b5t1kzu31275Ow==', '3mU3ci7XIVeEubop', '8Z/tVDduc+Z7vq3jTm5xog==', NULL, NULL, NULL, 'patient', 1, NULL, 'Active', '2025-12-14 11:35:13', '2025-12-14 19:35:13', 0),
(25, NULL, NULL, 'Lipardo_C', '$2y$10$jrkBHHHXA0mc3BUBMaORoOYu.fIeGyNlKT8FydwUqaS6gsS6CpNHm', 'Lipardo', '', 'Cris', 'Female', 'MwBU4HOVaKssWQ==', 'HnQY5Kxu23By5i8K', 'a7phArwF6WxmNRSYUgpR7g==', 'theartp1@gmail.com', 'LFv+h/siVS15TQ==', 'Y+GixfozHbSLgEYy', '+puThN9674m+GIkvXKnOmQ==', NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-15 15:17:16', '2025-12-15 23:17:16', 0),
(26, 25, 'Child', NULL, NULL, 'Lipardo', NULL, 'Juls', 'Male', 'jnpeK88otdsYNg==', 'hLA22VYVzQmOUfSY', 'cZJ3bX16EcxBthRLXAiRng==', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'patient', 2, NULL, 'Active', '2025-12-15 15:17:16', '2025-12-15 23:17:16', 0);

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
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `appointment_services`
--
ALTER TABLE `appointment_services`
  MODIFY `appointment_services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `appointment_transaction`
--
ALTER TABLE `appointment_transaction`
  MODIFY `appointment_transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `branch_announcements`
--
ALTER TABLE `branch_announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branch_promo`
--
ALTER TABLE `branch_promo`
  MODIFY `branch_promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `branch_service`
--
ALTER TABLE `branch_service`
  MODIFY `branch_services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `branch_supply`
--
ALTER TABLE `branch_supply`
  MODIFY `branch_supplies_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `dental_prescription`
--
ALTER TABLE `dental_prescription`
  MODIFY `prescription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `dental_tips`
--
ALTER TABLE `dental_tips`
  MODIFY `tip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `dental_transaction`
--
ALTER TABLE `dental_transaction`
  MODIFY `dental_transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `dental_transaction_services`
--
ALTER TABLE `dental_transaction_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `dental_vital`
--
ALTER TABLE `dental_vital`
  MODIFY `vitals_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `dentist`
--
ALTER TABLE `dentist`
  MODIFY `dentist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `dentist_branch`
--
ALTER TABLE `dentist_branch`
  MODIFY `dentist_branch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `dentist_schedule`
--
ALTER TABLE `dentist_schedule`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=477;

--
-- AUTO_INCREMENT for table `dentist_service`
--
ALTER TABLE `dentist_service`
  MODIFY `dentist_services_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=206;

--
-- AUTO_INCREMENT for table `promo`
--
ALTER TABLE `promo`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `qr_payment`
--
ALTER TABLE `qr_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `service_supplies`
--
ALTER TABLE `service_supplies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `supply`
--
ALTER TABLE `supply`
  MODIFY `supply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
