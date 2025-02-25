-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2025 at 06:04 PM
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(14, 12, 'bubu', '1234567890', '', '2025-02-20 15:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
CREATE TABLE IF NOT EXISTS `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `photos`
--

INSERT INTO `photos` (`id`, `filename`, `uploaded_at`, `description`, `tags`, `title`, `user_id`) VALUES
(10, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(11, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(12, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(14, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', '2025-02-16 20:19:32', NULL, NULL, NULL, 0),
(16, '1000007225-01.jpeg', '2025-02-16 20:21:22', NULL, NULL, NULL, 0),
(17, '1702392863857.jpg', '2025-02-16 20:21:29', NULL, NULL, NULL, 0),
(18, 'wallpaperflare.com_wallpaper.jpg', '2025-02-16 20:21:42', NULL, NULL, NULL, 0),
(19, '20240103_120659.JPG', '2025-02-16 20:30:51', NULL, NULL, NULL, 0),
(20, 'rgb(56, 1, 57) (1).png', '2025-02-16 20:31:25', NULL, NULL, NULL, 0),
(21, 'file (2).png', '2025-02-16 20:31:44', NULL, NULL, NULL, 0),
(22, 'file (3).png', '2025-02-16 20:31:44', NULL, NULL, NULL, 0),
(23, 'file (5).png', '2025-02-16 20:31:44', NULL, NULL, NULL, 0),
(32, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(33, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(34, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(36, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', '2025-02-19 10:45:23', NULL, NULL, NULL, 0),
(44, '17.jpg', '2025-02-19 11:22:46', NULL, NULL, NULL, 0),
(45, '17.jpg', '2025-02-19 11:23:32', NULL, NULL, NULL, 0),
(46, '17.jpg', '2025-02-19 11:23:51', NULL, NULL, NULL, 0),
(47, '17.jpg', '2025-02-19 11:24:06', NULL, NULL, NULL, 0),
(49, '1702392863857.jpg', '2025-02-19 11:25:57', NULL, NULL, NULL, 0),
(51, '17.jpg', '2025-02-19 11:30:30', NULL, NULL, NULL, 0),
(52, '1.jpg', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(53, 'bgimg.jpg', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(54, 'bgimgc.jpg', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(55, 'Screenshot 2024-02-15 161630.png', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(56, 'Screenshot 2024-02-15 162108.png', '2025-02-19 11:31:19', NULL, NULL, NULL, 0),
(57, 'WhatsApp Image 2024-06-02 at 14.05.43_38762eab.jpg', '2025-02-19 11:48:30', NULL, NULL, NULL, 0),
(58, '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '2025-02-20 14:10:25', NULL, NULL, NULL, 11),
(59, '7c2a0207-ca67-4a1d-a755-abd3f7ab7d7b.png', '2025-02-20 14:10:25', NULL, NULL, NULL, 11),
(60, '843b54d6-ac45-480d-9c95-6edbdec8c329.png', '2025-02-20 14:10:25', NULL, NULL, NULL, 11),
(61, '11001e19-5761-4d48-821b-9eda36382a55.png', '2025-02-20 14:10:25', NULL, NULL, NULL, 11),
(62, 'a43d1104-e99c-4944-a7d5-476b96c25161.png', '2025-02-20 14:10:25', NULL, NULL, NULL, 11),
(63, 'c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', '2025-02-20 14:10:25', NULL, NULL, NULL, 11),
(64, '1.jpg', '2025-02-20 14:10:49', NULL, NULL, NULL, 11),
(65, 'bgimg.jpg', '2025-02-20 14:10:49', NULL, NULL, NULL, 11),
(66, 'bgimgc.jpg', '2025-02-20 14:10:49', NULL, NULL, NULL, 11),
(67, 'Screenshot 2024-02-15 161630.png', '2025-02-20 14:10:49', NULL, NULL, NULL, 11),
(68, 'Screenshot 2024-02-15 162108.png', '2025-02-20 14:10:49', NULL, NULL, NULL, 11),
(69, '1.jpg', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(70, 'bgimg.jpg', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(71, 'bgimgc.jpg', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(72, 'Screenshot 2024-02-15 161630.png', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(73, 'Screenshot 2024-02-15 162108.png', '2025-02-20 14:11:27', NULL, NULL, NULL, 12),
(74, '1000007225-01.jpeg', '2025-02-20 14:39:40', NULL, NULL, NULL, 12);

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
  `remember_token` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `username`, `password`, `profile_pic`, `created_at`, `remember_token`, `phone`, `profile_picture`) VALUES
(11, 'snehasishsarkar439@gmail.com', 'Snehasish', '$2y$10$dDD3Ko4Uv4Cqtfw5eUeLLePSJhkR6/osAR1.Thhww7lYdHV5ZBOia', '2a0842f7-1a52-4e3c-8b0f-c4be0cef713c.png', '2025-02-17 14:29:34', '$2y$10$e/64Iv.SyUF9BMKqtWp17.yx8PyyP8rTBqJzI8.px5ENpTzHAYm9i', '7044122730', NULL),
(12, 'snehasishsarkar43@gmail.com', 'bubu', '$2y$10$nb7XgnKMD3NA.6GxHy1/J.1hY6cG0z6Nt0xEYmIH2Hl7wl3vmD7mG', '1702392863857.jpg', '2025-02-20 14:03:40', '$2y$10$HVO71gCNsfP0erKGoBysIuMXAA9I/RsyQuR1mpi435jLgoNRz/23S', '1234567890', NULL),
(13, 'bubu45@gmail.com', 'BUBU1', '$2y$10$aFfmgaIpqGy7zyp83GoTleyx07aeuFw8tbP1DuXCp44.95Cdo2AgS', '1000007225-01.jpeg', '2025-02-20 16:33:44', NULL, '', NULL),
(14, 'bubu445@gmail.com', 'BUBU2', '$2y$10$k6PBP4uNWzJOPyc67MPYBuGIsSeDnwy.Vnb/Gd3WkMTqyAKladBNe', 'c1b21a88-e78d-4785-9226-cf8f7e8f9fd7.png', '2025-02-20 16:54:14', NULL, '', NULL);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
