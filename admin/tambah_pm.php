<?php
$conn = mysqli_connect("localhost", "root", "", "hotelmantap");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (isset($_POST['simpan'])) {
    $nama     = $_POST['nama'];
    $kamar    = $_POST['kamar'];
    $checkin  = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $status   = $_POST['status'];

    mysqli_query($conn, "INSERT INTO data_pemesanan VALUES('', '$nama', '$kamar', '$checkin', '$checkout', '$status')");
    header("Location: dash.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Pemesanan</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f7f7;
            margin: 0;
            padding: 20px;
        }

        .container {
            width: 450px;
            background: #fff;
            padding: 20px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
        }

        h2 {
            text-align: center;
        }

        label {
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            border: none;
            color: white;
            background: #28a745;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }

        .btn:hover {
            background: #218838;
        }

        .back {
            text-align: center;
            margin-top: 15px;
        }

        .back a {
            text-decoration: none;
            color: #007bff;
        }

    </style>
</head>

<body>

<div class="container">
    <h2>Tambah Pemesanan</h2>

    <form method="POST">
        <label>Nama Tamu</label>
        <input type="text" name="nama" required>

        <label>Pilih Kamar</label>
        <select name="kamar" required>
            <option value="">-- Pilih Kamar --</option>

        

            <?php
            // Ambil data kamar dari database
            $kamar = mysqli_query($conn, "SELECT * FROM data_kamar WHERE status='tersedia'");
            while ($row = mysqli_fetch_assoc($kamar)) {
                echo "<option value='{$row['nomor_kamar']}'>{$row['nomor_kamar']} - {$row['tipe_kamar']}</option>";
            }
            ?>
        </select>

        <label>Check-in</label>
        <input type="date" name="checkin" required>

        <label>Check-out</label>
        <input type="date" name="checkout" required>

        <label>Status</label>
        <select name="status">
            <option value="aktif">Aktif</option>
            <option value="selesai">Selesai</option>
        </select>

        <button type="submit" name="simpan" class="btn">Simpan</button>
    </form>

    <div class="back">
        <a href="data_pemesanan.php">← Kembali</a>
    </div>
</div>

</body>
</html>
