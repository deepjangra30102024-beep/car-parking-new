-- Smart QR Code Based Car Parking Management System Database
CREATE DATABASE IF NOT EXISTS `car_parking_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `car_parking_db`;

-- 1. Admins Table
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'guard') DEFAULT 'admin',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default Admin User (username: admin, password: password123)
INSERT INTO `admins` (`username`, `password`, `role`) 
VALUES ('admin', '$2y$10$4N2uQnJ4d4FkZqWqOcvl7OC.eHn2Pz2GshnS0b7zG2yvN5XhN2nvy', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- 2. Vehicles Table
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unique_token` VARCHAR(64) NOT NULL UNIQUE,
    `vehicle_number` VARCHAR(30) NOT NULL UNIQUE,
    `vehicle_type` ENUM('Car', 'Bike', 'SUV', 'Truck', 'Other') DEFAULT 'Car',
    `vehicle_model` VARCHAR(100) NULL,
    `color` VARCHAR(50) NULL,
    `owner_name` VARCHAR(100) NOT NULL,
    `owner_phone` VARCHAR(20) NOT NULL,
    `emergency_contact_name` VARCHAR(100) NULL,
    `emergency_phone` VARCHAR(20) NOT NULL,
    `parking_slot` VARCHAR(50) NULL,
    `notes` TEXT NULL,
    `qr_image` VARCHAR(255) NULL,
    `status` ENUM('Active', 'Blocked', 'Inactive') DEFAULT 'Active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Parking Logs Table (Check-in / Check-out)
CREATE TABLE IF NOT EXISTS `parking_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT NOT NULL,
    `check_in` DATETIME NOT NULL,
    `check_out` DATETIME NULL,
    `parking_slot` VARCHAR(50) NULL,
    `status` ENUM('PARKED', 'COMPLETED') DEFAULT 'PARKED',
    `fee` DECIMAL(8, 2) DEFAULT 0.00,
    `notes` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
