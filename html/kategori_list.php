<?php
require_once __DIR__ . '/../php/kategori_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$cari = trim($_GET['cari'] ?? '');
$data = ambil_semua_kategori($pdo, $cari);

tampil_header('Data Kategori', 'kategori');
?>
<div class="toolbar">
    <form method="get" class="cari">
        <input type="text" name="cari" placeholder="Cari kategori" value="<?= e($cari) ?>">
        <button type="submit">Cari</button>
    </form>
    <a class="btn btn-primary" href="kategori_form.php">+ Tambah Kategori</a>
</div>

<div class="tabel-wrap">
<table>
    <thead><tr><th>No</th><th>Nama Kategori</th><th>Jumlah Barang</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$data): ?>
        <tr><td colspan="4" class="kosong">Tidak ada data kategori.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($r['nama_kategori']) ?></td>
            <td><?= (int)$r['jumlah_barang'] ?></td>
            <td class="aksi">
                <a class="btn btn-kecil" href="kategori_form.php?id=<?= (int)$r['id_kategori'] ?>">Ubah</a>
                <form method="post" action="../php/kategori_aksi.php" onsubmit="return confirm('Yakin hapus kategori ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_kategori" value="<?= (int)$r['id_kategori'] ?>">
                    <button type="submit" class="btn btn-kecil btn-bahaya">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php tampil_footer(); ?>
