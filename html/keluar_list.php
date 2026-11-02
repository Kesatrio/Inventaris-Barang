<?php
require_once __DIR__ . '/../php/keluar_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$cari   = trim($_GET['cari'] ?? '');
$dari   = tanggal_valid($_GET['dari'] ?? '')   ? $_GET['dari']   : '';
$sampai = tanggal_valid($_GET['sampai'] ?? '') ? $_GET['sampai'] : '';
$data   = ambil_semua_keluar($pdo, $cari, $dari, $sampai);
$total  = array_sum(array_column($data, 'jumlah'));

tampil_header('Barang Keluar', 'keluar');
?>
<div class="toolbar">
    <form method="get" class="cari">
        <input type="text" name="cari" placeholder="Cari barang / keterangan" value="<?= e($cari) ?>">
        <input type="date" name="dari" value="<?= e($dari) ?>" title="Dari tanggal">
        <input type="date" name="sampai" value="<?= e($sampai) ?>" title="Sampai tanggal">
        <button type="submit">Filter</button>
        <?php if ($cari !== '' || $dari || $sampai): ?><a class="btn" href="keluar_list.php">Reset</a><?php endif; ?>
    </form>
    <a class="btn btn-primary" href="keluar_form.php">+ Catat Barang Keluar</a>
</div>

<div class="tabel-wrap">
<table>
    <thead><tr><th>No</th><th>Tanggal</th><th>Barang</th><th>Jumlah</th><th>Keterangan</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php if (!$data): ?>
        <tr><td colspan="6" class="kosong">Tidak ada data barang keluar.</td></tr>
    <?php endif; ?>
    <?php foreach ($data as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= tanggal_id($r['tanggal']) ?></td>
            <td><?= e($r['kode_barang']) ?> &ndash; <?= e($r['nama_barang']) ?></td>
            <td class="angka-keluar">&minus;<?= (int)$r['jumlah'] ?> <?= e($r['satuan']) ?></td>
            <td><?= e($r['keterangan'] ?? '-') ?></td>
            <td class="aksi">
                <a class="btn btn-kecil" href="keluar_form.php?id=<?= (int)$r['id_keluar'] ?>">Ubah</a>
                <form method="post" action="../php/keluar_aksi.php"
                      onsubmit="return confirm('Hapus transaksi ini? Stok barang akan dikembalikan.');">
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_keluar" value="<?= (int)$r['id_keluar'] ?>">
                    <button type="submit" class="btn btn-kecil btn-bahaya">Hapus</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<p class="catatan"><?= count($data) ?> transaksi, total <?= $total ?> item keluar.</p>
<?php tampil_footer(); ?>
