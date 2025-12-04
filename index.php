<?php
require_once __DIR__ . '/db.php';
ensure_schema($koneksi);

$roomResult = $koneksi->query("SELECT * FROM rooms ORDER BY created_at DESC");
$rooms = $roomResult ? $roomResult->fetch_all(MYSQLI_ASSOC) : [];
$bookingStat = $koneksi->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'] ?? 0;
$availableStat = $koneksi->query("SELECT COUNT(*) AS total FROM rooms WHERE status='tersedia'")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HotelMantap - Sewa Kamar & Pembayaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">HotelMantap</a>
        <div class="d-flex gap-2">
            <a href="daftar.php" class="btn btn-outline-light btn-sm">Daftar</a>
            <a href="login.php" class="btn btn-light btn-sm text-primary">Login</a>
        </div>
    </div>
</nav>

<header class="py-5 bg-white shadow-sm">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <h1 class="fw-bold mb-3">Landing page reservasi HotelMantap</h1>
                <p class="text-muted mb-4">Jelajahi kamar dan pembayaran yang tersedia. Untuk melakukan pemesanan, registrasi dan login terlebih dahulu agar setiap transaksi tercatat ke akun.</p>
                <div class="d-flex gap-2">
                    <a href="daftar.php" class="btn btn-primary btn-lg">Daftar &amp; Pesan</a>
                    <a href="login.php" class="btn btn-outline-secondary btn-lg">Masuk</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-2">Statistik Singkat</h5>
                        <p class="mb-1">Pemesanan tercatat: <strong><?= $bookingStat; ?></strong></p>
                        <p class="mb-0">Kamar tersedia: <strong><?= $availableStat; ?></strong></p>
                        <small class="text-muted">Data langsung dari database yang sudah dibuat otomatis.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<main class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Daftar Kamar</h3>
        <span class="badge text-bg-secondary">Master data</span>
    </div>
    <div class="row g-3">
        <?php if (count($rooms) === 0): ?>
            <div class="col-12">
                <div class="alert alert-info mb-0">Belum ada kamar. Tambahkan lewat menu admin &raquo; Master Kamar.</div>
            </div>
        <?php endif; ?>
        <?php foreach ($rooms as $room): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title mb-1"><?= htmlspecialchars($room['name']); ?></h5>
                                <small class="text-muted">Tipe: <?= htmlspecialchars($room['type']); ?></small>
                            </div>
                            <span class="badge text-bg-<?= $room['status']==='tersedia' ? 'success' : 'warning'; ?> text-uppercase"><?= htmlspecialchars($room['status']); ?></span>
                        </div>
                        <p class="fw-bold mb-1">Rp <?= number_format($room['price'], 0, ',', '.'); ?> / malam</p>
                        <p class="text-muted small mb-4">Cocok untuk tamu bisnis maupun liburan.</p>
                        <a href="user/user_dashboard.php" class="btn btn-outline-primary mt-auto">Pesan kamar ini</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3 mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Midtrans & Transfer</h5>
                    <p class="text-muted">Pilihan pembayaran fleksibel: otomatis via Midtrans (referensi dibuat) atau unggah bukti transfer untuk diverifikasi admin.</p>
                    <ul class="mb-0">
                        <li>Midtrans: gunakan referensi untuk uji Snap/redirect sandbox.</li>
                        <li>Transfer bank: unggah bukti, status menunggu verifikasi.</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Master Data Terpadu</h5>
                    <p class="text-muted">Database otomatis terbuat (users, rooms, bookings) beserta admin default <code>admin@hotelmantap.id</code> / <code>admin123</code>.</p>
                    <p class="mb-0">Semua data terhubung antara dashboard tamu dan admin.</p>
                </div>
            </div>
        </div>
    </div>
</main>

<footer class="py-3 bg-white border-top">
    <div class="container d-flex justify-content-between">
        <span class="text-muted">&copy; <?= date('Y'); ?> HotelMantap</span>
        <span class="text-muted">Penyewaan hotel lengkap dengan master & pembayaran</span>
    </div>
</footer>
</body>
</html>
