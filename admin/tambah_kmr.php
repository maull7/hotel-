<?php
$conn = mysqli_connect("localhost", "root", "", "hotelmantap");

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

if (isset($_POST['simpan'])) {
    $nomor  = $_POST['nomor_kamar'];
    $tipe   = $_POST['tipe_kamar'];
    $harga  = $_POST['harga'];
    $status = $_POST['status'];

    mysqli_query($conn, "INSERT INTO data_kamar VALUES('', '$nomor', '$tipe', '$harga', '$status')");
    header("Location: data_kamar.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Kamar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light py-5">

<div class="container">
    <div class="col-md-6 mx-auto">
        
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="m-0">Tambah Data Kamar</h4>
            </div>

            <div class="card-body">
                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label">Nomor Kamar</label>
                        <input type="text" name="nomor_kamar" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tipe Kamar</label>
                        <input type="text" name="tipe_kamar" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" name="harga" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="tersedia">Tersedia</option>
                            <option value="terisi">Terisi</option>
                            <option value="perawatan">Perawatan</option>
                        </select>
                    </div>

                    <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
                    <a href="data_kamar.php" class="btn btn-secondary">Kembali</a>

                </form>
            </div>

        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
