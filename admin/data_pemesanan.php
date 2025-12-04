<?php
require '../db.php';
require_once __DIR__ . '/../components/auth.php';
ensure_schema($koneksi);

require_login('admin');

$alert = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['booking_id'])) {
        $bookingId = (int)$_POST['booking_id'];
        $status = $_POST['payment_status'] ?? 'menunggu';
        $stmt = $koneksi->prepare("UPDATE bookings SET payment_status = ? WHERE id = ?");
        $stmt->bind_param('si', $status, $bookingId);
        $stmt->execute();
        $stmt->close();
        $alert = ['type' => 'success', 'text' => 'Status pembayaran berhasil diperbarui.'];
    } elseif (isset($_POST['simulate_midtrans'])) {
        $reference = trim($_POST['reference'] ?? '');
        $simulationStatus = $_POST['simulation_status'] ?? 'dibayar';
        if ($reference) {
            $stmt = $koneksi->prepare("UPDATE bookings SET payment_status = ? WHERE payment_reference = ? AND payment_method = 'midtrans'");
            $stmt->bind_param('ss', $simulationStatus, $reference);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected > 0) {
                $alert = ['type' => 'success', 'text' => "Simulasi Midtrans berhasil: status diubah menjadi {$simulationStatus}."];
            } else {
                $alert = ['type' => 'warning', 'text' => 'Referensi tidak ditemukan atau bukan transaksi Midtrans.'];
            }
        }
    }
}

$result = $koneksi->query(
    "SELECT b.*, r.name AS room_name, r.type AS room_type FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     ORDER BY b.created_at DESC"
);
$bookings = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
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
    <?php if ($alert): ?>
        <div class="alert alert-<?= $alert['type']; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($alert['text']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Langkah verifikasi pembayaran</h5>
                    <ol class="mb-0 text-muted">
                        <li>Transaksi Midtrans mendapatkan kode referensi (format <code>MID-XXXXXX</code>).</li>
                        <li>Buka panel <strong>Simulasi Midtrans</strong> di samping, masukkan kode referensi, lalu pilih hasil pembayaran (berhasil/gagal).</li>
                        <li>Untuk transfer bank, admin dapat menandai status <em>verifikasi</em> atau <em>dibayar</em> setelah memeriksa bukti.</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0">Simulasi Midtrans</h5>
                        <span class="badge text-bg-primary">Sandbox</span>
                    </div>
                    <form method="post" class="row g-2">
                        <input type="hidden" name="simulate_midtrans" value="1">
                        <div class="col-12">
                            <label class="form-label">Kode Referensi</label>
                            <input type="text" name="reference" class="form-control" placeholder="MID-XXXXXX" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Hasil Pembayaran</label>
                            <select name="simulation_status" class="form-select">
                                <option value="dibayar">Berhasil</option>
                                <option value="gagal">Gagal</option>
                                <option value="menunggu">Kembali Menunggu</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary">Kirim Simulasi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-0">Data Pemesanan</h5>
                    <small class="text-muted">Kelola status pembayaran Midtrans & Transfer Bank</small>
                </div>
                <span class="badge text-bg-secondary"><?= count($bookings); ?> transaksi</span>
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
                        <?php if (count($bookings) > 0): ?>
                            <?php foreach ($bookings as $row): ?>
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
                            <?php endforeach; ?>
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
