<?php
$host = "localhost";
$user = "root";
$password = "";
$db = "hotelmantap";

$koneksi = mysqli_connect($host, $user, $password, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

function ensure_schema(mysqli $conn): void
{
    $conn->query(
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            email VARCHAR(120) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin','user') DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS rooms (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            type VARCHAR(100) NOT NULL,
            price INT NOT NULL,
            status ENUM('tersedia','terisi','perawatan') DEFAULT 'tersedia',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    $conn->query(
        "CREATE TABLE IF NOT EXISTS bookings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NULL,
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

    // Tambahkan kolom user_id jika database sudah terlanjur dibuat tanpa kolom tersebut
    $userIdColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'user_id'");
    if ($userIdColumn && $userIdColumn->num_rows === 0) {
        $conn->query("ALTER TABLE bookings ADD COLUMN user_id INT NULL AFTER id");
    }

    // Pastikan relasi ke tabel users tersedia
    $fkCheck = $conn->query(
        "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE ".
        "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bookings' AND COLUMN_NAME = 'user_id' ".
        "AND REFERENCED_TABLE_NAME = 'users' LIMIT 1"
    );
    if ($fkCheck && $fkCheck->num_rows === 0) {
        $conn->query("ALTER TABLE bookings ADD CONSTRAINT fk_bookings_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL");
    }

    $uploadDir = __DIR__ . '/uploads/payments';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $adminCheck = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    if ($adminCheck && $adminCheck->num_rows === 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password, role) VALUES ('Administrator', 'admin@hotelmantap.id', ?, 'admin')");
        $stmt->bind_param('s', $defaultPassword);
        $stmt->execute();
        $stmt->close();
    }

    $existingRooms = $conn->query("SELECT COUNT(*) AS total FROM rooms");
    if ($existingRooms && ($row = $existingRooms->fetch_assoc()) && (int)$row['total'] === 0) {
        $stmt = $conn->prepare(
            "INSERT INTO rooms (name, type, price, status) VALUES
            ('Superior City View', 'Superior', 550000, 'tersedia'),
            ('Deluxe Garden', 'Deluxe', 750000, 'tersedia'),
            ('Executive Suite', 'Suite', 1250000, 'tersedia')"
        );
        $stmt?->execute();
        $stmt?->close();
    }
}
?>
