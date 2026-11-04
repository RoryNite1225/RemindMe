-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2026 at 08:19 AM
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
-- Database: `remindme`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `for_faculty` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `for_faculty`) VALUES
(1, 'Class Schedule', 1),
(2, 'Quiz', 1),
(3, 'Class Announcement', 1),
(4, 'Exam', 0);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `code`, `name`) VALUES
(1, 'BSIT', 'BS Information Technology'),
(2, 'BSBA', 'BS Business Administration'),
(3, 'BSHM', 'BS Hospitality Management'),
(4, 'BSCrim', 'BS Criminology'),
(5, 'BSED', 'Bachelor of Secondary Education'),
(6, 'BEED', 'Bachelor of Elementary Education'),
(7, 'CCJE', 'Criminal Justice Education'),
(8, 'MIDWIFERY', 'Midwifery'),
(9, 'BSA', 'BS Accountancy');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_classes`
--

CREATE TABLE `faculty_classes` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `section` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_classes`
--

INSERT INTO `faculty_classes` (`id`, `faculty_id`, `course_id`, `section`) VALUES
(3, 8, 1, '2C');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `author_id` int(11) NOT NULL,
  `status` enum('active','archived') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `body`, `category_id`, `course_id`, `section`, `author_id`, `status`, `created_at`, `expires_at`) VALUES
(1, 'Test', 'My name is John Smith. I\'m 19 years old. My trailer is in the northeast section of Urbana-Champaign where all the other trailers are, and I am not married. \r\nLast summer I worked as an employee for the Illinois River Biological Station, and I got home every day by 8 PM at the latest. I don\'t smoke, nor do I occasionally drink. I\'m in bed by 11 PM, and make sure to get 7 hours of sleep, no matter what. After having a cold glass of Mountain Dew and doing about twenty minutes of stretches before going to bed, I usually have no problems sleeping until morning. Just like a baby, I wake up without any fatigue or stress in the morning. \r\nI was told there were no issues at my last check-up. I\'m trying to explain that I\'m a person who wishes to live a very quiet life. \r\nI take care not to trouble myself with any enemies, like wining and losing, that would cause me to lose sleep at night. This is what brings me happiness along with fishing. Although, if I were to fight, I wouldn\'t lose to anyone.', 3, NULL, NULL, 1, 'active', '2026-10-15 06:15:02', NULL),
(2, 'Welcome message', 'Hello everybody my name is Welcome\r\n\r\nWhat??\r\n\r\nHello everybody my name is Markiplier and I AM A FISH\r\n\r\nYou\'re so Portuguese\r\n\r\nWHO\'S COMING?\r\n\r\nIF YOU\'RE COMING RIGHT NOW, LET ME SEE IT!\r\n\r\nSHOW ME!\r\n\r\nHE? HE WHO IS COMING?!', 1, 1, '2C', 8, 'active', '2026-10-15 06:17:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `post_reads`
--

CREATE TABLE `post_reads` (
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','faculty','student') NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `course_id`, `section`, `created_at`) VALUES
(1, 'Admin User', 'admin@remindme.test', '$2y$10$etDg9vXETxq3Mz4WpyIyCOre8rIyn8kln5c2ZRtA.JxLU49T2WDly', 'admin', NULL, NULL, '2026-10-15 06:10:21'),
(7, 'Mark E Plier', 'ohhimark@gmail.com', '$2y$10$vQn/orNzxOmseqj4bCilvuLepzl1OMYN/2saBV1zDAanPri0682SK', 'student', 1, '2C', '2026-10-15 06:12:26'),
(8, 'Gerson Boom', 'imold@gmail.com', '$2y$10$PWUOMDfE.tGe9mHNvE/M/e9Y2w1Tqna8B06YARRG7FC0/ZNxewLAq', 'faculty', NULL, NULL, '2026-10-15 06:12:57'),
(9, 'Leon Scott Kennedy', 'residentevil@gmail.com', '$2y$10$7TYmgppfpQI6lHePM7P3ze5mxjgs079WpW/pcrFpUrCJIhmfi9AP.', 'student', 3, '1A', '2026-10-15 06:19:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `faculty_classes`
--
ALTER TABLE `faculty_classes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_class` (`faculty_id`,`course_id`,`section`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `post_reads`
--
ALTER TABLE `post_reads`
  ADD PRIMARY KEY (`user_id`,`post_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `faculty_classes`
--
ALTER TABLE `faculty_classes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `faculty_classes`
--
ALTER TABLE `faculty_classes`
  ADD CONSTRAINT `faculty_classes_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_classes_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `post_reads`
--
ALTER TABLE `post_reads`
  ADD CONSTRAINT `post_reads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_reads_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
