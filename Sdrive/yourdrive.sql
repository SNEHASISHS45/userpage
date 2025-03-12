-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 12, 2025 at 10:02 PM
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
-- Creation: Feb 22, 2025 at 11:33 AM
--

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
-- Creation: Feb 20, 2025 at 03:25 PM
--

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `group_name` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--
-- Creation: Mar 09, 2025 at 02:17 PM
--

CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `version` int(11) DEFAULT 1,
  `user_id` int(11) NOT NULL DEFAULT 1,
  `tags` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `url`, `uploaded_at`, `version`, `user_id`, `tags`) VALUES
(6, 'Snehasish sarkar cv', 'https://res.cloudinary.com/dzn369qpk/raw/upload/v1741529881/uqqbhglspr6zlf5enc9p.tmp', '2025-03-09 14:18:01', 1, 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--
-- Creation: Feb 22, 2025 at 11:33 AM
--

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
-- Creation: Feb 24, 2025 at 06:36 PM
--

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
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(121, '1000007225-01.jpeg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740680733/sdrive_backup/13/1000007225-01.jpg', '2025-02-27 18:25:27', NULL, NULL, NULL, 13),
(122, '17.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362653/sdrive_backup/11/17.jpg', '2025-03-07 15:50:53', NULL, NULL, '354', 11),
(123, '1702392863857.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362656/sdrive_backup/11/1702392863857.jpg', '2025-03-07 15:50:55', NULL, NULL, NULL, 11),
(124, '1728861863604-Photoroom.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362658/sdrive_backup/11/1728861863604-Photoroom.png', '2025-03-07 15:50:57', NULL, NULL, NULL, 11),
(125, 'bg.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362660/sdrive_backup/11/bg.jpg', '2025-03-07 15:50:59', NULL, NULL, NULL, 11),
(126, 'bg.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362666/sdrive_backup/11/bg.png', '2025-03-07 15:51:06', NULL, NULL, NULL, 11),
(127, 'file (2).png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362669/sdrive_backup/11/file%20%282%29.png', '2025-03-07 15:51:08', NULL, NULL, '22', 11),
(128, 'file (3).png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362672/sdrive_backup/11/file%20%283%29.png', '2025-03-07 15:51:11', NULL, 'Durga ma', NULL, 11),
(129, 'file (5).png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362675/sdrive_backup/11/file%20%285%29.png', '2025-03-07 15:51:14', NULL, 'Saraswati ma', NULL, 11),
(130, 'profile-pic.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362680/sdrive_backup/11/profile-pic.jpg', '2025-03-07 15:51:20', NULL, NULL, NULL, 11),
(131, 'splash.bmp.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362682/sdrive_backup/11/splash.bmp.jpg', '2025-03-07 15:51:22', NULL, NULL, NULL, 11),
(132, 'wallpaperflare.com_wallpaper.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741362689/sdrive_backup/11/wallpaperflare.com_wallpaper.jpg', '2025-03-07 15:51:29', NULL, NULL, NULL, 11),
(133, 'bgimgc.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741719872/sdrive_backup/11/bgimgc.jpg', '2025-03-11 19:04:31', NULL, NULL, NULL, 11),
(134, 'Screenshot 2024-09-10 160419.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741784196/sdrive_backup/11/Screenshot%202024-09-10%20160419.png', '2025-03-12 12:56:34', NULL, NULL, NULL, 11),
(135, 'IMG_20240214_172455.jpeg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741786890/sdrive_backup/11/IMG_20240214_172455.jpg', '2025-03-12 13:41:29', NULL, NULL, NULL, 11),
(137, 'pngwing.com.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741787121/sdrive_backup/11/pngwing.com.png', '2025-03-12 13:45:19', NULL, NULL, NULL, 11),
(138, 'WhatsApp Image 2024-06-10 at 23.33.06_8dda49d5.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741787597/sdrive_backup/11/WhatsApp%20Image%202024-06-10%20at%2023.33.06_8dda49d5.jpg', '2025-03-12 13:53:16', NULL, NULL, NULL, 11),
(139, '1707415191060.jpg', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741787673/sdrive_backup/11/1707415191060.jpg', '2025-03-12 13:54:31', NULL, NULL, NULL, 11),
(140, 'old vibes.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741787760/sdrive_backup/11/old%20vibes.png', '2025-03-12 13:55:59', NULL, NULL, NULL, 11),
(141, 'Snehasish sarkar cv.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741787843/sdrive_backup/11/Snehasish%20sarkar%20cv.png', '2025-03-12 13:57:21', NULL, NULL, NULL, 11),
(142, 'Screenshot 2024-02-15 161630.png', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741787865/sdrive_backup/11/Screenshot%202024-02-15%20161630.png', '2025-03-12 13:57:44', NULL, NULL, NULL, 11);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Creation: Feb 22, 2025 at 12:04 PM
--

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
(11, 'snehasishsarkar439@gmail.com', 'Snehasish', '$2y$10$z8a9G4rys/.OI0xz8MXKD.NgWf.fKmSASu8PlSX7Levw.gTiFcwmm', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1740562920/sdrive_backup/profile_pics/seyzjqgxtnhxjgwgnpgg.png', '2025-02-17 14:29:34', '7044122730', NULL, 'ba061bcf36b1853442179d2e15b50ec8', NULL, '$2y$10$JC8hSyl9Uf.ogUMmpJ2O7un4BfH8.blDd9MR.E8egZ.PSrT4hBuQi'),
(12, 'snehasishsarkar43@gmail.com', 'bubu', '$2y$10$tiHfYcVDrfVEHzt2dn5gHu2DhMUmFGB0p3jnYjJcLkOFZU1/XZayu', 'https://res.cloudinary.com/dzn369qpk/image/upload/v1741807898/sdrive_backup/profile_pics/kipvmvjadwdi76x3qcwq.jpg', '2025-02-20 14:03:40', '1234567890', NULL, '42a5fc011e672099817e8640539747a1', NULL, NULL),
(13, 'bubu45@gmail.com', 'BUBU1', '$2y$10$aFfmgaIpqGy7zyp83GoTleyx07aeuFw8tbP1DuXCp44.95Cdo2AgS', '1000007225-01.jpeg', '2025-02-20 16:33:44', '', NULL, '21477691ca45b696ef19453e526b4b88', NULL, NULL),
(14, 'bubu445@gmail.com', 'BUBU2', '$2y$10$k6PBP4uNWzJOPyc67MPYBuGIsSeDnwy.Vnb/Gd3WkMTqyAKladBNe', 'c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', '2025-02-20 16:54:14', '', NULL, NULL, NULL, NULL),
(15, 'sdsdsd22@gmail.com', 'sdsdsd', '$2y$10$42dfXqn1UTAq355U.UhZs.w7lTEqR/uMSrk3SfwXI.36EtSGBrFZG', '11001e19-5761-4d48-821b-9eda36382a55.png', '2025-02-21 15:22:03', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vault_items`
--
-- Creation: Feb 26, 2025 at 10:11 AM
--

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
