<?php
require_once __DIR__ . '/fungsi.php';

wajib_post('../html/login.php');

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'login') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM pengguna WHERE username = :u");
    $stmt->execute([':u' => $username]);
    $p = $stmt->fetch();

    // password_verify mencocokkan password dengan hash di database
    if ($p && password_verify($password, $p['password'])) {
        session_regenerate_id(true);
        $_SESSION['pengguna'] = [
            'id_pengguna'  => $p['id_pengguna'],
            'username'     => $p['username'],
            'nama_lengkap' => $p['nama_lengkap'],
        ];
        kembali('../html/dashboard.php', 'sukses', 'Selamat datang, ' . $p['nama_lengkap'] . '!');
    }

    $_SESSION['old_username'] = $username;
    kembali('../html/login.php', 'error', 'Username atau password salah.');
}

if ($aksi === 'logout') {
    $_SESSION = [];
    session_destroy();
    session_start();
    kembali('../html/login.php', 'sukses', 'Anda sudah logout.');
}

kembali('../html/login.php', 'error', 'Aksi tidak dikenal.');
