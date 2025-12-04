<?php
require '../db.php';
require_once __DIR__ . '/../components/auth.php';
ensure_schema($koneksi);

require_login('admin');

$totalRooms = $koneksi->query("SELECT COUNT(*) AS total FROM rooms")->fetch_assoc()['total'] ?? 0;
$availableRooms = $koneksi->query("SELECT COUNT(*) AS total FROM rooms WHERE status = 'tersedia'")->fetch_assoc()['total'] ?? 0;
$totalBookings = $koneksi->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'] ?? 0;
$pendingPayments = $koneksi->query("SELECT COUNT(*) AS total FROM bookings WHERE payment_status IN ('menunggu','verifikasi')")->fetch_assoc()['total'] ?? 0;
$incomeResult = $koneksi->query("SELECT SUM(total_price) AS total FROM bookings WHERE payment_status = 'dibayar'");
$totalIncome = $incomeResult && ($row = $incomeResult->fetch_assoc()) ? (int)$row['total'] : 0;

$bookings = $koneksi->query(
    "SELECT b.*, r.name AS room_name, r.type AS room_type FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     ORDER BY b.created_at DESC LIMIT 8"
);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">HotelMantap Admin</a>
        <div class="d-flex gap-2">
            <a href="data_pemesanan.php" class="btn btn-outline-light btn-sm">Pemesanan</a>
            <a href="data_kamar.php" class="btn btn-outline-light btn-sm">Kamar</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Kamar</p>
                    <h4 class="mb-0 fw-bold"><?= $totalRooms; ?></h4>
                    <small class="text-success"><?= $availableRooms; ?> tersedia</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Pemesanan</p>
                    <h4 class="mb-0 fw-bold"><?= $totalBookings; ?></h4>
                    <small class="text-muted">tercatat di sistem</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Pembayaran Pending</p>
                    <h4 class="mb-0 fw-bold"><?= $pendingPayments; ?></h4>
                    <small class="text-warning">Midtrans / Bank</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Lunas</p>
                    <h4 class="mb-0 fw-bold">Rp <?= number_format($totalIncome, 0, ',', '.'); ?></h4>
                    <small class="text-success">Pembayaran selesai</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">Monitoring Pembayaran</h5>
                <small class="text-muted">Ringkasan Midtrans dan Transfer Bank</small>
            </div>
            <a href="data_pemesanan.php" class="btn btn-primary btn-sm">Kelola Pemesanan</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Pemesanan Terbaru</h5>
                <a href="data_pemesanan.php" class="btn btn-outline-secondary btn-sm">Lihat semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tamu</th>
                            <th>Kamar</th>
                            <th>Jadwal</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($bookings && $bookings->num_rows > 0): ?>
                            <?php while ($row = $bookings->fetch_assoc()): ?>
                                <?php
                                    $badgeClass = match ($row['payment_status']) {
                                        'dibayar' => 'success',
                                        'verifikasi' => 'warning',
                                        'gagal' => 'danger',
                                        default => 'secondary',
                                    };
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($row['guest_name']); ?></strong><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['email']); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['room_name']); ?> (<?= htmlspecialchars($row['room_type']); ?>)</td>
                                    <td><?= htmlspecialchars($row['checkin']); ?> - <?= htmlspecialchars($row['checkout']); ?></td>
                                    <td class="text-capitalize"><?= str_replace('_',' ', htmlspecialchars($row['payment_method'])); ?></td>
                                    <td><span class="badge text-bg-<?= $badgeClass; ?> text-uppercase"><?= htmlspecialchars($row['payment_status']); ?></span></td>
                                    <td>Rp <?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted">Belum ada data.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
