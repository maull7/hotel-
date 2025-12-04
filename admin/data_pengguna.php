<?php
require_once '../db.php';
ensure_schema($koneksi);

$result = $koneksi->query("SELECT id, nama, email, role FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pengguna</title>

    <!-- BOOTSTRAP -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: #f4f6f9;
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: #1f2937;
            color: white;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 20px;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background: #374151;
        }

        /* Main Content */
        .content {
            margin-left: 260px;
            padding: 25px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>Admin Hotel</h2>
    <a href="dash.php">Dashboard</a>
    <a href="data_pemesanan.php">Data Pemesanan</a>
    <a href="data_kamar.php">Data Kamar</a>
    <a href="data_pengguna.php">Data Pengguna</a>
    <a href="../logout.php">Logout</a>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="m-0">Data Pengguna</h3>
        </div>

        <div class="card-body">

            <a href="tambah_pengguna.php" class="btn btn-success mb-3">+ Tambah Pengguna</a>

            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php $no = 1; ?>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= $row['nama'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td>
                            <?php if ($row['role'] == 'admin') { ?>
                                <span class="badge bg-primary">Admin</span>
                            <?php } else { ?>
                                <span class="badge bg-secondary">Pengguna</span>
                            <?php } ?>
                        </td>

                        <td>
                            <span class="text-muted">Kelola user dapat dilakukan via database.</span>
                        </td>
                    </tr>
                    <?php } ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center text-muted">Belum ada pengguna.</td></tr>
                <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>
</div>

</body>
</html>
