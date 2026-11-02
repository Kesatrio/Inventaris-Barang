<?php
require_once __DIR__ . '/../php/lokasi_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$cari = trim($_GET['cari'] ?? '');
$data = ambil_semua_lokasi($pdo, $cari);

tampil_header('Data Lokasi', 'lokasi');
?>
<div class="toolbar">
    <form method="get" class="cari">
        <input type="text" name="cari" placeholder="Cari lokasi" value="<?= e($cari) ?>">
        <button type="submit">Cari</button>
    </form>
    <a class="btn btn-primary" href="lokasi_form.php">+ Tambah Lokasi</a>
</div>

<div class="tabel-wrap">
<table>
    <thead><tr><th>No</th><th>Nama Lokasi</th><th>Keterangan</th><th>Jumlah Barang</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$data): ?>
        <tr><td colspan="5" class="kosong">Tidak ada data lokasi.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($r['nama_lokasi']) ?></td>
            <td><?= e($r['keterangan'] ?? '-') ?></td>
            <td><?= (int)$r['jumlah_barang'] ?></td>
            <td class="aksi">
                <a class="btn btn-kecil" href="lokasi_form.php?id=<?= (int)$r['id_lokasi'] ?>">Ubah</a>
                <form method="post" action="../php/lokasi_aksi.php" onsubmit="return confirm('Yakin hapus lokasi ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_lokasi" value="<?= (int)$r['id_lokasi'] ?>">
                    <button type="submit" class="btn btn-kecil btn-bahaya">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php tampil_footer(); ?>
