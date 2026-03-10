-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 10, 2026 at 04:39 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pnp_database`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetBarangayStatistics` (IN `p_barangay_id` INT, IN `p_start_date` DATE, IN `p_end_date` DATE)   BEGIN
    SELECT 
        b.barangay_name,
        COUNT(DISTINCT p.patrol_id) as patrol_count,
        COUNT(DISTINCT c.checkpoint_id) as checkpoint_count,
        COUNT(DISTINCT o.oplan_id) as oplan_count,
        COALESCE(SUM(p.personnel_count), 0) + COALESCE(SUM(c.border_personnel + c.mobile_personnel), 0) + COALESCE(SUM(o.personnel_count), 0) as total_personnel
    FROM barangays b
    LEFT JOIN patrol_activities p ON b.barangay_id = p.barangay_id AND DATE(p.submitted_at) BETWEEN p_start_date AND p_end_date
    LEFT JOIN checkpoint_activities c ON b.barangay_id = c.barangay_id AND DATE(c.submitted_at) BETWEEN p_start_date AND p_end_date
    LEFT JOIN oplan_activities o ON b.barangay_id = o.barangay_id AND DATE(o.submitted_at) BETWEEN p_start_date AND p_end_date
    WHERE b.barangay_id = p_barangay_id OR p_barangay_id = 0
    GROUP BY b.barangay_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserActivitySummary` (IN `p_user_id` INT)   BEGIN
    SELECT 
        'patrol' as type,
        COUNT(*) as total,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM patrol_activities
    WHERE user_id = p_user_id
    
    UNION ALL
    
    SELECT 
        'checkpoint',
        COUNT(*),
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END),
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END),
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END)
    FROM checkpoint_activities
    WHERE user_id = p_user_id
    
    UNION ALL
    
    SELECT 
        'oplan',
        COUNT(*),
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END),
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END),
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END)
    FROM oplan_activities
    WHERE user_id = p_user_id;
END$$

DELIMITER ;

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
(1, 1, 'LOGIN', 'users', 1, 'User logged in', '::1', '2026-03-10 00:50:33'),
(2, 2, 'LOGIN', 'users', 2, 'User logged in', '::1', '2026-03-10 00:53:13'),
(3, 1, 'LOGOUT', 'users', 1, 'User logged out', '::1', '2026-03-10 03:08:12');

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
(1, 'patrol', 1, 'uploads/patrol/1_1741500000.jpg', 'market_patrol.jpg', '2.3 MB', '2026-03-10 00:47:19'),
(2, 'patrol', 1, 'uploads/patrol/1_1741500001.jpg', 'establishment_check.jpg', '1.8 MB', '2026-03-10 00:47:19'),
(3, 'checkpoint', 1, 'uploads/checkpoint/1_1741500002.jpg', 'night_checkpoint.jpg', '3.1 MB', '2026-03-10 00:47:19'),
(4, 'oplan', 1, 'uploads/oplan/1_1741500003.jpg', 'firearm_recovery.jpg', '2.7 MB', '2026-03-10 00:47:19');

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
(1, 'Agusan Canyon', 8.33375600, 124.81538500, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(2, 'Abyawan', 8.42578000, 124.93722400, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(3, 'Alae', 8.42239400, 124.81303000, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(4, 'Dahilayan', 8.21923800, 124.85209300, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(5, 'Dalirig', 8.37639600, 124.90117600, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(6, 'Damilag', 8.35332400, 124.81329400, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(7, 'Dicklum', 8.37223500, 124.84915600, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(8, 'Guilang-guilang', 8.45752100, 125.04109100, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(9, 'Kalugmanan', 8.27723500, 124.86140300, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(10, 'Lindaban', 8.28964300, 124.84700500, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(11, 'Lingion', 8.40319400, 124.88830300, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(12, 'Lunocan', 8.43158700, 124.84030900, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(13, 'Maluko', 8.37517300, 124.95558900, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(14, 'Mambatangan', 8.46782200, 124.79061900, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(15, 'Minsuro', 8.51025300, 124.83125900, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(16, 'Mantibugao', 8.45850000, 124.82408400, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(17, 'Sankanan', 8.31593200, 124.85791300, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(18, 'Santiago', 8.43630800, 124.99578200, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(19, 'San Miguel', 8.38904800, 124.83593600, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(20, 'Santo Niño', 8.42842000, 124.86404200, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(21, 'Tankulan', 8.36637900, 124.86443200, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19'),
(22, 'Ticala', 8.34018700, 124.89189100, 'Manolo Fortich', 'Bukidnon', 1, '2026-03-10 00:47:19');

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
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `checkpoint_activities`
--

INSERT INTO `checkpoint_activities` (`checkpoint_id`, `user_id`, `barangay_id`, `specific_location`, `checkpoint_date`, `checkpoint_time`, `border_control_ops`, `border_personnel`, `overlapping_ops`, `mobile_checkpoint_ops`, `mobile_personnel`, `tct_ovr_accomplishment`, `arrested_accomplishment`, `accomplishment_description`, `latitude`, `longitude`, `gps_accuracy`, `status`, `admin_remarks`, `submitted_at`, `updated_at`) VALUES
(1, 2, 21, 'Tankulan National Highway', '2026-03-01', '20:00:00', 15, 8, 0, 5, 4, 3, 1, 'Conducted checkpoint operation. Checked 45 vehicles. Issued 3 TCT/OVR. Apprehended 1 individual for illegal possession.', 8.36637900, 124.86443200, NULL, 'approved', NULL, '2026-03-01 14:00:00', '2026-03-10 00:47:19'),
(2, 3, 3, 'Alae Boundary', '2026-03-02', '19:30:00', 12, 6, 0, 4, 3, 2, 0, 'Checkpoint operation at municipal boundary. Checked 38 vehicles. Issued 2 TCT/OVR. No arrests.', 8.42239400, 124.81303000, NULL, 'approved', NULL, '2026-03-02 13:30:00', '2026-03-10 00:47:19'),
(3, 4, 4, 'Dahilayan Entrance', '2026-03-03', '18:00:00', 8, 4, 0, 3, 2, 1, 0, 'Tourist area checkpoint. Checked 25 vehicles. All compliant.', 8.21923800, 124.85209300, NULL, 'rejected', '', '2026-03-03 12:00:00', '2026-03-10 00:51:47');

--
-- Triggers `checkpoint_activities`
--
DELIMITER $$
CREATE TRIGGER `after_checkpoint_insert` AFTER INSERT ON `checkpoint_activities` FOR EACH ROW BEGIN
    INSERT INTO notifications (user_id, type, message, report_type, report_id)
    SELECT user_id, 'new_report', 
           CONCAT('New checkpoint report submitted by ', 
                  (SELECT CONCAT(rank, ' ', first_name, ' ', last_name) FROM users WHERE user_id = NEW.user_id),
                  ' in ', (SELECT barangay_name FROM barangays WHERE barangay_id = NEW.barangay_id)),
           'checkpoint', NEW.checkpoint_id
    FROM users WHERE role = 'admin';
END
$$
DELIMITER ;

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `type`, `message`, `report_type`, `report_id`, `is_read`, `created_at`) VALUES
(1, 1, 'new_report', 'New patrol report submitted by PO3 Juan Dela Cruz in Tankulan', 'patrol', 1, 1, '2026-03-10 00:47:19'),
(2, 1, 'new_report', 'New patrol report submitted by PO3 Juan Dela Cruz in Tankulan', 'patrol', 2, 1, '2026-03-10 00:47:19'),
(3, 1, 'new_report', 'New patrol report submitted by SPO1 Maria Santos in Alae', 'patrol', 3, 1, '2026-03-10 00:47:19'),
(4, 1, 'new_report', 'New patrol report submitted by SPO1 Maria Santos in Alae', 'patrol', 4, 1, '2026-03-10 00:47:19'),
(5, 1, 'new_report', 'New patrol report submitted by PO2 Pedro Reyes in Dahilayan', 'patrol', 5, 1, '2026-03-10 00:47:19'),
(6, 1, 'new_report', 'New checkpoint report submitted by PO3 Juan Dela Cruz in Tankulan', 'checkpoint', 1, 1, '2026-03-10 00:47:19'),
(7, 1, 'new_report', 'New checkpoint report submitted by SPO1 Maria Santos in Alae', 'checkpoint', 2, 1, '2026-03-10 00:47:19'),
(8, 1, 'new_report', 'New checkpoint report submitted by PO2 Pedro Reyes in Dahilayan', 'checkpoint', 3, 1, '2026-03-10 00:47:19'),
(9, 1, 'new_report', 'New oplan report submitted by PO2 Pedro Reyes in Dahilayan', 'oplan', 1, 1, '2026-03-10 00:47:19'),
(10, 1, 'new_report', 'New oplan report submitted by PO3 Juan Dela Cruz in Tankulan', 'oplan', 2, 1, '2026-03-10 00:47:19'),
(11, 1, 'new_report', 'New oplan report submitted by SPO1 Maria Santos in Alae', 'oplan', 3, 1, '2026-03-10 00:47:19'),
(12, 1, 'new_report', 'New patrol report submitted by PO3 Juan Dela Cruz in Tankulan', 'patrol', 1, 1, '2026-03-01 01:35:00'),
(13, 1, 'new_report', 'New checkpoint report submitted by PO3 Juan Dela Cruz in Tankulan', 'checkpoint', 1, 1, '2026-03-01 14:00:00'),
(14, 1, 'new_report', 'New oplan report submitted by PO2 Pedro Reyes in Dahilayan', 'oplan', 1, 1, '2026-03-01 15:30:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `officer_performance`
-- (See below for the actual view)
--
CREATE TABLE `officer_performance` (
`user_id` int(11)
,`rank` varchar(20)
,`first_name` varchar(100)
,`last_name` varchar(100)
,`badge_number` varchar(50)
,`patrol_count` bigint(21)
,`checkpoint_count` bigint(21)
,`oplan_count` bigint(21)
,`total_activities` bigint(23)
,`approved_patrols` bigint(21)
,`approved_checkpoints` bigint(21)
,`approved_oplans` bigint(21)
);

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
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `oplan_activities`
--

INSERT INTO `oplan_activities` (`oplan_id`, `user_id`, `barangay_id`, `oplan_type`, `specific_location`, `oplan_date`, `oplan_time`, `personnel_count`, `operations_count`, `arrests_made`, `firearms_seized`, `contraband_kg`, `accomplishment_description`, `latitude`, `longitude`, `gps_accuracy`, `status`, `admin_remarks`, `submitted_at`, `updated_at`) VALUES
(1, 4, 4, 'Oplan Bakal', 'Dahilayan Interior', '2026-03-01', '22:00:00', 6, 1, 2, 1, 0.00, 'Oplan Bakal operation. Served warrant at residence. Apprehended 2 suspects. Recovered 1 firearm.', 8.21923800, 124.85209300, NULL, 'approved', NULL, '2026-03-01 15:30:00', '2026-03-10 00:47:19'),
(2, 2, 21, 'Oplan Sita', 'Tankulan Poblacion', '2026-03-02', '21:00:00', 5, 1, 3, 0, 2.50, 'Oplan Sita operation. Checked 10 individuals. Seized 2.5kg of suspected marijuana. Arrested 3 individuals.', 8.36637900, 124.86443200, NULL, 'approved', NULL, '2026-03-02 14:30:00', '2026-03-10 00:47:19'),
(3, 3, 3, 'Oplan Bakal', 'Alae Residential', '2026-03-03', '20:30:00', 4, 1, 1, 0, 0.00, 'Oplan Bakal operation. Apprehended 1 individual for outstanding warrant. No firearms recovered.', 8.42239400, 124.81303000, NULL, 'pending', NULL, '2026-03-03 14:00:00', '2026-03-10 00:47:19');

--
-- Triggers `oplan_activities`
--
DELIMITER $$
CREATE TRIGGER `after_oplan_insert` AFTER INSERT ON `oplan_activities` FOR EACH ROW BEGIN
    INSERT INTO notifications (user_id, type, message, report_type, report_id)
    SELECT user_id, 'new_report', 
           CONCAT('New oplan report submitted by ', 
                  (SELECT CONCAT(rank, ' ', first_name, ' ', last_name) FROM users WHERE user_id = NEW.user_id),
                  ' in ', (SELECT barangay_name FROM barangays WHERE barangay_id = NEW.barangay_id)),
           'oplan', NEW.oplan_id
    FROM users WHERE role = 'admin';
END
$$
DELIMITER ;

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
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patrol_activities`
--

INSERT INTO `patrol_activities` (`patrol_id`, `user_id`, `barangay_id`, `patrol_type`, `specific_location`, `patrol_date`, `patrol_time`, `personnel_count`, `vehicle_number`, `accomplishment_description`, `latitude`, `longitude`, `gps_accuracy`, `status`, `admin_remarks`, `submitted_at`, `updated_at`) VALUES
(1, 2, 21, 'Foot Patrol', 'Public Market, Tankulan', '2026-03-01', '09:30:00', 4, NULL, 'Conducted routine foot patrol around public market. Assisted 3 senior citizens. Checked 15 establishments for compliance. All businesses following regulations.', 8.36637900, 124.86443200, NULL, 'approved', NULL, '2026-03-01 01:35:00', '2026-03-10 00:47:19'),
(2, 2, 21, 'Mobile Patrol', 'National Highway, Tankulan', '2026-03-02', '14:15:00', 3, 'MCS-101', 'Patrolled national highway. Issued 2 traffic citations for illegal parking. No major incidents.', 8.36637900, 124.86443200, NULL, 'approved', NULL, '2026-03-02 06:20:00', '2026-03-10 00:47:19'),
(3, 3, 3, 'Foot Patrol', 'Alae Proper', '2026-03-01', '10:00:00', 2, NULL, 'Foot patrol around Alae commercial area. Checked 10 establishments. All compliant.', 8.42239400, 124.81303000, NULL, 'approved', NULL, '2026-03-01 02:05:00', '2026-03-10 00:47:19'),
(4, 3, 3, 'Mobile Patrol', 'Alae Highway', '2026-03-03', '08:45:00', 3, 'MCS-102', 'Mobile patrol along Alae highway. No incidents reported.', 8.42239400, 124.81303000, NULL, 'rejected', '', '2026-03-03 00:50:00', '2026-03-10 00:51:56'),
(5, 4, 4, 'Motorcycle Patrol', 'Dahilayan Forest Park', '2026-03-02', '15:30:00', 2, 'MC-001', 'Motorcycle patrol around tourist area. Assisted 2 tourists. No issues.', 8.21923800, 124.85209300, NULL, 'approved', NULL, '2026-03-02 07:35:00', '2026-03-10 00:47:19');

--
-- Triggers `patrol_activities`
--
DELIMITER $$
CREATE TRIGGER `after_patrol_insert` AFTER INSERT ON `patrol_activities` FOR EACH ROW BEGIN
    INSERT INTO notifications (user_id, type, message, report_type, report_id)
    SELECT user_id, 'new_report', 
           CONCAT('New patrol report submitted by ', 
                  (SELECT CONCAT(rank, ' ', first_name, ' ', last_name) FROM users WHERE user_id = NEW.user_id),
                  ' in ', (SELECT barangay_name FROM barangays WHERE barangay_id = NEW.barangay_id)),
           'patrol', NEW.patrol_id
    FROM users WHERE role = 'admin';
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `report_summary`
-- (See below for the actual view)
--
CREATE TABLE `report_summary` (
`report_type` varchar(10)
,`id` int(11)
,`user_id` int(11)
,`barangay_id` int(11)
,`subtype` varchar(17)
,`specific_location` mediumtext
,`submitted_at` timestamp
,`status` varchar(8)
,`accomplishment_description` mediumtext
);

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
(1, 'ADMIN-001', 'PMAJ', 'Admin', 'User', NULL, 'admin@pnp.gov.ph', '$2y$10$lSORDmWp7cnwDExxO9sBP.LCG8V/7tXhH6V65z3/XhQcNeN1dR6Kq', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Manolo Fortich MPS', 'Patrol Unit', 'admin', '2020-01-01', 'active', NULL, '2026-03-10 00:47:19', '2026-03-10 00:49:56'),
(2, 'PNP-2024-0123', 'PO3', 'Juan', 'Dela Cruz', NULL, 'juan.delacruz@pnp.gov.ph', '$2y$10$2hBweWjiikm6sYr6rELznugFsHIKnZwEIQG5WqehwFqwoyJyyg3kO', NULL, '0912-345-6789', '1990-03-15', 'Male', 'Married', 'Poblacion, Tankulan, Manolo Fortich', 'Maria Dela Cruz', '0918-765-4321', 'Manolo Fortich MPS', 'Patrol Unit', 'user', '2020-01-15', 'active', NULL, '2026-03-10 00:47:19', '2026-03-10 00:50:24'),
(3, 'PNP-2024-0124', 'SPO1', 'Maria', 'Santos', NULL, 'maria.santos@pnp.gov.ph', 'password123', NULL, '0923-456-7890', '1988-07-22', 'Female', 'Single', 'Alae, Manolo Fortich', 'Pedro Santos', '0929-876-5432', 'Manolo Fortich MPS', 'Checkpoint Unit', 'user', '2018-05-10', 'active', NULL, '2026-03-10 00:47:19', '2026-03-10 00:47:19'),
(4, 'PNP-2024-0125', 'PO2', 'Pedro', 'Reyes', NULL, 'pedro.reyes@pnp.gov.ph', 'password123', NULL, '0934-567-8901', '1992-11-08', 'Male', 'Single', 'Dahilayan, Manolo Fortich', 'Ana Reyes', '0938-765-4321', 'Manolo Fortich MPS', 'Oplan Unit', 'user', '2021-03-20', 'active', NULL, '2026-03-10 00:47:19', '2026-03-10 00:47:19');

-- --------------------------------------------------------

--
-- Structure for view `officer_performance`
--
DROP TABLE IF EXISTS `officer_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `officer_performance`  AS SELECT `u`.`user_id` AS `user_id`, `u`.`rank` AS `rank`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`badge_number` AS `badge_number`, (select count(0) from `patrol_activities` where `patrol_activities`.`user_id` = `u`.`user_id`) AS `patrol_count`, (select count(0) from `checkpoint_activities` where `checkpoint_activities`.`user_id` = `u`.`user_id`) AS `checkpoint_count`, (select count(0) from `oplan_activities` where `oplan_activities`.`user_id` = `u`.`user_id`) AS `oplan_count`, (select count(0) from `patrol_activities` where `patrol_activities`.`user_id` = `u`.`user_id`) + (select count(0) from `checkpoint_activities` where `checkpoint_activities`.`user_id` = `u`.`user_id`) + (select count(0) from `oplan_activities` where `oplan_activities`.`user_id` = `u`.`user_id`) AS `total_activities`, (select count(0) from `patrol_activities` where `patrol_activities`.`user_id` = `u`.`user_id` and `patrol_activities`.`status` = 'approved') AS `approved_patrols`, (select count(0) from `checkpoint_activities` where `checkpoint_activities`.`user_id` = `u`.`user_id` and `checkpoint_activities`.`status` = 'approved') AS `approved_checkpoints`, (select count(0) from `oplan_activities` where `oplan_activities`.`user_id` = `u`.`user_id` and `oplan_activities`.`status` = 'approved') AS `approved_oplans` FROM `users` AS `u` WHERE `u`.`role` = 'user' ;

-- --------------------------------------------------------

--
-- Structure for view `report_summary`
--
DROP TABLE IF EXISTS `report_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `report_summary`  AS SELECT 'patrol' AS `report_type`, `patrol_activities`.`patrol_id` AS `id`, `patrol_activities`.`user_id` AS `user_id`, `patrol_activities`.`barangay_id` AS `barangay_id`, `patrol_activities`.`patrol_type` AS `subtype`, `patrol_activities`.`specific_location` AS `specific_location`, `patrol_activities`.`submitted_at` AS `submitted_at`, `patrol_activities`.`status` AS `status`, `patrol_activities`.`accomplishment_description` AS `accomplishment_description` FROM `patrol_activities`union all select 'checkpoint' AS `checkpoint`,`checkpoint_activities`.`checkpoint_id` AS `checkpoint_id`,`checkpoint_activities`.`user_id` AS `user_id`,`checkpoint_activities`.`barangay_id` AS `barangay_id`,'Checkpoint' AS `Checkpoint`,`checkpoint_activities`.`specific_location` AS `specific_location`,`checkpoint_activities`.`submitted_at` AS `submitted_at`,`checkpoint_activities`.`status` AS `status`,`checkpoint_activities`.`accomplishment_description` AS `accomplishment_description` from `checkpoint_activities` union all select 'oplan' AS `oplan`,`oplan_activities`.`oplan_id` AS `oplan_id`,`oplan_activities`.`user_id` AS `user_id`,`oplan_activities`.`barangay_id` AS `barangay_id`,`oplan_activities`.`oplan_type` AS `oplan_type`,`oplan_activities`.`specific_location` AS `specific_location`,`oplan_activities`.`submitted_at` AS `submitted_at`,`oplan_activities`.`status` AS `status`,`oplan_activities`.`accomplishment_description` AS `accomplishment_description` from `oplan_activities`  ;

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
  ADD KEY `idx_activity` (`activity_type`,`activity_id`),
  ADD KEY `idx_photos_lookup` (`activity_type`,`activity_id`);

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
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_checkpoint_composite` (`checkpoint_date`,`barangay_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_read` (`user_id`,`is_read`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_notifications_unread` (`user_id`,`is_read`,`created_at`);

--
-- Indexes for table `oplan_activities`
--
ALTER TABLE `oplan_activities`
  ADD PRIMARY KEY (`oplan_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_barangay` (`barangay_id`),
  ADD KEY `idx_date` (`oplan_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_oplan_composite` (`oplan_date`,`barangay_id`,`oplan_type`);

--
-- Indexes for table `patrol_activities`
--
ALTER TABLE `patrol_activities`
  ADD PRIMARY KEY (`patrol_id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_barangay` (`barangay_id`),
  ADD KEY `idx_date` (`patrol_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_patrol_composite` (`patrol_date`,`barangay_id`,`patrol_type`);

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
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `activity_photos`
--
ALTER TABLE `activity_photos`
  MODIFY `photo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `barangays`
--
ALTER TABLE `barangays`
  MODIFY `barangay_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `checkpoint_activities`
--
ALTER TABLE `checkpoint_activities`
  MODIFY `checkpoint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `oplan_activities`
--
ALTER TABLE `oplan_activities`
  MODIFY `oplan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patrol_activities`
--
ALTER TABLE `patrol_activities`
  MODIFY `patrol_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
