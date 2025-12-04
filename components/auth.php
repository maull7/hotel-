<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login(string $role = 'user'): void
{
    if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }

    if ($role && ($_SESSION['role'] ?? null) !== $role) {
        header('Location: /login.php');
        exit;
    }
}

function current_user_id(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function current_user_name(): string
{
    return $_SESSION['nama'] ?? '';
}

function current_user_email(): string
{
    return $_SESSION['email'] ?? '';
}
?>
