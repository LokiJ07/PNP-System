-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql300.infinityfree.com
-- Generation Time: Mar 22, 2026 at 09:36 PM
-- Server version: 11.4.10-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_41350743_pnp_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `table_name`, `record_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'LOGIN', 'users', 1, 'User logged in', '::1', '2026-03-17 03:30:16'),
(2, 2, 'LOGIN', 'users', 2, 'User logged in', '::1', '2026-03-17 03:30:16'),
(3, 2, 'LOGIN', 'users', 2, 'User logged in', '138.84.126.202', '2026-03-17 03:35:26'),
(4, 2, 'LOGIN', 'users', 2, 'User logged in', '138.84.126.202', '2026-03-17 03:35:29'),
(5, 5, 'LOGIN', 'users', 5, 'User logged in', '138.84.126.202', '2026-03-17 03:35:58'),
(10, 5, 'LOGIN', 'users', 5, 'User logged in', '143.44.193.240', '2026-03-18 09:42:53'),
(11, 5, 'LOGOUT', 'users', 5, 'User logged out', '126.209.18.230', '2026-03-19 02:02:12'),
(12, 1, 'LOGIN', 'users', 1, 'User logged in', '126.209.18.230', '2026-03-19 02:02:20'),
(13, 1, 'LOGIN', 'users', 1, 'User logged in', '120.28.193.234', '2026-03-23 01:07:17');

-- --------------------------------------------------------

--
-- Table structure for table `activity_photos`
--

CREATE TABLE `activity_photos` (
  `photo_id` int(11) NOT NULL,
  `activity_type` enum('patrol','checkpoint','oplan') NOT NULL,
  `activity_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `photo_name` varchar(255) DEFAULT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_photos`
--

INSERT INTO `activity_photos` (`photo_id`, `activity_type`, `activity_id`, `photo_path`, `photo_name`, `file_size`, `uploaded_at`) VALUES
(1, 'checkpoint', 1, 'uploads/activity_photos/activity_2_1773718602_0.jpg', NULL, NULL, '2026-03-17 03:36:42');

-- --------------------------------------------------------

--
-- Table structure for table `barangays`
--

CREATE TABLE `barangays` (
  `barangay_id` int(11) NOT NULL,
  `barangay_name` varchar(100) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT 'Manolo Fortich',
  `province` varchar(100) DEFAULT 'Bukidnon',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangays`
--

INSERT INTO `barangays` (`barangay_id`, `barangay_name`, `latitude`, `longitude`, `municipality`, `province`, `is_active`, `created_at`) VALUES
(1, 'Agusan Canyon', '8.33375600', '124.81538500', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(2, 'Abyawan', '8.42578000', '124.93722400', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(3, 'Alae', '8.42239400', '124.81303000', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(4, 'Dahilayan', '8.21923800', '124.85209300', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(5, 'Dalirig', '8.37639600', '124.90117600', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(6, 'Damilag', '8.35332400', '124.81329400', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(7, 'Dicklum', '8.37223500', '124.84915600', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(8, 'Guilang-guilang', '8.45752100', '125.04109100', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(9, 'Kalugmanan', '8.27723500', '124.86140300', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(10, 'Lindaban', '8.28964300', '124.84700500', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(11, 'Lingion', '8.40319400', '124.88830300', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(12, 'Lunocan', '8.43158700', '124.84030900', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(13, 'Maluko', '8.37517300', '124.95558900', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(14, 'Mambatangan', '8.46782200', '124.79061900', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(15, 'Minsuro', '8.51025300', '124.83125900', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(16, 'Mantibugao', '8.45850000', '124.82408400', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(17, 'Sankanan', '8.31593200', '124.85791300', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(18, 'Santiago', '8.43630800', '124.99578200', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(19, 'San Miguel', '8.38904800', '124.83593600', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(20, 'Santo Niño', '8.42842000', '124.86404200', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(21, 'Tankulan', '8.36637900', '124.86443200', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16'),
(22, 'Ticala', '8.34018700', '124.89189100', 'Manolo Fortich', 'Bukidnon', 1, '2026-03-17 03:30:16');

-- --------------------------------------------------------

--
-- Table structure for table `checkpoint_activities`
--

CREATE TABLE `checkpoint_activities` (
  `checkpoint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `specific_location` text NOT NULL,
  `checkpoint_date` date NOT NULL,
  `checkpoint_time` time NOT NULL,
  `border_control_ops` int(11) DEFAULT 0,
  `border_personnel` int(11) DEFAULT 0,
  `overlapping_ops` int(11) DEFAULT 0,
  `mobile_checkpoint_ops` int(11) DEFAULT 0,
  `mobile_personnel` int(11) DEFAULT 0,
  `tct_ovr_accomplishment` int(11) DEFAULT 0,
  `arrested_accomplishment` int(11) DEFAULT 0,
  `accomplishment_description` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `gps_accuracy` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `drinking_violations` int(11) DEFAULT 0,
  `smoking_violations` int(11) DEFAULT 0,
  `halfnaked_violations` int(11) DEFAULT 0,
  `curfew_violations` int(11) DEFAULT 0,
  `vandalism_violations` int(11) DEFAULT 0,
  `other_violations` int(11) DEFAULT 0,
  `other_violations_desc` varchar(255) DEFAULT NULL,
  `fixed_count` int(11) DEFAULT 0,
  `fined_count` int(11) DEFAULT 0,
  `warned_count` int(11) DEFAULT 0,
  `charged_count` int(11) DEFAULT 0,
  `community_service` int(11) DEFAULT 0,
  `disposition_others` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkpoint_activities`
--

INSERT INTO `checkpoint_activities` (`checkpoint_id`, `user_id`, `barangay_id`, `specific_location`, `checkpoint_date`, `checkpoint_time`, `border_control_ops`, `border_personnel`, `overlapping_ops`, `mobile_checkpoint_ops`, `mobile_personnel`, `tct_ovr_accomplishment`, `arrested_accomplishment`, `accomplishment_description`, `latitude`, `longitude`, `gps_accuracy`, `status`, `admin_remarks`, `submitted_at`, `updated_at`, `drinking_violations`, `smoking_violations`, `halfnaked_violations`, `curfew_violations`, `vandalism_violations`, `other_violations`, `other_violations_desc`, `fixed_count`, `fined_count`, `warned_count`, `charged_count`, `community_service`, `disposition_others`) VALUES
(1, 2, 20, 'Santo Niño, Manolo Fortich', '2026-03-17', '11:35:00', 0, 0, 0, 0, 0, 0, 0, '0', '8.42842000', '124.86404200', '0.00', 'approved', NULL, '2026-03-17 03:36:42', '2026-03-17 03:36:42', 0, 0, 0, 0, 0, 0, '', 0, 0, 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `report_type` varchar(20) DEFAULT NULL,
  `report_id` int(11) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oplan_activities`
--

CREATE TABLE `oplan_activities` (
  `oplan_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `oplan_type` enum('Oplan Bakal','Oplan Sita') NOT NULL,
  `specific_location` text NOT NULL,
  `oplan_date` date NOT NULL,
  `oplan_time` time NOT NULL,
  `personnel_count` int(11) DEFAULT 1,
  `operations_count` int(11) DEFAULT 1,
  `arrests_made` int(11) DEFAULT 0,
  `firearms_seized` int(11) DEFAULT 0,
  `contraband_kg` decimal(10,2) DEFAULT 0.00,
  `accomplishment_description` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `gps_accuracy` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `drinking_violations` int(11) DEFAULT 0,
  `smoking_violations` int(11) DEFAULT 0,
  `halfnaked_violations` int(11) DEFAULT 0,
  `curfew_violations` int(11) DEFAULT 0,
  `vandalism_violations` int(11) DEFAULT 0,
  `other_violations` int(11) DEFAULT 0,
  `other_violations_desc` varchar(255) DEFAULT NULL,
  `kontra_boga` int(11) DEFAULT 0,
  `anti_vaping` int(11) DEFAULT 0,
  `house_visitations` int(11) DEFAULT 0,
  `firearms_crs` int(11) DEFAULT 0,
  `fas_deposit` int(11) DEFAULT 0,
  `renewed_fas` int(11) DEFAULT 0,
  `fixed_count` int(11) DEFAULT 0,
  `fined_count` int(11) DEFAULT 0,
  `warned_count` int(11) DEFAULT 0,
  `charged_count` int(11) DEFAULT 0,
  `community_service` int(11) DEFAULT 0,
  `disposition_others` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patrol_activities`
--

CREATE TABLE `patrol_activities` (
  `patrol_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `barangay_id` int(11) NOT NULL,
  `patrol_type` enum('Foot Patrol','Mobile Patrol','Motorcycle Patrol') NOT NULL,
  `specific_location` text NOT NULL,
  `patrol_date` date NOT NULL,
  `patrol_time` time NOT NULL,
  `personnel_count` int(11) DEFAULT 1,
  `vehicle_number` varchar(50) DEFAULT NULL,
  `accomplishment_description` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `gps_accuracy` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'approved',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `drinking_violations` int(11) DEFAULT 0,
  `smoking_violations` int(11) DEFAULT 0,
  `halfnaked_violations` int(11) DEFAULT 0,
  `curfew_violations` int(11) DEFAULT 0,
  `vandalism_violations` int(11) DEFAULT 0,
  `other_violations` int(11) DEFAULT 0,
  `other_violations_desc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patrol_activities`
--

INSERT INTO `patrol_activities` (`patrol_id`, `user_id`, `barangay_id`, `patrol_type`, `specific_location`, `patrol_date`, `patrol_time`, `personnel_count`, `vehicle_number`, `accomplishment_description`, `latitude`, `longitude`, `gps_accuracy`, `status`, `admin_remarks`, `submitted_at`, `updated_at`, `drinking_violations`, `smoking_violations`, `halfnaked_violations`, `curfew_violations`, `vandalism_violations`, `other_violations`, `other_violations_desc`) VALUES
(1, 5, 5, 'Foot Patrol', 'Dalirig, Manolo Fortich, Bukidnon, Northern Mindanao, 8703, Philippines', '2026-03-17', '11:36:00', 1, '', 'rwkjheygfhjesfuye', '8.37422200', '124.90236300', '0.00', 'approved', NULL, '2026-03-17 03:37:05', '2026-03-17 03:37:05', 0, 0, 0, 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `badge_number` varchar(50) NOT NULL,
  `rank` varchar(20) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `civil_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_number` varchar(20) DEFAULT NULL,
  `station` varchar(100) DEFAULT 'Manolo Fortich MPS',
  `unit` varchar(100) DEFAULT 'Patrol Unit',
  `role` enum('admin','user') DEFAULT 'user',
  `date_hired` date DEFAULT NULL,
  `account_status` enum('active','inactive','suspended') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `badge_number`, `rank`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `profile_pic`, `contact_number`, `birthdate`, `gender`, `civil_status`, `address`, `emergency_contact_name`, `emergency_contact_number`, `station`, `unit`, `role`, `date_hired`, `account_status`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'ADMIN-001', 'PMAJ', 'Admin', 'User', NULL, 'admin@pnp.gov.ph', '$2y$10$lSORDmWp7cnwDExxO9sBP.LCG8V/7tXhH6V65z3/XhQcNeN1dR6Kq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'admin', NULL, 'active', NULL, '2026-03-17 03:30:16', '2026-03-17 03:30:16'),
(2, 'PNP-2024-0123', 'SPO2', 'Sherwin', 'Lumakang', NULL, 'sherwin@pnp.gov.ph', '$2y$10$xU8GGDECMxBzEL1KbwcSg.9/c.yes59crb1ImREKYRA.v3fJYfSd.', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'user', NULL, 'active', NULL, '2026-03-17 03:30:16', '2026-03-17 03:30:16'),
(3, '222', 'SPO3', 'June Rey', 'Sabaldana', NULL, 'sabaldana@pnp.gov.ph', '$2y$10$a4KNC0l1zHoW1gan30kMYe7Go7uXYDzcK11TUCcgJz65hXTBdsGwG', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'user', NULL, 'active', NULL, '2026-03-17 03:31:22', '2026-03-17 03:31:22'),
(4, 'PNP-1204', 'SPO4', 'John Frich', 'Magan', NULL, 'magan@pnp.gov.ph', '$2y$10$l15YzxF6Ki36wcJfztchLeEXJNkT1uG6tE/lVbu4jdMIL.yXvjIJe', NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'user', NULL, 'active', NULL, '2026-03-17 03:32:53', '2026-03-17 03:32:53'),
(5, 'PNP-1205', 'PLTCOL', 'Stephanie', 'Omongos', NULL, 'omongos@pnp.gov.ph', '$2y$10$vz1Xq4hBttD2wzOg3DC8Z.NwFdA5UttHv0oNAcwED8lKi5UI2QG52', NULL, NULL, NULL, 'Female', NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'user', NULL, 'active', NULL, '2026-03-17 03:33:38', '2026-03-17 03:33:38'),
(6, 'PNP-1206', 'PCMS', 'Jacob', 'Estahan', NULL, 'estahan@pnp.gov.ph', '$2y$10$uFuApXmLTnJIhJyY76fwO.9CsycMpHGIbH47kF/S1owqBatNHyXp2', NULL, NULL, NULL, 'Male', NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'user', NULL, 'active', NULL, '2026-03-17 03:34:22', '2026-03-17 03:34:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `activity_photos`
--
ALTER TABLE `activity_photos`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `idx_activity` (`activity_type`,`activity_id`);

--
-- Indexes for table `barangays`
--
ALTER TABLE `barangays`
  ADD PRIMARY KEY (`barangay_id`),
  ADD UNIQUE KEY `barangay_name` (`barangay_name`);

--
-- Indexes for table `checkpoint_activities`
--
ALTER TABLE `checkpoint_activities`
  ADD PRIMARY KEY (`checkpoint_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_barangay` (`barangay_id`),
  ADD KEY `idx_date` (`checkpoint_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `oplan_activities`
--
ALTER TABLE `oplan_activities`
  ADD PRIMARY KEY (`oplan_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_barangay` (`barangay_id`),
  ADD KEY `idx_date` (`oplan_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `patrol_activities`
--
ALTER TABLE `patrol_activities`
  ADD PRIMARY KEY (`patrol_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_barangay` (`barangay_id`),
  ADD KEY `idx_date` (`patrol_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `badge_number` (`badge_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_badge` (`badge_number`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`account_status`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `activity_photos`
--
ALTER TABLE `activity_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `barangays`
--
ALTER TABLE `barangays`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `checkpoint_activities`
--
ALTER TABLE `checkpoint_activities`
  MODIFY `checkpoint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `oplan_activities`
--
ALTER TABLE `oplan_activities`
  MODIFY `oplan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patrol_activities`
--
ALTER TABLE `patrol_activities`
  MODIFY `patrol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `checkpoint_activities`
--
ALTER TABLE `checkpoint_activities`
  ADD CONSTRAINT `checkpoint_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `checkpoint_activities_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`barangay_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `oplan_activities`
--
ALTER TABLE `oplan_activities`
  ADD CONSTRAINT `oplan_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `oplan_activities_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`barangay_id`);

--
-- Constraints for table `patrol_activities`
--
ALTER TABLE `patrol_activities`
  ADD CONSTRAINT `patrol_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `patrol_activities_ibfk_2` FOREIGN KEY (`barangay_id`) REFERENCES `barangays` (`barangay_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
