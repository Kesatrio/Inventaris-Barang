<?php
require_once __DIR__ . '/../php/laporan_model.php';
require_once __DIR__ . '/../php/barang_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$jenis = ($_GET['jenis'] ?? 'stok') === 'transaksi' ? 'transaksi' : 'stok';

if ($jenis === 'stok') {
    $kategori       = (int)($_GET['kategori'] ?? 0);
    $daftarKategori = ambil_kategori($pdo);
    $data           = laporan_stok($pdo, $kategori);
    $totalNilai     = array_sum(array_column($data, 'nilai'));
} else {
    $dari   = tanggal_valid($_GET['dari'] ?? '')   ? $_GET['dari']   : date('Y-m-01');
    $sampai = tanggal_valid($_GET['sampai'] ?? '') ? $_GET['sampai'] : date('Y-m-d');
    $tipe   = in_array($_GET['tipe'] ?? '', ['Masuk', 'Keluar'], true) ? $_GET['tipe'] : '';
    $data   = laporan_transaksi($pdo, $dari, $sampai, $tipe);
    $totalMasuk = $totalKeluar = 0;
    foreach ($data as $t) {
        if ($t['jenis'] === 'Masuk') {
            $totalMasuk += $t['jumlah'];
        } else {
            $totalKeluar += $t['jumlah'];
        }
    }
}

tampil_header('Laporan', 'laporan');
?>
<div class="tab no-print">
    <a href="laporan.php?jenis=stok" class="<?= $jenis === 'stok' ? 'aktif' : '' ?>">Laporan Stok</a>
    <a href="laporan.php?jenis=transaksi" class="<?= $jenis === 'transaksi' ? 'aktif' : '' ?>">Laporan Transaksi</a>
</div>

<div class="toolbar no-print">
    <form method="get" class="cari">
        <input type="hidden" name="jenis" value="<?= $jenis ?>">
        <?php if ($jenis === 'stok'): ?>
            <select name="kategori">
                <option value="0">Semua kategori</option>
                <?php foreach ($daftarKategori as $k): ?>
                    <option value="<?= (int)$k['id_kategori'] ?>" <?= $kategori === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <input type="date" name="dari" value="<?= e($dari) ?>" required>
            <span class="sd">s.d.</span>
            <input type="date" name="sampai" value="<?= e($sampai) ?>" required>
            <select name="tipe">
                <option value="">Masuk &amp; Keluar</option>
                <option value="Masuk"  <?= $tipe === 'Masuk' ? 'selected' : '' ?>>Masuk saja</option>
                <option value="Keluar" <?= $tipe === 'Keluar' ? 'selected' : '' ?>>Keluar saja</option>
            </select>
        <?php endif; ?>
        <button type="submit">Tampilkan</button>
    </form>
    <button type="button" class="btn btn-primary" onclick="window.print()">🖨 Cetak</button>
</div>

<div class="kop-cetak">
    <strong>LAPORAN <?= $jenis === 'stok' ? 'STOK BARANG' : 'TRANSAKSI BARANG' ?></strong><br>
    Sistem Informasi Manajemen Inventaris Barang Kantor<br>
    <?php if ($jenis === 'transaksi'): ?>Periode <?= tanggal_id($dari) ?> s.d. <?= tanggal_id($sampai) ?><br><?php endif; ?>
    <small>Dicetak <?= tanggal_id(date('Y-m-d')) ?></small>
</div>

<div class="tabel-wrap">
<?php if ($jenis === 'stok'): ?>
    <table>
        <thead><tr><th>No</th><th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Lokasi</th><th>Stok</th><th>Harga</th><th>Nilai</th></tr></thead>
        <tbody>
        <?php if (!$data): ?><tr><td colspan="8" class="kosong">Tidak ada data.</td></tr><?php endif; ?>
        <?php foreach ($data as $i => $b): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= e($b['kode_barang']) ?></td>
                <td><?= e($b['nama_barang']) ?></td>
                <td><?= e($b['nama_kategori']) ?></td>
                <td><?= e($b['nama_lokasi']) ?></td>
                <td><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?><?= $b['stok'] <= $b['stok_minimum'] ? ' <span class="badge merah">Menipis</span>' : '' ?></td>
                <td><?= rupiah($b['harga']) ?></td>
                <td><?= rupiah($b['nilai']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><th colspan="7">Total Nilai Inventaris</th><th><?= rupiah($totalNilai) ?></th></tr></tfoot>
    </table>
<?php else: ?>
    <table>
        <thead><tr><th>No</th><th>Tanggal</th><th>Jenis</th><th>Barang</th><th>Supplier</th><th>Jumlah</th><th>Keterangan</th></tr></thead>
        <tbody>
        <?php if (!$data): ?><tr><td colspan="7" class="kosong">Tidak ada transaksi pada periode ini.</td></tr><?php endif; ?>
        <?php foreach ($data as $i => $t): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= tanggal_id($t['tanggal']) ?></td>
                <td><span class="badge <?= $t['jenis'] === 'Masuk' ? 'hijau' : 'merah' ?>"><?= $t['jenis'] ?></span></td>
                <td><?= e($t['kode_barang']) ?> &ndash; <?= e($t['nama_barang']) ?></td>
                <td><?= e($t['pihak'] ?? '-') ?></td>
                <td><?= (int)$t['jumlah'] ?> <?= e($t['satuan']) ?></td>
                <td><?= e($t['keterangan'] ?? '-') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><th colspan="5">Total item masuk</th><th colspan="2" class="angka-masuk">+<?= $totalMasuk ?></th></tr>
            <tr><th colspan="5">Total item keluar</th><th colspan="2" class="angka-keluar">&minus;<?= $totalKeluar ?></th></tr>
        </tfoot>
    </table>
<?php endif; ?>
</div>
<?php tampil_footer(); ?>
