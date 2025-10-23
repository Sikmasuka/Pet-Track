-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 12:17 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `pettrackdb`
-- --------------------------------------------------------
-- Table structure for table `admin`
CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_username` varchar(50) NOT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `admin_password` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_username`, `admin_name`, `admin_password`) VALUES
(2, 'vinjin', 'Jin Hobin', 'Vinjin123!'),
(3, 'admin1', NULL, '$2y$10$vf7UcbAPvG6O5z2jvaEeGuiFy8KsjPW2ly6HMRd9X5LjM51uyibuS'),
(9, 'jonggun', 'Park Jong Geon', '$2y$10$mStnLP.wsnGk8DASSsPgIuuSHp7Zy3GVeib5VTJ5pkMs6GJ74L952');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `appointment_date` date DEFAULT NULL,
  `appointment_time` time DEFAULT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) DEFAULT 'Scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `duration` int(11) NOT NULL DEFAULT 90
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `owner_name`, `contact_number`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_at`, `updated_at`, `duration`) VALUES
(46, 'tikoy', '091230123', '2025-08-30', '13:00:00', 'Checkup', 'Scheduled', '2025-08-24 05:37:52', NULL, 90),
(47, 'tikay', '1231242131', '2025-08-30', '15:00:00', 'Surgery', 'Scheduled', '2025-08-24 05:40:33', NULL, 90),
(48, 'James Lee', '09372841234', '2025-09-10', '14:30:00', 'Checkup', 'Scheduled', '2025-08-27 14:57:40', NULL, 90),
(49, 'Mikaykay Abecia', '532324223123', '2025-09-10', '09:30:00', 'Grooming', 'Scheduled', '2025-08-27 15:01:44', NULL, 90),
(50, 'Seongji Yeok', '093925166643', '2025-08-28', '14:30:00', 'Vaccination', 'Scheduled', '2025-08-27 15:37:48', NULL, 90),
(51, 'Sheryl Lozano', '21312421312', '2025-09-18', '14:30:00', 'Grooming', 'Scheduled', '2025-09-08 01:57:16', NULL, 90),
(52, 'Danreb Salvacion', '01234567891', '2025-09-18', '16:30:00', 'Grooming', 'Scheduled', '2025-09-11 03:58:26', NULL, 90),
(53, 'tokneneng', '09392516664', '2025-09-18', '09:30:00', 'Vaccination', 'Scheduled', '2025-09-11 13:48:20', NULL, 90),
(54, 'Mario Santos', '12345678901', '2025-10-08', '09:00:00', 'Grooming', 'Scheduled', '2025-10-01 13:33:21', NULL, 90),
(55, 'Danreb Salvacionss', 'Dongkits', '2025-10-09', '14:30:00', 'Checkup', 'Scheduled', '2025-10-02 05:26:30', NULL, 90),
(56, 'Maricar T. Bahala', '12345678910', '2025-10-10', '14:30:00', 'Checkup', 'Scheduled', '2025-10-04 14:07:00', NULL, 90),
(57, 'Bulilit Dela Cera', '09392516664', '2025-10-10', '09:30:00', 'Checkup', 'Scheduled', '2025-10-06 13:35:57', NULL, 90),
(58, 'Maris Awitin', '012345678901', '2025-10-11', '14:30:00', 'Vaccination', 'Scheduled', '2025-10-06 14:28:35', NULL, 90);

-- --------------------------------------------------------

--
-- Table structure for table `archive`
--

CREATE TABLE `archive` (
  `id` int(11) NOT NULL,
  `original_table` varchar(50) NOT NULL,
  `original_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `deleted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive`
--

INSERT INTO `archive` (`id`, `original_table`, `original_id`, `data`, `deleted_at`) VALUES
(1, 'Pet', 15, '{\"pet_id\":15,\"pet_name\":\"tigon\",\"pet_sex\":\"Female\",\"pet_weight\":\"5.00\",\"pet_breed\":\"bulldog\",\"pet_birth_date\":\"2345-11-12\",\"client_id\":16}', '2025-07-19 00:06:14'),
(8, 'Client', 16, '{\"client_id\":16,\"client_name\":\"kogang\",\"client_address\":\"maniso\",\"client_contact_number\":\"29384956758\"}', '2025-07-20 16:18:29'),
(10, 'Client', 25, '{\"client_id\":25,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:20:16'),
(12, 'Client', 24, '{\"client_id\":24,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:20:19'),
(14, 'Client', 23, '{\"client_id\":23,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:20:22'),
(16, 'Client', 22, '{\"client_id\":22,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:20:25'),
(18, 'Client', 20, '{\"client_id\":20,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:20:30'),
(20, 'Client', 21, '{\"client_id\":21,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:20:32'),
(21, 'Pet', 18, '{\"pet_id\":18,\"pet_name\":\"tigok\",\"pet_sex\":\"Male\",\"pet_weight\":\"8.00\",\"pet_breed\":\"bulldog\",\"pet_birth_date\":\"2123-12-31\",\"client_id\":19}', '2025-07-20 18:59:05'),
(22, 'Client', 19, '{\"client_id\":19,\"client_name\":\"kangkong\",\"client_address\":\"barangay 6\",\"client_contact_number\":\"9828383829\"}', '2025-07-20 18:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(100) DEFAULT NULL,
  `client_address` text DEFAULT NULL,
  `client_contact_number` varchar(15) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`client_id`, `client_name`, `client_address`, `client_contact_number`, `status`, `updated_at`) VALUES
(42, 'Roselyn Villanueva', 'Purok 2, Barangay Cogon, Balingasag, Misamis Oriental', '09176543210', 1, '2025-09-11 04:03:02'),
(44, 'Roberto Lagbas', 'Brgy. Mandangoa, Balingasag, Misamis Oriental', '09281234567', 1, NULL),
(47, 'Boknoy Esmaels', 'Barangay 3, Balingasag, Misamis Oriental', '09392516664', 1, NULL),
(48, 'Maria Teresa Dela Cruz', 'Poblacion, Balingasag, Misamis Oriental', '09123456789', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `Log_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Role` enum('admin','veterinarian') DEFAULT NULL,
  `Action_Type` varchar(50) DEFAULT NULL,
  `Table_Affected` varchar(50) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`Log_ID`, `User_ID`, `Role`, `Action_Type`, `Table_Affected`, `Description`, `Timestamp`) VALUES
(111, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-15 21:16:25'),
(112, 1, NULL, 'update', 'Admin', 'janpaul updated client \'janpaul2\' with pet \'chokoy\'', '2025-08-15 21:30:14'),
(113, 1, NULL, 'update', 'Admin', 'janpaul updated client \'janpaul3\' with pet \'chokoy\'', '2025-08-15 21:30:27'),
(114, 1, NULL, 'update', 'Admin', 'janpaul updated client \'janpaul2\'', '2025-08-15 22:30:22'),
(115, 1, NULL, 'update', 'Admin', 'janpaul updated client \'janpaul3\'', '2025-08-15 22:30:30'),
(116, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-15 22:30:37'),
(117, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-16 21:03:49'),
(118, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-16 21:03:57'),
(119, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-16 22:05:35'),
(120, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-16 22:06:12'),
(121, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-16 22:08:41'),
(122, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-16 22:58:24'),
(123, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-16 23:02:13'),
(124, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-16 23:05:13'),
(125, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-16 23:24:46'),
(126, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-17 18:36:09'),
(127, 0, NULL, 'Appointment', 'Guest', 'Guest Test User2 booked an appointment', '2025-08-17 19:14:30'),
(128, 0, NULL, 'Appointment', 'Guest', 'Guest janpaul25 booked an appointment on September 11, 2025 at 3:55 PM', '2025-08-17 20:21:08'),
(129, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-17 20:21:29'),
(130, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-17 20:52:44'),
(131, 0, NULL, 'Appointment', 'Guest', 'Guest Test User234 booked an appointment on September 10, 2025 at 2:45 PM', '2025-08-17 20:55:09'),
(132, 0, NULL, 'Appointment', 'Guest', 'Guest Test User6 booked an appointment on August 20, 2025 at 2:41 PM', '2025-08-17 21:27:11'),
(133, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-17 22:16:17'),
(134, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-18 09:35:33'),
(135, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-18 09:38:51'),
(136, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-18 09:40:16'),
(137, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-18 09:57:05'),
(138, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-18 09:57:15'),
(139, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-18 10:09:36'),
(140, 1, NULL, 'Login', 'Admin', 'admin Successfully Logged in', '2025-08-18 10:28:33'),
(141, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-18 10:33:37'),
(142, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-18 10:34:07'),
(143, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-18 11:38:13'),
(144, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-18 11:39:22'),
(145, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-18 11:40:08'),
(146, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-18 11:42:47'),
(147, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-18 11:44:00'),
(148, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-18 11:44:10'),
(149, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-19 21:48:06'),
(150, 0, NULL, 'Appointment', 'Guest', 'Guest Samuel Seo booked an appointment on August 21, 2025 at 3:23 PM', '2025-08-19 21:50:19'),
(151, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-19 22:10:46'),
(152, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-20 11:01:33'),
(153, 1, NULL, 'Login', 'Veterinarian', 'Jan Paul Michael M. Dela Cera Successfully Logged in', '2025-08-20 11:03:56'),
(154, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 11:51:49'),
(155, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-20 11:52:03'),
(156, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 11:56:11'),
(157, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-20 11:56:27'),
(158, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-20 18:12:13'),
(159, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 18:22:24'),
(160, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 19:15:21'),
(161, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 19:25:31'),
(162, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 19:31:20'),
(163, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-20 19:31:41'),
(164, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 19:34:10'),
(165, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-20 19:59:47'),
(166, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-20 20:00:24'),
(167, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-20 20:18:32'),
(168, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-21 11:21:21'),
(169, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-21 12:49:12'),
(170, 0, NULL, 'Appointment', 'Guest', 'Guest Daniel Park booked an appointment on August 22, 2025 at 2:23 PM', '2025-08-21 12:49:46'),
(171, 0, NULL, 'Appointment', 'Guest', 'Guest Shingen Yamazaki booked an appointment on August 23, 2025 at 3:21 PM', '2025-08-21 12:52:46'),
(172, 4, NULL, 'delete', 'Admin', 'samuel archived client \'janpaul3\'', '2025-08-21 13:36:16'),
(173, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-22 08:12:41'),
(174, 4, NULL, 'update', 'Admin', 'samuel updated client \'janpaul4\'', '2025-08-22 08:13:14'),
(175, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-22 08:13:50'),
(176, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-22 08:46:25'),
(177, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-22 09:55:06'),
(178, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-24 10:25:16'),
(179, 0, NULL, 'Appointment', 'Guest', 'Guest shikoy2 booked an appointment on August 30, 2025 at 2:12 PM', '2025-08-24 11:52:19'),
(180, 0, NULL, 'Appointment', 'Guest', 'Guest tikoy booked an appointment on September 12, 2025 at 2:00 PM', '2025-08-24 12:20:51'),
(181, 0, NULL, 'Appointment', 'Guest', 'Guest tikoy booked an appointment on September 20, 2025 at 1:30 PM', '2025-08-24 12:23:08'),
(182, 0, NULL, 'Appointment', 'Guest', 'Guest tikoykoy booked an appointment on August 28, 2025 at 2:30 PM', '2025-08-24 13:12:20'),
(183, 0, NULL, 'Appointment', 'Guest', 'Guest tikoy booked an appointment on August 30, 2025 at 1:00 PM', '2025-08-24 13:37:52'),
(184, 0, NULL, 'Appointment', 'Guest', 'Guest tikay booked an appointment on August 30, 2025 at 3:00 PM', '2025-08-24 13:40:33'),
(185, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-25 00:09:31'),
(186, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-25 00:13:48'),
(187, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-25 14:06:28'),
(188, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-27 22:57:04'),
(189, 0, NULL, 'Appointment', 'Guest', 'Guest James Lee booked an appointment on September 10, 2025 at 2:30 PM', '2025-08-27 22:57:40'),
(190, 0, NULL, 'Appointment', 'Guest', 'Guest Mikaykay Abecia booked an appointment on September 10, 2025 at 9:30 AM', '2025-08-27 23:01:44'),
(191, 0, NULL, 'Appointment', 'Guest', 'Guest Seongji Yeok booked an appointment on August 28, 2025 at 2:30 PM', '2025-08-27 23:37:48'),
(192, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-28 22:53:34'),
(193, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-29 07:13:42'),
(194, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-29 07:14:05'),
(195, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-29 07:19:23'),
(196, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-29 10:03:39'),
(197, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-29 20:53:50'),
(198, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Maria Santos\', pet \'Buddy\', and medical record', '2025-08-29 22:24:08'),
(199, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Maria Santos\'', '2025-08-29 23:01:59'),
(200, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Odemil Uyan\'', '2025-08-29 23:02:44'),
(201, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Odemil Uyan\'', '2025-08-29 23:08:42'),
(202, 4, NULL, 'restore', 'Admin', 'samuel restored client \'Odemil Uyan\' and associated pets and medical records', '2025-08-29 23:22:02'),
(203, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Odemil Uyan\'', '2025-08-29 23:22:10'),
(204, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-30 23:50:22'),
(205, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Odemil Uyan\' and associated pets and medical records', '2025-08-30 23:50:36'),
(206, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Maria Santos\'', '2025-08-30 23:50:43'),
(207, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Maria Santos\' and associated pets and medical records', '2025-08-30 23:50:47'),
(208, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-31 09:35:37'),
(209, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-31 13:15:27'),
(210, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-31 20:56:51'),
(211, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-31 21:19:51'),
(212, 4, NULL, 'update', 'Admin', 'samuel updated medical record ID 23 for pet ID 35 (\'chokoy\')', '2025-08-31 21:20:28'),
(213, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-08-31 21:27:07'),
(214, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-31 22:15:05'),
(215, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-08-31 22:48:19'),
(216, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-01 16:02:45'),
(217, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 16:03:46'),
(218, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 16:16:09'),
(219, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 16:19:42'),
(220, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 16:47:20'),
(221, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-01 21:15:56'),
(222, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-01 21:16:29'),
(223, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-01 21:18:24'),
(224, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 21:18:47'),
(225, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 21:19:07'),
(226, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 21:20:05'),
(227, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-01 21:44:25'),
(228, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-01 21:45:14'),
(229, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-01 21:53:45'),
(230, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-02 22:25:37'),
(231, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Maria Santos\', pet \'Buddy\', and medical record', '2025-09-02 22:28:21'),
(232, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Maria Santos\'', '2025-09-02 22:28:26'),
(233, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Maria Santos\' and associated pets and medical records', '2025-09-02 22:28:41'),
(234, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-04 09:23:14'),
(235, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-04 09:26:16'),
(236, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-04 09:27:27'),
(237, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-04 09:28:29'),
(238, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-04 09:33:21'),
(239, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-04 09:38:12'),
(241, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-05 21:37:33'),
(242, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-06 17:42:21'),
(243, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-06 17:45:07'),
(244, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 18:11:54'),
(245, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 18:15:05'),
(246, 4, NULL, 'delete', 'Admin', 'samuel archived client \'janpaul4\'', '2025-09-07 18:27:43'),
(247, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'janpaul4\' and associated pets and medical records', '2025-09-07 18:27:52'),
(248, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-07 21:58:54'),
(249, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 22:01:03'),
(250, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Boknoy\', pet \'wawang\', and medical record', '2025-09-07 22:05:02'),
(251, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 22:45:55'),
(252, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 22:49:37'),
(253, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-07 22:49:59'),
(254, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 22:51:20'),
(255, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-07 22:52:41'),
(256, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 22:52:49'),
(257, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-07 23:03:15'),
(258, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and updated a medical record', '2025-09-07 23:19:27'),
(259, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and updated a medical record', '2025-09-07 23:27:53'),
(260, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmael\'', '2025-09-07 23:30:21'),
(261, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Boknoy Esmael\' and associated pets and medical records', '2025-09-07 23:30:25'),
(262, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and updated a medical record', '2025-09-07 23:36:28'),
(263, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and updated a medical record', '2025-09-07 23:36:58'),
(264, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and updated a medical record', '2025-09-07 23:37:43'),
(265, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and updated/added a medical record', '2025-09-07 23:52:59'),
(266, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmael\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-08 00:14:10'),
(267, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmael\'', '2025-09-08 00:14:30'),
(268, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Boknoy Esmael\' and associated pets and medical records', '2025-09-08 00:14:38'),
(269, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-08 00:29:49'),
(270, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-08 09:52:56'),
(271, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmale\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-08 09:53:46'),
(272, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmale\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-08 09:54:03'),
(273, 0, NULL, 'Appointment', 'Guest', 'Guest Sheryl Lozano booked an appointment on September 18, 2025 at 2:30 PM', '2025-09-08 09:57:16'),
(274, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-08 09:58:36'),
(275, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-08 09:58:47'),
(276, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-08 10:15:52'),
(277, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Maria Lopez\', pet \'Max\', and medical record', '2025-09-08 10:19:07'),
(278, 4, NULL, 'update', 'Admin', 'samuel updated client \'Maria Lopez\' and pet \'Max\' and updated/added a medical record', '2025-09-08 10:19:28'),
(279, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmale\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-08 10:19:51'),
(280, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmale\'', '2025-09-08 10:20:07'),
(281, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Boknoy Esmale\' and associated pets and medical records', '2025-09-08 10:20:45'),
(282, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-09 08:52:42'),
(283, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-09 09:20:28'),
(284, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-09 09:52:51'),
(285, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-09 10:35:51'),
(286, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-10 21:22:20'),
(287, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-10 22:12:43'),
(288, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-10 22:25:40'),
(289, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-10 22:33:32'),
(290, 5, NULL, 'Login', 'Veterinarian', 'Dr. Odemil Uyan Successfully Logged in', '2025-09-10 22:44:56'),
(291, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-10 22:54:47'),
(292, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-10 22:55:02'),
(293, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-11 10:09:53'),
(294, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-11 10:11:58'),
(295, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-11 11:46:49'),
(296, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-11 11:50:29'),
(297, 5, NULL, 'Login', 'Veterinarian', 'Dr. Odemil Uyan Successfully Logged in', '2025-09-11 11:51:22'),
(298, 5, NULL, 'add', 'Admin', 'uyan_vet added a new client \'Roselyn Villanueva\', pet \'Mingming\', and medical record', '2025-09-11 11:54:18'),
(299, 0, NULL, 'Appointment', 'Guest', 'Guest Danreb Salvacion booked an appointment on September 18, 2025 at 4:30 PM', '2025-09-11 11:58:26'),
(300, 5, NULL, 'delete', 'Admin', 'uyan_vet archived client \'Roselyn Villanueva\'', '2025-09-11 12:02:30'),
(301, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Roselyn Villanueva\' and associated pets and medical records', '2025-09-11 12:03:02'),
(302, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-11 12:18:23'),
(303, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-11 21:38:08'),
(304, 0, NULL, 'Appointment', 'Guest', 'Guest tokneneng booked an appointment on September 18, 2025 at 9:30 AM', '2025-09-11 21:48:20'),
(305, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-11 22:22:12'),
(306, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-12 09:10:14'),
(307, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-12 10:26:01'),
(308, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-12 10:26:29'),
(309, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-12 21:41:32'),
(310, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-12 21:41:46'),
(311, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-13 21:33:47'),
(312, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-13 22:07:48'),
(313, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-13 22:11:14'),
(314, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-13 22:13:37'),
(315, 2, NULL, 'Login', 'Admin', 'vinjin Successfully Logged in', '2025-09-13 22:40:02'),
(316, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo Successfully Logged in', '2025-09-13 22:43:03'),
(317, 3, NULL, 'Login', 'Admin', 'admin1 logged in', '2025-09-14 20:45:14'),
(318, 9, NULL, 'Login', 'Veterinarian', 'Dr. Test Vet logged in', '2025-09-14 20:45:31'),
(319, 3, NULL, 'Login', 'Admin', 'admin1 logged in', '2025-09-14 20:48:09'),
(320, 9, NULL, 'Login', 'Veterinarian', 'Dr. Test Vet logged in', '2025-09-14 20:50:38'),
(321, 9, NULL, 'Login', 'Veterinarian', 'Dr. Test Vet logged in', '2025-09-14 20:56:06'),
(322, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-14 21:21:29'),
(323, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-15 21:45:44'),
(324, 3, NULL, 'Login', 'Admin', 'admin1 logged in', '2025-09-15 21:48:34'),
(325, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-15 22:07:47'),
(326, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-15 22:59:08'),
(327, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-16 00:48:25'),
(328, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-16 11:54:02'),
(329, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-16 14:03:47'),
(330, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-16 14:05:06'),
(331, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-16 14:22:50'),
(332, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-16 14:30:30'),
(333, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-16 15:10:26'),
(334, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-16 16:34:20'),
(335, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-26 02:39:44'),
(336, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-26 02:52:30'),
(337, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-26 02:52:53'),
(338, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-09-26 02:53:08'),
(339, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-26 02:56:50'),
(340, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-26 03:42:33'),
(341, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-28 18:59:45'),
(342, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmale\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-28 19:29:34'),
(343, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmale\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-28 20:56:48'),
(344, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmales\' and pet \'wawangloy\' and updated/added a medical record', '2025-09-28 20:57:01'),
(345, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmales\' and pet \'wawangloys\' and updated/added a medical record', '2025-09-28 20:57:11'),
(346, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmales\'', '2025-09-28 20:57:20'),
(347, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Boknoy Esmales\' and associated pets and medical records', '2025-09-28 20:57:28'),
(348, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-28 20:57:40'),
(349, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-28 20:57:59'),
(357, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-29 21:28:22'),
(358, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-09-29 23:11:54'),
(359, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Roberto Lagbas\', pet \'Bruno\', and medical record', '2025-09-30 22:21:02'),
(360, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Roberto Lagbas\'', '2025-09-30 22:21:36'),
(361, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Roberto Lagbas\' and associated pets and medical records', '2025-09-30 22:21:41'),
(362, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Roberto Lagbas\'', '2025-09-30 22:21:48'),
(363, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Roberto Lagbas\' and associated pets and medical records', '2025-09-30 22:21:53'),
(364, 0, NULL, 'Appointment', 'Guest', 'Guest Mario Santos booked an appointment on October 8, 2025 at 9:00 AM', '2025-10-01 21:33:21'),
(365, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-01 21:33:38'),
(366, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmales\'', '2025-10-01 22:24:54'),
(367, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Boknoy Esmales\' and associated pets and medical records', '2025-10-01 22:25:15'),
(368, 4, NULL, 'delete', 'Admin', 'samuel archived client \'janpaul4\'', '2025-10-01 22:25:26'),
(369, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'janpaul4\' and associated pets and medical records', '2025-10-01 22:25:30'),
(370, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-01 22:27:29'),
(371, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-01 22:44:07'),
(372, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmales\'', '2025-10-01 22:44:16'),
(373, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Boknoy Esmales\' and associated pets and medical records', '2025-10-01 22:44:21'),
(374, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-01 22:44:32'),
(375, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 10:21:05'),
(376, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 10:22:36'),
(377, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-02 10:34:45'),
(378, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 10:47:41'),
(379, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 11:06:35'),
(380, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 11:06:59'),
(381, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 11:07:39'),
(382, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-02 11:10:01'),
(383, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 11:13:15'),
(384, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Roberto Lagbas\', pet \'Bruno\', and medical record', '2025-10-02 11:16:28'),
(385, 4, NULL, 'update', 'Admin', 'samuel updated client \'Roberto Lagbas\' and pet \'Bruno\' and updated/added a medical record', '2025-10-02 11:18:00'),
(386, 0, NULL, 'Appointment', 'Guest', 'Guest Danreb Salvacionss booked an appointment on October 9, 2025 at 2:30 PM', '2025-10-02 13:26:30'),
(387, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 13:26:44'),
(388, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 13:49:37'),
(389, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-02 14:16:49'),
(390, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 14:27:25'),
(391, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-02 14:27:38'),
(392, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 14:38:18'),
(393, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-02 14:38:34'),
(394, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-02 15:12:11'),
(395, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-03 13:58:15'),
(396, 4, NULL, 'add', 'Admin', 'samuel added a new client \'janpaul3\', pet \'tigon\', and medical record', '2025-10-03 14:13:56'),
(397, 4, NULL, 'delete', 'Admin', 'samuel archived client \'janpaul3\'', '2025-10-03 14:14:21'),
(398, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Maria Lopez\'', '2025-10-03 14:14:25'),
(399, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Boknoy Esmales\'', '2025-10-03 14:14:29'),
(400, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Boknoy Esmales\' and associated pets and medical records', '2025-10-03 14:15:41'),
(401, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Maria Lopez\' and associated pets and medical records', '2025-10-03 14:15:42'),
(402, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'janpaul3\' and associated pets and medical records', '2025-10-03 14:15:44'),
(403, 4, NULL, 'add', 'Admin', 'samuel added a new client \'janpaul2\', pet \'tigok\', and medical record', '2025-10-03 14:16:23'),
(404, 4, NULL, 'delete', 'Admin', 'samuel archived client \'janpaul2\'', '2025-10-03 14:40:00'),
(405, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Odemil Uyan\'', '2025-10-03 14:40:22'),
(406, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'janpaul2\' and associated pets and medical records', '2025-10-03 14:40:28'),
(407, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Odemil Uyan\' and associated pets and medical records', '2025-10-03 14:40:29'),
(408, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Boknoy Esmael\', pet \'tigok\', and medical record', '2025-10-03 14:41:23'),
(409, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-03 21:20:46'),
(410, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-03 23:53:41'),
(411, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-04 20:43:27'),
(412, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-04 20:46:12'),
(413, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-04 22:04:37'),
(414, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmaels\' and pet \'tigok\' and updated/added a medical record', '2025-10-04 22:04:55'),
(415, 4, NULL, 'update', 'Admin', 'samuel updated client \'Boknoy Esmaels\' and pet \'tigok\' and updated/added a medical record', '2025-10-04 22:05:01'),
(416, 0, NULL, 'Appointment', 'Guest', 'Guest Maricar T. Bahala booked an appointment on October 10, 2025 at 2:30 PM', '2025-10-04 22:07:00'),
(417, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-04 22:24:55'),
(418, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-04 22:44:00'),
(419, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-04 23:09:55'),
(420, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-04 23:10:20'),
(421, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-05 00:02:27'),
(422, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:03:32'),
(423, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:04:04'),
(424, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:04:15'),
(425, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:05:18'),
(426, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:05:55'),
(427, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:06:08'),
(428, 10, NULL, 'Login', 'Veterinarian', 'Maricar T. Bahala logged in', '2025-10-05 00:06:47'),
(429, 0, NULL, 'Appointment', 'Guest', 'Guest Bulilit Dela Cera booked an appointment on October 10, 2025 at 9:30 AM', '2025-10-06 21:35:57'),
(430, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-06 21:36:02'),
(431, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-06 22:20:19'),
(432, 0, NULL, 'Appointment', 'Guest', 'Guest Maris Awitin booked an appointment on October 11, 2025 at 2:30 PM', '2025-10-06 22:28:35'),
(433, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-07 17:41:39'),
(434, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Maria Teresa Dela Cruz\', pet \'Budoy\', and medical record', '2025-10-07 17:49:44'),
(435, 4, NULL, 'update', 'Admin', 'samuel updated client \'Maria Teresa Dela Cruz\' and pet \'Budoy\' and updated/added a medical record', '2025-10-07 17:49:57'),
(436, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-07 17:53:54'),
(437, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-07 18:00:44'),
(438, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-07 18:02:42'),
(439, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-07 18:11:05');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `medical_condition` text DEFAULT NULL,
  `medical_diagnosis` text DEFAULT NULL,
  `medical_symptoms` text DEFAULT NULL,
  `medical_treatment` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `record_date` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`record_id`, `pet_id`, `date`, `medical_condition`, `medical_diagnosis`, `medical_symptoms`, `medical_treatment`, `updated_at`, `status`, `record_date`) VALUES
(29, 41, '2025-09-11', 'Eye discharge and sneezing', 'Upper respiratory infection (common in kittens)', 'Watery eyes, frequent sneezing, mild nasal discharge', 'Prescribed antibiotics, vitamin supplements, and advised warm, clean shelter', '2025-09-11 04:03:02', 1, '2025-09-11'),
(31, 43, '2025-10-02', 'Runny noses', 'Problem', 'Vomiting, diarrhea, lethargy', 'IV fluids, antivirals, antibiotics', '2025-10-02 03:18:00', 1, '2025-10-02'),
(34, 46, '2025-10-04', 'Runny Noses', '123123', '123123', '123123', '2025-10-04 14:05:01', 1, '2025-10-03'),
(35, 47, '2025-10-07', 'Flea Allergy Dermatitis, Runny Noses', 'Skin irritation caused by flea infestation', 'Constant scratching, red patches on skin, mild hair loss', 'Flea shampoo, topical anti-itch cream, and monthly flea preventive drops', '2025-10-07 09:49:57', 1, '2025-10-07');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `method_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `client_name`, `method_id`, `amount`, `description`, `date`) VALUES
(4, 'Odemil Uyan', 1, 1300.00, 'For check ups', '2025-09-10 21:57:53'),
(5, 'Odemil Uyan', 1, 1300.00, 'For check ups', '2025-09-10 21:58:33'),
(6, 'Boknoy Esmale', 2, 1500.00, 'Payment for grooming', '2025-09-10 22:27:03'),
(7, 'Roselyn Villanueva', 1, 1500.00, 'Payments for grooming.', '2025-09-11 11:55:38'),
(8, 'janpaul4', 1, 500.00, 'payments for checkups.', '2025-09-14 21:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `method_id` int(11) NOT NULL,
  `method_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`method_id`, `method_name`) VALUES
(1, 'Cash'),
(2, 'GCash'),
(3, 'Credit Card');

-- --------------------------------------------------------

--
-- Table structure for table `pet`
--

CREATE TABLE `pet` (
  `pet_id` int(11) NOT NULL,
  `pet_name` varchar(100) DEFAULT NULL,
  `pet_sex` varchar(10) DEFAULT NULL,
  `pet_weight` decimal(5,2) DEFAULT NULL,
  `pet_breed` varchar(50) DEFAULT NULL,
  `pet_birth_date` date DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `pet_species` enum('Dog','Cat') NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pet`
--

INSERT INTO `pet` (`pet_id`, `pet_name`, `pet_sex`, `pet_weight`, `pet_breed`, `pet_birth_date`, `client_id`, `status`, `pet_species`, `updated_at`) VALUES
(41, 'Mingming', 'Female', 10.00, 'Puspin', '2023-11-12', 42, 1, 'Cat', '2025-09-11 04:03:02'),
(43, 'Bruno', 'Male', 20.00, 'Labrador', '2024-12-11', 44, 1, 'Dog', NULL),
(46, 'tigok', 'Male', 12.00, 'Labrador', '2222-02-22', 47, 1, 'Dog', NULL),
(47, 'Budoy', 'Male', 12.00, 'Aspin', '2023-03-20', 48, 1, 'Dog', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `record_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `vet_id` int(11) DEFAULT NULL,
  `time_and_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `veterinarian`
--

CREATE TABLE `veterinarian` (
  `vet_id` int(11) NOT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `vet_contact_number` varchar(15) DEFAULT NULL,
  `vet_username` varchar(50) DEFAULT NULL,
  `vet_password` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `veterinarian`
--

INSERT INTO `veterinarian` (`vet_id`, `vet_name`, `vet_contact_number`, `vet_username`, `vet_password`) VALUES
(1, 'Jan Paul Michael M. Dela Cera', '01234567890', 'janpaul', '$2y$10$8bwkTCKQjkTGJEdKfc55F.aj7KSSPseB0Bl1QGdXP07ZYRcub4xiS'),
(3, 'Mr. Osas', '09759420944', 'osas', '$2y$10$XzOK9PildBlvttODtOoY4ecxWYCnVbU3ldpwGrExT59KccUuLHWYK'),
(4, 'Samuel Seo', '012345678901', 'samuel', '$2y$10$qfUYyNHBEorgQ4zkdu7lpeE4FOq4iS/.LUqfKYMAr18JF2sA.X9Z.'),
(5, 'Dr. Odemil Uyan', '09182345678', 'uyan_vet', '$2y$10$M9On./FghnT9cmlby7dF7eQtZsKy4j3UVZzSpQp6027hyqSdF9tGu'),
(6, 'Liezel Rodrigo', '09182345678', 'liezel_vet', '$2y$10$Q/Z.YkzYXQzPxycs5buSneEPa3nRahMcTjrXfTL7XIV8Mq/I9ofXW'),
(8, 'Default Veterinarian', '09123456789', 'default_vet', '$2y$10$Q/WHc6yEyuWFLIDGAEjKWO.CGVPKovo2s2l0l/.awIBOskRSbppYO'),
(9, 'Dr. Test Vet', NULL, 'vet1', '$2y$10$7PuUiTzHvmUHG8gaR6QjY.fgi7H5.A09qiKP4hHqfzE2gUwKF9DCe'),
(10, 'Maricar T. Bahala', '12345678890', 'Maricar', '$2y$10$81l0/56QI7vPfokuui0gH.dXPTGfUDzFD100YmvYD54Hc4Yk9vYq6');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_username` (`admin_username`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `archive`
--
ALTER TABLE `archive`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`Log_ID`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `pet_id` (`pet_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_method_id` (`method_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`method_id`);

--
-- Indexes for table `pet`
--
ALTER TABLE `pet`
  ADD PRIMARY KEY (`pet_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `vet_id` (`vet_id`);

--
-- Indexes for table `veterinarian`
--
ALTER TABLE `veterinarian`
  ADD PRIMARY KEY (`vet_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `archive`
--
ALTER TABLE `archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=440;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pet`
--
ALTER TABLE `pet`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `veterinarian`
--
ALTER TABLE `veterinarian`
  MODIFY `vet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pet` (`pet_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_method_id` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`method_id`);

--
-- Constraints for table `pet`
--
ALTER TABLE `pet`
  ADD CONSTRAINT `pet_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`record_id`) REFERENCES `medical_records` (`record_id`),
  ADD CONSTRAINT `reports_ibfk_2` FOREIGN KEY (`pet_id`) REFERENCES `pet` (`pet_id`),
  ADD CONSTRAINT `reports_ibfk_3` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`),
  ADD CONSTRAINT `reports_ibfk_4` FOREIGN KEY (`vet_id`) REFERENCES `veterinarian` (`vet_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
