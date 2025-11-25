-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 24, 2025 at 02:23 PM
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
-- Database: `pettrackdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_username` varchar(50) NOT NULL,
  `admin_name` varchar(100) DEFAULT NULL,
  `admin_password` varchar(100) DEFAULT NULL
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
  `client_id` int(11) DEFAULT NULL,
  `pet_id` int(11) DEFAULT NULL,
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

INSERT INTO `appointments` (`id`, `client_id`, `pet_id`, `owner_name`, `contact_number`, `appointment_date`, `appointment_time`, `reason`, `status`, `created_at`, `updated_at`, `duration`) VALUES
(98, 77, 89, 'Jan Paul Michael M. Dela Cera', '639392516664', '2025-11-24', '08:00:00', 'Vaccination', 'Scheduled', '2025-11-24 06:00:11', NULL, 90);

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
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`client_id`, `client_name`, `client_address`, `client_contact_number`, `status`, `updated_at`, `created_at`) VALUES
(75, 'Eadrian Basadre', 'barangay 2, Balingasag, Misamis Oriental', '639392516664', 1, NULL, '2025-11-22 23:40:03'),
(76, 'Maria Santos', 'Blk 12 Lot 7, Riverside Subdivision, Gusa, Cagayan de Oro City', '639982345671', 1, NULL, '2025-11-23 21:52:15'),
(77, 'Jan Paul Michael M. Dela Cera', 'barangay 6, Balingasag, Misamis Oriental', '639392516664', 1, NULL, '2025-11-23 23:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `client_accounts`
--

CREATE TABLE `client_accounts` (
  `account_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client_accounts`
--

INSERT INTO `client_accounts` (`account_id`, `client_id`, `username`, `email`, `password`, `created_at`, `updated_at`) VALUES
(7, 75, 'eadrian', 'eadrian_basadre@gmail.com', '$2y$10$Mxn/QjPQ7hc3/z0Rtp48X.LjvD.JFrbmk5dLtPZNIWYdoP0i5wOxC', '2025-11-22 15:40:03', '2025-11-22 15:40:03'),
(8, 77, 'Jan Paul', 'delacerajanpaul22@gmail.com', '$2y$10$nh2qNegLcMpibqX9k0o3YORrwnaTg/muArJV4aLxRxqs9sazGdyLK', '2025-11-23 15:05:54', '2025-11-23 15:05:54');

-- --------------------------------------------------------

--
-- Table structure for table `consultations`
--

CREATE TABLE `consultations` (
  `consultation_id` int(11) NOT NULL,
  `pet_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `vet_name` varchar(100) DEFAULT NULL,
  `consultation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(455, 0, NULL, 'Appointment', 'Guest', 'Guest Jikoy Galindo booked an appointment on October 24, 2025 at 10:30 AM', '2025-10-23 00:17:56'),
(456, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-23 00:18:02'),
(457, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-23 00:29:27'),
(458, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-23 00:31:14'),
(459, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-23 21:24:15'),
(460, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-23 21:38:04'),
(461, 0, NULL, 'Appointment', 'Guest', 'Guest Orlando B. Dela Cera booked an appointment on October 30, 2025 at 2:30 PM', '2025-10-23 21:46:30'),
(462, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-23 21:46:52'),
(463, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-23 21:49:10'),
(464, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-25 22:23:40'),
(465, 4, NULL, 'add', 'Admin', 'samuel added a new client \'shieik\', pet \'12312312\', and medical record', '2025-10-25 23:41:38'),
(466, 4, NULL, 'update', 'Admin', 'samuel updated client \'shieik23\' and pet \'12312312\' and updated/added a medical record', '2025-10-25 23:41:58'),
(467, 4, NULL, 'delete', 'Admin', 'samuel archived client \'shieik23\'', '2025-10-25 23:42:03'),
(468, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'shieik23\' and associated pets and medical records', '2025-10-25 23:42:13'),
(469, 4, NULL, 'delete', 'Admin', 'samuel archived client \'shieik23\'', '2025-10-25 23:42:19'),
(470, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'shieik23\' and associated pets and medical records', '2025-10-25 23:42:24'),
(471, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-26 14:50:51'),
(472, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Jerome A. Ladera\', pet \'Whiskers\', and medical record', '2025-10-26 15:52:46'),
(473, 4, NULL, 'update', 'Admin', 'samuel updated client \'Jerome A. Laderas\' and pet \'Whiskers\' and updated/added a medical record', '2025-10-26 15:53:35'),
(474, 4, NULL, 'update', 'Admin', 'samuel updated client \'Jerome A. Ladera\' and pet \'Whiskers\' and updated/added a medical record', '2025-10-26 15:53:49'),
(475, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Jerome A. Ladera\'', '2025-10-26 15:54:08'),
(476, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Jerome A. Ladera\' and associated pets and medical records', '2025-10-26 15:54:12'),
(477, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Jerome A. Ladera\'', '2025-10-26 15:54:16'),
(478, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Jerome A. Ladera\' and associated pets and medical records', '2025-10-26 15:54:20'),
(479, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Jerome A. Ladera\', pet \'Whiskers\', and medical record', '2025-10-26 16:49:31'),
(480, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-26 16:52:03'),
(481, 0, NULL, 'Appointment', 'Guest', 'Guest Maricar T. Bahala booked an appointment on October 30, 2025 at 9:30 AM', '2025-10-26 16:53:30'),
(482, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-26 17:32:46'),
(483, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Jerome A. Ladera\'', '2025-10-26 17:41:39'),
(484, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Jerome A. Ladera\' and associated pets and medical records', '2025-10-26 17:41:46'),
(485, 4, NULL, 'add', 'Admin', 'samuel added a new client \'Jerome A. Ladera\', pet \'Whiskers\', and medical record', '2025-10-26 17:45:02'),
(486, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-26 17:49:44'),
(487, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-26 17:51:48'),
(488, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-26 17:56:18'),
(489, 11, NULL, 'Login', 'Veterinarian', 'Jake Kim logged in', '2025-10-26 17:57:04'),
(490, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-26 21:09:34'),
(491, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Jerome A. Ladera\'', '2025-10-26 21:10:05'),
(492, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Jerome A. Ladera\' and associated pets and medical records', '2025-10-26 21:10:11'),
(493, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-26 21:20:47'),
(494, 12, NULL, 'Login', 'Veterinarian', 'Manuel Oclarit logged in', '2025-10-26 21:29:03'),
(495, 12, NULL, 'add', 'Admin', 'manuel added a new client \'Jerome A. Ladera\', pet \'Whiskers\', and medical record', '2025-10-26 21:34:47'),
(496, 12, NULL, 'update', 'Admin', 'manuel updated client \'Jerome A. Ladera\' and pet \'Whiskers\' and updated/added a medical record', '2025-10-26 21:36:32'),
(497, 12, NULL, 'delete', 'Admin', 'manuel archived client \'Jerome A. Ladera\'', '2025-10-26 21:43:47'),
(498, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Jerome A. Ladera\' and associated pets and medical records', '2025-10-26 21:44:04'),
(499, 12, NULL, 'delete', 'Admin', 'manuel archived client \'Jerome A. Ladera\'', '2025-10-26 21:44:11'),
(500, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Jerome A. Ladera\' and associated pets and medical records', '2025-10-26 21:44:16'),
(501, 0, NULL, 'Appointment', 'Guest', 'Guest Sheryl Mae Clo booked an appointment on October 31, 2025 at 2:30 PM', '2025-10-26 21:46:54'),
(502, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-26 22:07:37'),
(503, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-26 23:03:35'),
(504, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-27 11:25:30'),
(505, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-27 12:04:04'),
(506, 0, NULL, 'Appointment', 'Guest', 'Guest Kent Ralf Baccaro booked an appointment on November 2, 2025 at 3:30 PM', '2025-10-27 12:05:02'),
(507, 12, NULL, 'Login', 'Veterinarian', 'Manuel Oclarit logged in', '2025-10-27 12:11:52'),
(508, 12, NULL, 'delete', 'Admin', 'manuel archived client \'Boknoy Esmaels\'', '2025-10-27 12:12:04'),
(509, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Boknoy Esmaels\' and associated pets and medical records', '2025-10-27 12:12:09'),
(510, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-27 13:17:20'),
(511, 13, NULL, 'Login', 'Veterinarian', 'Odemil A. Uyan logged in', '2025-10-27 13:20:01'),
(512, 13, NULL, 'add', 'Admin', 'odemil added a new client \'John Michael Acut\', pet \'Max\', and medical record', '2025-10-27 13:22:20'),
(513, 0, NULL, 'Appointment', 'Guest', 'Guest Manuel Oclarit booked an appointment on January 22, 2026 at 10:30 AM', '2025-10-27 13:26:38'),
(514, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-10-27 13:42:46'),
(515, 13, NULL, 'Login', 'Veterinarian', 'Odemil A. Uyan logged in', '2025-10-27 13:57:27'),
(516, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-27 20:52:01'),
(517, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-29 21:26:59'),
(518, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:32:10'),
(519, 55, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-10-29 22:32:10'),
(520, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:32:51'),
(521, 55, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-10-29 22:32:51'),
(522, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:33:16'),
(523, 55, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-10-29 22:33:16'),
(524, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:33:46'),
(525, 55, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-10-29 22:33:46'),
(526, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:33:56'),
(527, 55, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-10-29 22:33:56'),
(528, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:35:02'),
(529, 55, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-10-29 22:35:02'),
(530, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:46:48'),
(531, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 22:47:34'),
(532, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 23:14:10'),
(533, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-29 23:14:26'),
(534, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-29 23:15:17'),
(535, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-29 23:26:26'),
(536, 55, NULL, 'Login', 'Client', ' logged in', '2025-10-30 21:11:46'),
(537, 56, NULL, 'Login', 'Client', 'eadrian_basadre@gmail.com logged in', '2025-10-30 21:16:58'),
(538, 58, NULL, 'Login', 'Client', 'eadrian_basadre@gmail.com logged in', '2025-10-30 21:38:20'),
(539, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on October 31, 2025 at 8:00 AM', '2025-10-30 23:48:15'),
(540, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-10-30 23:48:35'),
(541, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-01 23:09:09'),
(542, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-02 00:22:23'),
(543, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-02 17:57:50'),
(544, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-02 19:20:53'),
(545, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-11-02 19:21:10'),
(546, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-02 21:27:05'),
(547, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-02 21:28:46'),
(548, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-02 21:32:07'),
(549, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-02 21:34:04'),
(550, 59, NULL, 'Login', 'Client', 'JamesKhyl M. Dela Cera logged in', '2025-11-02 22:01:07'),
(551, 60, NULL, 'Registration', 'Client', 'JamesKhyl M. Dela Cera registered and logged in', '2025-11-02 22:10:12'),
(552, 60, NULL, 'Login', 'Client', 'JamesKhyl M. Dela Cera logged in', '2025-11-02 22:13:17'),
(553, 60, NULL, 'Login', 'Client', 'JamesKhyl M. Dela Cera logged in', '2025-11-02 22:40:48'),
(554, 0, NULL, 'Appointment', 'Guest', 'Guest kaykay booked an appointment on November 2, 2025 at 8:00 AM', '2025-11-02 22:57:09'),
(555, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-03 08:46:52'),
(556, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-03 08:47:26'),
(557, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-03 11:41:51'),
(558, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-03 15:32:49'),
(559, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-03 18:50:50'),
(560, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-03 18:51:37'),
(561, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on November 6, 2025 at 9:30 AM', '2025-11-03 18:52:55'),
(562, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-03 18:54:02'),
(563, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-03 18:54:18'),
(564, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-05 22:03:44'),
(565, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 17:34:09'),
(566, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 17:53:28'),
(567, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 18:03:47'),
(568, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on November 6, 2025 at 11:00 AM', '2025-11-06 18:05:12'),
(569, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on November 6, 2025 at 8:00 AM', '2025-11-06 18:09:07'),
(570, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 18:09:21'),
(571, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 18:24:12'),
(572, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on November 6, 2025 at 8:00 AM', '2025-11-06 18:25:57'),
(573, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 18:26:10'),
(574, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 18:35:50'),
(575, 58, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 18:43:02'),
(576, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on November 10, 2025 at 8:00 AM', '2025-11-06 18:48:43'),
(577, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 18:48:57'),
(578, 4, NULL, 'delete', 'Admin', 'samuel archived client \'JamesKhyl M. Dela Cera\'', '2025-11-06 18:49:14'),
(579, 4, NULL, 'update', 'Admin', 'samuel updated client \'eadrian\' (ID: 61) and pet \'dingdong\' and updated/added a medical record', '2025-11-06 18:49:40'),
(580, 4, NULL, 'delete', 'Admin', 'samuel archived client \'eadrian\'', '2025-11-06 18:49:59'),
(581, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Eadrian Basadre\' and associated pets and medical records', '2025-11-06 18:50:24'),
(582, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'JamesKhyl M. Dela Cera\' and associated pets and medical records', '2025-11-06 18:50:27'),
(583, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'eadrian\' and associated pets and medical records', '2025-11-06 18:50:29'),
(584, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-11-06 18:52:28'),
(585, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:19:34'),
(586, 62, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-06 19:20:12'),
(587, 0, NULL, 'Appointment', 'Guest', 'Guest eadrian booked an appointment on November 10, 2025 at 9:30 AM', '2025-11-06 19:21:06'),
(588, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:21:32'),
(589, 64, NULL, 'Registration', 'Client', 'JamesKhyl M. Dela Cera registered and logged in', '2025-11-06 19:22:47'),
(590, 0, NULL, 'Appointment', 'Guest', 'Guest Kaykay booked an appointment on November 10, 2025 at 3:30 PM', '2025-11-06 19:23:19'),
(591, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:23:29'),
(592, 4, NULL, 'delete', 'Admin', 'samuel archived client \'eadrian\'', '2025-11-06 19:23:41'),
(593, 4, NULL, 'delete', 'Admin', 'samuel archived client \'eadrian\'', '2025-11-06 19:23:44'),
(594, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'eadrian\' and associated pets and medical records', '2025-11-06 19:23:48'),
(595, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'eadrian\' and associated pets and medical records', '2025-11-06 19:23:50'),
(596, 64, NULL, 'Login', 'Client', 'JamesKhyl M. Dela Cera logged in', '2025-11-06 19:28:36'),
(597, 0, NULL, 'Appointment', 'Guest', 'Guest Kaykay booked an appointment on November 10, 2025 at 11:00 AM', '2025-11-06 19:29:16'),
(598, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:30:01'),
(599, 4, NULL, 'delete', 'Admin', 'samuel archived client \'JamesKhyl M. Dela Cera\'', '2025-11-06 19:32:44'),
(600, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'JamesKhyl M. Dela Cera\' and associated pets and medical records', '2025-11-06 19:32:49'),
(601, 62, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 19:33:46'),
(602, 62, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 19:34:18'),
(603, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:34:38'),
(604, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-06 19:34:43'),
(605, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted client \'Eadrian Basadre\' and associated pets and medical records', '2025-11-06 19:34:46'),
(606, 66, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-06 19:42:47'),
(607, 0, NULL, 'Appointment', 'Guest', 'Guest Eadrian booked an appointment on November 10, 2025 at 2:00 PM', '2025-11-06 19:43:30'),
(608, 66, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-06 19:43:41'),
(609, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:43:51'),
(610, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-06 19:44:15'),
(611, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian Basadre\' (ID: 66) and associated pets/records. Account preserved.', '2025-11-06 19:44:43'),
(612, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian\'', '2025-11-06 19:44:46'),
(613, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Eadrian\' and associated pets and medical records', '2025-11-06 19:44:56'),
(614, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Deleted Client\' (ID: 66) and associated pets/records. Account preserved.', '2025-11-06 19:44:58'),
(615, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Deleted Client\' (ID: 66) and associated pets/records. Account preserved.', '2025-11-06 19:45:01'),
(616, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian\'', '2025-11-06 19:46:10'),
(617, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Deleted Client\' and associated pets and medical records', '2025-11-06 19:46:16'),
(618, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian\' (ID: 67) and associated pets/records. Account preserved.', '2025-11-06 19:46:20'),
(619, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Deleted Client\' and associated pets and medical records', '2025-11-06 19:46:24'),
(620, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Deleted Client\'', '2025-11-06 19:46:27'),
(621, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Deleted Client\'', '2025-11-06 19:46:30'),
(622, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Deleted Client\' (ID: 66) and associated pets/records. Account preserved.', '2025-11-06 19:46:37'),
(623, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Deleted Client\' (ID: 67) and associated pets/records. Account preserved.', '2025-11-06 19:46:39'),
(624, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Deleted Client\' (ID: 66) and associated pets/records. Account preserved.', '2025-11-06 19:49:48'),
(625, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Deleted Client\' (ID: 67) and associated pets/records. Account preserved.', '2025-11-06 19:49:50'),
(626, 66, NULL, 'Login', 'Client', 'Deleted Client logged in', '2025-11-06 19:50:04'),
(627, 0, NULL, 'Appointment', 'Guest', 'Guest Eadrian booked an appointment on November 10, 2025 at 12:30 PM', '2025-11-06 19:50:47'),
(628, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 19:51:13'),
(629, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian\'', '2025-11-06 19:51:27'),
(630, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Eadrian\' and associated pets and medical records', '2025-11-06 19:51:38'),
(631, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-06 22:46:19'),
(632, 66, NULL, 'Login', 'Client', 'Deleted Client logged in', '2025-11-08 12:26:51'),
(633, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-08 12:27:02'),
(634, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-11-08 12:30:15'),
(635, 14, NULL, 'Login', 'Veterinarian', 'Febien M. Dela Cera logged in', '2025-11-08 12:44:03'),
(636, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-08 15:36:31'),
(637, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-08 15:36:47'),
(638, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-11-08 15:42:25'),
(639, 14, NULL, 'Login', 'Veterinarian', 'Febien M. Dela Cera logged in', '2025-11-08 15:54:33'),
(640, 14, NULL, 'delete', 'Admin', 'biano archived client \'Eadrian\'', '2025-11-08 15:56:19'),
(641, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian\' (ID: 68) and associated pets/records. Account preserved.', '2025-11-08 15:56:28'),
(642, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-08 15:56:47'),
(643, 0, NULL, 'Appointment', 'Guest', 'Guest Eadrian booked an appointment on November 12, 2025 at 8:00 AM', '2025-11-08 15:57:25'),
(644, 14, NULL, 'Login', 'Veterinarian', 'Febien M. Dela Cera logged in', '2025-11-08 15:57:36'),
(645, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-08 16:05:49'),
(646, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-08 16:11:47'),
(647, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-09 21:52:43'),
(648, 14, NULL, 'Login', 'Veterinarian', 'Febien M. Dela Cera logged in', '2025-11-09 21:58:00'),
(649, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-10 20:57:31'),
(650, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-13 00:13:38'),
(651, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-13 21:15:02'),
(652, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-11-13 23:04:47'),
(653, 9, NULL, 'Login', 'Admin', 'jonggun logged in', '2025-11-14 14:24:00'),
(654, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-14 18:33:44'),
(655, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-14 18:37:26'),
(656, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-14 18:45:14'),
(657, 66, NULL, 'Login', 'Client', ' logged in', '2025-11-14 18:45:40'),
(658, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 20:33:38'),
(659, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian\'', '2025-11-15 20:56:42'),
(660, 4, NULL, 'delete', 'Admin', 'samuel archived client \'John Michael Acut\'', '2025-11-15 20:56:46'),
(661, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Kaykay\'', '2025-11-15 20:56:49'),
(662, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Maria Teresa Dela Cruz\'', '2025-11-15 20:56:52'),
(663, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Roberto Lagbas\'', '2025-11-15 20:56:55'),
(664, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Roselyn Villanueva\'', '2025-11-15 20:56:58'),
(665, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Roselyn Villanueva\' (ID: 42) and associated pets/records. Account preserved.', '2025-11-15 20:57:02'),
(666, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Roberto Lagbas\' (ID: 44) and associated pets/records. Account preserved.', '2025-11-15 20:57:04'),
(667, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Maria Teresa Dela Cruz\' (ID: 48) and associated pets/records. Account preserved.', '2025-11-15 20:57:05'),
(668, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'John Michael Acut\' (ID: 54) and associated pets/records. Account preserved.', '2025-11-15 20:57:06'),
(669, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Kaykay\' (ID: 65) and associated pets/records. Account preserved.', '2025-11-15 20:57:07'),
(670, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian\' (ID: 69) and associated pets/records. Account preserved.', '2025-11-15 20:57:09'),
(671, 70, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-15 20:58:57'),
(672, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 21:00:26'),
(673, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-15 21:15:33'),
(674, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian Basadre\' (ID: 70) and associated pets/records. Account preserved.', '2025-11-15 21:15:39'),
(675, 71, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-15 21:25:12'),
(676, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 21:27:30'),
(677, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 21:33:46'),
(678, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 21:35:04'),
(679, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 21:48:45'),
(680, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 21:49:26'),
(681, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 22:17:45'),
(682, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 22:18:12'),
(683, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 22:29:23'),
(684, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-15 22:29:54'),
(685, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 23:58:17'),
(686, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 23:58:46'),
(687, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 23:58:54'),
(688, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-15 23:58:56'),
(689, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 00:08:53'),
(690, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-16 00:09:56'),
(691, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 00:10:42'),
(692, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 12:21:02'),
(693, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 12:21:41'),
(694, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 13:23:42'),
(695, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 14:43:25'),
(696, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-16 14:57:16'),
(697, 71, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-16 20:36:28'),
(698, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-20 at 11:00 AM', '2025-11-16 21:03:55'),
(699, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-20 at 8:00 AM', '2025-11-16 21:04:31'),
(700, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-16 21:04:48'),
(701, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-16 21:10:08'),
(702, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian Basadre\' (ID: 71) and associated pets/records. Account preserved.', '2025-11-16 21:10:12'),
(703, 71, NULL, 'Login', 'Client', ' logged in', '2025-11-16 21:10:34'),
(704, 71, NULL, 'Login', 'Client', ' logged in', '2025-11-16 21:11:58'),
(705, 71, NULL, 'Login', 'Client', ' logged in', '2025-11-16 21:12:08'),
(706, 71, NULL, 'Login', 'Client', ' logged in', '2025-11-16 21:14:24'),
(707, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-17 23:02:30'),
(708, 71, NULL, 'Login', 'Client', ' logged in', '2025-11-17 23:40:39'),
(709, 72, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-17 23:57:56'),
(710, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-20 at 9:30 AM', '2025-11-18 00:01:57'),
(711, 14, NULL, 'Login', 'Veterinarian', 'Febien M. Dela Cera logged in', '2025-11-18 00:02:21'),
(712, 72, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-18 00:14:07'),
(713, 72, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-18 00:15:00'),
(714, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-20 at 2:00 PM', '2025-11-18 00:15:22'),
(715, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-18 00:15:32'),
(716, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-20 23:02:14'),
(717, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-20 23:02:24'),
(718, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian Basadre\' (ID: 72) and associated pets/records. Account preserved.', '2025-11-20 23:02:32'),
(719, 73, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-20 23:41:33'),
(720, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-20 23:42:01'),
(721, 73, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-20 23:47:35'),
(722, 73, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-21 00:04:03'),
(723, 73, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-21 00:32:29'),
(724, 74, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-21 00:33:10'),
(725, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-21 at 8:00 AM', '2025-11-21 00:40:36'),
(726, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-21 00:40:52'),
(727, 4, NULL, 'update', 'Admin', 'updated client \'Eadrian Basadre\' (ID: 74), pet \'chokoy\' and medical record', '2025-11-21 01:06:29'),
(728, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-21 21:26:50'),
(729, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-21 22:57:21'),
(730, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-21 22:57:27'),
(731, NULL, NULL, 'restore', 'Admin', 'Unknown restored client \'Eadrian Basadre\' and associated pets and medical records', '2025-11-21 22:57:36'),
(732, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:21:00'),
(733, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:30:32'),
(734, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:30:36'),
(735, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:34:04'),
(736, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:38:35'),
(737, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:42:30'),
(738, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-30 at 8:00 AM', '2025-11-22 22:47:46'),
(739, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-22 22:48:06'),
(740, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:48:32'),
(741, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-30 at 11:00 AM', '2025-11-22 22:49:35'),
(742, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-22 22:49:45'),
(743, 74, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-22 22:57:40'),
(744, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-30 at 9:30 AM', '2025-11-22 22:58:40'),
(745, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-22 22:58:49'),
(746, 4, NULL, 'delete', 'Admin', 'samuel archived client \'Eadrian Basadre\'', '2025-11-22 23:38:28'),
(747, NULL, NULL, 'delete', 'Admin', 'Unknown permanently deleted data for client \'Eadrian Basadre\' (ID: 74) and associated pets/records. Account preserved.', '2025-11-22 23:38:32'),
(748, 74, NULL, 'Login', 'Client', ' logged in', '2025-11-22 23:38:45'),
(749, 75, NULL, 'Registration', 'Client', 'Eadrian Basadre registered and logged in', '2025-11-22 23:40:03'),
(750, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-11-30 at 12:30 PM', '2025-11-22 23:40:27'),
(751, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-22 23:40:38'),
(752, 75, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-23 20:12:22'),
(753, 75, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-23 20:22:00'),
(754, 75, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-23 20:22:07'),
(755, 75, NULL, 'Login', 'Client', 'Eadrian Basadre logged in', '2025-11-23 20:24:52'),
(756, 0, NULL, 'Booking', 'Guest', 'Eadrian Basadre booked an appointment on 2025-12-01 at 8:00 AM', '2025-11-23 20:28:10'),
(757, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-23 20:28:30'),
(758, 4, NULL, 'update', 'Admin', 'updated client \'Eadrian Basadre\' (ID: 75), pet \'chokoy\' and medical record', '2025-11-23 21:49:44'),
(759, 4, NULL, 'add', 'Admin', 'added new client \'Maria Santos\', pet \'Whiskers\' and medical record', '2025-11-23 21:52:15'),
(760, 77, NULL, 'Registration', 'Client', 'Jan Paul Michael M. Dela Cera registered and logged in', '2025-11-23 23:05:54'),
(761, 0, NULL, 'sms_failed', 'System', 'SMS failed to 09392516664 (Appt #96)', '2025-11-23 23:06:44'),
(762, 0, NULL, 'Booking', 'Guest', 'Jan Paul Michael M. Dela Cera booked an appointment on 2025-12-01 at 11:00 AM', '2025-11-23 23:06:44'),
(763, 0, NULL, 'sms_failed', 'System', 'SMS failed to 09392516664 (Appt #97)', '2025-11-23 23:21:38'),
(764, 0, NULL, 'Booking', 'Guest', 'Jan Paul Michael M. Dela Cera booked an appointment on 2025-12-02 at 8:00 AM', '2025-11-23 23:21:38'),
(765, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-24 13:20:58'),
(766, 77, NULL, 'Login', 'Client', 'Jan Paul Michael M. Dela Cera logged in', '2025-11-24 13:59:38'),
(767, 0, NULL, 'Booking', 'Guest', 'Jan Paul Michael M. Dela Cera booked an appointment on 2025-11-24 at 8:00 AM', '2025-11-24 14:00:11'),
(768, 4, NULL, 'Login', 'Veterinarian', 'Samuel Seo logged in', '2025-11-24 14:00:34');

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `record_id` int(11) NOT NULL,
  `pet_id` int(11) DEFAULT NULL,
  `consultation_id` int(11) DEFAULT NULL,
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

INSERT INTO `medical_records` (`record_id`, `pet_id`, `consultation_id`, `date`, `medical_condition`, `medical_diagnosis`, `medical_symptoms`, `medical_treatment`, `updated_at`, `status`, `record_date`) VALUES
(44, 85, NULL, '2025-11-23', 'Skin Allergy, Ear Infection', 'Dog exhibits dermatitis symptoms likely caused by food allergens. Redness and mild swelling in the ear canal detected.', 'Intense scratching, flaky skin patches, foul-smelling ear discharge, occasional head shaking.', 'Administer antihistamines daily for 7 days. Clean ears with prescribed otic solution every 12 hours. Switch to hypoallergenic diet.', NULL, 1, '2025-11-23'),
(45, 86, NULL, '2025-11-23', 'Fleas, Eye Irritation', 'Presence of mild flea infestation and conjunctivitis. Cat displays watery left eye and occasional sneezing.', 'Frequent scratching, visible fleas, watery eye, slight redness around the eyelid.', 'Administer flea treatment once every 30 days. Apply antibiotic eye drops twice daily for 5 days. Maintain clean bedding.', NULL, 1, '2025-11-23');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `client_name` varchar(100) NOT NULL,
  `method_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `client_id`, `client_name`, `method_id`, `amount`, `description`, `date`) VALUES
(4, NULL, 'Odemil Uyan', 1, 1300.00, 'For check ups', '2025-09-10 21:57:53'),
(5, NULL, 'Odemil Uyan', 1, 1300.00, 'For check ups', '2025-09-10 21:58:33'),
(6, NULL, 'Boknoy Esmale', 2, 1500.00, 'Payment for grooming', '2025-09-10 22:27:03'),
(7, NULL, 'Roselyn Villanueva', 1, 1500.00, 'Payments for grooming.', '2025-09-11 11:55:38'),
(8, NULL, 'janpaul4', 1, 500.00, 'payments for checkups.', '2025-09-14 21:50:55'),
(11, NULL, 'John Michael Acut', 1, 2500.00, 'Payments for checkups', '2025-10-27 13:24:15'),
(13, NULL, 'john michael acut', 1, 150.00, 'Consultation fee', '2023-10-01 00:00:00'),
(14, NULL, 'John Michael Acut', 1, 3000.00, 'payments for vaccination', '2025-10-27 22:12:42');

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
(2, 'GCash');

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
(85, 'chokoy', 'Male', 20.25, 'bulldog', '2023-02-01', 75, 1, 'Dog', NULL),
(86, 'Whiskers', 'Female', 4.10, 'Persian Mix', '2022-03-22', 76, 1, 'Cat', NULL),
(87, 'gigel', 'Male', 5.12, 'Sphynx cat', '2019-11-12', 77, 1, 'Cat', NULL),
(88, 'jake', 'Male', 5.12, 'bulldog', '2018-08-12', 77, 1, 'Dog', NULL),
(89, 'sipong', 'Male', 5.20, 'Sphynx cat', '2023-11-12', 77, 1, 'Cat', NULL);

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
(10, 'Maricar T. Bahala', '12345678890', 'Maricar', '$2y$10$81l0/56QI7vPfokuui0gH.dXPTGfUDzFD100YmvYD54Hc4Yk9vYq6'),
(11, 'Jake Kim', '09384521123', 'jakekim', '$2y$10$fQL/LRgba0zv335mucslj.c2zd2dTL8jDBiXIWKjrOwZ/cJNr9LNK'),
(12, 'Manuel Oclarit', '09384521123', 'manuel', '$2y$10$ReFcaAW/4xwP0ayvkDYP.uGvlJW0ARU40/WvoQpig8d6sY/YdJ.zm'),
(13, 'Odemil A. Uyan', '01234567890', 'odemil', '$2y$10$jPtmQ9Ut0FJ86bFBhbhEmejNPVLJN3Y8Ib2ph224ydwCMdUNZhl5i'),
(14, 'Febien M. Dela Cera', '09392516672', 'biano', '$2y$10$6F04LYtETksu7.DZSaX43ujf/b3t4D6IEBUgGYvfHBvKKbaK7B0ki');

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_unique_date_time` (`appointment_date`,`appointment_time`),
  ADD KEY `idx_client_id` (`client_id`),
  ADD KEY `idx_pet_id` (`pet_id`);

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
-- Indexes for table `client_accounts`
--
ALTER TABLE `client_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`consultation_id`),
  ADD KEY `idx_pet_id` (`pet_id`),
  ADD KEY `idx_client_id` (`client_id`);

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
  ADD KEY `pet_id` (`pet_id`),
  ADD KEY `fk_medical_consultation` (`consultation_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `fk_method_id` (`method_id`),
  ADD KEY `fk_payment_client` (`client_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `archive`
--
ALTER TABLE `archive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `client_accounts`
--
ALTER TABLE `client_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `consultation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=769;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `method_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pet`
--
ALTER TABLE `pet`
  MODIFY `pet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `veterinarian`
--
ALTER TABLE `veterinarian`
  MODIFY `vet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `client_accounts`
--
ALTER TABLE `client_accounts`
  ADD CONSTRAINT `fk_client_accounts_client_id` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `fk_consultation_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_consultation_pet` FOREIGN KEY (`pet_id`) REFERENCES `pet` (`pet_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `fk_medical_consultation` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`consultation_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`pet_id`) REFERENCES `pet` (`pet_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_method_id` FOREIGN KEY (`method_id`) REFERENCES `payment_methods` (`method_id`),
  ADD CONSTRAINT `fk_payment_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
