<?php
require_once __DIR__ . '/../php/supplier_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$cari = trim($_GET['cari'] ?? '');
$data = ambil_semua_supplier($pdo, $cari);

tampil_header('Data Supplier', 'supplier');
?>
<div class="toolbar">
    <form method="get" class="cari">
        <input type="text" name="cari" placeholder="Cari nama atau alamat" value="<?= e($cari) ?>">
        <button type="submit">Cari</button>
    </form>
    <a class="btn btn-primary" href="supplier_form.php">+ Tambah Supplier</a>
</div>

<div class="tabel-wrap">
<table>
    <thead><tr><th>No</th><th>Nama Supplier</th><th>Telepon</th><th>Alamat</th><th>Transaksi Masuk</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$data): ?>
        <tr><td colspan="6" class="kosong">Tidak ada data supplier.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= e($r['nama_supplier']) ?></td>
            <td><?= e($r['telepon'] ?? '-') ?></td>
            <td><?= e($r['alamat'] ?? '-') ?></td>
            <td><?= (int)$r['jumlah_transaksi'] ?> kali</td>
            <td class="aksi">
                <a class="btn btn-kecil" href="supplier_form.php?id=<?= (int)$r['id_supplier'] ?>">Ubah</a>
                <form method="post" action="../php/supplier_aksi.php" onsubmit="return confirm('Yakin hapus supplier ini?');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_supplier" value="<?= (int)$r['id_supplier'] ?>">
                    <button type="submit" class="btn btn-kecil btn-bahaya">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php tampil_footer(); ?>
