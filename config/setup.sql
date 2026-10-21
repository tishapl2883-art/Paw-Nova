-- PawConnect Database Schema & Seed Data
CREATE DATABASE IF NOT EXISTS `pawconnect_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pawconnect_db`;

-- Drop existing tables to allow clean re-runs
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `adoption_applications`;
DROP TABLE IF EXISTS `pets`;
DROP TABLE IF EXISTS `shelters`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `contact_messages`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(25) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Categories Table
CREATE TABLE `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL UNIQUE,
    `slug` VARCHAR(50) NOT NULL UNIQUE,
    `icon` VARCHAR(50) DEFAULT 'bi-heart-pulse',
    `description` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Shelters Table
CREATE TABLE `shelters` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `website` VARCHAR(200) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Pets Table
CREATE TABLE `pets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `category_id` INT NOT NULL,
    `shelter_id` INT DEFAULT NULL,
    `breed` VARCHAR(100) NOT NULL,
    `age_years` INT DEFAULT 0,
    `age_months` INT DEFAULT 0,
    `gender` ENUM('Male', 'Female') NOT NULL,
    `size` ENUM('Small', 'Medium', 'Large', 'Extra Large') DEFAULT 'Medium',
    `color` VARCHAR(50) DEFAULT NULL,
    `status` ENUM('Available', 'Pending', 'Adopted') DEFAULT 'Available',
    `description` TEXT NOT NULL,
    `image_url` VARCHAR(500) DEFAULT NULL,
    `adoption_fee` DECIMAL(10,2) DEFAULT 0.00,
    `vaccinated` TINYINT(1) DEFAULT 0,
    `spayed_neutered` TINYINT(1) DEFAULT 0,
    `microchipped` TINYINT(1) DEFAULT 0,
    `house_trained` TINYINT(1) DEFAULT 0,
    `good_with_kids` TINYINT(1) DEFAULT 1,
    `good_with_dogs` TINYINT(1) DEFAULT 1,
    `good_with_cats` TINYINT(1) DEFAULT 1,
    `special_needs` TEXT DEFAULT NULL,
    `location` VARCHAR(150) DEFAULT 'Main Shelter, City Center',
    `featured` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_pet_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pet_shelter` FOREIGN KEY (`shelter_id`) REFERENCES `shelters` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Adoption Applications Table
CREATE TABLE `adoption_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `application_no` VARCHAR(25) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `pet_id` INT NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `zip_code` VARCHAR(20) DEFAULT NULL,
    `housing_type` ENUM('Own House', 'Rent Apartment', 'Rent House', 'Condo/Townhouse', 'Other') NOT NULL,
    `has_yard` TINYINT(1) DEFAULT 0,
    `yard_fenced` TINYINT(1) DEFAULT 0,
    `landlord_permission` TINYINT(1) DEFAULT 1,
    `household_adults` INT DEFAULT 1,
    `household_children` INT DEFAULT 0,
    `current_pets` TEXT DEFAULT NULL,
    `pet_experience` ENUM('First-time owner', 'Experienced', 'Current pet owner', 'Expert') NOT NULL,
    `hours_alone_daily` INT DEFAULT 4,
    `vet_name_contact` VARCHAR(255) DEFAULT NULL,
    `reason_for_adoption` TEXT NOT NULL,
    `status` ENUM('Pending', 'Under Review', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
    `admin_notes` TEXT DEFAULT NULL,
    `applicant_feedback` TEXT DEFAULT NULL,
    `reviewed_by` INT DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_app_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Favorites Table
CREATE TABLE `favorites` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `pet_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `user_pet_unique` (`user_id`, `pet_id`),
    CONSTRAINT `fk_fav_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_fav_pet` FOREIGN KEY (`pet_id`) REFERENCES `pets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Contact Messages Table
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SEED DATA
-- ==========================================

-- Insert Users (Password for admin is 'admin123', for user is 'user123')
INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `city`, `role`) VALUES
(1, 'System Administrator', 'admin@pawconnect.org', '$2y$10$wE9mHjPq04sVf5wYlQ1xqup1u3w8m8z4vOa8jN7zX6Z7xG4G3H1gK', '555-010-9999', '123 Shelter Way', 'San Francisco', 'admin'),
(2, 'John Doe', 'john@example.com', '$2y$10$3zQxZl6wF4vFkI1X4mH7k.1D5YpWvM2h8R0Pj8s4Q6G9L5B1T9vY.', '555-012-3456', '742 Evergreen Terrace', 'Springfield', 'user'),
(3, 'Sarah Connor', 'sarah@example.com', '$2y$10$3zQxZl6wF4vFkI1X4mH7k.1D5YpWvM2h8R0Pj8s4Q6G9L5B1T9vY.', '555-019-8765', '456 Elm Street', 'Austin', 'user');

-- Insert Categories
INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`) VALUES
(1, 'Dogs', 'dogs', 'bi-heart-fill', 'Loyal, affectionate, and playful companions of all sizes.'),
(2, 'Cats', 'cats', 'bi-star-fill', 'Curious, independent, and gentle feline friends.'),
(3, 'Rabbits', 'rabbits', 'bi-flower1', 'Quiet, gentle, and loving small indoor pets.'),
(4, 'Birds', 'birds', 'bi-send-fill', 'Cheerful, colorful, and vocal avian companions.');

-- Insert Shelters
INSERT INTO `shelters` (`id`, `name`, `email`, `phone`, `address`, `city`, `website`) VALUES
(1, 'Happy Paws Rescue Haven', 'contact@happypaws.org', '(555) 234-5678', '100 Animal Way', 'Austin', 'https://happypaws.org'),
(2, 'Metro Animal Welfare Center', 'info@metroanimals.org', '(555) 876-5432', '450 Bayview Blvd', 'San Francisco', 'https://metroanimals.org'),
(3, 'Sunny Hills Pet Sanctuary', 'adopt@sunnyhills.org', '(555) 998-1122', '78 Meadow Lane', 'Springfield', 'https://sunnyhills.org');

-- Insert Pets
INSERT INTO `pets` (`id`, `name`, `category_id`, `shelter_id`, `breed`, `age_years`, `age_months`, `gender`, `size`, `color`, `status`, `description`, `image_url`, `adoption_fee`, `vaccinated`, `spayed_neutered`, `microchipped`, `house_trained`, `good_with_kids`, `good_with_dogs`, `good_with_cats`, `special_needs`, `location`, `featured`) VALUES
(1, 'Bella', 1, 1, 'Golden Retriever', 2, 4, 'Female', 'Large', 'Golden Cream', 'Available', 'Bella is a sweet, outgoing Golden Retriever who loves fetch, belly rubs, and meeting new people. She is very gentle with children and already knows basic obedience commands like sit, stay, and paw.', 'https://images.unsplash.com/photo-1552053831-71594a27632d?auto=format&fit=crop&w=800&q=80', 150.00, 1, 1, 1, 1, 1, 1, 1, 'None', 'Austin Shelter Main Campus', 1),

(2, 'Milo', 2, 2, 'British Shorthair', 1, 6, 'Male', 'Medium', 'Silver Tabby', 'Available', 'Milo is a calm, affectionate gentleman with soft fur and expressive green eyes. He loves lounging in sunny spots and curling up next to you while you read or work.', 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?auto=format&fit=crop&w=800&q=80', 100.00, 1, 1, 1, 1, 1, 0, 1, 'None', 'San Francisco Shelter', 1),

(3, 'Luna', 1, 1, 'German Shepherd Mix', 3, 0, 'Female', 'Large', 'Black & Tan', 'Available', 'Luna is an intelligent, athletic shepherd mix looking for an active family. She excels at agility games and would make an incredible hiking and jogging partner.', 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?auto=format&fit=crop&w=800&q=80', 175.00, 1, 1, 1, 1, 1, 1, 0, 'Needs regular physical exercise', 'Austin Shelter Main Campus', 1),

(4, 'Oliver', 2, 3, 'Orange Tabby', 0, 8, 'Male', 'Small', 'Orange & White', 'Available', 'Oliver is an energetic kitten bursting with curiosity. He loves feather wands, laser pointers, and purring like an engine when held.', 'https://images.unsplash.com/photo-1574158622682-e40e69881006?auto=format&fit=crop&w=800&q=80', 85.00, 1, 1, 1, 1, 1, 1, 1, 'None', 'Springfield Sanctuary', 1),

(5, 'Thumper', 3, 2, 'Holland Lop', 1, 2, 'Male', 'Small', 'White & Gray', 'Available', 'Thumper is a friendly, litter-box trained Holland Lop rabbit. He enjoys munching on timothy hay, fresh parsley, and gentle forehead scratches.', 'https://images.unsplash.com/photo-1585110396000-c9ffd4e4b308?auto=format&fit=crop&w=800&q=80', 50.00, 1, 1, 0, 1, 1, 0, 0, 'Quiet indoor home preferred', 'San Francisco Shelter', 0),

(6, 'Kiwi', 4, 3, 'Parakeet / Budgerigar', 1, 0, 'Male', 'Small', 'Bright Green & Yellow', 'Available', 'Kiwi is a cheerful and social little bird who loves singing tunes and perching on gentle fingers. Very inquisitive and easy to care for.', 'https://images.unsplash.com/photo-1552728089-57bdde30beb3?auto=format&fit=crop&w=800&q=80', 35.00, 1, 0, 0, 0, 1, 0, 0, 'Cage and toys included', 'Springfield Sanctuary', 0),

(7, 'Daisy', 1, 1, 'Beagle', 4, 2, 'Female', 'Medium', 'Tricolor', 'Pending', 'Daisy is a gentle beagle with big soulful eyes and a heart full of love. She gets along wonderfully with other dogs and loves sniff walks in the neighborhood.', 'https://images.unsplash.com/photo-1505628346881-b72b27e84530?auto=format&fit=crop&w=800&q=80', 120.00, 1, 1, 1, 1, 1, 1, 1, 'None', 'Austin Shelter Main Campus', 0),

(8, 'Simba', 2, 2, 'Maine Coon Mix', 2, 0, 'Male', 'Large', 'Ginger Fluff', 'Available', 'Simba is a magnificent, fluffy companion with a playful heart and charming chirps. He loves brushing sessions and watching birds from windows.', 'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?auto=format&fit=crop&w=800&q=80', 130.00, 1, 1, 1, 1, 1, 1, 1, 'Regular grooming recommended', 'San Francisco Shelter', 0),

(9, 'Rocky', 1, 3, 'Labrador Retriever', 0, 7, 'Male', 'Medium', 'Chocolate Brown', 'Adopted', 'Rocky has found his forever home with a loving family and a big backyard! He loves swimming and fetch.', 'https://images.unsplash.com/photo-1591769225440-811ad7d6eab2?auto=format&fit=crop&w=800&q=80', 200.00, 1, 1, 1, 1, 1, 1, 1, 'None', 'Springfield Sanctuary', 0);

-- Insert Sample Adoption Applications
INSERT INTO `adoption_applications` (`id`, `application_no`, `user_id`, `pet_id`, `full_name`, `email`, `phone`, `address`, `city`, `state`, `zip_code`, `housing_type`, `has_yard`, `yard_fenced`, `landlord_permission`, `household_adults`, `household_children`, `current_pets`, `pet_experience`, `hours_alone_daily`, `vet_name_contact`, `reason_for_adoption`, `status`, `admin_notes`, `applicant_feedback`, `reviewed_by`, `reviewed_at`) VALUES
(1, 'PAW-2026-001', 2, 7, 'John Doe', 'john@example.com', '555-012-3456', '742 Evergreen Terrace', 'Springfield', 'IL', '62701', 'Own House', 1, 1, 1, 2, 1, 'None currently. Had a beagle for 12 years.', 'Experienced', 3, 'Dr. Evans at Springfield Vet (555-987-1234)', 'We fell in love with Daisy. We have a secure fenced yard and lots of free time to provide a loving home.', 'Under Review', 'Applicant has a great fenced yard and prior beagle experience. Contacting vet reference.', 'Your application has been received and is currently under review by our adoption coordinator! We will call you for a brief phone interview.', 1, '2026-10-21 14:30:00'),

(2, 'PAW-2026-002', 3, 9, 'Sarah Connor', 'sarah@example.com', '555-019-8765', '456 Elm Street', 'Austin', 'TX', '78701', 'Own House', 1, 1, 1, 2, 0, 'One senior cat (very calm)', 'Current pet owner', 2, 'Austin Central Vet Hospital', 'Rocky is the perfect match for our active lifestyle. We love outdoor adventures and daily walks.', 'Approved', 'Application approved! Home check completed. Adoption finalized.', 'Congratulations! Your adoption application for Rocky has been approved. Please visit the shelter on Saturday to complete paperwork and welcome him home!', 1, '2026-10-20 11:15:00');

-- Insert Sample Favorites
INSERT INTO `favorites` (`user_id`, `pet_id`) VALUES
(2, 1),
(2, 2),
(3, 3);

-- Insert Sample Contact Messages
INSERT INTO `contact_messages` (`name`, `email`, `subject`, `message`, `is_read`) VALUES
('Michael Scott', 'michael@dunder.com', 'Volunteer Opportunities', 'Hi, I would love to spend weekends walking dogs at your Austin shelter. How can I apply as a volunteer?', 0),
('Pam Beesly', 'pam@artstudio.com', 'Adoption Event Schedule', 'Are you hosting any pet adoption fairs this upcoming month?', 1);
