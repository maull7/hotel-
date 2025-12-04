<?php
require '../db.php';
require_once __DIR__ . '/../components/auth.php';
ensure_schema($koneksi);

require_login('admin');

$alert = null;
$editRoom = null;

if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $roomStmt = $koneksi->prepare("SELECT * FROM rooms WHERE id = ?");
    $roomStmt->bind_param('i', $editId);
    $roomStmt->execute();
    $editRoom = $roomStmt->get_result()->fetch_assoc();
    $roomStmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $price = (int)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'tersedia';

    if ($action === 'delete') {
        $roomId = (int)($_POST['room_id'] ?? 0);
        if ($roomId > 0) {
            $stmt = $koneksi->prepare("DELETE FROM rooms WHERE id = ?");
            $stmt->bind_param('i', $roomId);
            $stmt->execute();
            $stmt->close();
            $alert = ['type' => 'success', 'text' => 'Kamar berhasil dihapus.'];
        }
    } elseif ($action === 'update') {
        $roomId = (int)($_POST['room_id'] ?? 0);
        if ($roomId > 0 && $name && $type && $price > 0) {
            $stmt = $koneksi->prepare("UPDATE rooms SET name = ?, type = ?, price = ?, status = ? WHERE id = ?");
            $stmt->bind_param('ssisi', $name, $type, $price, $status, $roomId);
            $stmt->execute();
            $stmt->close();
            $alert = ['type' => 'success', 'text' => 'Perubahan kamar berhasil disimpan.'];
            $editRoom = null;
        }
    } else {
        if ($name && $type && $price > 0) {
            $stmt = $koneksi->prepare("INSERT INTO rooms (name, type, price, status) VALUES (?,?,?,?)");
            $stmt->bind_param('ssis', $name, $type, $price, $status);
            $stmt->execute();
            $stmt->close();
            $alert = ['type' => 'success', 'text' => 'Kamar baru berhasil ditambahkan.'];
        }
    }
}

$roomsResult = $koneksi->query("SELECT * FROM rooms ORDER BY created_at DESC");
$rooms = $roomsResult ? $roomsResult->fetch_all(MYSQLI_ASSOC) : [];
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
    <?php if ($alert): ?>
        <div class="alert alert-<?= $alert['type']; ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($alert['text']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title mb-0"><?= $editRoom ? 'Ubah Kamar' : 'Tambah Kamar'; ?></h5>
                        <?php if ($editRoom): ?>
                            <a href="data_kamar.php" class="btn btn-link btn-sm">Batal</a>
                        <?php endif; ?>
                    </div>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="action" value="<?= $editRoom ? 'update' : 'create'; ?>">
                        <?php if ($editRoom): ?>
                            <input type="hidden" name="room_id" value="<?= $editRoom['id']; ?>">
                        <?php endif; ?>
                        <div class="col-12">
                            <label class="form-label">Nama Kamar</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editRoom['name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tipe</label>
                            <input type="text" name="type" class="form-control" placeholder="Deluxe, Suite, dll" value="<?= htmlspecialchars($editRoom['type'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Harga per Malam</label>
                            <input type="number" name="price" class="form-control" min="0" step="50000" value="<?= htmlspecialchars($editRoom['price'] ?? ''); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php
                                    $statuses = ['tersedia' => 'Tersedia', 'terisi' => 'Terisi', 'perawatan' => 'Perawatan'];
                                    $currentStatus = $editRoom['status'] ?? 'tersedia';
                                    foreach ($statuses as $value => $label):
                                ?>
                                    <option value="<?= $value; ?>" <?= $currentStatus === $value ? 'selected' : ''; ?>><?= $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button class="btn btn-primary"><?= $editRoom ? 'Update Kamar' : 'Simpan'; ?></button>
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
                        <span class="badge text-bg-secondary"><?= count($rooms); ?> kamar</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama</th>
                                    <th>Tipe</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($rooms) > 0): ?>
                                    <?php foreach ($rooms as $room): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($room['name']); ?></td>
                                            <td><?= htmlspecialchars($room['type']); ?></td>
                                            <td>Rp <?= number_format($room['price'], 0, ',', '.'); ?></td>
                                            <td class="text-capitalize"><?= htmlspecialchars($room['status']); ?></td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a class="btn btn-outline-secondary btn-sm" href="?edit=<?= $room['id']; ?>">Edit</a>
                                                    <form method="post" onsubmit="return confirm('Hapus kamar ini?');">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="room_id" value="<?= $room['id']; ?>">
                                                        <button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
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
