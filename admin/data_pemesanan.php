<?php
require '../db.php';
ensure_schema($koneksi);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $bookingId = (int)$_POST['booking_id'];
    $status = $_POST['payment_status'] ?? 'menunggu';
    $stmt = $koneksi->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $bookingId);
    $stmt->execute();
    $stmt->close();
}

$result = $koneksi->query(
    "SELECT b.*, r.name AS room_name, r.type AS room_type FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     ORDER BY b.created_at DESC"
);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dash.php">HotelMantap Admin</a>
        <div class="d-flex gap-2">
            <a href="dash.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="data_kamar.php" class="btn btn-outline-light btn-sm">Kamar</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Data Pemesanan</h5>
                    <small class="text-muted">Kelola status pembayaran Midtrans & Transfer Bank</small>
                </div>
                <span class="badge text-bg-secondary"><?= $result?->num_rows ?? 0; ?> transaksi</span>
            </div>
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tamu</th>
                            <th>Kamar</th>
                            <th>Jadwal</th>
                            <th>Metode</th>
                            <th>Referensi/Bukti</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
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
                                        <small class="text-muted">Telp: <?= htmlspecialchars($row['phone']); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['room_name']); ?> (<?= htmlspecialchars($row['room_type']); ?>)</td>
                                    <td><?= htmlspecialchars($row['checkin']); ?> - <?= htmlspecialchars($row['checkout']); ?></td>
                                    <td class="text-capitalize"><?= str_replace('_',' ', htmlspecialchars($row['payment_method'])); ?></td>
                                    <td>
                                        <?php if ($row['payment_reference']): ?>
                                            <span class="badge text-bg-dark"><?= htmlspecialchars($row['payment_reference']); ?></span>
                                        <?php elseif ($row['payment_proof']): ?>
                                            <a href="../<?= htmlspecialchars($row['payment_proof']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">Bukti TF</a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge text-bg-<?= $badgeClass; ?> text-uppercase"><?= htmlspecialchars($row['payment_status']); ?></span></td>
                                    <td>Rp <?= number_format($row['total_price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <form method="post" class="d-flex gap-2 align-items-center">
                                            <input type="hidden" name="booking_id" value="<?= $row['id']; ?>">
                                            <select name="payment_status" class="form-select form-select-sm">
                                                <option value="menunggu" <?= $row['payment_status']==='menunggu'?'selected':''; ?>>Menunggu</option>
                                                <option value="verifikasi" <?= $row['payment_status']==='verifikasi'?'selected':''; ?>>Verifikasi</option>
                                                <option value="dibayar" <?= $row['payment_status']==='dibayar'?'selected':''; ?>>Dibayar</option>
                                                <option value="gagal" <?= $row['payment_status']==='gagal'?'selected':''; ?>>Gagal</option>
                                            </select>
                                            <button class="btn btn-primary btn-sm">Update</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted">Belum ada data.</td></tr>
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
