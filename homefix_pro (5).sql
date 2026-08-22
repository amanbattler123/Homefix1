-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 09, 2025 at 08:28 PM
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
-- Database: `homefix_pro`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_conversations`
--

CREATE TABLE `chat_conversations` (
  `id` int(11) NOT NULL,
  `homeowner_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `service_request_id` int(11) DEFAULT NULL,
  `last_message` text DEFAULT NULL,
  `last_message_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `service_request_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `conversation_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`id`, `sender_id`, `receiver_id`, `service_request_id`, `message`, `is_read`, `created_at`, `conversation_id`) VALUES
(1, 5, 3, NULL, 'hey', 1, '2025-10-20 16:42:33', NULL),
(2, 3, 5, NULL, 'heyy', 1, '2025-10-20 18:33:57', NULL),
(5, 5, 3, NULL, 'vv', 0, '2025-11-03 18:41:29', NULL),
(6, 5, 7, NULL, 'heyy nati', 1, '2025-11-09 16:46:29', NULL),
(7, 7, 5, NULL, 'whats up', 0, '2025-11-09 16:48:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) DEFAULT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `homeowner_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `positive_points` text DEFAULT NULL,
  `improvement_suggestions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(100) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `action_url` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `is_read`, `related_id`, `created_at`, `action_url`) VALUES
(1, 1, 'New Service Request', 'New service request: hey', 'service_request', 0, 1, '2025-10-20 16:06:45', NULL),
(2, 1, 'New Service Request', 'New service request: hey', 'service_request', 0, 2, '2025-10-20 16:06:46', NULL),
(3, 1, 'New Service Request', 'New service request: hey', 'service_request', 0, 3, '2025-10-20 16:06:46', NULL),
(4, 1, 'New Service Request', 'New service request: www', 'service_request', 0, 4, '2025-10-20 16:28:34', NULL),
(5, 3, 'New Message', 'You have a new message', 'message', 0, 1, '2025-10-20 16:42:33', NULL),
(6, 5, 'Service Request Updated', 'Your service request \'www\' has been approved', 'service_request', 0, 4, '2025-10-20 17:40:20', NULL),
(8, 5, 'Service Request Updated', 'Your service request \'www\' has been approved', 'service_request', 0, 4, '2025-10-20 17:40:25', NULL),
(9, 5, 'Service Request Updated', 'Your service request \'www\' has been approved', 'service_request', 0, 4, '2025-10-20 17:40:29', NULL),
(15, 5, 'New Message', 'You have a new message', 'message', 0, 2, '2025-10-20 18:33:57', NULL),
(16, 1, 'New Service Request', 'New service request: wwq', 'service_request', 0, 5, '2025-10-20 18:36:18', NULL),
(17, 5, 'Service Request Updated', 'Your service request \'wwq\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 5, '2025-10-20 18:37:08', NULL),
(18, 3, 'New Message', 'You have a new message', 'message', 0, 3, '2025-10-26 18:27:05', NULL),
(19, 1, 'New Service Request', 'New service request: test1', 'service_request', 0, 6, '2025-10-26 18:55:47', NULL),
(21, 1, 'New Service Request', 'New service request: test2', 'service_request', 0, 7, '2025-10-26 19:26:03', NULL),
(22, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-26 19:26:26', NULL),
(23, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-26 19:36:09', NULL),
(24, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-26 19:49:03', NULL),
(25, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-26 20:06:40', NULL),
(26, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-27 14:18:03', NULL),
(27, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-27 14:33:35', NULL),
(28, 5, 'Service Request Updated', 'Your service request \'test2\' has been rejected', 'service_request', 0, 7, '2025-10-27 14:33:40', NULL),
(29, 5, 'Service Request Updated', 'Your service request \'test2\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 7, '2025-10-27 14:33:43', NULL),
(30, 1, 'New Service Request', 'New service request: clean', 'service_request', 0, 8, '2025-10-27 14:38:20', NULL),
(31, 5, 'Service Request Updated', 'Your service request \'clean\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 8, '2025-10-27 14:50:42', NULL),
(32, 1, 'New Service Request', 'New service request: qqq', 'service_request', 0, 9, '2025-10-27 17:46:22', NULL),
(33, 5, 'Service Request Updated', 'Your service request \'qqq\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 9, '2025-10-27 17:47:18', NULL),
(35, 1, 'New Service Request', 'New service request: rrr', 'service_request', 0, 10, '2025-10-28 05:55:45', NULL),
(36, 5, 'Service Request Updated', 'Your service request \'rrr\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 10, '2025-10-28 06:08:37', NULL),
(37, 5, 'Service Request Updated', 'Your service request \'qqq\' has been rejected', 'service_request', 0, 9, '2025-10-28 06:41:48', NULL),
(38, 5, 'Service Request Updated', 'Your service request \'qqq\' has been rejected', 'service_request', 0, 9, '2025-10-28 07:12:48', NULL),
(39, 5, 'Service Request Updated', 'Your service request \'qqq\' has been rejected', 'service_request', 0, 9, '2025-10-28 07:15:35', NULL),
(40, 5, 'Service Request Updated', 'Your service request \'qqq\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 9, '2025-10-28 07:15:39', NULL),
(41, 1, 'New Service Request', 'New service request: test1', 'service_request', 0, 11, '2025-10-28 17:28:53', NULL),
(42, 5, 'Technician Assigned', 'A technician has been assigned to your service request: test final . Waiting for technician acceptance.', 'service_request', 0, 12, '2025-10-28 17:48:34', NULL),
(43, 3, 'New Task Assignment', 'You have been assigned to service request: test final . Please accept or reject this assignment.', 'task_assignment', 0, 12, '2025-10-28 17:48:34', NULL),
(44, 1, 'New Service Request', 'New service request: test final ', 'service_request', 0, 12, '2025-10-28 17:48:34', NULL),
(45, 5, 'Task Accepted', 'Technician has accepted your service request: test final . Work will begin soon.', 'task_accepted', 0, 12, '2025-10-28 17:49:00', NULL),
(46, 5, 'Service Request Updated', 'Your service request \'test final \' has been in_progress', 'service_request', 0, 12, '2025-10-28 17:50:13', NULL),
(47, 5, 'Service Request Updated', 'Your service request \'test final \' has been completed', 'service_request', 0, 12, '2025-10-28 17:50:22', NULL),
(48, 5, 'Technician Assigned', 'A technician has been assigned to your service request: test 2. Waiting for technician acceptance.', 'service_request', 0, 13, '2025-10-28 17:56:15', NULL),
(49, 3, 'New Task Assignment', 'You have been assigned to service request: test 2. Please accept or reject this assignment.', 'task_assignment', 0, 13, '2025-10-28 17:56:15', NULL),
(50, 1, 'New Service Request', 'New service request: test 2', 'service_request', 0, 13, '2025-10-28 17:56:15', NULL),
(51, 5, 'Task Accepted', 'Technician has accepted your service request: test 2. Work will begin soon.', 'task_accepted', 0, 13, '2025-10-28 17:56:54', NULL),
(52, 5, 'Price Estimate Received', 'Technician has submitted a price estimate of $1,000.00 for your service request: \'test 2\'.', 'price_estimate', 0, 13, '2025-10-28 18:42:57', NULL),
(53, 1, 'Price Estimate Submitted', 'Technician has submitted a price estimate of $1,000.00 for service request: \'test 2\'.', 'price_estimate_admin', 0, 13, '2025-10-28 18:42:57', NULL),
(54, 5, 'Technician Assigned', 'A technician has been assigned to your service request: uuu. Waiting for technician acceptance.', 'service_request', 0, 14, '2025-10-28 19:02:34', NULL),
(55, 3, 'New Task Assignment', 'You have been assigned to service request: uuu. Please accept or reject this assignment.', 'task_assignment', 0, 14, '2025-10-28 19:02:35', NULL),
(56, 1, 'New Service Request', 'New service request: uuu', 'service_request', 0, 14, '2025-10-28 19:02:35', NULL),
(57, 5, 'Task Accepted', 'Technician has accepted your service request: uuu. Work will begin soon.', 'task_accepted', 0, 14, '2025-10-28 19:03:15', NULL),
(58, 5, 'Price Estimate Received', 'Technician has submitted a price estimate of $1,000.00 for your service request: \'uuu\'.', 'price_estimate', 0, 14, '2025-10-28 19:03:46', NULL),
(59, 1, 'Price Estimate Submitted', 'Technician has submitted a price estimate of $1,000.00 for service request: \'uuu\'.', 'price_estimate_admin', 0, 14, '2025-10-28 19:03:46', NULL),
(60, 5, 'Technician Assigned', 'A technician has been assigned to your service request: qqq. Waiting for technician acceptance.', 'service_request', 0, 15, '2025-10-28 19:18:53', NULL),
(61, 3, 'New Task Assignment', 'You have been assigned to service request: qqq. Please accept or reject this assignment.', 'task_assignment', 0, 15, '2025-10-28 19:18:53', NULL),
(62, 1, 'New Service Request', 'New service request: qqq', 'service_request', 0, 15, '2025-10-28 19:18:54', NULL),
(63, 5, 'Service Request Updated', 'Your service request \'qqq\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 15, '2025-10-28 19:19:17', NULL),
(64, 5, 'Technician Assigned', 'A technician has been assigned to your service request: bbb. Waiting for technician acceptance.', 'service_request', 0, 16, '2025-10-28 19:20:47', NULL),
(65, 3, 'New Task Assignment', 'You have been assigned to service request: bbb. Please accept or reject this assignment.', 'task_assignment', 0, 16, '2025-10-28 19:20:47', NULL),
(66, 1, 'New Service Request', 'New service request: bbb', 'service_request', 0, 16, '2025-10-28 19:20:47', NULL),
(67, 5, 'Task Accepted', 'Technician has accepted your service request: bbb. Work will begin soon.', 'task_accepted', 0, 16, '2025-10-28 19:21:15', NULL),
(68, 5, 'Price Estimate Received', 'Technician has submitted a price estimate of $1,000.00 for your service request: \'bbb\'.', 'price_estimate', 0, 16, '2025-10-28 19:21:44', NULL),
(69, 1, 'Price Estimate Submitted', 'Technician has submitted a price estimate of $1,000.00 for service request: \'bbb\'.', 'price_estimate_admin', 0, 16, '2025-10-28 19:21:44', NULL),
(70, 5, 'Technician Assigned', 'A technician has been assigned to your service request: ttt. Waiting for technician acceptance.', 'service_request', 0, 17, '2025-10-28 19:33:00', NULL),
(71, 3, 'New Task Assignment', 'You have been assigned to service request: ttt. Please accept or reject this assignment.', 'task_assignment', 0, 17, '2025-10-28 19:33:00', NULL),
(72, 1, 'New Service Request', 'New service request: ttt', 'service_request', 0, 17, '2025-10-28 19:33:00', NULL),
(73, 5, 'Task Accepted', 'Technician has accepted your service request: ttt. Work will begin soon.', 'task_accepted', 0, 17, '2025-10-28 19:33:27', NULL),
(74, 5, 'Price Estimate Received', 'Technician has submitted a price estimate of $1,000.00 for your service request: \'ttt\'. Please review and accept or reject the estimate.', 'price_estimate', 0, 17, '2025-10-28 19:55:24', NULL),
(75, 5, 'Service Request Updated', 'Your service request \'bbb\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 16, '2025-10-29 09:17:22', NULL),
(76, 5, 'Technician Assigned', 'A technician has been assigned to your service request: this is final . Waiting for technician acceptance.', 'service_request', 0, 18, '2025-10-29 10:49:18', NULL),
(77, 3, 'New Task Assignment', 'You have been assigned to service request: this is final . Please accept or reject this assignment.', 'task_assignment', 0, 18, '2025-10-29 10:49:19', NULL),
(78, 1, 'New Service Request', 'New service request: this is final ', 'service_request', 0, 18, '2025-10-29 10:49:19', NULL),
(79, 5, 'Task Accepted', 'Technician has accepted your service request: this is final . Work will begin soon.', 'task_accepted', 0, 18, '2025-10-29 10:51:16', NULL),
(80, 5, 'Price Estimate Received', 'Technician has submitted a price estimate of $1,000.00 for your service request: \'this is final \'. Please review and accept or reject the estimate.', 'price_estimate', 0, 18, '2025-10-29 10:51:58', NULL),
(81, 5, 'Technician Assigned', 'A technician has been assigned to your service request: test5. Waiting for technician acceptance.', 'service_request', 0, 19, '2025-10-29 11:19:11', NULL),
(82, 3, 'New Task Assignment', 'You have been assigned to service request: test5. Please accept or reject this assignment.', 'task_assignment', 0, 19, '2025-10-29 11:19:11', NULL),
(83, 1, 'New Service Request', 'New service request: test5', 'service_request', 0, 19, '2025-10-29 11:19:11', NULL),
(84, 5, 'Task Accepted', 'Technician has accepted your service request: test5. Work will begin soon.', 'task_accepted', 0, 19, '2025-10-29 11:19:38', NULL),
(85, 5, 'Price Estimate Received', 'Technician has submitted a price estimate of $100.00 for your service request: \'test5\'. Please review and accept or reject the estimate.', 'price_estimate', 0, 19, '2025-10-29 11:21:13', NULL),
(86, 5, 'Technician Assigned', 'A technician has been assigned to your service request: final6. Waiting for technician acceptance.', 'service_request', 0, 20, '2025-10-29 12:38:45', NULL),
(87, 3, 'New Task Assignment', 'You have been assigned to service request: final6. Please accept or reject this assignment.', 'task_assignment', 0, 20, '2025-10-29 12:38:46', NULL),
(88, 1, 'New Service Request', 'New service request: final6', 'service_request', 0, 20, '2025-10-29 12:38:46', NULL),
(89, 5, 'Task Accepted', 'Technician has accepted your service request: final6. Work will begin soon.', 'task_accepted', 0, 20, '2025-10-29 12:39:49', NULL),
(90, 5, 'Price Estimate Received', 'Technician has submitted inspection results with price estimate of $1,000.00 for your service request: \'final6\'. Please review and accept or reject.', 'price_estimate', 0, 20, '2025-10-29 12:41:00', NULL),
(91, 1, 'Inspection Submitted', 'Technician has submitted inspection for request: final6', 'inspection_submitted', 0, 20, '2025-10-29 12:41:00', NULL),
(92, 3, 'Price Accepted - Start Work', 'Homeowner has accepted your price estimate of $1,000.00. You can now start the work.', 'price_accepted', 0, 20, '2025-10-29 19:25:47', NULL),
(93, 1, 'Price Accepted', 'Homeowner has accepted price estimate for request: final6', 'price_accepted_admin', 0, 20, '2025-10-29 19:25:47', NULL),
(94, 3, 'New Message', 'You have a new message', 'message', 0, 5, '2025-11-03 18:41:29', NULL),
(95, 1, 'New Service Request', 'New service request: aaa', 'service_request', 0, 21, '2025-11-09 16:21:36', NULL),
(96, 5, 'Service Request Updated', 'Your service request \'aaa\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 21, '2025-11-09 16:22:16', NULL),
(97, 1, 'New Service Request', 'New service request: qqqq', 'service_request', 0, 22, '2025-11-09 16:46:15', NULL),
(98, 7, 'New Message', 'You have a new message', 'message', 0, 6, '2025-11-09 16:46:29', NULL),
(99, 5, 'Service Request Updated', 'Your service request \'qqqq\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 22, '2025-11-09 16:47:18', NULL),
(100, 5, 'Service Request Updated', 'Your service request \'qqqq\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 22, '2025-11-09 16:47:22', NULL),
(101, 5, 'New Message', 'You have a new message', 'message', 0, 7, '2025-11-09 16:48:17', NULL),
(102, 1, 'New Service Request', 'New service request: tt', 'service_request', 0, 23, '2025-11-09 16:53:51', NULL),
(103, 5, 'Service Request Updated', 'Your service request \'tt\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 23, '2025-11-09 16:54:30', NULL),
(104, 5, 'Service Request Updated', 'Your service request \'tt\' has been approved by admin. We are now assigning a technician.', 'service_request', 0, 23, '2025-11-09 17:11:23', NULL),
(105, 5, 'Service Request Updated', 'Your service request \'tt\' has been approved', 'service_request', 0, 23, '2025-11-09 18:33:47', NULL),
(106, 5, 'Service Request Updated', 'Your service request \'tt\' has been rejected', 'service_request', 0, 23, '2025-11-09 18:33:49', NULL),
(107, 5, 'Service Request Updated', 'Your service request \'tt\' has been approved', 'service_request', 0, 23, '2025-11-09 18:33:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `homeowner_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','tele_birr') NOT NULL,
  `payment_status` enum('pending','paid','verified','rejected') DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('telebirr','cbe','bank_transfer','cash') NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','completed','verified','rejected') DEFAULT 'pending',
  `paid_at` datetime DEFAULT NULL,
  `verified_by_admin` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `homeowner_id` int(11) NOT NULL,
  `rating` decimal(2,1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `technician_id`, `homeowner_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 5, 4.0, 'very excellent ', '2025-11-03 16:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `homeowner_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `price_range` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `image`, `price_range`, `created_at`) VALUES
(1, 'Plumbing', 'Fix leaks, install fixtures, drain cleaning, pipe repair', 'plumbing.jpg', '150-800 ETB', '2025-10-20 11:31:33'),
(2, 'Electrical', 'Wiring, outlets, lighting installation, electrical repairs', 'electrical.jpg', '200-1200 ETB', '2025-10-20 11:31:33'),
(3, 'HVAC', 'Heating, ventilation, air conditioning installation and repair', 'hvac.jpg', '500-3000 ETB', '2025-10-20 11:31:33'),
(4, 'Carpentry', 'Furniture making, cabinets, trim work, wood repairs', 'carpentry.jpg', '300-2000 ETB', '2025-10-20 11:31:33'),
(5, 'Painting', 'Interior and exterior painting, wall finishing', 'painting.jpg', '800-5000 ETB', '2025-10-20 11:31:33'),
(6, 'Cleaning', 'Deep cleaning, move in/out cleaning, office cleaning', 'cleaning.jpg', '400-2500 ETB', '2025-10-20 11:31:33'),
(7, 'Landscaping', 'Lawn care, gardening, hardscaping, irrigation', 'landscaping.jpg', '600-3500 ETB', '2025-10-20 11:31:33'),
(8, 'Appliance Repair', 'Fix refrigerators, washers, ovens, microwaves', 'appliance.jpg', '250-1500 ETB', '2025-10-20 11:31:33'),
(9, 'Roofing', 'Roof repair, replacement, maintenance, waterproofing', 'roofing.jpg', '1000-8000 ETB', '2025-10-20 11:31:33'),
(10, 'Handyman', 'Multiple small repair tasks, assembly, maintenance', 'handyman.jpg', '200-1200 ETB', '2025-10-20 11:31:33'),
(11, 'Plumbing', 'Pipe repairs, faucet installation, drain cleaning', NULL, NULL, '2025-10-27 17:29:33'),
(12, 'Electrical', 'Wiring, lighting installation, electrical repairs', NULL, NULL, '2025-10-27 17:29:33'),
(13, 'HVAC', 'Heating, ventilation, air conditioning services', NULL, NULL, '2025-10-27 17:29:33'),
(14, 'Carpentry', 'Furniture repair, woodwork, installation', NULL, NULL, '2025-10-27 17:29:33'),
(15, 'Painting', 'Wall painting, decorating, surface preparation', NULL, NULL, '2025-10-27 17:29:33'),
(16, 'Cleaning', 'House cleaning, deep cleaning, maintenance', NULL, NULL, '2025-10-27 17:29:33'),
(17, 'Landscaping', 'Gardening, lawn care, landscape design', NULL, NULL, '2025-10-27 17:29:33'),
(18, 'Appliance Repair', 'Home appliance repair and maintenance', NULL, NULL, '2025-10-27 17:29:33'),
(19, 'Roofing', 'Roof repair, maintenance, installation', NULL, NULL, '2025-10-27 17:29:33'),
(20, 'Handyman', 'General repair and multiple services', NULL, NULL, '2025-10-27 17:29:33'),
(21, 'Plumbing', 'Fix leaks, install fixtures, drain cleaning', NULL, '$50-$150', '2025-10-28 07:12:23'),
(22, 'Electrical', 'Wiring, outlets, lighting installation', NULL, '$75-$200', '2025-10-28 07:12:23'),
(23, 'HVAC', 'Heating, ventilation, air conditioning', NULL, '$100-$500', '2025-10-28 07:12:23'),
(24, 'Painting', 'Interior and exterior painting', NULL, '$200-$800', '2025-10-28 07:12:23'),
(25, 'Cleaning', 'Deep cleaning, move in/out cleaning', NULL, '$100-$300', '2025-10-28 07:12:23'),
(26, 'Landscaping', 'Lawn care, gardening, hardscaping', NULL, '$80-$250', '2025-10-28 07:12:23'),
(27, 'Appliance Repair', 'Fix refrigerators, washers, ovens', NULL, '$75-$200', '2025-10-28 07:12:23'),
(28, 'Roofing', 'Repair, replacement, maintenance', NULL, '$300-$1000', '2025-10-28 07:12:23'),
(29, 'Handyman', 'Multiple small repair tasks', NULL, '$60-$100', '2025-10-28 07:12:23');

-- --------------------------------------------------------

--
-- Table structure for table `service_feedback`
--

CREATE TABLE `service_feedback` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `homeowner_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_inspections`
--

CREATE TABLE `service_inspections` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `inspection_date` datetime DEFAULT NULL,
  `findings` text DEFAULT NULL,
  `recommended_work` text DEFAULT NULL,
  `estimated_hours` int(11) DEFAULT NULL,
  `materials_cost` decimal(10,2) DEFAULT NULL,
  `labor_cost` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `inspection_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_payments`
--

CREATE TABLE `service_payments` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `payment_date` datetime DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `homeowner_id` int(11) NOT NULL,
  `technician_id` int(11) DEFAULT NULL,
  `service_type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `address` text NOT NULL,
  `subcity` varchar(100) NOT NULL,
  `woreda` varchar(100) NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `preferred_time` time DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','waiting_acceptance','assigned','waiting_inspection','price_proposed','price_accepted','in_progress','completed','payment_requested','paid','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `admin_approved` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `inspection_submitted_at` datetime DEFAULT NULL,
  `price_accepted_at` datetime DEFAULT NULL,
  `work_started_at` datetime DEFAULT NULL,
  `work_completed_at` datetime DEFAULT NULL,
  `payment_requested_at` datetime DEFAULT NULL,
  `payment_received_at` datetime DEFAULT NULL,
  `inspection_notes` text DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `inspection_images` text DEFAULT NULL,
  `homeowner_images` text DEFAULT NULL,
  `inspection_scheduled_at` datetime DEFAULT NULL,
  `inspection_completed_at` datetime DEFAULT NULL,
  `price_rejected_at` datetime DEFAULT NULL,
  `price_rejection_reason` text DEFAULT NULL,
  `payment_due_date` date DEFAULT NULL,
  `payment_receipt_file` varchar(255) DEFAULT NULL,
  `payment_verified_at` datetime DEFAULT NULL,
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `homeowner_id`, `technician_id`, `service_type`, `title`, `description`, `address`, `subcity`, `woreda`, `preferred_date`, `preferred_time`, `budget`, `status`, `admin_approved`, `created_at`, `updated_at`, `inspection_submitted_at`, `price_accepted_at`, `work_started_at`, `work_completed_at`, `payment_requested_at`, `payment_received_at`, `inspection_notes`, `estimated_cost`, `inspection_images`, `homeowner_images`, `inspection_scheduled_at`, `inspection_completed_at`, `price_rejected_at`, `price_rejection_reason`, `payment_due_date`, `payment_receipt_file`, `payment_verified_at`, `admin_notes`) VALUES
(4, 5, NULL, 'Plumbing', 'www', 'www', 'addis', 'aaa', '5', '2025-10-21', '00:33:00', 500.00, 'approved', 0, '2025-10-20 16:28:34', '2025-10-20 17:40:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 5, 3, 'Plumbing', 'wwq', 'uytrewq', 'addis', 'Yeka', '2', '2025-10-21', '02:41:00', NULL, 'approved', 0, '2025-10-20 18:36:17', '2025-10-20 18:37:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 5, 3, 'Plumbing', 'test2', 'www', 'addis', 'addis', '1', '2025-10-28', '04:44:00', NULL, 'approved', 0, '2025-10-26 19:26:03', '2025-10-27 14:33:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 5, 3, 'Plumbing', 'test1', 'aaa', 'ad;z;cx', 'addis', '2', '2025-10-29', '21:29:00', NULL, '', 0, '2025-10-28 17:28:53', '2025-10-28 17:28:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 5, 3, 'Plumbing', 'test final ', 'final', 'addis', 'Yeka', '2', '2025-10-31', '22:50:00', NULL, 'completed', 0, '2025-10-28 17:48:33', '2025-10-28 17:50:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 5, 3, 'Plumbing', 'test 2', 'aaaa', 'aaaa', 'aaa', 'aaaa', '2025-10-30', '23:58:00', NULL, 'waiting_acceptance', 0, '2025-10-28 17:56:14', '2025-11-09 18:32:06', NULL, NULL, NULL, NULL, NULL, NULL, 'qqq', 1000.00, '[\"inspections\\/1761676975_69010eaf64f4e_photo5.jpg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 5, 3, 'Plumbing', 'uuu', 'uuu\r\n', 'uuu', 'addis', '111', '2025-10-31', '00:04:00', NULL, 'waiting_acceptance', 0, '2025-10-28 19:02:34', '2025-11-09 18:32:06', NULL, NULL, NULL, NULL, NULL, NULL, 'www', 1000.00, '[\"inspections\\/1761678225_69011391dc2d6_photo6.jpg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 5, 3, 'Plumbing', 'qqq', 'qqq', 'qqq', 'qwwe', '12e', '2025-10-31', '00:20:00', NULL, 'approved', 1, '2025-10-28 19:18:51', '2025-10-28 19:19:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 5, 3, 'Plumbing', 'bbb', 'bbb', 'bbb', 'bbb', 'bbb', '2025-10-29', '22:22:00', NULL, 'waiting_acceptance', 1, '2025-10-28 19:20:46', '2025-11-09 18:32:06', NULL, NULL, NULL, NULL, NULL, NULL, 'ppp', 1000.00, '[\"inspections\\/1761679302_690117c654269_aau logo3.png\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 5, 3, 'Plumbing', 'ttt', 'ttt', 'ttt', 'ttt', 'ttt', '2025-10-30', '03:37:00', NULL, 'waiting_acceptance', 0, '2025-10-28 19:33:00', '2025-11-09 18:32:06', NULL, NULL, NULL, NULL, NULL, NULL, 'qqq', 1000.00, '[\"inspections\\/1761681321_69011fa981707_photo6.jpg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 5, 3, 'Plumbing', 'this is final ', 'final ', 'final', 'final', 'final', '2025-10-30', '16:47:00', NULL, 'waiting_acceptance', 0, '2025-10-29 10:49:13', '2025-11-09 18:32:06', NULL, NULL, NULL, NULL, NULL, NULL, 'uuu', 1000.00, '[\"inspections\\/1761735116_6901f1ccc56a9_photo7.jpg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 5, 3, 'Plumbing', 'test5', 'test5', 'addis', 'Lideta', '2', '2025-10-30', '23:57:00', NULL, 'waiting_acceptance', 0, '2025-10-29 11:19:11', '2025-11-09 18:32:06', NULL, NULL, NULL, NULL, NULL, NULL, 'its expensive', 100.00, '[\"inspections\\/1761736871_6901f8a79f8f0_photo4.jpg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 5, 3, 'Plumbing', 'final6', 'final6', 'final6', 'Kirkos', '11', '2025-10-30', '19:41:00', NULL, 'price_accepted', 0, '2025-10-29 12:38:44', '2025-10-29 19:25:47', '2025-10-29 15:41:00', '2025-10-29 22:25:47', NULL, NULL, NULL, NULL, 'inspection 6', 1000.00, '[\"inspections\\/1761741659_69020b5bbfa1b_photo3.jpg\"]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 5, 7, 'HVAC', 'aaa', 'aaa', '123 Main St, Example City', 'Kirkos', '05', '2025-11-21', '23:25:00', NULL, 'waiting_acceptance', 0, '2025-11-09 16:21:35', '2025-11-09 18:37:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 5, 7, 'HVAC', 'qqqq', '111', '123 Ma123in St, Example City', 'Kirkos', '05', '2025-11-10', '20:48:00', NULL, 'waiting_acceptance', 0, '2025-11-09 16:46:14', '2025-11-09 18:37:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 5, 7, 'HVAC', 'tt', 'ttt', '123 Main St, Ex111ample City', 'Kirkos', '05', '2025-11-10', '20:54:00', NULL, 'waiting_acceptance', 0, '2025-11-09 16:53:51', '2025-11-09 18:37:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `service_request_photos`
--

CREATE TABLE `service_request_photos` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `photo_path` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_assignments`
--

CREATE TABLE `task_assignments` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','rejected','in_progress','completed') DEFAULT 'pending',
  `inspection_date` datetime DEFAULT NULL,
  `work_start_date` datetime DEFAULT NULL,
  `work_completed_date` datetime DEFAULT NULL,
  `technician_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_assignments`
--

INSERT INTO `task_assignments` (`id`, `service_request_id`, `technician_id`, `assigned_by`, `status`, `inspection_date`, `work_start_date`, `work_completed_date`, `technician_notes`, `created_at`) VALUES
(1, 13, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-28 17:56:14'),
(2, 14, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-28 19:02:34'),
(3, 16, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-28 19:20:46'),
(4, 17, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-28 19:33:00'),
(5, 18, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-29 10:49:13'),
(6, 19, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-29 11:19:11'),
(7, 20, 3, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-10-29 12:38:44'),
(8, 21, 7, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-11-09 18:37:02'),
(9, 22, 7, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-11-09 18:37:02'),
(10, 23, 7, NULL, 'pending', NULL, NULL, NULL, NULL, '2025-11-09 18:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `technician_services`
--

CREATE TABLE `technician_services` (
  `id` int(11) NOT NULL,
  `technician_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `subcity` varchar(100) DEFAULT NULL,
  `woreda` varchar(100) DEFAULT NULL,
  `role` enum('homeowner','technician','admin') NOT NULL,
  `profession` varchar(100) DEFAULT NULL,
  `certification_file` varchar(255) DEFAULT NULL,
  `bank_account` varchar(255) DEFAULT NULL,
  `tele_birr` varchar(255) DEFAULT NULL,
  `residence_id_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `phone`, `address`, `profile_photo`, `subcity`, `woreda`, `role`, `profession`, `certification_file`, `bank_account`, `tele_birr`, `residence_id_file`, `status`, `created_at`) VALUES
(1, 'Admin', 'User', 'admin@homefixpro.com', 'admin123', '0912345678', 'Bole Subcity', 'admin.jpg', 'Bole', 'Woreda 3', 'admin', NULL, NULL, NULL, NULL, NULL, 'approved', '2025-10-20 11:31:35'),
(3, 'Mike', 'Technician', 'tech@test.com', 'test123', '0912345680', 'Yeka Subcity', 'technician.jpg', 'Yeka', 'Woreda 4', 'technician', 'Plumbing', NULL, '100023456789', '0912345678', 'residence_123.jpg', 'approved', '2025-10-20 11:31:35'),
(5, 'Hailu', 'Fesseha', 'hailu@gmail.com', '123456', '0909090909', 'aaa', '1760964681_profile_Screenshot (23).png', 'Yeka', 'Woreda 7', 'homeowner', '', NULL, '100023456789', '0912345678', 'residence_123.jpg', 'approved', '2025-10-20 12:51:22'),
(7, 'Nati', 'Nati', 'nati21@gmail.com', '123456', '0911121314', '123,addis', '1762692737_profile_Screenshot (16).png', 'Yeka', 'Woreda 4', 'technician', 'HVAC', '1762692737_cert_Screenshot (23).png', NULL, NULL, '1762692737_residence_Screenshot (22).png', 'approved', '2025-11-09 12:52:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`homeowner_id`,`technician_id`,`service_request_id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `service_request_id` (`service_request_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `service_request_id` (`service_request_id`),
  ADD KEY `idx_chat_messages_conversation` (`conversation_id`),
  ADD KEY `idx_chat_messages_created` (`created_at`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `homeowner_id` (`homeowner_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifications_user_read` (`user_id`,`is_read`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `homeowner_id` (`homeowner_id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_payments_service_request_id` (`service_request_id`),
  ADD KEY `idx_payments_status` (`payment_status`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`),
  ADD KEY `verified_by_admin` (`verified_by_admin`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `homeowner_id` (`homeowner_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`),
  ADD KEY `homeowner_id` (`homeowner_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_feedback`
--
ALTER TABLE `service_feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`),
  ADD KEY `homeowner_id` (`homeowner_id`),
  ADD KEY `technician_id` (`technician_id`);

--
-- Indexes for table `service_inspections`
--
ALTER TABLE `service_inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`);

--
-- Indexes for table `service_payments`
--
ALTER TABLE `service_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_request_id` (`service_request_id`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `technician_id` (`technician_id`),
  ADD KEY `idx_service_requests_status` (`status`),
  ADD KEY `idx_service_requests_homeowner` (`homeowner_id`);

--
-- Indexes for table `service_request_photos`
--
ALTER TABLE `service_request_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `idx_task_assignments_technician_status` (`technician_id`,`status`),
  ADD KEY `idx_task_assignments_request_id` (`service_request_id`),
  ADD KEY `idx_task_assignments_technician` (`technician_id`);

--
-- Indexes for table `technician_services`
--
ALTER TABLE `technician_services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_technician_service` (`technician_id`,`service_id`),
  ADD KEY `service_id` (`service_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role_status` (`role`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `service_feedback`
--
ALTER TABLE `service_feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_inspections`
--
ALTER TABLE `service_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_payments`
--
ALTER TABLE `service_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `service_request_photos`
--
ALTER TABLE `service_request_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_assignments`
--
ALTER TABLE `task_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `technician_services`
--
ALTER TABLE `technician_services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_conversations`
--
ALTER TABLE `chat_conversations`
  ADD CONSTRAINT `chat_conversations_ibfk_1` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_conversations_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_conversations_ibfk_3` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`);

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_3` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_4` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_5` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`),
  ADD CONSTRAINT `chat_messages_ibfk_6` FOREIGN KEY (`conversation_id`) REFERENCES `chat_conversations` (`id`);

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_3` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`),
  ADD CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`verified_by_admin`) REFERENCES `users` (`id`);

--
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ratings_ibfk_2` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `service_feedback`
--
ALTER TABLE `service_feedback`
  ADD CONSTRAINT `service_feedback_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`),
  ADD CONSTRAINT `service_feedback_ibfk_2` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `service_feedback_ibfk_3` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `service_inspections`
--
ALTER TABLE `service_inspections`
  ADD CONSTRAINT `service_inspections_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`);

--
-- Constraints for table `service_payments`
--
ALTER TABLE `service_payments`
  ADD CONSTRAINT `service_payments_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`);

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`homeowner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `service_requests_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `service_request_photos`
--
ALTER TABLE `service_request_photos`
  ADD CONSTRAINT `service_request_photos_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `service_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `task_assignments`
--
ALTER TABLE `task_assignments`
  ADD CONSTRAINT `task_assignments_ibfk_1` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`),
  ADD CONSTRAINT `task_assignments_ibfk_2` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `task_assignments_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `technician_services`
--
ALTER TABLE `technician_services`
  ADD CONSTRAINT `technician_services_ibfk_1` FOREIGN KEY (`technician_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `technician_services_ibfk_2` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
