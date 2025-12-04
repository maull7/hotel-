<?php
$conn = mysqli_connect("localhost", "root", "", "hotelmantap");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>


<!DOCTYPE html>
<html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Data Pemesanan</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; }
        .sidebar { width: 240px; background: #1f2937; color: white; position: fixed; top: 0; bottom: 0; padding: 20px; }
        .sidebar h2 { text-align: center; margin-top: 0; }
        .sidebar a { display: block; color: #e5e7eb; padding: 10px; text-decoration: none; border-radius: 6px; }
        .sidebar a:hover { background: #374151; }

        .main { margin-left: 260px; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; }
        table th { background: #e5e7eb; }

        .btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; }
        .tambah { background: #10b981; color: white; margin-bottom: 10px; }
        .edit { background: #3b82f6; color: white; }
        .hapus { background: #ef4444; color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Hotel</h2>
        <a href="#">Dashboard</a>
        <a href="data_pemesanan.php">Data Pemesanan</a>
        <a href="data_kamar.php">Data Kamar</a>
        <a href="#">Data Pengguna</a>
        <a href="dash.php">kembali</a>
    </div>
<head>
    <title>Data Kamar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light py-4">

<style>
    .content {
        margin-left: 250px; /* Agar tidak nutup sidebar */
        padding: 20px;
    }
</style>

<div class="content">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="m-0">Data Kamar</h3>
        </div>

        <div class="card-body">
            <a href="tambah_kmr.php" class="btn btn-success mb-3">+ Tambah Kamar</a>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nomor Kamar</th>
                        <th>Tipe</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $no = 1;
                $result = mysqli_query($conn, "SELECT * FROM data_kamar ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($result)) {
                ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['nomor_kamar'] ?></td>
                        <td><?= $row['tipe_kamar'] ?></td>
                        <td>Rp <?= number_format($row['harga']) ?></td>

                        <td>
                            <?php if ($row['status'] == 'tersedia') { ?>
                                <span class="badge bg-success">Tersedia</span>

                            <?php } elseif ($row['status'] == 'terisi') { ?>
                                <span class="badge bg-warning text-dark">Terisi</span>

                            <?php } else { ?>
                                <span class="badge bg-secondary">Perawatan</span>
                            <?php } ?>
                        </td>

                        <td>
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                            <a href="hapus.php?id=<?= $row['id'] ?>"
                               onclick="return confirm('Hapus kamar ini?')"
                               class="btn btn-sm btn-danger">
                                Hapus
                            </a>
                        </td>

                    </tr>
                <?php } ?>
                </tbody>

            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>