<?php
require '../db.php';
ensure_schema($koneksi);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'tersedia';

    if ($name && $type && $price > 0) {
        $stmt = $koneksi->prepare("INSERT INTO rooms (name, type, price, status) VALUES (?,?,?,?)");
        $stmt->bind_param('ssis', $name, $type, $price, $status);
        $stmt->execute();
        $stmt->close();
    }
}

$rooms = $koneksi->query("SELECT * FROM rooms ORDER BY created_at DESC");
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Data Kamar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="dash.php">HotelMantap Admin</a>
        <div class="d-flex gap-2">
            <a href="dash.php" class="btn btn-outline-light btn-sm">Dashboard</a>
            <a href="data_pemesanan.php" class="btn btn-outline-light btn-sm">Pemesanan</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Tambah Kamar</h5>
                    <form method="post" class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nama Kamar</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tipe</label>
                            <input type="text" name="type" class="form-control" placeholder="Deluxe, Suite, dll" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Harga per Malam</label>
                            <input type="number" name="price" class="form-control" min="0" step="50000" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="tersedia">Tersedia</option>
                                <option value="terisi">Terisi</option>
                                <option value="perawatan">Perawatan</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="card-title mb-0">Master Kamar</h5>
                            <small class="text-muted">Data diambil langsung dari database</small>
                        </div>
                        <span class="badge text-bg-secondary"><?= $rooms?->num_rows ?? 0; ?> kamar</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($rooms && $rooms->num_rows > 0): ?>
                                    <?php while ($room = $rooms->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($room['name']); ?></td>
                                            <td><?= htmlspecialchars($room['type']); ?></td>
                                            <td>Rp <?= number_format($room['price'], 0, ',', '.'); ?></td>
                                            <td class="text-capitalize"><?= htmlspecialchars($room['status']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">Belum ada kamar.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
