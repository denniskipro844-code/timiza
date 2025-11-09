-- Timiza Youth Initiative Database Schema
-- MySQL Database Structure

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Database: timiza_db

-- --------------------------------------------------------

-- Table structure for table `admin_users`
CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (password: admin123)
INSERT INTO `admin_users` (`username`, `password`, `email`, `full_name`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@timizayouth.org', 'System Administrator');

-- --------------------------------------------------------

-- Table structure for table `programs`
CREATE TABLE `programs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `content` longtext,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample programs
INSERT INTO `programs` (`name`, `slug`, `description`, `content`, `icon`, `sort_order`) VALUES
('Health & Gender', 'health-gender', 'Promoting sexual and reproductive health rights, gender equality, and addressing gender-based violence through education, advocacy, and community engagement.', 'Our Health & Gender program focuses on empowering young people with knowledge and skills to make informed decisions about their health and relationships. We conduct comprehensive sexuality education workshops, advocate for gender equality, and provide support for survivors of gender-based violence.', 'fas fa-heartbeat', 1),
('Climate Change', 'climate-change', 'Building climate resilience through environmental conservation, sustainable agriculture, and renewable energy initiatives led by young people.', 'Through our Climate Change program, we engage youth in environmental conservation activities including tree planting, sustainable farming practices, and clean energy projects. We believe young people are key to addressing the climate crisis.', 'fas fa-leaf', 2),
('Peace & Security', 'peace-security', 'Promoting peaceful coexistence, conflict resolution, and community security through dialogue, mediation training, and peace-building initiatives.', 'Our Peace & Security program trains young people as peace ambassadors and conflict mediators. We facilitate community dialogues and promote inter-community understanding to build lasting peace.', 'fas fa-dove', 3),
('Disability Inclusion', 'disability-inclusion', 'Ensuring equal opportunities and full participation of persons with disabilities in all aspects of community life.', 'We advocate for the rights of persons with disabilities and work to create inclusive communities where everyone can participate fully in social, economic, and political life.', 'fas fa-universal-access', 4),
('Education & Skills', 'education-skills', 'Enhancing educational outcomes and building practical skills for economic empowerment and sustainable livelihoods.', 'Our Education & Skills program provides digital literacy training, vocational skills development, and entrepreneurship mentorship to help young people build sustainable livelihoods.', 'fas fa-graduation-cap', 5);

-- --------------------------------------------------------

-- Table structure for table `news`
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `excerpt` text,
  `content` longtext NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'News',
  `author` varchar(100) DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample news articles
INSERT INTO `news` (`title`, `slug`, `excerpt`, `content`, `category`, `author`, `status`, `featured`) VALUES
('Timiza Youth Initiative Launches Climate Action Program', 'climate-action-program-launch', 'We are excited to announce the launch of our comprehensive climate action program aimed at engaging young people in environmental conservation.', 'Kilifi County, Kenya - Timiza Youth Initiative is proud to announce the launch of our new Climate Action Program, designed to empower young people to become environmental champions in their communities.\n\nThe program includes tree planting initiatives, sustainable agriculture training, and clean energy projects. Over the next year, we aim to plant 10,000 trees and train 500 young farmers in sustainable practices.\n\n"Climate change is one of the biggest challenges facing our generation," said Sarah Mwangi, Executive Director of TYI. "Through this program, we are giving young people the tools and knowledge they need to make a real difference."\n\nThe program is supported by local government and international partners, ensuring its sustainability and impact.', 'Environment', 'Sarah Mwangi', 'published', 1),
('100 Youth Complete Health Education Workshop', 'health-education-workshop-completion', 'A milestone achievement as 100 young people successfully complete our comprehensive health education workshop series.', 'This month marked a significant milestone for our Health & Gender program as 100 young people from across Kilifi County completed our comprehensive health education workshop series.\n\nThe workshops covered topics including sexual and reproductive health, gender equality, and prevention of gender-based violence. Participants gained valuable knowledge and skills to make informed decisions about their health and relationships.\n\n"The knowledge I gained has changed my perspective on many issues," said Grace, one of the participants. "I now feel confident to educate my peers and advocate for our rights."\n\nThe program will continue with advanced training for selected participants who will become peer educators in their communities.', 'Health', 'James Kombe', 'published', 1),
('Partnership with Local Schools Strengthens Education Program', 'school-partnership-education', 'TYI partners with 10 local schools to enhance digital literacy and skills training for students.', 'Timiza Youth Initiative has formed partnerships with 10 local schools in Kilifi County to strengthen our Education & Skills program. This collaboration will bring digital literacy training and vocational skills development directly to students.\n\nThe partnership includes provision of computer equipment, training materials, and certified instructors. Over 500 students are expected to benefit from this initiative in the first phase.\n\n"Education is the foundation of empowerment," noted Grace Nyong, Community Outreach Lead. "By working directly with schools, we can reach more young people and provide them with essential 21st-century skills."\n\nThe program will focus on computer literacy, entrepreneurship, and life skills training.', 'Education', 'Grace Nyong', 'published', 0);

-- --------------------------------------------------------

-- Table structure for table `volunteers`
CREATE TABLE `volunteers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `interest_area` varchar(100) NOT NULL,
  `experience` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `notes` text,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `interest_area` (`interest_area`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample volunteers
INSERT INTO `volunteers` (`name`, `email`, `phone`, `interest_area`, `experience`, `status`) VALUES
('John Mwangi', 'john.mwangi@email.com', '+254712345678', 'Climate Change', 'Environmental science student with passion for conservation', 'approved'),
('Mary Kamau', 'mary.kamau@email.com', '+254723456789', 'Health & Gender', 'Community health volunteer with 2 years experience', 'approved'),
('David Kombe', 'david.kombe@email.com', '+254734567890', 'Education & Skills', 'IT professional interested in digital literacy training', 'pending'),
('Grace Wanjiku', 'grace.wanjiku@email.com', '+254745678901', 'Peace & Security', 'Social work background, experienced in conflict resolution', 'approved'),
('Peter Ochieng', 'peter.ochieng@email.com', '+254756789012', 'Disability Inclusion', 'Special needs teacher with advocacy experience', 'pending');

-- --------------------------------------------------------

-- Table structure for table `contact_messages`
CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read','replied') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample contact messages
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `status`) VALUES
('Alice Njeri', 'alice.njeri@email.com', 'Partnership Inquiry', 'Hello, I represent a local NGO and would like to explore partnership opportunities with TYI. Please contact me to discuss further.', 'unread'),
('Robert Kiprotich', 'robert.k@email.com', 'Volunteer Application Follow-up', 'I submitted a volunteer application last week and wanted to follow up on the status. Looking forward to contributing to your programs.', 'read'),
('Susan Wambui', 'susan.wambui@email.com', 'Program Information', 'Could you please provide more information about your climate action program? I am interested in participating.', 'unread');

-- --------------------------------------------------------

-- Table structure for table `gallery`
CREATE TABLE `gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `caption` varchar(500) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'General',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

-- Table structure for table `events`
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text,
  `content` longtext,
  `event_date` datetime NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('upcoming','ongoing','completed','cancelled') DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `event_date` (`event_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert sample events
INSERT INTO `events` (`title`, `slug`, `description`, `content`, `event_date`, `location`, `status`) VALUES
('Youth Climate Summit 2024', 'youth-climate-summit-2024', 'Annual summit bringing together young climate activists from across Kilifi County', 'Join us for our annual Youth Climate Summit where young people will share ideas, learn from experts, and develop action plans for environmental conservation in their communities.', '2024-03-15 09:00:00', 'Kilifi Community Center', 'upcoming'),
('Health Education Workshop - Malindi', 'health-workshop-malindi', 'Comprehensive health education workshop for youth in Malindi area', 'A day-long workshop covering sexual and reproductive health, gender equality, and life skills for young people aged 15-25.', '2024-02-20 08:30:00', 'Malindi Youth Center', 'upcoming');

COMMIT;