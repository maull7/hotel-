<?php
session_start();

// Data kamar hotel
$kamar_hotel = [
    ["nama" => "Superior Room", "harga" => 500000, "gambar" => "https://via.placeholder.com/300x200?text=Superior+Room"],
    ["nama" => "Deluxe Room", "harga" => 750000, "gambar" => "https://via.placeholder.com/300x200?text=Deluxe+Room"],
    ["nama" => "Suite Room", "harga" => 1200000, "gambar" => "https://via.placeholder.com/300x200?text=Suite+Room"]
];

// Fungsi menghitung lama menginap
function lamaMenginap($checkin, $checkout) {
    $tgl1 = new DateTime($checkin);
    $tgl2 = new DateTime($checkout);
    $diff = $tgl2->diff($tgl1);
    return max($diff->days, 1);
}

// Fungsi mendapatkan harga kamar
function getHargaKamar($namaKamar, $kamar_hotel) {
    foreach($kamar_hotel as $k) if($k['nama']===$namaKamar) return $k['harga'];
    return 0;
}

// Handle submit pemesanan
$notif = "";
if(isset($_POST['submit'])) {
    $checkin = $_POST['checkin'];
    $checkout = $_POST['checkout'];
    $jumlah_kamar = intval($_POST['jumlah_kamar']);
    $nama_tamu = $_POST['nama'];
    $kamar = $_POST['kamar'];
    $lama = lamaMenginap($checkin, $checkout);
    $harga = getHargaKamar($kamar, $kamar_hotel);
    $total = $harga * $jumlah_kamar * $lama;

    $notif = "Pemesanan untuk $nama_tamu berhasil! Total: Rp " . number_format($total,0,",",".");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pemesanan Hotel Online Interaktif</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container my-5">
    <h1 class="text-center mb-4">Pemesanan Hotel Online</h1>

    <?php if($notif != ""): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($notif); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Galeri Kamar -->
    <h3>Galeri Kamar</h3>
    <div class="row mb-4">
        <?php foreach($kamar_hotel as $k): ?>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
                <img src="<?= $k['gambar']; ?>" class="card-img-top" alt="<?= $k['nama']; ?>">
                <div class="card-body text-center">
                    <h5 class="card-title"><?= $k['nama']; ?></h5>
                    <p class="card-text">Rp <?= number_format($k['harga'],0,",","."); ?> / malam</p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Form Pemesanan -->
    <div class="card p-4 shadow-sm">
        <h4 class="mb-4">Form Pemesanan</h4>
        <form method="post" id="formPemesanan">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Tamu</label>
                    <input type="text" class="form-control" name="nama" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pilih Kamar</label>
                    <select class="form-select" name="kamar" id="kamar" required>
                        <option value="">-- Pilih Kamar --</option>
                        <?php foreach($kamar_hotel as $k): ?>
                        <option value="<?= $k['nama']; ?>" data-harga="<?= $k['harga']; ?>"><?= $k['nama']; ?> - Rp <?= number_format($k['harga'],0,",","."); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Jumlah Kamar</label>
                    <input type="number" class="form-control" name="jumlah_kamar" id="jumlah_kamar" value="1" min="1" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Check-in</label>
                    <input type="date" class="form-control" name="checkin" id="checkin" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Check-out</label>
                    <input type="date" class="form-control" name="checkout" id="checkout" required>
                </div>
                <div class="col-md-12 mt-3">
                    <h5>Total Harga: <span id="totalHarga">Rp 0</span></h5>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="submit" name="submit" class="btn btn-primary">Pesan Sekarang</button>
            </div>
            <div class="mt-3 text-end">
                <a href="../logout.php" class="btn btn-primary">Logout</a>
            </div>
        </form>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const kamarSelect = document.getElementById('kamar');
const jumlahInput = document.getElementById('jumlah_kamar');
const checkinInput = document.getElementById('checkin');
const checkoutInput = document.getElementById('checkout');
const totalHargaEl = document.getElementById('totalHarga');

function hitungTotal() {
    const kamarOption = kamarSelect.selectedOptions[0];
    if(!kamarOption) return totalHargaEl.textContent = "Rp 0";

    const harga = parseInt(kamarOption.dataset.harga);
    const jumlah = parseInt(jumlahInput.value) || 1;
    const checkin = new Date(checkinInput.value);
    const checkout = new Date(checkoutInput.value);

    let lama = 1;
    if(checkin && checkout && checkout > checkin) {
        lama = Math.ceil((checkout - checkin) / (1000*60*60*24));
    }

    const total = harga * jumlah * lama;
    totalHargaEl.textContent = "Rp " + total.toLocaleString('id-ID');
}

kamarSelect.addEventListener('change', hitungTotal);
jumlahInput.addEventListener('input', hitungTotal);
checkinInput.addEventListener('change', hitungTotal);
checkoutInput.addEventListener('change', hitungTotal);
</script>

</body>
</html>
