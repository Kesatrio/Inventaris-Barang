<?php
// Kerangka tampilan (header + navigasi + footer) yang dipakai semua halaman
require_once __DIR__ . '/fungsi.php';

function tampil_header(string $judul, string $aktif, bool $sempit = false): void
{
    $menu = [
        'dashboard' => ['Dashboard',     'dashboard.php'],
        'barang'    => ['Barang',        'barang_list.php'],
        'masuk'     => ['Barang Masuk',  'masuk_list.php'],
        'keluar'    => ['Barang Keluar', 'keluar_list.php'],
        'kategori'  => ['Kategori',      'kategori_list.php'],
        'lokasi'    => ['Lokasi',        'lokasi_list.php'],
        'supplier'  => ['Supplier',      'supplier_list.php'],
        'laporan'   => ['Laporan',       'laporan.php'],
    ];
    $nama  = $_SESSION['pengguna']['nama_lengkap'] ?? '';
    $flash = ambil_flash();
    ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> - Inventaris Kantor</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="topbar-isi">
        <a class="brand" href="dashboard.php">📦 Inventaris Kantor</a>
        <nav class="menu">
            <?php foreach ($menu as $kunci => [$label, $url]): ?>
                <a href="<?= $url ?>" class="<?= $kunci === $aktif ? 'aktif' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="user">
            <span><?= e($nama) ?></span>
            <form method="post" action="../php/auth_aksi.php">
                <input type="hidden" name="aksi" value="logout">
                <button type="submit" class="btn btn-kecil">Logout</button>
            </form>
        </div>
    </div>
</header>
<main class="container<?= $sempit ? ' sempit' : '' ?>">
    <h2><?= e($judul) ?></h2>
    <?php if ($flash): ?>
        <div class="alert <?= e($flash['tipe']) ?>"><?= e($flash['pesan']) ?></div>
    <?php endif; ?>
<?php
}

function tampil_footer(): void
{
    ?>
</main>
<footer class="footer">Sistem Informasi Manajemen Inventaris Barang Kantor &middot; Tugas Besar Basis Data A2</footer>
</body>
</html>
<?php
}
