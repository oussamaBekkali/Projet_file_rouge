-- ========================================================
-- Fleet Management System Database Schema
-- Database: fleet_management
-- ========================================================

CREATE DATABASE IF NOT EXISTS `fleet_management`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `fleet_management`;

-- --------------------------------------------------------
-- Table structure for table `driver`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `delivery`;
DROP TABLE IF EXISTS `driver`;

CREATE TABLE `driver` (
    `id_driver` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `license_number` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `vehicle`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `vehicle`;

CREATE TABLE `vehicle` (
    `id_vehicle` INT AUTO_INCREMENT PRIMARY KEY,
    `registration_number` VARCHAR(50) NOT NULL UNIQUE,
    `model` VARCHAR(100) NOT NULL,
    `brand` VARCHAR(100) NOT NULL,
    `vehicle_type` ENUM('Truck', 'Van', 'Pickup', 'Trailer', 'Semi-Trailer', 'Other') NOT NULL,
    `capacity` FLOAT NOT NULL,
    `status` ENUM('Available', 'On Trip', 'Maintenance', 'Out of Service') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `goodstype`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `goodstype`;

CREATE TABLE `goodstype` (
    `id_goods_type` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `delivery`
-- --------------------------------------------------------
CREATE TABLE `delivery` (
    `id_delivery` INT AUTO_INCREMENT PRIMARY KEY,
    `departure_location` VARCHAR(150) NOT NULL,
    `destination` VARCHAR(150) NOT NULL,
    `delivery_date` DATETIME NOT NULL,
    `status` ENUM('Pending', 'In Transit', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    `weight` FLOAT NOT NULL,
    `id_driver` INT NOT NULL,
    `id_vehicle` INT NOT NULL,
    `id_goods_type` INT NOT NULL,
    CONSTRAINT `fk_delivery_driver` FOREIGN KEY (`id_driver`) 
        REFERENCES `driver` (`id_driver`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_delivery_vehicle` FOREIGN KEY (`id_vehicle`) 
        REFERENCES `vehicle` (`id_vehicle`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE,
    CONSTRAINT `fk_delivery_goodstype` FOREIGN KEY (`id_goods_type`) 
        REFERENCES `goodstype` (`id_goods_type`) 
        ON DELETE RESTRICT 
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Sample Seed Data
-- --------------------------------------------------------

INSERT INTO `driver` (`first_name`, `last_name`, `phone`, `email`, `license_number`) VALUES
('Jean', 'Dupont', '+33 6 12 34 56 78', 'jean.dupont@fleetlog.com', 'DL-789012'),
('Sarah', 'Connor', '+33 6 98 76 54 32', 'sarah.connor@fleetlog.com', 'DL-345678'),
('Mohamed', 'Alami', '+33 7 11 22 33 44', 'mohamed.alami@fleetlog.com', 'DL-901234'),
('Emma', 'Watson', '+33 6 55 44 33 22', 'emma.watson@fleetlog.com', 'DL-567890');

INSERT INTO `vehicle` (`registration_number`, `model`, `brand`, `vehicle_type`, `capacity`, `status`) VALUES
('AB-123-CD', 'Actros 1845', 'Mercedes-Benz', 'Truck', 18000.0, 'Available'),
('EF-456-GH', 'Master III', 'Renault', 'Van', 1500.0, 'On Trip'),
('IJ-789-KL', 'FH16 750', 'Volvo', 'Truck', 25000.0, 'Available'),
('MN-012-OP', 'Transit Custom', 'Ford', 'Van', 1200.0, 'Maintenance'),
('QR-345-ST', 'Hilux', 'Toyota', 'Pickup', 1000.0, 'Available');

INSERT INTO `goodstype` (`name`, `description`) VALUES
('Perishable Goods', 'Refrigerated or temperature-sensitive food and dairy products'),
('Electronics', 'High-value computing, screens, appliances, and delicate electronics'),
('Construction Materials', 'Cement, timber, steel bars, and hardware supplies'),
('Pharmaceuticals', 'Medicines, vaccines, and sterile hospital equipment'),
('Textiles & Clothing', 'Garments, fabrics, and packaged consumer apparel');

INSERT INTO `delivery` (`departure_location`, `destination`, `delivery_date`, `status`, `weight`, `id_driver`, `id_vehicle`, `id_goods_type`) VALUES
('Paris Hub Nord', 'Lyon Distribution Center', '2026-09-25 08:30:00', 'In Transit', 12500.0, 1, 1, 1),
('Marseille Port', 'Nice Central Depot', '2026-09-26 10:00:00', 'Pending', 950.0, 2, 2, 2),
('Lille Logistic Park', 'Strasbourg Euro Hub', '2026-09-24 06:15:00', 'Delivered', 19800.0, 3, 3, 3),
('Bordeaux Warehouse', 'Toulouse Cargo Bay', '2026-09-27 14:00:00', 'Pending', 600.0, 4, 5, 4),
('Rouen Terminal', 'Paris Hub Sud', '2026-09-23 11:45:00', 'Delivered', 850.0, 1, 2, 5);
