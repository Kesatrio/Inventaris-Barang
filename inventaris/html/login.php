<?php
require_once __DIR__ . '/../php/fungsi.php';

// Kalau sudah login, langsung ke dashboard
if (!empty($_SESSION['pengguna'])) {
    header('Location: dashboard.php');
    exit;
}
$flash    = ambil_flash();
$username = $_SESSION['old_username'] ?? '';
unset($_SESSION['old_username']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Inventaris Kantor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="halaman-login">
<div class="kotak-login">
    <div class="logo">📦</div>
    <h1>Inventaris Kantor</h1>
    <p class="sub">Sistem Informasi Manajemen Inventaris Barang Kantor</p>

    <?php if ($flash): ?>
        <div class="alert <?= e($flash['tipe']) ?>"><?= e($flash['pesan']) ?></div>
    <?php endif; ?>

    <form method="post" action="../php/auth_aksi.php" class="form">
        <input type="hidden" name="aksi" value="login">
        <label>Username
            <input type="text" name="username" required autofocus value="<?= e($username) ?>">
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn btn-primary btn-penuh">Masuk</button>
    </form>
</div>
</body>
</html>
