<?php
session_start();
require_once __DIR__ . '/db.php';
ensure_schema($koneksi);

// Proses Login
if (isset($_POST["login"])) {
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';

    $stmt = $koneksi->prepare("SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $row["password"])) {

            // Set session
            $_SESSION["login"] = true;
            $_SESSION["user_id"] = (int)$row["id"];
            $_SESSION["nama"]  = $row["nama"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["role"]  = $row["role"];

            // Redirect berdasarkan role
            $redirect = $_GET['redirect'] ?? null;
            if ($redirect) {
                header("Location: " . $redirect);
            } elseif ($row["role"] == "admin") {
                header("Location: admin/dash.php");
            } else {
                header("Location: user/user_dashboard.php");
            }
            exit;

        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Estetik Bootstrap</title>

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
        .login-card {
            background: #ffffffcc;
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 35px;
            width: 380px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        .form-control:focus {
            border-color: #c388f6;
            box-shadow: 0 0 6px #c388f677;
        }
        .btn-custom {
            background: #c388f6;
            color: white;
        }
        .btn-custom:hover {
            background: #a96be0;
            color: white;
        }
        a {
            color: #a96be0;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <h3 class="text-center mb-4">Welcome Back ✨</h3>

        <?php if (isset($error)) { ?>
            <div class="alert alert-danger text-center"><?= $error; ?></div>
        <?php } ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input name="email" type="email" class="form-control" placeholder="Masukkan email Anda" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input name="password" type="password" class="form-control" placeholder="Masukkan password" required>
            </div>

            <button type="submit" name="login" class="btn btn-custom w-100 mt-2">Login</button>

            <p class="text-center mt-3">
                Belum punya akun? <a href="daftar.php">Daftar</a>
            </p>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
