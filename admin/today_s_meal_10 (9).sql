-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 04, 2025 at 02:00 PM
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
-- Database: `today_s_meal_10`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `user_id` int(11) NOT NULL,
  `cat_id` int(11) DEFAULT NULL,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`user_id`, `cat_id`, `last_login`) VALUES
(1, NULL, '2025-05-22 07:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `action_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action_type` text DEFAULT NULL,
  `action_target` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` int(11) NOT NULL,
  `c_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category_photo` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `cloud_kitchens_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `c_name`, `description`, `category_photo`, `created_by`, `cloud_kitchens_count`) VALUES
(1, 'Egyptian Cuisine', 'Traditional and Classic Egyptian dishes ', 'egyptian.jpg', 1, 15),
(2, 'Italian Cuisine', 'Pasta, pizza and other Italian favorites', 'italian.jpg', 1, 10),
(3, 'Asian Cuisine', 'Chinese, Japanese, Thai and other Asian dishes', 'asian.jpg', 1, 8),
(4, 'Healthy Food', 'Low-calorie, high-protein and balanced meals', 'healthy.jpg', 1, 7),
(5, 'Desserts', 'Sweet treats and baked goods', 'desserts.jpg', 1, 5),
(6, 'Seafood', 'Fresh fish and seafood dishes', 'seafood.jpg', 1, 3),
(7, 'Grill', 'Grilled meats and vegetables', 'grilled.jpg', 1, 6),
(8, 'Fast Food', 'fast food kinds', 'ff.jpg', 1, 4),
(9, 'Bakeries', 'All Types of Bakries', 'bake.jpg', 1, 3),
(10, 'Mediterranean', 'Dishes from the Mediterranean region', 'mediterranean.jpg', 1, 5),
(11, 'Breakfast', 'Morning meals and brunch options', 'breakfast.jpg', 1, 4),
(12, 'Sandwiches', 'Various sandwich options', 'sandwiches.jpg', 1, 3),
(13, 'Salads', 'Fresh salad options', 'salads.jpg', 1, 2),
(14, 'Juices', 'Fresh juices and smoothies', 'juices.jpg', 1, 2),
(15, 'International', 'Dishes from around the world', 'international.jpg', 1, 4);

-- --------------------------------------------------------

--
-- Table structure for table `caterer_tags`
--

CREATE TABLE `caterer_tags` (
  `user_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `caterer_tags`
--

INSERT INTO `caterer_tags` (`user_id`, `tag_id`) VALUES
(3, 4),
(3, 14),
(5, 1),
(5, 6),
(7, 4),
(7, 15),
(9, 2),
(9, 7),
(9, 9),
(18, 8),
(18, 10),
(20, 4),
(20, 13),
(22, 4),
(22, 7),
(24, 2),
(24, 8),
(24, 9),
(26, 2),
(26, 9),
(26, 10),
(28, 4),
(28, 14),
(30, 10),
(30, 11),
(32, 1),
(32, 6),
(34, 7),
(34, 9),
(34, 13),
(36, 10),
(36, 11),
(38, 4),
(38, 15),
(40, 4),
(40, 14),
(42, 1),
(42, 6),
(44, 4),
(44, 15),
(46, 2),
(46, 7),
(46, 9),
(48, 8),
(48, 10),
(50, 4),
(50, 13);

-- --------------------------------------------------------

--
-- Table structure for table `cloud_kitchen_owner`
--

CREATE TABLE `cloud_kitchen_owner` (
  `user_id` int(11) NOT NULL,
  `start_year` year(4) NOT NULL,
  `c_n_id` varchar(50) NOT NULL,
  `status` enum('active','suspended','blocked') DEFAULT 'active',
  `orders_count` int(11) DEFAULT 0,
  `business_name` varchar(255) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `years_of_experience` enum('Beginner (0-1 years)','Intermediate (2-3 years)','Advanced (4-5 years)','Expert (6+ years)') NOT NULL,
  `customized_orders` tinyint(1) DEFAULT 0,
  `average_rating` decimal(3,2) DEFAULT 0.00,
  `is_approved` tinyint(1) DEFAULT 0,
  `speciality_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cloud_kitchen_owner`
--

INSERT INTO `cloud_kitchen_owner` (`user_id`, `start_year`, `c_n_id`, `status`, `orders_count`, `business_name`, `registration_date`, `years_of_experience`, `customized_orders`, `average_rating`, `is_approved`, `speciality_id`) VALUES
(3, '2018', '30005123456789', 'blocked', 120, 'Alexandria Kitchen', '2024-01-15 08:00:00', 'Intermediate (2-3 years)', 1, 4.50, 1, 1),
(5, '2019', '30005234567890', 'active', 85, 'Port Said Delights', '2024-02-20 09:30:00', 'Beginner (0-1 years)', 0, 4.20, 1, 2),
(7, '2017', '30005345678901', 'blocked', 150, 'Ismailia Meals', '2024-03-10 07:15:00', 'Advanced (4-5 years)', 1, 4.75, 1, 3),
(9, '2020', '30005456789012', 'blocked', 60, 'Tanta Treats', '2024-04-05 12:45:00', 'Beginner (0-1 years)', 0, 3.90, 1, 4),
(18, '2016', '30005567890123', 'active', 200, 'Giza Gourmet', '2024-05-12 13:20:00', 'Expert (6+ years)', 1, 4.80, 1, 5),
(20, '2019', '30005678901234', 'active', 95, 'Cairo Cuisine', '2024-06-18 05:30:00', 'Intermediate (2-3 years)', 0, 4.30, 1, 6),
(22, '2018', '30005789012345', 'active', 110, 'Kasr El Aini Kitchen', '2024-07-22 09:15:00', 'Intermediate (2-3 years)', 1, 4.40, 1, 7),
(24, '2020', '30005890123456', 'blocked', 70, 'Ahram Meals', '2024-08-30 07:45:00', 'Beginner (0-1 years)', 0, 3.80, 1, 8),
(26, '2017', '30005901234567', 'active', 130, 'Remaya Restaurant', '2024-09-15 11:00:00', 'Advanced (4-5 years)', 1, 4.60, 1, 9),
(28, '2019', '30006012345678', 'active', 90, 'Sudan Street Food', '2024-10-20 06:30:00', 'Intermediate (2-3 years)', 0, 4.10, 1, 10),
(30, '2018', '30006123456789', 'active', 105, 'Zamalek Zest', '2024-11-05 09:20:00', 'Intermediate (2-3 years)', 1, 4.45, 1, 11),
(32, '2020', '30006234567890', 'active', 65, 'Marashly Meals', '2024-12-10 13:10:00', 'Beginner (0-1 years)', 0, 3.95, 1, 12),
(34, '2017', '30006345678901', 'active', 140, 'Malek El Afdal Kitchen', '2025-01-15 08:25:00', 'Advanced (4-5 years)', 1, 4.70, 1, 13),
(36, '2019', '30006456789012', 'active', 80, 'Nahda Nutrition', '2025-02-20 11:40:00', 'Intermediate (2-3 years)', 0, 4.15, 1, 14),
(38, '2018', '30006567890123', 'active', 115, 'Falaki Foods', '2025-03-25 14:55:00', 'Intermediate (2-3 years)', 1, 4.35, 1, 15),
(40, '2020', '30006678901234', 'active', 75, 'Sherifein Specialties', '2025-04-05 07:05:00', 'Beginner (0-1 years)', 0, 3.85, 1, 1),
(42, '2017', '30006789012345', 'active', 125, 'Sabtiya Savories', '2025-05-10 09:30:00', 'Advanced (4-5 years)', 1, 4.55, 1, 2),
(44, '2019', '30006890123456', 'active', 85, 'Mansour Meals', '2025-05-15 11:45:00', 'Intermediate (2-3 years)', 0, 4.25, 1, 3),
(46, '2018', '30006901234567', 'active', 100, 'Merghany Munchies', '2025-05-18 14:00:00', 'Intermediate (2-3 years)', 1, 4.30, 1, 4),
(48, '2020', '30007012345678', 'active', 55, 'Hegaz Healthy', '2025-05-20 07:15:00', 'Beginner (0-1 years)', 0, 3.75, 1, 5),
(50, '2017', '30007123456789', 'active', 160, 'Maamoun Meals', '2025-05-22 10:30:00', 'Expert (6+ years)', 1, 4.85, 1, 6),
(80, '2020', '30007234567890', 'active', 0, 'Cairo Delights', '2025-06-02 19:01:20', 'Intermediate (2-3 years)', 1, 0.00, 1, 1),
(81, '2021', '30007345678901', 'active', 0, 'Nile Bites', '2025-06-02 19:01:20', 'Beginner (0-1 years)', 0, 0.00, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `cloud_kitchen_specialist_category`
--

CREATE TABLE `cloud_kitchen_specialist_category` (
  `cloud_kitchen_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cloud_kitchen_specialist_category`
--

INSERT INTO `cloud_kitchen_specialist_category` (`cloud_kitchen_id`, `cat_id`) VALUES
(3, 1),
(3, 10),
(3, 11),
(5, 2),
(5, 15),
(7, 3),
(7, 15),
(9, 4),
(9, 8),
(18, 5),
(18, 14),
(20, 6),
(20, 7),
(22, 1),
(22, 7),
(24, 8),
(24, 9),
(26, 4),
(26, 9),
(28, 1),
(28, 10),
(30, 10),
(30, 11),
(32, 12),
(32, 13),
(34, 4),
(34, 13),
(36, 11),
(36, 14),
(38, 3),
(38, 15),
(40, 1),
(40, 10),
(42, 2),
(42, 15),
(44, 3),
(44, 7),
(46, 4),
(46, 8),
(48, 5),
(48, 14),
(50, 6),
(50, 7),
(80, 1),
(80, 10),
(81, 2);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `customer_id` int(11) DEFAULT NULL,
  `kitchen_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `subject`, `message`, `status`, `customer_id`, `kitchen_id`, `created_at`, `resolved_at`) VALUES
(1, 'Late Delivery', 'My order arrived 1 hour later than promised', 'resolved', 2, 3, '2025-05-01 10:30:00', '2025-05-01 12:00:00'),
(2, 'Missing Item', 'The dessert was missing from my order', 'resolved', 4, 5, '2025-05-02 11:30:00', '2025-05-02 13:00:00'),
(3, 'Wrong Order', 'Received someone else\'s order', 'pending', 6, 7, '2025-05-03 14:15:00', NULL),
(4, 'Food Quality', 'The salad was not fresh', 'resolved', 8, 9, '2025-05-04 09:15:00', '2025-05-04 11:00:00'),
(5, 'Packaging Issue', 'Food spilled during delivery', 'pending', 10, 18, '2025-05-05 16:00:00', NULL),
(6, 'Incorrect Temperature', 'Food arrived cold', 'resolved', 16, 20, '2025-05-06 17:30:00', '2025-05-06 19:00:00'),
(7, 'Rude Delivery', 'The delivery person was rude', 'pending', 17, 22, '2025-05-07 10:00:00', NULL),
(8, 'Allergy Concern', 'Had allergic reaction to undisclosed ingredient', 'resolved', 19, 24, '2025-05-08 14:00:00', '2025-05-08 16:00:00'),
(9, 'Portion Size', 'Portion was much smaller than expected', 'pending', 21, 26, '2025-05-09 15:30:00', NULL),
(10, 'Wrong Address', 'Order delivered to wrong address', 'resolved', 23, 28, '2025-05-10 11:00:00', '2025-05-10 12:30:00');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `user_id` int(11) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `BOD` date NOT NULL,
  `status` enum('active','suspended','blocked') DEFAULT 'active',
  `is_subscribed` tinyint(1) DEFAULT 0,
  `last_login` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`user_id`, `gender`, `BOD`, `status`, `is_subscribed`, `last_login`, `registration_date`) VALUES
(2, 'Male', '1990-05-15', 'active', 1, '2025-05-22 06:30:00', '2024-01-10 06:00:00'),
(4, 'Female', '1985-08-22', 'active', 0, '2025-05-21 11:15:00', '2024-02-15 08:30:00'),
(6, 'Female', '1995-03-10', 'active', 1, '2025-05-22 08:45:00', '2024-03-20 10:00:00'),
(8, 'Female', '1988-11-05', 'active', 0, '2025-05-20 13:20:00', '2024-04-05 07:15:00'),
(10, 'Female', '1992-07-18', 'active', 1, '2025-05-22 05:10:00', '2024-05-12 11:45:00'),
(16, 'Male', '1993-09-25', 'active', 0, '2025-05-21 16:30:00', '2024-06-18 08:20:00'),
(17, 'Female', '1987-12-30', 'active', 1, '2025-05-22 07:15:00', '2024-07-22 13:40:00'),
(19, 'Female', '1991-04-12', 'active', 0, '2025-05-20 10:25:00', '2024-08-30 07:10:00'),
(21, 'Female', '1989-06-08', 'active', 1, '2025-05-22 09:05:00', '2024-09-15 11:30:00'),
(23, 'Female', '1994-02-14', 'active', 0, '2025-05-21 14:50:00', '2024-10-20 05:45:00'),
(25, 'Female', '1996-10-03', 'active', 1, '2025-05-22 04:30:00', '2024-11-05 10:15:00'),
(27, 'Female', '1986-01-20', 'active', 0, '2025-05-21 17:10:00', '2024-12-10 13:50:00'),
(29, 'Female', '1990-07-07', 'active', 1, '2025-05-22 06:45:00', '2025-01-15 07:25:00'),
(31, 'Female', '1993-11-28', 'active', 0, '2025-05-20 11:35:00', '2025-02-20 09:40:00'),
(33, 'Female', '1988-05-17', 'active', 1, '2025-05-22 08:20:00', '2025-03-25 11:15:00'),
(35, 'Female', '1995-08-09', 'active', 0, '2025-05-21 15:05:00', '2025-04-05 14:30:00'),
(37, 'Female', '1991-12-24', 'active', 1, '2025-05-22 05:50:00', '2025-05-10 07:20:00'),
(39, 'Female', '1987-03-19', 'active', 0, '2025-05-20 12:40:00', '2025-05-15 11:10:00'),
(41, 'Female', '1994-06-22', 'active', 1, '2025-05-22 07:55:00', '2025-05-18 06:45:00'),
(43, 'Female', '1989-09-11', 'active', 0, '2025-05-21 16:15:00', '2025-05-20 09:30:00'),
(45, 'Male', '1992-04-05', 'active', 1, '2025-05-22 04:10:00', '2025-05-21 05:15:00'),
(47, 'Male', '1996-01-30', 'active', 0, '2025-05-21 13:25:00', '2025-05-22 07:40:00'),
(49, 'Female', '1985-10-15', 'active', 1, '2025-05-20 09:50:00', '2025-05-22 08:55:00'),
(82, 'Male', '1990-01-15', 'active', 1, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(83, 'Female', '1988-05-22', 'active', 0, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(84, 'Male', '1995-09-10', 'active', 1, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(85, 'Female', '1992-11-05', 'active', 1, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(86, 'Male', '1987-07-18', 'active', 0, '2025-06-02 19:02:45', '2025-06-02 19:02:45'),
(90, 'Male', '2025-06-04', 'active', 0, '2025-06-04 11:02:10', '2025-06-04 11:02:10');

-- --------------------------------------------------------

--
-- Table structure for table `customized_order`
--

CREATE TABLE `customized_order` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `kitchen_id` int(11) NOT NULL,
  `budget_min` decimal(10,2) NOT NULL,
  `budget_max` decimal(10,2) NOT NULL,
  `chosen_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `ord_description` text NOT NULL,
  `img_reference` varchar(255) DEFAULT NULL,
  `people_servings` int(11) NOT NULL,
  `preferred_completion_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_approval` enum('approved','rejected','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customized_order`
--

INSERT INTO `customized_order` (`order_id`, `customer_id`, `kitchen_id`, `budget_min`, `budget_max`, `chosen_amount`, `status`, `ord_description`, `img_reference`, `people_servings`, `preferred_completion_date`, `created_at`, `customer_approval`) VALUES
(25, 4, 9, 100.00, 150.00, 130.00, 'accepted', 'Need a healthy meal plan for 5 people with high protein and low carbs', 'mealplan.jpg', 5, '2025-05-22 00:00:00', '2025-05-21 11:00:00', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(11) NOT NULL,
  `order_id` varchar(20) NOT NULL,
  `delivery_person_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `distance` decimal(8,2) NOT NULL,
  `estimated_duration` int(11) NOT NULL,
  `scheduled_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `pickup_time` timestamp NULL DEFAULT NULL,
  `completion_time` timestamp NULL DEFAULT NULL,
  `cancelled_time` timestamp NULL DEFAULT NULL,
  `delayed_time` timestamp NULL DEFAULT NULL,
  `last_status_update` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `p_method` enum('cash','visa') NOT NULL,
  `route_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`route_data`)),
  `estimated_distance` decimal(10,2) DEFAULT NULL COMMENT 'Estimated trip distance in meters',
  `status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `delivery_person_id`, `customer_id`, `provider_id`, `distance`, `estimated_duration`, `scheduled_time`, `pickup_time`, `completion_time`, `cancelled_time`, `delayed_time`, `last_status_update`, `notes`, `p_method`, `route_data`, `estimated_distance`, `status`) VALUES
(1, '22', 68, 47, 3, 0.00, 15, '2025-06-01 17:08:16', '2025-06-01 16:08:03', NULL, '2025-06-01 16:08:16', NULL, '2025-06-01 16:08:16', NULL, 'visa', NULL, NULL, 'pending'),
(2, '22', 69, 47, 20, 4698.82, 23494, '2025-06-02 15:04:22', '2025-06-02 14:03:56', '2025-06-02 14:04:22', NULL, NULL, '2025-06-02 14:04:22', NULL, 'visa', NULL, NULL, 'pending'),
(3, '22', 11, 47, 20, 4698.82, 23494, '2025-06-02 17:43:23', '2025-06-02 16:43:19', '2025-06-02 16:43:23', NULL, NULL, '2025-06-02 16:43:23', NULL, 'visa', NULL, NULL, 'pending'),
(5, '28', 87, 84, 80, 0.03, 0, '2025-06-02 20:49:28', NULL, NULL, NULL, NULL, NULL, NULL, 'visa', NULL, NULL, 'pending'),
(6, '27', 87, 83, 81, 0.18, 0, '2025-06-03 08:22:29', NULL, NULL, NULL, NULL, NULL, NULL, 'visa', NULL, NULL, 'pending'),
(7, '29', 87, 85, 81, 0.07, 0, '2025-06-03 17:26:49', NULL, '2025-06-03 16:26:49', NULL, NULL, '2025-06-03 16:26:49', NULL, 'visa', NULL, NULL, 'pending'),
(8, '26', 88, 82, 80, 0.08, 0, '2025-06-03 10:47:34', NULL, '2025-06-03 09:47:34', NULL, NULL, '2025-06-03 09:47:34', NULL, 'visa', NULL, NULL, 'pending'),
(9, '30', 87, 86, 80, 0.03, 0, '2025-06-03 17:27:04', NULL, NULL, NULL, NULL, NULL, NULL, 'visa', NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_details`
--

CREATE TABLE `delivery_details` (
  `delivery_id` int(11) NOT NULL,
  `d_location` text NOT NULL,
  `p_method` enum('cash','visa') NOT NULL,
  `delivery_date_and_time` datetime NOT NULL,
  `d_status` enum('delivered','not_delivered') NOT NULL,
  `ord_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery_man`
--

CREATE TABLE `delivery_man` (
  `user_id` int(11) NOT NULL,
  `d_n_id` varchar(20) DEFAULT NULL,
  `d_license` varchar(50) NOT NULL,
  `d_zone` varchar(255) NOT NULL,
  `status` enum('online','offline') DEFAULT 'offline',
  `current_status` enum('free','busy') DEFAULT 'free',
  `is_approved` tinyint(1) DEFAULT 0,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `del_id` varchar(255) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_man`
--

INSERT INTO `delivery_man` (`user_id`, `d_n_id`, `d_license`, `d_zone`, `status`, `current_status`, `is_approved`, `registration_date`, `del_id`, `zone_id`, `latitude`, `longitude`) VALUES
(11, '29905012345678', 'DL12345678', 'Cairo', 'online', 'busy', 1, '2024-01-05 07:00:00', '', 0, 0.0000000, 0.0000000),
(12, '29905123456789', 'DL23456789', 'Giza', 'online', 'busy', 1, '2024-01-10 08:30:00', '', 0, 0.0000000, 0.0000000),
(13, '29905234567890', 'DL34567890', 'Alexandria', 'offline', 'free', 1, '2024-02-15 09:45:00', '', 0, 0.0000000, 0.0000000),
(14, '29905345678901', 'DL45678901', 'Heliopolis', 'online', 'free', 1, '2024-03-20 10:15:00', '', 0, 0.0000000, 0.0000000),
(15, '29905456789012', 'DL56789012', 'Zamalek', 'offline', 'free', 1, '2024-04-25 12:30:00', '', 0, 0.0000000, 0.0000000),
(54, NULL, 'DL20258075', 'Cairo', 'offline', 'free', 0, '2025-06-01 08:32:23', '', 0, 0.0000000, 0.0000000),
(55, '299258790550', 'DL99185821', 'Cairo', 'offline', 'free', 1, '2025-06-01 12:30:12', '', 0, 0.0000000, 0.0000000),
(58, '299257972178', 'DL11151502', 'Cairo', 'offline', 'free', 1, '2025-06-01 12:32:07', '', 0, 0.0000000, 0.0000000),
(60, '299250851374', 'attached_assets/licenses/license_1748781429_2688.p', 'Cairo', 'offline', 'free', 0, '2025-06-01 12:37:09', '', 0, 0.0000000, 0.0000000),
(63, 'DEL250759006', 'attached_assets/licenses/license_1748781548_7813.p', 'Cairo', 'offline', 'free', 0, '2025-06-01 12:39:08', '', 0, 0.0000000, 0.0000000),
(65, '29948569752315', 'attached_assets/licenses/license_1748782937_2009.p', 'Cairo', 'offline', 'free', 0, '2025-06-01 13:02:17', '', 0, 0.0000000, 0.0000000),
(66, '29912345678910', 'attached_assets/licenses/license_1748784391_9770.p', 'Alexandria', 'offline', 'free', 0, '2025-06-01 13:26:32', '', 0, 0.0000000, 0.0000000),
(68, '29912345678911', 'attached_assets/licenses/license_1748784971_5927.p', 'Zamalek', 'online', 'busy', 0, '2025-06-01 13:36:11', '', 2, 0.0000000, 0.0000000),
(69, '299485697523191', 'attached_assets/licenses/license_1748786161_3787.p', 'Cairo', 'online', 'busy', 1, '2025-06-01 13:56:01', 'DEL256745555', 0, 0.0000000, 0.0000000),
(72, '299123456788560', 'attached_assets/licenses/license_1748876176_4830.p', '', 'offline', 'free', 0, '2025-06-02 14:56:16', 'DEL259099722', 0, 0.0000000, 0.0000000),
(73, '29912345678741', 'uploads/licenses/license_1748877702_3935.png', '', 'offline', 'free', 0, '2025-06-02 15:21:42', 'DEL259446791', 0, 0.0000000, 0.0000000),
(75, '29912345678743', 'uploads/licenses/license_1748882439_6281.png', '', 'offline', 'free', 0, '2025-06-02 16:40:39', 'DEL251658908', 0, 0.0000000, 0.0000000),
(76, '29912345690743', 'attached_assets/licenses/license_1748883525_9591.j', '', 'offline', 'free', 0, '2025-06-02 16:58:46', 'DEL259376321', 0, 0.0000000, 0.0000000),
(87, '29948888852315', 'attached_assets/licenses/license_1748891025_2111.j', '5th settlement', 'online', 'busy', 1, '2025-06-02 19:03:45', 'DEL257045031', 2, 30.0850000, 31.5382000),
(88, '29948569752417', 'attached_assets/licenses/license_1748947499_9227.j', '5th settlement', 'online', 'free', 1, '2025-06-03 10:44:59', 'DEL254779878', 2, 30.0004560, 31.4626260),
(89, '2999952152155', 'attached_assets/licenses/license_1748971706_7841.p', '5th settlement', 'offline', 'free', 1, '2025-06-03 17:28:26', 'DEL253124078', 2, 30.0850000, 31.5382000);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_status_history`
--

CREATE TABLE `delivery_status_history` (
  `id` int(11) NOT NULL,
  `delivery_id` int(11) NOT NULL,
  `status` varchar(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_status_history`
--

INSERT INTO `delivery_status_history` (`id`, `delivery_id`, `status`, `timestamp`) VALUES
(1, 1, 'pending', '2025-06-01 17:07:10'),
(2, 1, 'in-progress', '2025-06-01 16:08:03'),
(3, 1, 'cancelled', '2025-06-01 16:08:16'),
(4, 2, 'pending', '2025-06-02 15:01:06'),
(5, 2, 'in-progress', '2025-06-02 14:03:56'),
(6, 2, 'completed', '2025-06-02 14:04:22'),
(7, 3, 'pending', '2025-06-02 17:42:27'),
(8, 3, 'in-progress', '2025-06-02 16:43:19'),
(9, 3, 'completed', '2025-06-02 16:43:23'),
(13, 5, 'pending', '2025-06-02 20:49:28'),
(14, 6, 'pending', '2025-06-03 08:22:29'),
(15, 7, 'pending', '2025-06-03 09:18:10'),
(16, 8, 'pending', '2025-06-03 10:47:07'),
(17, 8, 'delivered', '2025-06-03 09:47:34'),
(18, 7, 'delivered', '2025-06-03 16:26:49'),
(19, 9, 'pending', '2025-06-03 17:27:04');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_subscriptions`
--

CREATE TABLE `delivery_subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `plan_duration` enum('1_month','6_months','12_months') NOT NULL,
  `start_date` date NOT NULL DEFAULT curdate(),
  `end_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_subscriptions`
--

INSERT INTO `delivery_subscriptions` (`subscription_id`, `customer_id`, `plan_duration`, `start_date`, `end_date`, `is_active`, `payment_status`) VALUES
(1, 2, '6_months', '2025-01-10', '2025-07-10', 1, 'paid'),
(2, 6, '1_month', '2025-05-01', '2025-06-01', 1, 'paid'),
(3, 10, '12_months', '2025-01-01', '2026-01-01', 1, 'paid'),
(4, 17, '6_months', '2025-03-01', '2025-09-01', 1, 'paid'),
(5, 21, '1_month', '2025-05-15', '2025-06-15', 1, 'paid'),
(6, 25, '12_months', '2025-01-01', '2026-01-01', 1, 'paid'),
(7, 29, '6_months', '2025-04-01', '2025-10-01', 1, 'paid'),
(8, 33, '1_month', '2025-05-10', '2025-06-10', 1, 'paid'),
(9, 37, '12_months', '2025-01-01', '2026-01-01', 1, 'paid'),
(10, 41, '6_months', '2025-03-15', '2025-09-15', 1, 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tokens`
--

CREATE TABLE `delivery_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `type` varchar(20) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dietary_tags`
--

CREATE TABLE `dietary_tags` (
  `tag_id` int(11) NOT NULL,
  `tag_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dietary_tags`
--

INSERT INTO `dietary_tags` (`tag_id`, `tag_name`) VALUES
(2, 'Dairy-Free'),
(12, 'Diabetic-Friendly'),
(1, 'Gluten-Free'),
(13, 'Heart-Healthy'),
(9, 'High-Fiber'),
(11, 'High-Protein'),
(5, 'Keto'),
(14, 'Kid-Friendly'),
(6, 'Low-Carb'),
(7, 'Low-Fat'),
(3, 'Nut-Free'),
(10, 'Organic'),
(15, 'Spicy'),
(8, 'Sugar-Free'),
(4, 'Vegan');

-- --------------------------------------------------------

--
-- Table structure for table `external_user`
--

CREATE TABLE `external_user` (
  `user_id` int(11) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `ext_role` enum('customer','cloud_kitchen_owner') NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `zone_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `external_user`
--

INSERT INTO `external_user` (`user_id`, `address`, `ext_role`, `latitude`, `longitude`, `zone_id`) VALUES
(2, '15 El Nozha St., Cairo', 'customer', 0.0000000, 0.0000000, 0),
(3, '22 Mohamed Farid St., Alexandria', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(4, '37, Ismail Mohamed Street, Zamalek, Cairo, Egypt', 'customer', 30.0626300, 31.2496700, 0),
(5, '45 El Geish St., Port Said', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(6, '45 abo elfeda  , zamalek, egypt', 'customer', 30.0641800, 31.2200900, 0),
(7, '33 El Nasr St., Ismailia', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(8, '7 El Thawra St., Suez', 'customer', 0.0000000, 0.0000000, 0),
(9, '19 El Salam St., Tanta', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(10, '24 El Borg St., Aswan', 'customer', 0.0000000, 0.0000000, 0),
(16, '5 El Khalifa El Maamoun St., Cairo', 'customer', 0.0000000, 0.0000000, 0),
(17, '30 El Hegaz St., Heliopolis', 'customer', 0.0000000, 0.0000000, 0),
(18, '11 El Shaheed St., Giza', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(19, '9 El Tahrir St., Dokki', 'customer', 0.0000000, 0.0000000, 0),
(20, '14 El Manyal St., Cairo', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(21, '3 El Azhar St., Cairo', 'customer', 0.0000000, 0.0000000, 0),
(22, '17 El Kasr El Aini St., Cairo', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(23, '2 El Nile St., Giza', 'customer', 0.0000000, 0.0000000, 0),
(24, '6 El Ahram St., Giza', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(25, '10 El Haram St., Giza', 'customer', 0.0000000, 0.0000000, 0),
(26, '13 El Remaya Sq., Giza', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(27, '20 El Batal Ahmed Abdel Aziz St., Mohandessin', 'customer', 0.0000000, 0.0000000, 0),
(28, '25 El Sudan St., Mohandessin', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(29, '18 El Mesaha Sq., Dokki', 'customer', 0.0000000, 0.0000000, 0),
(30, '16 El Brazil St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(31, '4 El Gabalaya St., Zamalek', 'customer', 0.0000000, 0.0000000, 0),
(32, '23 El Marashly St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(33, '1 El Gezira St., Zamalek', 'customer', 0.0000000, 0.0000000, 0),
(34, '27 El Malek El Afdal St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(35, '29 El Nil St., Agouza', 'customer', 0.0000000, 0.0000000, 0),
(36, '31 El Nahda St., Dokki', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(37, '32 El Tahrir St., Downtown', 'customer', 0.0000000, 0.0000000, 0),
(38, '34 El Falaki St., Downtown', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(39, '35 El Bustan St., Downtown', 'customer', 0.0000000, 0.0000000, 0),
(40, '36 El Sherifein St., Downtown', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(41, '37 El Gomhoreya St., Downtown', 'customer', 0.0000000, 0.0000000, 0),
(42, '38 El Sabtiya St., Downtown', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(43, '15 El Nozha St., Cairo', 'customer', 30.1406586, 31.3740845, 0),
(44, '40 El Mansour Mohamed St., Zamalek', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(45, '41 El Sheikh El Sharbatly St., Heliopolis', 'customer', 0.0000000, 0.0000000, 0),
(46, '42 El Merghany St., Heliopolis', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(47, '45 mohamed mazhar zamalek egypt', 'customer', 30.0641800, 31.2200900, 0),
(48, '44 El Hegaz St., Heliopolis', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(49, '45 El Nozha St., Heliopolis', 'customer', 0.0000000, 0.0000000, 0),
(50, '46 El Khalifa El Maamoun St., Heliopolis', 'cloud_kitchen_owner', 0.0000000, 0.0000000, 0),
(80, 'Building 12, Road 90, 5th Settlement', 'cloud_kitchen_owner', 30.0130560, 31.4200000, 2),
(81, 'Mall of Egypt, 5th Settlement', 'cloud_kitchen_owner', 30.0150000, 31.4225000, 2),
(82, 'Villa 25, Street 200, 5th Settlement', 'customer', 30.0125000, 31.4195000, 2),
(83, 'Apartment 301, Building 45, 5th Settlement', 'customer', 30.0140000, 31.4210000, 2),
(84, 'Compound 7, Road 150, 5th Settlement', 'customer', 30.0132000, 31.4203000, 2),
(85, 'Building 33, Road 180, 5th Settlement', 'customer', 30.0145000, 31.4220000, 2),
(86, 'Villa 12, Street 210, 5th Settlement', 'customer', 30.0128000, 31.4198000, 2),
(90, 'New Cairo, Cairo, Egypt', 'customer', 0.0000000, 0.0000000, 0);

-- --------------------------------------------------------

--
-- Table structure for table `meals`
--

CREATE TABLE `meals` (
  `meal_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `status` enum('out of stock','low stock','available') DEFAULT 'out of stock',
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals`
--

INSERT INTO `meals` (`meal_id`, `cloud_kitchen_id`, `name`, `description`, `photo`, `stock_quantity`, `status`, `price`) VALUES
(1, 3, 'Koshari', 'Traditional Egyptian dish with rice, pasta, lentils, and crispy onions', 'koshari.jpg', 50, 'available', 35.00),
(2, 3, 'Molokhia', 'Green leafy stew served with rice and chicken', 'molokhia.jpg', 30, 'available', 45.00),
(3, 5, 'Spaghetti Carbonara', 'Classic Italian pasta with creamy sauce and bacon', 'carbonara.jpg', 40, 'available', 55.00),
(4, 5, 'Margherita Pizza', 'Traditional pizza with tomato sauce and mozzarella', 'pizza.jpg', 25, 'available', 65.00),
(5, 7, 'Chicken Pad Thai', 'Thai stir-fried noodles with chicken and vegetables', 'padthai.jpg', 35, 'available', 60.00),
(6, 7, 'Sushi Platter', 'Assorted sushi rolls with soy sauce and wasabi', 'sushi.jpg', 20, 'low stock', 120.00),
(7, 9, 'Grilled Chicken Salad', 'Healthy salad with grilled chicken and vegetables', 'chickensalad.jpg', 45, 'available', 40.00),
(8, 9, 'Quinoa Bowl', 'Nutritious quinoa with vegetables and tahini dressing', 'quinoa.jpg', 30, 'available', 45.00),
(9, 18, 'Chocolate Cake', 'Rich chocolate cake with chocolate frosting', 'chocolatecake.jpg', 15, 'available', 50.00),
(10, 18, 'Baklava', 'Sweet pastry with nuts and honey syrup', 'baklava.jpg', 20, 'available', 40.00),
(11, 20, 'Grilled Sea Bass', 'Fresh sea bass with lemon and herbs', 'seabass.jpg', 10, 'low stock', 150.00),
(12, 20, 'Fried Calamari', 'Crispy fried squid with tartar sauce', 'calamari.jpg', 15, 'available', 80.00),
(13, 22, 'Grilled Kofta', 'Egyptian grilled meat skewers with rice', 'kofta.jpg', 30, 'available', 60.00),
(14, 22, 'Chicken Shawarma', 'Marinated chicken with garlic sauce and bread', 'shawarma.jpg', 25, 'available', 45.00),
(15, 24, 'Vegetable Tagine', 'Moroccan vegetable stew with couscous', 'tagine.jpg', 20, 'available', 50.00),
(16, 24, 'Falafel Plate', 'Fried falafel with tahini and salad', 'falafel.jpg', 35, 'available', 35.00),
(17, 26, 'Vegan Burger', 'Plant-based burger with vegan cheese', 'veganburger.jpg', 25, 'available', 55.00),
(18, 26, 'Vegan Chocolate Mousse', 'Dairy-free chocolate dessert', 'veganmousse.jpg', 15, 'available', 30.00),
(19, 28, 'Greek Salad', 'Fresh salad with feta cheese and olives', 'greeksalad.jpg', 40, 'available', 45.00),
(20, 28, 'Hummus Plate', 'Creamy hummus with pita bread', 'hummus.jpg', 30, 'available', 35.00),
(21, 30, 'Foul Medames', 'Traditional Egyptian breakfast beans', 'foul.jpg', 50, 'available', 25.00),
(22, 30, 'Taameya', 'Egyptian falafel with tahini sauce', 'taameya.jpg', 40, 'available', 30.00),
(23, 32, 'Cheese Burger', 'Classic beef burger with cheese', 'cheeseburger.jpg', 30, 'available', 65.00),
(24, 32, 'Chicken Sub', 'Grilled chicken sub with vegetables', 'chickensub.jpg', 25, 'available', 45.00),
(25, 34, 'Caesar Salad', 'Romaine lettuce with Caesar dressing and croutons', 'caesar.jpg', 35, 'available', 40.00),
(26, 34, 'Tabbouleh', 'Lebanese parsley and bulgur salad', 'tabbouleh.jpg', 30, 'available', 35.00),
(27, 36, 'Orange Juice', 'Freshly squeezed orange juice', 'orangejuice.jpg', 50, 'available', 20.00),
(28, 36, 'Strawberry Smoothie', 'Fresh strawberry smoothie with yogurt', 'smoothie.jpg', 40, 'available', 30.00),
(29, 38, 'Chicken Tikka Masala', 'Indian chicken curry with rice', 'tikkamasala.jpg', 25, 'available', 70.00),
(30, 38, 'Vegetable Biryani', 'Fragrant rice with mixed vegetables', 'biryani.jpg', 30, 'available', 55.00),
(31, 40, 'Koshari', 'Traditional Egyptian dish with rice, pasta, lentils, and crispy onions', 'koshari2.jpg', 40, 'available', 30.00),
(32, 40, 'Fatta', 'Egyptian dish with rice, bread, meat and garlic vinegar sauce', 'fatta.jpg', 25, 'available', 50.00),
(33, 42, 'Penne Arrabiata', 'Spicy tomato pasta with chili', 'arrabiata.jpg', 35, 'available', 50.00),
(34, 42, 'Lasagna', 'Layered pasta with meat and cheese', 'lasagna.jpg', 20, 'available', 65.00),
(35, 44, 'Beef Teriyaki', 'Japanese beef with teriyaki sauce and rice', 'teriyaki.jpg', 25, 'available', 80.00),
(36, 44, 'Vegetable Tempura', 'Assorted fried vegetables with dipping sauce', 'tempura.jpg', 30, 'available', 45.00),
(37, 46, 'Protein Bowl', 'High-protein meal with chicken and quinoa', 'proteinbowl.jpg', 35, 'available', 55.00),
(38, 46, 'Avocado Toast', 'Whole grain toast with avocado and eggs', 'avocadotoast.jpg', 40, 'available', 40.00),
(39, 48, 'Red Velvet Cake', 'Classic red velvet cake with cream cheese frosting', 'redvelvet.jpg', 15, 'available', 60.00),
(40, 48, 'Cheesecake', 'New York style cheesecake with berry sauce', 'cheesecake.jpg', 10, 'low stock', 65.00),
(41, 50, 'Grilled Prawns', 'Jumbo prawns with garlic butter', 'prawns.jpg', 15, 'available', 180.00),
(42, 50, 'Seafood Paella', 'Spanish rice dish with mixed seafood', 'paella.jpg', 10, 'low stock', 200.00),
(43, 80, 'Stuffed Vine Leaves', 'Traditional Egyptian dish with rice and herbs', 'vineleaves.jpg', 30, 'available', 45.00),
(44, 80, 'Grilled Kofta', 'Egyptian grilled meat with spices', 'kofta.jpg', 40, 'available', 65.00),
(45, 81, 'Fettuccine Alfredo', 'Creamy pasta with parmesan', 'alfredo.jpg', 35, 'available', 75.00),
(46, 81, 'Margherita Pizza', 'Classic Italian pizza', 'pizza_marg.jpg', 25, 'available', 85.00);

-- --------------------------------------------------------

--
-- Table structure for table `meals_in_each_package`
--

CREATE TABLE `meals_in_each_package` (
  `package_meal_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meals_in_each_package`
--

INSERT INTO `meals_in_each_package` (`package_meal_id`, `package_id`, `meal_id`, `quantity`, `price`) VALUES
(1, 1, 5, 2, 120.00),
(2, 2, 6, 1, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `meal_category`
--

CREATE TABLE `meal_category` (
  `meal_id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_category`
--

INSERT INTO `meal_category` (`meal_id`, `cat_id`) VALUES
(1, 1),
(2, 1),
(3, 2),
(4, 2),
(5, 3),
(6, 3),
(7, 4),
(8, 4),
(9, 5),
(10, 5),
(11, 6),
(12, 6),
(13, 7),
(14, 7),
(15, 8),
(16, 8),
(17, 9),
(18, 5),
(18, 9),
(19, 10),
(20, 10),
(21, 1),
(21, 11),
(22, 1),
(22, 11),
(23, 12),
(24, 12),
(25, 13),
(26, 13),
(27, 14),
(28, 14),
(29, 15),
(30, 15),
(31, 1),
(32, 1),
(33, 2),
(34, 2),
(35, 3),
(36, 3),
(37, 4),
(38, 4),
(38, 11),
(39, 5),
(40, 5),
(41, 6),
(42, 6);

-- --------------------------------------------------------

--
-- Table structure for table `meal_subcategory`
--

CREATE TABLE `meal_subcategory` (
  `meal_id` int(11) NOT NULL,
  `subcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meal_subcategory`
--

INSERT INTO `meal_subcategory` (`meal_id`, `subcat_id`) VALUES
(1, 1),
(2, 2),
(3, 5),
(4, 6),
(5, 9),
(6, 8),
(9, 14),
(10, 14);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `kitchen_order_status` enum('preparing','ready_for_delivery','delivered','new') DEFAULT 'new',
  `total_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `ord_type` enum('customized','normal','scheduled') NOT NULL,
  `delivery_type` enum('all_at_once','daily_delivery') DEFAULT NULL,
  `delivery_date` timestamp NULL DEFAULT NULL,
  `order_status` enum('pending','in_progress','delivered','cancelled') DEFAULT 'pending',
  `customer_selected_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `cloud_kitchen_id`, `kitchen_order_status`, `total_price`, `order_date`, `ord_type`, `delivery_type`, `delivery_date`, `order_status`, `customer_selected_date`) VALUES
(1, 2, 3, 'delivered', 80.00, '2025-05-01 07:00:00', 'normal', 'all_at_once', '2025-05-01 09:30:00', 'delivered', NULL),
(2, 4, 5, 'delivered', 120.00, '2025-05-02 08:30:00', 'normal', 'all_at_once', '2025-05-02 11:00:00', 'delivered', NULL),
(3, 6, 7, 'delivered', 180.00, '2025-05-03 09:45:00', 'normal', 'all_at_once', '2025-05-03 12:15:00', 'delivered', NULL),
(4, 8, 9, 'delivered', 85.00, '2025-05-04 06:15:00', 'normal', 'all_at_once', '2025-05-04 08:45:00', 'delivered', NULL),
(5, 10, 18, 'delivered', 90.00, '2025-05-05 11:30:00', 'normal', 'all_at_once', '2025-05-05 14:00:00', 'delivered', NULL),
(6, 16, 20, 'delivered', 230.00, '2025-05-06 13:20:00', 'normal', 'all_at_once', '2025-05-06 15:50:00', 'delivered', NULL),
(7, 17, 22, 'ready_for_delivery', 105.00, '2025-05-07 05:30:00', 'normal', 'all_at_once', '2025-05-07 08:00:00', 'pending', NULL),
(8, 19, 24, 'delivered', 85.00, '2025-05-08 09:15:00', 'normal', 'all_at_once', '2025-05-08 11:45:00', 'delivered', NULL),
(9, 21, 26, 'delivered', 85.00, '2025-05-09 11:00:00', 'normal', 'all_at_once', '2025-05-09 13:30:00', 'delivered', NULL),
(10, 23, 28, 'delivered', 80.00, '2025-05-10 06:30:00', 'normal', 'all_at_once', '2025-05-10 09:00:00', 'delivered', NULL),
(11, 25, 30, 'delivered', 55.00, '2025-05-11 08:20:00', 'normal', 'all_at_once', '2025-05-11 10:50:00', 'delivered', NULL),
(12, 27, 32, 'delivered', 110.00, '2025-05-12 12:10:00', 'normal', 'all_at_once', '2025-05-12 14:40:00', 'delivered', NULL),
(13, 29, 34, 'delivered', 75.00, '2025-05-13 07:25:00', 'normal', 'all_at_once', '2025-05-13 09:55:00', 'delivered', NULL),
(14, 31, 36, 'delivered', 50.00, '2025-05-14 10:40:00', 'normal', 'all_at_once', '2025-05-14 13:10:00', 'delivered', NULL),
(15, 33, 38, 'delivered', 125.00, '2025-05-15 13:55:00', 'normal', 'all_at_once', '2025-05-15 16:25:00', 'delivered', NULL),
(16, 35, 40, 'delivered', 80.00, '2025-05-16 06:05:00', 'normal', 'all_at_once', '2025-05-16 08:35:00', 'delivered', NULL),
(17, 37, 42, 'delivered', 115.00, '2025-05-17 09:30:00', 'normal', 'all_at_once', '2025-05-17 12:00:00', 'delivered', NULL),
(18, 39, 44, 'delivered', 125.00, '2025-05-18 11:45:00', 'normal', 'all_at_once', '2025-05-18 14:15:00', 'delivered', NULL),
(19, 41, 46, 'delivered', 95.00, '2025-05-19 14:00:00', 'normal', 'all_at_once', '2025-05-19 16:30:00', 'delivered', NULL),
(20, 43, 48, 'delivered', 125.00, '2025-05-20 07:15:00', 'normal', 'all_at_once', '2025-05-20 09:45:00', 'delivered', NULL),
(21, 45, 50, 'preparing', 380.00, '2025-05-21 10:30:00', 'normal', 'all_at_once', '2025-05-21 13:00:00', 'in_progress', NULL),
(22, 47, 20, 'ready_for_delivery', 80.00, '2025-05-22 05:45:00', 'normal', 'all_at_once', '2025-05-22 08:15:00', 'in_progress', NULL),
(23, 49, 5, 'new', 175.00, '2025-05-22 07:20:00', 'normal', 'all_at_once', '2025-05-22 09:50:00', 'pending', NULL),
(24, 2, 7, 'new', 240.00, '2025-05-22 08:55:00', 'scheduled', 'daily_delivery', '2025-05-23 10:00:00', 'delivered', NULL),
(25, 4, 9, 'new', 130.00, '2025-05-22 10:30:00', 'customized', NULL, '2025-05-22 13:00:00', 'delivered', NULL),
(26, 82, 80, 'delivered', 110.00, '2025-06-02 19:02:45', 'normal', 'all_at_once', NULL, 'delivered', NULL),
(27, 83, 81, 'delivered', 160.00, '2025-06-02 19:02:45', 'normal', 'all_at_once', NULL, 'delivered', NULL),
(29, 85, 81, 'delivered', 85.00, '2025-06-02 19:02:45', 'normal', 'all_at_once', NULL, 'delivered', NULL),
(30, 86, 80, 'delivered', 130.00, '2025-06-02 19:02:45', 'normal', 'all_at_once', NULL, 'delivered', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_content`
--

CREATE TABLE `order_content` (
  `order_content_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `meal_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_content`
--

INSERT INTO `order_content` (`order_content_id`, `order_id`, `meal_id`, `quantity`, `price`) VALUES
(1, 1, 1, 2, 70.00),
(2, 1, 2, 1, 45.00),
(3, 2, 3, 1, 55.00),
(4, 2, 4, 1, 65.00),
(5, 3, 5, 2, 120.00),
(6, 3, 6, 1, 120.00),
(7, 4, 7, 1, 40.00),
(8, 4, 8, 1, 45.00),
(9, 5, 9, 1, 50.00),
(10, 5, 10, 1, 40.00),
(11, 6, 11, 1, 150.00),
(12, 6, 12, 1, 80.00),
(13, 7, 13, 1, 60.00),
(14, 7, 14, 1, 45.00),
(15, 8, 15, 1, 50.00),
(16, 8, 16, 1, 35.00),
(17, 9, 17, 1, 55.00),
(18, 9, 18, 1, 30.00),
(19, 10, 19, 1, 45.00),
(20, 10, 20, 1, 35.00),
(21, 11, 21, 2, 50.00),
(22, 11, 22, 1, 30.00),
(23, 12, 23, 1, 65.00),
(24, 12, 24, 1, 45.00),
(25, 13, 25, 1, 40.00),
(26, 13, 26, 1, 35.00),
(27, 14, 27, 2, 40.00),
(28, 14, 28, 1, 30.00),
(29, 15, 29, 1, 70.00),
(30, 15, 30, 1, 55.00),
(31, 16, 31, 2, 60.00),
(32, 16, 32, 1, 50.00),
(33, 17, 33, 1, 50.00),
(34, 17, 34, 1, 65.00),
(35, 18, 35, 1, 80.00),
(36, 18, 36, 1, 45.00),
(37, 19, 37, 1, 55.00),
(38, 19, 38, 1, 40.00),
(39, 20, 39, 1, 60.00),
(40, 20, 40, 1, 65.00),
(41, 21, 41, 1, 180.00),
(42, 21, 42, 1, 200.00),
(43, 22, 1, 2, 70.00),
(44, 22, 2, 1, 45.00),
(45, 23, 3, 1, 55.00),
(46, 23, 4, 2, 130.00),
(47, 24, 5, 2, 120.00),
(48, 24, 6, 1, 120.00),
(49, 25, 7, 2, 80.00),
(50, 25, 8, 1, 45.00),
(51, 26, 43, 2, 90.00),
(52, 26, 44, 1, 65.00),
(53, 27, 45, 1, 75.00),
(54, 27, 46, 1, 85.00),
(56, 29, 46, 1, 85.00),
(57, 30, 44, 2, 130.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_packages`
--

CREATE TABLE `order_packages` (
  `package_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `package_name` varchar(255) NOT NULL,
  `delivery_date` date NOT NULL,
  `package_price` decimal(10,2) NOT NULL,
  `package_status` enum('pending','preparing','ready_for_pickup','in_transit','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_packages`
--

INSERT INTO `order_packages` (`package_id`, `order_id`, `package_name`, `delivery_date`, `package_price`, `package_status`, `payment_status`) VALUES
(1, 24, 'Day 1', '2025-05-23', 120.00, 'pending', 'pending'),
(2, 24, 'Day 2', '2025-05-24', 120.00, 'pending', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `total_ord_price` decimal(10,2) NOT NULL,
  `delivery_fees` decimal(10,2) NOT NULL,
  `website_revenue` decimal(10,2) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `p_date_time` datetime NOT NULL,
  `p_method` enum('cash','visa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_details`
--

INSERT INTO `payment_details` (`payment_id`, `order_id`, `total_ord_price`, `delivery_fees`, `website_revenue`, `total_payment`, `p_date_time`, `p_method`) VALUES
(1, 1, 80.00, 15.00, 8.00, 103.00, '2025-05-01 10:05:00', 'visa'),
(2, 2, 120.00, 20.00, 12.00, 152.00, '2025-05-02 11:35:00', 'cash'),
(3, 3, 180.00, 25.00, 18.00, 223.00, '2025-05-03 12:50:00', 'visa'),
(4, 4, 85.00, 15.00, 8.50, 108.50, '2025-05-04 09:20:00', 'cash'),
(5, 5, 90.00, 15.00, 9.00, 114.00, '2025-05-05 14:35:00', 'visa'),
(6, 6, 230.00, 30.00, 23.00, 283.00, '2025-05-06 16:25:00', 'cash'),
(7, 7, 105.00, 15.00, 10.50, 130.50, '2025-05-07 08:35:00', 'visa'),
(8, 8, 85.00, 15.00, 8.50, 108.50, '2025-05-08 12:20:00', 'cash'),
(9, 9, 85.00, 15.00, 8.50, 108.50, '2025-05-09 14:05:00', 'visa'),
(10, 10, 80.00, 15.00, 8.00, 103.00, '2025-05-10 09:35:00', 'cash');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_no` int(11) NOT NULL,
  `stars` tinyint(3) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `cloud_kitchen_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_no`, `stars`, `order_id`, `cloud_kitchen_id`, `customer_id`, `review_date`) VALUES
(1, 5, 1, 3, 2, '2025-05-01 11:00:00'),
(2, 4, 2, 5, 4, '2025-05-02 12:30:00'),
(3, 5, 3, 7, 6, '2025-05-03 13:45:00'),
(4, 3, 4, 9, 8, '2025-05-04 10:15:00'),
(5, 4, 5, 18, 10, '2025-05-05 15:30:00'),
(6, 5, 6, 20, 16, '2025-05-06 17:00:00'),
(7, 4, 7, 22, 17, '2025-05-07 09:30:00'),
(8, 3, 8, 24, 19, '2025-05-08 13:15:00'),
(9, 5, 9, 26, 21, '2025-05-09 15:00:00'),
(10, 4, 10, 28, 23, '2025-05-10 10:30:00'),
(11, 5, 11, 30, 25, '2025-05-11 12:20:00'),
(12, 3, 12, 32, 27, '2025-05-12 16:10:00'),
(13, 4, 13, 34, 29, '2025-05-13 11:25:00'),
(14, 5, 14, 36, 31, '2025-05-14 14:40:00'),
(15, 4, 15, 38, 33, '2025-05-15 17:45:00'),
(16, 3, 16, 40, 35, '2025-05-16 10:05:00'),
(17, 5, 17, 42, 37, '2025-05-17 13:30:00'),
(18, 4, 18, 44, 39, '2025-05-18 15:45:00'),
(19, 5, 19, 46, 41, '2025-05-19 18:00:00'),
(20, 3, 20, 48, 43, '2025-05-20 11:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `route_cache`
--

CREATE TABLE `route_cache` (
  `id` int(11) NOT NULL,
  `start_lat` decimal(10,8) NOT NULL,
  `start_lng` decimal(11,8) NOT NULL,
  `end_lat` decimal(10,8) NOT NULL,
  `end_lng` decimal(11,8) NOT NULL,
  `vehicle_type` varchar(20) DEFAULT 'car',
  `route_data` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_category`
--

CREATE TABLE `sub_category` (
  `subcat_id` int(11) NOT NULL,
  `subcat_name` varchar(100) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `parent_cat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sub_category`
--

INSERT INTO `sub_category` (`subcat_id`, `subcat_name`, `admin_id`, `parent_cat_id`) VALUES
(1, 'Siani Bedouin', 1, 1),
(2, 'Red Sea Seafood', 1, 1),
(3, 'Festive Food', 1, 1),
(4, 'Upper Egypt', 1, 1),
(5, 'Pasta', 1, 2),
(6, 'Pizza', 1, 2),
(7, 'Risotto', 1, 2),
(8, 'Sushi', 1, 3),
(9, 'Pad Thai', 1, 3),
(10, 'Dim Sum', 1, 3),
(14, 'Cakes', 1, 5),
(16, 'Ice Cream', 1, 5),
(17, 'Grilled Fish', 1, 6),
(18, 'Fried Fish', 1, 6),
(19, 'Seafood Platter', 1, 6),
(20, 'Grilled Chicken', 1, 7),
(21, 'Grilled Meat', 1, 7),
(22, 'Grilled Vegetables', 1, 7),
(27, 'Greek', 1, 10),
(28, 'Turkish', 1, 10),
(29, 'Lebanese', 1, 10),
(30, 'Egyptian Breakfast', 1, 11),
(31, 'Continental Breakfast', 1, 11),
(32, 'Burgers', 1, 12),
(33, 'Subs', 1, 12),
(34, 'Wraps', 1, 12),
(35, 'Greek Salad', 1, 13),
(36, 'Caesar Salad', 1, 13),
(37, 'Fresh Juices', 1, 14),
(38, 'Smoothies', 1, 14),
(39, 'Mexican', 1, 15),
(40, 'Indian', 1, 15);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `u_name` varchar(100) NOT NULL,
  `mail` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `u_role` enum('admin','external_user','delivery_man') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `u_name`, `mail`, `phone`, `password`, `reset_token`, `reset_token_expiry`, `u_role`, `created_at`, `last_login`) VALUES
(1, 'Admin User', 'admin@todaymeal.com', '01012345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'admin', '2025-05-31 15:04:27', NULL),
(2, 'Mohamed Ali', 'mohamed.ali@example.com', '01011223344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(3, 'Ahmed Hassan', 'ahmed.hassan@example.com', '01022334455', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(4, 'Fatma Mahmoud', 'fatma.mahmoud@example.com', '01033445566', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(5, 'Youssef Ibrahim', 'youssef.ibrahim@example.com', '01044556677', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(6, 'Aya Mohamed', 'aya.mohamed@example.com', '01055667788', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(7, 'Omar Khaled', 'omar.khaled@example.com', '01066778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(8, 'Hana Samir', 'hana.samir@example.com', '01077889900', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(9, 'Karim Adel', 'karim.adel@example.com', '01088990011', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(10, 'Nourhan Wael', 'nourhan.wael@example.com', '01099001122', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(11, 'Delivery Man 1', 'delivery1@todaymeal.com', '01112345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', '2025-06-02 17:38:53'),
(12, 'Delivery Man 2', 'delivery2@todaymeal.com', '01123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(13, 'Delivery Man 3', 'delivery3@todaymeal.com', '01134567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(14, 'Delivery Man 4', 'delivery4@todaymeal.com', '01145678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(15, 'Delivery Man 5', 'delivery5@todaymeal.com', '01156789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'delivery_man', '2025-05-31 15:04:27', NULL),
(16, 'Ali Mohamed', 'ali.mohamed@example.com', '01212345678', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(17, 'Mona Ahmed', 'mona.ahmed@example.com', '01223456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(18, 'Tamer Hosny', 'tamer.hosny@example.com', '01234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(19, 'Dalia Magdy', 'dalia.magdy@example.com', '01245678901', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(20, 'Wael Khedr', 'wael.khedr@example.com', '01256789012', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(21, 'Rania Said', 'rania.said@example.com', '01267890123', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(22, 'Sherif Gamal', 'sherif.gamal@example.com', '01278901234', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(23, 'Heba Ali', 'heba.ali@example.com', '01289012345', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(24, 'Amr Diab', 'amr.diab@example.com', '01290123456', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(25, 'Nada Mostafa', 'nada.mostafa@example.com', '01011223399', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(26, 'Khaled ElNabawy', 'khaled.elnabawy@example.com', '01022334499', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(27, 'Samira Ahmed', 'samira.ahmed@example.com', '01033445599', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(28, 'Mahmoud Yassin', 'mahmoud.yassin@example.com', '01044556699', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(29, 'Laila Elwi', 'laila.elwi@example.com', '01055667799', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(30, 'Hassan Hosny', 'hassan.hosny@example.com', '01066778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(31, 'Yasmin Abdelaziz', 'yasmin.abdelaziz@example.com', '01077889999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(32, 'Tarek Lotfy', 'tarek.lotfy@example.com', '01088990099', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(33, 'Mai Kassab', 'mai.kassab@example.com', '01099001199', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(34, 'Adel Emam', 'adel.emam@example.com', '01112345699', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(35, 'Mervat Amin', 'mervat.amin@example.com', '01123456799', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(36, 'Hany Ramzy', 'hany.ramzy@example.com', '01134567899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(37, 'Nelly Karim', 'nelly.karim@example.com', '01145678999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(38, 'Ahmed Helmy', 'ahmed.helmy@example.com', '01156789099', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(39, 'Donia Samir', 'donia.samir@example.com', '01167890199', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(40, 'Mohamed Henedy', 'mohamed.henedy@example.com', '01178901299', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(41, 'Mona Zaki', 'mona.zaki@example.com', '01189012399', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(42, 'Karim Abdel Aziz', 'karim.abdelaziz@example.com', '01190123499', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(43, 'Yousra', 'yousra@example.com', '01211223399', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(44, 'Sherihan', 'sherihan@example.com', '01222334499', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(45, 'Hani Salama', 'hani.salama@example.com', '01233445599', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(46, 'Sawsan Badr', 'sawsan.badr@example.com', '01244556699', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(47, 'Ahmed Rizk', 'ahmed.rizk@example.com', '01255667799', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(48, 'Lebleba', 'lebleba@example.com', '01266778899', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(49, 'Ezzat El Alaily', 'ezzat.alaily@example.com', '01277889999', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(50, 'Fifi Abdou', 'fifi.abdou@example.com', '01288990099', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-05-31 15:04:27', NULL),
(54, 'DEL20251002', 'ahmed@delivery.com', '+201272377148', '$2y$10$oSEyaiaThOIY6/Vo8o0dX.thRxQz2XhV6nmiG0nr4s65GVAqcxa3O', NULL, NULL, 'delivery_man', '2025-06-01 08:32:23', '2025-06-01 11:37:49'),
(55, 'hussein', 'hu@delivery.com', '+201172377189', '$2y$10$RFkY3tpGui81OFDsRvU.x.Tn5ymtQ32Zlwb4TTKWCNnHe8A1HmPmO', NULL, NULL, 'delivery_man', '2025-06-01 12:30:12', NULL),
(58, 'hussein 2', 'hue@delivery.com', '+201172377189', '$2y$10$sofxCH998258KJMfFZ724OfHeym0z/nOBOdwVvEvbM1MWruHjONtK', NULL, NULL, 'delivery_man', '2025-06-01 12:32:07', NULL),
(60, 'ali', 'ali@gmail.com', '+2012333678', '$2y$10$ycbh4XMXtlXupnxanrLpuu9XYmc2bdceXBds6m94hBs/4QYCkJNiS', NULL, NULL, 'delivery_man', '2025-06-01 12:37:09', NULL),
(63, 'aliali', 'aliali@gmail.com', '+2012777678', '$2y$10$vU0GGIj9OKj5LC/GTOLrC.XYagnX5Hf8.d2yJHT7BHi0llLjwSxdW', NULL, NULL, 'delivery_man', '2025-06-01 12:39:08', NULL),
(65, 'aliali ali', 'alialiali@gmail.com', '+2012777778', '$2y$10$VnHOAXb5I7wXbu6pWUXVSOHptlSsYPOyEQCZ/iGsWkV7HEOZf8ZNi', NULL, NULL, 'delivery_man', '2025-06-01 13:02:17', NULL),
(66, 'fff', 'fff@gmail', '+201277776', '$2y$10$TvNwvVaxor1akZUrwKtBSugoHaFMFSza6/JNla2NCmouRUhVQaL32', NULL, NULL, 'delivery_man', '2025-06-01 13:26:31', NULL),
(68, 'fffnnn', 'fffnn@gmail', '+201277777', '$2y$10$sWeOMyLWKQNLEmMKrlcgqOUIZeZb/yOnZMh/yJlUfriBmMDpHsoDq', NULL, NULL, 'delivery_man', '2025-06-01 13:36:11', '2025-06-01 13:37:38'),
(69, 'name', 'name@gmail.com', '01230123012', '$2y$10$gSoFwIVCvvU5v0i7dWua.u9AUvyBTLKgv1gn.5OeYIbG0/A3s90X.', NULL, NULL, 'delivery_man', '2025-06-01 13:56:01', '2025-06-02 15:00:51'),
(70, 'na', 'na@gmail.com', '012012012', '$2y$10$.BsPwzHZED0FNNB6JsMhtudeHAS.08b0x5ZCkmEvXIN/Z4fK54LoS', NULL, NULL, 'delivery_man', '2025-06-02 14:54:21', NULL),
(72, 'nana', 'naa@gmail.com', '012012013', '$2y$10$VoAr7RaNAwjWGjQ5LoUyhuPJKQwpkzodTK1bPjM2GpIQbs7tr4nVK', NULL, NULL, 'delivery_man', '2025-06-02 14:56:16', NULL),
(73, 'Hussein Ali', 'ha@gmail.com', '+201277779', '$2y$10$GoD1dgkYatKTMPXpix6wIOWUMy/ocfbVEYxBUMCkQAFHvwG0VCT9q', NULL, NULL, 'delivery_man', '2025-06-02 15:21:42', NULL),
(75, 'Hussein Ali', 'haaa@gmail.com', '+201277773', '$2y$10$klcKx9TjXnNeL4X6K.AR/eC4mi8JP5fPotCDYezechsjpNiHZ2.zq', NULL, NULL, 'delivery_man', '2025-06-02 16:40:39', NULL),
(76, 'Hussein Ali', 'haaaa@gmail.com', '+201277779', '$2y$10$H5kaKrJUJlZPSNQtFoA0eOJQ./5eP8A3DFY8.ByQMSyF2JuaGmyly', NULL, NULL, 'delivery_man', '2025-06-02 16:58:46', NULL),
(80, 'Cairo Delights', 'cairo.delights@example.com', '01055550001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:01:20', NULL),
(81, 'Nile Bites', 'nile.bites@example.com', '01055550002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:01:20', NULL),
(82, 'Omar Hassan', 'omar.hassan@example.com', '01066660001', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(83, 'Laila Ahmed', 'laila.ahmed@example.com', '01066660002', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(84, 'Karim Mohamed', 'karim.mohamed@example.com', '01066660003', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(85, 'Nadia Samir', 'nadia.samir@example.com', '01066660004', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(86, 'Youssef Ali', 'youssef.ali@example.com', '01066660005', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'external_user', '2025-06-02 19:02:45', NULL),
(87, 'hany', 'hany@gmail.com', '+20127557988', '$2y$10$ozvHnjykXeZQZljuI1dICeGAuJKHU2Evrhxb/AfBa2/ltQSqcgBXi', NULL, NULL, 'delivery_man', '2025-06-02 19:03:45', '2025-06-03 12:06:27'),
(88, 'name 2', 'name2@gmail.com', '+201272477147', '$2y$10$UDX1IDaLPLelpczUGgPC6u6NrJNWbpFXBgcP7xVjnKNDgGeKh5CkG', NULL, NULL, 'delivery_man', '2025-06-03 10:44:59', '2025-06-03 10:46:25'),
(89, 'name', 'gg@gmail.com', '01225255588', '$2y$10$7fDVzQyBKHmZG8NVk3ajze2xIWJi8i9LDro2.NhKM3IEmhuJP3CpS', NULL, NULL, 'delivery_man', '2025-06-03 17:28:26', '2025-06-03 17:31:04'),
(90, 'customer1', 'customer1@gmail.com', '127777988', '$2y$10$IXJEga0.r0rhTCm9mXURfOxGFrFk7bPTQGHATmCPkg55661dCbYbm', NULL, NULL, 'external_user', '2025-06-04 11:02:10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `zone_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `zones`
--

INSERT INTO `zones` (`zone_id`, `name`) VALUES
(2, '5th settlement'),
(3, 'maadi'),
(1, 'sheikh Zayed'),
(0, 'test'),
(4, 'zamalek');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`action_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`),
  ADD UNIQUE KEY `c_name` (`c_name`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `caterer_tags`
--
ALTER TABLE `caterer_tags`
  ADD KEY `user_id` (`user_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `cloud_kitchen_owner`
--
ALTER TABLE `cloud_kitchen_owner`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `speciality_id` (`speciality_id`);

--
-- Indexes for table `cloud_kitchen_specialist_category`
--
ALTER TABLE `cloud_kitchen_specialist_category`
  ADD PRIMARY KEY (`cloud_kitchen_id`,`cat_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `kitchen_id` (`kitchen_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `customized_order`
--
ALTER TABLE `customized_order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `kitchen_id` (`kitchen_id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_person_id` (`delivery_person_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `provider_id` (`provider_id`);

--
-- Indexes for table `delivery_man`
--
ALTER TABLE `delivery_man`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `d_license` (`d_license`),
  ADD UNIQUE KEY `d_n_id` (`d_n_id`),
  ADD KEY `fk_zone` (`zone_id`);

--
-- Indexes for table `delivery_status_history`
--
ALTER TABLE `delivery_status_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Indexes for table `delivery_subscriptions`
--
ALTER TABLE `delivery_subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`);

--
-- Indexes for table `delivery_tokens`
--
ALTER TABLE `delivery_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`,`type`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `dietary_tags`
--
ALTER TABLE `dietary_tags`
  ADD PRIMARY KEY (`tag_id`),
  ADD UNIQUE KEY `tag_name` (`tag_name`);

--
-- Indexes for table `external_user`
--
ALTER TABLE `external_user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `fk_external_user_zone` (`zone_id`);

--
-- Indexes for table `meals`
--
ALTER TABLE `meals`
  ADD PRIMARY KEY (`meal_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`);

--
-- Indexes for table `meals_in_each_package`
--
ALTER TABLE `meals_in_each_package`
  ADD PRIMARY KEY (`package_meal_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `meal_category`
--
ALTER TABLE `meal_category`
  ADD PRIMARY KEY (`meal_id`,`cat_id`),
  ADD KEY `cat_id` (`cat_id`);

--
-- Indexes for table `meal_subcategory`
--
ALTER TABLE `meal_subcategory`
  ADD PRIMARY KEY (`meal_id`,`subcat_id`),
  ADD KEY `subcat_id` (`subcat_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`);

--
-- Indexes for table `order_content`
--
ALTER TABLE `order_content`
  ADD PRIMARY KEY (`order_content_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `meal_id` (`meal_id`);

--
-- Indexes for table `order_packages`
--
ALTER TABLE `order_packages`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_no`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `cloud_kitchen_id` (`cloud_kitchen_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `route_cache`
--
ALTER TABLE `route_cache`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_coordinates` (`start_lat`,`start_lng`,`end_lat`,`end_lng`,`vehicle_type`);

--
-- Indexes for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD PRIMARY KEY (`subcat_id`),
  ADD KEY `parent_cat_id` (`parent_cat_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `mail` (`mail`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`zone_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `action_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `delivery_status_history`
--
ALTER TABLE `delivery_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `delivery_subscriptions`
--
ALTER TABLE `delivery_subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_tokens`
--
ALTER TABLE `delivery_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dietary_tags`
--
ALTER TABLE `dietary_tags`
  MODIFY `tag_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `meals`
--
ALTER TABLE `meals`
  MODIFY `meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `meals_in_each_package`
--
ALTER TABLE `meals_in_each_package`
  MODIFY `package_meal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_content`
--
ALTER TABLE `order_content`
  MODIFY `order_content_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `order_packages`
--
ALTER TABLE `order_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_no` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `route_cache`
--
ALTER TABLE `route_cache`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_category`
--
ALTER TABLE `sub_category`
  MODIFY `subcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `category_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`user_id`);

--
-- Constraints for table `caterer_tags`
--
ALTER TABLE `caterer_tags`
  ADD CONSTRAINT `caterer_tags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `caterer_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `dietary_tags` (`tag_id`) ON DELETE CASCADE;

--
-- Constraints for table `cloud_kitchen_owner`
--
ALTER TABLE `cloud_kitchen_owner`
  ADD CONSTRAINT `cloud_kitchen_owner_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `external_user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cloud_kitchen_owner_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `external_user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cloud_kitchen_owner_ibfk_3` FOREIGN KEY (`speciality_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE;

--
-- Constraints for table `cloud_kitchen_specialist_category`
--
ALTER TABLE `cloud_kitchen_specialist_category`
  ADD CONSTRAINT `cloud_kitchen_specialist_category_ibfk_1` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`),
  ADD CONSTRAINT `cloud_kitchen_specialist_category_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `customer`
--
ALTER TABLE `customer`
  ADD CONSTRAINT `customer_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `external_user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `customized_order`
--
ALTER TABLE `customized_order`
  ADD CONSTRAINT `customized_order_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customized_order_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customized_order_ibfk_3` FOREIGN KEY (`kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`delivery_person_id`) REFERENCES `delivery_man` (`user_id`),
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`),
  ADD CONSTRAINT `deliveries_ibfk_3` FOREIGN KEY (`provider_id`) REFERENCES `cloud_kitchen_owner` (`user_id`);

--
-- Constraints for table `delivery_man`
--
ALTER TABLE `delivery_man`
  ADD CONSTRAINT `delivery_man_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery_status_history`
--
ALTER TABLE `delivery_status_history`
  ADD CONSTRAINT `delivery_status_history_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_subscriptions`
--
ALTER TABLE `delivery_subscriptions`
  ADD CONSTRAINT `delivery_subscriptions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_tokens`
--
ALTER TABLE `delivery_tokens`
  ADD CONSTRAINT `delivery_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `external_user`
--
ALTER TABLE `external_user`
  ADD CONSTRAINT `external_user_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `external_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_external_user_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`zone_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `meals`
--
ALTER TABLE `meals`
  ADD CONSTRAINT `meals_ibfk_1` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`),
  ADD CONSTRAINT `meals_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `meals_in_each_package`
--
ALTER TABLE `meals_in_each_package`
  ADD CONSTRAINT `meals_in_each_package_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `order_packages` (`package_id`),
  ADD CONSTRAINT `meals_in_each_package_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `meal_category`
--
ALTER TABLE `meal_category`
  ADD CONSTRAINT `meal_category_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`),
  ADD CONSTRAINT `meal_category_ibfk_2` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`);

--
-- Constraints for table `meal_subcategory`
--
ALTER TABLE `meal_subcategory`
  ADD CONSTRAINT `meal_subcategory_ibfk_1` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`),
  ADD CONSTRAINT `meal_subcategory_ibfk_2` FOREIGN KEY (`subcat_id`) REFERENCES `sub_category` (`subcat_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`);

--
-- Constraints for table `order_content`
--
ALTER TABLE `order_content`
  ADD CONSTRAINT `order_content_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_content_ibfk_2` FOREIGN KEY (`meal_id`) REFERENCES `meals` (`meal_id`);

--
-- Constraints for table `order_packages`
--
ALTER TABLE `order_packages`
  ADD CONSTRAINT `order_packages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`cloud_kitchen_id`) REFERENCES `cloud_kitchen_owner` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`user_id`);

--
-- Constraints for table `sub_category`
--
ALTER TABLE `sub_category`
  ADD CONSTRAINT `sub_category_ibfk_1` FOREIGN KEY (`parent_cat_id`) REFERENCES `category` (`cat_id`),
  ADD CONSTRAINT `sub_category_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
