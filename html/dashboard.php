<?php
require_once __DIR__ . '/../php/laporan_model.php';
require_once __DIR__ . '/../php/layout.php';
wajib_login();

$r         = ringkasan_dashboard($pdo);
$menipis   = barang_stok_menipis($pdo);
$terakhir  = transaksi_terakhir($pdo);
$kategori  = barang_per_kategori($pdo);
$nilaiMaks = max(array_column($kategori, 'nilai') ?: [0]) ?: 1;

tampil_header('Dashboard', 'dashboard');
?>
<div class="kartu-grid">
    <div class="kartu">
        <div class="kartu-label">Jenis Barang</div>
        <div class="kartu-angka"><?= (int)$r['jumlah_barang'] ?></div>
        <div class="kartu-sub">Total <?= number_format($r['total_stok'], 0, ',', '.') ?> item di gudang</div>
    </div>
    <div class="kartu">
        <div class="kartu-label">Nilai Inventaris</div>
        <div class="kartu-angka kecil"><?= rupiah($r['nilai_inventaris']) ?></div>
        <div class="kartu-sub">Stok &times; harga satuan</div>
    </div>
    <div class="kartu">
        <div class="kartu-label">Masuk / Keluar Bulan Ini</div>
        <div class="kartu-angka"><span class="angka-masuk">+<?= $r['masuk_bulan_ini'] ?></span> / <span class="angka-keluar">&minus;<?= $r['keluar_bulan_ini'] ?></span></div>
        <div class="kartu-sub">Item sejak <?= tanggal_id(date('Y-m-01')) ?></div>
    </div>
    <a class="kartu <?= $r['stok_menipis'] > 0 ? 'peringatan' : '' ?>" href="barang_list.php?menipis=on">
        <div class="kartu-label">Stok Menipis</div>
        <div class="kartu-angka"><?= (int)$r['stok_menipis'] ?></div>
        <div class="kartu-sub">Perlu segera diisi ulang &rarr;</div>
    </a>
</div>

<div class="dua-kolom">
    <section>
        <h3>Stok Menipis</h3>
        <div class="tabel-wrap">
        <table>
            <thead><tr><th>Barang</th><th>Stok</th><th>Min.</th><th></th></tr></thead>
            <tbody>
            <?php if (!$menipis): ?>
                <tr><td colspan="4" class="kosong">Semua stok aman 👍</td></tr>
            <?php endif; ?>
            <?php foreach ($menipis as $b): ?>
                <tr>
                    <td><?= e($b['nama_barang']) ?><br><small><?= e($b['kode_barang']) ?> &middot; <?= e($b['nama_lokasi']) ?></small></td>
                    <td class="angka-keluar"><?= (int)$b['stok'] ?> <?= e($b['satuan']) ?></td>
                    <td><?= (int)$b['stok_minimum'] ?></td>
                    <td><a class="btn btn-kecil" href="masuk_form.php?barang=<?= (int)$b['id_barang'] ?>">Isi stok</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </section>

    <section>
        <h3>Transaksi Terakhir</h3>
        <div class="tabel-wrap">
        <table>
            <thead><tr><th>Tanggal</th><th>Barang</th><th>Jumlah</th></tr></thead>
            <tbody>
            <?php if (!$terakhir): ?>
                <tr><td colspan="3" class="kosong">Belum ada transaksi.</td></tr>
            <?php endif; ?>
            <?php foreach ($terakhir as $t): ?>
                <tr>
                    <td><?= tanggal_id($t['tanggal']) ?></td>
                    <td><?= e($t['nama_barang']) ?></td>
                    <td class="<?= $t['jenis'] === 'Masuk' ? 'angka-masuk' : 'angka-keluar' ?>">
                        <?= $t['jenis'] === 'Masuk' ? '+' : '&minus;' ?><?= (int)$t['jumlah'] ?> <?= e($t['satuan']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </section>
</div>

<section>
    <h3>Nilai Inventaris per Kategori</h3>
    <div class="batang-list">
        <?php foreach ($kategori as $k): ?>
            <div class="batang-baris">
                <div class="batang-label"><?= e($k['nama_kategori']) ?> <small>(<?= (int)$k['jumlah'] ?> barang)</small></div>
                <div class="batang-track"><div class="batang-isi" style="width: <?= round($k['nilai'] / $nilaiMaks * 100, 1) ?>%"></div></div>
                <div class="batang-nilai"><?= rupiah($k['nilai']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php tampil_footer(); ?>
