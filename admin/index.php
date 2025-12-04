<?php
require '../db.php';
data($koneksi);
function data($koneksi) {
    $result = $koneksi->query("SELECT * FROM pemesanan ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pemesanan Hotel</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f4f6f9; }
        .sidebar { width: 240px; background: #1f2937; color: white; position: fixed; top: 0; bottom: 0; padding: 20px; }
        .sidebar h2 { margin-top: 0; text-align: center; }
        .sidebar a { display: block; color: #e5e7eb; padding: 10px; text-decoration: none; border-radius: 6px; }
        .sidebar a:hover { background: #374151; }
        .main { margin-left: 260px; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th, table td { border: 1px solid #ddd; padding: 10px; }
        table th { background: #e5e7eb; }
        .btn { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; }
        .edit { background: #3b82f6; color: white; }
        .hapus { background: #ef4444; color: white; }
        .tambah { background: #10b981; color: white; padding: 8px 14px; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Hotel</h2>
        <a href="#">Dashboard</a>
        <a href="#">Data Pemesanan</a>
        <a href="#">Data Kamar</a>
        <a href="#">Data Pengguna</a>
        <a href="#">Logout</a>
    </div>

    <div class="main">
        <h1>Data Pemesanan</h1>

        <a href="tambah.php"><button class="btn tambah">+ Tambah Pemesanan</button></a>

        <div class="card">
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nama Tamu</th>
                    <th>Kamar</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>

                <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['nama'] ?></td>
                    <td><?= $row['kamar'] ?></td>
                    <td><?= $row['checkin'] ?></td>
                    <td><?= $row['checkout'] ?></td>
                    <td><?= $row['status'] ?></td>
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>"><button class="btn edit">Edit</button></a>
                        <a href="hapus.php?id=<?= $row['id'] ?>" onclick="return confirm('Hapus?')"><button class="btn hapus">Hapus</button></a>
                    </td>
                </tr>
                <?php } ?>

            </table>
        </div>
    </div>
</body>
</html>

<?php } ?>
