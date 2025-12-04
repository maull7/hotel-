-- Database bootstrap untuk HotelMantap
-- Termasuk master users, rooms, dan bookings

CREATE DATABASE IF NOT EXISTS hotelmantap;
USE hotelmantap;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rooms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  type VARCHAR(100) NOT NULL,
  price INT NOT NULL,
  status ENUM('tersedia','terisi','perawatan') DEFAULT 'tersedia',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  guest_name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  room_id INT NOT NULL,
  checkin DATE NOT NULL,
  checkout DATE NOT NULL,
  guests INT DEFAULT 1,
  total_price INT NOT NULL,
  payment_method ENUM('midtrans','transfer_bank') NOT NULL,
  payment_status ENUM('menunggu','dibayar','verifikasi','gagal') DEFAULT 'menunggu',
  payment_reference VARCHAR(120) DEFAULT NULL,
  payment_proof VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (nama, email, password, role) VALUES
('Administrator', 'admin@hotelmantap.id', '$2y$10$HXMUhA3Y5HVkmI.ljyFZiO1eMnbY/Xg1SJ.D/9TX0ucWpL9ePxhS2', 'admin')
ON DUPLICATE KEY UPDATE email = email;

INSERT INTO rooms (name, type, price, status) VALUES
('Superior City View', 'Superior', 550000, 'tersedia'),
('Deluxe Garden', 'Deluxe', 750000, 'tersedia'),
('Executive Suite', 'Suite', 1250000, 'tersedia')
ON DUPLICATE KEY UPDATE name = VALUES(name);
