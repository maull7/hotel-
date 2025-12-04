<?php
require_once '../db.php';
require_once __DIR__ . '/../components/auth.php';
ensure_schema($koneksi);

require_login('user');
$userId = (int) current_user_id();
$userName = current_user_name();
$userEmail = current_user_email();
$bankTransferNumber = '987654321009';
$bankTransferHolder = 'HotelMantap Payment';

function generate_barcode_svg(string $code): string
{
    $sanitized = preg_replace('/[^A-Z0-9]/i', '', $code);
    $xPosition = 12;
    $bars = [];

    foreach (str_split($sanitized ?: '0000') as $char) {
        $digit = is_numeric($char) ? (int) $char : (ord($char) % 10);
        $width = 2 + ($digit % 3);
        $height = 80 + (($digit % 2) * 10);
        $bars[] = "<rect x=\"{$xPosition}\" y=\"10\" width=\"{$width}\" height=\"{$height}\" fill=\"#000\" />";
        $xPosition += $width + 2;
    }

    $totalWidth = max(200, $xPosition + 10);

    return "<svg aria-label=\"Barcode {$sanitized}\" viewBox=\"0 0 {$totalWidth} 120\" role=\"img\">" .
        implode('', $bars) .
        "<text x=\"10\" y=\"115\" font-family=\"monospace\" font-size=\"14\">{$sanitized}</text>" .
        "</svg>";
}

$bankTransferBarcode = generate_barcode_svg($bankTransferNumber);

function fetch_rooms(mysqli $conn): array
{
    $rooms = [];
    $result = $conn->query("SELECT * FROM rooms ORDER BY type, price");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
        }
        $result->close();
    }
    return $rooms;
}

function calculate_total(int $price, int $guests, int $nights): int
{
    $nights = max($nights, 1);
    return $price * $guests * $nights;
}

$rooms = fetch_rooms($koneksi);
$alert = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guestName = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $roomId = (int)($_POST['room_id'] ?? 0);
    $checkin = $_POST['checkin'] ?? '';
    $checkout = $_POST['checkout'] ?? '';
    $guests = (int)($_POST['guests'] ?? 1);
    $paymentMethod = $_POST['payment_method'] ?? 'midtrans';

    $roomStmt = $koneksi->prepare("SELECT * FROM rooms WHERE id = ?");
    $roomStmt->bind_param('i', $roomId);
    $roomStmt->execute();
    $roomData = $roomStmt->get_result()->fetch_assoc();
    $roomStmt->close();

    if (!$roomData) {
        $alert = ['type' => 'danger', 'text' => 'Kamar tidak ditemukan.'];
    } elseif (!$checkin || !$checkout || new DateTime($checkout) <= new DateTime($checkin)) {
        $alert = ['type' => 'warning', 'text' => 'Tanggal check-in dan check-out tidak valid.'];
    } else {
        $nights = (new DateTime($checkin))->diff(new DateTime($checkout))->days;
        $totalPrice = calculate_total((int)$roomData['price'], $guests, $nights);

        $paymentStatus = $paymentMethod === 'midtrans' ? 'menunggu' : 'verifikasi';
        $paymentReference = $paymentMethod === 'midtrans' ? 'MID-' . strtoupper(bin2hex(random_bytes(4))) : null;
        $paymentProofPath = null;

        if ($paymentMethod === 'transfer_bank' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/payments';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION) ?: 'jpg';
            $safeName = 'bukti_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $destination = $uploadDir . '/' . $safeName;
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $destination)) {
                $paymentProofPath = 'uploads/payments/' . $safeName;
            }
        }

        $stmt = $koneksi->prepare(
            "INSERT INTO bookings (
        user_id, 
        guest_name, 
        email, 
        phone, 
        room_id, 
        checkin, 
        checkout, 
        guests, 
        total_price, 
        payment_method, 
        payment_status, 
        payment_reference, 
        payment_proof
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            'isssississsss',
            $userId,
            $guestName,
            $email,
            $phone,
            $roomId,
            $checkin,
            $checkout,
            $guests,
            $totalPrice,
            $paymentMethod,
            $paymentStatus,
            $paymentReference,
            $paymentProofPath
        );

        $stmt->execute();
        $stmt->close();


        $alertText = "Pemesanan berhasil disimpan untuk {$guestName}. Total pembayaran Rp " . number_format($totalPrice, 0, ',', '.');
        if ($paymentMethod === 'midtrans') {
            $alertText .= " | Metode: Midtrans (Ref: {$paymentReference}). Simulasikan pembayaran lewat Snap/Bank Redirect.";
        } else {
            $alertText .= " | Metode: Transfer Bank. Gunakan nomor ATM/VA {$bankTransferNumber} dan unggah bukti pembayaran.";
        }

        $alert = ['type' => 'success', 'text' => $alertText];
    }
}

$bookingResult = $koneksi->query(
    "SELECT b.*, r.name AS room_name, r.type AS room_type FROM bookings b
     JOIN rooms r ON r.id = b.room_id
     WHERE b.user_id = {$userId}
     ORDER BY b.created_at DESC LIMIT 10"
);
$bookings = $bookingResult ? $bookingResult->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tamu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .barcode-wrapper svg {
            max-width: 260px;
            height: auto;
            background: #fff;
            padding: 10px 12px;
            border: 1px dashed #ced4da;
            border-radius: 10px;
            box-shadow: inset 0 0 0 1px #f8f9fa;
        }

        .bank-transfer-box {
            background: #f8fafc;
            border: 1px solid #dce2ea;
            border-radius: 12px;
        }
    </style>
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">HotelMantap</a>
            <div class="d-flex gap-2">
                <span class="text-white fw-semibold">Halo, <?= htmlspecialchars($userName); ?></span>
                <a href="../logout.php" class="btn btn-light btn-sm text-primary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="mb-1">Form Pemesanan</h4>
                                <small class="text-muted">Pilih kamar, isi data tamu, dan pilih metode pembayaran</small>
                            </div>
                            <span class="badge text-bg-success">Midtrans / Transfer Bank</span>
                        </div>

                        <?php if ($alert): ?>
                            <div class="alert alert-<?= $alert['type']; ?>"><?= htmlspecialchars($alert['text']); ?></div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Tamu</label>
                                <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($userName); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($userEmail); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Jumlah Tamu</label>
                                <input type="number" name="guests" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pilih Kamar</label>
                                <select name="room_id" class="form-select" required>
                                    <option value="">-- Pilih kamar --</option>
                                    <?php foreach ($rooms as $room): ?>
                                        <option value="<?= $room['id']; ?>" data-price="<?= $room['price']; ?>">
                                            <?= htmlspecialchars($room['name']); ?> (<?= htmlspecialchars($room['type']); ?>) - Rp <?= number_format($room['price'], 0, ',', '.'); ?>/malam
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Check-in</label>
                                <input type="date" name="checkin" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Check-out</label>
                                <input type="date" name="checkout" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Metode Pembayaran</label>
                                <select name="payment_method" class="form-select" id="payment_method" required>
                                    <option value="midtrans">Midtrans (otomatis)</option>
                                    <option value="transfer_bank">Transfer Bank (upload bukti)</option>
                                </select>
                            </div>
                            <div class="col-12" id="proof_wrapper" style="display:none;">
                                <label class="form-label">Bukti Transfer (jpg/png/pdf)</label>
                                <input type="file" name="payment_proof" class="form-control" accept="image/*,.pdf">
                                <small class="text-muted">Wajib diisi jika memilih transfer bank.</small>
                            </div>
                            <div class="col-12" id="bank_transfer_info" style="display:none;">
                                <div class="p-3 bank-transfer-box">
                                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                        <div>
                                            <h6 class="mb-1">Transfer Bank</h6>
                                            <p class="mb-2 small text-muted">Scan barcode atau gunakan nomor ATM/Virtual Account di bawah ini.</p>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge text-bg-primary">ATM/VA</span>
                                                <span class="fw-bold fs-5"><?= rtrim(chunk_split($bankTransferNumber, 4, ' ')); ?></span>
                                            </div>
                                            <small class="text-muted">Atas nama <?= htmlspecialchars($bankTransferHolder); ?></small>
                                        </div>
                                        <div class="barcode-wrapper text-center flex-grow-1">
                                            <?= $bankTransferBarcode; ?>
                                            <div class="small text-muted mt-2">Barcode otomatis tampil saat pilih transfer bank.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-0 text-muted" id="total_info">Total akan muncul setelah memilih kamar & tanggal.</p>
                                </div>
                                <button class="btn btn-primary px-4" type="submit">Simpan Pemesanan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">Master Kamar</h5>
                        <?php foreach ($rooms as $room): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <strong><?= htmlspecialchars($room['name']); ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($room['type']); ?></small>
                                </div>
                                <span class="badge text-bg-info">Rp <?= number_format($room['price'], 0, ',', '.'); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="mb-3">Pembayaran</h5>
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2"><strong>Midtrans:</strong> sistem otomatis, referensi dikirimkan setelah simpan. Gunakan referensi untuk uji coba sandbox.</li>
                            <li class="mb-2"><strong>Transfer Bank:</strong> upload bukti, admin akan verifikasi.</li>
                            <li class="mb-2"><strong>Konfirmasi:</strong> status bisa dipantau lewat menu admin pada Master Pemesanan.</li>
                        </ul>
                        <div class="alert alert-info mb-0">
                            <strong>Langkah verifikasi Midtrans:</strong>
                            <ol class="mb-0 mt-2 small">
                                <li>Pilih metode pembayaran <em>Midtrans</em> saat menyimpan pesanan.</li>
                                <li>Catat kode referensi otomatis (contoh: <code>MID-XXXXXX</code>).</li>
                                <li>Buka menu Admin &raquo; Data Pemesanan, lalu gunakan panel <strong>Simulasi Midtrans</strong> untuk menandai pembayaran sukses/gagal sesuai kode referensi.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">Riwayat Pemesanan Terbaru</h4>
                    <span class="text-muted small">Top 10 terbaru</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tamu</th>
                                <th>Kamar</th>
                                <th>Jadwal</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Referensi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($bookings) > 0): ?>
                                <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($booking['guest_name']); ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($booking['email']); ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($booking['room_name']); ?> (<?= htmlspecialchars($booking['room_type']); ?>)</td>
                                        <td><?= htmlspecialchars($booking['checkin']); ?> - <?= htmlspecialchars($booking['checkout']); ?></td>
                                        <td class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($booking['payment_method'])); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = match ($booking['payment_status']) {
                                                'dibayar' => 'success',
                                                'verifikasi' => 'warning',
                                                'gagal' => 'danger',
                                                default => 'secondary',
                                            };
                                            ?>
                                            <span class="badge text-bg-<?= $badgeClass; ?> text-uppercase"><?= htmlspecialchars($booking['payment_status']); ?></span>
                                        </td>
                                        <td>Rp <?= number_format($booking['total_price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <?php if ($booking['payment_reference']): ?>
                                                <span class="badge text-bg-dark"><?= htmlspecialchars($booking['payment_reference']); ?></span>
                                            <?php elseif ($booking['payment_proof']): ?>
                                                <a href="../<?= htmlspecialchars($booking['payment_proof']); ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Bukti TF</a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data pemesanan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const roomSelect = document.querySelector('select[name="room_id"]');
        const checkin = document.querySelector('input[name="checkin"]');
        const checkout = document.querySelector('input[name="checkout"]');
        const guestsInput = document.querySelector('input[name="guests"]');
        const totalInfo = document.getElementById('total_info');
        const paymentMethod = document.getElementById('payment_method');
        const proofWrapper = document.getElementById('proof_wrapper');
        const bankTransferInfo = document.getElementById('bank_transfer_info');

        function updateTotal() {
            const option = roomSelect.selectedOptions[0];
            if (!option || !checkin.value || !checkout.value) {
                totalInfo.textContent = 'Total akan muncul setelah memilih kamar & tanggal.';
                return;
            }
            const price = parseInt(option.dataset.price || '0');
            const start = new Date(checkin.value);
            const end = new Date(checkout.value);
            const diff = Math.max(1, Math.ceil((end - start) / (1000 * 60 * 60 * 24)));
            const guests = parseInt(guestsInput.value || '1');
            const total = price * guests * diff;
            totalInfo.textContent = `Total: Rp ${total.toLocaleString('id-ID')} (${diff} malam, ${guests} tamu)`;
        }

        roomSelect.addEventListener('change', updateTotal);
        checkin.addEventListener('change', updateTotal);
        checkout.addEventListener('change', updateTotal);
        guestsInput.addEventListener('input', updateTotal);
        paymentMethod.addEventListener('change', () => {
            const isTransfer = paymentMethod.value === 'transfer_bank';
            proofWrapper.style.display = isTransfer ? 'block' : 'none';
            bankTransferInfo.style.display = isTransfer ? 'block' : 'none';
        });

        // Initialize visibility on first load
        paymentMethod.dispatchEvent(new Event('change'));
    </script>
</body>

</html>