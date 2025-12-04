<?php
session_start();
require_once __DIR__ . '/db.php';
ensure_schema($koneksi);

if (isset($_POST["register"])) {
    $nama = trim($_POST["nama"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm = $_POST["confirm"] ?? '';

    if ($password !== $confirm) {
        $error = "Password dan konfirmasi tidak sama!";
    } else {

        // Hash password
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // Cek email sudah dipakai atau belum
        $check = $koneksi->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $check->bind_param('s', $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult && $checkResult->num_rows > 0) {
            $error = "Email sudah digunakan!";
        } else {
            // Tambah user baru (role default: user)
            $query = $koneksi->prepare("INSERT INTO users (nama, email, password, role) VALUES (?,?,?,'user')");
            $query->bind_param('sss', $nama, $email, $hash);
            $query->execute();
            $query->close();

            $success = "Pendaftaran berhasil! Silakan login.";
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Estetik</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #f6d5f7, #fbe9d7);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Poppins", sans-serif;
        }
        .reg-card {
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 35px;
            width: 420px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .btn-custom {
            background: #c388f6;
            color: white;
        }
        .btn-custom:hover {
            background: #a96be0;
        }
    </style>
</head>
<body>

<div class="reg-card">
    <h3 class="text-center mb-4">Daftar Akun ✨</h3>

    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php } ?>

    <?php if (isset($success)) { ?>
        <div class="alert alert-success"><?= $success; ?></div>
    <?php } ?>

    <form method="POST">
        <div class="mb-3">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Konfirmasi Password</label>
            <input type="password" name="confirm" class="form-control" required>
        </div>

        <button type="submit" name="register" class="btn btn-custom w-100">Daftar</button>

        <p class="text-center mt-3">
            Sudah punya akun? <a href="login.php">Login</a>
        </p>
    </form>
</div>

</body>
</html>
