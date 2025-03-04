-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2025 at 04:55 PM
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
-- Database: `yourdrive`
--
CREATE DATABASE IF NOT EXISTS `yourdrive` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `yourdrive`;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `group_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `name`, `phone`, `group_name`, `created_at`) VALUES
(1, 11, 'Snehasish Sarkar', '7044122730', 'me', '2025-02-20 15:31:50'),
(2, 11, 'Snehasish Sarkar', '7044122730', 'me', '2025-02-20 15:31:51'),
(3, 11, 'Snehasish Sarkar', '7044122730', 'me', '2025-02-20 15:31:51'),
(4, 11, 'Snehasish Sarkar', '7044122730', 'me', '2025-02-20 15:31:51'),
(5, 11, 'mememe', '7044122730', 'me', '2025-02-20 15:32:35'),
(6, 11, 'mememe', '7044122730', 'me', '2025-02-20 15:32:35'),
(7, 11, 'mememe', '7044122730', 'me', '2025-02-20 15:32:35'),
(8, 11, 'mememe', '7044122730', 'me', '2025-02-20 15:32:36'),
(9, 11, 'ofdsoklfnlsodjkpi', '763216387926139', '', '2025-02-20 15:36:57'),
(10, 11, 'Snehasish Sarkar', '07044122730', '', '2025-02-20 15:39:45'),
(11, 11, 'Snehasish Sarkar', '07044122730', '', '2025-02-20 15:39:51'),
(12, 11, 'Snehasish Sarkar', '7044122730', 'me', '2025-02-20 15:44:19'),
(14, 12, 'bubu', '1234567890', '', '2025-02-20 15:59:40'),
(15, 11, 'asdsad', '131312312312321', 'asdas', '2025-02-24 18:55:43');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `version` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `url`, `uploaded_at`, `version`) VALUES
(1, 's', 'https://res.cloudinary.com/dzn369qpk/raw/upload/v1740985448/t4yh26p0jmcn4hx3rkmw.tmp', '2025-03-03 07:25:48', 1),
(2, 's', 'https://res.cloudinary.com/dzn369qpk/raw/upload/v1740985716/mpz7qxdz2v9c4ta1dvrv.tmp', '2025-03-03 07:25:48', 1),
(3, 's', 'https://res.cloudinary.com/dzn369qpk/raw/upload/v1740985727/i1afk3qaiba7axc2ess1.tmp', '2025-03-03 07:25:48', 1),
(4, 'ss', 'https://res.cloudinary.com/dzn369qpk/raw/upload/v1740985830/lltzepnygmvwxhprynjd.tmp', '2025-03-03 07:25:48', 1),
(5, 'Online Booking Receipt', 'https://res.cloudinary.com/dzn369qpk/raw/upload/v1740985951/pfj3cnxdtghb8kbxpupj.tmp', '2025-03-03 07:25:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_data` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `photos`
--

INSERT INTO `photos` (`id`, `filename`, `filepath`, `uploaded_at`, `description`, `tags`, `title`, `user_id`) VALUES
(10, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(11, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(12, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', '', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(14, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', '', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(16, '1000007225-01.jpeg', '', '2025-02-16 20:21:22', NULL, NULL, NULL, 0),
(17, '1702392863857.jpg', '', '2025-02-16 20:21:29', NULL, NULL, NULL, 0),
(18, 'wallpaperflare.com_wallpaper.jpg', '', '2025-02-16 20:21:42', NULL, NULL, NULL, 0),
(19, '20240103_120659.JPG', '', '2025-02-16 20:30:51', NULL, NULL, NULL, 0),
(20, 'rgb(56, 1, 57) (1).png', '', '2025-02-16 20:31:25', NULL, NULL, NULL, 0),
(21, 'file (2).png', '', '2025-02-16 20:31:44', NULL, NULL, NULL, 0),
(22, 'file (3).png', '', '2025-02-16 20:31:44', NULL, NULL, NULL, 0),
(23, 'file (5).png', '', '2025-02-16 20:31:44', NULL, NULL, NULL, 0),
(32, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(33, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(34, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', '', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(36, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', '', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(44, '17.jpg', '', '2025-02-19 11:22:46', NULL, NULL, NULL, 0),
(45, '17.jpg', '', '2025-02-19 11:23:32', NULL, NULL, NULL, 0),
(46, '17.jpg', '', '2025-02-19 11:23:51', NULL, NULL, NULL, 0),
(47, '17.jpg', '', '2025-02-19 11:24:06', NULL, NULL, NULL, 0),
(49, '1702392863857.jpg', '', '2025-02-19 11:25:57', NULL, NULL, NULL, 0),
(51, '17.jpg', '', '2025-02-19 11:30:30', NULL, NULL, NULL, 0),
(52, '1.jpg', '', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(53, 'bgimg.jpg', '', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(54, 'bgimgc.jpg', '', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(55, 'Screenshot 2024-02-15 161630.png', '', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(56, 'Screenshot 2024-02-15 162108.png', '', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(57, 'WhatsApp Image 2024-06-02 at 14.05.43_38762eab.jpg', '', '2025-02-19 11:48:30', NULL, NULL, NULL, 0),
(69, '1.jpg', '', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(70, 'bgimg.jpg', '', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(71, 'bgimgc.jpg', '', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(72, 'Screenshot 2024-02-15 161630.png', '', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(73, 'Screenshot 2024-02-15 162108.png', '', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(74, '1000007225-01.jpeg', '', '2025-02-20 14:39:40', NULL, NULL, NULL, 12),
(75, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '', '2025-02-23 11:52:53', NULL, NULL, NULL, 15),
(76, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '', '2025-02-23 11:52:53', NULL, NULL, NULL, 15),
(77, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', '', '2025-02-23 11:52:53', NULL, NULL, NULL, 15),
(79, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', '', '2025-02-23 11:52:53', NULL, NULL, NULL, 15),
(81, '17.jpg', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(82, '20240103_120659.JPG', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(83, '1000007225-01.jpeg', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(86, 'profile-pic.jpg', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(87, 'rgb(56, 1, 57) (1).png', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(90, 'wallpaperflare.com_wallpaper.jpg', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(91, 'WhatsApp Image 2024-06-02 at 14.05.43_38762eab.jpg', '', '2025-02-23 11:53:31', NULL, NULL, NULL, 15),
(108, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565301/sdrive_backup/11/2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '2025-02-26 10:21:38', NULL, NULL, NULL, 11),
(109, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565306/sdrive_backup/11/7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '2025-02-26 10:21:42', NULL, NULL, NULL, 11),
(110, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565310/sdrive_backup/11/843b54d6-ac45-480d-9c95-6edbdec8c329.png', '2025-02-26 10:21:46', NULL, NULL, 'Snehasish', 11),
(111, '11001e19-5761-4d48-821b-9eda36382a55.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565315/sdrive_backup/11/11001e19-5761-4d48-821b-9eda36382a55.png', '2025-02-26 10:21:51', NULL, NULL, NULL, 11),
(112, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565319/sdrive_backup/11/a43d1104-e99c-4944-a7d5-476b96c25161.png', '2025-02-26 10:21:55', NULL, NULL, 'Bubu', 11),
(113, 'c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565324/sdrive_backup/11/c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', '2025-02-26 10:22:00', NULL, NULL, NULL, 11),
(114, '1000007225-01.jpeg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740565352/sdrive_backup/11/1000007225-01.jpg', '2025-02-26 10:22:28', NULL, NULL, NULL, 11),
(115, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680307/sdrive_backup/13/2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '2025-02-27 18:18:21', NULL, NULL, NULL, 13),
(116, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680311/sdrive_backup/13/7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '2025-02-27 18:18:26', NULL, NULL, NULL, 13),
(117, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680317/sdrive_backup/13/843b54d6-ac45-480d-9c95-6edbdec8c329.png', '2025-02-27 18:18:31', NULL, NULL, NULL, 13),
(118, '11001e19-5761-4d48-821b-9eda36382a55.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680321/sdrive_backup/13/11001e19-5761-4d48-821b-9eda36382a55.png', '2025-02-27 18:18:36', NULL, NULL, NULL, 13),
(119, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680326/sdrive_backup/13/a43d1104-e99c-4944-a7d5-476b96c25161.png', '2025-02-27 18:18:40', NULL, NULL, NULL, 13),
(120, 'c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680330/sdrive_backup/13/c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', '2025-02-27 18:18:45', NULL, NULL, NULL, 13),
(121, '1000007225-01.jpeg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680733/sdrive_backup/13/1000007225-01.jpg', '2025-02-27 18:25:27', NULL, NULL, NULL, 13);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `phone` varchar(15) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `login_token` varchar(32) DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `pin_hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `profile_pic`, `created_at`, `phone`, `profile_picture`, `login_token`, `remember_token`, `pin_hash`) VALUES
(11, 'snehasishsarkar439@gmail.com', 'Snehasish', '$2y$10$z8a9G4rys/.OI0xz8MXKD.NgWf.fKmSASu8PlSX7Levw.gTiFcwmm', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740562920/sdrive_backup/profile_pics/seyzjqgxtnhxjgwgnpgg.png', '2025-02-17 14:29:34', '7044122730', NULL, 'cddaad3890985816d5103f1a92981b8f', NULL, '$2y$10$JC8hSyl9Uf.ogUMmpJ2O7un4BfH8.blDd9MR.E8egZ.PSrT4hBuQi'),
(12, 'snehasishsarkar43@gmail.com', 'bubu', '$2y$10$nb7XgnKMD3NA.6GxHy1/J.1hY6cG0z6Nt0xEYmIH2Hl7wl3vmD7mG', '1702392863857.jpg', '2025-02-20 14:03:40', '1234567890', NULL, NULL, NULL, NULL),
(13, 'bubu45@gmail.com', 'BUBU1', '$2y$10$aFfmgaIpqGy7zyp83GoTleyx07aeuFw8tbP1DuXCp44.95Cdo2AgS', '1000007225-01.jpeg', '2025-02-20 16:33:44', '', NULL, '21477691ca45b696ef19453e526b4b88', NULL, NULL),
(14, 'bubu445@gmail.com', 'BUBU2', '$2y$10$k6PBP4uNWzJOPyc67MPYBuGIsSeDnwy.Vnb/Gd3WkMTqyAKladBNe', 'c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', '2025-02-20 16:54:14', '', NULL, NULL, NULL, NULL),
(15, 'sdsdsd22@gmail.com', 'sdsdsd', '$2y$10$42dfXqn1UTAq355U.UhZs.w7lTEqR/uMSrk3SfwXI.36EtSGBrFZG', '11001e19-5761-4d48-821b-9eda36382a55.png', '2025-02-21 15:22:03', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vault_items`
--

DROP TABLE IF EXISTS `vault_items`;
CREATE TABLE IF NOT EXISTS `vault_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `cloud_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `vault_items`
--
ALTER TABLE `vault_items`
  ADD CONSTRAINT `vault_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
