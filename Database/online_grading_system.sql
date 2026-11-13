-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2025 at 03:24 AM
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
-- Database: `online_grading_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('present','absent','late','excused') DEFAULT NULL,
  `grading_period` enum('prelim','midterm','finals') NOT NULL DEFAULT 'midterm',
  `remarks` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `student_id`, `class_id`, `attendance_date`, `status`, `grading_period`, `remarks`, `recorded_by`, `created_at`, `updated_at`) VALUES
(75, 74, 60, '2025-12-08', 'present', 'finals', NULL, 72, '2025-12-08 08:35:16', '2025-12-08 08:35:18'),
(76, 73, 60, '2025-12-08', 'present', 'finals', NULL, 72, '2025-12-08 08:35:16', '2025-12-08 08:35:22'),
(77, 74, 60, '2025-12-08', 'present', 'midterm', NULL, 72, '2025-12-08 11:41:57', '2025-12-08 11:42:01'),
(78, 73, 60, '2025-12-08', 'absent', 'midterm', NULL, 72, '2025-12-08 11:41:57', '2025-12-08 11:42:03'),
(79, 74, 60, '2025-12-09', 'present', 'finals', NULL, 72, '2025-12-08 13:29:08', '2025-12-08 13:31:13'),
(80, 73, 60, '2025-12-09', NULL, 'finals', NULL, 72, '2025-12-08 13:29:08', '2025-12-08 13:29:08');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `action_type` enum('login','logout','create','update','delete','join','view') NOT NULL,
  `table_affected` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action`, `action_type`, `table_affected`, `record_id`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(952, 72, 'User registered and verified email', '', 'users', 72, 'New teacher registration: John Mark Delos Reyes Santos De', '127.0.0.1', NULL, '2025-12-08 04:02:11'),
(953, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:02:27'),
(954, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 04:02:33'),
(955, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:06:43'),
(956, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 04:06:49'),
(957, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:07:42'),
(958, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 04:07:47'),
(959, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:07:55'),
(960, 72, 'Changed password', 'update', 'users', 72, 'Password changed from IP: 127.0.0.1', '127.0.0.1', NULL, '2025-12-08 04:31:28'),
(961, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 04:31:33'),
(962, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:31:50'),
(963, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 04:32:00'),
(964, 72, 'Password reset requested', 'create', 'password_resets', NULL, 'User requested password reset for email: tracyannisip@gmail.com', '127.0.0.1', NULL, '2025-12-08 04:32:16'),
(965, 72, 'Password reset successfully', 'update', 'users', 72, 'Password was reset via email verification', '127.0.0.1', NULL, '2025-12-08 04:32:54'),
(966, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:33:06'),
(967, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 04:34:00'),
(968, 73, 'User registered and verified email', '', 'users', 73, 'New student registration: Tracy Ann Fabian David Isip Ticsay', '::1', NULL, '2025-12-08 04:36:07'),
(969, 73, 'User logged in', 'login', 'users', 73, 'Successful login', '::1', NULL, '2025-12-08 04:36:17'),
(970, 73, 'Changed password', 'update', 'users', 73, 'Password changed from IP: ::1', '::1', NULL, '2025-12-08 04:37:08'),
(971, 73, 'User logged out', 'logout', 'users', 73, 'Tracy Ann Fabian David Isip Ticsay logged out from the system', '::1', NULL, '2025-12-08 04:37:19'),
(972, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 04:37:30'),
(973, 73, 'Password reset requested', 'create', 'password_resets', NULL, 'User requested password reset for email: tracyannisip7@gmail.com', '::1', NULL, '2025-12-08 04:38:15'),
(974, 73, 'Password reset requested', 'create', 'password_resets', NULL, 'User requested password reset for email: tracyannisip7@gmail.com', '::1', NULL, '2025-12-08 04:40:05'),
(975, 73, 'Password reset requested', 'create', 'password_resets', NULL, 'User requested password reset for email: tracyannisip7@gmail.com', '::1', NULL, '2025-12-08 04:57:24'),
(976, 73, 'Password reset requested', 'create', 'password_resets', NULL, 'User requested password reset for email: tracyannisip7@gmail.com', '::1', NULL, '2025-12-08 05:18:25'),
(977, 73, 'Password reset successfully', 'update', 'users', 73, 'Password was reset via email verification', '::1', NULL, '2025-12-08 05:33:20'),
(978, 73, 'User logged in', 'login', 'users', 73, 'Successful login', '::1', NULL, '2025-12-08 05:33:30'),
(979, 72, 'Created class: Web Development - BSCS 3-A', 'create', 'classes', 58, '{\"class_id\":\"58\",\"class_code\":\"PSU158\",\"class_name\":\"Web Development\",\"subject\":\"WEBSYS 313\",\"section\":\"BSCS 3-A\",\"schedule_count\":1,\"timestamp\":\"2025-12-08 14:07:11\"}', '127.0.0.1', NULL, '2025-12-08 06:07:11'),
(980, 73, 'Joined class: Web Development', 'join', 'enrollments', 45, '{\"class_name\":\"Web Development\",\"subject\":\"WEBSYS 313\",\"section\":\"BSCS 3-A\",\"teacher_name\":\"John Mark Delos Reyes Santos De\"}', '::1', NULL, '2025-12-08 06:08:44'),
(981, 72, 'Created class: Web Development - BSIT 3-B', 'create', 'classes', 59, '{\"class_id\":\"59\",\"class_code\":\"PSU366\",\"class_name\":\"Web Development\",\"subject\":\"WEBSYS 313\",\"section\":\"BSIT 3-B\",\"schedule_count\":1,\"timestamp\":\"2025-12-08 14:10:53\"}', '127.0.0.1', NULL, '2025-12-08 06:10:53'),
(982, 72, 'Created class: Introduction to Programming - BSCS 3-A', 'create', 'classes', 60, '{\"class_id\":\"60\",\"class_code\":\"PSU985\",\"class_name\":\"Introduction to Programming\",\"subject\":\"CC213\",\"section\":\"BSCS 3-A\",\"schedule_count\":1,\"timestamp\":\"2025-12-08 14:12:15\"}', '127.0.0.1', NULL, '2025-12-08 06:12:15'),
(983, 72, 'Added attendance column', 'create', 'attendance', 58, 'Added attendance date: Dec 08, 2025 for midterm', '127.0.0.1', NULL, '2025-12-08 06:26:08'),
(984, 73, 'Updated profile picture', 'update', 'users', 73, 'Changed profile picture', '::1', NULL, '2025-12-08 06:55:17'),
(985, 73, 'Unenrolled from class: Web Development', '', 'enrollments', 45, '{\"class_name\":\"Web Development\",\"subject\":\"WEBSYS 313\",\"section\":\"BSCS 3-A\"}', '::1', NULL, '2025-12-08 06:59:37'),
(986, 73, 'Rejoined class: Web Development', 'join', 'enrollments', 45, '{\"class_name\":\"Web Development\",\"subject\":\"WEBSYS 313\",\"section\":\"BSCS 3-A\",\"teacher_name\":\"John Mark Delos Reyes Santos De\"}', '::1', NULL, '2025-12-08 07:00:36'),
(987, 72, 'Marked attendance', 'update', 'attendance', 73, 'Marked Tracy Ann Fabian David Isip Ticsay as Present on Dec 08, 2025 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:08'),
(988, 72, 'Added attendance column', 'create', 'attendance', 58, 'Added attendance date: Dec 09, 2025 for midterm', '127.0.0.1', NULL, '2025-12-08 07:03:15'),
(989, 72, 'Marked attendance', 'update', 'attendance', 73, 'Marked Tracy Ann Fabian David Isip Ticsay as Late on Dec 09, 2025 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:20'),
(990, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Recitation: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:30'),
(991, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Recitation for Tracy Ann Fabian David Isip Ticsay: 40 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:35'),
(992, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Activity 1: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:47'),
(993, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Activity 1 for Tracy Ann Fabian David Isip Ticsay: 35 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:52'),
(994, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Quiz 1: 100 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:03:57'),
(995, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 1 for Tracy Ann Fabian David Isip Ticsay: 75 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:02'),
(996, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Project 1: 100 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:08'),
(997, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Project 1 for Tracy Ann Fabian David Isip Ticsay: 90 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:14'),
(998, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Midterm Exam: 100 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:18'),
(999, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Midterm Exam for Tracy Ann Fabian David Isip Ticsay: 23 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:22'),
(1000, 72, 'Added grade column', 'create', 'grades', 58, 'Added Quiz 2 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 07:04:31'),
(1001, 72, 'Added grade column', 'create', 'grades', 58, 'Added Activity 2 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 07:04:33'),
(1002, 72, 'Added grade column', 'create', 'grades', 58, 'Added Quiz 3 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 07:04:36'),
(1003, 72, 'Added grade column', 'create', 'grades', 58, 'Added Activity 3 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 07:04:41'),
(1004, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Activity 2: 20 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:49'),
(1005, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Activity 2 for Tracy Ann Fabian David Isip Ticsay: 15 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:53'),
(1006, 72, 'Set column total', 'update', 'grades', 58, 'Set total score for Quiz 2: 30 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:04:57'),
(1007, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 2 for Tracy Ann Fabian David Isip Ticsay: 25 (midterm)', '127.0.0.1', NULL, '2025-12-08 07:05:04'),
(1008, 73, 'Unenrolled from class: Web Development', '', 'enrollments', 45, '{\"class_name\":\"Web Development\",\"subject\":\"WEBSYS 313\",\"section\":\"BSCS 3-A\"}', '::1', NULL, '2025-12-08 07:19:11'),
(1009, 72, 'Restored class section', 'update', 'classes', 60, 'Restored section BSCS 3-A - CC213 Introduction to Programming', '127.0.0.1', NULL, '2025-12-08 07:20:36'),
(1010, 73, 'Joined class: Introduction to Programming', 'join', 'enrollments', 46, '{\"class_name\":\"Introduction to Programming\",\"subject\":\"CC213\",\"section\":\"BSCS 3-A\",\"teacher_name\":\"John Mark Delos Reyes Santos De\"}', '::1', NULL, '2025-12-08 07:46:00'),
(1011, 73, 'User logged out', 'logout', 'users', 73, 'Tracy Ann Fabian David Isip Ticsay logged out from the system', '::1', NULL, '2025-12-08 07:47:47'),
(1012, 74, 'User registered and verified email', '', 'users', 74, 'New student registration: Troy Renz Fabian Isip', '::1', NULL, '2025-12-08 07:51:07'),
(1013, 74, 'User logged in', 'login', 'users', 74, 'Successful login', '::1', NULL, '2025-12-08 07:51:18'),
(1014, 74, 'Joined class: Introduction to Programming', 'join', 'enrollments', 47, '{\"class_name\":\"Introduction to Programming\",\"subject\":\"CC213\",\"section\":\"BSCS 3-A\",\"teacher_name\":\"John Mark Delos Reyes Santos De\"}', '::1', NULL, '2025-12-08 07:52:22'),
(1015, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Activity 1: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:16'),
(1016, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Quiz 1: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:20'),
(1017, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Project 1: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:23'),
(1018, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Project 1 for Troy Renz Fabian Isip: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:27'),
(1019, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Midterm Exam: 100 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:31'),
(1020, 72, 'Added grade column', 'create', 'grades', 60, 'Added Quiz 2 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 08:32:33'),
(1021, 72, 'Added grade column', 'create', 'grades', 60, 'Added Quiz 3 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 08:32:36'),
(1022, 72, 'Added grade column', 'create', 'grades', 60, 'Added Activity 2 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 08:32:39'),
(1023, 72, 'Added grade column', 'create', 'grades', 60, 'Added Activity 3 (initial total: 0) for midterm', '127.0.0.1', NULL, '2025-12-08 08:32:40'),
(1024, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Recitation: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:50'),
(1025, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Activity 1 for Troy Renz Fabian Isip: 20 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:54'),
(1026, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Activity 1 for Tracy Ann Fabian David Isip Ticsay: 30 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:32:59'),
(1027, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Activity 2: 40 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:03'),
(1028, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Activity 2 for Troy Renz Fabian Isip: 25 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:06'),
(1029, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Activity 2 for Tracy Ann Fabian David Isip Ticsay: 20 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:12'),
(1030, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Activity 3: 40 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:15'),
(1031, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Activity 3 for Troy Renz Fabian Isip: 40 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:19'),
(1032, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Activity 3 for Tracy Ann Fabian David Isip Ticsay: 40 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:23'),
(1033, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Quiz 1 for Troy Renz Fabian Isip: 50 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:35'),
(1034, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 1 for Tracy Ann Fabian David Isip Ticsay: 45 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:46'),
(1035, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Quiz 2: 40 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:53'),
(1036, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Quiz 2 for Troy Renz Fabian Isip: 20 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:33:57'),
(1037, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 2 for Tracy Ann Fabian David Isip Ticsay: 25 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:34:01'),
(1038, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Midterm Exam for Troy Renz Fabian Isip: 89 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:34:06'),
(1039, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Midterm Exam for Tracy Ann Fabian David Isip Ticsay: 89 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:34:12'),
(1040, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Quiz 3: 90 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:34:18'),
(1041, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Quiz 3 for Troy Renz Fabian Isip: 79 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:34:24'),
(1042, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 3 for Tracy Ann Fabian David Isip Ticsay: 80 (midterm)', '127.0.0.1', NULL, '2025-12-08 08:34:29'),
(1043, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Activity 1: 30 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:35'),
(1044, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Activity 1 for Troy Renz Fabian Isip: 30 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:38'),
(1045, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Activity 1 for Tracy Ann Fabian David Isip Ticsay: 30 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:41'),
(1046, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Quiz 1: 40 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:44'),
(1047, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Quiz 1 for Troy Renz Fabian Isip: 40 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:48'),
(1048, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 1 for Tracy Ann Fabian David Isip Ticsay: 40 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:51'),
(1049, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Project 1: 100 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:54'),
(1050, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Project 1 for Troy Renz Fabian Isip: 90 (finals)', '127.0.0.1', NULL, '2025-12-08 08:34:57'),
(1051, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Project 1 for Tracy Ann Fabian David Isip Ticsay: 99 (finals)', '127.0.0.1', NULL, '2025-12-08 08:35:01'),
(1052, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Final Exam: 100 (finals)', '127.0.0.1', NULL, '2025-12-08 08:35:04'),
(1053, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Final Exam for Tracy Ann Fabian David Isip Ticsay: 89 (finals)', '127.0.0.1', NULL, '2025-12-08 08:35:08'),
(1054, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Final Exam for Troy Renz Fabian Isip: 90 (finals)', '127.0.0.1', NULL, '2025-12-08 08:35:10'),
(1055, 72, 'Added attendance column', 'create', 'attendance', 60, 'Added attendance date: Dec 08, 2025 for finals', '127.0.0.1', NULL, '2025-12-08 08:35:16'),
(1056, 72, 'Marked attendance', 'update', 'attendance', 74, 'Marked Troy Renz Fabian Isip as Present on Dec 08, 2025 (finals)', '127.0.0.1', NULL, '2025-12-08 08:35:18'),
(1057, 72, 'Marked attendance', 'update', 'attendance', 73, 'Marked Tracy Ann Fabian David Isip Ticsay as Present on Dec 08, 2025 (finals)', '127.0.0.1', NULL, '2025-12-08 08:35:22'),
(1058, 72, 'Permanently deleted class section', 'delete', 'classes', 58, 'Deleted: BSCS 3-A - WEBSYS 313 Web Development', '127.0.0.1', NULL, '2025-12-08 08:51:21'),
(1059, 74, 'User logged out', 'logout', 'users', 74, 'Troy Renz Fabian Isip logged out from the system', '::1', NULL, '2025-12-08 09:04:56'),
(1060, 72, 'Added attendance column', 'create', 'attendance', 60, 'Added attendance date: Dec 08, 2025 for midterm', '127.0.0.1', NULL, '2025-12-08 11:41:57'),
(1061, 72, 'Marked attendance', 'update', 'attendance', 74, 'Marked Troy Renz Fabian Isip as Present on Dec 08, 2025 (midterm)', '127.0.0.1', NULL, '2025-12-08 11:42:01'),
(1062, 72, 'Marked attendance', 'update', 'attendance', 73, 'Marked Tracy Ann Fabian David Isip Ticsay as Absent on Dec 08, 2025 (midterm)', '127.0.0.1', NULL, '2025-12-08 11:42:03'),
(1063, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Recitation for Troy Renz Fabian Isip: 0 (midterm)', '127.0.0.1', NULL, '2025-12-08 11:42:30'),
(1064, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 11:55:35'),
(1065, 73, 'User logged in', 'login', 'users', 73, 'Successful login', '127.0.0.1', NULL, '2025-12-08 12:55:29'),
(1066, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '::1', NULL, '2025-12-08 13:27:41'),
(1067, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '::1', NULL, '2025-12-08 13:27:56'),
(1068, 73, 'User logged in', 'login', 'users', 73, 'Successful login', '::1', NULL, '2025-12-08 13:28:09'),
(1069, 73, 'User logged out', 'logout', 'users', 73, 'Tracy Ann Fabian David Isip Ticsay logged out from the system', '127.0.0.1', NULL, '2025-12-08 13:28:41'),
(1070, 72, 'User logged in', 'login', 'users', 72, 'Successful login', '127.0.0.1', NULL, '2025-12-08 13:28:48'),
(1071, 72, 'Added attendance column', 'create', 'attendance', 60, 'Added attendance date: Dec 09, 2025 for finals', '127.0.0.1', NULL, '2025-12-08 13:29:08'),
(1072, 72, 'Marked attendance', 'update', 'attendance', 74, 'Marked Troy Renz Fabian Isip as Absent on Dec 09, 2025 (finals)', '127.0.0.1', NULL, '2025-12-08 13:29:14'),
(1073, 72, 'Added grade column', 'create', 'grades', 60, 'Added Quiz 2 (initial total: 0) for finals', '127.0.0.1', NULL, '2025-12-08 13:29:20'),
(1074, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Quiz 2: 50 (finals)', '127.0.0.1', NULL, '2025-12-08 13:29:24'),
(1075, 72, 'Updated grade', 'update', 'grades', 73, 'Updated Quiz 2 for Tracy Ann Fabian David Isip Ticsay: 50 (finals)', '127.0.0.1', NULL, '2025-12-08 13:29:30'),
(1076, 72, 'Added grade column', 'create', 'grades', 60, 'Added Quiz 3 (initial total: 0) for finals', '127.0.0.1', NULL, '2025-12-08 13:30:08'),
(1077, 72, 'Set column total', 'update', 'grades', 60, 'Set total score for Quiz 3: 50 (finals)', '127.0.0.1', NULL, '2025-12-08 13:30:15'),
(1078, 72, 'Updated grade', 'update', 'grades', 74, 'Updated Quiz 3 for Troy Renz Fabian Isip: 30 (finals)', '127.0.0.1', NULL, '2025-12-08 13:30:18'),
(1079, 72, 'Marked attendance', 'update', 'attendance', 74, 'Marked Troy Renz Fabian Isip as Present on Dec 09, 2025 (finals)', '127.0.0.1', NULL, '2025-12-08 13:31:13'),
(1080, 73, 'User logged out', 'logout', 'users', 73, 'Tracy Ann Fabian David Isip Ticsay logged out from the system', '::1', NULL, '2025-12-08 13:36:06'),
(1081, 75, 'User registered and verified email', '', 'users', 75, 'New student registration: Tracy Ann Fabian Isip', '::1', NULL, '2025-12-08 13:48:04'),
(1082, 75, 'User logged in', 'login', 'users', 75, 'Successful login', '::1', NULL, '2025-12-08 13:48:17'),
(1083, 75, 'Joined class: Introduction to Programming', 'join', 'enrollments', 48, '{\"class_name\":\"Introduction to Programming\",\"subject\":\"CC213\",\"section\":\"BSCS 3-A\",\"teacher_name\":\"John Mark Delos Reyes Santos De\"}', '::1', NULL, '2025-12-08 13:48:51'),
(1084, 72, 'User logged out', 'logout', 'users', 72, 'John Mark Delos Reyes Santos De logged out from the system', '127.0.0.1', NULL, '2025-12-08 14:43:05');

-- --------------------------------------------------------

--
-- Table structure for table `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `class_code` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classes`
--

INSERT INTO `classes` (`class_id`, `teacher_id`, `class_name`, `subject`, `section`, `class_code`, `created_at`, `updated_at`, `status`) VALUES
(59, 72, 'Web Development', 'WEBSYS 313', 'BSIT 3-B', 'PSU366', '2025-12-08 06:10:53', '2025-12-08 06:10:53', 'active'),
(60, 72, 'Introduction to Programming', 'CC213', 'BSCS 3-A', 'PSU985', '2025-12-08 06:12:15', '2025-12-08 07:20:36', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `class_schedules`
--

CREATE TABLE `class_schedules` (
  `schedule_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `class_schedules`
--

INSERT INTO `class_schedules` (`schedule_id`, `class_id`, `day_of_week`, `start_time`, `end_time`, `room`, `created_at`, `updated_at`) VALUES
(42, 59, 'Monday', '10:00:00', '12:00:00', 'ROOM 101', '2025-12-08 06:10:53', '2025-12-08 06:10:53'),
(43, 60, 'Monday', '10:00:00', '12:00:00', 'ROOM 203', '2025-12-08 06:12:15', '2025-12-08 06:12:15');

-- --------------------------------------------------------

--
-- Table structure for table `email_verifications`
--

CREATE TABLE `email_verifications` (
  `verification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','dropped','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `class_id`, `enrolled_at`, `updated_at`, `status`) VALUES
(46, 73, 60, '2025-12-08 07:46:00', '2025-12-08 07:46:00', 'active'),
(47, 74, 60, '2025-12-08 07:52:22', '2025-12-08 07:52:22', 'active'),
(48, 75, 60, '2025-12-08 13:48:51', '2025-12-08 13:48:51', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `activity_name` varchar(100) NOT NULL,
  `activity_type` enum('quiz','exam','assignment','project','recitation','other') NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `max_score` decimal(5,2) NOT NULL,
  `percentage` decimal(5,2) GENERATED ALWAYS AS (`score` / `max_score` * 100) STORED,
  `remarks` varchar(255) DEFAULT NULL,
  `grading_period` enum('prelim','midterm','finals') NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`grade_id`, `student_id`, `class_id`, `activity_name`, `activity_type`, `score`, `max_score`, `remarks`, `grading_period`, `recorded_by`, `created_at`, `updated_at`) VALUES
(328, 73, 60, 'Recitation', 'recitation', 0.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:47:28', '2025-12-08 08:32:50'),
(329, 73, 60, 'Quiz 1', 'quiz', 45.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:47:28', '2025-12-08 08:33:46'),
(330, 73, 60, 'Activity 1', '', 30.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:47:28', '2025-12-08 08:32:59'),
(331, 73, 60, 'Project 1', 'project', 0.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:47:28', '2025-12-08 08:32:23'),
(332, 73, 60, 'Midterm Exam', 'exam', 89.00, 100.00, NULL, 'midterm', 72, '2025-12-08 07:47:28', '2025-12-08 08:34:12'),
(333, 74, 60, 'Recitation', 'recitation', 0.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:55:35', '2025-12-08 11:42:30'),
(334, 74, 60, 'Quiz 1', 'quiz', 50.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:55:35', '2025-12-08 08:33:35'),
(335, 74, 60, 'Activity 1', '', 20.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:55:35', '2025-12-08 08:32:54'),
(336, 74, 60, 'Project 1', 'project', 50.00, 50.00, NULL, 'midterm', 72, '2025-12-08 07:55:35', '2025-12-08 08:32:27'),
(337, 74, 60, 'Midterm Exam', 'exam', 89.00, 100.00, NULL, 'midterm', 72, '2025-12-08 07:55:35', '2025-12-08 08:34:06'),
(338, 74, 60, 'Quiz 2', 'quiz', 20.00, 40.00, NULL, 'midterm', 72, '2025-12-08 08:32:33', '2025-12-08 08:33:57'),
(339, 73, 60, 'Quiz 2', 'quiz', 25.00, 40.00, NULL, 'midterm', 72, '2025-12-08 08:32:33', '2025-12-08 08:34:01'),
(340, 74, 60, 'Quiz 3', 'quiz', 79.00, 90.00, NULL, 'midterm', 72, '2025-12-08 08:32:36', '2025-12-08 08:34:24'),
(341, 73, 60, 'Quiz 3', 'quiz', 80.00, 90.00, NULL, 'midterm', 72, '2025-12-08 08:32:36', '2025-12-08 08:34:29'),
(342, 74, 60, 'Activity 2', '', 25.00, 40.00, NULL, 'midterm', 72, '2025-12-08 08:32:39', '2025-12-08 08:33:06'),
(343, 73, 60, 'Activity 2', '', 20.00, 40.00, NULL, 'midterm', 72, '2025-12-08 08:32:39', '2025-12-08 08:33:12'),
(344, 74, 60, 'Activity 3', '', 40.00, 40.00, NULL, 'midterm', 72, '2025-12-08 08:32:40', '2025-12-08 08:33:19'),
(345, 73, 60, 'Activity 3', '', 40.00, 40.00, NULL, 'midterm', 72, '2025-12-08 08:32:40', '2025-12-08 08:33:23'),
(346, 74, 60, 'Recitation', 'recitation', 0.00, 0.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:31'),
(347, 74, 60, 'Quiz 1', 'quiz', 40.00, 40.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:48'),
(348, 74, 60, 'Activity 1', '', 30.00, 30.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:38'),
(349, 74, 60, 'Project 1', 'project', 90.00, 100.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:57'),
(350, 74, 60, 'Final Exam', 'exam', 90.00, 100.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:35:10'),
(351, 73, 60, 'Recitation', 'recitation', 0.00, 0.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:31'),
(352, 73, 60, 'Quiz 1', 'quiz', 40.00, 40.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:51'),
(353, 73, 60, 'Activity 1', '', 30.00, 30.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:34:41'),
(354, 73, 60, 'Project 1', 'project', 99.00, 100.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:35:01'),
(355, 73, 60, 'Final Exam', 'exam', 89.00, 100.00, NULL, 'finals', 72, '2025-12-08 08:34:31', '2025-12-08 08:35:08'),
(356, 74, 60, 'Quiz 2', 'quiz', 0.00, 50.00, NULL, 'finals', 72, '2025-12-08 13:29:20', '2025-12-08 13:29:24'),
(357, 73, 60, 'Quiz 2', 'quiz', 50.00, 50.00, NULL, 'finals', 72, '2025-12-08 13:29:20', '2025-12-08 13:29:30'),
(358, 74, 60, 'Quiz 3', 'quiz', 30.00, 50.00, NULL, 'finals', 72, '2025-12-08 13:30:08', '2025-12-08 13:30:18'),
(359, 73, 60, 'Quiz 3', 'quiz', 0.00, 50.00, NULL, 'finals', 72, '2025-12-08 13:30:08', '2025-12-08 13:30:15');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `reset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `reset_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`reset_id`, `user_id`, `email`, `reset_code`, `expires_at`, `used`, `created_at`, `used_at`, `ip_address`) VALUES
(27, 72, 'tracyannisip@gmail.com', '502261', '2025-12-08 13:02:13', 1, '2025-12-08 04:32:13', '2025-12-08 12:32:54', NULL),
(28, 73, 'tracyannisip7@gmail.com', '040496', '2025-12-08 13:08:12', 1, '2025-12-08 04:38:12', NULL, NULL),
(29, 73, 'tracyannisip7@gmail.com', '076442', '2025-12-08 13:10:00', 1, '2025-12-08 04:40:00', NULL, NULL),
(30, 73, 'tracyannisip7@gmail.com', '089436', '2025-12-08 13:27:21', 1, '2025-12-08 04:57:21', NULL, NULL),
(31, 73, 'tracyannisip7@gmail.com', '151761', '2025-12-08 13:48:21', 1, '2025-12-08 05:18:21', '2025-12-08 13:33:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `day_of_week` varchar(20) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `room` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_codes`
--

CREATE TABLE `teacher_codes` (
  `code_id` int(11) NOT NULL,
  `invitation_code` varchar(50) NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `use_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_codes`
--

INSERT INTO `teacher_codes` (`code_id`, `invitation_code`, `is_used`, `created_at`, `use_count`) VALUES
(1, 'PSU-indEx2025', 1, '2025-10-20 11:47:57', 17);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `profile_picture_blob` longblob DEFAULT NULL,
  `profile_picture_mime_type` varchar(50) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `role` enum('teacher','student') NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `program` varchar(10) DEFAULT NULL COMMENT 'Course program: BSIT, BSCS, ACT, BSIS',
  `year_section` varchar(20) DEFAULT NULL COMMENT 'Year and Section: e.g., BSCS 3-A',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('active','inactive','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `email_verified`, `verification_token`, `password`, `full_name`, `first_name`, `last_name`, `middle_name`, `profile_picture_blob`, `profile_picture_mime_type`, `profile_picture`, `role`, `username`, `contact_number`, `student_number`, `program`, `year_section`, `created_at`, `updated_at`, `status`) VALUES
(72, 'tracyannisip@gmail.com', 1, NULL, '$2y$10$RbhIEZFtnEP1pElQ8t6XwuD/TiX9W9KpWxHNtH5AFjdSPDmhixFkW', 'John Mark Delos Reyes Santos De', 'John Mark', 'Santos De', 'Delos Reyes', NULL, NULL, NULL, 'teacher', NULL, '09123456789', NULL, NULL, NULL, '2025-12-08 04:02:11', '2025-12-08 04:32:54', 'active'),
(73, 'tracyannisip7@gmail.com', 1, NULL, '$2y$10$huM2HpdrBzyi6IrTUfU3se4fnyei0q/SKrw6dOzRP1Hjvc5USGm6.', 'Tracy Ann Fabian David Isip Ticsay', 'Tracy Ann', 'Isip Ticsay', 'Fabian David', NULL, NULL, 'profile_73_1765176917.jpg', 'student', NULL, '09953919857', '5676879813', 'BSCS', '3-A', '2025-12-08 04:36:07', '2025-12-08 06:55:17', 'active'),
(74, 'kkurt8316@gmail.com', 1, NULL, '$2y$10$dQXq19LLxYgksBOdmBrhHO11qZKsxolRMvppONWB/xhdFRWX/eQCm', 'Troy Renz Fabian Isip', 'Troy Renz', 'Isip', 'Fabian', NULL, NULL, NULL, 'student', NULL, '09953919854', '5676879813', 'BSCS', '3-A', '2025-12-08 07:51:07', '2025-12-08 07:51:07', 'active'),
(75, 'bondockrystal8@gmail.com', 1, NULL, '$2y$10$22bOAcHoWLieDXsw4n4ELeWJmaiGeDeIhX4e8sLhUqkQ0IZvQnKb6', 'Tracy Ann Fabian Isip', 'Tracy Ann', 'Isip', 'Fabian', NULL, NULL, NULL, 'student', NULL, '09953919857', '5676879813', 'BSIT', '3-A', '2025-12-08 13:48:04', '2025-12-08 13:48:04', 'active');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `before_update_profile_pic` BEFORE UPDATE ON `users` FOR EACH ROW BEGIN
    IF NEW.profile_picture_blob IS NOT NULL THEN
        SET NEW.profile_picture_mime_type = NEW.profile_picture_mime_type;
    END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`student_id`,`class_id`,`attendance_date`,`grading_period`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_attendance_student` (`student_id`),
  ADD KEY `idx_attendance_date` (`attendance_date`),
  ADD KEY `idx_attendance_student_date` (`student_id`,`class_id`,`attendance_date`,`grading_period`),
  ADD KEY `idx_attendance_period` (`class_id`,`grading_period`,`attendance_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user` (`user_id`),
  ADD KEY `idx_audit_date` (`created_at`);

--
-- Indexes for table `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `class_code` (`class_code`),
  ADD UNIQUE KEY `class_code_2` (`class_code`),
  ADD KEY `idx_class_code` (`class_code`),
  ADD KEY `idx_teacher_status` (`teacher_id`,`status`);

--
-- Indexes for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `day_of_week` (`day_of_week`),
  ADD KEY `idx_class_schedules_class_day` (`class_id`,`day_of_week`),
  ADD KEY `idx_class_schedules_time` (`start_time`,`end_time`);

--
-- Indexes for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD PRIMARY KEY (`verification_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`verification_code`),
  ADD KEY `idx_expires` (`expires_at`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD UNIQUE KEY `unique_enrollment` (`student_id`,`class_id`),
  ADD UNIQUE KEY `unique_active_enrollment` (`student_id`,`class_id`,`status`),
  ADD KEY `idx_enrollment_student` (`student_id`),
  ADD KEY `idx_enrollment_class` (`class_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_grade_record` (`student_id`,`class_id`,`activity_name`,`grading_period`),
  ADD KEY `recorded_by` (`recorded_by`),
  ADD KEY `idx_grades_student` (`student_id`),
  ADD KEY `idx_grades_student_activity` (`student_id`,`class_id`,`activity_name`),
  ADD KEY `idx_grades_period` (`class_id`,`grading_period`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`reset_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_code` (`reset_code`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `day_of_week` (`day_of_week`),
  ADD KEY `idx_schedule_class` (`class_id`),
  ADD KEY `idx_schedule_day` (`day_of_week`),
  ADD KEY `idx_schedule_class_day` (`class_id`,`day_of_week`);

--
-- Indexes for table `teacher_codes`
--
ALTER TABLE `teacher_codes`
  ADD PRIMARY KEY (`code_id`),
  ADD UNIQUE KEY `invitation_code` (`invitation_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_role` (`role`),
  ADD KEY `idx_user_profile` (`user_id`),
  ADD KEY `idx_student_number` (`student_number`),
  ADD KEY `idx_program` (`program`),
  ADD KEY `idx_year_section` (`year_section`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1085;

--
-- AUTO_INCREMENT for table `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `class_schedules`
--
ALTER TABLE `class_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `email_verifications`
--
ALTER TABLE `email_verifications`
  MODIFY `verification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=360;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `reset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `teacher_codes`
--
ALTER TABLE `teacher_codes`
  MODIFY `code_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `class_schedules`
--
ALTER TABLE `class_schedules`
  ADD CONSTRAINT `class_schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `email_verifications`
--
ALTER TABLE `email_verifications`
  ADD CONSTRAINT `email_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grades_ibfk_3` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
