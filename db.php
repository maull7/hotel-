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
            FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );

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
