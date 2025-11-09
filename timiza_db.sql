-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2025 at 07:06 AM
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
-- Database: `timiza_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `email`, `full_name`, `active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@timizayouth.org', 'System Administrator', 1, '2025-10-27 07:27:18', '2025-10-27 07:27:18');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `status`, `created_at`) VALUES
(1, 'Alice Njeri', 'alice.njeri@email.com', 'Partnership Inquiry', 'Hello, I represent a local NGO and would like to explore partnership opportunities with TYI. Please contact me to discuss further.', 'read', '2025-10-27 07:27:20'),
(2, 'Robert Kiprotich', 'robert.k@email.com', 'Volunteer Application Follow-up', 'I submitted a volunteer application last week and wanted to follow up on the status. Looking forward to contributing to your programs.', 'read', '2025-10-27 07:27:20'),
(3, 'Susan Wambui', 'susan.wambui@email.com', 'Program Information', 'Could you please provide more information about your climate action program? I am interested in participating.', 'read', '2025-10-27 07:27:20');

-- --------------------------------------------------------

--
-- Table structure for table `contact_message_replies`
--

CREATE TABLE `contact_message_replies` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `sent_success` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_message_replies`
--

INSERT INTO `contact_message_replies` (`id`, `message_id`, `subject`, `body`, `sent_success`, `created_at`) VALUES
(1, 1, 'Re: Partnership Inquiry', 'hi', 0, '2025-10-27 13:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(10) UNSIGNED NOT NULL,
  `donor_name` varchar(150) NOT NULL,
  `donor_email` varchar(150) DEFAULT NULL,
  `donor_phone` varchar(50) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'KES',
  `provider` enum('mpesa','payhero') NOT NULL,
  `provider_channel` varchar(50) DEFAULT NULL,
  `status` enum('pending','initiated','processing','completed','failed','cancelled') DEFAULT 'pending',
  `reference` varchar(40) NOT NULL,
  `provider_reference` varchar(80) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_name`, `donor_email`, `donor_phone`, `amount`, `currency`, `provider`, `provider_channel`, `status`, `reference`, `provider_reference`, `meta`, `created_at`, `updated_at`) VALUES
(1, 'BRIAN KIPLANGAT', NULL, '0795701071', 1.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027135631346', NULL, NULL, '2025-10-27 12:56:31', '2025-10-27 12:56:31'),
(2, 'BRIAN KIPLANGAT', NULL, '0795701071', 2.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027145527679', NULL, NULL, '2025-10-27 13:55:27', '2025-10-27 13:55:27'),
(3, 'BRIAN KIPLANGAT', NULL, '0795701071', 2.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027151003725', NULL, NULL, '2025-10-27 14:10:03', '2025-10-27 14:10:03'),
(4, 'BRIAN KIPLANGAT', NULL, '0795701071', 15.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027151043662', NULL, NULL, '2025-10-27 14:10:43', '2025-10-27 14:10:43'),
(5, 'BRIAN KIPLANGAT', NULL, '0795701071', 15.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027151846385', NULL, NULL, '2025-10-27 14:18:46', '2025-10-27 14:18:46'),
(6, 'BRIAN KIPLANGAT', NULL, '0795701071', 5.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027151915619', NULL, NULL, '2025-10-27 14:19:15', '2025-10-27 14:19:15'),
(7, 'BRIAN KIPLANGAT', NULL, '0795701071', 5.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027152558468', NULL, NULL, '2025-10-27 14:25:58', '2025-10-27 14:25:58'),
(8, 'BRIAN KIPLANGAT', NULL, '0795701071', 5.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027152622806', NULL, NULL, '2025-10-27 14:26:22', '2025-10-27 14:26:22'),
(9, 'BRIAN KIPLANGAT', NULL, '0795701071', 6333.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027152648247', NULL, NULL, '2025-10-27 14:26:48', '2025-10-27 14:26:48'),
(10, 'BRIAN KIPLANGAT', NULL, '0795701071', 6.00, 'KES', 'mpesa', 'till', 'pending', 'TYI20251027152717335', NULL, NULL, '2025-10-27 14:27:17', '2025-10-27 14:27:17'),
(11, 'BRIAN KIPLANGAT', NULL, '0795701071', 100000.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027152746213', NULL, NULL, '2025-10-27 14:27:46', '2025-10-27 14:27:46'),
(12, 'BRIAN KIPLANGAT', NULL, '0795701071', 100000.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027154100874', NULL, NULL, '2025-10-27 14:41:00', '2025-10-27 14:41:00'),
(13, 'BRIAN KIPLANGAT', NULL, '0795701071', 100000.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251027155415779', NULL, NULL, '2025-10-27 14:54:15', '2025-10-27 14:54:15'),
(14, 'BRIAN KIPLANGAT', NULL, '0795701071', 100000.00, 'KES', 'payhero', NULL, 'pending', 'TYI20251028064546749', NULL, NULL, '2025-10-28 05:45:46', '2025-10-28 05:45:46');

-- --------------------------------------------------------

--
-- Table structure for table `donation_events`
--

CREATE TABLE `donation_events` (
  `id` int(10) UNSIGNED NOT NULL,
  `donation_id` int(10) UNSIGNED NOT NULL,
  `provider` enum('mpesa','payhero') NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `status` varchar(30) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donation_events`
--

INSERT INTO `donation_events` (`id`, `donation_id`, `provider`, `event_type`, `status`, `payload`, `created_at`) VALUES
(1, 1, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero credentials are not configured.\"}', '2025-10-27 12:56:32'),
(2, 2, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\"}', '2025-10-27 13:55:34'),
(3, 3, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\"}', '2025-10-27 14:10:06'),
(4, 4, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\"}', '2025-10-27 14:10:51'),
(5, 5, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\"}', '2025-10-27 14:18:48'),
(6, 6, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\"}', '2025-10-27 14:19:17'),
(7, 7, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_message\":\"Endpoint not found\"}}', '2025-10-27 14:26:07'),
(8, 8, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_message\":\"Endpoint not found\"}}', '2025-10-27 14:26:27'),
(9, 9, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_message\":\"Endpoint not found\"}}', '2025-10-27 14:26:51'),
(10, 10, 'mpesa', 'stk_request', 'failed', '{\"success\":false,\"message\":\"M-Pesa credentials are not configured.\"}', '2025-10-27 14:27:17'),
(11, 11, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_message\":\"Endpoint not found\"}}', '2025-10-27 14:27:48'),
(12, 12, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_message\":\"Endpoint not found\"}}', '2025-10-27 14:41:04'),
(13, 13, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_code\":\"PERMISSION_DENIED\",\"error_message\":\"Merchant Account Inactive\",\"status_code\":500}}', '2025-10-27 14:54:20'),
(14, 14, 'payhero', 'init_request', 'failed', '{\"success\":false,\"message\":\"PayHero request failed\",\"response\":{\"error_code\":\"PERMISSION_DENIED\",\"error_message\":\"Merchant Account Inactive\",\"status_code\":500}}', '2025-10-28 05:45:50');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `event_date` datetime NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `slug`, `description`, `content`, `event_date`, `location`, `image`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Youth Climate Summit 2024', 'youth-climate-summit-2024', 'Annual summit bringing together young climate activists from across Kilifi County', 'Join us for our annual Youth Climate Summit where young people will share ideas, learn from experts, and develop action plans for environmental conservation in their communities.', '2024-03-15 09:00:00', 'Kilifi Community Center', NULL, 'upcoming', '2025-10-27 07:27:22', '2025-10-27 07:27:22'),
(2, 'Health Education Workshop - Malindi', 'health-workshop-malindi', 'Comprehensive health education workshop for youth in Malindi area', 'A day-long workshop covering sexual and reproductive health, gender equality, and life skills for young people aged 15-25.', '2024-02-20 08:30:00', 'Malindi Youth Center', NULL, 'upcoming', '2025-10-27 07:27:22', '2025-10-27 07:27:22');

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `filename`, `original_name`, `caption`, `category`, `active`, `created_at`) VALUES
(1, 'nx-1761562943.png', 'NX.png', 'hi', 'programs', 1, '2025-10-27 11:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'News',
  `author` varchar(100) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `excerpt`, `content`, `image`, `category`, `author`, `status`, `featured`, `created_at`, `updated_at`) VALUES
(1, 'Timiza Youth Initiative Launches Climate Action Program', 'climate-action-program-launch', 'We are excited to announce the launch of our comprehensive climate action program aimed at engaging young people in environmental conservation.', 'Kilifi County, Kenya - Timiza Youth Initiative is proud to announce the launch of our new Climate Action Program, designed to empower young people to become environmental champions in their communities.\n\nThe program includes tree planting initiatives, sustainable agriculture training, and clean energy projects. Over the next year, we aim to plant 10,000 trees and train 500 young farmers in sustainable practices.\n\n\"Climate change is one of the biggest challenges facing our generation,\" said Sarah Mwangi, Executive Director of TYI. \"Through this program, we are giving young people the tools and knowledge they need to make a real difference.\"\n\nThe program is supported by local government and international partners, ensuring its sustainability and impact.', NULL, 'Environment', 'Sarah Mwangi', 'published', 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(2, '100 Youth Complete Health Education Workshop', 'health-education-workshop-completion', 'A milestone achievement as 100 young people successfully complete our comprehensive health education workshop series.', 'This month marked a significant milestone for our Health & Gender program as 100 young people from across Kilifi County completed our comprehensive health education workshop series.\n\nThe workshops covered topics including sexual and reproductive health, gender equality, and prevention of gender-based violence. Participants gained valuable knowledge and skills to make informed decisions about their health and relationships.\n\n\"The knowledge I gained has changed my perspective on many issues,\" said Grace, one of the participants. \"I now feel confident to educate my peers and advocate for our rights.\"\n\nThe program will continue with advanced training for selected participants who will become peer educators in their communities.', NULL, 'Health', 'James Kombe', 'published', 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(3, 'Partnership with Local Schools Strengthens Education Program', 'school-partnership-education', 'TYI partners with 10 local schools to enhance digital literacy and skills training for students.', 'Timiza Youth Initiative has formed partnerships with 10 local schools in Kilifi County to strengthen our Education & Skills program. This collaboration will bring digital literacy training and vocational skills development directly to students.\n\nThe partnership includes provision of computer equipment, training materials, and certified instructors. Over 500 students are expected to benefit from this initiative in the first phase.\n\n\"Education is the foundation of empowerment,\" noted Grace Nyong, Community Outreach Lead. \"By working directly with schools, we can reach more young people and provide them with essential 21st-century skills.\"\n\nThe program will focus on computer literacy, entrepreneurship, and life skills training.', NULL, 'Education', 'Grace Nyong', 'published', 0, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(4, 'hi', 'hi', 'jjjjjjjjj', 'hi', 'assets/images/news/nx-1761559661.png', 'Events', 'admin', 'published', 0, '2025-10-27 09:02:53', '2025-10-27 10:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `content` longtext DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`id`, `name`, `slug`, `description`, `content`, `image`, `icon`, `active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Health & Gender', 'health-gender', 'Promoting sexual and reproductive health rights, gender equality, and addressing gender-based violence through education, advocacy, and community engagement.', 'Our Health & Gender program focuses on empowering young people with knowledge and skills to make informed decisions about their health and relationships. We conduct comprehensive sexuality education workshops, advocate for gender equality, and provide support for survivors of gender-based violence.', NULL, 'fas fa-heartbeat', 1, 1, '2025-10-27 07:27:18', '2025-10-27 07:27:18'),
(2, 'Climate Change', 'climate-change', 'Building climate resilience through environmental conservation, sustainable agriculture, and renewable energy initiatives led by young people.', 'Through our Climate Change program, we engage youth in environmental conservation activities including tree planting, sustainable farming practices, and clean energy projects. We believe young people are key to addressing the climate crisis.', NULL, 'fas fa-leaf', 1, 2, '2025-10-27 07:27:18', '2025-10-27 07:27:18'),
(3, 'Peace & Security', 'peace-security', 'Promoting peaceful coexistence, conflict resolution, and community security through dialogue, mediation training, and peace-building initiatives.', 'Our Peace & Security program trains young people as peace ambassadors and conflict mediators. We facilitate community dialogues and promote inter-community understanding to build lasting peace.', NULL, 'fas fa-dove', 1, 3, '2025-10-27 07:27:18', '2025-10-27 07:27:18'),
(4, 'Disability Inclusion', 'disability-inclusion', 'Ensuring equal opportunities and full participation of persons with disabilities in all aspects of community life.', 'We advocate for the rights of persons with disabilities and work to create inclusive communities where everyone can participate fully in social, economic, and political life.', NULL, 'fas fa-universal-access', 1, 4, '2025-10-27 07:27:18', '2025-10-27 07:27:18'),
(5, 'Education & Skills', 'education-skills', 'Enhancing educational outcomes and building practical skills for economic empowerment and sustainable livelihoods.', 'Our Education & Skills program provides digital literacy training, vocational skills development, and entrepreneurship mentorship to help young people build sustainable livelihoods.', NULL, 'fas fa-graduation-cap', 1, 5, '2025-10-27 07:27:18', '2025-10-27 07:27:18');

-- --------------------------------------------------------

--
-- Table structure for table `volunteers`
--

CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `interest_area` varchar(100) NOT NULL,
  `experience` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `volunteers`
--

INSERT INTO `volunteers` (`id`, `name`, `email`, `phone`, `interest_area`, `experience`, `status`, `notes`, `active`, `created_at`, `updated_at`) VALUES
(1, 'John Mwangi', 'john.mwangi@email.com', '+254712345678', 'Climate Change', 'Environmental science student with passion for conservation', 'approved', NULL, 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(2, 'Mary Kamau', 'mary.kamau@email.com', '+254723456789', 'Health & Gender', 'Community health volunteer with 2 years experience', 'approved', NULL, 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(3, 'David Kombe', 'david.kombe@email.com', '+254734567890', 'Education & Skills', 'IT professional interested in digital literacy training', 'pending', NULL, 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(4, 'Grace Wanjiku', 'grace.wanjiku@email.com', '+254745678901', 'Peace & Security', 'Social work background, experienced in conflict resolution', 'approved', NULL, 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(5, 'Peter Ochieng', 'peter.ochieng@email.com', '+254756789012', 'Disability Inclusion', 'Special needs teacher with advocacy experience', 'pending', NULL, 1, '2025-10-27 07:27:19', '2025-10-27 07:27:19'),
(6, 'BRIAN KIPLANGAT', 'briankiplangat241@gmail.com', '0795701071', 'Health &amp; Gender', 'hi', 'pending', NULL, 1, '2025-10-27 12:46:32', '2025-10-27 12:46:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `contact_message_replies`
--
ALTER TABLE `contact_message_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_message_reply_message` (`message_id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Indexes for table `donation_events`
--
ALTER TABLE `donation_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_donation_events_donation` (`donation_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `event_date` (`event_date`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `active` (`active`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `volunteers`
--
ALTER TABLE `volunteers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `status` (`status`),
  ADD KEY `interest_area` (`interest_area`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_message_replies`
--
ALTER TABLE `contact_message_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `donation_events`
--
ALTER TABLE `donation_events`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `volunteers`
--
ALTER TABLE `volunteers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contact_message_replies`
--
ALTER TABLE `contact_message_replies`
  ADD CONSTRAINT `fk_message_reply_message` FOREIGN KEY (`message_id`) REFERENCES `contact_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `donation_events`
--
ALTER TABLE `donation_events`
  ADD CONSTRAINT `fk_donation_events_donation` FOREIGN KEY (`donation_id`) REFERENCES `donations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
